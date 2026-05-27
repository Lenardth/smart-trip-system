<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlightListingRequest;
use App\Models\FlightListing;
use App\Services\AgencyFlightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AgencyFlightController extends Controller
{
    public function __construct(private readonly AgencyFlightService $agencyFlights) {}

    public function index(): View
    {
        $this->authorizeAgency();

        return view('agency.flights.index', $this->agencyFlights->dashboardData(Auth::user()));
    }

    public function store(StoreFlightListingRequest $request): RedirectResponse
    {
        $this->agencyFlights->create(Auth::user(), $request->validated());

        return back()->with('success', 'Flight listing saved.');
    }

    public function publish(FlightListing $flight): RedirectResponse
    {
        $this->authorizeAgencyListing($flight);
        $flight->update(['status' => 'published']);

        return back()->with('success', 'Flight published.');
    }

    public function archive(FlightListing $flight): RedirectResponse
    {
        $this->authorizeAgencyListing($flight);
        $flight->update(['status' => 'archived']);

        return back()->with('success', 'Flight archived.');
    }

    public function incomingBookings(): View
    {
        $this->authorizeAgency();

        return view('agency.bookings.index', [
            'bookings' => $this->agencyFlights->incomingBookings(Auth::user()),
        ]);
    }

    private function authorizeAgency(): void
    {
        abort_unless(Auth::user()?->user_type === 'agency', 403);
    }

    private function authorizeAgencyListing(FlightListing $flight): void
    {
        $this->authorizeAgency();
        abort_unless($flight->agency_id === Auth::id(), 403);
    }
}
