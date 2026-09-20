<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified()
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_expired_signature()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_hash()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_for_a_different_authenticated_user()
    {
        $verificationOwner = User::factory()->unverified()->create([
            'email' => 'owner@example.com',
        ]);
        $otherUser = User::factory()->unverified()->create([
            'email' => 'other@example.com',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $verificationOwner->id, 'hash' => sha1($verificationOwner->email)]
        );

        $this->actingAs($otherUser)->get($verificationUrl)->assertForbidden();

        $this->assertFalse($verificationOwner->fresh()->hasVerifiedEmail());
        $this->assertFalse($otherUser->fresh()->hasVerifiedEmail());
    }

    public function test_verification_notification_can_be_resent()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this
            ->actingAs($user)
            ->post(route('verification.send', absolute: false))
            ->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_notification_resend_is_throttled()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this
                ->actingAs($user)
                ->post(route('verification.send', absolute: false));
        }

        $this
            ->actingAs($user)
            ->post(route('verification.send', absolute: false))
            ->assertTooManyRequests();
    }

    public function test_verified_users_are_redirected_from_resend_without_notification()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('verification.send', absolute: false))
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNothingSent();
    }
}
