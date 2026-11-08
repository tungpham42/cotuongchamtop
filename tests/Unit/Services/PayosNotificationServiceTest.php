<?php

namespace Tests\Unit\Services;

use App\Models\PayosPayment;
use App\Models\User;
use App\Services\PayosNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PayosNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_notification_for_pending_payment()
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Nguyễn Văn Test',
            'email' => 'test@example.com',
        ]);

        $payment = PayosPayment::create([
            'user_id' => $user->id,
            'order_code' => 123456789,
            'amount' => 49000,
            'status' => PayosPayment::STATUS_PENDING,
            'description' => 'VIP 1 Thang-Test',
            'checkout_url' => 'https://pay.payos.vn/web/test',
            'meta' => [
                'plan' => 'vip_month',
            ],
        ]);

        $service = new PayosNotificationService();
        $result = $service->notify($payment, PayosPayment::STATUS_PENDING);

        $this->assertTrue($result);

        $payment->refresh();
        $this->assertContains(PayosPayment::STATUS_PENDING, $payment->meta['notified_statuses'] ?? []);
    }

    public function test_it_does_not_duplicate_notification_for_same_status()
    {
        Mail::fake();

        $user = User::factory()->create();

        $payment = PayosPayment::create([
            'user_id' => $user->id,
            'order_code' => 987654321,
            'amount' => 19000,
            'status' => PayosPayment::STATUS_PENDING,
            'description' => 'VIP 1 Tuan',
            'meta' => [
                'plan' => 'vip_week',
                'notified_statuses' => [PayosPayment::STATUS_PENDING],
            ],
        ]);

        $service = new PayosNotificationService();
        $result = $service->notify($payment, PayosPayment::STATUS_PENDING);

        // Should return false because pending was already notified
        $this->assertFalse($result);

        // But notifying CANCELLED should succeed
        $cancelledResult = $service->notify($payment, PayosPayment::STATUS_CANCELLED);
        $this->assertTrue($cancelledResult);

        $payment->refresh();
        $this->assertContains(PayosPayment::STATUS_CANCELLED, $payment->meta['notified_statuses']);
    }

    public function test_handle_cancel_endpoint_triggers_cancelled_notification()
    {
        Mail::fake();

        $user = User::factory()->create();

        $payment = PayosPayment::create([
            'user_id' => $user->id,
            'order_code' => 555666777,
            'amount' => 199000,
            'status' => PayosPayment::STATUS_PENDING,
            'description' => 'VIP Tron Doi',
            'meta' => [
                'plan' => 'standard',
            ],
        ]);

        $response = $this->get(route('payos.cancel', ['orderCode' => 555666777]));

        $response->assertRedirect();

        $payment->refresh();
        $this->assertEquals(PayosPayment::STATUS_CANCELLED, $payment->status);
        $this->assertContains(PayosPayment::STATUS_CANCELLED, $payment->meta['notified_statuses'] ?? []);
    }
}
