<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'duration',
        'subtitle',
        'user_limit',
        'branch_limit',
        'storage_limit',
        'is_active',
        'sub_branch_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'user_limit' => 'integer',
        'branch_limit' => 'integer',
        'storage_limit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function features()
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'plan_id');
    }
}
