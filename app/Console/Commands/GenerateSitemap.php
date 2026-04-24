<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for Mayush Marketplace';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = Sitemap::create();

        // 1. Homepage
        $sitemap->add(Url::create(route('home'))
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));

        // 2. Categories
        Category::all()->each(function (Category $category) use ($sitemap) {
            if ($category->slug) {
                $sitemap->add(Url::create(route('products.category', $category->slug))
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8));
            }
        });

        // 3. Brands
        Brand::all()->each(function (Brand $brand) use ($sitemap) {
            if ($brand->slug) {
                $sitemap->add(Url::create(route('products.brand', $brand->slug))
                    ->setLastModificationDate($brand->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.6));
            }
        });

        // 4. Products
        Product::where('published', 1)->where('approved', 1)->each(function (Product $product) use ($sitemap) {
            if ($product->slug) {
                $sitemap->add(Url::create(route('product', $product->slug))
                    ->setLastModificationDate($product->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.9));
            }
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at ' . public_path('sitemap.xml'));
    }
}
