<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DispatcherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'first_name' => 'Adam',
                'last_name' => 'Owais',
                'email' => 'adam@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'Andrea',
                'last_name' => 'Williams',
                'email' => 'andrea@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'HR',
                'last_name' => 'Resource',
                'email' => 'hr@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'Jack',
                'last_name' => 'Jones',
                'email' => 'jack@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'Samuel',
                'last_name' => 'Bagg',
                'email' => 'samuel@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
        ];
        $role = Role::where(['name' => 'dispatcher'])->first();
        foreach ($data as $dispatcher) {
           $dispatcherUser = User::create($dispatcher);
           $dispatcherUser->assignRole($role);
        }
    }
}
