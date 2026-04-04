<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(): JsonResponse
    {
        $media = Media::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'type'       => $m->type,
                'url'        => $m->url,
                'title'      => $m->title ?? $m->file_name,
                'file_name'  => $m->file_name,
                'is_favorite'=> $m->is_favorite,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['media' => $media]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'media'   => ['required', 'array'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi', 'max:51200'],
        ]);

        $uploaded = [];

        foreach ($request->file('media', []) as $file) {
            $mime = $file->getMimeType();
            $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $path = $file->store('media/' . Auth::id(), 'public');

            $media = Media::create([
                'user_id'   => Auth::id(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'file_size' => $file->getSize(),
                'type'      => $type,
                'title'     => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ]);

            $uploaded[] = [
                'id'        => $media->id,
                'type'      => $media->type,
                'url'       => $media->url,
                'file_name' => $media->file_name,
            ];
        }

        return response()->json(['success' => true, 'uploaded' => $uploaded], 201);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        $items = Media::where('user_id', Auth::id())
            ->whereIn('id', $request->ids)
            ->get();

        foreach ($items as $item) {
            Storage::disk('public')->delete($item->file_path);
            $item->delete();
        }

        return response()->json(['success' => true]);
    }

    public function toggleFavorite(Media $media): JsonResponse
    {
        if ($media->user_id !== Auth::id()) abort(403);
        $media->update(['is_favorite' => !$media->is_favorite]);
        return response()->json(['success' => true, 'is_favorite' => $media->is_favorite]);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        if ($media->user_id !== Auth::id()) abort(403);
        $data = $request->validate(['title' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string']]);
        $media->update($data);
        return response()->json(['success' => true]);
    }

    public function stats(): JsonResponse
    {
        $userId = Auth::id();
        return response()->json([
            'photos' => Media::where('user_id', $userId)->where('type', 'image')->count(),
            'videos' => Media::where('user_id', $userId)->where('type', 'video')->count(),
            'total'  => Media::where('user_id', $userId)->count(),
        ]);
    }
}
