<?php

namespace App\Modules\MarketData\Infrastructure\Credit;

use App\Modules\MarketData\Application\DTO\CreditReservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreditReservationService
{
    /**
     * Reserve provider credits before an upstream Sectors attempt.
     */
    public function reserve(
        string $userId,
        string $endpoint,
        int $estimatedCredits,
        string $correlationId,
        int $attempt = 1,
    ): CreditReservation {
        $maxAttempts = (int) config('marketdata.credits.max_attempts', 2);

        if ($attempt > $maxAttempts) {
            throw CreditReservationException::retryLimitExceeded();
        }

        return DB::transaction(function () use ($userId, $endpoint, $estimatedCredits, $correlationId, $attempt): CreditReservation {
            $existing = DB::table('market_data_credit_reservations')
                ->where('correlation_id', $correlationId)
                ->where('attempt', $attempt)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $this->reservationFromRow($existing);
            }

            $usageDate = Carbon::now((string) config('marketdata.credits.timezone', 'Asia/Jakarta'))->toDateString();
            $countedStatuses = ['reserved', 'committed'];

            $globalUsed = (int) DB::table('market_data_credit_reservations')
                ->whereIn('status', $countedStatuses)
                ->lockForUpdate()
                ->sum('estimated_credits');

            if (($globalUsed + $estimatedCredits) > (int) config('marketdata.credits.global_budget', 1000)) {
                throw CreditReservationException::budgetExhausted();
            }

            $dailyUsed = (int) DB::table('market_data_credit_reservations')
                ->where('user_id', $userId)
                ->where('usage_date', $usageDate)
                ->whereIn('status', $countedStatuses)
                ->lockForUpdate()
                ->sum('estimated_credits');

            if (($dailyUsed + $estimatedCredits) > (int) config('marketdata.credits.daily_user_quota', 20)) {
                throw CreditReservationException::dailyQuotaExhausted();
            }

            $id = strtolower((string) Str::ulid());
            $status = 'reserved';

            DB::table('market_data_credit_reservations')->insert([
                'id' => $id,
                'user_id' => $userId,
                'usage_date' => $usageDate,
                'endpoint' => $endpoint,
                'estimated_credits' => $estimatedCredits,
                'attempt' => $attempt,
                'status' => $status,
                'correlation_id' => $correlationId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return new CreditReservation(
                id: $id,
                userId: $userId,
                usageDate: $usageDate,
                endpoint: $endpoint,
                estimatedCredits: $estimatedCredits,
                attempt: $attempt,
                status: $status,
                correlationId: $correlationId,
            );
        });
    }

    private function reservationFromRow(object $row): CreditReservation
    {
        return new CreditReservation(
            id: (string) $row->id,
            userId: (string) $row->user_id,
            usageDate: (string) $row->usage_date,
            endpoint: (string) $row->endpoint,
            estimatedCredits: (int) $row->estimated_credits,
            attempt: (int) $row->attempt,
            status: (string) $row->status,
            correlationId: (string) $row->correlation_id,
        );
    }
}
