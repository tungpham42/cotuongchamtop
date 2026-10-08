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
 * Design rules (so tiers never blur together):
 *  - Every tier owns a distinct hue: copper, steel, gold, cyan, violet, fire.
 *  - Every tier owns a distinct icon silhouette (shield, star, medal,
 *    trophy, gem, crown), so rank is readable even without color.
 *  - Material gets richer going up: metallic gradient -> animated shimmer
 *    -> pulsing sparkle -> rotating fire ring (see gems-tier.css).
 *  - Ring thickness and glow radius both grow with tier.
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
            $gems >= 500 => self::Legendary,
            $gems >= 200  => self::Diamond,
            $gems >= 100  => self::Platinum,
            $gems >= 50   => self::Gold,
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
            self::Gold      => 50,
            self::Platinum  => 100,
            self::Diamond   => 200,
            self::Legendary => 500,
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
     *
     * One unique silhouette per tier: shield -> star -> medal -> trophy ->
     * gem -> crown.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Bronze    => 'fa-shield',
            self::Silver    => 'fa-star',
            self::Gold      => 'fa-medal',
            self::Platinum  => 'fa-trophy',
            self::Diamond   => 'fa-gem',
            self::Legendary => 'fa-crown',
        };
    }

    /**
     * Flat accent color used by the small badge icon. Mid-tone on purpose
     * so each reads on both light and dark backgrounds.
     */
    public function color(): string
    {
        return match ($this) {
            self::Bronze    => '#cd7f32', // copper
            self::Silver    => '#8e9bab', // cool steel
            self::Gold      => '#ffb300', // rich amber gold
            self::Platinum  => '#14b8d4', // ice cyan
            self::Diamond   => '#a855f7', // vivid violet
            self::Legendary => '#ff3d2e', // fire red
        };
    }

    /**
     * Ring thickness (px) for the avatar frame. Strictly increasing past
     * Silver so higher ranks stand out at a glance.
     */
    public function frameThickness(): int
    {
        return match ($this) {
            self::Bronze    => 3,
            self::Silver    => 4,
            self::Gold      => 5,
            self::Platinum  => 5,
            self::Diamond   => 6,
            self::Legendary => 7,
        };
    }

    /**
     * CSS `background` value for the avatar frame ring.
     *
     *  - Bronze/Silver/Gold: multi-stop metallic gradients (dark-light-dark)
     *    that read as polished metal rather than a flat color.
     *  - Platinum: icy cyan sheen with white highlights.
     *  - Diamond: violet-to-magenta facets with a bright white "cut" stop.
     *  - Legendary: conic fire ring. It reads --gems-angle, which
     *    gems-tier.css animates to spin it; unstyled it stays a static ring.
     */
    public function frameBackground(): string
    {
        return match ($this) {
            self::Bronze    => 'linear-gradient(135deg, #e8b37a 0%, #cd7f32 40%, #8a4b17 70%, #d99a5b 100%)',
            self::Silver    => 'linear-gradient(135deg, #ffffff 0%, #b4c0cd 35%, #6f7d8c 65%, #e3e9ef 100%)',
            self::Gold      => 'linear-gradient(135deg, #fff3b0 0%, #ffcc1f 30%, #b87c00 62%, #ffe27a 100%)',
            self::Platinum  => 'linear-gradient(135deg, #f0fdff 0%, #67e8f9 30%, #0e8fb0 62%, #c8f6ff 100%)',
            self::Diamond   => 'linear-gradient(135deg, #f5e9ff 0%, #c084fc 25%, #7c3aed 50%, #e879f9 75%, #f5e9ff 100%)',
            self::Legendary => 'conic-gradient(from var(--gems-angle, 0deg), #ff1744, #ff9100, #ffea00, #ff9100, #ff1744)',
        };
    }

    /**
     * CSS `box-shadow` value giving the frame a glow. Radius and opacity
     * climb every tier; Diamond and Legendary use layered shadows (tight
     * bright core + wide soft halo) for a real "aura" look.
     */
    public function frameGlow(): string
    {
        return match ($this) {
            self::Bronze    => '0 0 3px rgba(205, 127, 50, 0.45)',
            self::Silver    => '0 0 5px rgba(142, 155, 171, 0.55)',
            self::Gold      => '0 0 8px rgba(255, 179, 0, 0.65)',
            self::Platinum  => '0 0 10px rgba(20, 184, 212, 0.75)',
            self::Diamond   => '0 0 6px rgba(232, 121, 249, 0.9), 0 0 16px rgba(168, 85, 247, 0.7)',
            self::Legendary => '0 0 8px rgba(255, 234, 0, 0.8), 0 0 20px rgba(255, 61, 46, 0.85), 0 0 34px rgba(255, 145, 0, 0.5)',
        };
    }

    /**
     * BEM-style hook for a stylesheet to add extras inline styles can't do.
     * See gems-tier.css: shimmer on Gold/Platinum, sparkle pulse on Diamond,
     * spinning fire ring on Legendary. Purely a class name — nothing breaks
     * if left unstyled.
     */
    public function frameCssClass(): string
    {
        return 'avatar-frame--' . $this->value;
    }
}
