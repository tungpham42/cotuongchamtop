<?php

namespace App\Console\Commands;

use App\Http\Controllers\PayOSController;
use App\Models\PayosPayment;
use Illuminate\Console\Command;

class SyncPendingPaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payos:sync-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync pending PayOS payments with PayOS API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PayOSController $controller)
    {
        $pendingPayments = PayosPayment::where('status', PayosPayment::STATUS_PENDING)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        $this->info("Found " . $pendingPayments->count() . " pending payment(s).");

        foreach ($pendingPayments as $payment) {
            $prevStatus = $payment->status;
            $updated = $controller->syncPaymentStatus($payment);
            $this->info("Order {$payment->order_code}: {$prevStatus} -> {$updated->status}");
        }

        $this->info("Sync completed.");

        return Command::SUCCESS;
    }
}
