<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCategoryCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-category-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filteredProductIds = \App\Http\Helpers::filter_products(\App\Models\Product::query())->pluck('id')->toArray();

        $mainCategories = \Illuminate\Support\Facades\DB::table('products')
            ->whereIn('id', $filteredProductIds)
            ->whereNotNull('category_id')
            ->select('id as product_id', 'category_id');

        $pivotCategories = \Illuminate\Support\Facades\DB::table('product_categories')
            ->whereIn('product_id', $filteredProductIds)
            ->select('product_id', 'category_id');

        $combinedCategories = $mainCategories->union($pivotCategories);

        $productCountsSubCategory = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$combinedCategories->toSql()}) as combined"))
            ->mergeBindings($combinedCategories)
            ->select('category_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT product_id) as count'))
            ->groupBy('category_id')
            ->pluck('count', 'category_id');

        $categoriesMap = \App\Models\Category::pluck('name', 'id')->toArray();

        $this->info("Counts from combined query (unique by category):");
        foreach ([1, 8, 341] as $id) {
            $count = $productCountsSubCategory[$id] ?? 0;
            $this->info("{$categoriesMap[$id]} ($id): {$count}");
        }

        $allCategories = \App\Models\Category::with('childrenCategories')->where('level', 0)->get();

        foreach ($allCategories as $cat) {
            $this->getCategoryCount($cat, $productCountsSubCategory);
        }

        $this->info("\nAfter recursive summation (how the UI gets it):");
        foreach ($allCategories as $cat) {
            if (in_array($cat->id, [1, 8, 341])) {
                $this->info("{$cat->name} ({$cat->id}): {$cat->products_count}");
            }
            foreach ($cat->childrenCategories as $child) {
                if (in_array($child->id, [1, 8, 341])) {
                    $this->info("  {$child->name} ({$child->id}): {$child->products_count}");
                }
            }
        }

        $this->info("\nTrue unique product counts (no double counting):");
        foreach ([1, 8, 341] as $id) {
            $branchIds = $this->getAllDescendantsIds($id);
            
            $mainCount = \Illuminate\Support\Facades\DB::table('products')
                ->whereIn('id', $filteredProductIds)
                ->whereIn('category_id', $branchIds)
                ->select('id as product_id');
                
            $pivotCount = \Illuminate\Support\Facades\DB::table('product_categories')
                ->whereIn('product_id', $filteredProductIds)
                ->whereIn('category_id', $branchIds)
                ->select('product_id');
                
            $combinedCount = $mainCount->union($pivotCount);
            
            $trueCount = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$combinedCount->toSql()}) as combined"))
                ->mergeBindings($combinedCount)
                ->count(\Illuminate\Support\Facades\DB::raw('DISTINCT product_id'));
                
            $this->info("{$categoriesMap[$id]} ($id): {$trueCount}");
        }
    }

    private function getCategoryCount($category, $productCounts) {
        $ownCount = $productCounts[$category->id] ?? 0;
        $totalCount = $ownCount;
        if (!empty($category->childrenCategories)) {
            foreach ($category->childrenCategories as $child) {
                $totalCount += $this->getCategoryCount($child, $productCounts);
            }
        }
        $category->products_count = $totalCount;
        return $totalCount;
    }

    private function getAllDescendantsIds($categoryId) {
        $ids = [$categoryId];
        $children = \App\Models\Category::where('parent_id', $categoryId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getAllDescendantsIds($childId));
        }
        return $ids;
    }

}
