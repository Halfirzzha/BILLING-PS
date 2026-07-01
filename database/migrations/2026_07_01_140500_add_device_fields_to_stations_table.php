<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table): void {
            $table->string('device_token')->nullable()->unique()->after('qr_token');
            $table->string('device_status')->default('offline')->after('status');
            $table->string('app_mode')->default('qr')->after('device_status');
            $table->string('current_screen')->nullable()->after('adb_identifier');
            $table->string('device_version')->nullable()->after('current_screen');
            $table->timestamp('last_heartbeat_at')->nullable()->after('device_version');
            $table->timestamp('last_command_synced_at')->nullable()->after('last_heartbeat_at');
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table): void {
            $table->dropColumn([
                'device_token',
                'device_status',
                'app_mode',
                'current_screen',
                'device_version',
                'last_heartbeat_at',
                'last_command_synced_at',
            ]);
        });
    }
};
