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

    /**
     * Render player name (supports registered users, guest accounts, or waiting state)
     */
    public function renderPlayerName(int|string|null $userId, bool $forRoom = false, bool $isProfile = false): string
    {
        // 1. If no user ID is present, render the waiting indicator
        if (empty($userId)) {
            $bg = $forRoom ? 'bg-light' : 'bg-danger';
            return '<span class="waitingIndicator">'
                . '<span class="indicator '.$bg.'"></span>'
                . '<span class="indicator '.$bg.'"></span>'
                . '<span class="indicator '.$bg.'"></span>'
                . '<span class="indicator '.$bg.'"></span>'
                . '<span class="indicator '.$bg.'"></span>'
                . '</span>';
        }

        $dimension = $forRoom ? 28 : 38;
        $fontSize = $forRoom ? 14 : 19;
        $linkClass = $forRoom ? 'text-light showPromotion animate-light' : 'text-danger showPromotion animate';

        // 2. Handle Non-Logged In / Guest Players (e.g., "guest_123", "guest_456")
        if (is_string($userId) && (str_starts_with($userId, 'guest_') || !is_numeric($userId))) {
            $formattedGuestName = ucfirst(str_replace('_', ' ', $userId)); // e.g., "Guest 123"
            $avatarSrc = Avatar::create($formattedGuestName)->setDimension($dimension)->setFontSize($fontSize);

            return '<span class="user-status-indicator"><i title="' . __('Khách') . '" class="text-secondary fad fa-circle"></i></span>&nbsp;'
                . '<img src="' . $avatarSrc . '" style="width: '.$dimension.'px; height: '.$dimension.'px; object-fit: cover; border-radius: 4px;" />&nbsp;'
                . '<span class="' . $linkClass . '">' . e($formattedGuestName) . '</span>';
        }

        // 3. Handle Registered Users
        $user = User::find($userId);

        // Fallback if numeric ID was provided but user record was not found
        if (!$user) {
            $guestLabel = 'Guest #' . $userId;
            $avatarSrc = Avatar::create($guestLabel)->setDimension($dimension)->setFontSize($fontSize);

            return '<span class="user-status-indicator"><i title="' . __('Khách') . '" class="text-secondary fad fa-circle"></i></span>&nbsp;'
                . '<img src="' . $avatarSrc . '" style="width: '.$dimension.'px; height: '.$dimension.'px; object-fit: cover; border-radius: 4px;" />&nbsp;'
                . '<span class="' . $linkClass . '">' . e($guestLabel) . '</span>';
        }

        $onlineStatus = $this->renderOnlineStatusIndicator((int) $userId);
        $avatarSrc = $user->profile_picture
            ? asset('storage/' . $user->profile_picture)
            : Avatar::create($user->name)->setDimension($dimension)->setFontSize($fontSize);

        $profileLink = localized_url('app.player', ['id' => $userId]);
        $nameText = $isProfile ? $user->name : '# ' . $userId . '  ' . $user->name;

        if ($isProfile) {
            $linkClass = 'text-light showPromotion animate-light';
        }

        return $onlineStatus . '&nbsp;<img src="' . $avatarSrc . '" style="width: '.$dimension.'px; height: '.$dimension.'px; object-fit: cover; border-radius: 4px;" />&nbsp;<a class="'.$linkClass.'" href="' . $profileLink . '">' . e($nameText) . '</a>';
    }

    public function renderPlayersTitle(string $roomCode): string
    {
        // Add the anonymous player columns to the selection
        $room = Room::select('host_id', 'guest_id', 'host_session', 'guest_session', 'anonymous_red_id', 'anonymous_black_id')
            ->where('code', $roomCode)
            ->first();

        if ($room) {
            // Coalesce through the available identifiers
            $hostToRender = $room->host_id ?? $room->host_session ?? $room->anonymous_red_id;
            $guestToRender = $room->guest_id ?? $room->guest_session ?? $room->anonymous_black_id;

            return '<span class="host-title">' . $this->renderPlayerName($hostToRender, true) . '</span> <span class="guest-title">' . $this->renderPlayerName($guestToRender, true) . '</span>';
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
