<?php

namespace Modules\Classify\Database\Seeders;

use App\Models\Category;
use App\Models\Module;
use App\Models\Store;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Classify\Entities\ClassifyListing;
use Modules\Classify\Entities\ClassifyListingImage;

class ClassifyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('module_type', 'classify')->first();
        if (!$module) {
            $this->command?->warn('Classify module not found. Skip seeding.');
            return;
        }

        $store = Store::where('module_id', $module->id)->where('status', 1)->first();
        if (!$store) {
            $this->command?->warn('No active Classify store found. Create a store first, then re-run: php artisan db:seed --class=Modules\\Classify\\Database\\Seeders\\ClassifyDemoSeeder');
            return;
        }

        $placeholderName = $this->ensurePlaceholderImage();

        $parentCategories = [
            'Mobile Phones',
            'Cars',
            'Motorcycles',
            'Property',
            'Electronics',
            'Furniture',
            'Fashion',
            'Jobs',
        ];

        $categoryIds = [];
        foreach ($parentCategories as $index => $name) {
            $existing = Category::withoutGlobalScopes()
                ->where('module_id', $module->id)
                ->where('position', 0)
                ->where('name', $name)
                ->first();
            if ($existing) {
                $categoryIds[] = $existing->id;
                continue;
            }
            $category = Category::withoutGlobalScopes()->create([
                'name' => $name,
                'module_id' => $module->id,
                'position' => 0,
                'parent_id' => 0,
                'status' => 1,
                'priority' => $index + 1,
            ]);
            Translation::updateOrCreate(
                [
                    'translationable_type' => Category::class,
                    'translationable_id' => $category->id,
                    'locale' => 'en',
                    'key' => 'name',
                ],
                ['value' => $name]
            );
            $categoryIds[] = $category->id;
        }

        $locations = [
            ['city' => 'Larkana', 'lat' => '27.5590', 'lng' => '68.2120', 'address' => 'Larkana, Pakistan'],
            ['city' => 'Feni', 'lat' => '23.0159', 'lng' => '91.3976', 'address' => 'Feni, Bangladesh'],
            ['city' => 'London', 'lat' => '51.5074', 'lng' => '-0.1278', 'address' => 'London, UK'],
        ];

        $conditions = ['new', 'used', 'refurbished'];
        $titles = [
            'iPhone 13 Pro', 'Samsung Galaxy S22', 'Toyota Corolla 2018', 'Honda Civic 2016',
            'Studio Apartment', 'Office Desk', 'Leather Sofa', 'Winter Jacket',
            'Gaming Laptop', 'DSLR Camera', 'Mountain Bike', 'Electric Scooter',
            'Wedding Dress', 'Office Chair', 'Smart TV 55"', 'PlayStation 5',
            'MacBook Air', 'Dining Table Set', 'Baby Crib', 'Vacuum Cleaner',
            'Gold Necklace', 'Used Refrigerator', 'Plot for Sale', 'Warehouse Space',
        ];

        $created = 0;
        foreach ($titles as $i => $title) {
            $slug = Str::slug($title) . '-demo-' . ($i + 1);
            if (ClassifyListing::where('slug', $slug)->exists()) {
                continue;
            }

            $loc = $locations[$i % count($locations)];
            $categoryId = $categoryIds[$i % count($categoryIds)];

            $listing = ClassifyListing::create([
                'module_id' => $module->id,
                'store_id' => $store->id,
                'vendor_id' => $store->vendor_id,
                'zone_id' => $store->zone_id,
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'description' => "Demo listing for {$title}. Replace images and details from admin or vendor panel.",
                'price' => rand(50, 5000),
                'is_negotiable' => (bool) rand(0, 1),
                'condition' => $conditions[$i % 3],
                'phone' => $store->phone,
                'address' => $loc['address'],
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'status' => 'published',
                'is_approved' => 1,
                'published_at' => now()->subDays(rand(1, 14)),
                'expires_at' => now()->addDays(30),
            ]);

            ClassifyListingImage::create([
                'listing_id' => $listing->id,
                'image' => $placeholderName,
                'storage' => 'public',
                'is_primary' => 1,
                'sort_order' => 0,
            ]);

            $created++;
        }

        $this->command?->info("Classify demo seed complete: {$created} listings, " . count($categoryIds) . ' categories.');
    }

    private function ensurePlaceholderImage(): string
    {
        $filename = 'placeholder.png';
        $targetDir = storage_path('app/public/classify');
        $targetPath = $targetDir . '/' . $filename;

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (!File::exists($targetPath)) {
            $candidates = [
                base_path('../client_b2you/public/static/no-image-found.png'),
                public_path('assets/admin/img/160x160/img2.jpg'),
            ];
            foreach ($candidates as $source) {
                if (File::exists($source)) {
                    File::copy($source, $targetPath);
                    break;
                }
            }
        }

        return $filename;
    }
}
