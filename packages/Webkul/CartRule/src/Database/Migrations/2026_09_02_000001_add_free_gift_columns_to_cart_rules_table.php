<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_rules', 'gift_product_id')) {
                /**
                 * The product handed over when a `free_gift` rule is satisfied. Nulled rather
                 * than cascaded on delete, so removing a gift product disables the offer
                 * instead of silently deleting the promotion that referenced it.
                 */
                $table->integer('gift_product_id')->unsigned()->nullable()->after('free_shipping');

                $table->foreign('gift_product_id')->references('id')->on('products')->onDelete('set null');
            }

            if (! Schema::hasColumn('cart_rules', 'gift_qty')) {
                $table->integer('gift_qty')->unsigned()->default(1)->after('gift_product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_rules', function (Blueprint $table) {
            if (Schema::hasColumn('cart_rules', 'gift_product_id')) {
                $table->dropForeign(['gift_product_id']);

                $table->dropColumn('gift_product_id');
            }

            if (Schema::hasColumn('cart_rules', 'gift_qty')) {
                $table->dropColumn('gift_qty');
            }
        });
    }
};
