<?php

namespace App\Support;

/**
 * Decoration tier derived from a user's total Gems (the sum of their
 * KarmaLog amounts, see User::getGemsAttribute()).
 *
 * Each tier drives two visual pieces:
 *  - a small badge icon (icon() + color()) — see UserPresenter::renderGemsDecoration()
 *  - an avatar frame (frameThickness()/frameBackground()/frameGlow()) — see
 *    UserPresenter::renderAvatar(), which wraps the player's avatar image
 *    in a colored/gradient ring that gets thicker and glows more at higher
 *    tiers.
 *
 * Thresholds are deliberately generous at the bottom (karma accrues
 * slowly from things like every_login / match_played / match_win) and
 * compress toward the top so the highest badges stay meaningful.
 */
enum GemsTier: string
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';
    case Diamond = 'diamond';
    case Legendary = 'legendary';

    /**
     * Resolve the tier for a given amount of gems.
     */
    public static function fromGems(int $gems): self
    {
        return match (true) {
            $gems >= 1000 => self::Legendary,
            $gems >= 400  => self::Diamond,
            $gems >= 200  => self::Platinum,
            $gems >= 75   => self::Gold,
            $gems >= 20   => self::Silver,
            default       => self::Bronze,
        };
    }

    /**
     * Minimum gems required to reach this tier.
     */
    public function minGems(): int
    {
        return match ($this) {
            self::Bronze    => 0,
            self::Silver    => 20,
            self::Gold      => 75,
            self::Platinum  => 200,
            self::Diamond   => 400,
            self::Legendary => 1000,
        };
    }

    /**
     * Gems needed to reach the next tier, or null if already at the top.
     */
    public function nextTier(): ?self
    {
        return match ($this) {
            self::Bronze    => self::Silver,
            self::Silver    => self::Gold,
            self::Gold      => self::Platinum,
            self::Platinum  => self::Diamond,
            self::Diamond   => self::Legendary,
            self::Legendary => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Bronze    => __('Đồng'),
            self::Silver    => __('Bạc'),
            self::Gold      => __('Vàng'),
            self::Platinum  => __('Bạch Kim'),
            self::Diamond   => __('Kim Cương'),
            self::Legendary => __('Huyền Thoại'),
        };
    }

    /**
     * Font Awesome Duotone icon suffix for the small badge (project already
     * loads the `fad` set, see UserPresenter::renderOnlineStatusIndicator).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Bronze    => 'fa-shield',
            self::Silver    => 'fa-shield-alt',
            self::Gold      => 'fa-award',
            self::Platinum  => 'fa-gem',
            self::Diamond   => 'fa-gem',
            self::Legendary => 'fa-crown',
        };
    }

    /**
     * Flat accent color used by the small badge icon.
     */
    public function color(): string
    {
        return match ($this) {
            self::Bronze    => '#a8763e',
            self::Silver    => '#9aa4ad',
            self::Gold      => '#e6b800',
            self::Platinum  => '#5bc0de',
            self::Diamond   => '#7b3fe4',
            self::Legendary => '#ff4d4f',
        };
    }

    /**
     * Ring thickness (px) for the avatar frame. Grows with tier so higher
     * ranks are recognizable at a glance even before reading the tooltip.
     */
    public function frameThickness(): int
    {
        return match ($this) {
            self::Bronze, self::Silver     => 2,
            self::Gold, self::Platinum     => 3,
            self::Diamond, self::Legendary => 4,
        };
    }

    /**
     * CSS `background` value for the avatar frame ring. Lower tiers get a
     * flat color; Gold and up get a gradient so the frame reads as a more
     * premium material.
     */
    public function frameBackground(): string
    {
        return match ($this) {
            self::Bronze    => '#a8763e',
            self::Silver    => '#9aa4ad',
            self::Gold      => 'linear-gradient(135deg, #f5d16b, #e6b800)',
            self::Platinum  => 'linear-gradient(135deg, #bdeaf7, #5bc0de)',
            self::Diamond   => 'linear-gradient(135deg, #b57bff, #7b3fe4)',
            self::Legendary => 'linear-gradient(135deg, #ff4d4f, #ffb347, #ff4d4f)',
        };
    }

    /**
     * CSS `box-shadow` value giving the frame a glow, or null for tiers
     * that shouldn't glow (Bronze/Silver — kept understated on purpose).
     */
    public function frameGlow(): ?string
    {
        return match ($this) {
            self::Bronze, self::Silver => null,
            self::Gold      => '0 0 6px rgba(230, 184, 0, 0.6)',
            self::Platinum  => '0 0 8px rgba(91, 192, 222, 0.7)',
            self::Diamond   => '0 0 10px rgba(123, 63, 228, 0.8)',
            self::Legendary => '0 0 14px rgba(255, 77, 79, 0.85)',
        };
    }

    /**
     * BEM-style hook for a stylesheet to add extras inline styles can't do
     * (e.g. a slow rotating gradient or shimmer keyframe on Legendary).
     * Purely a class name — no behavior is implied if left unstyled.
     */
    public function frameCssClass(): string
    {
        return 'avatar-frame--' . $this->value;
    }
}
