<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Load;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats()
    {
        $totalLoads = Load::count();
        $completeLoads = Load::where('status', 'complete')->count();
        $totalDrivers = Driver::count(); // Assuming you have a Driver model
        $activeDrivers = Driver::where('status', 'active')->count();

        // Calculate total earnings
        $totalEarnings = Load::sum('driver_earnings');

        // Calculate driver earnings separately
        $driverEarnings = Load::whereNotNull('driver_id')->sum('driver_earnings');

        return response()->json([
            'totalLoads' => $totalLoads,
            'completeLoads' => $completeLoads,
            'totalDrivers' => $totalDrivers,
            'activeDrivers' => $activeDrivers,
            'totalEarnings' => $totalEarnings,
            'driverEarnings' => $driverEarnings,
        ]);
    }
}
