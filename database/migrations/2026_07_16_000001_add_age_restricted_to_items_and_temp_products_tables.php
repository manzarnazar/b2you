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
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'age_restricted')) {
                $table->boolean('age_restricted')->default(0);
            }
        });

        Schema::table('temp_products', function (Blueprint $table) {
            if (!Schema::hasColumn('temp_products', 'age_restricted')) {
                $table->boolean('age_restricted')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'age_restricted')) {
                $table->dropColumn('age_restricted');
            }
        });

        Schema::table('temp_products', function (Blueprint $table) {
            if (Schema::hasColumn('temp_products', 'age_restricted')) {
                $table->dropColumn('age_restricted');
            }
        });
    }
};
