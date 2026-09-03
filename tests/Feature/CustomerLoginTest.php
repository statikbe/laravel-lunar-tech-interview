<?php

namespace Tests\Feature;

use App\Livewire\LoginPage;
use App\Models\User;
use Database\Seeders\InterviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create(['default' => true]);
        Channel::factory()->create(['default' => true]);
        Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

        $this->seed(InterviewSeeder::class);
    }

    public function test_the_login_page_is_reachable(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_a_customer_can_sign_in(): void
    {
        Livewire::test(LoginPage::class)
            ->set('email', 'klant@interview.test')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect('/');

        $this->assertAuthenticated();
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        Livewire::test(LoginPage::class)
            ->set('email', 'klant@interview.test')
            ->set('password', 'niet-het-wachtwoord')
            ->call('authenticate')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_guest_cart_follows_the_customer_after_signing_in(): void
    {
        CartSession::add($this->aVariant(), 1);
        $guestCart = CartSession::current();

        $this->assertNull($guestCart->user_id, 'De gastwinkelwagen hoort nog geen gebruiker te hebben.');

        Livewire::test(LoginPage::class)
            ->set('email', 'klant@interview.test')
            ->set('password', 'password')
            ->call('authenticate');

        $user = User::where('email', 'klant@interview.test')->firstOrFail();

        $this->assertSame(
            $user->id,
            Cart::find($guestCart->id)->user_id,
            'De winkelwagen is niet meegegaan naar de ingelogde klant.',
        );
    }

    public function test_the_cart_is_linked_to_the_lunar_customer_as_well(): void
    {
        CartSession::add($this->aVariant(), 1);
        $guestCart = CartSession::current();

        Livewire::test(LoginPage::class)
            ->set('email', 'klant@interview.test')
            ->set('password', 'password')
            ->call('authenticate');

        $user = User::where('email', 'klant@interview.test')->firstOrFail();

        $this->assertSame(
            $user->latestCustomer()->id,
            Cart::find($guestCart->id)->customer_id,
            'De winkelwagen hangt niet aan de Lunar-klant, dus een wallet op Customer is niet te vinden.',
        );
    }

    public function test_the_navigation_offers_a_way_in_when_nobody_is_signed_in(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Inloggen')
            ->assertDontSee('Uitloggen');
    }

    public function test_the_navigation_shows_who_is_signed_in(): void
    {
        $user = User::where('email', 'klant@interview.test')->firstOrFail();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Uitloggen');
    }

    public function test_a_customer_can_sign_out(): void
    {
        $user = User::where('email', 'klant@interview.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    private function aVariant(): ProductVariant
    {
        $currency = Currency::getDefault();

        $product = Product::factory()
            ->has(
                ProductVariant::factory()->afterCreating(
                    function (ProductVariant $variant) use ($currency) {
                        $variant->prices()->create(
                            Price::factory()->make([
                                'currency_id' => $currency->id,
                                'price' => 5000,
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
