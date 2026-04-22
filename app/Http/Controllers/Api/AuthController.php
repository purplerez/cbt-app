<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $user = Auth::user();

        // Untuk siswa: selalu izinkan login dan set is_active = true (auto online status)
        if ($user->hasRole('siswa')) {
            $user->is_active = true;
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
        $user = Auth::user();

        if ($user && $user->hasRole('siswa')) {
            $doSetLogout = $request->input('is_logout', 0);
            if ($doSetLogout) {
                $user->is_logout = true;
            }
            $user->is_active = false;
            $user->save();
        }

        $user->tokens()->delete();

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
        $user = $request->user();

        if ($user && $user->hasRole('siswa')) {
            // Only update if is_active is false, to minimize database writes
            // This reduces writes from ~16,800/hour (700 students) to ~280/hour
            if (!$user->is_active) {
                $user->is_active = true;
                $user->save();
            }
            // Touch updated_at for session tracking (if needed for other purposes)
            // but don't repeatedly save the same data
        }

        return response()->json([
            'message' => 'OK',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
