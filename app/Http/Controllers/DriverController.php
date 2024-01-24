<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DriversImport; // Replace with your import class
use App\Exceptions\ValidationException;
use App\Models\Driver;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\DriverImportNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DriverController extends Controller
{
    public function index(Request $request){
        // Retrieve the "driver" role
        $driverRole = Role::where('name', 'driver')->first();


        // Check if the role exists
        if ($driverRole) {
            $perPage = $request->get('per_page', 10); // You can adjust the default number of items per page
            // Retrieve all users with the "driver" role
            $drivers = $driverRole->users()->with('profile', 'driver')->paginate($perPage);
            // print_r($drivers);

            // You can now use $drivers as a collection of users with the "driver" role
            return response()->json([
                'drivers' => $drivers->items(),
                'current_page' => $drivers->currentPage(),
                'last_page' => $drivers->lastPage(),
            ]);
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
            // 'phone_number' => ['required', 'number', 'max:255'],
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
            // 'profile_photo' => $request->profile_picture,
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


        if($user){

            // Send password reset notification
            $user->notify(new DriverImportNotification($user, $password));

            // redirect('/drivers');
            return response()->json(['message' => 'Driver created successfully']);
        }else{
            // redirect('dashboard');
            return response()->json(['message' => 'Some error occured on Server and Driver is not created. Please contact Administrator.']);
        }

    }

    public function show($id)
    {
        // $driver = User::findOrFail($id);

        // Retrieve the driver user with related data
        $driver = User::with(['profile', 'driver'])
            ->where('id', $id)
            ->firstOrFail();

        $hired_user = $driver->driver->hired_by;

        $hired_by = User::where('id', $hired_user)->first();
        

        return response()->json(['driver' => $driver, 'hired_by' => $hired_by]);
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
        // dd($request->all());
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:102400', // Validate the file input
        ]);

        try {
            // Import data from the uploaded file
            $import = new DriversImport();
            $importedData = Excel::toArray($import, $request->file('file'));

            // print_r($importedData[0]);

            // Process each row of data
            foreach ($importedData[0] as $row) {
                // Your validation and transformation logic
                // $validatedData = $this->validateAndTransformData($row);

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
                
                //     return response()->json(['message' => 'Driver created successfully', 'user' => $user]);
                // } else {
                //     return response()->json(['error' => 'Some error occurred on the server. Driver not created.', 'data' => $row], 500);
                }
            }

            return response()->json(['message' => 'Bulk upload successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Bulk upload failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateGeneralInformation(Request $request, $id)
    {
        // $request->validate([
        //     'first_name' => 'required|string',
        //     'last_name' => 'required|string',
        //     'email' => 'required|string|email',
        //     'profile_picture' => 'required|string',
        //     'dob' => 'required|string',
        //     'hired_by' => 'required|string',
        //     'status' => 'required|string',
        //     'phone' => 'required|string',
        //     'note' => 'string',
        // ]);

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
            'status' => 'required|in:active,inactive', // Adjust options as needed
            'note' => 'nullable',
        ]);
        // error_log($request);

        // Handle profile picture upload if provided
        // if ($request->hasFile('profile_picture')) {
        //     $path = $request->file('profile_picture')->store('profile_pictures');
        //     $request->merge(['profile_picture' => $path]);
        // }
        if ($request->hasFile('profile_picture')) {
            $user = User::where('id', $id)->first();
            $profile = Profile::where('user_id', $id)->first();
            $driver = Driver::where('user_id', $id)->first();
            $profilePicture = $request->file('profile_picture');
            // $user_id = auth()->user()->id; // Adjust this based on how you get the user ID
    
            // Generate a unique file name
            // $fileName = "{$user_id}_photo_" . time() . '.' . $profilePicture->getClientOriginalExtension();
    
            // Save the file in the specified directory
            // $profilePicture->storeAs("public/assets/images/profiles/{$user_id}_{$request['first_name']}_{$request['last_name']}", $fileName);
            $old_image = $user->profile->profile_photo;
            // Retrieve the uploaded file
            $image = request()->file('profile_picture');
    
            // Generate a unique name for the image
            $imageName = "{$id}_photo_" . time() . '.' . $image->getClientOriginalExtension();
    
            $folderPath = "assets/images/users/{$id}_{$request['first_name']}_{$request['last_name']}";
            // Define the storage path
            // $storagePath = public_path();
    
            // Store the image in the specified folder
            $status = $image->move(public_path($folderPath), $imageName);
            $fullPath = $folderPath.'/'.$imageName;
    
            // $user->update([
            //     'photo' => $imageName
            // ]);
    
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
            
            if( $old_image && !empty($old_image) && file_exists($folderPath.DIRECTORY_SEPARATOR.$old_image)){
                unlink($folderPath.DIRECTORY_SEPARATOR.$old_image);
            }
    
            return response()->json(['message' => 'Driver Data updated successfully', 'driver' => $user]);
        }

    

    }
    
    public function updateDocuments(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            // Add other validation rules for the fields you want to update
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

    public function updateTrucks(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            // Add other validation rules for the fields you want to update
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

    public function updateReminders(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            // Add other validation rules for the fields you want to update
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




    // private function validateAndTransformData(array $data)
    // {
    //     // Your validation and transformation logic
    //     $username = $data["First Name"] . '_' . $data["Last Name"]; 
    //     $profilePictureName = $this->generateProfilePictureName($username, $data['Profile Picture']);

    //     $validatedData = [
    //         'first_name' => $data['First Name'],
    //         'last_name' => $data['Last Name'],
    //         'profile_picture' => $profilePictureName,
    //         'email' => $data['Email'],
    //         'phone_number' => $data['Phone Number'],
    //     ];

    //     // Additional validation or data transformation if needed

    //     return $validatedData;
    // }

    // private function generateProfilePictureName($username, $originalName)
    // {
    //     // Your logic to generate a unique profile picture name
    //     $timestamp = now()->timestamp;
    //     $extension = pathinfo($originalName, PATHINFO_EXTENSION);

    //     return "{$username}_{$timestamp}.{$extension}";
    // }
}
