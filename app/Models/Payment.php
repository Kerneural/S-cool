<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_SUCCEEDED = 'SUCCEEDED';

    public const STATUS_FAILED = 'FAILED';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const PROVIDER_SEPAY_SANDBOX = 'SEPAY_SANDBOX';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'external_reference',
        'community_id',
        'user_id',
        'community_invitation_id',
        'amount',
        'currency',
        'status',
        'gateway_provider',
        'gateway_transaction_id',
        'paid_at',
        'payload',
    ];

    /**
     * Baseline defaults.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'currency' => 'VND',
        'status' => self::STATUS_PENDING,
        'gateway_provider' => self::PROVIDER_SEPAY_SANDBOX,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    /**
     * Get the community associated with this payment.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the user who made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invitation linked to this payment, if any.
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(CommunityInvitation::class, 'community_invitation_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount, 0, ',', '.').' '.$this->currency;
    }
}
