<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;



class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles with explicit guard
        $roles = ['admin', 'kepala', 'guru', 'siswa'];
        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web'
            ]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('12345678'),
                'role' => 'admin',
                'is_active' => 1,
                'email_verified_at' => now()
            ]
        );
        
        // Clear and reassign role
        $admin->syncRoles([]); // Clear existing roles
        $admin->assignRole('admin');

        // Create users with specific roles
        foreach (['kepala', 'guru', 'siswa'] as $role) {
            User::factory(3)->create([
                'role' => $role,
                'is_active' => 1,
                'email_verified_at' => now()
            ])->each(function ($user) use ($role) {
                $user->syncRoles([]); // Clear any existing roles
                $user->assignRole($role);
            });
        }

        /* Verify role assignments
        \Log::info('Admin users: ' . User::role('admin')->count());
        \Log::info('Kepala users: ' . User::role('kepala')->count());
        \Log::info('Guru users: ' . User::role('guru')->count());
        \Log::info('Siswa users: ' . User::role('siswa')->count());
        */
    }
}
