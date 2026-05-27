<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profiles) {}

    public function uploadPicture(Request $request): RedirectResponse
    {
        $maxFileSize = config('profile.upload.max_file_size', 5120);
        $mimes = config('profile.upload.mimes', ['jpg', 'jpeg', 'png', 'webp']);

        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:' . implode(',', $mimes), 'max:' . $maxFileSize],
        ]);

        $this->profiles->uploadPicture($request->user(), $request->file('profile_picture'));

        return back()->with('status', 'profile-picture-updated');
    }

    public function deletePicture(Request $request): RedirectResponse
    {
        $this->profiles->deletePicture($request->user());

        return back()->with('status', 'profile-picture-deleted');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $this->profiles->updatePassword($request->user(), $validated['password']);

        return back()->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $this->profiles->deleteAccount($request->user());
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'account-deleted');
    }
}
