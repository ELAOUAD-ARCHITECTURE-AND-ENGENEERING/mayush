<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\CustomerPackage;
use App\Models\ClubPoint;
use App\Models\ClubPointDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * LoyaltyService — Phase 4: Customer Allegiance & Loyalty
 *
 * Centralizes all loyalty logic:
 *  - Dynamic point preview calculation for product cards
 *  - Automatic tier recalculation after each order
 *  - Progress tracking toward next tier
 */
class LoyaltyService
{
    // Tier boundaries (MAD annual spend)
    const TIERS = [
        1 => ['key' => 'silver',   'label' => 'Silver',   'color' => '#94a3b8', 'icon' => '🥈'],
        2 => ['key' => 'gold',     'label' => 'Gold',     'color' => '#f59e0b', 'icon' => '🥇'],
        3 => ['key' => 'platinum', 'label' => 'Platinum', 'color' => '#6366f1', 'icon' => '💎'],
    ];

    /**
     * Calculate the potential club points a customer would earn for a product.
     * Applies the loyalty multiplier if the customer is on a loyalty tier.
     *
     * @param  Product   $product
     * @param  User|null $user    Current authenticated user (optional)
     * @return int
     */
    public function getPotentialPoints(Product $product, ?User $user = null): int
    {
        $basePoints = (int) $product->earn_point;
        if ($basePoints <= 0) {
            return 0;
        }

        $multiplier = 1.0;
        if ($user && $user->customer_package_id) {
            $pkg = CustomerPackage::find($user->customer_package_id);
            if ($pkg && isset($pkg->loyalty_multiplier)) {
                $multiplier = (float) $pkg->loyalty_multiplier;
            }
        }

        return (int) round($basePoints * $multiplier);
    }

    /**
     * Convert a point value to its monetary equivalent string.
     *
     * @param  int $points
     * @return float
     */
    public function pointsToMonetaryValue(int $points): float
    {
        $rate = (float) get_setting('club_point_convert_rate', 10);
        return $rate > 0 ? round($points / $rate, 2) : 0;
    }

    /**
     * Get the customer's rolling 12-month spend (in base system currency).
     *
     * @param  User $user
     * @return float
     */
    public function getAnnualSpend(User $user): float
    {
        return Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->sum('grand_total');
    }

    /**
     * Recalculate and (if needed) auto-upgrade the user's loyalty tier
     * based on their rolling 12-month spend. Only operates on packages
     * marked as loyalty tiers (is_loyalty_tier = true).
     *
     * @param  User $user
     * @return bool  true if tier was changed, false otherwise
     */
    public function recalculateTier(User $user): bool
    {
        try {
            // Only auto-assign loyalty tiers — packages without is_loyalty_tier are purchasable/manual
            $tiers = CustomerPackage::where('is_loyalty_tier', true)
                ->orderByDesc('min_spend')
                ->get();

            if ($tiers->isEmpty()) {
                return false; // No loyalty tiers configured yet
            }

            $annualSpend = $this->getAnnualSpend($user);

            // Update the rolling annual spend on the user
            $user->annual_spend = $annualSpend;
            $user->save();

            // Find the highest qualifying tier
            $newTier = null;
            foreach ($tiers as $tier) {
                if ($annualSpend >= (float) $tier->min_spend) {
                    $newTier = $tier;
                    break;
                }
            }

            $newPackageId = $newTier ? $newTier->id : null;

            // Only save & log if tier actually changed
            if ($user->customer_package_id !== $newPackageId) {
                $user->customer_package_id = $newPackageId;
                $user->save();

                Log::info("[LoyaltyService] Tier upgraded", [
                    'user_id'      => $user->id,
                    'annual_spend' => $annualSpend,
                    'new_tier'     => $newTier ? $newTier->getTranslation('name') : 'Basic',
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Log::error("[LoyaltyService] recalculateTier failed for user {$user->id}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Get the user's progress toward the next loyalty tier.
     *
     * @param  User $user
     * @return array{current_tier: ?CustomerPackage, next_tier: ?CustomerPackage, percent: int, spend_gap: float, annual_spend: float}
     */
    public function getTierProgress(User $user): array
    {
        $annualSpend = $this->getAnnualSpend($user);

        $tiers = CustomerPackage::where('is_loyalty_tier', true)
            ->orderBy('min_spend')
            ->get();

        $currentTier = null;
        $nextTier    = null;

        foreach ($tiers as $index => $tier) {
            if ($annualSpend >= (float) $tier->min_spend) {
                $currentTier = $tier;
            } else {
                $nextTier = $tier;
                break;
            }
        }

        // Calculate progress percentage toward next tier
        $percent = 100;
        $spendGap = 0;

        if ($nextTier) {
            $currentMin = $currentTier ? (float) $currentTier->min_spend : 0;
            $nextMin    = (float) $nextTier->min_spend;
            $range      = $nextMin - $currentMin;
            $progress   = $annualSpend - $currentMin;
            $percent    = $range > 0 ? min(99, (int) round(($progress / $range) * 100)) : 0;
            $spendGap   = max(0, $nextMin - $annualSpend);
        }

        return [
            'current_tier' => $currentTier,
            'next_tier'    => $nextTier,
            'percent'      => $percent,
            'spend_gap'    => $spendGap,
            'annual_spend' => $annualSpend,
        ];
    }

    /**
     * Get the user's full club point balance.
     *
     * @param  User $user
     * @return int
     */
    public function getPointBalance(User $user): int
    {
        $clubPoint = $user->club_point;
        return $clubPoint ? (int) $clubPoint->points : 0;
    }

    /**
     * Get recent point history for a user.
     *
     * @param  User $user
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPointHistory(User $user, int $limit = 10)
    {
        $clubPoint = $user->club_point;
        if (!$clubPoint) {
            return collect();
        }

        return ClubPointDetail::where('club_point_id', $clubPoint->id)
            ->with('product')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get tier metadata (color, icon, label) for a given tier level integer.
     *
     * @param  int $level
     * @return array
     */
    public static function getTierMeta(int $level): array
    {
        return self::TIERS[$level] ?? ['key' => 'basic', 'label' => 'Basic', 'color' => '#64748b', 'icon' => '⭐'];
    }
}
