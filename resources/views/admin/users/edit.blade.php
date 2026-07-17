@extends('layouts.admin')

@section('title', 'Edit User | Admin')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header" style="margin-bottom: 24px;">
            <div>
                <h1 class="admin-card-title">Edit User: {{ $user->name }}</h1>
                <p class="admin-card-text">Modify account credentials, change system role, or update administrative access.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="admin-button admin-button-secondary" style="background: #e2e8f0; color: #334155; border: none; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-arrow-left" aria-hidden="true" style="margin-right: 6px;"></i>
                <span>Back to Users</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error" style="margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-form">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="admin-form-group">
                    <label for="name" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Name <span style="color: red;">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="admin-form-input" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;" required>
                </div>

                <div class="admin-form-group">
                    <label for="email" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Email Address <span style="color: red;">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="admin-form-input" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="admin-form-group">
                    <label for="password" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">New Password (Optional)</label>
                    <input type="password" name="password" id="password" class="admin-form-input" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;" placeholder="Leave empty to keep existing password">
                </div>

                <div class="admin-form-group">
                    <label for="role" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Account Type / Base Role <span style="color: red;">*</span></label>
                    <select name="role" id="role" class="admin-form-input" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; height: 43px;" required>
                        @foreach(\App\Enums\UserRole::cases() as $case)
                            <option value="{{ $case->value }}" {{ old('role', $user->role->value) === $case->value ? 'selected' : '' }}>{{ ucfirst($case->value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">

            {{-- Assign Roles & Permissions --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                
                {{-- Spatie Roles --}}
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-users-gear" style="color: #5DA44E;"></i> Assign Administrative Roles
                    </h3>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">Assigning a role grants all permissions associated with that role automatically.</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 250px; overflow-y: auto; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        @foreach($roles as $role)
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: #334155; cursor: pointer;">
                                <input type="checkbox" name="spatie_roles[]" value="{{ $role->name }}" {{ in_array($role->name, $userRoles, true) ? 'checked' : '' }} style="border-radius: 4px; border: 1px solid #cbd5e1; width: 16px; height: 16px;">
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Spatie Permissions --}}
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-shield-halved" style="color: #5DA44E;"></i> Direct Permissions
                    </h3>
                    <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">Directly assign specific granular permissions to this user (bypassing role scopes).</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 250px; overflow-y: auto; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        @foreach($permissions as $perm)
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: #334155; cursor: pointer;">
                                <input type="checkbox" name="spatie_permissions[]" value="{{ $perm->name }}" {{ in_array($perm->name, $userPermissions, true) ? 'checked' : '' }} style="border-radius: 4px; border: 1px solid #cbd5e1; width: 16px; height: 16px;">
                                <span>{{ $perm->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 12px;">
                <button type="submit" class="admin-button">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true" style="margin-right: 6px;"></i>
                    <span>Save Changes</span>
                </button>
                <a href="{{ route('admin.users.index') }}" class="admin-button admin-button-secondary" style="background: #ef4444; color: white; border: none; display: inline-flex; align-items: center; justify-content: center;">Cancel</a>
            </div>
        </form>
    </section>
@endsection
