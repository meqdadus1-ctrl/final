<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الـ cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // الأقسام
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            // الموظفون
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete', 'employees.profile',
            // البنوك
            'banks.view', 'banks.create', 'banks.edit', 'banks.delete',
            // الحضور
            'attendance.view', 'attendance.view_own', 'attendance.create', 'attendance.edit', 'attendance.delete',
            // السلف
            'loans.view', 'loans.view_own', 'loans.create', 'loans.edit', 'loans.delete', 'loans.approve',
            // الرواتب
            'payslips.view', 'payslips.create', 'payslips.edit', 'payslips.delete',
            // الإجازات
            'leaves.view', 'leaves.view_own', 'leaves.create', 'leaves.edit', 'leaves.approve', 'leaves.reject',
            // التوظيف
            'jobs.view', 'jobs.create', 'jobs.edit', 'jobs.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // أنشئ الـ roles
        $admin   = Role::firstOrCreate(['name' => 'admin',    'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'manager',  'guard_name' => 'web']);
        $hr      = Role::firstOrCreate(['name' => 'hr',       'guard_name' => 'web']);
        $employee= Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        // Admin يأخذ كل الصلاحيات
        $admin->syncPermissions(Permission::all());

        // Manager
        $manager->syncPermissions([
            'departments.view',
            'employees.view', 'employees.create', 'employees.edit', 'employees.profile',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'loans.view', 'loans.create', 'loans.approve',
            'payslips.view', 'payslips.create',
            'leaves.view', 'leaves.approve', 'leaves.reject',
            'jobs.view', 'jobs.create', 'jobs.edit',
            'banks.view',
        ]);

        // HR
        $hr->syncPermissions([
            'departments.view',
            'employees.view', 'employees.create', 'employees.edit', 'employees.profile',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'loans.view',
            'payslips.view',
            'leaves.view', 'leaves.create',
            'jobs.view', 'jobs.create',
        ]);

        // Employee (بوابة الموظف فقط)
        $employee->syncPermissions([
            'attendance.view_own',
            'loans.view_own',
            'leaves.view_own', 'leaves.create',
        ]);

        $this->command->info('✅ تم إنشاء ' . count($permissions) . ' صلاحية و 4 أدوار بنجاح.');
    }
}
