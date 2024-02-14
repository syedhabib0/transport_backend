<?php

namespace App\Http\Controllers;

use App\Models\DriverLocation;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Store the driver's location in the database
        $location = new DriverLocation([
            'driver_id' => $request->input('driver_id'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);
        $location->save();

        return response()->json(['message' => 'Location updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
