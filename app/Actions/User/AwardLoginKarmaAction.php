<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\KarmaLog;
use Illuminate\Support\Facades\DB;

class AwardLoginKarmaAction
{
    private const DAILY_LOGIN_KARMA = 1;

    public function execute(User $user): void
    {
        $alreadyAwardedToday = KarmaLog::where('user_id', $user->id)
            ->where('reason', 'daily_login')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyAwardedToday) {
            return;
        }

        DB::transaction(function () use ($user) {
            $user->increment('karma', self::DAILY_LOGIN_KARMA);

            KarmaLog::create([
                'user_id' => $user->id,
                'amount' => self::DAILY_LOGIN_KARMA,
                'reason' => 'daily_login',
            ]);
        });
    }
}
