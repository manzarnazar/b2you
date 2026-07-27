<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassifyListingFieldValuesTable extends Migration
{
    public function up()
    {
        Schema::create('classify_listing_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->unsignedBigInteger('field_id')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'field_id']);
            $table->foreign('listing_id')
                ->references('id')
                ->on('classify_listings')
                ->cascadeOnDelete();
            $table->foreign('field_id')
                ->references('id')
                ->on('classify_category_fields')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classify_listing_field_values');
    }
}
