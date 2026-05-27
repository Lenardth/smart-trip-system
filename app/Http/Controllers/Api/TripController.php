<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTripRequest;
use App\Http\Requests\Api\UpdateTripRequest;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function __construct(private readonly TripService $trips) {}

    public function index(): JsonResponse
    {
        return response()->json(['trips' => $this->trips->allForUser(Auth::id())]);
    }

    public function upcoming(): JsonResponse
    {
        return response()->json(['trips' => $this->trips->upcomingForUser(Auth::id())]);
    }

    public function store(StoreTripRequest $request): JsonResponse
    {
        $result = $this->trips->createForUser($request->validated(), Auth::id());
        $status = $result['status'];
        unset($result['status']);

        return response()->json($result, $status);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->trips->deleteForUser($id, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Trip deleted successfully.',
        ]);
    }

    public function update(UpdateTripRequest $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'trip' => $this->trips->updateForUser($request->validated(), $id, Auth::id()),
        ]);
    }
}
