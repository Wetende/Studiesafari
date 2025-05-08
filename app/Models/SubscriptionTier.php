<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SubscriptionTier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_in_days',
        'is_active',
        'features',
        'max_courses',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'duration_in_days' => 'integer',
        'is_active' => 'boolean',
        'features' => 'array',
        'max_courses' => 'integer',
    ];

    /**
     * Get the user subscriptions for the subscription tier.
     */
    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the number of active subscribers for this tier.
     */
    public function getActiveSubscribersCountAttribute(): int
    {
        return $this->userSubscriptions()
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('ends_at', '>', now())
            ->count();
    }

    /**
     * Calculate the monthly price.
     */
    public function getMonthlyPriceAttribute(): float
    {
        return $this->price * 30 / $this->duration_in_days;
    }

    /**
     * Scope a query to only include active subscription tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
