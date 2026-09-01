<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $currencyId = DB::table('currencies')->where('code', 'BDT')->value('id');

        if (! $currencyId) {
            $currencyId = DB::table('currencies')->insertGetId([
                'code'              => 'BDT',
                'name'              => 'Bangladeshi Taka',
                'symbol'            => '৳',
                'decimal'           => 2,
                'group_separator'   => ',',
                'decimal_separator' => '.',
                'currency_position' => 'left',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        DB::table('channels')
            ->where('code', 'default')
            ->whereIn('base_currency_id', [null, DB::table('currencies')->where('code', 'USD')->value('id')])
            ->update(['base_currency_id' => $currencyId]);

        $channelId = DB::table('channels')->where('code', 'default')->value('id');

        if (
            $channelId
            && ! DB::table('channel_currencies')->where(['channel_id' => $channelId, 'currency_id' => $currencyId])->exists()
        ) {
            DB::table('channel_currencies')->insert([
                'channel_id'  => $channelId,
                'currency_id' => $currencyId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
