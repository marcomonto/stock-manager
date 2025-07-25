<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $notes
 * @property OrderStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItem> $orderItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 */
class Order extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'id',
        'notes',
        'status'
    ];

    protected $casts = [
        'status' => OrderStatus::class
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

}
