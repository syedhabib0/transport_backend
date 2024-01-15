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
        // dd($request);
        // Validate the request
        // $request->validate([
        //     'first_name' => 'required|string|max:255',
        //     'last_name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255',
        //     // 'phone' => 'required|number',
        // ]);

        // Create or update database record
        // $userData = User::create(
        //     'first_name' => $request->input('first_name'),
        //     'last_name' => $request->input('last_name'),
        //     'email' => $request->input('email'),
        //     // ['phone' => $request->input('phone')],
        // );

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

        // Create a new profile associated with the user
        $profile = new Profile([
            'phone' => $request->phone_number,
            // Add other profile fields as needed
        ]);

        $user->profile()->save($profile);


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

    // public function uploadBulk(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,csv|max:100000', // 100 MB
    //     ]);

    //     $file = $request->file('file');

    //     // Validate column names
    //     $import = new DriversImport(); // Replace with your import class
    //     $headings = $import->toArray($file)->toArray()[0][0];

    //     $requiredColumns = ['First Name', 'Last Name', 'Email', 'Phone Number'];
    //     foreach ($requiredColumns as $column) {
    //         if (!in_array($column, $headings)) {
    //             throw new ValidationException("Column '$column' is required.");
    //         }
    //     }

    //     // Process the file using Maatwebsite\Excel
    //     Excel::import($import, $file);

    //     return response()->json(['message' => 'File uploaded and processed successfully.']);
    // }

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

    private function validateAndTransformData(array $data)
    {
        // Your validation and transformation logic
        $username = $data["First Name"] . '_' . $data["Last Name"]; 
        $profilePictureName = $this->generateProfilePictureName($username, $data['Profile Picture']);

        $validatedData = [
            'first_name' => $data['First Name'],
            'last_name' => $data['Last Name'],
            'profile_picture' => $profilePictureName,
            'email' => $data['Email'],
            'phone_number' => $data['Phone Number'],
        ];

        // Additional validation or data transformation if needed

        return $validatedData;
    }

    private function generateProfilePictureName($username, $originalName)
    {
        // Your logic to generate a unique profile picture name
        $timestamp = now()->timestamp;
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        return "{$username}_{$timestamp}.{$extension}";
    }
}
