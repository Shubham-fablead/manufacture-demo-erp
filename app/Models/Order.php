<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'order_number',
        'order_date',
        'shipping',
        'tds_percentage',
        'tds_amount',
        'user_id',
        'assigned_staff',
        'order_type',
        'discount',
        'tax_id',
        'gst_option',
        'branch_id',
        'created_by',
        'total_amount',
        'remaining_amount',
        'payment_status',
        'delivery_status',
        'payment_method',
        'emi_down_payment',
        'emi_loan_amount',
        'emi_interest_rate',
        'emi_tenure',
        'emi_monthly_amount',
        'emi_aadhar_number',
        'emi_pan_number',
        'emi_guarnator_name',
        'emi_do_id',
        'emi_bank_id',
        'emi_months',
        'emi_duration',
        'next_pending_date',
        'order_invoice',
        'quotation_status',
        'approved_status',
        'remarks',
        'isDeleted',
        'created_at',
        'updated_at',
    ];  

    protected $casts = [
        'tax_id' => 'array',
        'tds_percentage' => 'decimal:2',
        'tds_amount' => 'decimal:2',
    ];

    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function payments()
    {
        return $this->hasMany(PaymentStore::class, 'order_id', 'id');
    }
    public function returns()
    {
        return $this->hasMany(SalesReturn::class, 'order_id', 'id');
    }
   public function labour_items()
    {
        return $this->hasMany(Sales_Labour_Items::class, 'order_id', 'id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = Carbon::now('Asia/Kolkata');
            }

            if (empty($model->updated_at)) {
                $model->updated_at = Carbon::now('Asia/Kolkata');
            }
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
