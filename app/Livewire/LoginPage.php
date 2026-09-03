<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;

class LoginPage extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate()
    {
        $this->validate();

        // Grab the guest cart before signing in, so we can hand it over
        // afterwards. Without this the customer loses whatever they had
        // collected the moment they log in.
        $guestCart = CartSession::current();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        if ($guestCart) {
            // The merge policy folds the guest cart into any cart the
            // customer already had from an earlier visit.
            CartSession::associate($guestCart, Auth::user(), 'merge');
        }

        return redirect()->intended('/');
    }

    public function render(): View
    {
        return view('livewire.login-page');
    }
}
