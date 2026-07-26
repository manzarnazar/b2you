<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassifyListingFavoritesTable extends Migration
{
    public function up()
    {
        Schema::create('classify_listing_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('listing_id')->constrained('classify_listings')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'listing_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('classify_listing_favorites');
    }
}
