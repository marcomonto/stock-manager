<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property OrderStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItem> $orderItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 */
class Order extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function canBeModified(): bool
    {
        return $this->status != OrderStatus::CANCELLED;
    }

    public function toArrayResponse(
        bool $withDetails = false
    ): array {
        $orderSerialized = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
            'createdAt' => $this->created_at->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
        if ($withDetails) {
            return [
                ...$orderSerialized,
                'orderItems' => $this->orderItems->map(fn (OrderItem $orderItem) => [
                    'name' => $orderItem->product->name,
                    'quantity' => $orderItem->quantity,
                    'createdAt' => $orderItem->created_at->format('Y-m-d H:i:s'),
                    'updatedAt' => $orderItem->updated_at->format('Y-m-d H:i:s'),
                ]),
            ];
        }

        return $orderSerialized;
    }
}
