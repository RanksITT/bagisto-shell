<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The attributes a lubricant catalogue is browsed by. Every one is filterable so it can
     * drive layered navigation, and none is configurable: a pack size or viscosity grade is
     * its own SKU here rather than a variant axis, matching how the trade lists them.
     */
    const ATTRIBUTES = [
        'oil_grade' => [
            'admin_name' => 'Viscosity Grade',
            'options' => ['0W-20', '5W-30', '5W-40', '10W-30', '10W-40', '15W-40', '20W-40', '20W-50'],
        ],

        'pack_size' => [
            'admin_name' => 'Pack Size',
            'options' => ['0.8L', '1L', '3L', '4L', '5L', '20L'],
        ],

        'oil_type' => [
            'admin_name' => 'Oil Type',
            'options' => ['Full Synthetic', 'Semi Synthetic', 'Mineral'],
        ],

        'vehicle_type' => [
            'admin_name' => 'Vehicle Type',
            'options' => ['Motorcycle', 'Car', 'Heavy Duty Diesel'],
        ],
    ];

    /**
     * Brands stocked alongside the three the rebrand already added.
     */
    const ADDITIONAL_BRANDS = ['Castrol', 'Mobil', 'Total', 'Rancon'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::ATTRIBUTES as $code => $definition) {
            $attributeId = DB::table('attributes')->where('code', $code)->value('id');

            if (! $attributeId) {
                $attributeId = DB::table('attributes')->insertGetId([
                    'code' => $code,
                    'admin_name' => $definition['admin_name'],
                    'type' => 'select',
                    'swatch_type' => 'dropdown',
                    'is_required' => 0,
                    'is_unique' => 0,
                    'is_filterable' => 1,
                    'is_comparable' => 1,
                    'is_configurable' => 0,
                    'is_user_defined' => 1,
                    'is_visible_on_front' => 1,
                    'value_per_locale' => 0,
                    'value_per_channel' => 0,
                    'enable_wysiwyg' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->translate('attribute_translations', 'attribute_id', $attributeId, 'name', $definition['admin_name']);

                $this->mapToGeneralGroups($attributeId);
            }

            $this->addOptions($attributeId, $definition['options']);
        }

        $brandId = DB::table('attributes')->where('code', 'brand')->value('id');

        if ($brandId) {
            $this->addOptions($brandId, self::ADDITIONAL_BRANDS);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('attributes')->whereIn('code', array_keys(self::ATTRIBUTES))->delete();

        $brandId = DB::table('attributes')->where('code', 'brand')->value('id');

        if ($brandId) {
            DB::table('attribute_options')
                ->where('attribute_id', $brandId)
                ->whereIn('admin_name', self::ADDITIONAL_BRANDS)
                ->delete();
        }
    }

    /**
     * Add any option a select attribute does not already carry, with its translations.
     */
    protected function addOptions(int $attributeId, array $options): void
    {
        $existing = DB::table('attribute_options')
            ->where('attribute_id', $attributeId)
            ->pluck('admin_name')
            ->all();

        $sortOrder = (int) DB::table('attribute_options')->where('attribute_id', $attributeId)->max('sort_order');

        foreach ($options as $option) {
            if (in_array($option, $existing)) {
                continue;
            }

            $optionId = DB::table('attribute_options')->insertGetId([
                'attribute_id' => $attributeId,
                'admin_name' => $option,
                'sort_order' => ++$sortOrder,
            ]);

            $this->translate('attribute_option_translations', 'attribute_option_id', $optionId, 'label', $option);
        }
    }

    /**
     * Show the attribute in the General group of every family, so it can be edited on the
     * product form rather than only existing in the database.
     */
    protected function mapToGeneralGroups(int $attributeId): void
    {
        $groups = DB::table('attribute_groups')->where('code', 'general')->pluck('id');

        foreach ($groups as $groupId) {
            $exists = DB::table('attribute_group_mappings')
                ->where('attribute_id', $attributeId)
                ->where('attribute_group_id', $groupId)
                ->exists();

            if ($exists) {
                continue;
            }

            $position = (int) DB::table('attribute_group_mappings')
                ->where('attribute_group_id', $groupId)
                ->max('position');

            DB::table('attribute_group_mappings')->insert([
                'attribute_id' => $attributeId,
                'attribute_group_id' => $groupId,
                'position' => $position + 1,
            ]);
        }
    }

    /**
     * Write the same value into every locale the installation runs.
     */
    protected function translate(string $table, string $foreignKey, int $id, string $column, string $value): void
    {
        $locales = DB::table('locales')->pluck('code');

        if ($locales->isEmpty()) {
            $locales = collect([config('app.locale')]);
        }

        foreach ($locales as $locale) {
            DB::table($table)->insert([
                $foreignKey => $id,
                'locale' => $locale,
                $column => $value,
            ]);
        }
    }
};
