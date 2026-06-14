<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class DashboardTest extends TestCase {
    use RefreshDatabase;
    public function test_home_redirects_guests_to_login() {
        $response = $this->get(route('home'));
        $response->assertRedirect(route('login'));
    }
    public function test_authenticated_users_can_visit_the_dashboard() {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }
}