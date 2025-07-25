<?php

namespace App\Services;

use App\Models\Product;
use InvalidArgumentException;

class ProductService
{
    public function isAvailable(
        Product $product,
        int $quantity = 1,
    ): bool
    {
        return $product->is_active
            && $product->hasStock($quantity);
    }

    public function decrementStock(
        Product $product,
        int $quantity,
    ): void
    {
        if ($product->hasStock($quantity)) {
            $product->update([
                'stock_quantity' => $product->stock_quantity - $quantity
            ]);
        }
        throw new InvalidArgumentException();
    }

    public function incrementStock(
        Product $product,
        int $quantity,
    ): void
    {
        $product->update([
            'stock_quantity' => $product->stock_quantity + $quantity
        ]);
    }

}
