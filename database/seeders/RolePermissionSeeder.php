<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =====================================================
        // كل الصلاحيات
        // =====================================================
        $permissions = [
            // الموظفون
            'employees.view', 'employees.create', 'employees.edit',
            'employees.delete', 'employees.profile',

            // الأقسام
            'departments.view', 'departments.create',
            'departments.edit', 'departments.delete',

            // الحضور
            'attendance.view', 'attendance.view_own',
            'attendance.create', 'attendance.edit', 'attendance.delete',

            // الرواتب
            'payslips.view', 'payslips.view_own', 'payslips.create',
            'payslips.edit', 'payslips.delete', 'payslips.pdf',

            // السلف
            'loans.view', 'loans.view_own', 'loans.create',
            'loans.edit', 'loans.delete', 'loans.approve',

            // الإجازات
            'leaves.view', 'leaves.view_own', 'leaves.create',
            'leaves.edit', 'leaves.approve', 'leaves.reject',

            // التوظيف
            'jobs.view', 'jobs.create', 'jobs.edit', 'jobs.delete',

            // التقارير
            'reports.view', 'reports.export',

            // البنوك
            'banks.view', 'banks.create', 'banks.edit', 'banks.delete',

            // المستخدمون والأدوار
            'users.view', 'users.create', 'users.edit',
            'users.delete', 'users.assign_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =====================================================
        // الأدوار وصلاحياتها
        // =====================================================

        // --- Admin: كل شيء ---
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        // --- HR: إدارة الموظفين والرواتب والحضور ---
        $hr = Role::firstOrCreate(['name' => 'hr']);
        $hr->syncPermissions([
            'employees.view', 'employees.create', 'employees.edit', 'employees.profile',
            'departments.view',
            'attendance.view', 'attendance.create', 'attendance.edit',
            'payslips.view', 'payslips.create', 'payslips.edit', 'payslips.pdf',
            'loans.view', 'loans.create', 'loans.edit', 'loans.approve',
            'leaves.view', 'leaves.create', 'leaves.approve', 'leaves.reject',
            'jobs.view', 'jobs.create', 'jobs.edit',
            'reports.view', 'reports.export',
            'banks.view',
        ]);

        // --- Manager: عرض فقط + موافقة الإجازات والسلف ---
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'employees.view', 'employees.profile',
            'departments.view',
            'attendance.view',
            'payslips.view',
            'loans.view', 'loans.approve',
            'leaves.view', 'leaves.approve', 'leaves.reject',
            'reports.view',
        ]);

        // --- Employee: بياناته فقط ---
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'attendance.view_own',
            'payslips.view_own', 'payslips.pdf',
            'loans.view_own', 'loans.create',
            'leaves.view_own', 'leaves.create',
        ]);

        // =====================================================
        // أعطِ أول مستخدم دور Admin تلقائياً
        // =====================================================
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasRole('admin')) {
            $firstUser->assignRole('admin');
            $this->command->info('✅ تم تعيين دور Admin للمستخدم: ' . $firstUser->name);
        }

        $this->command->info('✅ تم إنشاء ' . count($permissions) . ' صلاحية و 4 أدوار بنجاح!');
    }
}
