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

        // Set is_active menjadi false jika user adalah siswa
        if ($user && $user->hasRole('siswa')) {
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
     */
    public function heartbeat(Request $request)
    {
        $user = $request->user();

        if ($user && $user->hasRole('siswa')) {
            $user->is_active = true;
            $user->save();
        }

        return response()->json([
            'message' => 'OK',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
