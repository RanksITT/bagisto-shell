<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The starting set of product brands sold on Rancon LubMart. More are added
     * through Admin > Catalog > Attributes > Brand as they come on board.
     */
    const BRANDS = ['Shell', 'Motul', 'Pristine'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $attributeId = DB::table('attributes')->where('code', 'brand')->value('id');

        if (! $attributeId) {
            return;
        }

        $existingNames = DB::table('attribute_options')
            ->where('attribute_id', $attributeId)
            ->pluck('admin_name')
            ->all();

        $locales = DB::table('locales')->pluck('code');

        if ($locales->isEmpty()) {
            $locales = collect([config('app.locale')]);
        }

        foreach (self::BRANDS as $index => $brand) {
            if (in_array($brand, $existingNames)) {
                continue;
            }

            $optionId = DB::table('attribute_options')->insertGetId([
                'attribute_id' => $attributeId,
                'admin_name' => $brand,
                'sort_order' => $index + 1,
            ]);

            foreach ($locales as $locale) {
                DB::table('attribute_option_translations')->insert([
                    'attribute_option_id' => $optionId,
                    'locale' => $locale,
                    'label' => $brand,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $attributeId = DB::table('attributes')->where('code', 'brand')->value('id');

        if (! $attributeId) {
            return;
        }

        DB::table('attribute_options')
            ->where('attribute_id', $attributeId)
            ->whereIn('admin_name', self::BRANDS)
            ->delete();
    }
};
