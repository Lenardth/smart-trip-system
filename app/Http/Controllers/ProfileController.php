<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function uploadPicture(Request $request): RedirectResponse
    {
        $maxFileSize = config('profile.upload.max_file_size', 5120);
        $mimes = config('profile.upload.mimes', ['jpg', 'jpeg', 'png', 'webp']);
        
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:' . implode(',', $mimes), 'max:' . $maxFileSize],
        ]);

        $user = $request->user();
        $storageDisk = config('profile.upload.storage_disk', 'public');
        $storagePath = config('profile.upload.storage_path', 'profile-pictures');

        if ($user->profile_picture) {
            Storage::disk($storageDisk)->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store($storagePath, $storageDisk);
        $user->update(['profile_picture' => $path]);

        return back()->with('status', 'profile-picture-updated');
    }

    public function deletePicture(Request $request): RedirectResponse
    {
        $storageDisk = config('profile.upload.storage_disk', 'public');
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk($storageDisk)->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return back()->with('status', 'profile-picture-deleted');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'account-deleted');
    }
}
