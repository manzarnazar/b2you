<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassifyCategoryFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('classify_category_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('label');
            $table->string('name');
            $table->string('type', 32); // text, number, textarea, select, checkbox, radio, date, file
            $table->string('placeholder')->nullable();
            $table->text('default_value')->nullable();
            $table->boolean('is_required')->default(0);
            $table->boolean('is_active')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'name']);
            $table->index(['category_id', 'is_active', 'sort_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('classify_category_fields');
    }
}
