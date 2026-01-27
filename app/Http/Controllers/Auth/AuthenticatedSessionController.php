<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Headmaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        session()->regenerate();

        $user = auth()->user();

        // Untuk siswa: selalu izinkan login dan set is_active = true (auto online status)
        if ($user->hasRole('siswa')) {
            $user->is_active = true;
            $user->save();
        }

        //sanctum token
        $token = $user->createToken('dashboard-token')->plainTextToken;
        session(['api_token' => $token]);

        logActivity("$user->name (ID: {$user->id}) berhasil login");

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('guru')) {
            try {
                $school_id = $user->teacher->school_id;
                $guru_id = $user->teacher->id;

                session([
                    'school_id' => $school_id,
                    'school_name' => $user->teacher->school->name,
                    'guru_id' => $guru_id
                ]);

                if (!isset($school_id)) {
                    throw new \Exception('School ID not found');
                }

                return redirect()->route('guru.dashboard');
            } catch (\Exception $e) {
                return $this->forceLogout($request, 'Gagal login : ' . $e->getMessage());
            }
        } elseif ($user->hasRole('siswa')) {
            return redirect()->route('siswa.dashboard');
        } elseif ($user->hasRole('kepala')) {
            try {
                $school_id = Headmaster::where('user_id', $user->id)->first()->school_id;
                $kepala_id = $user->head->id;

                session([
                    'school_id' => $school_id,
                    'school_name' => $user->head->school->name,
                    'kepala_id' => $kepala_id
                ]);

                if (!isset($school_id)) {
                    throw new \Exception('School ID not found');
                }
                return redirect()->route('kepala.dashboard');
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->withErrors(['error' => 'Gagal login : ' . $e->getMessage()]);
            }
        } elseif ($user->hasRole('super')) {
            return redirect()->route('super.dashboard');
        }

        // return redirect()->intended(route('dashboard', absolute: false));
        return redirect()->intended(RouteServiceProvider::HOME());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        logActivity("$user->name (ID: {$user->id}) berhasil Logout");

        // Set is_active menjadi false jika user adalah siswa
        if ($user && $user->hasRole('siswa')) {
            $user->is_active = false;
            $user->save();
        }

        $request->user()->tokens()->delete();
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function forceLogout(Request $request, string $message): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            logActivity("{$user->name} (ID: {$user->id}) dipaksa logout. Alasan: {$message}");

            // Hapus semua token Sanctum
            $user->tokens()->delete();
        }

        // Logout dari guard web
        Auth::guard('web')->logout();

        // Bersihkan seluruh data session
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect ke halaman welcome (/) dengan pesan error
        return redirect('/')->withErrors(['error' => $message]);
    }
}
