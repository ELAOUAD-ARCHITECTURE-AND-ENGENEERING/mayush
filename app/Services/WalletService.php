<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    public function credit(User $user, $amount, string $paymentMethod, $paymentDetails = null, ?string $paymentReference = null): Wallet
    {
        $amount = (float) $amount;

        if ($amount <= 0) {
            throw new InvalidArgumentException('Wallet recharge amount must be greater than zero.');
        }

        $reference = $paymentReference ?: $this->referenceFromPaymentDetails($user->id, $amount, $paymentMethod, $paymentDetails);

        return DB::transaction(function () use ($user, $amount, $paymentMethod, $paymentDetails, $reference) {
            if ($reference) {
                $existing = Wallet::where('payment_reference', $reference)->first();

                if ($existing) {
                    return $existing;
                }
            }

            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $wallet = Wallet::create([
                'user_id' => $lockedUser->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_details' => $this->stringifyPaymentDetails($paymentDetails),
                'payment_reference' => $reference,
                'approval' => true,
                'offline_payment' => false,
            ]);

            $lockedUser->balance = (float) $lockedUser->balance + $amount;
            $lockedUser->save();

            return $wallet;
        });
    }

    public function referenceFromPaymentDetails(int $userId, float $amount, string $paymentMethod, $paymentDetails = null): ?string
    {
        $details = $this->normalizePaymentDetails($paymentDetails);
        $gatewayReference = $this->findGatewayReference($details);

        if ($gatewayReference !== null && $gatewayReference !== '') {
            return strtolower($paymentMethod) . ':' . $gatewayReference;
        }

        $detailsString = $this->stringifyPaymentDetails($paymentDetails);

        if ($detailsString === null || trim($detailsString) === '') {
            return null;
        }

        return strtolower($paymentMethod) . ':' . hash('sha256', $userId . '|' . number_format($amount, 2, '.', '') . '|' . $detailsString);
    }

    private function normalizePaymentDetails($paymentDetails): array
    {
        if (is_array($paymentDetails)) {
            return $paymentDetails;
        }

        if (is_object($paymentDetails)) {
            return json_decode(json_encode($paymentDetails), true) ?: [];
        }

        if (is_string($paymentDetails)) {
            $decoded = json_decode($paymentDetails, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function findGatewayReference(array $details): ?string
    {
        $keys = [
            'payment_id',
            'paymentId',
            'paymentID',
            'transaction_id',
            'transactionId',
            'trx_id',
            'trxID',
            'tran_id',
            'reference',
            'reference_id',
            'token',
            'conversationId',
            'TransId',
            'transId',
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $details) && $details[$key] !== null && $details[$key] !== '') {
                return (string) $details[$key];
            }
        }

        foreach ($details as $value) {
            if (is_array($value)) {
                $nested = $this->findGatewayReference($value);

                if ($nested) {
                    return $nested;
                }
            }
        }

        return null;
    }

    private function stringifyPaymentDetails($paymentDetails): ?string
    {
        if ($paymentDetails === null) {
            return null;
        }

        if (is_string($paymentDetails)) {
            return $paymentDetails;
        }

        return json_encode($paymentDetails);
    }
}
