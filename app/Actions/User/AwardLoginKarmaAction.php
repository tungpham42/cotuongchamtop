<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\KarmaLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AwardLoginKarmaAction
{
    private const LOGIN_KARMA = 1;
    private const REASON = 'every_login';

    public function execute(User $user): array
    {
        DB::transaction(function () use ($user) {
            $user->increment('karma', self::LOGIN_KARMA);

            KarmaLog::create([
                'user_id' => $user->id,
                'amount' => self::LOGIN_KARMA,
                'reason' => self::REASON,
            ]);
        });

        $reward = [
            [
                'amount' => self::LOGIN_KARMA,
                'reason' => self::REASON,
                'label' => KarmaLog::reasonLabel(self::REASON),
            ],
        ];

        Session::flash('karma_earned', $reward);

        return $reward;
    }
}
