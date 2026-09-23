<?php

namespace Tests\Feature\MarketData;

use App\Models\User;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationException;
use App\Modules\MarketData\Infrastructure\Credit\CreditReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreditReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reserves_credit_for_a_user_request(): void
    {
        Carbon::setTestNow('2026-09-21 01:00:00');

        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 1000,
            'marketdata.credits.daily_user_quota' => 20,
            'marketdata.credits.timezone' => 'Asia/Jakarta',
        ]);

        $reservation = app(CreditReservationService::class)->reserve(
            userId: $user->id,
            endpoint: 'companies',
            estimatedCredits: 1,
            correlationId: 'corr-success-1',
        );

        $this->assertSame($user->id, $reservation->userId);
        $this->assertSame('companies', $reservation->endpoint);
        $this->assertSame(1, $reservation->estimatedCredits);
        $this->assertSame('reserved', $reservation->status);

        $this->assertDatabaseHas('market_data_credit_reservations', [
            'id' => $reservation->id,
            'user_id' => $user->id,
            'endpoint' => 'companies',
            'estimated_credits' => 1,
            'status' => 'reserved',
            'usage_date' => '2026-09-21',
            'correlation_id' => 'corr-success-1',
        ]);
    }

    public function test_it_returns_existing_reservation_for_the_same_correlation_attempt(): void
    {
        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 1000,
            'marketdata.credits.daily_user_quota' => 20,
        ]);

        $service = app(CreditReservationService::class);
        $first = $service->reserve($user->id, 'companies', 1, 'corr-idempotent');
        $second = $service->reserve($user->id, 'companies', 1, 'corr-idempotent');

        $this->assertSame($first->id, $second->id);
        $this->assertSame($first->correlationId, $second->correlationId);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
    }

    public function test_it_rejects_reservations_that_exceed_the_global_budget(): void
    {
        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 2,
            'marketdata.credits.daily_user_quota' => 20,
        ]);

        $service = app(CreditReservationService::class);
        $service->reserve($user->id, 'companies', 2, 'corr-global-1');

        $this->expectException(CreditReservationException::class);
        $this->expectExceptionMessage('Sectors credit budget is exhausted.');

        $service->reserve($user->id, 'companies', 1, 'corr-global-2');
    }

    public function test_it_rejects_reservations_that_exceed_the_daily_user_quota(): void
    {
        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 1000,
            'marketdata.credits.daily_user_quota' => 3,
            'marketdata.credits.timezone' => 'Asia/Jakarta',
        ]);

        $service = app(CreditReservationService::class);
        $service->reserve($user->id, 'companies', 2, 'corr-daily-1');

        $this->expectException(CreditReservationException::class);
        $this->expectExceptionMessage('Daily Sectors credit quota is exhausted.');

        $service->reserve($user->id, 'companies', 2, 'corr-daily-2');
    }

    public function test_failed_reservations_do_not_write_ledger_rows(): void
    {
        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 1,
            'marketdata.credits.daily_user_quota' => 20,
        ]);

        try {
            app(CreditReservationService::class)->reserve(
                userId: $user->id,
                endpoint: 'companies',
                estimatedCredits: 2,
                correlationId: 'corr-failed-1',
            );

            $this->fail('Expected CreditReservationException.');
        } catch (CreditReservationException $exception) {
            $this->assertSame('Sectors credit budget is exhausted.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('market_data_credit_reservations', [
            'correlation_id' => 'corr-failed-1',
        ]);
    }

    public function test_daily_quota_uses_the_configured_wib_reset_date(): void
    {
        Carbon::setTestNow('2026-09-20 18:00:00 UTC');

        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 1000,
            'marketdata.credits.daily_user_quota' => 20,
            'marketdata.credits.timezone' => 'Asia/Jakarta',
        ]);

        $reservation = app(CreditReservationService::class)->reserve(
            userId: $user->id,
            endpoint: 'companies',
            estimatedCredits: 1,
            correlationId: 'corr-wib-1',
        );

        $this->assertSame('2026-09-21', $reservation->usageDate);
    }

    public function test_it_allows_only_one_retry_attempt_after_the_initial_attempt(): void
    {
        $user = User::factory()->create();

        config([
            'marketdata.credits.global_budget' => 1000,
            'marketdata.credits.daily_user_quota' => 20,
            'marketdata.credits.max_attempts' => 2,
        ]);

        $service = app(CreditReservationService::class);
        $service->reserve($user->id, 'companies', 1, 'corr-retry', 1);
        $service->reserve($user->id, 'companies', 1, 'corr-retry', 2);

        $this->expectException(CreditReservationException::class);
        $this->expectExceptionMessage('Sectors retry attempt limit exceeded.');

        $service->reserve($user->id, 'companies', 1, 'corr-retry', 3);
    }
}
