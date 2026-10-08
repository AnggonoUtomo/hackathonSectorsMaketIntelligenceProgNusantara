<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_verification_links_redirect_without_notifications_or_data_changes(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $this->get('/verify-email')->assertRedirect(route('dashboard'));
        $this->post(route('verification.send'))->assertRedirect(route('dashboard'));
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $this->get($url)->assertRedirect(route('dashboard'));
        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_legacy_verification_routes_still_require_login(): void
    {
        $this->get('/verify-email')->assertRedirect('/login');
        $this->post(route('verification.send'))->assertRedirect('/login');
    }
}
