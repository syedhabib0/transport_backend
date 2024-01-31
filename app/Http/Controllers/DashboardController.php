<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Load;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function getStats()
    {
        $totalLoads = Load::count();
        $completeLoads = Load::where('status', 'complete')->count();
        $totalDrivers = Driver::count(); // Assuming you have a Driver model
        $activeDrivers = Driver::where('status', 'available')->count();

        $driverRole = Role::where('name', 'driver')->first();
        $drivers = $driverRole->users()->with('profile', 'driver')->get();

        // Calculate total earnings
        $totalEarnings = Load::sum('total_fare');

        // Calculate driver earnings separately
        $driverEarnings = Load::whereNotNull('driver_id')->sum('driver_fare');

        return response()->json([
            'totalLoads' => $totalLoads,
            'drivers' => $drivers,
            'completeLoads' => $completeLoads,
            'totalDrivers' => $totalDrivers,
            'activeDrivers' => $activeDrivers,
            'totalEarnings' => $totalEarnings,
            'driverEarnings' => $driverEarnings,
        ]);
    }
}
