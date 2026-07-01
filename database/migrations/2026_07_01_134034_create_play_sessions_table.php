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
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->string('payment_method')->default('time_balance');
            $table->unsignedInteger('started_with_minutes')->default(0);
            $table->unsignedInteger('consumed_minutes')->default(0);
            $table->unsignedInteger('overage_minutes')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_sessions');
    }
};
