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
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
    
            // Check if the 'completed' query parameter is present
            $isCompleted = $request->has('completed');
    
            // Eager load the relationships using "with"
            $loadsQuery = Load::with('driver.profile', 'driver.user');
    
            // If 'completed' is true, filter only completed loads
            if ($isCompleted) {
                $loadsQuery->where('status', 'completed');
            }
    
            // Paginate the results
            $loads = $loadsQuery->paginate($perPage);
    
            return response()->json([
                'loads' => $loads->items(),
                'current_page' => $loads->currentPage(),
                'last_page' => $loads->lastPage(),
            ]);
        } catch (\Exception $e) {
            // Handle the exception, log it, or return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

    public function filterLoads(Request $request)
    {
        // Retrieve the filter parameters from the request
        $filters = $request->all();

        // Apply filters to the query
        $loads = Load::with('driver.profile', 'driver.user')
        ->when($filters['loadId_filter'], function ($query) use ($filters) {
            $query->where('id', $filters['loadId_filter']);
        })
        ->when($filters['billId_filter'], function ($query) use ($filters) {
            $query->where('bill_id', $filters['billId_filter']);
        })
        ->when($filters['driver_filter'], function ($query) use ($filters) {
            $query->where('driver_id', $filters['driver_filter']);
        })
        ->when($filters['pickupDate_filter'], function ($query) use ($filters) {
            $query->where('pickup_date', $filters['pickupDate_filter']);
        })
        ->when($filters['dropOff_filter'], function ($query) use ($filters) {
            $query->where('dropoff_location', $filters['dropOff_filter']);
        })
        ->when($filters['createdDate_filter'], function ($query) use ($filters) {
            $query->where('created_at', $filters['createdDate_filter']);
        })
        ->when($filters['totalFare_filter'], function ($query) use ($filters) {
            $query->where('total_fare', $filters['totalFare_filter']);
        })
        ->when($filters['driverFare_filter'], function ($query) use ($filters) {
            $query->where('driver_fare', $filters['driverFare_filter']);
        })
        ->when($filters['loadStatus_filter'], function ($query) use ($filters) {
            $query->where('status', $filters['loadStatus_filter']);
        });
        // Add more filters as needed

        // Execute the query
        $filteredLoads = $loads->get();

        return response()->json(['loads' => $filteredLoads]);
    }

    public function newOrders()
    {
        try {
            $user_id = getCurrentUser()->id;
            $driver = Driver::where('user_id', $user_id)->first();
            $newOrders = Load::where('driver_id', $driver->id)->where('status', 'available')->get();
            if($newOrders->isNotEmpty()){
                return successResponse('New Orders found successfully', $newOrders);
            }
            return errorResponse('New Orders not found', 404);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function activeOrders()
    {
        try {
            $user_id = getCurrentUser()->id;
            $driver = Driver::where('user_id', $user_id)->first();
            $activeOrders = Load::where('driver_id', $driver->id)->where('status', 'active')->get();
            if($activeOrders->isNotEmpty()){
                return successResponse('Active Orders found successfully', $activeOrders);
            }
            return errorResponse('Active Orders not found', 404);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function acceptOrder(Request $request, $orderId)
    {
        $request->validate([
            'accepted' => 'required|boolean'
        ]);

        try {
            $order = Load::where('id', $orderId)->update(['status'=> ($request->accepted == 1) ? 'active' : 'cancelled']);
            return successResponse('Order status has been updated', $order);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

}
