<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityMembership extends Model
{
    use HasFactory;

    public const STATES = ['PENDING_PAYMENT', 'ACTIVE', 'SUSPENDED', 'REMOVED', 'LEFT'];

    protected $guarded = ['*'];

    protected $attributes = ['status' => 'PENDING_PAYMENT'];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Trusted domain primitive; callers must authorize their actor separately.
    // Paid activation is deliberately unavailable until verified billing exists.
    public function transitionTo(string $target): void
    {
        DB::transaction(function () use ($target): void {
            $current = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $allowed = [
                'PENDING_PAYMENT' => [],
                'ACTIVE' => ['SUSPENDED', 'REMOVED', 'LEFT'],
                'SUSPENDED' => ['ACTIVE', 'REMOVED'],
                'REMOVED' => [],
                'LEFT' => [],
            ];
            if (! in_array($target, $allowed[$current->status] ?? [], true)) {
                throw ValidationException::withMessages(['membership' => 'This membership transition is not permitted.']);
            }
            $current->status = $target;
            $current->save();
        });
        $this->refresh();
    }
}
