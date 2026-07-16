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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_age_verified')) {
                $table->boolean('is_age_verified')->default(0);
            }
            if (!Schema::hasColumn('users', 'age_verified_at')) {
                $table->timestamp('age_verified_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'age_verification_document_type')) {
                $table->string('age_verification_document_type')->nullable();
            }
            if (!Schema::hasColumn('users', 'age_verification_document')) {
                $table->string('age_verification_document')->nullable();
            }
            if (!Schema::hasColumn('users', 'age_verified_by')) {
                $table->string('age_verified_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'date_of_birth',
                'is_age_verified',
                'age_verified_at',
                'age_verification_document_type',
                'age_verification_document',
                'age_verified_by',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
