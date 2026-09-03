<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Currency;

/**
 * Makes this a Belgian store.
 *
 * `lunar:install` always creates USD as the default currency, so we change
 * it here rather than leaving a dollar shop for a Belgian company. Prices
 * are stored as whole cents against the currency record, so renaming it
 * leaves every amount numerically untouched.
 *
 * Runs before the product seeders, since those price against the default
 * currency.
 */
class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::getDefault() ?? Currency::query()->firstOrFail();

        $currency->update([
            'code' => 'EUR',
            'name' => 'Euro',
            'exchange_rate' => 1,
            'decimal_places' => 2,
            'enabled' => true,
            'default' => true,
        ]);
    }
}
