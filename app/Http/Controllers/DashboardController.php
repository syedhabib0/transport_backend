<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Load;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function getStats()
    {
        $totalLoads = Load::count();
        $completeLoads = Load::where('status', 'delivered')->count();
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
    
    public function dashboardMobile()
    {
        try {
            $user_id = getCurrentUser()->id;

            $driver = Driver::where('user_id', $user_id)->first();

            if ($driver) {
                $driver_id = $driver->id;

                $activeOrder = Load::where('driver_id', $driver_id)->where('status', 'active')->count();
                $deliveredOrder = Load::where('driver_id', $driver_id)->where('status', 'delivered')->count();
                $cancelledOrder = Load::where('driver_id', $driver_id)->where('status', 'cancelled')->count();

                $driverEarningToday = Load::where('driver_id', $driver_id)
                    ->whereDate('created_at', today())
                    ->whereNotIn('status', ['cancelled'])
                    ->sum('driver_fare');

                $driverEarningWeekly = Load::where('driver_id', $driver_id)
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->whereNotIn('status', ['cancelled'])
                    ->sum('driver_fare');

                $driverEarningMonthly = Load::where('driver_id', $driver_id)
                    ->whereMonth('created_at', today())
                    ->whereNotIn('status', ['cancelled'])
                    ->sum('driver_fare');

                $driverTotalEarning = Load::where('driver_id', $driver_id)
                ->whereNotIn('status', ['cancelled'])
                ->sum('driver_fare');

                $activeOrders = Load::where('driver_id', $driver_id)
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->take(2)
                    ->get();

                return successResponse('data found', [
                    'activeOrderCount' => $activeOrder,
                    'deliveredOrderCount' => $deliveredOrder,
                    'cancelledOrderCount' => $cancelledOrder,
                    'driverEarningToday' => round($driverEarningToday, 0),
                    'driverEarningWeekly' => round($driverEarningWeekly, 0),
                    'driverEarningMonthly' => round($driverEarningMonthly, 0),
                    'driverTotalEarning' => round($driverTotalEarning, 0),
                    'activeOrders' => $activeOrders
                ]);
            }
            return errorResponse('Dasboard data not found', 404);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
