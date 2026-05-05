<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blog_categories')) {
            return;
        }

        $now = now();

        foreach ($this->categories() as $category) {
            $values = [
                'category_name' => $category['name'],
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('blog_categories', 'status')) {
                $values['status'] = 1;
            }

            if (Schema::hasColumn('blog_categories', 'deleted_at')) {
                $values['deleted_at'] = null;
            }

            $exists = DB::table('blog_categories')
                ->where('slug', $category['slug'])
                ->exists();

            if ($exists) {
                DB::table('blog_categories')
                    ->where('slug', $category['slug'])
                    ->update($values);

                continue;
            }

            DB::table('blog_categories')->insert(array_merge($values, [
                'slug' => $category['slug'],
                'created_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. These editorial categories may contain
        // production articles after deployment, so rollback must not delete them.
    }

    private function categories(): array
    {
        return [
            ['name' => 'Interior Design Ideas', 'slug' => 'interior-design-ideas'],
            ['name' => 'Architecture Inspiration', 'slug' => 'architecture-inspiration'],
            ['name' => 'Home Renovation', 'slug' => 'home-renovation'],
            ['name' => 'Construction Guides', 'slug' => 'construction-guides'],
            ['name' => '3D Visualization', 'slug' => '3d-visualization'],
            ['name' => 'Furniture Buying Guides', 'slug' => 'furniture-buying-guides'],
            ['name' => 'Lighting Design', 'slug' => 'lighting-design'],
            ['name' => 'Kitchen Design', 'slug' => 'kitchen-design'],
            ['name' => 'Bathroom Design', 'slug' => 'bathroom-design'],
            ['name' => 'Living Room Design', 'slug' => 'living-room-design'],
            ['name' => 'Bedroom Design', 'slug' => 'bedroom-design'],
            ['name' => 'Office and Workspace Design', 'slug' => 'office-workspace-design'],
            ['name' => 'Outdoor and Garden Design', 'slug' => 'outdoor-garden-design'],
            ['name' => 'Small Space Solutions', 'slug' => 'small-space-solutions'],
            ['name' => 'Luxury Interiors', 'slug' => 'luxury-interiors'],
            ['name' => 'Minimalist Design', 'slug' => 'minimalist-design'],
            ['name' => 'Moroccan Design and Craft', 'slug' => 'moroccan-design-craft'],
            ['name' => 'Materials and Finishes', 'slug' => 'materials-finishes'],
            ['name' => 'Flooring and Wall Coverings', 'slug' => 'flooring-wall-coverings'],
            ['name' => 'Color Trends', 'slug' => 'color-trends'],
            ['name' => 'Decor and Accessories', 'slug' => 'decor-accessories'],
            ['name' => 'Smart Home and Technology', 'slug' => 'smart-home-technology'],
            ['name' => 'Sustainable Design', 'slug' => 'sustainable-design'],
            ['name' => 'Real Estate Staging', 'slug' => 'real-estate-staging'],
            ['name' => 'Commercial Interiors', 'slug' => 'commercial-interiors'],
            ['name' => 'Hospitality Design', 'slug' => 'hospitality-design'],
            ['name' => 'Retail and Showroom Design', 'slug' => 'retail-showroom-design'],
            ['name' => 'Project Planning and Budgeting', 'slug' => 'project-planning-budgeting'],
            ['name' => 'Before and After Projects', 'slug' => 'before-after-projects'],
            ['name' => 'Design Tools and Software', 'slug' => 'design-tools-software'],
        ];
    }
};
