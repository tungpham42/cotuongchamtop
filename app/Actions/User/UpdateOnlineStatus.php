<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\Session as DbSession;
use Carbon\Carbon;

class UpdateOnlineStatus
{
    public function execute(int $userId): void
    {
        DbSession::where('user_id', $userId)->update(['last_activity' => time()]);

        User::updateOrInsert(
            ['id' => $userId],
            ['last_seen_at' => Carbon::now()]
        );
    }
}
