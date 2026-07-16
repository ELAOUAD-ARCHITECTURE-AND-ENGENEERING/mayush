<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Services\IndexNowService;
use Illuminate\Console\Command;

class SubmitIndexNowUrls extends Command
{
    protected $signature = 'seo:indexnow:submit
        {--url=* : Absolute or application-relative URL to submit}
        {--recent : Submit a bounded set of recently updated public URLs}
        {--limit=100 : Maximum recent URLs to submit}';

    protected $description = 'Submit public Mayush URLs to IndexNow for faster Bing and Copilot discovery';

    public function handle(IndexNowService $indexNow): int
    {
        $urls = $this->option('url') ?: [];

        if ($this->option('recent')) {
            $urls = array_merge($urls, $this->recentPublicUrls((int) $this->option('limit')));
        }

        if ($urls === []) {
            $this->warn('No URLs supplied. Pass --url=https://mayushdesign.com/... or --recent.');

            return self::FAILURE;
        }

        $result = $indexNow->submitUrls($urls);

        if ($result['submitted'] ?? false) {
            $this->info('Submitted ' . $result['url_count'] . ' URL(s) to IndexNow.');

            return self::SUCCESS;
        }

        $this->warn('IndexNow submission skipped or failed: ' . ($result['reason'] ?? 'Unknown reason'));

        return $indexNow->isConfigured() ? self::FAILURE : self::SUCCESS;
    }

    private function recentPublicUrls(int $limit): array
    {
        $limit = max(1, min($limit, 1000));
        $perTypeLimit = max(1, (int) ceil($limit / 4));

        $urls = [route('home')];

        Product::publiclyVisible()
            ->whereNotNull('slug')
            ->latest('updated_at')
            ->limit($perTypeLimit)
            ->get(['id', 'slug'])
            ->each(function (Product $product) use (&$urls) {
                $urls[] = route('product', $product->slug);
            });

        Category::whereNotNull('slug')
            ->latest('updated_at')
            ->limit($perTypeLimit)
            ->get(['id', 'slug'])
            ->each(function (Category $category) use (&$urls) {
                $urls[] = route('products.category', $category->slug);
            });

        Blog::where('status', 1)
            ->whereNotNull('slug')
            ->latest('updated_at')
            ->limit($perTypeLimit)
            ->get(['id', 'slug'])
            ->each(function (Blog $blog) use (&$urls) {
                $urls[] = route('blog.details', $blog->slug);
            });

        Shop::publiclyVisible()
            ->whereNotNull('slug')
            ->latest('updated_at')
            ->limit($perTypeLimit)
            ->get(['id', 'slug'])
            ->each(function (Shop $shop) use (&$urls) {
                $urls[] = route('shop.visit', $shop->slug);
            });

        return array_slice(array_values(array_unique($urls)), 0, $limit);
    }
}
