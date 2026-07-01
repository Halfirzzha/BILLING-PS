<?php

namespace Tests\Feature\Foundation;

use App\Enums\PaymentMethod;
use App\Enums\PlaySessionStatus;
use App\Enums\RoleName;
use App\Enums\StationAppMode;
use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Enums\StationStatus;
use App\Enums\TimeLedgerType;
use App\Enums\WalletTransactionType;
use Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_role_names_have_expected_values(): void
    {
        $this->assertSame('developer', RoleName::Developer->value);
        $this->assertSame('super_admin', RoleName::SuperAdmin->value);
        $this->assertSame('operator', RoleName::Operator->value);
        $this->assertSame('member', RoleName::Member->value);
        $this->assertCount(4, RoleName::cases());
    }

    public function test_domain_enums_expose_core_values(): void
    {
        $this->assertSame('idle', StationStatus::Idle->value);
        $this->assertSame('qr', StationAppMode::Qr->value);
        $this->assertSame('active', PlaySessionStatus::Active->value);
        $this->assertSame('time_balance', PaymentMethod::TimeBalance->value);
        $this->assertSame('top_up', WalletTransactionType::TopUp->value);
        $this->assertSame('session_debit', TimeLedgerType::SessionDebit->value);
        $this->assertSame('refresh_state', StationCommandType::RefreshState->value);
        $this->assertSame('pending', StationCommandStatus::Pending->value);
    }
}
