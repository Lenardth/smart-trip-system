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
        $topics = CommunityTopic::latest()
            ->take(10)
            ->get(['id', 'author', 'title', 'tags', 'replies', 'created_at']);

        $topics->transform(fn($t) => [
            ...$t->toArray(),
            'tags' => is_string($t->tags) ? (json_decode($t->tags, true) ?? []) : ($t->tags ?? []),
        ]);

        return response()->json($topics);
    }

    public function showTopic($id): JsonResponse
    {
        $topic = CommunityTopic::findOrFail($id);

        $replies = CommunityReply::where('topic_id', $id)
            ->oldest()
            ->get(['id', 'author', 'body', 'created_at']);

        return response()->json([
            'topic' => [
                ...$topic->toArray(),
                'tags' => is_string($topic->tags)
                    ? (json_decode($topic->tags, true) ?? [])
                    : ($topic->tags ?? []),
            ],
            'replies' => $replies,
        ]);
    }

    public function storeTopic(Request $request): JsonResponse
    {
        $data = $request->validate([
            'author' => 'required|string|max:100',
            'title'  => 'required|string|max:255',
            'tags'   => 'nullable|array',
            'tags.*' => 'string|max:50',
            'body'   => 'nullable|string|max:5000',
        ]);

        $data['tags']    = json_encode($data['tags'] ?? []);
        $data['replies'] = 0;

        $topic = CommunityTopic::create($data);

        $this->broadcast(new CommunityTopicCreated($topic));

        return response()->json($topic, 201);
    }

    public function storeReply(Request $request, $topicId): JsonResponse
    {
        $topic = CommunityTopic::findOrFail($topicId);

        $data = $request->validate([
            'author' => 'required|string|max:100',
            'body'   => 'required|string|max:5000',
        ]);

        $data['topic_id'] = $topic->id;
        $reply = CommunityReply::create($data);
        $topic->increment('replies');

        return response()->json([
            'reply'       => $reply,
            'reply_count' => $topic->fresh()->replies,
        ], 201);
    }

    public function groups(): JsonResponse
    {
        $groups = CommunityGroup::latest()
            ->take(6)
            ->get(['id', 'organizer', 'name', 'destination', 'date', 'spots_left', 'status']);

        return response()->json($groups);
    }

    public function storeGroup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organizer'   => 'required|string|max:100',
            'name'        => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'date'        => 'required|string|max:50',
            'spots_left'  => 'required|integer|min:1|max:100',
        ]);

        $data['status'] = 'open';
        $group = CommunityGroup::create($data);

        $this->broadcast(new CommunityGroupCreated($group));

        return response()->json($group, 201);
    }

    public function tags(): JsonResponse
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
        $stories = CommunityStory::latest('published_at')
            ->take(6)
            ->get(['id', 'author', 'title', 'excerpt', 'image_url', 'likes', 'comments', 'published_at']);

        return response()->json($stories);
    }

    public function travelers(): JsonResponse
    {
        $travelers = User::orderBy('created_at')
            ->take(4)
            ->get(['id', 'name'])
            ->map(fn($u) => [
                'name'      => $u->name,
                'bio'       => 'Travel enthusiast',
                'trips'     => rand(3, 30),
                'countries' => rand(2, 20),
                'posts'     => rand(1, 15),
                'badge'     => collect(['Explorer','Adventurer','Globetrotter','Nomad'])->random(),
            ]);

        return response()->json($travelers);
    }

    /* ── Helper ── */

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
