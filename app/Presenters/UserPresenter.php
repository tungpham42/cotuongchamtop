<?php

namespace App\Presenters;

use App\Models\User;
use App\Models\Room;
use App\Models\Session as DbSession;
use App\Actions\User\CalculateUserStats;
use Avatar;
use Illuminate\Support\Facades\Cache;

class UserPresenter
{
    public function __construct(private CalculateUserStats $statsAction) {}

    public function renderOnlinePlayersCount(): string
    {
        $onlinePlayers = DbSession::whereNotNull('user_id')->pluck('user_id')->unique()->count();
        return trans_choice('messages.players_online_count', $onlinePlayers, ['count' => $onlinePlayers]);
    }

    public function renderOnlineStatusIndicator(?int $userId): string
    {
        if (!$userId) {
            return '';
        }

        $hasActiveSession = DbSession::where('user_id', $userId)->exists();
        $statusClass = $hasActiveSession ? 'text-success' : 'text-danger';
        $statusTitle = $hasActiveSession ? __('Trực tuyến') : __('Ngoại tuyến');

        return '<span class="user-status-indicator" data-user-id="' . $userId . '"> <i title="' . $statusTitle . '" class="' . $statusClass . ' fad fa-circle"></i></span>';
    }

    public function renderPlayerName(?int $userId, bool $forRoom = false, bool $isProfile = false): string
    {
        $user = User::find($userId);

        // If $userId is null or user doesn't exist, return the waiting indicator
        if (!$user) {
            $bg = $forRoom ? 'bg-light' : 'bg-danger';
            return '<span class="waitingIndicator"><span class="indicator '.$bg.'"></span><span class="indicator '.$bg.'"></span><span class="indicator '.$bg.'"></span><span class="indicator '.$bg.'"></span><span class="indicator '.$bg.'"></span></span>';
        }

        $onlineStatus = $this->renderOnlineStatusIndicator($userId);
        $dimension = $forRoom ? 28 : 38;
        $fontSize = $forRoom ? 14 : 19;

        $avatarSrc = $user->profile_picture ? asset('storage/' . $user->profile_picture) : Avatar::create($user->name)->setDimension($dimension)->setFontSize($fontSize);
        $profileLink = localized_url('app.player', ['id' => $userId]);

        $nameText = $isProfile ? $user->name : '# ' . $userId . '  ' . $user->name;
        $linkClass = $forRoom ? 'text-light showPromotion animate-light' : 'text-danger showPromotion animate';
        if ($isProfile) $linkClass = 'text-light showPromotion animate-light';

        return $onlineStatus . '&nbsp;<img src="' . $avatarSrc . '" style="width: '.$dimension.'px; height: '.$dimension.'px; object-fit: cover; border-radius: 4px;" />&nbsp;<a class="'.$linkClass.'" href="' . $profileLink . '">' . $nameText . '</a>';
    }

    public function renderPlayersTitle(string $roomCode): string
    {
        $room = Room::select('host_id', 'guest_id')->where('code', $roomCode)->first();
        if ($room) {
            return '<span class="host-title">' . $this->renderPlayerName($room->host_id, true) . '</span> <span class="guest-title">' . $this->renderPlayerName($room->guest_id, true) . '</span>';
        }
        return '';
    }

    public function renderUserRank(?int $userId): ?int
    {
        if (!$userId) return null;

        $user = User::find($userId);
        if (!$user) return null;

        $rank = User::where('elo', '>', ceil($user->elo))->count() + 1;
        return $rank == User::count() + 1 ? User::count() : $rank;
    }

    public function renderElo(?int $userId): ?int
    {
        if (!$userId) return null;

        $user = User::find($userId);
        return $user ? ceil($user->elo) : null;
    }

    public function renderStat(?int $userId, string $type): int
    {
        if (!$userId) return 0;

        $this->statsAction->updatePoints($userId);
        $stats = $this->statsAction->getMatchStats($userId);
        return $stats[$type] ?? 0;
    }

    public function renderPlayerEmail(?int $userId): string
    {
        if (!$userId) return '';

        $user = User::find($userId);
        return $user ? '<a class="text-danger showPromotion animate" href="mailto:'.$user->email.'">'.$user->email.'</a>' : '';
    }

    public function renderPlayerRank(?int $userId): string
    {
        if (!$userId) return '';

        $user = User::find($userId);
        if (!$user) {
            return '';
        }

        $rank = User::where('elo', '>', ceil($user->elo))->count() + 1;
        $totalUsers = User::count();

        if ($rank > $totalUsers) {
            $rank = $totalUsers;
        }

        return $rank . '/' . $totalUsers;
    }
}
