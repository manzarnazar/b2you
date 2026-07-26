<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class SeedClassifyBusinessSettings extends Migration
{
    public function up()
    {
        $defaults = [
            'classify_listing_duration_days' => 30,
            'classify_listing_fee' => 0,
            'classify_commission_percent' => 0,
            'classify_approval_required' => 1,
            'classify_max_images' => 8,
            'classify_premium_enabled' => 1,
            'classify_premium_fee' => 0,
            'classify_premium_duration_days' => 7,
            'classify_featured_enabled' => 1,
            'classify_featured_fee' => 0,
            'classify_featured_duration_days' => 7,
            'classify_auto_expiry' => 1,
        ];

        foreach ($defaults as $key => $value) {
            $exists = DB::table('business_settings')->where('key', $key)->first();
            if (!$exists) {
                DB::table('business_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('business_settings')->whereIn('key', [
            'classify_listing_duration_days',
            'classify_listing_fee',
            'classify_commission_percent',
            'classify_approval_required',
            'classify_max_images',
            'classify_premium_enabled',
            'classify_premium_fee',
            'classify_premium_duration_days',
            'classify_featured_enabled',
            'classify_featured_fee',
            'classify_featured_duration_days',
            'classify_auto_expiry',
        ])->delete();
    }
}
