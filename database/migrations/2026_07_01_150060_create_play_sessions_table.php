<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->string('payment_method')->default('time_balance');
            $table->timestamp('started_at');
            $table->timestamp('planned_end_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('started_with_minutes')->default(0);
            $table->unsignedInteger('consumed_minutes')->default(0);
            $table->unsignedInteger('minutes_debited')->default(0);
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['station_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('time_ledger_entries', function (Blueprint $table) {
            $table->foreign('play_session_id')->references('id')->on('play_sessions')->nullOnDelete();
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->foreign('current_session_id')->references('id')->on('play_sessions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('time_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['play_session_id']);
        });
        Schema::table('stations', function (Blueprint $table) {
            $table->dropForeign(['current_session_id']);
        });
        Schema::dropIfExists('play_sessions');
    }
};
