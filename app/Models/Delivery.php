<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class Delivery extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'order_id',
        'order_item_id',
        'product_id',
        'delivered_quantity',
        'ordered_quantity',
        'status',
        'delivered_by',
        'delivered_at',
        'notes',
    ];
    protected $casts = [
        'delivered_at' => 'datetime',
        'delivered_quantity' => 'decimal:2',
        'ordered_quantity' => 'decimal:2',
    ];
    /**
     * Get the order that owns the delivery.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    /**
     * Get the order item associated with the delivery.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    /**
     * Get the product associated with the delivery.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Get the user who delivered the item.
     */
    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}