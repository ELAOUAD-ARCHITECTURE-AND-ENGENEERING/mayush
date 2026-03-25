<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\FlashDeal;
use App\Models\Coupon;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(50)->create();
        Category::factory()->count(50)->create();
        Brand::factory()->count(50)->create();
        Product::factory()->count(50)->create();
        Order::factory()->count(50)->create();
        FlashDeal::factory()->count(50)->create();
        Coupon::factory()->count(50)->create();
    }
}
