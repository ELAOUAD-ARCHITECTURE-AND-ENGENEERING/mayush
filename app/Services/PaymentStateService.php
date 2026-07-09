<?php

namespace App\Services;

use App\Models\Order;
use App\Models\CombinedOrder;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\EliteSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentStateService
{
    /**
     * Check if a transition is valid
     */
    public function canTransition(string $from, string $to): bool
    {
        $from = strtolower($from);
        $to = strtolower($to);
        
        $allowedTransitions = [
            'initiated' => ['pending', 'authorized', 'paid', 'failed', 'cancelled', 'expired'],
            'pending' => ['authorized', 'paid', 'failed', 'cancelled', 'expired'],
            'authorized' => ['paid', 'failed', 'cancelled'],
            'paid' => [], // Cannot transition out of paid
            'failed' => ['paid'], // Allowed only via valid callback
            'cancelled' => ['paid'], // Allowed only via valid callback
            'expired' => [],
        ];

        return in_array($to, $allowedTransitions[$from] ?? []);
    }

    /**
     * Safely process a payment attempt status transition
     */
    public function transitionPaymentAttempt(PaymentAttempt $attempt, string $to, array $context = []): bool
    {
        return DB::transaction(function () use ($attempt, $to, $context) {
            $lockedAttempt = PaymentAttempt::lockForUpdate()->find($attempt->id);
            if (!$lockedAttempt) return false;

            if (!$this->canTransition($lockedAttempt->status, $to)) {
                Log::warning("PaymentStateService: Invalid transition from {$lockedAttempt->status} to {$to}", [
                    'attempt_id' => $lockedAttempt->id
                ]);
                return false;
            }

            $lockedAttempt->status = $to;
            if ($to === 'paid') {
                $lockedAttempt->completed_at = now();
            } elseif (in_array($to, ['failed', 'cancelled', 'expired'])) {
                $lockedAttempt->failed_at = now();
            }

            if (!empty($context)) {
                $metadata = $lockedAttempt->metadata ?? [];
                $lockedAttempt->metadata = array_merge($metadata, $context);
            }

            $lockedAttempt->save();
            return true;
        });
    }

    /**
     * Mark an order or combined order as paid safely.
     * Returns true if status was actually changed to paid, false if it was already paid.
     */
    public function markOrderPaidSafely($orderEntity, array $paymentDetails = []): bool
    {
        return DB::transaction(function () use ($orderEntity, $paymentDetails) {
            // Lock the rows to prevent race conditions
            if ($orderEntity instanceof CombinedOrder) {
                $lockedOrder = CombinedOrder::lockForUpdate()->find($orderEntity->id);
                if (!$lockedOrder) return false;

                $allAlreadyPaid = true;
                foreach ($lockedOrder->orders as $subOrder) {
                    $lockedSubOrder = Order::lockForUpdate()->find($subOrder->id);
                    if ($lockedSubOrder && $lockedSubOrder->payment_status !== 'paid') {
                        $allAlreadyPaid = false;
                        $lockedSubOrder->payment_status = 'paid';
                        $lockedSubOrder->payment_details = json_encode($paymentDetails);
                        $lockedSubOrder->save();
                    }
                }
                return !$allAlreadyPaid; // Returns true if it ACTUALLY changed something

            } elseif ($orderEntity instanceof Order) {
                $lockedOrder = Order::lockForUpdate()->find($orderEntity->id);
                if (!$lockedOrder || $lockedOrder->payment_status === 'paid') {
                    return false; // Already paid
                }
                $lockedOrder->payment_status = 'paid';
                $lockedOrder->payment_details = json_encode($paymentDetails);
                $lockedOrder->save();
                return true;

            } elseif ($orderEntity instanceof User) {
                // Wallet top-up (amount validation should be done upstream)
                $lockedUser = User::lockForUpdate()->find($orderEntity->id);
                if (!$lockedUser) return false;
                return true;

            } elseif ($orderEntity instanceof EliteSubscription) {
                $lockedSub = EliteSubscription::lockForUpdate()->find($orderEntity->id);
                // In Elite Subscription we might not have a direct payment_status column checked here,
                // but we lock the row anyway to serialize callbacks.
                if (!$lockedSub) return false;
                if (isset($lockedSub->payment_status) && $lockedSub->payment_status === 'paid') {
                    return false;
                }
                return true;
            }

            return false;
        });
    }
}
