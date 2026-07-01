<?php

namespace Tests\Feature\Portal;

use App\Enums\PaymentMethod;
use App\Enums\PlaySessionStatus;
use App\Models\Station;
use App\Models\TimeLedgerEntry;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PortalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_join_stores_station_context_and_guest_is_sent_to_login(): void
    {
        $station = Station::factory()->create();

        $this->get('/join/'.$station->qr_token)
            ->assertRedirect(route('portal'))
            ->assertSessionHas('join_station_id', $station->id);

        $this->get('/portal')->assertRedirect(route('login'));
    }

    public function test_portal_shows_balances(): void
    {
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 90]);

        $this->actingAs($user)->get('/portal')
            ->assertOk()
            ->assertSee('Saldo Wallet')
            ->assertSee('90');
    }

    public function test_member_can_buy_package_with_wallet(): void
    {
        $user = User::factory()->create();
        app(BillingService::class)->topUpWallet($user, 50000);
        $package = TimePackage::factory()->create(['minutes' => 60, 'price' => 20000]);

        $this->actingAs($user)->post("/portal/purchase/{$package->id}")
            ->assertRedirect();

        $user->refresh();
        $this->assertSame(30000, $user->wallet_balance);
        $this->assertSame(60, $user->remaining_minutes);
    }

    public function test_start_session_requires_time_balance(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $station = Station::factory()->create();

        $this->actingAs($user)->post('/portal/session/start', ['station_id' => $station->id])
            ->assertSessionHasErrors('session');

        $this->assertDatabaseCount('play_sessions', 0);
    }

    public function test_member_can_start_and_end_session(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();

        $this->actingAs($user)->post('/portal/session/start', ['station_id' => $station->id])
            ->assertRedirect();
        $this->assertDatabaseHas('play_sessions', [
            'user_id' => $user->id,
            'station_id' => $station->id,
            'status' => PlaySessionStatus::Active->value,
        ]);

        $this->actingAs($user)->post('/portal/session/end')->assertRedirect();
        $this->assertDatabaseHas('play_sessions', [
            'user_id' => $user->id,
            'status' => PlaySessionStatus::Completed->value,
        ]);
    }
}
