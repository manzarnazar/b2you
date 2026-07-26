<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassifyListingsTable extends Migration
{
    public function up()
    {
        Schema::create('classify_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->index();
            $table->foreignId('store_id')->nullable()->index();
            $table->foreignId('vendor_id')->nullable()->index();
            $table->foreignId('zone_id')->nullable()->index();
            $table->foreignId('category_id')->nullable()->index();
            $table->foreignId('sub_category_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->longText('description')->nullable();
            $table->decimal('price', 23, 8)->default(0);
            $table->boolean('is_negotiable')->default(0);
            $table->string('condition')->default('used'); // new, used, refurbished
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('status')->default('pending')->index(); // draft, pending, published, rejected, sold, expired, archived
            $table->boolean('is_approved')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_premium')->default(0);
            $table->boolean('is_featured')->default(0);
            $table->timestamp('premium_until')->nullable();
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('favorites_count')->default(0);
            $table->unsignedInteger('chats_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classify_listings');
    }
}
