<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureKepalaSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user adalah kepala sekolah
        if (Auth::check() && Auth::user()->hasRole('kepala')) {
            $school_id = session('school_id');
            
            // Jika session school_id tidak ada, coba ambil dari database
            if (!$school_id) {
                $user = Auth::user();
                $headmaster = $user->head;
                
                if ($headmaster && $headmaster->school_id) {
                    session([
                        'school_id' => $headmaster->school_id,
                        'school_name' => $headmaster->school->name,
                        'kepala_id' => $headmaster->id
                    ]);
                    
                    Log::info('EnsureKepalaSession: Session school_id restored for user ' . $user->id);
                } else {
                    Log::error('EnsureKepalaSession: Cannot find headmaster data for user ' . $user->id);
                    return redirect()->route('login')->withErrors(['error' => 'Data kepala sekolah tidak ditemukan. Silakan hubungi administrator.']);
                }
            }
        }
        
        return $next($request);
    }
}
