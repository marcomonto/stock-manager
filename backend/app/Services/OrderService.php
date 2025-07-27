<?php

namespace App\Services;

use _PHPStan_e7febc360\Nette\InvalidArgumentException;
use App\Enums\OrderStatus;
use App\Gateways\CacheGateway;
use App\Models\Order;
use App\Models\Product;
use App\Utils\PaginationOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected CacheGateway $cacheGateway,
    ) {}

    private const int CACHE_TTL_SINGLE = 7200;

    private const int CACHE_TTL_LIST = 600;

    private const string CACHE_PREFIX = 'orders:';

    private const string CACHE_LIST_PREFIX = 'orders:list:';

    public function create(
        array $orderItems,
        string $name,
        string $description,
    ): Order {
        $itemsToAttach = $this->checkOrderItems($orderItems);

        $order = Order::query()
            ->create([
                'id' => Str::ulid(),
                'status' => OrderStatus::DELIVERED, // For the initial logic the changes are instant
                'name' => $name,
                'description' => $description,
            ]);
        $order->products()->attach($itemsToAttach);

        $this->invalidateOrderCaches($order->id);

        return $order;
    }

    public function update(
        Order $order,
        array $orderItems,
        string $name,
        string $description,
    ): Order {
        if (! $order->canBeModified()) {
            throw new InvalidArgumentException('Order cannot be modified in current status');
        }

        $this->restoreStockForOrderItems($order);
        $itemsToSync = $this->checkOrderItems($orderItems);

        $order->update([
            'name' => $name,
            'description' => $description,
        ]);

        $order->products()->sync($itemsToSync);

        $this->invalidateOrderCaches($order->id);

        return $order;
    }

    public function delete(string $id): void
    {
        $order = $this->find($id);
        $this->restoreStockForOrderItems($order);
        $order->update(['status' => OrderStatus::CANCELLED]);
        $order->delete();

        $this->invalidateOrderCaches($id);
    }

    public function list(
        ?string $name = null,
        ?string $description = null,
        ?\DateTime $creationDate = null,
        ?PaginationOptions $paginationOptions = null,
    ): Collection {
        $cacheKey = $this->generateListCacheKey($name, $description, $creationDate, $paginationOptions);

        return $this->cacheGateway->remember($cacheKey, self::CACHE_TTL_LIST, function () use ($name, $description, $creationDate, $paginationOptions) {
            return $this->executeListQuery($name, $description, $creationDate, $paginationOptions);
        });
    }

    public function find(string $id): Order
    {
        $cacheKey = self::CACHE_PREFIX.$id;
        $order = $this->cacheGateway->remember($cacheKey, self::CACHE_TTL_SINGLE,
            function () use ($id) {
                return Order::query()
                    ->where('id', $id)
                    ->first();
            });

        if (! $order) {
            throw new InvalidArgumentException("Order {$id} not found");
        }

        return $order;
    }

    public function get(string $id): ?Order
    {
        $cacheKey = self::CACHE_PREFIX.$id;

        return $this->cacheGateway
            ->remember($cacheKey, self::CACHE_TTL_SINGLE, function () use ($id) {
                return Order::query()
                    ->where('id', $id)
                    ->first();
            });
    }

    /**
     * Clear cache for a specific order and all list caches
     */
    protected function invalidateOrderCaches(string $orderId): void
    {
        $this->cacheGateway->forget(
            self::CACHE_PREFIX.$orderId
        );
        $this->cacheGateway->clearByPrefix(
            self::CACHE_LIST_PREFIX
        );
    }

    /**
     * Generate cache key for list operations
     */
    protected function generateListCacheKey(
        ?string $name,
        ?string $description,
        ?\DateTime $creationDate,
        ?PaginationOptions $paginationOptions
    ): string {
        $keyParts = [
            self::CACHE_LIST_PREFIX,
            'name:'.($name ?? 'null'),
            'desc:'.($description ?? 'null'),
            'date:'.($creationDate ? $creationDate->format('Y-m-d') : 'null'),
            'page:'.($paginationOptions?->page ?? 'null'),
            'per_page:'.($paginationOptions?->rowsPerPage ?? 'null'),
        ];

        return implode(':', $keyParts);
    }

    /**
     * Execute the list query (extracted for reusability)
     */
    protected function executeListQuery(
        ?string $name,
        ?string $description,
        ?\DateTime $creationDate,
        ?PaginationOptions $paginationOptions
    ): Collection {
        $query = Order::query();
        if ($name) {
            $query->where('name', 'like', '%'.$name.'%');
        }
        if ($description) {
            $query->where('description', 'like', '%'.$description.'%');
        }
        if ($creationDate) {
            $query->whereDate('created_at', $creationDate->format('Y-m-d'));
        }
        $query->orderByDesc('created_at');
        if ($paginationOptions) {
            $paginator = $query->simplePaginate(
                perPage: $paginationOptions->rowsPerPage,
                page: $paginationOptions->page
            );

            return collect($paginator->items());
        }

        return $query->get();
    }

    protected function restoreStockForOrderItems(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            $item->product->increment('stock_quantity', $item->quantity);
        }
    }

    protected function checkOrderItems(array $orderItems): array
    {
        $productIds = collect($orderItems)
            ->pluck(0)
            ->unique()
            ->sort()
            ->values();

        // to avoid possible deadlock with multiple concurrent requests
        // on same products, very edgy case, but better handle it
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $itemsToAttach = [];

        foreach ($orderItems as [$productId, $quantity]) {
            $product = $products->get($productId);

            if (! $product) {
                throw new InvalidArgumentException("Product not present with given id: $productId");
            }

            if (! $product->is_active) {
                throw new InvalidArgumentException("Product $product->name not active");
            }

            if ($product->stock_quantity < $quantity) {
                throw new InvalidArgumentException("Insufficient Quantity for $product->name");
            }
            if (! empty($itemsToAttach[$productId])) {
                throw new InvalidArgumentException('Multiple products with same id');
            }
            $product->decrement('stock_quantity', $quantity);
            $itemsToAttach[$productId] = ['quantity' => $quantity];
        }

        return $itemsToAttach;
    }
}
