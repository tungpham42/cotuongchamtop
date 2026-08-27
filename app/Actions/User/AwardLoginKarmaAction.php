<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\KarmaLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AwardLoginKarmaAction
{
    private const DAILY_LOGIN_KARMA = 1;
    private const REASON = 'daily_login';

    public function execute(User $user): void
    {
        $alreadyAwardedToday = KarmaLog::where('user_id', $user->id)
            ->where('reason', self::REASON)
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
                'reason' => self::REASON,
            ]);
        });

        // Flashed once, read by the layout on the very next page load
        // (right after the login redirect) to pop a bootbox notification.
        Session::flash('karma_earned', [
            [
                'amount' => self::DAILY_LOGIN_KARMA,
                'reason' => self::REASON,
                'label' => KarmaLog::reasonLabel(self::REASON),
            ],
        ]);
    }
}
