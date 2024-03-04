<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create demo users
        $user1 = \App\Models\User::factory()->create([
            // 'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'is_admin' => 1,
        ]);

        $user2 = \App\Models\User::factory()->create([
            // 'name' => 'Dispatcher User',
            'first_name' => 'Dispatcher',
            'last_name' => 'User',
            'email' => 'dispatcher@example.com',
            'password' => Hash::make('test3333'),
            'is_admin' => 0,
        ]);

        $user3 = \App\Models\User::factory()->create([
            // 'name' => 'Driver User',
            'first_name' => 'Driver',
            'last_name' => 'User',
            'email' => 'driver@example.com',
            'password' => Hash::make('test3333'),
            'is_admin' => 0,
        ]);

        $profile = new Profile([
            'phone' => '00923459586145',
        ]);
        $user3->profile()->save($profile);

        // Create a new profile associated with the user
        $driver = new Driver([
            'profile_id' => $profile->id,
            'hired_by' => 1,
        ]);

        $user3->driver()->save($driver);

        $role1 = Role::create(['name' => 'admin']);

        $role2 = Role::create(['name' => 'dispatcher']);

        $role3 = Role::create(['name' => 'driver']);

        $user1->assignRole($role1);

        $user2->assignRole($role2);

        $user3->assignRole($role3);
    }
}
