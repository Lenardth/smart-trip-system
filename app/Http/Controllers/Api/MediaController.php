<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'media' => 'required|array',
                'media.*' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp,mp4,mov,avi,mkv|max:102400',
                'trip_id' => 'nullable|exists:trips,id',
                'location' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedMedia = [];
            $files = $request->file('media');

            foreach ($files as $file) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();
                $type = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
                
                $path = $file->storeAs('public/media/' . $type . 's', $filename);
                
                $media = Media::create([
                    'user_id' => auth()->id(),
                    'trip_id' => $request->trip_id,
                    'title' => $file->getClientOriginalName(),
                    'description' => null,
                    'file_path' => str_replace('public/', '', $path),
                    'file_name' => $filename,
                    'mime_type' => $mimeType,
                    'file_size' => $file->getSize(),
                    'type' => $type,
                    'location' => $request->location,
                    'is_favorite' => false,
                ]);

                $uploadedMedia[] = $media;
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedMedia) . ' file(s) uploaded successfully',
                'data' => $uploadedMedia
            ], 200);

        } catch (\Exception $e) {
            Log::error('Media upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Media::where('user_id', auth()->id())
                     ->with('trip')
                     ->orderBy('created_at', 'desc');

        if ($request->trip_id) {
            $query->where('trip_id', $request->trip_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->favorites) {
            $query->where('is_favorite', true);
        }

        $media = $query->paginate($request->per_page ?? 50);

        return response()->json($media);
    }

    public function show($id)
    {
        $media = Media::where('user_id', auth()->id())
                     ->where('id', $id)
                     ->with('trip')
                     ->firstOrFail();

        return response()->json($media);
    }

    public function update(Request $request, $id)
    {
        $media = Media::where('user_id', auth()->id())
                     ->where('id', $id)
                     ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_favorite' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $media->update($request->only(['title', 'description', 'location', 'is_favorite']));

        return response()->json([
            'success' => true,
            'message' => 'Media updated successfully',
            'data' => $media
        ]);
    }

    public function destroy($id)
    {
        $media = Media::where('user_id', auth()->id())
                     ->where('id', $id)
                     ->firstOrFail();

        Storage::delete('public/' . $media->file_path);
        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully'
        ]);
    }

    public function toggleFavorite($id)
    {
        $media = Media::where('user_id', auth()->id())
                     ->where('id', $id)
                     ->firstOrFail();

        $media->is_favorite = !$media->is_favorite;
        $media->save();

        return response()->json([
            'success' => true,
            'message' => $media->is_favorite ? 'Added to favorites' : 'Removed from favorites',
            'data' => $media
        ]);
    }

    public function stats()
    {
        $userId = auth()->id();

        $stats = [
            'total' => Media::where('user_id', $userId)->count(),
            'images' => Media::where('user_id', $userId)->where('type', 'image')->count(),
            'videos' => Media::where('user_id', $userId)->where('type', 'video')->count(),
            'favorites' => Media::where('user_id', $userId)->where('is_favorite', true)->count(),
            'total_size' => Media::where('user_id', $userId)->sum('file_size'),
        ];

        return response()->json($stats);
    }
}