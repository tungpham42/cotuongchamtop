<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\KarmaLog;
use Illuminate\Support\Facades\DB;

class AwardMatchWinKarmaAction
{
    private const REASON = 'match_win';
    private const KARMA_AMOUNT = 3;

    /**
     * Award karma to a user for winning a match (room).
     *
     * @param  User  $user
     * @param  int   $roomId  The id of the room/match this award is for.
     */
    public function execute(User $user, int $roomId): void
    {
        $alreadyAwarded = KarmaLog::where('user_id', $user->id)
            ->where('reason', self::REASON)
            ->where('reference_id', $roomId)
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        DB::transaction(function () use ($user, $roomId) {
            $user->increment('karma', self::KARMA_AMOUNT);

            KarmaLog::create([
                'user_id' => $user->id,
                'amount' => self::KARMA_AMOUNT,
                'reason' => self::REASON,
                'reference_id' => $roomId,
            ]);
        });
    }
}
