<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary related records
        ProductCategory::factory()->create(['id' => 1, 'name' => 'Test Category']);
        Shipment::factory()->create(['id' => 1]);
    }

    public function test_guest_cannot_access_products(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_products(): void
    {
        $user = User::factory()->create();
        Product::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'code', 'price', 'actual_quantity'],
                ],
            ]);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'code' => 'TEST001',
                'original_quantity' => 100,
                'price' => 99.99,
                'cost' => 50.00,
                'category_id' => 1,
                'shipment_id' => 1,
                'tax' => 21,
                'expiry_mode' => 'none',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'code' => 'TEST001',
        ]);
    }

    public function test_staff_cannot_create_product(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'code' => 'TEST001',
                'original_quantity' => 100,
                'price' => 99.99,
                'cost' => 50.00,
                'category_id' => 1,
                'shipment_id' => 1,
                'expiry_mode' => 'none',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create([
            'name' => 'Old Name',
            'code' => 'OLD001',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/products/{$product->id}", [
                'name' => 'New Name',
                'code' => 'OLD001',
                'price' => 199.99,
                'cost' => 100.00,
                'category_id' => $product->category_id,
                'shipment_id' => $product->shipment_id,
                'original_quantity' => 50,
                'expiry_mode' => 'none',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_search_by_code_returns_fifo_sorted_results(): void
    {
        $user = User::factory()->create();
        
        // Create older shipment
        $oldShipment = Shipment::factory()->create([
            'order_date' => now()->subDays(10),
        ]);
        
        // Create newer shipment
        $newShipment = Shipment::factory()->create([
            'order_date' => now()->subDays(5),
        ]);

        // Create products with same code but different shipments
        $oldProduct = Product::factory()->create([
            'code' => 'SAME001',
            'shipment_id' => $oldShipment->id,
            'actual_quantity' => 10,
        ]);

        $newProduct = Product::factory()->create([
            'code' => 'SAME001',
            'shipment_id' => $newShipment->id,
            'actual_quantity' => 20,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/products/search?code=SAME001');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // First item should be from older shipment (FIFO)
        $this->assertEquals($oldProduct->id, $data[0]['id']);
        $this->assertEquals($newProduct->id, $data[1]['id']);
    }
}
