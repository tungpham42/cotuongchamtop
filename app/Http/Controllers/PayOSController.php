<?php

namespace App\Http\Controllers;

use App\Models\PayosPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PayOS\Exceptions\WebhookException;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\Models\V2\PaymentRequests\PaymentLinkStatus;
use PayOS\PayOS;

class PayOSController extends Controller
{
    public function createStandard(Request $request)
    {
        $user = $request->user();

        if ($user->isStandard()) {
            return redirect('/ho-so-cua-toi')->with('success', 'Bạn đã ở gói Standard (ẩn quảng cáo).');
        }

        try {
            $client = $this->makeClient();
        } catch (\Throwable $e) {
            Log::error('PayOS configuration error', ['message' => $e->getMessage()]);

            return redirect('/ho-so-cua-toi')->withErrors([
                'message' => 'Không thể kết nối PayOS. Vui lòng kiểm tra PAYOS_CLIENT_ID/API_KEY/CHECKSUM_KEY.',
            ]);
        }

        $orderCode = $this->generateOrderCode();
        $baseDescription = 'Standard-' . ($user->email ?: $user->name);
        $description = Str::limit($baseDescription, 25, ''); // PayOS giới hạn 25 ký tự

        try {
            $paymentRequest = new CreatePaymentLinkRequest(
                orderCode: $orderCode,
                amount: config('payos.standard_amount'),
                description: $description,
                returnUrl: config('payos.return_url'),
                cancelUrl: config('payos.cancel_url')
            );

            $link = $client->paymentRequests->create($paymentRequest);
        } catch (\Throwable $e) {
            Log::error('PayOS create payment failed', [
                'order_code' => $orderCode,
                'error' => $e->getMessage(),
            ]);

            return redirect('/ho-so-cua-toi')->withErrors([
                'message' => 'Không tạo được liên kết thanh toán, vui lòng thử lại sau.',
            ]);
        }

        PayosPayment::create([
            'user_id' => $user->id,
            'order_code' => $orderCode,
            'amount' => config('payos.standard_amount'),
            'status' => PayosPayment::STATUS_PENDING,
            'description' => $description,
            'payment_link_id' => $link->paymentLinkId ?? null,
            'checkout_url' => $link->checkoutUrl ?? null,
            'meta' => [
                'status' => $link->status->value ?? null,
                'qr_code' => $link->qrCode ?? null,
            ],
        ]);

        return redirect()->away($link->checkoutUrl);
    }

    public function handleReturn(Request $request)
    {
        $orderCode = $request->get('orderCode');

        if (!$orderCode) {
            return redirect('/ho-so-cua-toi')->withErrors([
                'message' => 'Không tìm thấy mã đơn hàng để kiểm tra.',
            ]);
        }

        $payment = PayosPayment::where('order_code', $orderCode)->first();

        if ($payment) {
            $payment = $this->syncPaymentStatus($payment);

            if ($payment->status === PayosPayment::STATUS_PAID) {
                return redirect('/ho-so-cua-toi')->with('success', 'Thanh toán thành công, gói Standard đã được kích hoạt.');
            }

            if ($payment->status === PayosPayment::STATUS_CANCELLED) {
                return redirect('/ho-so-cua-toi')->withErrors([
                    'message' => 'Giao dịch đã bị huỷ.',
                ]);
            }

            if ($payment->status === PayosPayment::STATUS_FAILED) {
                return redirect('/ho-so-cua-toi')->withErrors([
                    'message' => 'Thanh toán chưa thành công. Vui lòng thử lại.',
                ]);
            }

            return redirect('/ho-so-cua-toi')->with('status', 'Thanh toán đang được xác nhận. Vui lòng chờ trong giây lát.');
        }

        return redirect('/ho-so-cua-toi')->withErrors([
            'message' => 'Không tìm thấy giao dịch tương ứng.',
        ]);
    }

    public function handleCancel(Request $request)
    {
        $orderCode = $request->get('orderCode');

        if ($orderCode) {
            PayosPayment::where('order_code', $orderCode)->update([
                'status' => PayosPayment::STATUS_CANCELLED,
            ]);
        }

        return redirect('/ho-so-cua-toi')->withErrors([
            'message' => 'Bạn đã huỷ thanh toán Standard.',
        ]);
    }

    public function webhook(Request $request)
    {
        try {
            $client = $this->makeClient();
        } catch (\Throwable $e) {
            Log::error('PayOS configuration error', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'Configuration error'], 500);
        }

        try {
            $webhookData = $client->webhooks->verify($request->all());
        } catch (WebhookException $e) {
            Log::warning('PayOS webhook signature invalid', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Invalid signature'], 400);
        } catch (\Throwable $e) {
            Log::error('PayOS webhook unexpected error', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Unexpected error'], 500);
        }

        $payment = PayosPayment::where('order_code', $webhookData->orderCode ?? null)->first();

        if (!$payment) {
            Log::warning('PayOS webhook order not found', ['order_code' => $webhookData->orderCode ?? null]);

            return response()->json(['message' => 'Order not found'], 404);
        }

        if ((int) $webhookData->amount < $payment->amount) {
            $payment->update([
                'status' => PayosPayment::STATUS_FAILED,
                'meta' => $this->mergeMeta($payment, [
                    'webhook' => $request->all(),
                    'reason' => 'underpaid',
                ]),
            ]);

            return response()->json(['message' => 'Amount mismatch'], 400);
        }

        $this->markPaymentAsPaid(
            $payment,
            Carbon::parse($webhookData->transactionDateTime ?? now()),
            $webhookData->paymentLinkId ?? null,
            ['webhook' => $request->all()]
        );

        return response()->json(['message' => 'Webhook processed']);
    }

    private function generateOrderCode(): int
    {
        do {
            // PayOS yêu cầu order_code <= 9007199254740991, nên chỉ dùng timestamp (10 số) + 3 số ngẫu nhiên
            $candidate = (int) (now()->timestamp . random_int(100, 999));
        } while (PayosPayment::where('order_code', $candidate)->exists());

        return $candidate;
    }

    private function makeClient(): PayOS
    {
        return new PayOS(
            config('payos.client_id'),
            config('payos.api_key'),
            config('payos.checksum_key'),
            config('payos.partner_code')
        );
    }

    private function syncPaymentStatus(PayosPayment $payment): PayosPayment
    {
        if ($payment->status === PayosPayment::STATUS_PAID) {
            return $payment;
        }

        try {
            $client = $this->makeClient();
            $paymentLink = $client->paymentRequests->get($payment->order_code);

            if ($paymentLink->status === PaymentLinkStatus::PAID) {
                $transaction = $paymentLink->transactions[0] ?? null;

                $this->markPaymentAsPaid(
                    $payment,
                    Carbon::parse($transaction->transactionDateTime ?? now()),
                    $paymentLink->id ?? null,
                    ['sync' => $paymentLink]
                );
            } elseif (in_array($paymentLink->status, [PaymentLinkStatus::CANCELLED, PaymentLinkStatus::EXPIRED, PaymentLinkStatus::FAILED], true)) {
                $payment->update([
                    'status' => PayosPayment::STATUS_CANCELLED,
                    'meta' => $this->mergeMeta($payment, ['sync' => $paymentLink]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('PayOS sync payment failed', [
                'order_code' => $payment->order_code,
                'error' => $e->getMessage(),
            ]);
        }

        return $payment->refresh();
    }

    private function markPaymentAsPaid(PayosPayment $payment, Carbon $paidAt, ?string $paymentLinkId = null, array $meta = []): void
    {
        if ($payment->status === PayosPayment::STATUS_PAID) {
            return;
        }

        $payment->update([
            'status' => PayosPayment::STATUS_PAID,
            'payment_link_id' => $paymentLinkId ?? $payment->payment_link_id,
            'paid_at' => $paidAt,
            'meta' => $this->mergeMeta($payment, $meta),
        ]);

        $payment->user?->activateStandard();
    }

    private function mergeMeta(PayosPayment $payment, array $newMeta): array
    {
        $current = $payment->meta ?? [];

        if (!is_array($current)) {
            $current = [];
        }

        return array_merge($current, $newMeta);
    }
}
