<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The brand name every storefront-facing default-channel string is rebranded to.
     */
    const BRAND_NAME = 'Rancon LubMart';

    /**
     * The seeded placeholder channel names, one per shipped locale, this migration is safe to
     * overwrite. A channel already renamed by an admin keeps whatever name they gave it.
     */
    const PLACEHOLDER_NAMES = [
        'Default', 'افتراضي', 'ডিফল্ট', 'Predeterminat', 'Standard', 'Predeterminado',
        'پیش‌فرض', 'Défaut', 'ברירת מחדל', 'डिफ़ॉल्ट', 'Predefinito', 'デフォルト',
        'Standaard', 'Domyślny', 'Padrão', 'Implicit', 'По умолчанию', 'පෙරනිමවේ',
        'Varsayılan', 'За замовчуванням', '默認',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $channelId = DB::table('channels')->where('code', 'default')->value('id');

        if (! $channelId) {
            return;
        }

        DB::table('channel_translations')
            ->where('channel_id', $channelId)
            ->whereIn('name', self::PLACEHOLDER_NAMES)
            ->update(['name' => self::BRAND_NAME]);

        DB::table('channel_translations')
            ->where('channel_id', $channelId)
            ->whereNull('logo_alt')
            ->update(['logo_alt' => self::BRAND_NAME]);

        DB::table('channel_translations')
            ->where('channel_id', $channelId)
            ->get(['id', 'home_seo'])
            ->each(function ($translation) {
                $homeSeo = json_decode($translation->home_seo ?? '{}', true) ?: [];

                if (! in_array($homeSeo['meta_title'] ?? null, array_merge(self::PLACEHOLDER_NAMES, ['Demo store']))) {
                    return;
                }

                $homeSeo['meta_title'] = self::BRAND_NAME;

                DB::table('channel_translations')
                    ->where('id', $translation->id)
                    ->update(['home_seo' => json_encode($homeSeo)]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
