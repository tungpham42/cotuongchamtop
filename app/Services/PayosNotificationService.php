<?php

namespace App\Services;

use App\Models\PayosPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class PayosNotificationService
{
    /**
     * Gửi email thông báo trạng thái giao dịch PayOS tới Admin (tung.42@gmail.com).
     *
     * @param PayosPayment $payment
     * @param string|null $status Trạng thái cụ thể (pending, cancelled, paid, failed)
     * @return bool
     */
    public function notify(PayosPayment $payment, ?string $status = null): bool
    {
        $status = $status ?: $payment->status;
        $recipient = config('payos.notify_email', 'tung.42@gmail.com');

        if (empty($recipient)) {
            return false;
        }

        // Chống gửi trùng lặp nếu trạng thái này đã được gửi thông báo trước đó
        $meta = $payment->meta ?? [];
        $notifiedStatuses = $meta['notified_statuses'] ?? [];
        if (in_array($status, $notifiedStatuses, true)) {
            return false;
        }

        try {
            $payment->loadMissing('user');
            $user = $payment->user;

            $statusConfig = $this->getStatusConfig($status);
            $planKey = $meta['plan'] ?? 'standard';
            $plansConfig = config('payos.plans', []);
            $planName = $plansConfig[$planKey]['name'] ?? ($payment->description ?: 'Gói VIP');
            $formattedAmount = number_format($payment->amount, 0, ',', '.') . ' VNĐ';

            $subject = sprintf(
                '[%s] PayOS #%s - %s (%s)',
                $statusConfig['prefix'],
                $payment->order_code,
                $planName,
                $formattedAmount
            );

            $htmlContent = $this->renderHtmlEmail($payment, $user, $statusConfig, $planName, $formattedAmount);

            $sent = $this->sendEmail($recipient, $subject, $htmlContent);

            if ($sent) {
                $notifiedStatuses[] = $status;
                $meta['notified_statuses'] = $notifiedStatuses;
                $payment->update(['meta' => $meta]);
                Log::info('PayOS email notification sent successfully', [
                    'recipient' => $recipient,
                    'order_code' => $payment->order_code,
                    'status' => $status,
                ]);
            }

            return $sent;
        } catch (\Throwable $e) {
            Log::error('PayOS notification service error', [
                'order_code' => $payment->order_code ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cấu hình nhãn, màu sắc và tiền tố tiêu đề cho từng trạng thái.
     */
    private function getStatusConfig(string $status): array
    {
        return match ($status) {
            PayosPayment::STATUS_PENDING => [
                'prefix' => 'PayOS Chờ thanh toán',
                'label' => 'Đang chờ thanh toán (Pending)',
                'badge_color' => '#f39c12',
                'badge_bg' => '#fef5e7',
                'icon' => '⏳',
                'note' => 'Khách hàng vừa tạo liên kết thanh toán và đang mở trang PayOS.',
            ],
            PayosPayment::STATUS_CANCELLED => [
                'prefix' => 'PayOS ĐÃ HỦY',
                'label' => 'Giao dịch đã bị hủy (Cancelled)',
                'badge_color' => '#e74c3c',
                'badge_bg' => '#fdeeed',
                'icon' => '❌',
                'note' => 'Khách hàng đã bấm nút hủy giao dịch hoặc liên kết đã hết hạn.',
            ],
            PayosPayment::STATUS_PAID => [
                'prefix' => 'PayOS THÀNH CÔNG',
                'label' => 'Thanh toán thành công (Paid)',
                'badge_color' => '#27ae60',
                'badge_bg' => '#eafaf1',
                'icon' => '✅',
                'note' => 'Tiền đã về tài khoản và hệ thống đã tự động kích hoạt gói VIP cho người dùng.',
            ],
            PayosPayment::STATUS_FAILED => [
                'prefix' => 'PayOS THẤT BẠI',
                'label' => 'Giao dịch thất bại (Failed)',
                'badge_color' => '#c0392b',
                'badge_bg' => '#fadbd8',
                'icon' => '⚠️',
                'note' => 'Giao dịch không thành công (sai số tiền hoặc lỗi kết nối).',
            ],
            default => [
                'prefix' => 'PayOS Cập nhật',
                'label' => ucfirst($status),
                'badge_color' => '#7f8c8d',
                'badge_bg' => '#f2f4f4',
                'icon' => 'ℹ️',
                'note' => 'Trạng thái giao dịch vừa được cập nhật.',
            ],
        };
    }

    /**
     * Render giao diện HTML email thông báo đẹp mắt, responsive.
     */
    private function renderHtmlEmail(
        PayosPayment $payment,
        $user,
        array $statusConfig,
        string $planName,
        string $formattedAmount
    ): string {
        $userName = $user ? htmlspecialchars($user->name ?: 'Không tên') : 'Khách vãng lai';
        $userEmail = $user ? htmlspecialchars($user->email ?: 'N/A') : 'N/A';
        $userId = $user ? $user->id : 'N/A';
        $updatedAt = now()->timezone('Asia/Ho_Chi_Minh')->format('H:i:s d/m/Y');
        $checkoutUrl = $payment->checkout_url;

        $checkoutButtonHtml = '';
        if ($payment->status === PayosPayment::STATUS_PENDING && !empty($checkoutUrl)) {
            $checkoutButtonHtml = "
                <div style='margin-top: 20px; text-align: center;'>
                    <a href='{$checkoutUrl}' target='_blank' style='display: inline-block; background-color: #27ae60; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold;'>
                        Mở liên kết thanh toán PayOS
                    </a>
                </div>
            ";
        }

        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <title>Thông báo PayOS</title>
        </head>
        <body style='margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f8; color: #333333;'>
            <table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0'>
                <tr>
                    <td align='center'>
                        <table role='presentation' width='600' cellspacing='0' cellpadding='0' border='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); max-width: 600px; width: 100%;'>
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, #2c3e50, #1a252f); padding: 25px; text-align: center; color: #ffffff;'>
                                    <h1 style='margin: 0; font-size: 22px; font-weight: 700;'>♟️ CỜ TƯỚNG CHẤM TOP</h1>
                                    <p style='margin: 6px 0 0; font-size: 14px; opacity: 0.85;'>Thông báo Giao dịch PayOS</p>
                                </td>
                            </tr>

                            <!-- Status Banner -->
                            <tr>
                                <td style='padding: 20px 30px; background-color: {$statusConfig['badge_bg']}; border-bottom: 1px solid #e1e8ed;'>
                                    <div style='display: flex; align-items: center;'>
                                        <span style='font-size: 26px; margin-right: 12px;'>{$statusConfig['icon']}</span>
                                        <div>
                                            <div style='font-size: 16px; font-weight: bold; color: {$statusConfig['badge_color']};'>
                                                {$statusConfig['label']}
                                            </div>
                                            <div style='font-size: 13px; color: #555555; margin-top: 3px;'>
                                                {$statusConfig['note']}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Details Table -->
                            <tr>
                                <td style='padding: 25px 30px;'>
                                    <table width='100%' cellspacing='0' cellpadding='8' border='0' style='border-collapse: collapse; font-size: 14px;'>
                                        <tr style='border-bottom: 1px solid #f0f0f0;'>
                                            <td style='color: #7f8c8d; width: 150px;'>Mã đơn hàng:</td>
                                            <td style='font-weight: 700; color: #2c3e50;'>#{$payment->order_code}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #f0f0f0;'>
                                            <td style='color: #7f8c8d;'>Gói cước:</td>
                                            <td style='font-weight: 700; color: #2980b9;'>{$planName}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #f0f0f0;'>
                                            <td style='color: #7f8c8d;'>Số tiền:</td>
                                            <td style='font-weight: 700; font-size: 16px; color: #e67e22;'>{$formattedAmount}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #f0f0f0;'>
                                            <td style='color: #7f8c8d;'>Nội dung CK:</td>
                                            <td><code>{$payment->description}</code></td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #f0f0f0;'>
                                            <td style='color: #7f8c8d;'>Kỳ thủ (User):</td>
                                            <td><strong>{$userName}</strong> (ID: #{$userId})</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #f0f0f0;'>
                                            <td style='color: #7f8c8d;'>Email kỳ thủ:</td>
                                            <td><a href='mailto:{$userEmail}' style='color: #3498db; text-decoration: none;'>{$userEmail}</a></td>
                                        </tr>
                                        <tr>
                                            <td style='color: #7f8c8d;'>Thời gian:</td>
                                            <td style='color: #555555;'>{$updatedAt} (Giờ VN)</td>
                                        </tr>
                                    </table>

                                    {$checkoutButtonHtml}
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f8fafc; padding: 15px 30px; text-align: center; font-size: 12px; color: #95a5a6; border-top: 1px solid #e1e8ed;'>
                                    Hệ thống thông báo tự động từ <a href='https://cotuong.top' target='_blank' style='color: #7f8c8d; text-decoration: underline;'>cotuong.top</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    /**
     * Gửi email bằng PHPMailer (chuẩn SMTP dự án), fallback qua Laravel Mail nếu cần.
     */
    private function sendEmail(string $recipient, string $subject, string $htmlContent): bool
    {
        $fromAddress = config('mail.from.address') ?: 'noreply@cotuong.top';
        $fromName    = config('mail.from.name') ?: 'Cờ Tướng';

        // Khi chạy kiểm thử (PHPUnit/Test environment), dùng trực tiếp Laravel Mail để hỗ trợ Mail::fake()
        if (app()->runningUnitTests()) {
            try {
                Mail::html($htmlContent, function ($message) use ($recipient, $subject, $fromAddress, $fromName) {
                    $message->from($fromAddress, $fromName)
                        ->to($recipient)
                        ->subject($subject);
                });

                return true;
            } catch (\Throwable $e) {
                Log::error('Mail sending failed in test', ['error' => $e->getMessage()]);
                return false;
            }
        }

        $smtpHost = config('mail.mailers.smtp.host');
        $smtpUser = config('mail.mailers.smtp.username');

        // Thử gửi qua PHPMailer nếu có cấu hình SMTP
        if (!empty($smtpHost) && !empty($smtpUser)) {
            try {
                $mail = new PHPMailer(true);
                $mail->SMTPDebug = 0;
                $mail->Timeout = 5; // Giới hạn 5 giây, không làm nghẽn tiến trình
                $mail->isSMTP();
                $mail->CharSet = 'UTF-8';

                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = config('mail.mailers.smtp.password');
                $mail->SMTPSecure = config('mail.mailers.smtp.encryption', 'tls');
                $mail->Port       = config('mail.mailers.smtp.port', 587);

                $mail->setFrom($fromAddress, $fromName);

                $mail->addAddress($recipient);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlContent;

                return $mail->send();
            } catch (PHPMailerException $e) {
                Log::warning('PHPMailer failed for PayOS notification, falling back to Laravel Mail', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback sang Laravel Mail Facade
        try {
            Mail::html($htmlContent, function ($message) use ($recipient, $subject, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName)
                    ->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('All mail drivers failed for PayOS notification', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
