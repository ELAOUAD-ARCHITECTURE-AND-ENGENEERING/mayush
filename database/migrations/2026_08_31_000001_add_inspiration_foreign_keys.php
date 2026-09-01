<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // Match this application's legacy products/users primary-key widths exactly.
            DB::statement('ALTER TABLE inspiration_items MODIFY product_id INT NOT NULL');
            DB::statement('ALTER TABLE inspirations MODIFY created_by INT UNSIGNED NULL');

            $this->addMysqlForeign('inspiration_items', 'inspiration_items_inspiration_id_foreign',
                'FOREIGN KEY (inspiration_id) REFERENCES inspirations(id) ON DELETE CASCADE');
            $this->addMysqlForeign('inspiration_items', 'inspiration_items_product_id_foreign',
                'FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT');
            $this->addMysqlForeign('inspirations', 'inspirations_created_by_foreign',
                'FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL');
            $this->addMysqlForeign('inspiration_hotspots', 'inspiration_hotspots_inspiration_id_foreign',
                'FOREIGN KEY (inspiration_id) REFERENCES inspirations(id) ON DELETE CASCADE');
            $this->addMysqlForeign('inspiration_hotspots', 'inspiration_hotspots_inspiration_item_id_foreign',
                'FOREIGN KEY (inspiration_item_id) REFERENCES inspiration_items(id) ON DELETE CASCADE');

            if (!$this->mysqlIndexExists('inspiration_hotspots', 'hotspot_item_unique')) {
                DB::statement('ALTER TABLE inspiration_hotspots ADD CONSTRAINT hotspot_item_unique UNIQUE (inspiration_item_id)');
            }

            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite cannot safely add these constraints after table creation.
            // The initial Inspiration migration declares them inline instead.
            return;
        }

        Schema::table('inspiration_items', function (Blueprint $table) {
            $table->foreign('inspiration_id')->references('id')->on('inspirations')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
        Schema::table('inspirations', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('inspiration_hotspots', function (Blueprint $table) {
            $table->foreign('inspiration_id')->references('id')->on('inspirations')->cascadeOnDelete();
            $table->foreign('inspiration_item_id')->references('id')->on('inspiration_items')->cascadeOnDelete();
            $table->unique('inspiration_item_id', 'hotspot_item_unique');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('inspiration_hotspots', function (Blueprint $table) {
            $table->dropUnique('hotspot_item_unique');
            $table->dropForeign(['inspiration_item_id']);
            $table->dropForeign(['inspiration_id']);
        });
        Schema::table('inspiration_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['inspiration_id']);
        });
        Schema::table('inspirations', fn (Blueprint $table) => $table->dropForeign(['created_by']));
    }

    private function addMysqlForeign(string $table, string $name, string $definition): void
    {
        if (!$this->mysqlConstraintExists($table, $name)) {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} {$definition}");
        }
    }

    private function mysqlConstraintExists(string $table, string $name): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $name)
            ->exists();
    }

    private function mysqlIndexExists(string $table, string $name): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $name)
            ->exists();
    }
};
