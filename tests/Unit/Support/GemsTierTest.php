<?php

namespace Tests\Unit\Support;

use App\Support\GemsTier;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GemsTierTest extends TestCase
{
    public static function tierThresholdProvider(): array
    {
        return [
            'bronze minimum' => [0, GemsTier::Bronze],
            'silver boundary' => [20, GemsTier::Silver],
            'gold boundary' => [50, GemsTier::Gold],
            'platinum boundary' => [100, GemsTier::Platinum],
            'diamond boundary' => [200, GemsTier::Diamond],
            'legendary boundary' => [500, GemsTier::Legendary],
        ];
    }

    #[DataProvider('tierThresholdProvider')]
    public function test_gems_threshold_maps_to_expected_tier(int $gems, GemsTier $expected): void
    {
        $this->assertSame($expected, GemsTier::fromGems($gems));
    }

    public function test_gems_below_silver_are_bronze(): void
    {
        $this->assertSame(GemsTier::Bronze, GemsTier::fromGems(19));
    }

    public function test_gems_above_legendary_remain_legendary(): void
    {
        $this->assertSame(GemsTier::Legendary, GemsTier::fromGems(10000));
    }

    public function test_tier_minimums_are_consistent_with_thresholds(): void
    {
        foreach (GemsTier::cases() as $tier) {
            $this->assertSame($tier, GemsTier::fromGems($tier->minGems()));
        }
    }

    public function test_next_tier_progression_is_correct(): void
    {
        $this->assertSame(GemsTier::Silver, GemsTier::Bronze->nextTier());
        $this->assertSame(GemsTier::Gold, GemsTier::Silver->nextTier());
        $this->assertSame(GemsTier::Platinum, GemsTier::Gold->nextTier());
        $this->assertSame(GemsTier::Diamond, GemsTier::Platinum->nextTier());
        $this->assertSame(GemsTier::Legendary, GemsTier::Diamond->nextTier());
        $this->assertNull(GemsTier::Legendary->nextTier());
    }

    public function test_frame_css_class_is_stable(): void
    {
        $this->assertSame('avatar-frame--bronze', GemsTier::Bronze->frameCssClass());
        $this->assertSame('avatar-frame--legendary', GemsTier::Legendary->frameCssClass());
    }
}
