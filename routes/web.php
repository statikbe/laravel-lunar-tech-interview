<?php

use App\Livewire\CheckoutPage;
use App\Livewire\CheckoutSuccessPage;
use App\Livewire\CollectionPage;
use App\Livewire\Home;
use App\Livewire\LoginPage;
use App\Livewire\ProductPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', Home::class);

Route::get('/collections/{slug}', CollectionPage::class)->name('collection.view');

Route::get('/products/{slug}', ProductPage::class)->name('product.view');

Route::get('checkout', CheckoutPage::class)->name('checkout.view');

Route::get('checkout/success', CheckoutSuccessPage::class)->name('checkout-success.view');

Route::get('login', LoginPage::class)->name('login');

Route::post('logout', function () {
    Auth::logout();

    session()->invalidate();
    session()->regenerateToken();

    return redirect('/');
})->name('logout');
