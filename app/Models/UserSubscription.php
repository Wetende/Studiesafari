<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class UserSubscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'subscription_tier_id',
        'starts_at',
        'ends_at',
        'is_active',
        'auto_renew',
        'status',
        'canceled_at',
        'cancellation_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'canceled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription tier that owns the subscription.
     */
    public function subscriptionTier(): BelongsTo
    {
        return $this->belongsTo(SubscriptionTier::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               $this->status === 'active' && 
               now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Check if the subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if the subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || now()->isAfter($this->ends_at);
    }

    /**
     * Check if the subscription should be auto-renewed.
     */
    public function shouldAutoRenew(): bool
    {
        return $this->auto_renew && 
               $this->is_active && 
               !$this->isCanceled() && 
               now()->diffInDays($this->ends_at) <= 3;
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('status', 'active')
                     ->where('starts_at', '<=', now())
                     ->where('ends_at', '>=', now());
    }

    /**
     * Scope a query to only include expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                     ->orWhere('ends_at', '<', now());
    }
}
