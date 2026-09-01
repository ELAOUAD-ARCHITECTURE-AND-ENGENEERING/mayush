<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER IF NOT EXISTS inspirations_integrity_insert
            BEFORE INSERT ON inspirations
            WHEN NEW.hero_image IS NULL
                OR trim(NEW.hero_image) = ''
                OR NEW.status NOT IN ('draft', 'published', 'archived')
            BEGIN
                SELECT RAISE(ABORT, 'Invalid Inspiration image or status');
            END
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER IF NOT EXISTS inspirations_integrity_update
            BEFORE UPDATE OF hero_image, status ON inspirations
            WHEN NEW.hero_image IS NULL
                OR trim(NEW.hero_image) = ''
                OR NEW.status NOT IN ('draft', 'published', 'archived')
            BEGIN
                SELECT RAISE(ABORT, 'Invalid Inspiration image or status');
            END
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER IF NOT EXISTS inspiration_hotspots_coordinates_insert
            BEFORE INSERT ON inspiration_hotspots
            WHEN NEW.x IS NULL OR NEW.x < 0 OR NEW.x > 1
                OR NEW.y IS NULL OR NEW.y < 0 OR NEW.y > 1
            BEGIN
                SELECT RAISE(ABORT, 'Invalid Inspiration hotspot coordinates');
            END
        SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER IF NOT EXISTS inspiration_hotspots_coordinates_update
            BEFORE UPDATE OF x, y ON inspiration_hotspots
            WHEN NEW.x IS NULL OR NEW.x < 0 OR NEW.x > 1
                OR NEW.y IS NULL OR NEW.y < 0 OR NEW.y > 1
            BEGIN
                SELECT RAISE(ABORT, 'Invalid Inspiration hotspot coordinates');
            END
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS inspiration_hotspots_coordinates_update');
        DB::unprepared('DROP TRIGGER IF EXISTS inspiration_hotspots_coordinates_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS inspirations_integrity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS inspirations_integrity_insert');
    }
};
