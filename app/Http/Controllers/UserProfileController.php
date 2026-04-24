<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CommunityStory;
use App\Models\CommunityTopic;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('community.profile', compact('user'));
    }

    public function showFeed()
    {
        return view('community.feed');
    }

    public function showMembers()
    {
        return view('community.members');
    }

    public function getMembers(): JsonResponse
    {
        $members = User::with(['followers', 'following'])
            ->get()
            ->map(fn($user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'avatar'     => $user->avatar ?: null,
                'bio'        => $user->bio ?? 'Travel enthusiast exploring the world',
                'location'   => $user->location ?? 'Unknown',
                'joined'     => $user->created_at->format('M Y'),
                'posts'      => CommunityStory::where('user_id', $user->id)->count() + 
                               CommunityTopic::where('user_id', $user->id)->count(),
                'followers'  => $user->followers()->count(),
                'following'  => $user->following()->count(),
                'badge'      => $this->getUserBadge($user),
            ]);

        return response()->json(['members' => $members]);
    }

    private function getUserBadge($user)
    {
        $postCount = CommunityStory::where('user_id', $user->id)->count() + 
                    CommunityTopic::where('user_id', $user->id)->count();
        
        if ($postCount >= 50) return 'Influencer';
        if ($postCount >= 20) return 'Active Member';
        if ($postCount >= 5) return 'Contributor';
        return null;
    }

    public function getProfile($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $authUserId = Auth::id();

        // Get user's stories/vlogs
        $stories = CommunityStory::where('user_id', $id)
            ->withCount('likes', 'comments')
            ->latest('published_at')
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'title'         => $s->title,
                'excerpt'       => $s->excerpt,
                'image_url'     => $s->image_url,
                'media_type'    => $s->media_type ?? 'image',
                'video_url'     => $s->video_url,
                'thumbnail_url' => $s->thumbnail_url,
                'duration'      => $s->duration,
                'likes'         => $s->likes_count ?? $s->likes ?? 0,
                'comments'      => $s->comments_count ?? $s->comments ?? 0,
                'views'         => $s->views ?? 0,
                'is_liked'      => Auth::check() ? $s->isLikedBy($authUserId) : false,
                'created_at'    => $s->published_at?->diffForHumans() ?? '',
            ]);

        // Get user's forum topics
        $topics = CommunityTopic::where('user_id', $id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($t) => [
                'id'            => $t->id,
                'title'         => $t->title,
                'body'          => $t->body,
                'tags'          => is_string($t->tags) ? (json_decode($t->tags, true) ?? []) : ($t->tags ?? []),
                'replies_count' => $t->replies ?? 0,
                'likes'         => $t->likes ?? 0,
                'created_at'    => $t->created_at->diffForHumans(),
            ]);

        // Get user's trips
        $trips = Trip::where('user_id', $id)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($trip) => [
                'id'          => $trip->id,
                'destination' => $trip->destination,
                'start_date'  => $trip->start_date?->format('M d, Y'),
                'end_date'    => $trip->end_date?->format('M d, Y'),
                'status'      => $trip->status,
            ]);

        // Calculate stats
        $stats = [
            'stories'   => CommunityStory::where('user_id', $id)->count(),
            'topics'    => CommunityTopic::where('user_id', $id)->count(),
            'trips'     => Trip::where('user_id', $id)->count(),
            'likes'     => CommunityStory::where('user_id', $id)->sum('likes') + 
                          CommunityTopic::where('user_id', $id)->sum('likes'),
            'followers' => $user->followers()->count(),
            'following' => $user->following()->count(),
        ];

        $isFollowing = $authUserId ? $user->isFollowedBy($authUserId) : false;
        $isOwnProfile = $authUserId === $user->id;

        return response()->json([
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'avatar'          => $user->avatar ?: null,
                'bio'             => $user->bio ?? 'Travel enthusiast exploring the world',
                'location'        => $user->location ?? 'Unknown',
                'created_at'      => $user->created_at->format('M Y'),
                'is_following'    => $isFollowing,
                'is_own_profile'  => $isOwnProfile,
            ],
            'stats'   => $stats,
            'stories' => $stories,
            'topics'  => $topics,
            'trips'   => $trips,
        ]);
    }

    public function follow($id): JsonResponse
    {
        $authUserId = Auth::id();
        
        if (!$authUserId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($authUserId == $id) {
            return response()->json(['message' => 'You cannot follow yourself'], 422);
        }

        $user = User::findOrFail($id);
        $authUser = Auth::user();

        if ($authUser->isFollowing($id)) {
            // Unfollow
            $authUser->following()->detach($id);
            $isFollowing = false;
            $message = 'Unfollowed';
        } else {
            // Follow
            $authUser->following()->attach($id);
            $isFollowing = true;
            $message = 'Following';
        }

        return response()->json([
            'is_following' => $isFollowing,
            'message' => $message,
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function feed(): JsonResponse
    {
        $authUserId = Auth::id();
        
        if (!$authUserId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $authUser = Auth::user();
        $followingIds = $authUser->following()->pluck('users.id')->toArray();
        $followingIds[] = $authUserId; // Include own posts

        // Get stories from followed users
        $stories = CommunityStory::whereIn('user_id', $followingIds)
            ->with('user:id,name,profile_picture')
            ->withCount('likes', 'comments')
            ->latest('published_at')
            ->take(20)
            ->get()
            ->map(fn($s) => [
                'id'            => $s->id,
                'title'         => $s->title,
                'excerpt'       => $s->excerpt,
                'image_url'     => $s->image_url,
                'media_type'    => $s->media_type ?? 'image',
                'video_url'     => $s->video_url,
                'thumbnail_url' => $s->thumbnail_url,
                'duration'      => $s->duration,
                'author'        => $s->user?->name ?? $s->author ?? 'Anonymous',
                'user_id'       => $s->user_id,
                'author_avatar' => $s->user?->avatar ?: null,
                'likes'         => $s->likes_count ?? $s->likes ?? 0,
                'comments'      => $s->comments_count ?? $s->comments ?? 0,
                'views'         => $s->views ?? 0,
                'is_liked'      => $s->isLikedBy($authUserId),
                'created_at'    => $s->published_at?->diffForHumans() ?? '',
            ]);

        return response()->json(['stories' => $stories]);
    }
}
