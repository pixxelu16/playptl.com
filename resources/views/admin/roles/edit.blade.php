@extends('layouts.admin')

@section('title', 'Edit Role | '.config('app.name', 'playptl'))
@section('meta_description', 'Update custom role name and privileges.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Role: {{ $role->name }}</h1>
                <p class="admin-card-text">Modify permissions and configuration for this system role.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.roles.index') }}" style="display: inline-flex; align-items: center; gap: 8px; color: #5DA44E; font-weight: 600; text-decoration: none;">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Roles</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error" style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.roles.update', $role) }}" style="display: flex; flex-direction: column; gap: 20px; max-width: 800px; margin-top: 20px;">
            @csrf
            @method('PUT')
            
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px;">
                <label for="name" style="font-weight: 700; font-size: 13px; color: #222;">Role Name <span style="color: #dc2626;">*</span></label>
                @if ($isProtected)
                    <input type="text" value="{{ $role->name }}" disabled 
                        style="height: 44px; width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0 12px; font-size: 14px; background-color: #f3f4f6; color: #6b7280; box-sizing: border-box; cursor: not-allowed;">
                    <p style="font-size: 11px; color: #6b7280; margin-top: 4px; font-style: italic;">Note: System default roles cannot be renamed.</p>
                @else
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" placeholder="e.g. Content Editor, Support Agent" 
                        style="height: 44px; width: 100%; border: 1px solid #dddddd; border-radius: 8px; padding: 0 12px; font-size: 14px; box-sizing: border-box;" required>
                @endif
            </div>

            <div style="margin-bottom: 15px;">
                <h3 style="font-weight: 700; font-size: 14px; color: #222; margin-bottom: 6px; border-bottom: 1px solid #eee; padding-bottom: 8px;">Assign Permissions</h3>
                <p style="font-size: 12px; color: #666; margin-bottom: 16px;">Check all capabilities this role is authorized to perform:</p>
                
                @if ($role->name === 'Super Admin')
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 14px; border-radius: 8px; color: #166534; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Super Admin implicitly bypasses all permissions checks. Individual allocations are not required.</span>
                    </div>
                @else
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px;">
                        @foreach ($permissions as $perm)
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background-color: #fafafa; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" @checked(in_array($perm->name, $rolePermissions, true)) style="width: 17px; height: 17px; accent-color: #5DA44E; cursor: pointer;">
                                <div>
                                    <span style="font-size: 13px; font-weight: 600; color: #222; text-transform: capitalize;">{{ $perm->name }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <button class="admin-button" type="submit" style="background-color: #5FA252; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; align-self: flex-start; transition: opacity 0.2s;">
                Update Role Settings
            </button>
        </form>
    </section>
@endsection
