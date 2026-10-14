<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    use HasFactory;

    public const STATUS_SIMULATED_UNPAID = 'SIMULATED_UNPAID';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'integer',
            'payment_amount' => 'integer',
            'commission_amount' => 'integer',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(CommunityInvitation::class, 'community_invitation_id');
    }
}
