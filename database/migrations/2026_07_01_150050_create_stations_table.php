<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('idle');
            $table->string('app_mode')->default('qr');
            $table->boolean('is_active')->default(true);
            $table->string('qr_token')->unique();
            $table->string('device_token')->nullable()->unique();
            $table->string('adb_identifier')->nullable();
            $table->unsignedBigInteger('current_session_id')->nullable()->index();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
