<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\Session as DbSession;
use Carbon\Carbon;

class UpdateUserStatusAction
{
    public function execute(int $id)
    {
        DbSession::where('user_id', $id)->update(['last_activity' => time()]);

        User::updateOrInsert(
            ['id' => $id],
            ['last_seen_at' => Carbon::now()]
        );
    }
}
