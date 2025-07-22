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
        //
        $roles = ['admin', 'kepala', 'guru', 'siswa'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $admin = User::firstOrCreate(['name' => 'admin', 'email' => 'admin@gmail.com', 'password' => '12345678','school_id' => 1]);
        $admin->assignRole('admin');

        User::factory(10)->create()->each(function ($user) {
            $role = $user->role;
            if(in_array($role, ['kepala', 'guru', 'siswa'])) {
                $user->assignRole($role);
            } else {
                $user->assignRole('siswa'); // Default role if not matched
            }
        });
    }
}
