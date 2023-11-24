<?php

namespace Database\Seeders;

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

        // create permissions
        // Permission::create(['name' => 'edit articles']);
        // Permission::create(['name' => 'delete articles']);
        // Permission::create(['name' => 'publish articles']);
        // Permission::create(['name' => 'unpublish articles']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'admin']);
        // $role1->givePermissionTo('edit articles');
        // $role1->givePermissionTo('delete articles');

        $role2 = Role::create(['name' => 'dispatcher']);
        // $role2->givePermissionTo('publish articles');
        // $role2->givePermissionTo('unpublish articles');

        $role3 = Role::create(['name' => 'driver']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider
        $user1->assignRole($role1);

        $user2->assignRole($role2);

        $user3->assignRole($role3);
    }
}
