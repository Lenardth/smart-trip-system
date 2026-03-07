<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $media = Media::where('user_id', $user->id)
            ->with('trip')
            ->latest()
            ->get();

        return response()->json([
            'media'        => $media,
            'total_photos' => Media::where('user_id', $user->id)->images()->count(),
            'total_videos' => Media::where('user_id', $user->id)->videos()->count(),
            'total_media'  => Media::where('user_id', $user->id)->count(),
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'media.*'  => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp,mp4,mov,avi,mkv|max:102400',
            'trip_id'  => 'nullable|exists:trips,id',
            'location' => 'nullable|string|max:255',
        ]);

        $user     = Auth::user();
        $uploaded = [];

        foreach ($request->file('media') as $file) {
            $type     = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video';
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path     = 'media/' . $user->id . '/' . date('Y/m');
            $storedPath = $file->storeAs($path, $filename, 'public');

            if ($type === 'image') {
                $this->createThumbnail($storedPath);
            }

            $media = Media::create([
                'user_id'   => $user->id,
                'trip_id'   => $request->trip_id,
                'title'     => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_name' => $filename,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'type'      => $type,
                'location'  => $request->location,
            ]);

            $uploaded[] = $media;
        }

        return response()->json([
            'message' => 'Files uploaded successfully',
            'files'   => $uploaded,
            'count'   => count($uploaded),
        ]);
    }

    private function createThumbnail(string $imagePath): void
    {
        try {
            $fullPath   = storage_path('app/public/' . $imagePath);
            $image      = Image::make($fullPath);
            $thumbPath  = str_replace('.', '_thumb.', $imagePath);
            $mediumPath = str_replace('.', '_medium.', $imagePath);

            $image->fit(300, 300)->save(storage_path('app/public/' . $thumbPath));
            $image->fit(800, 800)->save(storage_path('app/public/' . $mediumPath));
        } catch (\Throwable $e) {
            \Log::error('Thumbnail creation failed: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        $user    = Auth::user();
        $deleted = 0;

        foreach ($request->ids as $id) {
            $media = Media::where('id', $id)->where('user_id', $user->id)->first();

            if (!$media) continue;

            Storage::disk('public')->delete($media->file_path);
            Storage::disk('public')->delete([
                str_replace('.', '_thumb.', $media->file_path),
                str_replace('.', '_medium.', $media->file_path),
            ]);

            $media->delete();
            $deleted++;
        }

        return response()->json([
            'message' => "Deleted {$deleted} items",
            'deleted' => $deleted,
        ]);
    }

    public function toggleFavorite($id)
    {
        $media = Media::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $media->update(['is_favorite' => !$media->is_favorite]);

        return response()->json(['is_favorite' => $media->is_favorite]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:255',
            'trip_id'     => 'nullable|exists:trips,id',
        ]);

        $media = Media::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $media->update($request->only(['title', 'description', 'location', 'trip_id']));

        return response()->json([
            'message' => 'Media updated successfully',
            'media'   => $media,
        ]);
    }

    public function stats()
    {
        $userId = Auth::id();
        $photos = Media::where('user_id', $userId)->images()->count();
        $videos = Media::where('user_id', $userId)->videos()->count();
        $size   = Media::where('user_id', $userId)->sum('file_size');

        return response()->json([
            'total_photos' => $photos,
            'total_videos' => $videos,
            'total_media'  => $photos + $videos,
            'used_storage' => round($size / 1024 / 1024, 2),
        ]);
    }
}
