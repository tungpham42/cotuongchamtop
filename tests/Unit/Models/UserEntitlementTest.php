<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class UserEntitlementTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_without_standard_subscription_is_not_standard(): void
    {
        $user = new User([
            'subscription_plan' => 'free',
        ]);

        $this->assertFalse($user->isStandard());
    }

    public function test_standard_subscription_without_expiry_is_active(): void
    {
        $user = new User([
            'subscription_plan' => 'standard',
            'subscription_ends_at' => null,
        ]);

        $this->assertTrue($user->isStandard());
    }

    public function test_expired_standard_subscription_is_not_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));

        $user = new User([
            'subscription_plan' => 'standard',
            'subscription_ends_at' => now()->subSecond(),
        ]);

        $this->assertFalse($user->isStandard());
    }

    public function test_future_standard_subscription_is_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));

        $user = new User([
            'subscription_plan' => 'standard',
            'subscription_ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($user->isStandard());
    }

    public function test_explicit_ads_removed_flag_grants_ad_free_access(): void
    {
        $user = new User([
            'ads_removed' => true,
            'subscription_plan' => 'free',
        ]);

        $this->assertTrue($user->hasAdsRemoved());
    }

    public function test_active_standard_subscription_grants_ad_free_access(): void
    {
        $user = new User([
            'ads_removed' => false,
            'subscription_plan' => 'standard',
            'subscription_ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($user->hasAdsRemoved());
    }
}
