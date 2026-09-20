<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NusaLensNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedPages(): array
    {
        return [
            'temukan saham' => ['/temukan-saham'],
            'perusahaan' => ['/perusahaan'],
            'bandingkan' => ['/bandingkan'],
            'jelaskan nilai' => ['/jelaskan-nilai'],
            'kandidat menarik' => ['/kandidat-menarik'],
        ];
    }

    #[DataProvider('protectedPages')]
    public function test_guests_are_redirected_from_nusalens_pages(string $path): void
    {
        $this->get($path)->assertRedirect('/login');
    }

    #[DataProvider('protectedPages')]
    public function test_verified_users_can_visit_nusalens_pages(string $path): void
    {
        $this->actingAs(User::factory()->create());

        $this->get($path)->assertOk();
    }

    #[DataProvider('protectedPages')]
    public function test_unverified_users_are_redirected_from_nusalens_pages(string $path): void
    {
        $user = User::factory()->unverified()->create();

        $this
            ->actingAs($user)
            ->get($path)
            ->assertRedirect(route('verification.notice', absolute: false));
    }
}
