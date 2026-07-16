<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public static function log(
        string $type,
        $subject,
        string $description,
        array $properties = []
    ): ActivityLog {
        $request = request();

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'subject_type' => is_object($subject) ? get_class($subject) : $subject,
            'subject_id' => is_object($subject) ? $subject->getKey() : null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public static function getForSubject($subject, int $limit = 50)
    {
        $type = is_object($subject) ? get_class($subject) : $subject;
        $id = is_object($subject) ? $subject->getKey() : null;

        return ActivityLog::forSubject($type, $id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public static function getRecent(int $limit = 50)
    {
        return ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public static function getUserActivity(int $userId, int $limit = 50)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
