<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_factory_creates_users_with_ulid_identifiers()
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->assertIsString($firstUser->id);
        $this->assertTrue(Str::isUlid($firstUser->id));
        $this->assertTrue(Str::isUlid($secondUser->id));
        $this->assertNotSame($firstUser->id, $secondUser->id);
    }

    public function test_database_sessions_store_the_authenticated_user_ulid()
    {
        config(['session.driver' => 'database']);
        Session::driver()->flush();

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('sessions', [
            'id' => session()->getId(),
            'user_id' => $user->id,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        config(['session.driver' => 'database']);
        Session::driver()->flush();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $this->assertDatabaseMissing('sessions', [
            'user_id' => $user->id,
        ]);
        $response->assertRedirect('/');
    }
}
