<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Blog;
use App\Models\Shop;
use App\Models\Page;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL as UrlFacade;
use Illuminate\Support\Str;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap {--base-url= : Canonical production base URL, for example https://mayushdesign.com}';

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
        try {
            $baseUrl = rtrim($this->option('base-url') ?: config('app.url'), '/');
            if (!$baseUrl || Str::contains($baseUrl, ['localhost', '127.0.0.1', '::1'])) {
                $message = 'Refusing to generate sitemap with a local APP_URL. Set APP_URL=https://mayushdesign.com or pass --base-url=https://mayushdesign.com.';

                if (app()->environment('production')) {
                    $this->error($message);
                    return self::FAILURE;
                }

                $this->warn($message);
            }

            if (!Str::startsWith($baseUrl, 'https://')) {
                $this->warn('Sitemap base URL should be HTTPS for production SEO: ' . $baseUrl);
            }

            UrlFacade::forceRootUrl($baseUrl);
            if (Str::startsWith($baseUrl, 'https://')) {
                UrlFacade::forceScheme('https');
            }

            $this->info("Starting sitemap generation...");
            
            $sitemap = Sitemap::create();

            $homeUrl = route('home');
            $this->info("Adding Homepage: " . $homeUrl);
            // 1. Homepage
            $sitemap->add(Url::create($homeUrl)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0));

            $catCount = Category::count();
            $this->info("Found {$catCount} Categories");
            // 2. Categories
            Category::all()->each(function (Category $category) use ($sitemap) {
                if ($category->slug) {
                    $sitemap->add(Url::create(route('products.category', $category->slug))
                        ->setLastModificationDate($category->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));
                }
            });

            $brandQuery = Brand::query();
            if (Schema::hasColumn('brands', 'status')) {
                $brandQuery->where('status', 1);
            }

            $brandCount = (clone $brandQuery)->count();
            $this->info("Found {$brandCount} Brands");
            // 3. Brands
            $brandQuery->each(function (Brand $brand) use ($sitemap) {
                if ($brand->slug) {
                    $sitemap->add(Url::create(route('products.brand', $brand->slug))
                        ->setLastModificationDate($brand->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.6));
                }
            });

            $prodCount = Product::where('published', 1)->where('approved', 1)->count();
            $this->info("Found {$prodCount} Published/Approved Products");
            // 4. Products
            Product::where('published', 1)->where('approved', 1)->each(function (Product $product) use ($sitemap) {
                if ($product->slug) {
                    $sitemap->add(Url::create(route('product', $product->slug))
                        ->setLastModificationDate($product->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.9));
                }
            });

            $blogCount = Blog::where('status', 1)->whereNotNull('slug')->count();
            $this->info("Found {$blogCount} Published Blog Posts");
            Blog::where('status', 1)->whereNotNull('slug')->each(function (Blog $blog) use ($sitemap) {
                $sitemap->add(Url::create(route('blog.details', $blog->slug))
                    ->setLastModificationDate($blog->updated_at ?: Carbon::now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7));
            });

            $shopQuery = Shop::whereNotNull('slug')
                ->where('verification_status', 1)
                ->whereHas('user', function ($query) {
                    $query->where('banned', 0);
                });

            $shopCount = (clone $shopQuery)->count();
            $this->info("Found {$shopCount} Seller Shops");
            $shopQuery->each(function (Shop $shop) use ($sitemap) {
                $sitemap->add(Url::create(route('shop.visit', $shop->slug))
                    ->setLastModificationDate($shop->updated_at ?: Carbon::now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7));
            });

            foreach (['sellerpolicy', 'returnpolicy', 'supportpolicy', 'terms', 'privacypolicy', 'blog', 'brands.all', 'categories.all'] as $routeName) {
                if (Route::has($routeName)) {
                    $sitemap->add(Url::create(route($routeName))
                        ->setLastModificationDate(Carbon::now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.5));
                }
            }

            if (class_exists(Page::class) && Schema::hasTable('pages') && Schema::hasColumn('pages', 'slug')) {
                Page::whereNotNull('slug')->each(function (Page $page) use ($sitemap) {
                    $sitemap->add(Url::create(route('custom-pages.show_custom_page', $page->slug))
                        ->setLastModificationDate($page->updated_at ?: Carbon::now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.5));
                });
            }

            $this->info("Writing to file...");
            $sitemap->writeToFile(public_path('sitemap.xml'));

            $this->info('Sitemap generated successfully at ' . public_path('sitemap.xml'));
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Error generating sitemap: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
