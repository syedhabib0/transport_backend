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
                'first_name' => 'Aaron',
                'last_name' => 'Walker',
                'email' => 'aaron@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
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
                'first_name' => 'Gabe',
                'last_name' => 'Silva',
                'email' => 'gabe@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'Harley',
                'last_name' => 'Bates',
                'email' => 'harley@iwscarriers.com',
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
                'first_name' => 'James',
                'last_name' => 'Ford',
                'email' => 'james@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'Jimbo',
                'last_name' => 'Jackson',
                'email' => 'jimbo@iwscarriers.com',
                'password' => Hash::make('password'),
                'is_admin' => 0,  
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Waters',
                'email' => 'mike@iwscarriers.com',
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
