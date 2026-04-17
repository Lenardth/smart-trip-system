<?php

namespace App\Http\Controllers;

use App\Models\CommunityGroup;
use App\Models\CommunityStory;
use App\Models\CommunityTopic;
use App\Models\CommunityReply;
use App\Models\User;
use App\Events\CommunityTopicCreated;
use App\Events\CommunityGroupCreated;
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
        // Force fresh connection and use raw PDO
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        
        $pdo = DB::connection()->getPdo();
        
        $sql = "SELECT ct.*, u.name as user_name, u.profile_picture as user_avatar 
                FROM community_topics ct 
                LEFT JOIN users u ON ct.user_id = u.id 
                ORDER BY ct.created_at DESC 
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $topics = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $mapped = array_map(function($t) {
            return [
                'id'            => $t['id'],
                'title'         => $t['title'],
                'body'          => $t['body'],
                'author'        => $t['user_name'] ?? $t['author'] ?? 'Anonymous',
                'author_avatar' => $t['user_avatar'] ?? null,
                'user_id'       => $t['user_id'],
                'tags'          => is_string($t['tags']) ? (json_decode($t['tags'], true) ?? []) : ($t['tags'] ?? []),
                'replies_count' => $t['replies'] ?? 0,
                'likes'         => $t['likes'] ?? 0,
                'created_at'    => \Carbon\Carbon::parse($t['created_at'])->diffForHumans(),
            ];
        }, $topics);

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
                'author'     => $r->user?->name ?? $r->author ?? 'Anonymous',
                'user_id'    => $r->user_id,
                'created_at' => $r->created_at->diffForHumans(),
            ]);

        return response()->json([
            'topic' => [
                'id'         => $topic->id,
                'title'      => $topic->title,
                'body'       => $topic->body,
                'author'     => $topic->user?->name ?? $topic->author ?? 'Anonymous',
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
                'organizer'   => $g->user?->name ?? $g->organizer ?? 'Anonymous',
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

    public function likeTopic($id): JsonResponse
    {
        $userId = Auth::id();
        $topic = CommunityTopic::findOrFail($id);

        $like = \App\Models\CommunityLike::where('user_id', $userId)
            ->where('likeable_type', CommunityTopic::class)
            ->where('likeable_id', $id)
            ->first();

        if ($like) {
            // Unlike
            $like->delete();
            $topic->decrement('likes');
            $liked = false;
        } else {
            // Like
            \App\Models\CommunityLike::create([
                'user_id' => $userId,
                'likeable_type' => CommunityTopic::class,
                'likeable_id' => $id,
            ]);
            $topic->increment('likes');
            $liked = true;
        }

        return response()->json([
            'likes' => $topic->fresh()->likes,
            'liked' => $liked,
        ]);
    }

    public function likeStory($id): JsonResponse
    {
        $userId = Auth::id();
        $story = CommunityStory::findOrFail($id);

        $like = \App\Models\CommunityLike::where('user_id', $userId)
            ->where('likeable_type', CommunityStory::class)
            ->where('likeable_id', $id)
            ->first();

        if ($like) {
            // Unlike
            $like->delete();
            $story->decrement('likes');
            $liked = false;
        } else {
            // Like
            \App\Models\CommunityLike::create([
                'user_id' => $userId,
                'likeable_type' => CommunityStory::class,
                'likeable_id' => $id,
            ]);
            $story->increment('likes');
            $liked = true;
        }

        return response()->json([
            'likes' => $story->fresh()->likes,
            'liked' => $liked,
        ]);
    }
    public function destroyTopic($id): JsonResponse
    {
        $topic = CommunityTopic::findOrFail($id);

        if ($topic->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Delete all replies first
        CommunityReply::where('topic_id', $id)->delete();

        // Delete the topic
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
                'author'      => $s->user?->name ?? $s->author ?? 'Anonymous',
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

    public function storyComments($storyId): JsonResponse
    {
        $comments = \App\Models\CommunityStoryComment::with('user:id,name,profile_picture')
            ->where('story_id', $storyId)
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'body'       => $c->body,
                'author'     => $c->user?->name ?? $c->author ?? 'Anonymous',
                'user_id'    => $c->user_id,
                'avatar'     => $c->user?->avatar ?? null,
                'created_at' => $c->created_at->diffForHumans(),
            ]);

        return response()->json(['comments' => $comments]);
    }

    public function storeStoryComment(Request $request, $storyId): JsonResponse
    {
        $story = CommunityStory::findOrFail($storyId);

        $data = $request->validate([
            'author' => 'nullable|string|max:100',
            'body'   => 'required|string|max:2000',
        ]);

        $data['story_id'] = $story->id;
        $data['user_id']  = Auth::id();

        if (Auth::check() && empty($data['author'])) {
            $data['author'] = Auth::user()->name;
        }

        $comment = \App\Models\CommunityStoryComment::create($data);
        $story->increment('comments');

        return response()->json([
            'comment' => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'author'     => $comment->user?->name ?? $comment->author ?? 'Anonymous',
                'user_id'    => $comment->user_id,
                'avatar'     => $comment->user?->avatar ?? null,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
            'comment_count' => $story->fresh()->comments,
        ], 201);
    }

    public function joinGroup($groupId): JsonResponse
    {
        $group = CommunityGroup::findOrFail($groupId);
        $userId = Auth::id();

        // Check if already a member
        $existing = \App\Models\CommunityGroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this group.',
            ], 422);
        }

        // Check if group is full
        $memberCount = \App\Models\CommunityGroupMember::where('group_id', $groupId)
            ->where('status', 'accepted')
            ->count();

        if ($memberCount >= $group->spots_left) {
            return response()->json([
                'success' => false,
                'message' => 'This group is full.',
            ], 422);
        }

        // Add member
        \App\Models\CommunityGroupMember::create([
            'group_id' => $groupId,
            'user_id'  => $userId,
            'status'   => 'accepted',
        ]);

        // Update spots left
        $group->decrement('spots_left');

        return response()->json([
            'success'    => true,
            'message'    => 'Successfully joined the group!',
            'spots_left' => $group->fresh()->spots_left,
        ]);
    }

    public function leaveGroup($groupId): JsonResponse
    {
        $userId = Auth::id();

        $member = \App\Models\CommunityGroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this group.',
            ], 422);
        }

        $member->delete();

        $group = CommunityGroup::findOrFail($groupId);
        $group->increment('spots_left');

        return response()->json([
            'success'    => true,
            'message'    => 'Successfully left the group.',
            'spots_left' => $group->fresh()->spots_left,
        ]);
    }

    public function groupMembers($groupId): JsonResponse
    {
        $members = \App\Models\CommunityGroupMember::with('user:id,name,profile_picture')
            ->where('group_id', $groupId)
            ->where('status', 'accepted')
            ->get()
            ->map(fn($m) => [
                'id'     => $m->user->id,
                'name'   => $m->user->name,
                'avatar' => $m->user->avatar,
                'joined' => $m->created_at->diffForHumans(),
            ]);

        return response()->json(['members' => $members]);
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
