<?php

namespace Modules\Classify\Console;

use Illuminate\Console\Command;
use Modules\Classify\Services\ListingExpiryService;
use Modules\Classify\Services\ListingService;

class ExpireListingsCommand extends Command
{
    protected $signature = 'classify:expire-listings';
    protected $description = 'Expire published Classify listings past their expires_at date';

    public function handle(ListingExpiryService $expiryService, ListingService $listingService): int
    {
        if (!$listingService->autoExpiryEnabled()) {
            $this->info('Auto expiry disabled.');
            return self::SUCCESS;
        }

        $count = $expiryService->expireDueListings();
        $this->info("Expired {$count} listings.");
        return self::SUCCESS;
    }
}
