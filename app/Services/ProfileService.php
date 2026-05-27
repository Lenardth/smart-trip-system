<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function uploadPicture(User $user, UploadedFile $picture): void
    {
        $storageDisk = config('profile.upload.storage_disk', 'public');
        $storagePath = config('profile.upload.storage_path', 'profile-pictures');

        if ($user->profile_picture) {
            Storage::disk($storageDisk)->delete($user->profile_picture);
        }

        $user->update([
            'profile_picture' => $picture->store($storagePath, $storageDisk),
        ]);
    }

    public function deletePicture(User $user): void
    {
        $storageDisk = config('profile.upload.storage_disk', 'public');

        if ($user->profile_picture) {
            Storage::disk($storageDisk)->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
    }

    public function deleteAccount(User $user): void
    {
        Auth::logout();
        $user->delete();
    }
}
