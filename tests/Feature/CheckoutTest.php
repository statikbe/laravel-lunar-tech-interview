<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as Money;
use Lunar\Facades\ShippingManifest;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use Tests\TestCase;

/**
 * A safety net over the step from cart to order.
 *
 * The invariant worth guarding is that an order records the same amount
 * the customer was shown. Anything that changes how a cart is paid for
 * has to keep that true.
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Currency $currency;

    private TaxClass $taxClass;

    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create(['default' => true]);
        TaxZone::factory()->create(['default' => true]);

        $this->taxClass = TaxClass::factory()->create(['default' => true]);

        $this->currency = Currency::factory()->create([
            'default' => true,
            'decimal_places' => 2,
        ]);
    }

    public function test_the_storefront_is_reachable(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_a_cart_becomes_an_order_recording_the_same_total(): void
    {
        $cart = $this->cartReadyForCheckout();

        $cartTotal = $cart->total->value;
        $this->assertGreaterThan(0, $cartTotal, 'The cart stayed empty.');

        $order = $cart->createOrder();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame(
            $cartTotal,
            $order->total->value,
            'The order total differs from what the customer saw in the cart.',
        );
    }

    public function test_the_order_keeps_a_line_for_every_line_in_the_cart(): void
    {
        $cart = $this->cartReadyForCheckout();

        $order = $cart->createOrder();

        $this->assertSame(
            $cart->lines->count(),
            $order->productLines->count(),
            'Lines went missing between cart and order.',
        );
    }

    private function cartReadyForCheckout(): Cart
    {
        $country = Country::factory()->create();

        $cart = Cart::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $cart->add($this->variantCosting(5000), 2);

        $address = [
            'first_name' => 'Interview',
            'last_name' => 'Klant',
            'line_one' => 'Teststraat 1',
            'city' => 'Gent',
            'postcode' => '9000',
            'country_id' => $country->id,
        ];

        $cart->setShippingAddress($address);
        $cart->setBillingAddress($address);
        $option = $this->flatRateShipping();
        ShippingManifest::addOption($option);
        $cart->setShippingOption($option);

        return $cart->refresh()->calculate();
    }

    /**
     * The kit ships no shipping methods by default. A cart resolves its
     * chosen option through the ShippingManifest, so the option has to be
     * registered there as well as set on the cart.
     */
    private function flatRateShipping(): ShippingOption
    {
        return new ShippingOption(
            name: 'Standaard verzending',
            description: 'Vaste verzendkost voor de tests',
            identifier: 'TEST-FLAT-RATE',
            price: new Money(500, $this->currency, 1),
            taxClass: $this->taxClass,
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
}
