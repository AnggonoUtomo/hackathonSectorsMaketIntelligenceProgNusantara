<?php

namespace Tests\Feature\Release;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_inertia_paths_match_the_case_sensitive_source_directory(): void
    {
        $this->assertSame([resource_path('js/pages')], config('inertia.page_paths'));
        $this->assertSame([resource_path('js/pages')], config('inertia.testing.page_paths'));
        $this->assertFileExists(app('inertia.testing.view-finder')->find('nusalens/company-profile'));
    }

    public function test_public_registration_is_rate_limited_without_creating_accounts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/register', [])->assertUnprocessable();
        }
        $this->postJson('/register', [])->assertTooManyRequests();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_reset_endpoint_has_ip_limit_even_with_rotating_email(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/forgot-password', ['email' => 'invalid-'.$i])->assertUnprocessable();
        }
        $this->postJson('/forgot-password', ['email' => 'another'])->assertTooManyRequests();
    }

    public function test_comparison_write_limit_is_per_account_and_makes_no_provider_calls(): void
    {
        Http::preventStrayRequests();
        $this->actingAs(User::factory()->create());
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/bandingkan/snapshots', [])->assertUnprocessable();
        }
        $this->postJson('/bandingkan/snapshots', [])->assertTooManyRequests();
        $this->actingAs(User::factory()->create())->postJson('/bandingkan/snapshots', [])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_security_headers_do_not_force_hsts_on_local_http(): void
    {
        $this->get('/login')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'")
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_https_production_response_sets_hsts(): void
    {
        $this->app['env'] = 'production';
        $this->get('https://nusalens.example/login')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}
