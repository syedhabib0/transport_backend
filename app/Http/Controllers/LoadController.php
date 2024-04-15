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
            $loads = $this->filterLoads($request);

            return response()->json($loads);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching loads.'], 500);
        }
    }

    public function filterLoads(Request $request)
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 10);

        return Load::with([
            'driver.profile',
            'driver.user'
        ])->when($filters['bill_id'], function ($query) use ($filters) {
            $query->where('bill_id', $filters['bill_id']);
        })
        ->when($filters['driver_id'], function ($query) use ($filters) {
            $query->where('driver_id', $filters['driver_id']);
        })
        ->when($filters['pickup_date'], function ($query) use ($filters) {
            $query->where('pickup_date', $filters['pickup_date']);
        })
        ->when($filters['created_at'], function ($query) use ($filters) {
            $query->whereDate('created_at', $filters['created_at']);
        })
        ->when($filters['total_fare'], function ($query) use ($filters) {
            $query->where('total_fare', '<' ,$filters['total_fare']);
        })
        ->when($filters['driver_fare'], function ($query) use ($filters) {
            $query->where('driver_fare', '<' , $filters['driver_fare']);
        })
        ->when($filters['load_status'], function ($query) use ($filters) {
            $query->where('status', $filters['load_status']);
        })->paginate($perPage);
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
            'delivery_date' => 'nullable|date',
            'total_fare' => 'required|numeric',
            'driver_fare' => 'required|numeric',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'drop_off_latitude' => 'required',
            'drop_off_longitude' => 'required',
            'pickup_time' => 'required',
            'dropoff_time' => 'required'
        ]);

        $loadExist = Load::where('driver_id', $validatedData['driver'])->whereIn('status', ['active', 'on-going'])->get();

        if ($loadExist->isNotEmpty()) {
            return errorResponse('The load is already assigned to the driver');
        }

        $user_id = Auth::id();

        $driver = Driver::where('id', $validatedData['driver'])->with(['user','profile','details','vehicles'])->first();

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
            'pickup_time' => $validatedData['pickup_time'],
            'dropoff_time' => $validatedData['dropoff_time'],
            'delivery_date' => $validatedData['delivery_date'],
        ]);

        $load->pickUpLocation()->updateOrCreate(['load_id' => $load->id], [
            'latitude' => $validatedData['pickup_latitude'],
            'longitude' => $validatedData['pickup_longitude'],
        ]);

        $load->dropOffLocation()->updateOrCreate(['load_id' => $load->id], [
            'latitude' => $validatedData['drop_off_latitude'],
            'longitude' => $validatedData['drop_off_longitude'],
        ]);

        $load->currentLocation()->updateOrCreate(['load_id' => $load->id], [
            'latitude' => $validatedData['pickup_latitude'],
            'longitude' => $validatedData['pickup_longitude'],
        ]);

        if ($load) {
            if (!is_null($driver->user->fcm_token)) {
                sendPushNotification('New Order', 'You have recieved a new order from iws', $driver->user->fcm_token);
            }
            return successResponse('Load added successfully', $load);
        } else {
            // Failed to add load
            return response()->json(['success' => false, 'message' => 'Failed to add load'], 500);
        }

    }

    public function getOnGoingLoads(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
    
            $loads = Load::with([
                'pickUpLocation',
                'dropOffLocation',
                'currentLocation',
                'driver.profile',
                'driver.user'
                ])
            ->where('status', 'on-going')
            ->paginate($perPage);
    
            return response()->json($loads);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching ongoing loads.'], 500);
        }    
    }

    public function show($id)
    {
        try {
            $load = Load::with([
                'pickUpLocation',
                'dropOffLocation',
                'currentLocation',
                'driver.profile',
                'driver.user',
                'driver.licenseDetails' => function ($query) {
                    $query->select(['driver_id', 'license_number']);
                },
                'driver.hiredBy' => function ($query) {
                    $query->select(['id', 'first_name', 'last_name']);
                },
                'driver.vehicles' => function ($query) {
                    $query->latest()->first();
                }
            ])
            ->where('id', $id)
            ->first();
    
            return successResponse('load fetched successfully', $load);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching ongoing loads.'], 500);
        }   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function newOrders()
    {
        try {
            $user_id = getCurrentUser()->id;
            $driver = Driver::where('user_id', $user_id)->first();
            $newOrders = Load::with(['pickUpLocation', 'dropOffLocation'])
            ->where('driver_id', $driver->id)->where('status', 'available')->get();
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
            $activeOrders = Load::with(['pickUpLocation', 'dropOffLocation'])
            ->where('driver_id', $driver->id)->where('status', 'active')->get();
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
            $order = Load::where('id', $orderId)->with('driver')->first();
            $order->status = ($request->accepted == 1) ? 'active' : 'cancelled';
            $order->booking_confirmed_at = ($request->accepted == 1) ? now() : null;
            $order->save();
            $order->driver->status = ($request->accepted == 1) ? 'not-available' : 'available';
            $order->driver->save();
            return successResponse('Order status has been updated', $order);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function startTrip($orderId)
    {
        try {
            $order = Load::where('id', $orderId)->with('driver')->first();
            if ($order->status == 'active') {
                $order->status = 'on-going';
                $order->start_trip_at = now();
                $order->save();
                return successResponse('Trip has been started', $order);
            } else {
                return errorResponse('The order is not active');
            }
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function sendTrackLocation(Request $request, $orderId)
    {
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required'
        ]);
        try {
            $order = Load::where('id', $orderId)->with('driver')->first();
            if ($order->status == 'on-going') {
                $order->currentLocation()->updateOrCreate(['load_id' => $order->id], [
                    'latitude' => $validatedData['latitude'],
                    'longitude' => $validatedData['longitude'],
                ]);
                return successResponse('Trip location has been updated', $order);
            } else {
                return errorResponse('The order status is not on-going');
            }
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function orderDelivered($orderId)
    {
        try {
            $order = Load::where('id', $orderId)->with('driver')->first();
            if ($order->status == 'on-going') {
                $order->status = 'delivered';
                $order->end_trip_at = now();
                $order->save();
                $order->driver->status = 'available';
                $order->driver->save();
                return successResponse('Trip has been completed', $order);
            } else {
                return errorResponse('The order is not on-going');
            }
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getStatuses(Request $request)
    {
        try {
            return successResponse('Load Statuses fetched successfully', getLoadStatus());
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching ongoing loads.'], 500);
        }
    }

}
