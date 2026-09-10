<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // No FK constraints here: address rows are historical snapshots shared by carts,
            // orders and the address book. ON DELETE SET NULL would silently corrupt old
            // orders, so geo rows are retired with status=0 rather than deleted.
            $table->unsignedInteger('bd_division_id')->nullable()->after('country')->index();
            $table->unsignedInteger('bd_district_id')->nullable()->after('bd_division_id')->index();
            $table->unsignedInteger('bd_upazila_id')->nullable()->after('bd_district_id')->index();
            $table->unsignedInteger('bd_union_id')->nullable()->after('bd_upazila_id')->index();

            // Human-readable level 4 whatever its provenance: a snapshot of the union name for
            // rural addresses, or the customer's typed area for metro thanas. A copy, not a
            // join, so it is immune to later renames.
            // bd_union_id IS NOT NULL distinguishes structured from free text.
            $table->string('bd_area_name', 128)->nullable()->after('bd_union_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'bd_division_id',
                'bd_district_id',
                'bd_upazila_id',
                'bd_union_id',
                'bd_area_name',
            ]);
        });
    }
};
