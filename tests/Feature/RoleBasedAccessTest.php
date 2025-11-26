<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden. You do not have permission to access this resource.']);
    }

    public function test_manager_can_manage_products(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager, 'sanctum')
            ->postJson('/api/products', [
                'name' => 'Manager Product',
                'code' => 'MGR001',
                'original_quantity' => 50,
                'price' => 99.99,
                'cost' => 50.00,
                'category_id' => 1,
                'shipment_id' => 1,
                'expiry_mode' => 'none',
            ]);

        $response->assertStatus(201);
    }

    public function test_staff_cannot_manage_products(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/products', [
                'name' => 'Staff Product',
                'code' => 'STF001',
                'original_quantity' => 50,
                'price' => 99.99,
                'cost' => 50.00,
                'category_id' => 1,
                'shipment_id' => 1,
                'expiry_mode' => 'none',
            ]);

        $response->assertStatus(403);
    }

    public function test_all_authenticated_users_can_view_products(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/products');

        $response->assertStatus(200);
    }

    public function test_all_authenticated_users_can_create_orders(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/orders', [
                'source' => 'pos',
                'cashier_id' => $staff->id,
                'subtotal_czk' => 100,
                'grand_total_czk' => 100,
                'rounded_total_czk' => 100,
                'payment_currency' => 'CZK',
                'amount_tendered_czk' => 100,
                'payment_method' => 'cash',
                'paid_amount' => 100,
                'items' => [],
            ]);

        // May fail validation but shouldn't be 403 Forbidden
        $this->assertNotEquals(403, $response->status());
    }
}
