<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // Required data
        $required = collect([
            ['name' => 'The Victoria',  'price' => 374662, 'bedrooms' => 4, 'bathrooms' => 2, 'storeys' => 2, 'garages' => 2],
            ['name' => 'The Xavier',    'price' => 513268, 'bedrooms' => 4, 'bathrooms' => 2, 'storeys' => 1, 'garages' => 2],
            ['name' => 'The Como',      'price' => 454990, 'bedrooms' => 4, 'bathrooms' => 3, 'storeys' => 2, 'garages' => 3],
            ['name' => 'The Aspen',     'price' => 384356, 'bedrooms' => 4, 'bathrooms' => 2, 'storeys' => 2, 'garages' => 2],
            ['name' => 'The Lucretia',  'price' => 572002, 'bedrooms' => 4, 'bathrooms' => 3, 'storeys' => 2, 'garages' => 2],
            ['name' => 'The Toorak',    'price' => 521951, 'bedrooms' => 5, 'bathrooms' => 2, 'storeys' => 1, 'garages' => 2],
            ['name' => 'The Skyscape',  'price' => 263604, 'bedrooms' => 3, 'bathrooms' => 2, 'storeys' => 2, 'garages' => 2],
            ['name' => 'The Clifton',   'price' => 386103, 'bedrooms' => 3, 'bathrooms' => 2, 'storeys' => 1, 'garages' => 1],
            ['name' => 'The Geneva',    'price' => 390600, 'bedrooms' => 4, 'bathrooms' => 3, 'storeys' => 2, 'garages' => 2],
        ])->map(fn($item) => array_merge($item, [
            'created_at' => $now,
            'updated_at' => $now,
        ]))->toArray();

        Property::insert($required);

        // Fake data - optimized bulk insert
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');

        $fake = Property::factory()->count(10000)->make()->map(function ($item) use ($now) {
            return array_merge($item->toArray(), [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        })->toArray();

        // Insert in chunks of 1000
        foreach (array_chunk($fake, 1000) as $chunk) {
            Property::insert($chunk);
        }

        DB::statement('SET UNIQUE_CHECKS=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
