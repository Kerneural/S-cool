<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Validation\ValidationException;

class EventWallTime
{
    /** Resolve a wall time only when exactly one UTC instant matches it. */
    public static function toUtc(string $value, string $timezone, string $field): Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/D', $value)) {
            throw ValidationException::withMessages([$field => 'Enter a local date and time without an offset.']);
        }
        $wall = str_replace('T', ' ', $value);
        if (strlen($wall) === 16) {
            $wall .= ':00';
        }
        $nominal = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $wall, new DateTimeZone('UTC'));
        if (! $nominal || $nominal->format('Y-m-d H:i:s') !== $wall) {
            throw ValidationException::withMessages([$field => 'Enter a valid date and time.']);
        }

        $zone = new DateTimeZone($timezone);
        $timestamp = $nominal->getTimestamp();
        $transitions = $zone->getTransitions($timestamp - 172800, $timestamp + 172800);
        $offsets = $transitions === false ? [$zone->getOffset($nominal)] : array_unique(array_column($transitions, 'offset'));
        $matches = [];
        foreach ($offsets as $offset) {
            $candidate = Carbon::createFromTimestampUTC($timestamp - $offset);
            if ($candidate->copy()->setTimezone($zone)->format('Y-m-d H:i:s') === $wall) {
                $matches[$candidate->getTimestamp()] = $candidate;
            }
        }
        if (count($matches) !== 1) {
            throw ValidationException::withMessages([$field => 'This local time is missing or ambiguous due to a timezone transition. Choose another time.']);
        }

        return array_values($matches)[0];
    }
}
