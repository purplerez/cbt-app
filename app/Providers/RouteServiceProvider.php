<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Return path to redirect after login based on role.
     */
    public static function home(): string
    {
        $user = auth()->user();

        if ($user?->hasRole('admin')) {
            return '/admin/dashboard';
        } elseif ($user?->hasRole('kepala')) {
            return '/kepala/dashboard';
        } elseif ($user?->hasRole('guru')) {
            return '/guru/dashboard';
        } elseif ($user?->hasRole('siswa')) {
            return '/siswa/dashboard';
        }

        return '/'; // fallback
    }

    public function boot(): void
    {
        parent::boot();

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
