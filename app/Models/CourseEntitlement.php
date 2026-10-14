<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEntitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'course_id',
        'user_id',
        'payment_id',
        'amount',
        'currency',
    ];

    protected $attributes = [
        'amount' => 0,
        'currency' => 'VND',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount, 0, ',', '.').' '.$this->currency;
    }
}
