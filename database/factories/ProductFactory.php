<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'name'              => $name,
            'slug'              => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'added_by'          => 'admin',
            'user_id'           => User::factory(),
            'category_id'       => 1,
            'brand_id'          => null,
            'unit'              => 'pcs',
            'min_qty'           => 1,
            'current_stock'     => $this->faker->numberBetween(10, 100),
            'unit_price'        => $this->faker->randomFloat(2, 10, 500),
            'purchase_price'    => $this->faker->randomFloat(2, 5, 200),
            'tax_type'          => 'percent',
            'shipping_type'     => 'flat_rate',
            'flat_shipping_cost'=> 5.00,
            'is_quantity_multiplied' => 0,
            'est_shipping_days' => 3,
            'num_of_sale'       => $this->faker->numberBetween(0, 500),
            'meta_title'        => $name,
            'meta_description'  => $this->faker->sentence(),
            'rating'            => $this->faker->randomFloat(1, 1, 5),
            'digital'           => 0,
            'auction_product'   => 0,
            'wholesale_product' => 0,
            'featured'          => 0,
            'todays_deal'       => 0,
            'published'         => 1,
            'approved'          => 1,
            'discount'          => 0,
            'discount_type'     => 'percent',
            'discount_start_date' => null,
            'discount_end_date'   => null,
            'earn_point'        => 0,
            'tags'              => $this->faker->words(3, true),
            'photos'            => null,
            'thumbnail_img'     => null,
            'description'       => $this->faker->paragraph(),
        ];
    }

    /** Product with a percentage discount applied. */
    public function withDiscount(float $percent = 20): static
    {
        return $this->state([
            'discount'      => $percent,
            'discount_type' => 'percent',
        ]);
    }

    /** Product with fixed-amount discount. */
    public function withAmountDiscount(float $amount = 10): static
    {
        return $this->state([
            'discount'      => $amount,
            'discount_type' => 'amount',
        ]);
    }

    /** Wholesale product. */
    public function wholesale(): static
    {
        return $this->state(['wholesale_product' => 1]);
    }

    /** Auction product. */
    public function auction(): static
    {
        return $this->state(['auction_product' => 1]);
    }

    /** Out of stock product. */
    public function outOfStock(): static
    {
        return $this->state(['current_stock' => 0]);
    }

    /** Unpublished / draft product. */
    public function unpublished(): static
    {
        return $this->state(['published' => 0]);
    }

    /** Unapproved product. */
    public function unapproved(): static
    {
        return $this->state(['approved' => 0]);
    }

    /** Digital product. */
    public function digital(): static
    {
        return $this->state(['digital' => 1]);
    }
}
