<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DriversImport;
use App\Models\Driver;
use App\Models\DriverLocation;
use App\Models\Profile;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\DriverImportNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;

class DriverController extends Controller
{
    protected $driver;

    public function __construct(
        Driver $driver
    )
    {
        $this->driver = $driver;
    }

    public function index(Request $request){
        $driverRole = Role::where('name', 'driver')->first();

        if ($driverRole) {
            $query = $driverRole->users()->with('profile', 'driver');

            if ($request->has('name')) {
                $query->where(function($query) use ($request) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%']);
                });
            }

            if ($request->has('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->has('status')) {
                $query->whereHas('driver', function ($query) use ($request) {
                    $query->where('status', $request->status);
                });
            }

            $perPage = $request->get('per_page', 10);
            $drivers = $query->paginate($perPage);

            $data = [
                'drivers' => $drivers->items(),
                'current_page' => $drivers->currentPage(),
                'last_page' => $drivers->lastPage(),
            ];
            return successResponse('Drivers fetched successfully', $data);
        } else {
            return response()->json(['error' => 'Role not found'], 404);
        }
    }

    public function create(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        ]);

        $password = Str::random(8);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password), // Auto-generate password
        ]);
        
        // Assign the "driver" role to the user
        $driverRole = Role::where('name', 'driver')->first();
        $user->assignRole($driverRole);
        
        $current_user_id = Auth::id();

        // Create a new profile associated with the user
        $profile = new Profile([
            'phone' => $request->phone_number,
        ]);
        $user->profile()->save($profile);

        // Create a new profile associated with the user
        $driver = new Driver([
            'profile_id' => $profile->id,
            'hired_by' => $current_user_id,
        ]);

        $user->driver()->save($driver);


        if($user){
            Mail::to($request->email)->send(new DriverImportNotification($user, $password));
            return successResponse('Driver created successfully', $user);
        }else{
            return response()->json(['message' => 'Some error occured on Server and Driver is not created. Please contact Administrator.']);
        }

    }

    public function show($id)
    {
        // Retrieve the driver user with related data
        $user = User::where('id', $id)->first();

        $driverData = Driver::where('user_id', $id)->with(['insuranceDetails', 'licenseDetails'])->first();

        $profileData = Profile::where('user_id', $id)->first();
        $vehicleData = Vehicle::where('driver_id', $driverData->id)->with('licenseDetails', 'vehicleImages', 'otherDetails')->first();

        $hired_user = $driverData->hired_by;

        $hired_by = User::where('id', $hired_user)->first();
        

        return response()->json(['user' => $user, 'driverData' => $driverData, 'profileData' => $profileData, 'hired_by' => $hired_by, 'vehicleData' => $vehicleData]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            // Add other validation rules for the fields you want to update
        ]);

        $profile = Profile::where('user_id', $id);
        $driver = Driver::where('user_id', $id);

        // Update the driver with the new data
        $driver->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            // Update other fields as needed
        ]);

        return response()->json(['message' => 'Driver updated successfully', 'driver' => $driver]);
    }

    public function uploadBulk(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:102400', // Validate the file input
        ]);

        try {
            // Import data from the uploaded file
            $import = new DriversImport();
            $importedData = Excel::toArray($import, $request->file('file'));


            // Process each row of data
            foreach ($importedData[0] as $row) {
                // Your validation and transformation logic

                $password = Str::random(8);

                $current_user_id = Auth::id();

                // Create a new user
                $user = User::create([
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'password' => Hash::make($password), // Auto-generate password
                ]);

                // Assign the "driver" role to the user
                $driverRole = Role::where('name', 'driver')->first();
                $user->assignRole($driverRole);

                // Create a new profile associated with the user
                $profile = new Profile([
                    'phone' => $row['phone_number'],
                    // Add other profile fields as needed
                ]);

                $user->profile()->save($profile);

                // Create a new profile associated with the user
                $driver = new Driver([
                    'profile_id' => $profile->id,
                    'hired_by' => $current_user_id,
                    // Add other profile fields as needed
                ]);

                $user->driver()->save($driver);


                if ($user) {
                    // Send password reset notification
                    $user->notify(new DriverImportNotification($user, $password));
                
                }
            }

            return response()->json(['message' => 'Bulk upload successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Bulk upload failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateGeneralInformation(Request $request, $id)
    {

        $user = User::where('id', $id)->with('profile', 'driver')->first();
        
        // Validate the request data
        $request->validate([
            // 'user_id' => 'required|exists:users,id',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif',
            'dob' => 'required|date',
            'phone' => 'required',
            'hired_by' => 'nullable',
            'status' => 'required|in:available,not-available,will-be-available,under-our-load,under-our-bid,suspended',
            'note' => 'nullable',
        ]);

        if ($request->hasFile('profile_picture')) {
            $user = User::where('id', $id)->first();
            $profile = Profile::where('user_id', $id)->first();
            $driver = Driver::where('user_id', $id)->first();
            $profilePicture = $request->file('profile_picture');
            
            $old_image = $user->profile->profile_photo;
            // Retrieve the uploaded file
            $image = request()->file('profile_picture');
    
            // Generate a unique name for the image
            $imageName = "{$id}_photo_" . time() . '.' . $image->getClientOriginalExtension();
    
            $folderPath = "images/users/{$id}_{$request['first_name']}_{$request['last_name']}";
            if (!file_exists("storage/" . $folderPath)) {
                mkdir('storage/' . $folderPath, 0777, true);
            }
            
            // Store the image in the specified folder
            $status = $image->move(public_path('storage/' . $folderPath), $imageName);
            $fullPath = $folderPath.'/'.$imageName;
    
            if($status){    
                $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                ]);
        
                $profile->update([
                    'phone' => $request->phone,
                    'dob' => $request->dob,
                    'profile_photo' => $fullPath,
                ]);
                
                if($request->note){
                    $driver->update([
                        'status' => $request->status,
                        'note' => $request->note,
                    ]);
                }else{
                    $driver->update([
                        'status' => $request->status,
                    ]);
                }
            }
            
            if( $old_image && !empty($old_image) && file_exists('storage/' . $folderPath.DIRECTORY_SEPARATOR.$old_image)){
                unlink('storage/' . $folderPath.DIRECTORY_SEPARATOR.$old_image);
            }
    
            return response()->json(['message' => 'Driver Data updated successfully', 'driver' => $user]);
        }

    

    }
    
    public function updateDocuments(Request $request, $id)
    {
        $request->validate([
            'license_number' => 'required',
            'license_expiry_date' => 'required',
            'license_issuance_country' => 'required',
            'license_issuance_state' => 'required',
            'license_photo_front' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'license_photo_back' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'insurance_number' => 'required',
            'insurance_expiry_date' => 'required',
            'insurance_photo_front' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'insurance_photo_back' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Add other validation rules for the fields you want to update
        ]);

       
        
        
        $user = User::where('id', $id)->with('profile', 'driver')->first();
        $userId = $user->id;
        
        $driver = Driver::where('user_id', $userId)->with('insuranceDetails', 'licenseDetails')->first();

        // Retrieve old image names from the database
        // $oldImages = $this->getOldImages($userId);

        $userDirectory = "driver_details/{$userId}_{$user->first_name}_{$user->last_name}";
        if (!file_exists("storage/" . $userDirectory)) {
            mkdir("storage/" . $userDirectory, 0777, true);
        }

        $licenseFields = ['license_photo_front', 'license_photo_back'];
        $insuranceFields = ['insurance_photo_front', 'insurance_photo_back'];

        
        $this->handleDriverImageUpload($driver, $request, $licenseFields, $userDirectory, 'licenseDetails');
        $this->handleDriverImageUpload($driver, $request, $insuranceFields, $userDirectory, 'insuranceDetails');
        
        $insurance = $driver->insuranceDetails()->updateOrCreate(
            ['driver_id' => $driver->id],
            [
                'insurance_number' => $request['insurance_number'],
                'insurance_expiry_date' => $request['insurance_expiry_date'],
            ]
        );

        $license = $driver->licenseDetails()->updateOrCreate(
            ['driver_id' => $driver->id],
            [
                'license_number' => $request['license_number'],
                'license_expiry_date' => $request['license_expiry_date'],
                'license_issuance_country' => $request['license_issuance_country'],
                'license_issuance_state' => $request['license_issuance_state'],
            ]
        );


        if($license && $insurance){
            return response()->json(['message' => 'Driver updated successfully', 'driver' => $driver]);

        }

        return response()->json(['message' => 'Driver updated successfully', 'driver' => $driver]);
    }

    private function handleDriverImageUpload($driver, $request, $fieldNames, $folderPath, $relationName)
    {
        $relation = $driver->{$relationName}; // Get the insuranceDetails or licenseDetails relation
        // Check if the relation is null, and if so, create it
        if (!$relation) {
            $relation = $driver->{$relationName}()->create([]);
        }
        // Convert single field name to an array for consistency
        $fieldNames = is_array($fieldNames) ? $fieldNames : [$fieldNames];
        foreach ($fieldNames as $fieldName) {
        if ($request->hasFile($fieldName)) {
            
            $oldImage = $relation->{$fieldName} ?? '';
    
            $image = $request->file($fieldName);
    
            // Generate a unique name for the image
            $imageName = "{$driver->id}_{$fieldName}_" . time() . '.' . $image->getClientOriginalExtension();
    
            // Store the image in the specified folder
            $status = $image->move(public_path('storage/' . $folderPath), $imageName);
            $imagePath = $folderPath . '/' . $imageName;
    
            if ($status) {
                $relation->updateOrCreate(
                    ['driver_id' => $driver->id],
                    [$fieldName => $imagePath],
                );
    
                // Delete the old image if it exists
                if ($oldImage && !empty($oldImage) && file_exists('storage/' . $folderPath . DIRECTORY_SEPARATOR . $oldImage)) {
                    unlink('storage/' . $folderPath . DIRECTORY_SEPARATOR . $oldImage);
                }
            }}
        }
    }

    private function handleVehicleImageUpload($vehicle, $request, $fieldNames, $folderPath, $relationName)
    {
        $relation = $vehicle->{$relationName};
        
        // Check if the relation is null, and if so, create it
        if (!$relation) {
            $relation = $vehicle->{$relationName}()->create([]);
        }
        
        // Convert single field name to an array for consistency
        $fieldNames = is_array($fieldNames) ? $fieldNames : [$fieldNames];

        
        foreach ($fieldNames as $fieldName) {
            if ($request->hasFile($fieldName)) {
                
                $relation = $vehicle->{$relationName};
                if (!$relation) {
                    $relation = $vehicle->{$relationName}()->create([]);
                }
                $oldImage = $relation->{$fieldName};
                $image = $request->file($fieldName);
                
                // Generate a unique name for the image
                $imageName = "{$vehicle->id}_vehicles_{$fieldName}_" . time() . '.' . $image->getClientOriginalExtension();
    
                // Store the image in the specified folder
                $status = $image->move(public_path('storage/' . $folderPath), $imageName);
                $imagePath = $folderPath . '/' . $imageName;
    
                if ($status) {
                    // Update the relation's details
                    $relation->updateOrCreate(
                        ['vehicle_id' => $vehicle->id], // Make sure to provide the correct foreign key
                        [$fieldName => $imagePath]
                    );
    
                    // Delete the old image if it exists
                    if ($oldImage && !empty($oldImage) && file_exists('storage/' . $folderPath . DIRECTORY_SEPARATOR . $oldImage)) {
                        unlink('storage/' . $folderPath . DIRECTORY_SEPARATOR . $oldImage);
                    }
                }
            }
        }
    }

    public function createTrucks(Request $request, $id)
    {
        $request->validate([
            'vehicle_type' => 'required|string',
            'unit_number' => 'required|string',
            'make' => 'required|string',
            'model' => 'required|string',
            'payload_weight' => 'required|string',
            'length' => 'required|string',
            'width' => 'required|string',
            'height' => 'required|string',
            'dimension_in' => 'required|string',
            'is_available' => 'required|string',
            'lift_gate' => 'nullable|string',
            'hazmat' => 'nullable|string',
            'icc_bar' => 'nullable|string',
            'tsa' => 'nullable|string',
            'twic' => 'nullable|string',
            'pallet_jack' => 'nullable|string',
            'true_dock_high' => 'nullable|string',
            'tanker_endorsement' => 'nullable|string',
            'license_plate_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'state' => 'required|string',
            'license_expiry' => 'required|string',
            'is_expirable' => 'nullable|string',
            'front_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'back_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'left_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'right_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cargo_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);
        
        $user = User::where('id', $id)->with('profile', 'driver')->first();
        $userId = $user->id;

        $driver = Driver::where('user_id', $userId)->with('vehicles')->first();

        
        // Create a new vehicle
        $vehicle = $this->createVehicle($driver, $request, $user);

        return response()->json(['message' => 'Truck created successfully', 'driver' => $driver, 'vehicles' => $vehicle]);
    }

    public function updateTrucks(Request $request, $id, $vehicleId)
    {
        $request->validate([
            'vehicle_type' => 'nullable|string',
            'unit_number' => 'nullable|string',
            'make' => 'nullable|string',
            'model' => 'nullable|string',
            'payload_weight' => 'nullable|string',
            'length' => 'nullable|string',
            'width' => 'nullable|string',
            'height' => 'nullable|string',
            'dimension_in' => 'nullable|string',
            'is_available' => 'nullable|string',
            'lift_gate' => 'nullable|string',
            'hazmat' => 'nullable|string',
            'icc_bar' => 'nullable|string',
            'tsa' => 'nullable|string',
            'twic' => 'nullable|string',
            'pallet_jack' => 'nullable|string',
            'true_dock_high' => 'nullable|string',
            'tanker_endorsement' => 'nullable|string',
            'license_plate_image' => request()->hasFile('license_plate_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
            'state' => 'nullable|string',
            'license_expiry' => 'nullable|string',
            'is_expirable' => 'nullable|string',
            'front_image' => request()->hasFile('front_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
            'back_image' => request()->hasFile('back_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
            'left_image' => request()->hasFile('left_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
            'right_image' => request()->hasFile('right_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
            'cargo_image' => request()->hasFile('cargo_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable', 
        ]);

        $user = User::where('id', $id)->with('profile', 'driver')->first();
        $userId = $user->id;

        $driver = Driver::where('user_id', $userId)->with('vehicles')->first();

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }

        // Retrieve the specific vehicle by ID
        $vehicle = $driver->vehicles()->find($vehicleId);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        // Create a new vehicle
        $updatedVehicle = $this->createVehicle($driver, $request, $user, $vehicle);

        return response()->json(['message' => 'Truck Data updated successfully', 'driver' => $driver, 'vehicles' => $updatedVehicle]);
    }

    private function createVehicle($driver, $request, $user, $vehicle = null)
    {
        if ($vehicle) {
            // If the vehicle exists, update its details
            $vehicle->update([
                'vehicle_type' => $request->input('vehicle_type'),
                'unit_number' => $request->input('unit_number'),
                'make' => $request->input('make'),
                'model' => $request->input('model'),
                'payload_weight' => $request->input('payload_weight'),
            ]);
    
            // Update additional details and handle images
            $latestVehicle = $this->updateVehicleDetails($vehicle, $request, $user);
    
            return $latestVehicle;
        } else {
            $createVehicle = $driver->vehicles()->create([
                'driver_id' => $driver->id,
                'vehicle_type' => $request->input('vehicle_type'),
                'unit_number' => $request->input('unit_number'),
                'make' => $request->input('make'),
                'model' => $request->input('model'),
                'payload_weight' => $request->input('payload_weight'),
            ]);

            if ($createVehicle) {
                $latestVehicle = $this->updateVehicleDetails($createVehicle, $request, $user);
                return $latestVehicle;
            }else{
                return "Can't update Vehicle. Try Again!";
            }
        }
    }
    private function updateVehicleDetails($vehicle, $request, $user)
    {
        // Your logic for updating license details and other details
        $userDirectory = "driver_vehicle_images/{$user->id}_{$user->first_name}_{$user->last_name}";
        if (!file_exists("storage/" . $userDirectory)) {
            mkdir("storage/" . $userDirectory, 0777, true);
        }
        
        $fieldNames = ['front_image', 'back_image', 'left_image', 'right_image', 'cargo_image'];
        
        $this->handleVehicleImageUpload($vehicle, $request, $fieldNames, $userDirectory, 'vehicleImages');
        
        $vehicle->licenseDetails()->updateOrCreate(
            ['vehicle_id' => $vehicle->id],
            [
            'license_plate_state' => $request->input('state'),
            'license_plate_expiry' => $request->input('license_expiry'),
            'is_expirable' => $request->input('is_expirable') ? 1 : 0,
        ]);

        $this->handleVehicleImageUpload($vehicle, $request, 'license_plate_image', $userDirectory, 'licenseDetails');


        $vehicle->otherDetails()->updateOrCreate(
            ['vehicle_id' => $vehicle->id],
            [
            'length' => $request->input('length'),
            'height' => $request->input('height'),
            'width' => $request->input('width'),
            'dimension_in' => $request->input('dimension_in'),
            'is_available' => $request->input('is_available'),
            'lift_gate' => $request->input('lift_gate') ? 1 : 0,
            'hazmat' => $request->input('hazmat') ? 1 : 0,
            'icc_bar' => $request->input('icc_bar') ? 1 : 0,
            'tsa' => $request->input('tsa') ? 1 : 0,
            'twic' => $request->input('twic') ? 1 : 0,
            'pallet_jack' => $request->input('pallet_jack') ? 1 : 0,
            'true_dock_high' => $request->input('true_dock_high') ? 1 : 0,
            'tanker_endorsement' => $request->input('tanker_endorsement') ? 1 : 0,
        ]);

        $latestVehicle = Vehicle::where('driver_id', $user->driver->id)->with('licenseDetails', 'vehicleImages', 'otherDetails')->get();

        return $latestVehicle;
    }

    public function getVehicles(Request $request, $id){
        
        $driver = Driver::where('user_id', $id)->first();

        $vehicle = Vehicle::where('driver_id', $driver->id)->with('licenseDetails', 'vehicleImages', 'otherDetails')->get();
        return response()->json([
            'drivers' => $driver,
            'vehicles' => $vehicle,
        ]);
    }
    public function showVehicle(Request $request, $id, $vehicleId){
        
        $driver = Driver::where('user_id', $id)->first();

        $vehicle = Vehicle::where('id', $vehicleId)->with('licenseDetails', 'vehicleImages', 'otherDetails')->first();
        return response()->json([
            'driver' => $driver,
            'vehicle' => $vehicle,
        ]);
    }




    public function updateReminders(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ]);

        $driver = Driver::findOrFail($id);

        // Update the driver with the new data
        $driver->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            // Update other fields as needed
        ]);

        return response()->json(['message' => 'Driver updated successfully', 'driver' => $driver]);
    }

    public function updateStatus(Request $request, Driver $driver)
    {
        // \Log::info('Controller method executed successfully.');

        // print_r($request);
        $request->validate([
            'status' => 'required|in:available,not available,will be available,under our load,under our bid,suspended',
        ]);

        $driver->status = $request->input('status');
        $driver->save();

        return response()->json(['message' => 'Driver status updated successfully']);
    }

    public function searchDrivers(Request $request)
    {
        // Retrieve the filter parameters from the request
        $filters = $request->all();

        // Apply filters to the query
        $drivers = User::with(['driver', 'profile'])
        ->whereHas('roles', function ($q) {
            $q->where('name', 'driver');
        })
        ->when($filters['namePhoneEmail'], function ($query) use ($filters) {
            $query->where(function ($subquery) use ($filters) {
                $subquery->where('first_name', 'like', "%{$filters['namePhoneEmail']}%")
                    ->orWhere('last_name', 'like', "%{$filters['namePhoneEmail']}%")
                    ->orWhere('email', 'like', "%{$filters['namePhoneEmail']}%")
                    ->orWhereHas('profile', function ($q) use ($filters) {
                        $q->where('phone', 'like', "%{$filters['namePhoneEmail']}%");
                    });
            });
        })
        ->when($filters['status'], function ($query) use ($filters) {
            $query->whereHas('driver', function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        })
        ->when($filters['unitNumber'], function ($query) use ($filters) {
                $query->where('id', $filters['unitNumber']);
        });

        // Execute the query
        $filteredDrivers = $drivers->get();

        return response()->json(['drivers' => $filteredDrivers]);
    }

    public function savelocation(Request $request)
    {
        $request->validate([
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        try {
            $driver = $this->driver->where('user_id', getCurrentUser()->id)->first();
            if($driver){
                $driver->location = $request->location;
                $driver->save();
                $driver->location()->updateOrCreate(['driver_id' => $driver->id],$request->only(['latitude', 'longitude']));
                return successResponse('Driver location saved successfully', $driver);
            }
            return errorResponse('Driver not found', 404);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }

    }

    public function updateDriverTruckDetail(Request $request)
    {
        $request->validate([
            'vehicle_type' => 'nullable|in:sprinter-vans,box-trucks,reefers,hazmat,straight-trucks,dry-van,flatbed,conestoga',
            'unit_number' => 'nullable|string',
            'license_plate_image' => request()->hasFile('license_plate_image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',
            'license_expiry' => 'nullable|date_format:Y-m-d',
        ]);

        $currentUser = getCurrentUser();
        $user = User::where('id', $currentUser->id)->first();

        $driver = Driver::where('user_id', $currentUser->id)->first();

        if (!$driver) {
            return errorResponse('Driver not found', 404);
        }

        $vehicle = $driver->vehicles()->latest('created_at')->first();

        if (!$vehicle) {
            return errorResponse('Vehicle not found', 404);
        }
        
        $vehicle->update([
            'vehicle_type' => $request->input('vehicle_type'),
            'unit_number' => $request->input('unit_number')
        ]);
        
        $vehicle->licenseDetails()->updateOrCreate(
            ['vehicle_id' => $vehicle->id],
            [
            'license_plate_expiry' => $request->input('license_expiry')
        ]);
        
        $userDirectory = "driver_vehicle_images/{$user->id}_{$user->first_name}_{$user->last_name}";
        if (!file_exists("storage/" . $userDirectory)) {
            mkdir("storage/" . $userDirectory, 0777, true);
        }

        $this->handleVehicleImageUpload($vehicle, $request, 'license_plate_image', $userDirectory, 'licenseDetails');

        return successResponse('Truck Data updated successfully', $vehicle);
    }
    

    public function searchDriverLocations(Request $request)
    {
        $request->validate([
            'longitude' => 'required',
            'latitude' => 'required',
            'radius' => 'required',
        ]);
        try {
            $searchLatitude = $request->latitude;
            $searchLongitude = $request->longitude;
            $radiusMiles = $request->radius;
            $driverLocationsQuery = DriverLocation::select('driver_id', 'latitude', 'longitude')
            ->with(['driver.user'])
            ->whereHas('driver', function ($query) use ($request) {
                if (!empty($request['driver_status'])) {
                    $query->whereIn('status', $request['driver_status']);
                }
            })
            ->whereHas('driver.vehicles', function ($query) use ($request) {
                if (isset($request['truck_type']) && $request['truck_type'] != 'all') {
                    $query->where('vehicle_type', $request['truck_type']);
                }
            });

            // Check if any of the vehicle details are present in the request
            $vehicleDetails = array_intersect_key($request->only(['lift_gate', 'hazmat', 'icc_bar', 'tsa', 'twic', 'pallet_jack', 'true_dock_high', 'tanker_endorsement']), array_flip(['lift_gate', 'hazmat', 'icc_bar', 'tsa', 'twic', 'pallet_jack', 'true_dock_high', 'tanker_endorsement']));
            
            // If any vehicle detail is present, apply the where clause
            if (!empty($vehicleDetails)) {
                $driverLocationsQuery->whereHas('driver.vehicles.otherDetails', function ($query) use ($vehicleDetails) {
                    $query->where($vehicleDetails);
                });
            }

            $driverLocations = $driverLocationsQuery->get();

            $distances = [];
            foreach ($driverLocations as $location) {
                $distance = $this->calculateDistance(
                    $searchLatitude,
                    $searchLongitude,
                    $location->latitude,
                    $location->longitude
                );
                if ($distance <= $radiusMiles) {
                    $distances[] = $location;
                }
            }

            if(!empty($distances)){
                return successResponse('Drivers found successfully', $distances);
            }
            return successResponse('Drivers not found');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), $e->getCode());
        }

    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 3959;
        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        return $distance;
    }

    public function getDriverStatus()
    {
        return successResponse('Driver Statuses fetched successfully', getDriverStatus());
    }

    public function getDriverDropdown()
    {
        try {
            $records = Driver::with('user')->get();

            $data = $records->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'full_name' => $driver->user->first_name . ' ' . $driver->user->last_name
                ];
            });
            return successResponse('Driver Dropdown listing', $data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching loads.'], 500);
        }
    }
}
