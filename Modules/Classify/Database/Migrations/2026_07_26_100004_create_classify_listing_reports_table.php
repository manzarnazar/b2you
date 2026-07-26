<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassifyListingReportsTable extends Migration
{
    public function up()
    {
        Schema::create('classify_listing_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('classify_listings')->cascadeOnDelete();
            $table->foreignId('user_id')->index();
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending')->index(); // pending, resolved, dismissed
            $table->foreignId('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('classify_listing_reports');
    }
}
