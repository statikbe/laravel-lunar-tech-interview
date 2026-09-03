<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

/**
 * A safety net over the cart totals.
 *
 * Not exhaustive on purpose. These tests describe what the cart does
 * today, so that anything which quietly changes the arithmetic shows up
 * as a red test instead of as a wrong number on an invoice.
 */
class CartTotalsTest extends TestCase
{
    use RefreshDatabase;

    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create(['default' => true]);

        $this->currency = Currency::factory()->create([
            'default' => true,
            'decimal_places' => 2,
        ]);
    }

    public function test_an_empty_cart_totals_zero(): void
    {
        $cart = Cart::factory()->create()->calculate();

        $this->assertSame(0, $cart->subTotal->value);
        $this->assertSame(0, $cart->total->value);
    }

    public function test_money_is_held_as_whole_units_of_the_smallest_denomination(): void
    {
        $cart = $this->cartHolding($this->variantCosting(5000));

        $this->assertIsInt($cart->subTotal->value);
        $this->assertIsInt($cart->taxTotal->value);
        $this->assertIsInt($cart->total->value);
    }

    public function test_a_line_multiplies_the_unit_price_by_the_quantity(): void
    {
        $variant = $this->variantCosting(5000);

        $cart = $this->cartHolding($variant, 3);
        $line = $cart->lines->first();

        $this->assertSame($variant->id, $line->purchasable_id);
        $this->assertSame(3, $line->quantity);
        $this->assertSame(15000, $line->subTotal->value);
    }

    public function test_the_parts_of_the_cart_add_up_to_the_total(): void
    {
        $cart = $this->cartHolding($this->variantCosting(5000), 2);

        $expected = $cart->subTotal->value
            - ($cart->discountTotal?->value ?? 0)
            + ($cart->taxTotal?->value ?? 0)
            + ($cart->shippingTotal?->value ?? 0);

        $this->assertSame($expected, $cart->total->value);
    }

    public function test_there_is_no_shipping_cost_until_an_option_is_chosen(): void
    {
        $cart = $this->cartHolding($this->variantCosting(5000));

        $this->assertNull(
            $cart->shippingTotal,
            'There should be no shipping cost until an option is chosen.',
        );
    }

    /**
     * A single-variant product priced in the default currency.
     *
     * @param  int  $price  in the smallest unit of the currency
     */
    private function variantCosting(int $price): ProductVariant
    {
        $currency = $this->currency;

        $product = Product::factory()
            ->has(
                ProductVariant::factory()->afterCreating(
                    function (ProductVariant $variant) use ($currency, $price) {
                        $variant->prices()->create(
                            Price::factory()->make([
                                'currency_id' => $currency->id,
                                'price' => $price,
                            ])->getAttributes()
                        );
                    }
                ),
                'variants'
            )
            ->create();

        return $product->variants->first();
    }

    private function cartHolding(ProductVariant $variant, int $quantity = 1): Cart
    {
        $cart = Cart::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $cart->add($variant, $quantity);

        return $cart->refresh()->calculate();
    }
}
