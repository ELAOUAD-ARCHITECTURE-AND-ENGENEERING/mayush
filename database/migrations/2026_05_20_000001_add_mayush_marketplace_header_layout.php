<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('elements')) {
            Schema::create('elements', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('element_types')) {
            return;
        }

        if (!Schema::hasColumn('element_types', 'element_id')) {
            Schema::table('element_types', function (Blueprint $table) {
                $table->unsignedBigInteger('element_id')->nullable()->after('id');
            });
        }

        $headerElementId = DB::table('elements')->where('name', 'Header')->value('id');

        if (!$headerElementId) {
            $headerElementId = DB::table('elements')->insertGetId([
                'name' => 'Header',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (range(1, 6) as $headerNumber) {
            $this->upsertHeaderType('Header ' . $headerNumber, $headerElementId);
        }

        $this->upsertHeaderType('Header 7', $headerElementId);

        if (!Schema::hasTable('element_styles')) {
            return;
        }

        $headerTypeId = DB::table('element_types')->where('name', 'Header 7')->value('id');

        if (!$headerTypeId) {
            return;
        }

        foreach ($this->styles() as $name => $value) {
            DB::table('element_styles')->updateOrInsert(
                [
                    'element_type_id' => $headerTypeId,
                    'name' => $name,
                ],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('element_types')) {
            return;
        }

        $headerTypeId = DB::table('element_types')->where('name', 'Header 7')->value('id');

        if ($headerTypeId && Schema::hasTable('element_styles')) {
            DB::table('element_styles')->where('element_type_id', $headerTypeId)->delete();
        }

        DB::table('element_types')->where('name', 'Header 7')->delete();
    }

    private function styles(): array
    {
        return [
            'top_header_bg_color' => '#111827',
            'middle_header_bg_color' => '#111827',
            'bottom_header_bg_color' => '#243244',
            'top_header_text_color' => '#ffffff',
            'middle_header_text_color' => '#ffffff',
            'bottom_header_text_color' => '#ffffff',
        ];
    }

    private function upsertHeaderType(string $name, int $headerElementId): void
    {
        $values = [
            'is_default' => $name === 'Header 1' ? 1 : 0,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if (Schema::hasColumn('element_types', 'element_id')) {
            $values['element_id'] = $headerElementId;
        }

        DB::table('element_types')->updateOrInsert(['name' => $name], $values);
    }
};
