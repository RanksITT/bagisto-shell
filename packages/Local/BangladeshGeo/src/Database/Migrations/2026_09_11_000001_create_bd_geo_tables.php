<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bd_divisions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // Primary keys are UPSTREAM ids, not auto-increment, so re-import is idempotent.
            $table->unsignedInteger('id')->primary();
            $table->string('code', 64)->unique();
            $table->string('name', 64);
            $table->string('bn_name', 64)->nullable();
            $table->string('url', 128)->nullable();
            $table->enum('source', ['upstream', 'local'])->default('upstream');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('bd_districts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('division_id');
            $table->unsignedInteger('country_state_id')->nullable()->unique();

            // code == the exact district English name. INSERT-ONLY: never updated by a
            // dataset refresh, or a rename would invalidate addresses.state on old orders.
            $table->string('code', 64)->unique();
            $table->string('iso_code', 8)->nullable();
            $table->string('name', 64);
            $table->string('bn_name', 64)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->string('url', 128)->nullable();
            $table->boolean('has_city_corporation')->default(false);
            $table->enum('source', ['upstream', 'local'])->default('upstream');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['division_id', 'status', 'name']);
            $table->foreign('division_id')->references('id')->on('bd_divisions')->restrictOnDelete();
            $table->foreign('country_state_id')->references('id')->on('country_states')->nullOnDelete();
        });

        Schema::create('bd_upazilas', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // Upstream ids are 1..494; locally curated metro thanas use ids >= 900001
            // so an upstream refresh can never collide with them.
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('district_id');
            $table->string('name', 96);
            $table->string('bn_name', 96)->nullable();
            $table->string('url', 128)->nullable();

            // 'thana' rows are curated city-corporation areas: the same rung of the ladder,
            // a different flavour. Keeping them here keeps one code path everywhere.
            $table->enum('type', ['upazila', 'thana'])->default('upazila');
            $table->enum('source', ['upstream', 'local'])->default('upstream');

            // Denormalised, recomputed by bd-geo:sync. Drives the adaptive level-4 renderer
            // so the front end never fetches an empty union list just to discover it is empty.
            $table->unsignedSmallInteger('unions_count')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['district_id', 'name']);
            $table->index(['district_id', 'status', 'name']);
            $table->index('type');
            $table->foreign('district_id')->references('id')->on('bd_districts')->restrictOnDelete();
        });

        Schema::create('bd_unions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('upazila_id');
            $table->string('name', 96);
            $table->string('bn_name', 96)->nullable();
            $table->string('url', 128)->nullable();
            $table->enum('source', ['upstream', 'local'])->default('upstream');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['upazila_id', 'name']);
            $table->index(['upazila_id', 'status', 'name']);
            $table->foreign('upazila_id')->references('id')->on('bd_upazilas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bd_unions');
        Schema::dropIfExists('bd_upazilas');
        Schema::dropIfExists('bd_districts');
        Schema::dropIfExists('bd_divisions');
    }
};
