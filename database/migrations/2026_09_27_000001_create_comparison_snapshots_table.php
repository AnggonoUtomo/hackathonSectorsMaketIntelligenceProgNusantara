<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparison_snapshots', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->json('symbols');
            $table->json('payload');
            $table->unsignedInteger('version')->default(1);
            $table->ulid('created_from_snapshot_id')->nullable();
            $table->timestamps();

            $table->foreign('created_from_snapshot_id', 'comparison_snapshots_parent_foreign')
                ->references('id')->on('comparison_snapshots')->nullOnDelete();
            $table->index(['user_id', 'created_at'], 'comparison_snapshots_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparison_snapshots');
    }
};
