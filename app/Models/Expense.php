<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'sr_no', 'payment_mode', 'expense_name', 'expense_date', 'amount', 'description', 'expense_type_id', 'isDeleted','created_by'];

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id')
            ->where('isDeleted', 0);
    }

    public static function getNextSrNoForBranch($branchId)
    {
        $maxSrNo = self::where('branch_id', $branchId)->max('sr_no');
        return (int) ($maxSrNo ?? 0) + 1;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
