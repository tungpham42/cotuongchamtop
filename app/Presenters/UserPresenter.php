<?php

namespace App\Presenters;

use App\Models\User;
use App\Models\Room;
use App\Models\Session as DbSession;
use App\Actions\User\CalculateUserStats;
use App\Support\GemsTier;
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
     * Renders the Karma/Gems decoration badge for a user — a small icon
     * whose look (icon + color) reflects the tier unlocked by their
     * current gems total (see App\Support\GemsTier). For standalone use
     * (e.g. a profile header, a leaderboard column) where only the badge
     * is wanted without the avatar frame; renderPlayerName() renders both.
     */
    public function renderGemsDecoration(?int $userId): string
    {
        if (!$userId) {
            return '';
        }

        $user = User::find($userId);
        if (!$user) {
            return '';
        }

        return $this->buildGemsBadge($user);
    }

    /**
     * Builds the avatar image wrapped in a ring framed/colored per the
     * user's GemsTier — thicker and more glowing at higher tiers. This is
     * what renderPlayerName() uses instead of a bare <img>.
     */
    public function renderAvatar(User $user, int $dimension, int $fontSize): string
    {
        $avatarSrc = $user->profile_picture
            ? asset('storage/' . $user->profile_picture)
            : Avatar::create($user->name)->setDimension($dimension)->setFontSize($fontSize);

        /** @var GemsTier $tier */
        $tier = $user->gems_tier;
        $thickness = $tier->frameThickness();
        $innerRadius = 4;
        $outerRadius = $innerRadius + $thickness;
        $glow = $tier->frameGlow();

        $frameStyle = 'display: inline-block;'
            . ' line-height: 0;'
            . ' padding: ' . $thickness . 'px;'
            . ' border-radius: ' . $outerRadius . 'px;'
            . ' background: ' . $tier->frameBackground() . ';'
            . ($glow ? ' box-shadow: ' . $glow . ';' : '');

        $title = $tier->label() . ' · ' . $user->gems . ' ' . __('karma');

        return '<span class="avatar-frame ' . $tier->frameCssClass() . '" data-user-id="' . $user->id . '" data-tier="' . $tier->value . '" title="' . e($title) . '" style="' . $frameStyle . '">'
            . '<img src="' . $avatarSrc . '" style="display: block; width: ' . $dimension . 'px; height: ' . $dimension . 'px; object-fit: cover; border-radius: ' . $innerRadius . 'px;" />'
            . '</span>';
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

        $avatar = $this->renderAvatarWithGemsBadge($user, $dimension, $fontSize);
        $profileLink = localized_url('app.player', ['id' => $userId]);

        $nameText = $isProfile ? $user->name : '# ' . $userId . '  ' . $user->name;
        $linkClass = $forRoom ? 'text-light showPromotion animate-light' : 'text-danger showPromotion animate';
        if ($isProfile) $linkClass = 'text-light showPromotion animate-light';

        return $onlineStatus . '&nbsp;' . $avatar . '&nbsp;<a class="'.$linkClass.'" href="' . $profileLink . '">' . $nameText . '</a>';
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

    /**
     * Wraps renderAvatar()'s framed avatar with the gems badge overlaid as
     * a chip on the bottom-right corner — a white backing circle with a
     * tier-colored ring keeps the icon legible and prominent against any
     * avatar image, sized proportional to the avatar.
     */
    private function renderAvatarWithGemsBadge(User $user, int $dimension, int $fontSize): string
    {
        $avatar = $this->renderAvatar($user, $dimension, $fontSize);
        $gemsBadge = $this->buildGemsBadge($user);

        /** @var GemsTier $tier */
        $tier = $user->gems_tier;

        $badgeSize = max(18, (int) round($dimension * 0.58));
        $iconSize = max(10, (int) round($badgeSize * 0.62));

        $badgeStyle = 'position: absolute;'
            . ' bottom: -4px;'
            . ' right: -4px;'
            . ' width: ' . $badgeSize . 'px;'
            . ' height: ' . $badgeSize . 'px;'
            . ' display: flex;'
            . ' align-items: center;'
            . ' justify-content: center;'
            . ' background: #fff;'
            . ' border: 2px solid ' . $tier->color() . ';'
            . ' border-radius: 50%;'
            . ' box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.15), 0 2px 5px rgba(0, 0, 0, 0.45);'
            . ' font-size: ' . $iconSize . 'px;'
            . ' line-height: 1;';

        return '<span style="position: relative; display: inline-block; line-height: 0; vertical-align: middle;">'
            . $avatar
            . '<span style="' . $badgeStyle . '">' . $gemsBadge . '</span>'
            . '</span>';
    }

    /**
     * Small tier badge markup, shared by renderGemsDecoration() and
     * anywhere else that wants the icon without the avatar frame.
     */
    private function buildGemsBadge(User $user): string
    {
        /** @var GemsTier $tier */
        $tier = $user->gems_tier;
        $title = $tier->label() . ' · ' . $user->gems . ' ' . __('karma');

        return '<span class="gems-decoration gems-decoration--' . $tier->value . '" data-user-id="' . $user->id . '" title="' . e($title) . '">'
            . '<i class="fad ' . $tier->icon() . '" style="color: ' . $tier->color() . ';"></i>'
            . '</span>';
    }
}
