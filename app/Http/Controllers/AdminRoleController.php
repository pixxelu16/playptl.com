<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    // Protected default roles that cannot be deleted or renamed
    protected array $protectedRoles = [
        'Super Admin',
        'Admin',
        'Player',
        'Coach',
        'Mentor',
        'Student'
    ];

    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name']
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $isProtected = in_array($role->name, $this->protectedRoles, true);

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions', 'isProtected'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $isProtected = in_array($role->name, $this->protectedRoles, true);

        $rules = [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name']
        ];

        // Only allow renaming if the role is not a protected system role
        if (!$isProtected) {
            $rules['name'] = ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)];
        }

        $validated = $request->validate($rules);

        if (!$isProtected && isset($validated['name'])) {
            $role->name = $validated['name'];
            $role->save();
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, $this->protectedRoles, true)) {
            return redirect()->route('admin.roles.index')->with('error', 'Protected system roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
