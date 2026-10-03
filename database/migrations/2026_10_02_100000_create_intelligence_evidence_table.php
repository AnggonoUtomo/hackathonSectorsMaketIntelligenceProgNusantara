<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intelligence_evidence', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('fingerprint', 64)->unique();
            $table->string('symbol', 4)->index();
            $table->string('formula_version', 40);
            $table->json('input');
            $table->json('result');
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intelligence_evidence');
    }
};
