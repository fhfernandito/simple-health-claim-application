<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BenefitPlan extends Model
{
    protected $fillable = [
        'plan_name',
        'annual_limit',
    ];

    protected $casts = [
        'annual_limit' => 'decimal:2',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
