<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'name',
        'policy_number',
        'benefit_plan_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($member) {
            $member->policy_number = self::generatePolicyNumber();
        });
    }

    public static function generatePolicyNumber(): string
    {
        $year = date('Y');
        $prefix = "POL-{$year}-";

        $lastMember = self::where('policy_number', 'like', "{$prefix}%")
            ->orderBy('policy_number', 'desc')
            ->first();

        if ($lastMember) {
            $lastNumber = (int) substr($lastMember->policy_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function benefitPlan(): BelongsTo
    {
        return $this->belongsTo(BenefitPlan::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
