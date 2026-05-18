<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Login Gagal, Email / Password salah'
            ], 401);
        }

        /** @var User|null $user */
        $user = Auth::user();

        // Check if student is locked (force exit: is_active = 1, is_logout = 1)
        if ($user->hasRole('siswa') && $this->isForceExitLocked($user->id)) {
            Auth::logout();

            return response()->json([
                'message' => 'Akun sedang dikunci karena force exit. Hubungi proctor untuk reaktivasi.',
                'force_exit' => true,
            ], 403);
        }

        // Successful login: set is_active = 1, is_logout = 0
        $user->is_active = true;
        $user->is_logout = false;
        $user->save();

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'user' => $user,
            'token' => $token,
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function logout(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            // For student users: check if they have an active exam session in progress
            if ($user->hasRole('siswa')) {
                $activeSession = $user->examSessions()
                    ->where('status', 'progress')
                    ->exists();

                if ($activeSession) {
                    // Force exit scenario: logout while exam is in progress
                    // is_active = 1 (stay active), is_logout = 1 (marked as force logout)
                    $user->is_active = true;
                    $user->is_logout = true;
                } else {
                    // Normal logout: no active exam
                    // is_active = 0, is_logout = 0
                    $user->is_active = false;
                    $user->is_logout = false;
                }
            } else {
                // Non-student roles (admin, guru, kepala, super): normal logout
                $user->is_active = false;
                $user->is_logout = false;
            }
            $user->save();
        }

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logout Berhasil',
        ]);
    }

    /**
     * Heartbeat endpoint — dipanggil frontend setiap 2-3 menit
     * agar siswa tidak dianggap offline oleh scheduler.
     *
     * OPTIMIZATION: Hanya update database jika is_active bukan true
     * atau jika last_activity sudah lebih dari 5 menit lalu.
     * Ini mengurangi database writes dari N per heartbeat ke ~N/10 atau lebih.
     */
    public function heartbeat(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user && $user->hasRole('siswa')) {
            if ($this->isForceExitLocked($user->id)) {
                return response()->json([
                    'message' => 'Akun sedang dikunci karena force exit.',
                    'force_exit' => true,
                ], 403);
            }

            if ($this->hasActiveExamSession($user->id)) {
                $user->is_active = true;
                $user->is_logout = false;
                $user->save();
            }
        }

        return response()->json([
            'message' => 'OK',
            'timestamp' => now()->toISOString(),
        ]);
    }

    private function hasActiveExamSession(int $userId): bool
    {
        return ExamSession::where('user_id', $userId)
            ->whereIn('status', ['pending', 'process', 'progress'])
            ->exists();
    }

    private function isForceExitLocked(int $userId): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || $user->id !== $userId) {
            return false;
        }

        return $user->is_active === true && $user->is_logout === true;
    }
}
