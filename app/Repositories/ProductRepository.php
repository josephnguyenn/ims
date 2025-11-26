<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    /**
     * Get all products with optional filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 50, bool $paginate = true)
    {
        $query = Product::with(['shipment', 'category']);

        // Apply category filter
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Apply low stock filter
        if (! empty($filters['low_stock'])) {
            $query->where('actual_quantity', '<', 10);
        }

        // Apply price range filters
        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Default sorting
        $query->orderBy('name', 'asc');

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product
    {
        return Product::with('shipment', 'category')->find($id);
    }

    /**
     * Find products by barcode with FIFO sorting
     */
    public function findByCode(string $code): Collection
    {
        $cacheKey = "products:code:{$code}";

        return Cache::remember($cacheKey, 300, function () use ($code) {
            $items = Product::with('shipment')
                ->where('code', $code)
                ->where('actual_quantity', '>', 0)
                ->get();

            return $items->sortBy(function ($p) {
                return optional($p->shipment)->order_date ?? $p->created_at;
            })->values();
        });
    }

    /**
     * Get low stock products
     */
    public function getLowStock(int $threshold = 10): Collection
    {
        return Product::with(['shipment', 'category'])
            ->where('actual_quantity', '<', $threshold)
            ->orderBy('actual_quantity', 'asc')
            ->get();
    }

    /**
     * Get products expiring soon
     */
    public function getExpiringSoon(int $days = 30): Collection
    {
        $date = now()->addDays($days);

        return Product::with(['shipment', 'category'])
            ->whereNotNull('expired_date')
            ->where('expired_date', '<=', $date)
            ->where('expired_date', '>', now())
            ->orderBy('expired_date', 'asc')
            ->get();
    }

    /**
     * Create a new product
     */
    public function create(array $data): Product
    {
        $product = Product::create($data);
        $this->clearCache();

        return $product->load('shipment', 'category');
    }

    /**
     * Update a product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        $this->clearCache($product->code);

        return $product->fresh(['shipment', 'category']);
    }

    /**
     * Delete a product
     */
    public function delete(Product $product): bool
    {
        $code = $product->code;
        $deleted = $product->delete();
        
        if ($deleted) {
            $this->clearCache($code);
        }

        return $deleted;
    }

    /**
     * Clear product cache
     */
    protected function clearCache(?string $code = null): void
    {
        if ($code) {
            Cache::forget("products:code:{$code}");
        }
        
        // Clear other related caches
        Cache::tags(['products', 'analytics'])->flush();
    }
}
