<?php

namespace Tests\Feature\Portal;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_and_becomes_member(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('portal'));
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(RoleName::Member->value));
        $this->assertMatchesRegularExpression('/^MBR-\d{6}$/', $user->member_code);
    }

    public function test_registration_validates_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi2@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registered_user_can_login(): void
    {
        User::factory()->create(['email' => 'siti@example.com', 'password' => 'secret123']);

        $this->post('/login', ['email' => 'siti@example.com', 'password' => 'secret123'])
            ->assertRedirect(route('portal'));
        $this->assertAuthenticated();
    }
}
