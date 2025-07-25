<?php

namespace App\Services;

use _PHPStan_e7febc360\Nette\InvalidArgumentException;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;

class OrderService
{
    public function create(
        array   $orderItems,
        ?string $notes = null,
    ): Order
    {
        $productIds = collect($orderItems)
            ->pluck(0)
            ->unique()
            ->sort()
            ->values();

        // to avoid possible deadlock with multiple requests
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $itemsToAttach = [];

        foreach ($orderItems as [$productId, $quantity]) {
            $product = $products->get($productId);

            if (!$product) {
                throw new InvalidArgumentException();
            }

            if ($product->stock_quantity < $quantity) {
                throw new InvalidArgumentException();
            }
            $product->decrement('quantity', $quantity);
            $itemsToAttach[$productId] = ['quantity' => $quantity];
        }
        $order = Order::query()
            ->create([
                'id' => Str::ulid(),
                'status' => OrderStatus::DELIVERED, // For the initial logic the changes are instant
                'notes' => $notes,
            ]);
        $order->products()->attach($itemsToAttach);
        return $order;
    }

    /**
     * @throws \Exception
     */
    public function addProduct(
        Order   $order,
        Product $product,
        int     $quantity
    ): OrderItem
    {
        if (!$order->canBeModified()) {
            throw new \InvalidArgumentException();
        }

        if (!$product->isAvailable($quantity)) {
            throw new \InvalidArgumentException();
        }

        $existingItem = $order
            ->orderItems()
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if (!$product->isAvailable($newQuantity - $existingItem->quantity)) {
                throw new \Exception("Stock insufficiente per {$product->name}");
            }
            $existingItem->update(['quantity' => $newQuantity]);
            return $existingItem;
        }

        return $product->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => $quantity
        ]);
    }

    public function removeProduct(
        Order   $order,
        Product $product
    ): bool
    {
        if (!$order->canBeModified()) {
            throw new InvalidArgumentException();
        }

        return $order->orderItems()->where('product_id', $product->id)->delete() > 0;
    }

    public function updateStatus(
        Order       $order,
        OrderStatus $newStatus
    ): Order
    {
        $order->update(['status' => $newStatus]);
        return $order;
    }

    public function cancel(
        Order $order,
    ): Order
    {
        if ($order->status === OrderStatus::CANCELLED) {
            return $order;
        }

        if (in_array(
            $order->status,
            [OrderStatus::PENDING, OrderStatus::PROCESSING])
        ) {
            foreach ($order->orderItems as $item) {
                $item->product->incrementStock($item->quantity);
            }
        }

        $this->updateStatus(
            $order,
            OrderStatus::CANCELLED
        );
        return $order;
    }
}
