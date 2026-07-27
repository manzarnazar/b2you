<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classify_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('classify_listings')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->index();
            $table->foreignId('store_id')->index();
            $table->foreignId('vendor_id')->index();
            $table->foreignId('customer_id')->index();
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_customer')->default(0);
            $table->unsignedInteger('unread_vendor')->default(0);
            $table->timestamps();

            $table->unique(['listing_id', 'customer_id'], 'classify_conv_listing_customer_unique');
        });

        Schema::create('classify_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('classify_conversations')->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'vendor']);
            $table->foreignId('customer_id')->nullable()->index();
            $table->foreignId('vendor_id')->nullable()->index();
            $table->text('message');
            $table->boolean('is_seen')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classify_messages');
        Schema::dropIfExists('classify_conversations');
    }
};
