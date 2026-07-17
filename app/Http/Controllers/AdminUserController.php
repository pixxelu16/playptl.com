<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Per page filter
        $perPage = (int) $request->get('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }

        $users = $query->latest('id')->paginate($perPage)->withQueryString();

        return view('admin.users.index', compact('users', 'perPage'));
    }

    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('admin.users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'spatie_roles' => ['nullable', 'array'],
            'spatie_roles.*' => ['string', 'exists:roles,name'],
            'spatie_permissions' => ['nullable', 'array'],
            'spatie_permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $username = User::generateUniqueUsername($validated['email']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $username,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Sync Spatie Roles and Permissions
        if (!empty($validated['spatie_roles'])) {
            $user->syncRoles($validated['spatie_roles']);
        }
        if (isset($validated['spatie_permissions'])) {
            $user->syncPermissions($validated['spatie_permissions']);
        }

        // Generate password reset link and send welcome email
        try {
            $token = \Illuminate\Support\Facades\Password::createToken($user);
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\AdminCreatedUserWelcomeMail($user, $resetUrl));
        } catch (\Throwable $e) {
            // Log or ignore to prevent stopping flow if SMTP is down
        }

        return redirect()->route('admin.users.index')->with('status', 'User created successfully and welcome email has been sent.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        $userPermissions = $user->permissions->pluck('name')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'permissions', 'userRoles', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', Rules\Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'spatie_roles' => ['nullable', 'array'],
            'spatie_roles.*' => ['string', 'exists:roles,name'],
            'spatie_permissions' => ['nullable', 'array'],
            'spatie_permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync Spatie Roles and Permissions
        if (isset($validated['spatie_roles'])) {
            $user->syncRoles($validated['spatie_roles']);
        } else {
            $user->syncRoles([]);
        }

        if (isset($validated['spatie_permissions'])) {
            $user->syncPermissions($validated['spatie_permissions']);
        } else {
            $user->syncPermissions([]);
        }

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }
}
