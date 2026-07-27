<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classify_conversations')) {
            return;
        }

        // Merge duplicate buyer–seller threads (same store + customer) into the oldest conversation.
        $groups = DB::table('classify_conversations')
            ->select('store_id', 'customer_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('store_id', 'customer_id')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($groups as $group) {
            $duplicateIds = DB::table('classify_conversations')
                ->where('store_id', $group->store_id)
                ->where('customer_id', $group->customer_id)
                ->where('id', '!=', $group->keep_id)
                ->pluck('id');

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            DB::table('classify_messages')
                ->whereIn('conversation_id', $duplicateIds)
                ->update(['conversation_id' => $group->keep_id]);

            $lastMessage = DB::table('classify_messages')
                ->where('conversation_id', $group->keep_id)
                ->orderByDesc('id')
                ->first();

            $unreadCustomer = (int) DB::table('classify_conversations')
                ->where('store_id', $group->store_id)
                ->where('customer_id', $group->customer_id)
                ->sum('unread_customer');
            $unreadVendor = (int) DB::table('classify_conversations')
                ->where('store_id', $group->store_id)
                ->where('customer_id', $group->customer_id)
                ->sum('unread_vendor');

            DB::table('classify_conversations')->where('id', $group->keep_id)->update([
                'last_message_id' => $lastMessage->id ?? null,
                'last_message_at' => $lastMessage->created_at ?? null,
                'unread_customer' => $unreadCustomer,
                'unread_vendor' => $unreadVendor,
                'updated_at' => now(),
            ]);

            DB::table('classify_conversations')->whereIn('id', $duplicateIds)->delete();
        }

        $indexRows = DB::select("SHOW INDEX FROM classify_conversations WHERE Key_name = 'classify_conv_listing_customer_unique'");
        if (!empty($indexRows)) {
            Schema::table('classify_conversations', function (Blueprint $table) {
                $table->dropUnique('classify_conv_listing_customer_unique');
            });
        }

        $fkRows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classify_conversations'
              AND COLUMN_NAME = 'listing_id'
              AND REFERENCED_TABLE_NAME = 'classify_listings'
        ");
        foreach ($fkRows as $fk) {
            $name = $fk->CONSTRAINT_NAME ?? null;
            if ($name) {
                DB::statement("ALTER TABLE classify_conversations DROP FOREIGN KEY `{$name}`");
            }
        }

        DB::statement('ALTER TABLE classify_conversations MODIFY listing_id BIGINT UNSIGNED NULL');

        $storeUnique = DB::select("SHOW INDEX FROM classify_conversations WHERE Key_name = 'classify_conv_store_customer_unique'");
        if (empty($storeUnique)) {
            Schema::table('classify_conversations', function (Blueprint $table) {
                $table->unique(['store_id', 'customer_id'], 'classify_conv_store_customer_unique');
            });
        }

        $fkAfter = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classify_conversations'
              AND COLUMN_NAME = 'listing_id'
              AND REFERENCED_TABLE_NAME = 'classify_listings'
        ");
        if (empty($fkAfter)) {
            Schema::table('classify_conversations', function (Blueprint $table) {
                $table->foreign('listing_id')
                    ->references('id')
                    ->on('classify_listings')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Irreversible merge; leave schema as store+customer unique.
    }
};
