<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $forceExitLocked = $user->hasRole('siswa') && $this->isForceExitLocked($user->id);

        // Allow login even when the account is force-exit locked.
        // The lock state is preserved so the dashboard can show the current status,
        // while exam entry middleware/endpoints continue to block prohibited actions.
        if (!$forceExitLocked) {
            // Successful login: set is_active = 1, is_logout = 0
            $user->is_active = true;
            $user->is_logout = false;
            $user->save();
        }

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

    // Heartbeat endpoint removed. Student activity/inactivity is tracked via
    // token `last_used_at` and the `DeactivateInactiveStudents` scheduler.

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
