<?php

namespace App\Http\Controllers;

use App\Models\CommunityGroup;
use App\Models\CommunityStory;
use App\Models\CommunityTopic;
use App\Models\CommunityReply;
use App\Models\User;
use App\Events\CommunityTopicCreated;
use App\Events\CommunityGroupCreated;
use App\Events\CommunityStoryCreated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommunityController extends Controller
{
    public function index()
    {
        return view('community.index');
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'members' => User::count(),
            'stories' => CommunityStory::count(),
            'groups'  => CommunityGroup::where('status', 'open')->count(),
            'topics'  => CommunityTopic::count(),
        ]);
    }

    public function topics(): JsonResponse
    {
        $topics = CommunityTopic::with('user:id,name,profile_picture')
            ->latest()
            ->take(10)
            ->get();

        $mapped = $topics->map(fn($t) => [
            'id'            => $t->id,
            'title'         => $t->title,
            'body'          => $t->body,
            'author'        => $t->user?->name ?? $t->author,
            'user_id'       => $t->user_id,
            'tags'          => is_string($t->tags) ? (json_decode($t->tags, true) ?? []) : ($t->tags ?? []),
            'replies_count' => $t->replies ?? 0,
            'created_at'    => $t->created_at->diffForHumans(),
        ]);

        return response()->json($mapped);
    }

    public function showTopic($id): JsonResponse
    {
        $topic = CommunityTopic::with('user:id,name,profile_picture')->findOrFail($id);

        $replies = CommunityReply::with('user:id,name,profile_picture')
            ->where('topic_id', $id)
            ->oldest()
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'body'       => $r->body,
                'author'     => $r->user?->name ?? $r->author,
                'user_id'    => $r->user_id,
                'created_at' => $r->created_at->diffForHumans(),
            ]);

        return response()->json([
            'topic' => [
                'id'         => $topic->id,
                'title'      => $topic->title,
                'body'       => $topic->body,
                'author'     => $topic->user?->name ?? $topic->author,
                'user_id'    => $topic->user_id,
                'tags'       => is_string($topic->tags) ? (json_decode($topic->tags, true) ?? []) : ($topic->tags ?? []),
                'created_at' => $topic->created_at->diffForHumans(),
            ],
            'replies' => $replies,
        ]);
    }

    public function storeTopic(Request $request): JsonResponse
    {
        $data = $request->validate([
            'author' => 'nullable|string|max:100',
            'title'  => 'required|string|max:255',
            'tags'   => 'nullable|array',
            'tags.*' => 'string|max:50',
            'body'   => 'nullable|string|max:5000',
        ]);

        $data['tags']    = json_encode($data['tags'] ?? []);
        $data['replies'] = 0;
        $data['user_id'] = Auth::id();

        if (Auth::check() && empty($data['author'])) {
            $data['author'] = Auth::user()->name;
        }

        $topic = CommunityTopic::create($data);

        $this->broadcast(new CommunityTopicCreated($topic));

        return response()->json($topic, 201);
    }

    public function storeReply(Request $request, $topicId): JsonResponse
    {
        $topic = CommunityTopic::findOrFail($topicId);

        $data = $request->validate([
            'author' => 'nullable|string|max:100',
            'body'   => 'required|string|max:5000',
        ]);

        $data['topic_id'] = $topic->id;
        $data['user_id']  = Auth::id();

        if (Auth::check() && empty($data['author'])) {
            $data['author'] = Auth::user()->name;
        }

        $reply = CommunityReply::create($data);
        $topic->increment('replies');

        return response()->json([
            'reply'       => $reply,
            'reply_count' => $topic->fresh()->replies,
        ], 201);
    }

    public function groups(): JsonResponse
    {
        $groups = CommunityGroup::with('user:id,name,profile_picture')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($g) => [
                'id'          => $g->id,
                'name'        => $g->name,
                'destination' => $g->destination,
                'date'        => $g->date,
                'organizer'   => $g->user?->name ?? $g->organizer,
                'user_id'     => $g->user_id,
                'spots_left'  => $g->spots_left,
                'spots_taken' => 0,
                'spots_total' => $g->spots_left,
                'status'      => $g->status,
            ]);

        return response()->json($groups);
    }

    public function storeGroup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organizer'   => 'nullable|string|max:100',
            'name'        => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'date'        => 'required|string|max:50',
            'spots_left'  => 'required|integer|min:1|max:100',
        ]);

        $data['status']  = 'open';
        $data['user_id'] = Auth::id();

        if (Auth::check() && empty($data['organizer'])) {
            $data['organizer'] = Auth::user()->name;
        }

        $group = CommunityGroup::create($data);

        $this->broadcast(new CommunityGroupCreated($group));

        return response()->json($group, 201);
    }

    public function destroyTopic($id): JsonResponse
    {
        $topic = CommunityTopic::findOrFail($id);

        if ($topic->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        CommunityReply::where('topic_id', $id)->delete();
        $topic->delete();

        return response()->json(['success' => true]);
    }

    public function updateTopic(Request $request, $id): JsonResponse
    {
        $topic = CommunityTopic::findOrFail($id);

        if ($topic->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'body'  => 'nullable|string|max:5000',
            'tags'  => 'nullable|array',
            'tags.*'=> 'string|max:50',
        ]);

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        $topic->update($data);

        return response()->json(['success' => true, 'topic' => $topic->fresh()]);
    }

    public function destroyReply($id): JsonResponse
    {
        $reply = CommunityReply::findOrFail($id);

        if ($reply->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $topicId = $reply->topic_id;
        $reply->delete();

        CommunityTopic::where('id', $topicId)->decrement('replies');

        return response()->json(['success' => true]);
    }

    public function destroyGroup($id): JsonResponse
    {
        $group = CommunityGroup::findOrFail($id);

        if ($group->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $group->delete();

        return response()->json(['success' => true]);
    }
    {
        $tags = CommunityTopic::whereNotNull('tags')
            ->pluck('tags')
            ->flatMap(fn($t) => is_string($t) ? (json_decode($t, true) ?? []) : ($t ?? []))
            ->filter()
            ->map(fn($tag) => trim($tag))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(12)
            ->map(fn($count, $name) => ['name' => $name, 'count' => $count])
            ->values();

        return response()->json($tags);
    }

    public function stories(): JsonResponse
    {
        $stories = CommunityStory::with('user:id,name,profile_picture')
            ->latest('published_at')
            ->take(6)
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'title'       => $s->title,
                'excerpt'     => $s->excerpt,
                'image_url'   => $s->image_url,
                'image'       => $s->image_url,
                'author'      => $s->user?->name ?? $s->author,
                'user_id'     => $s->user_id,
                'author_avatar' => $s->user?->avatar,
                'likes'       => $s->likes    ?? 0,
                'comments'    => $s->comments ?? 0,
                'created_at'  => $s->published_at?->diffForHumans() ?? '',
            ]);

        return response()->json($stories);
    }

    public function travelers(): JsonResponse
    {
        $travelers = User::orderBy('created_at')
            ->take(8)
            ->get(['id', 'name', 'profile_picture', 'bio', 'location'])
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'avatar'    => $u->avatar,
                'bio'       => $u->bio       ?? 'Travel enthusiast',
                'location'  => $u->location  ?? null,
                'trips'     => rand(3, 30),
                'countries' => rand(2, 20),
                'posts'     => rand(1, 15),
                'badge'     => collect(['Explorer', 'Adventurer', 'Globetrotter', 'Nomad'])->random(),
            ]);

        return response()->json(['travelers' => $travelers]);
    }

    private function broadcast($event): void
    {
        try {
            if (config('broadcasting.connections.pusher.key')) {
                broadcast($event)->toOthers();
            }
        } catch (\Throwable $e) {
            Log::warning('Broadcast skipped: ' . $e->getMessage());
        }
    }
}
