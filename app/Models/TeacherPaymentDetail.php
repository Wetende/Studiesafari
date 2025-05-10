<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TeacherPaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method',
        'account_details',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    /**
     * The teacher who owns the payment details.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the payment details are valid and verified.
     */
    public function isValid(): bool
    {
        return $this->is_verified && !empty($this->account_details);
    }

    /**
     * Get a specific account detail by key.
     */
    public function getAccountDetail(string $key): ?string
    {
        $details = json_decode($this->account_details, true);
        return $details[$key] ?? null;
    }

    /**
     * Set a specific account detail by key.
     */
    public function setAccountDetail(string $key, string $value): void
    {
        $details = json_decode($this->account_details, true) ?: [];
        $details[$key] = $value;
        $this->account_details = json_encode($details);
    }
}
