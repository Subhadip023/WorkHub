<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

if (! function_exists('log_activity')) {
    /**
     * Record a system or user activity log with observation metadata.
     *
     * @param  array<string, mixed>  $properties
     * @param  Authenticatable|Model|null  $causer
     */
    function log_activity(
        string $description,
        string $event = 'custom',
        ?Model $subject = null,
        array $properties = [],
        mixed $causer = null
    ): ?Activity {
        $causer = $causer ?? auth()->user();

        if (! $causer) {
            return null;
        }

        $observationProperties = array_merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ], $properties);

        $activityLogger = activity()->event($event);

        if ($subject) {
            $activityLogger->performedOn($subject);
        }

        $activityLogger->causedBy($causer);

        /** @var Activity $activity */
        $activity = $activityLogger
            ->withProperties($observationProperties)
            ->log($description);

        return $activity;
    }
}
