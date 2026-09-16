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
        if (Schema::getColumnType('users', 'id') !== 'varchar') {
            Schema::table('users', function (Blueprint $table) {
                $table->string('id')->change();
            });
        }

        $missingColumns = [
            'role' => ! Schema::hasColumn('users', 'role'),
            'area_id' => ! Schema::hasColumn('users', 'area_id'),
            'representative_id' => ! Schema::hasColumn('users', 'representative_id'),
            'phone' => ! Schema::hasColumn('users', 'phone'),
            'must_change_password' => ! Schema::hasColumn('users', 'must_change_password'),
        ];

        Schema::table('users', function (Blueprint $table) use ($missingColumns) {
            if ($missingColumns['role']) {
                $table->string('role', 30)->default('representante');
            }
            if ($missingColumns['area_id']) {
                $table->string('area_id')->nullable()->index();
            }
            if ($missingColumns['representative_id']) {
                $table->string('representative_id')->nullable()->index();
            }
            if ($missingColumns['phone']) {
                $table->string('phone')->nullable();
            }
            if ($missingColumns['must_change_password']) {
                $table->boolean('must_change_password')->default(false);
            }
        });
    }

    /**
     * The legacy integer schema cannot be safely restored after string IDs are stored.
     */
    public function down(): void {}
};
