<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Outerweb\FilamentSettings\Pages\Settings;

class BusinessSettings extends Settings
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Business Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->schema([
                        TextInput::make('business.name')
                            ->label('Business name')
                            ->default('Billing PS5')
                            ->required(),
                        TextInput::make('business.currency')
                            ->default('IDR')
                            ->required(),
                        TextInput::make('billing.default_hourly_rate')
                            ->numeric()
                            ->default(20000)
                            ->required(),
                        Toggle::make('billing.allow_wallet_purchase')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
