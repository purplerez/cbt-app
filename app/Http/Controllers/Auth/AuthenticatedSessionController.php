<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        // dd([
        //     'user_id' => $user->id,
        //     'user_email' => $user->email,
        //     'all_roles' => $user->getRoleNames()->toArray(),
        //     'roles_collection' => $user->roles->pluck('name')->toArray(),
        //     'has_admin' => $user->hasRole('admin'),
        //     'has_guru' => $user->hasRole('guru'),
        //     'has_siswa' => $user->hasRole('siswa'),
        //     'has_kepala' => $user->hasRole('kepala'),
        //     'direct_role_check' => [
        //         'admin' => $user->roles->contains('name', 'admin'),
        //         'guru' => $user->roles->contains('name', 'guru'),
        //         'siswa' => $user->roles->contains('name', 'siswa'),
        //         'kepala' => $user->roles->contains('name', 'kepala'),
        //     ]
        // ]);

        if($user->hasRole('admin')){
               return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('guru')) {
                return redirect()->route('guru.dashboard');
        } elseif ($user->hasRole('siswa')) {
                return redirect()->route('siswa.dashboard');
        } elseif ($user->hasRole('kepala')){
                return redirect()->route('kepala.dashboard');
        }

        // return redirect()->intended(route('dashboard', absolute: false));
        return redirect()->intended(RouteServiceProvider::HOME());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
