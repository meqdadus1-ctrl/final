<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) الصلاحيات والأدوار
        $this->call(RolePermissionSeeder::class);

        // 2) حساب المدير الافتراضي
        $admin = User::firstOrCreate(
            ['email' => 'admin@hr.test'],
            [
                'name'     => 'مدير النظام',
                'password' => Hash::make('password'),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
