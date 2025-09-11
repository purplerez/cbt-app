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
        $userData = [
            'kepala' => [
                ['name' => 'Kepala Sekolah 1', 'email' => 'kepala1@gmail.com'],
                ['name' => 'Kepala Sekolah 2', 'email' => 'kepala2@gmail.com'],
                ['name' => 'Kepala Sekolah 3', 'email' => 'kepala3@gmail.com'],
            ],
            'guru' => [
                ['name' => 'Guru Matematika', 'email' => 'guru1@gmail.com'],
                ['name' => 'Guru Bahasa Indonesia', 'email' => 'guru2@gmail.com'],
                ['name' => 'Guru Bahasa Inggris', 'email' => 'guru3@gmail.com'],
            ],
            'siswa' => [
                ['name' => 'Ahmad Rizki', 'email' => 'siswa1@gmail.com'],
                ['name' => 'Siti Nurhaliza', 'email' => 'siswa2@gmail.com'],
                ['name' => 'Budi Santoso', 'email' => 'siswa3@gmail.com'],
                ['name' => 'Dewi Sartika', 'email' => 'siswa4@gmail.com'],
                ['name' => 'Muhammad Fauzi', 'email' => 'siswa5@gmail.com'],
            ]
        ];

        foreach ($userData as $role => $users) {
            foreach ($users as $userData) {
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => bcrypt('12345678'),
                        'role' => $role,
                        'is_active' => 1,
                        'email_verified_at' => now()
                    ]
                );

                $user->syncRoles([]); // Clear any existing roles
                $user->assignRole($role);
            }
        }

        /* Verify role assignments
        \Log::info('Admin users: ' . User::role('admin')->count());
        \Log::info('Kepala users: ' . User::role('kepala')->count());
        \Log::info('Guru users: ' . User::role('guru')->count());
        \Log::info('Siswa users: ' . User::role('siswa')->count());
        */
    }
}
