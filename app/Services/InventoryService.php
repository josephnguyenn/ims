<?php

namespace App\Services;

use App\Models\ProductShipment;
use Exception;

class InventoryService
{
    /**
     * Deduct stock from product shipments using FIFO
     */
    public static function deductFIFOByCode(string $code, int $quantityToDeduct): void
    {
        $remaining = $quantityToDeduct;

        // Get products with this code, ordered by oldest expiry/shipment
        $products = \App\Models\Product::where('code', $code)
            ->where('actual_quantity', '>', 0)
            ->orderBy('expired_date') // or created_at if no expired_date
            ->get();

        foreach ($products as $product) {
            if ($remaining <= 0) break;

            $deductQty = min($product->actual_quantity, $remaining);
            $product->decrement('actual_quantity', $deductQty);
            $remaining -= $deductQty;
        }

        if ($remaining > 0) {
            throw new \Exception("Không đủ hàng tồn cho mã sản phẩm: $code");
        }
    }

}
