<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $missingImages = DB::table('inspirations')
            ->whereNull('hero_image')
            ->orWhere('hero_image', '')
            ->count();
        $invalidStatuses = DB::table('inspirations')
            ->whereNotIn('status', ['draft', 'published', 'archived'])
            ->count();
        $invalidCoordinates = DB::table('inspiration_hotspots')
            ->where(fn ($query) => $query
                ->where('x', '<', 0)->orWhere('x', '>', 1)
                ->orWhere('y', '<', 0)->orWhere('y', '>', 1))
            ->count();

        if ($missingImages || $invalidStatuses || $invalidCoordinates) {
            throw new RuntimeException(sprintf(
                'Inspiration data violates required constraints (missing images: %d, invalid statuses: %d, invalid coordinates: %d). Repair these rows before migrating.',
                $missingImages,
                $invalidStatuses,
                $invalidCoordinates
            ));
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE inspirations MODIFY hero_image VARCHAR(191) NOT NULL");
            DB::statement("ALTER TABLE inspirations MODIFY status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");
            DB::statement('ALTER TABLE inspiration_hotspots ADD CONSTRAINT inspiration_hotspots_x_check CHECK (x >= 0 AND x <= 1)');
            DB::statement('ALTER TABLE inspiration_hotspots ADD CONSTRAINT inspiration_hotspots_y_check CHECK (y >= 0 AND y <= 1)');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inspirations ALTER COLUMN hero_image SET NOT NULL');
            DB::statement("ALTER TABLE inspirations ADD CONSTRAINT inspirations_status_check CHECK (status IN ('draft','published','archived'))");
            DB::statement('ALTER TABLE inspiration_hotspots ADD CONSTRAINT inspiration_hotspots_x_check CHECK (x >= 0 AND x <= 1)');
            DB::statement('ALTER TABLE inspiration_hotspots ADD CONSTRAINT inspiration_hotspots_y_check CHECK (y >= 0 AND y <= 1)');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE inspiration_hotspots DROP CHECK inspiration_hotspots_x_check');
            DB::statement('ALTER TABLE inspiration_hotspots DROP CHECK inspiration_hotspots_y_check');
            DB::statement("ALTER TABLE inspirations MODIFY status VARCHAR(191) NOT NULL DEFAULT 'draft'");
            DB::statement('ALTER TABLE inspirations MODIFY hero_image VARCHAR(191) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inspiration_hotspots DROP CONSTRAINT inspiration_hotspots_x_check');
            DB::statement('ALTER TABLE inspiration_hotspots DROP CONSTRAINT inspiration_hotspots_y_check');
            DB::statement('ALTER TABLE inspirations DROP CONSTRAINT inspirations_status_check');
            DB::statement('ALTER TABLE inspirations ALTER COLUMN hero_image DROP NOT NULL');
        }
    }
};
