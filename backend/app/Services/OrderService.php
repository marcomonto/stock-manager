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
            $product->decrement('stock_quantity', $quantity);
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

    public function update(
        Order   $order,
        array   $orderItems,
        ?string $notes = null,
    ): Order
    {
        if (!$order->canBeModified()) {
            throw new InvalidArgumentException("Order cannot be modified in current status");
        }

        $this->_restoreStockForOrderItems($order);
        $productIds = collect($orderItems)
            ->pluck(0)
            ->unique()
            ->sort()
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $itemsToSync = [];

        foreach ($orderItems as [$productId, $quantity]) {
            $product = $products->get($productId);

            if (!$product) {
                throw new InvalidArgumentException("Product {$productId} not found");
            }

            if (!$product->hasStock($quantity)) {
                throw new InvalidArgumentException("Insufficient stock for product {$productId}");
            }

            $product->decrement('stock_quantity', $quantity);
            $itemsToSync[$productId] = ['quantity' => $quantity];
        }
        $order->update([
            'notes' => $notes,
        ]);

        $order->products()->sync($itemsToSync);
        return $order;
    }

    public function updateStatus(
        Order       $order,
        OrderStatus $newStatus
    ): Order
    {
        $order->update(['status' => $newStatus]);
        return $order;
    }

    public function delete(
        string $orderId,
    ): Order
    {
        $order = $this->find($orderId);
        if ($order->status === OrderStatus::CANCELLED) {
            return $order;
        }
        $this->_restoreStockForOrderItems($order);

        $this->updateStatus(
            $order,
            OrderStatus::CANCELLED
        );
        return $order;
    }

    public function list(
        bool $withDetails = false,
    ){

    }

    public function find(string $id): Order
    {
        $order = Order::query()
            ->where('id', $id)
            ->first();
        if (!$order) {
            throw new InvalidArgumentException("Order {$id} not found");
        }
        return $order;
    }

    public function get(string $id): ?Order
    {
        return Order::query()
            ->where('id', $id)
            ->first();
    }

    private function _restoreStockForOrderItems(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }
    }
}
