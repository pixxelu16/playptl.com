<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_redirects_organiser_to_organiser_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Organiser]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('organiser.dashboard'));
    }

    public function test_dashboard_redirects_player_to_player_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Player]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('player.dashboard'));
    }

    public function test_user_is_redirected_from_wrong_role_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::Player]);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertRedirect(route('player.dashboard'));
    }
}
