<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Load;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loads = Load::all();
        $drivers = Driver::all();
        return response()->json(['loads' => $loads, 'drivers' => $drivers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 'user_id',
        // 'driver_id',
        // 'unit_no',
        // 'bill_id',
        // 'load_type',
        // 'weight',
        // 'destination',
        // 'pickup_location',
        // 'dropoff_location',
        // 'pickup_date',
        // 'delivery_date',
        // 'status',
        // 'total_fare',
        // 'driver_fare',
        // Validation rules, adjust as per your requirements
        $validatedData = $request->validate([
            'driver' => 'required|string',
            'bill_id' => 'required|string',
            'pickup' => 'required|string',
            'drop_off' => 'required|string',
            'pickup_date' => 'required|date',
            'total_fare' => 'required|numeric',
            'driver_fare' => 'required|numeric',
        ]);

        $user_id = Auth::id();

        $driver = Driver::where('id', $validatedData['driver'])->with(['user','profile','hired_by', 'details','vehicles']);

        // Create a new trip using the validated data
        $load = Load::create([
            'user_id' => $user_id,
            'driver_id' => $validatedData['driver'],
            // 'unit_no' => $driver->vehicles->id,
            'bill_id' => $validatedData['bill_id'],
            'pickup_location' => $validatedData['pickup'],
            'dropoff_location' => $validatedData['drop_off'],
            'pickup_date' => $validatedData['pickup_date'],
            'total_fare' => $validatedData['total_fare'],
            'driver_fare' => $validatedData['driver_fare'],
        ]);

        if ($load) {
            // Trip added successfully
            return response()->json(['success' => true, 'message' => 'Load added successfully']);
        } else {
            // Failed to add load
            return response()->json(['success' => false, 'message' => 'Failed to add load'], 500);
        }

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
