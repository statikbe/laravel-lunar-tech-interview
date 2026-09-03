<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Country;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        $taxClass = TaxClass::first();

        $country = Country::firstWhere('iso3', 'BEL');

        $taxZone = TaxZone::factory()->create([
            'name' => 'Belgium',
            'active' => true,
            'default' => true,
            'zone_type' => 'country',
        ]);

        TaxZoneCountry::factory()->create([
            'country_id' => $country->id,
            'tax_zone_id' => $taxZone->id,
        ]);

        $rate = TaxRate::factory()->create([
            'name' => 'BTW',
            'tax_zone_id' => $taxZone->id,
            'priority' => 1,
        ]);

        $rate->taxRateAmounts()->createMany([
            [
                'percentage' => 21,
                'tax_class_id' => $taxClass->id,
            ],
        ]);
    }
}
