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

        $user = User::where('email', 'klant@interview.test')->first();

        $this->assertNotNull($user, 'The fixed customer login is missing.');
        $this->assertTrue(
            Hash::check('password', $user->password),
            'The fixed customer password does not work.',
        );
    }

    public function test_the_customer_login_is_linked_to_a_lunar_customer(): void
    {
        $this->seed(InterviewSeeder::class);

        $user = User::where('email', 'klant@interview.test')->firstOrFail();
        $customer = Customer::where('company_name', 'Interview Testklant')->first();

        $this->assertNotNull($customer, 'The Lunar customer is missing.');
        $this->assertTrue(
            $customer->users()->where('users.id', $user->id)->exists(),
            'The customer login is not linked to the Lunar customer.',
        );
    }

    public function test_it_creates_an_admin_who_can_reach_the_backend(): void
    {
        $this->seed(InterviewSeeder::class);

        $staff = Staff::where('email', 'admin@interview.test')->first();

        $this->assertNotNull($staff, 'The fixed admin is missing.');
        $this->assertTrue((bool) $staff->admin, 'The admin is missing the admin flag.');
        $this->assertTrue(
            $staff->hasRole('admin'),
            'The admin has no admin role and would see an empty panel.',
        );
        $this->assertTrue(
            Hash::check('password', $staff->password),
            'The fixed admin password does not work.',
        );
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(InterviewSeeder::class);
        $this->seed(InterviewSeeder::class);

        $this->assertSame(1, User::where('email', 'klant@interview.test')->count());
        $this->assertSame(1, Staff::where('email', 'admin@interview.test')->count());
        $this->assertSame(1, Customer::where('company_name', 'Interview Testklant')->count());
    }
}
