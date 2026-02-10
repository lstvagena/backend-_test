<?php

namespace App\Helpers;

use App\Models\UserActivityLogFile;
use App\Models\Utilities\SystemParameter\SysSetup;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Request;

class UserActivityHelper
{
    public static function record(array $activity)
    {
        $user = null;
        if (Request::hasCookie('auth_token')) {
    $personalAccessToken = PersonalAccessToken::findToken(Request::cookie('auth_token'));

    if ($personalAccessToken) {
        $user = $personalAccessToken->tokenable;
    }
} elseif (isset($activity['user'])) {
    $user = $activity['user'];
}


        if (!$user) {
            return null;
        }

        $record = UserActivityLogFile::create([
            'user_id' => $user->user_id,
            'event' => $activity['event'],
            'auditable_type' => $activity['auditable_type'],
          //  'auditable_id' => $activity['auditable_id'] ?? null,
            'old_value' => $activity['old_value'],
            'new_value' => $activity['new_value'],
            'url' => Request::url(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'remarks' => $activity['remarks'],
            'module_name' => $activity['auditable_type']
        ]);
        self::applyLogLimit();
        return $record;
    }

    private static function applyLogLimit()
    {
        $maximumActivityLogs = 1000;
        $lastRecordId = UserActivityLogFile::orderByDesc('id')->limit(1)->value('id');
        if ($lastRecordId && $lastRecordId > $maximumActivityLogs) {
            $recordsToDelete = $lastRecordId - $maximumActivityLogs;
            UserActivityLogFile::where('id', '<=', $recordsToDelete)->delete();
        }
    }
}
