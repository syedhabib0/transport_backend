<?php

namespace App\Imports;

use App\Models\Profile;
use App\Models\User;
use App\Notifications\DriverImportNotification;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DriversImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Validate and transform the data as needed before inserting into the database
        $validatedData = $this->validateAndTransformData($row);

        $password = Str::random(8);

        // Create and return a new Driver model instance
        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($password), // Auto-generate password
            'profile_picture' => $validatedData['profile_picture'],
            'phone_number' => $validatedData['phone_number'],
        ]);

        // Create a new profile associated with the user
        $profile = new Profile([
            'phone' => $row['Phone Number'],
            // Add other profile fields as needed
        ]);

        $user->profile()->save($profile);

        // Send password reset notification
        $user->notify(new DriverImportNotification($user, $password));

    }

    // Additional validation and transformation logic can be added as needed
    private function validateAndTransformData(array $data)
    {
        // Implement your validation logic here
        // For example, you can use Laravel's validation functions

        $profilePictureName = $this->generateProfilePictureName($data['First Name'], $data['Profile Picture']);
        
        $validatedData = [
            'first_name' => $data['First Name'],
            'last_name' => $data['Last Name'],
            'profile_picture' => $profilePictureName,
            'email' => $data['Email'],
            'phone_number' => $data['Phone Number'],
        ];

        // You can perform additional validation or data transformation here

        return $validatedData;
    }

    private function generateProfilePictureName($username, $originalName)
    {
        // Use the username, user ID, and original name to generate a unique name
        $userId = auth()->id(); // Assuming you have a user authentication system
        $defaultName = pathinfo($originalName, PATHINFO_FILENAME);

        return "{$username}_{$userId}_{$defaultName}." . pathinfo($originalName, PATHINFO_EXTENSION);
    }
}
