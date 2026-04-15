<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PointAssignmentLog;
use App\Models\PointTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PointManagementService
{
    /**
     * Calculate point value for a specific product based on a template.
     */
    public function calculatePointsByTemplate(Product $product, PointTemplate $template)
    {
        $points = 0;

        if ($template->type == 'fixed') {
            $points = $template->value;
        } elseif ($template->type == 'percentage_of_price') {
            $points = ($product->unit_price * $template->value) / 100;
        }

        // Apply Thresholds
        if ($template->min_threshold !== null && $points < $template->min_threshold) {
            $points = $template->min_threshold;
        }

        if ($template->max_threshold !== null && $points > $template->max_threshold) {
            $points = $template->max_threshold;
        }

        // Ensure positive integer
        return max(0, (int) round($points));
    }

    /**
     * Apply a uniform fixed point value or template to an array of Product IDs.
     */
    public function assignPointsByProductIds(array $productIds, $pointsOrTemplateId, $actionType = 'multi-select', $adminId = null)
    {
        if (empty($productIds)) return false;

        $products = Product::whereIn('id', $productIds)->get();
        return $this->processAndLogAssignment($products, $pointsOrTemplateId, $actionType, $adminId);
    }

    /**
     * Apply points/templates to all products within a specific category.
     */
    public function assignPointsByCategory($categoryId, $pointsOrTemplateId, $adminId = null)
    {
        // Get all products in category or subcategories
        $categoryIds = \App\Utility\CategoryUtility::children_ids($categoryId);
        $categoryIds[] = $categoryId;

        $products = Product::whereIn('category_id', $categoryIds)->get();

        return $this->processAndLogAssignment($products, $pointsOrTemplateId, 'category', $adminId);
    }

    /**
     * Core processing: Calculate, Backup old state, Save new state, and Log.
     */
    protected function processAndLogAssignment($products, $value, $actionType, $adminId)
    {
        if ($products->isEmpty()) return 0;

        $template = null;
        if (is_string($value) && strpos($value, 'template_') === 0) {
            $templateId = str_replace('template_', '', $value);
            $template = PointTemplate::find($templateId);
        }

        $oldState = [];
        $updatesCount = 0;

        DB::beginTransaction();
        try {
            foreach ($products as $product) {
                // Save old state for rollback
                $oldState[$product->id] = $product->earn_point;

                // Calculate new points
                if ($template) {
                    $newPoints = $this->calculatePointsByTemplate($product, $template);
                } else {
                    $newPoints = max(0, (int) $value);
                }

                // Only update if changed
                if ((int)$product->earn_point !== $newPoints) {
                    $product->earn_point = $newPoints;
                    $product->save();
                    $updatesCount++;
                }
            }

            // Create Audit Log if there were updates
            if ($updatesCount > 0) {
                PointAssignmentLog::create([
                    'admin_id' => $adminId ?? auth()->id(),
                    'action_type' => $actionType,
                    'affected_products_count' => count($oldState), // Total scope 
                    'payload_backup' => json_encode($oldState)
                ]);
            }

            DB::commit();
            
            // Force frontend cache wipe
            Cache::forget('system_degraded'); 
            
            return $updatesCount;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Point Management Assignment Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform a rollback using an existing PointAssignmentLog.
     */
    public function rollbackLog($logId)
    {
        $log = PointAssignmentLog::findOrFail($logId);
        $oldState = json_decode($log->payload_backup, true);

        if (empty($oldState)) return false;

        $updatesCount = 0;

        DB::beginTransaction();
        try {
            foreach ($oldState as $productId => $oldEarnPoint) {
                $product = Product::find($productId);
                if ($product) {
                    $product->earn_point = $oldEarnPoint;
                    $product->save();
                    $updatesCount++;
                }
            }
            
            // Delete the log after successful rollback so it can't be rolled back twice
            $log->delete();

            DB::commit();
            return $updatesCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Point Management Rollback Failed: ' . $e->getMessage());
            return false;
        }
    }
}
