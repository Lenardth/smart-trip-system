<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use App\Models\MemoryFrame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MemoryController extends Controller
{
    public function index()
    {
        $memories = Memory::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('memories.index', compact('memories'));
    }

    public function create()
    {
        return view('memories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:photo,video',
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:10240', // 10MB max
            'is_public' => 'boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('memories/' . Auth::id(), 'public');

        $memory = Memory::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'file_path' => $path,
            'is_public' => $request->is_public ?? false,
        ]);

        if ($request->type === 'photo') {
            $this->createThumbnail($memory, $file);
        }

        return redirect()->route('memories.show', $memory)
            ->with('success', 'Memory saved successfully!');
    }

    public function show(Memory $memory)
    {
        if ($memory->user_id !== Auth::id() && !$memory->is_public) {
            abort(403, 'This memory is private.');
        }

        $frames = $memory->frames()->get();

        return view('memories.show', compact('memory', 'frames'));
    }

    public function addFrame(Request $request, Memory $memory)
    {
        $request->validate([
            'frame_type' => 'required|in:polaroid,modern,vintage',
        ]);

        $frame = MemoryFrame::create([
            'memory_id' => $memory->id,
            'user_id' => Auth::id(),
            'frame_type' => $request->frame_type,
            'frame_settings' => $request->settings ?? [],
        ]);

        return response()->json([
            'success' => true,
            'frame' => $frame,
            'config' => $frame->getFrameConfig(),
        ]);
    }

    public function destroy(Memory $memory)
    {
        if ($memory->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        Storage::disk('public')->delete($memory->file_path);
        if ($memory->thumbnail_path) {
            Storage::disk('public')->delete($memory->thumbnail_path);
        }

        $memory->delete();

        return redirect()->route('memories.index')
            ->with('success', 'Memory deleted successfully!');
    }

    private function createThumbnail($memory, $file): void
    {
        $thumbnailPath = 'memories/' . Auth::id() . '/thumbnails/' . $file->hashName();

        Storage::disk('public')->makeDirectory(dirname($thumbnailPath));
        Storage::disk('public')->copy($memory->file_path, $thumbnailPath);

        $memory->update(['thumbnail_path' => $thumbnailPath]);
    }
}