<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\InterviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Customer;
use Tests\TestCase;

class InterviewSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_known_customer_login(): void
    {
        $this->seed(InterviewSeeder::class);

        $user = User::where('email', 'kandidaat@interview.test')->first();

        $this->assertNotNull($user, 'De vaste klantlogin ontbreekt.');
        $this->assertTrue(
            Hash::check('password', $user->password),
            'Het vaste wachtwoord van de klant werkt niet.',
        );
    }

    public function test_the_customer_login_is_linked_to_a_lunar_customer(): void
    {
        $this->seed(InterviewSeeder::class);

        $user = User::where('email', 'kandidaat@interview.test')->firstOrFail();
        $customer = Customer::where('company_name', 'Interview Testklant')->first();

        $this->assertNotNull($customer, 'De Lunar-klant ontbreekt.');
        $this->assertTrue(
            $customer->users()->where('users.id', $user->id)->exists(),
            'De klantlogin is niet aan de Lunar-klant gekoppeld.',
        );
    }

    public function test_it_creates_an_admin_who_can_reach_the_backend(): void
    {
        $this->seed(InterviewSeeder::class);

        $staff = Staff::where('email', 'admin@interview.test')->first();

        $this->assertNotNull($staff, 'De vaste beheerder ontbreekt.');
        $this->assertTrue((bool) $staff->admin, 'De beheerder heeft de admin-vlag niet.');
        $this->assertTrue(
            $staff->hasRole('admin'),
            'De beheerder heeft de rol admin niet en ziet dus een leeg paneel.',
        );
        $this->assertTrue(
            Hash::check('password', $staff->password),
            'Het vaste wachtwoord van de beheerder werkt niet.',
        );
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(InterviewSeeder::class);
        $this->seed(InterviewSeeder::class);

        $this->assertSame(1, User::where('email', 'kandidaat@interview.test')->count());
        $this->assertSame(1, Staff::where('email', 'admin@interview.test')->count());
        $this->assertSame(1, Customer::where('company_name', 'Interview Testklant')->count());
    }
}
