<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

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

        // Create and return a new Driver model instance
        return new Driver([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'profile_picture' => $validatedData['profile_picture'],
            'email' => $validatedData['email'],
            'phone_number' => $validatedData['phone_number'],
        ]);
    }

    // Additional validation and transformation logic can be added as needed
    private function validateAndTransformData(array $data)
    {
        // Implement your validation logic here
        // For example, you can use Laravel's validation functions

        $profilePictureName = $this->generateProfilePictureName($data['Username'], $data['Profile Picture']);
        
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
