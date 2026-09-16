<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('representatives', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('area_id')->index();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('postal_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('portfolio')->nullable();
            $table->text('client_focus')->nullable();
            $table->timestamps();
        });

        Schema::create('skus', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('category');
            $table->string('weight');
            $table->timestamps();
        });

        Schema::create('global_goals', function (Blueprint $table) {
            $table->string('period', 7)->primary();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('area_goals', function (Blueprint $table) {
            $table->string('period', 7);
            $table->string('area_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->primary(['period', 'area_id']);
        });

        Schema::create('representative_goals', function (Blueprint $table) {
            $table->string('period', 7);
            $table->string('representative_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->primary(['period', 'representative_id']);
        });

        Schema::create('manual_histories', function (Blueprint $table) {
            $table->string('period', 7);
            $table->string('representative_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
            $table->primary(['period', 'representative_id']);
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('period', 7)->index();
            $table->string('representative_id')->index();
            $table->string('area_id')->index();
            $table->string('sku_id')->nullable()->index();
            $table->string('client');
            $table->decimal('amount', 15, 2);
            $table->decimal('volume', 15, 3)->default(0);
            $table->string('week', 2);
            $table->unsignedBigInteger('occurred_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('manual_histories');
        Schema::dropIfExists('representative_goals');
        Schema::dropIfExists('area_goals');
        Schema::dropIfExists('global_goals');
        Schema::dropIfExists('skus');
        Schema::dropIfExists('representatives');
        Schema::dropIfExists('areas');
    }
};
