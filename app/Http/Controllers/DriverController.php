<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DriversImport; // Replace with your import class
use App\Exceptions\ValidationException;
use App\Models\User;

class DriverController extends Controller
{
    public function create(Request $request)
    {
        dd($request);
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
        ]);

        $userData = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => '',
        ]);


        if($userData){
            // redirect('/drivers');
            return response()->json(['message' => 'Driver created successfully']);
        }else{
            // redirect('dashboard');
            return response()->json(['message' => 'Some error occured on Server and Driver is not created. Please contact Administrator.']);
        }

    }

    public function uploadBulk(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:100000', // 100 MB
        ]);

        $file = $request->file('file');

        // Validate column names
        $import = new DriversImport(); // Replace with your import class
        $headings = $import->toArray($file)->toArray()[0][0];

        $requiredColumns = ['First Name', 'Last Name', 'Email', 'Phone Number'];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headings)) {
                throw new ValidationException("Column '$column' is required.");
            }
        }

        // Process the file using Maatwebsite\Excel
        Excel::import($import, $file);

        return response()->json(['message' => 'File uploaded and processed successfully.']);
    }
}
