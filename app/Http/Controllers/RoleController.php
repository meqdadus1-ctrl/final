<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RoleController extends Controller
{
    /* =====================================================
     *  LIST USERS – /roles
     * ===================================================== */
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        $roles = Role::orderBy('name')->get();

        return view('roles.index', compact('users', 'roles'));
    }

    /* =====================================================
     *  ASSIGN ROLE – POST /roles/assign
     * ===================================================== */
    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]);

        return back()->with('success', "تم تعيين دور {$request->role} للمستخدم {$user->name}");
    }

    /* =====================================================
     *  REMOVE ROLE – POST /roles/remove
     * ===================================================== */
    public function remove(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);

        // لا تحذف آخر admin
        if ($request->role === 'admin' && User::role('admin')->count() <= 1) {
            return back()->with('error', 'لا يمكن حذف آخر مستخدم بدور Admin!');
        }

        $user->removeRole($request->role);

        return back()->with('success', "تم إزالة دور {$request->role} من {$user->name}");
    }

    /* =====================================================
     *  SHOW ROLE PERMISSIONS – /roles/{role}
     * ===================================================== */
    public function show(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($p) {
            return explode('.', $p->name)[0];
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.show', compact('role', 'permissions', 'rolePermissions'));
    }

    /* =====================================================
     *  UPDATE ROLE PERMISSIONS – PUT /roles/{role}
     * =====================================================  */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return back()->with('success', "تم تحديث صلاحيات دور {$role->name}");
    }

    /* =====================================================
     *  CREATE USER – POST /roles/users/create
     * ===================================================== */
    public function createUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return back()->with('success', "تم إنشاء المستخدم {$user->name} بدور {$request->role}");
    }

    /* =====================================================
     *  DELETE USER – DELETE /roles/users/{user}
     * ===================================================== */
    public function deleteUser(User $user)
    {
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('error', 'لا يمكن حذف آخر Admin!');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص!');
        }

        $user->delete();
        return back()->with('success', 'تم حذف المستخدم.');
    }
}
