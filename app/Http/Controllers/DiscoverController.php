<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\DiscoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function __construct(private readonly DiscoverService $discover) {}

    public function index()
    {
        return $this->discover->index();
    }

    public function list(Request $request): JsonResponse
    {
        return $this->discover->list($request);
    }

    public function show(Destination $destination)
    {
        return $this->discover->show($destination);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->discover->search($request);
    }
}
