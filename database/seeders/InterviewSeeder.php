<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Customer;

/**
 * Fixed logins for the technical interview.
 *
 * Every candidate starts from the same two accounts, so the day is
 * comparable and nobody spends time creating one. Idempotent on purpose:
 * running the seeder twice must not produce duplicates.
 */
class InterviewSeeder extends Seeder
{
    public function run(): void
    {
        $this->createCustomerLogin();
        $this->createAdminLogin();
    }

    /**
     * The storefront account. Signs in at /, checks out, owns the cart.
     */
    private function createCustomerLogin(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'klant@interview.test'],
            [
                'name' => 'Interview Klant',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $customer = Customer::updateOrCreate(
            ['company_name' => 'Interview Testklant'],
            [
                'first_name' => 'Interview',
                'last_name' => 'Klant',
            ],
        );

        $customer->users()->syncWithoutDetaching([$user->id]);
    }

    /**
     * The admin panel account at /lunar.
     *
     * Note this is a different model from the storefront login: the panel
     * authenticates Staff on the "staff" guard, not App\Models\User. The
     * admin flag grants elevated rights, but the panel only shows what the
     * assigned role permits — without the admin role you sign in to an
     * empty panel.
     */
    private function createAdminLogin(): void
    {
        $staff = Staff::updateOrCreate(
            ['email' => 'admin@interview.test'],
            [
                'first_name' => 'Interview',
                'last_name' => 'Beheerder',
                'password' => Hash::make('password'),
                'admin' => true,
            ],
        );

        $staff->assignRole('admin');
    }
}
