<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassifyListingImagesTable extends Migration
{
    public function up()
    {
        Schema::create('classify_listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('classify_listings')->cascadeOnDelete();
            $table->string('image');
            $table->string('storage')->default('public');
            $table->boolean('is_primary')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classify_listing_images');
    }
}
