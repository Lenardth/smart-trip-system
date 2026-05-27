<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FlightController extends Controller
{
    public function index(): View
    {
        return view('flights.index');
    }
}
