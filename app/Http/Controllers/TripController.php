<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index()
    {
        // Hanya menampilkan trip milik driver yang login, atau semua trip untuk admin/company
        $user = Auth::user();
        if ($user->role == 'driver') {
            $trips = Trip::where('driver_id', $user->id)->get();
        } elseif ($user->role == 'company') {
            $trips = Trip::whereIn('driver_id', $user->company->users->pluck('id'))->get();
        } else {
            $trips = Trip::all();
        }

        return response()->json($trips);
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_location' => 'string',
        ]);

        $trip = Trip::create([
            'driver_id' => Auth::id(),
            'start_time' => now(),
            'start_location' => $request->start_location,
            'status' => 'active',
        ]);

        return response()->json($trip, 201);
    }

    public function show(Trip $trip)
    {
        // Authorization: hanya driver yang bersangkutan, admin, atau company yang memiliki driver
        $user = Auth::user();
        if ($user->role == 'driver' && $trip->driver_id != $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        if ($user->role == 'company' && !$user->company->users->contains('id', $trip->driver_id)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($trip);
    }

    public function update(Request $request, Trip $trip)
    {
        // Hanya driver yang bersangkutan yang bisa mengupdate (mengakhiri) trip
        if ($trip->driver_id != Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'end_location' => 'string',
            'distance' => 'numeric',
        ]);

        $trip->update([
            'end_time' => now(),
            'end_location' => $request->end_location,
            'distance' => $request->distance,
            'status' => 'completed',
        ]);

        return response()->json($trip);
    }

    public function destroy(Trip $trip)
    {
        // Hanya admin yang bisa menghapus trip
        if (Auth::user()->role != 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $trip->delete();
        return response()->json(null, 204);
    }
}