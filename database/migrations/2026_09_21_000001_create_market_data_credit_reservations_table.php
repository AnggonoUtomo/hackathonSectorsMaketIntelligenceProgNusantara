<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_data_credit_reservations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('user_id')->nullable()->index();
            $table->date('usage_date')->index();
            $table->string('endpoint', 120);
            $table->unsignedInteger('estimated_credits');
            $table->unsignedInteger('attempt')->default(1);
            $table->string('status', 32)->default('reserved')->index();
            $table->string('correlation_id', 120);
            $table->timestamps();

            $table->unique(['correlation_id', 'attempt'], 'market_data_credit_reservations_correlation_attempt_unique');
            $table->index(['user_id', 'usage_date', 'status'], 'market_data_credit_reservations_user_day_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_data_credit_reservations');
    }
};
