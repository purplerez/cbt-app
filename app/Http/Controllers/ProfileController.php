<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile information.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form for editing.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Check if email is being changed
            if ($validated['email'] !== $user->email) {
                $user->email_verified_at = null;
            }

            $user->fill($validated);
            $user->save();

            // Log the activity
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Memperbaharui Profil');

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());

            return Redirect::route('profile.edit')->with('error', 'Gagal memperbarui profil. Silakan coba lagi.');
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log the activity
            $user = $request->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Mengubah Password');

            return back()->with('status', 'password-updated');
        } catch (\Exception $e) {
            \Log::error('Password update error: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengubah password. Silakan coba lagi.');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);

            $user = $request->user();
            $userName = $user->name;
            $userId = $user->id;

            // Log the activity before deletion
            logActivity($userName . ' (ID: ' . $userId . ') Berhasil Menghapus Profil');

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')->with('status', 'account-deleted');
        } catch (\Exception $e) {
            \Log::error('Account deletion error: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus akun. Silakan coba lagi.');
        }
    }
}
