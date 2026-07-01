<?php

namespace App\Filament\Resources\Stations;

use App\Filament\Resources\Stations\Pages\CreateStation;
use App\Filament\Resources\Stations\Pages\EditStation;
use App\Filament\Resources\Stations\Pages\ListStations;
use App\Filament\Resources\Stations\Pages\ViewStation;
use App\Filament\Resources\Stations\Schemas\StationForm;
use App\Filament\Resources\Stations\Schemas\StationInfolist;
use App\Filament\Resources\Stations\Tables\StationsTable;
use App\Models\Station;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StationResource extends Resource
{
    protected static ?string $model = Station::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $modelLabel = 'Station';

    protected static ?string $pluralModelLabel = 'Stations';

    public static function form(Schema $schema): Schema
    {
        return StationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStations::route('/'),
            'create' => CreateStation::route('/create'),
            'view' => ViewStation::route('/{record}'),
            'edit' => EditStation::route('/{record}/edit'),
        ];
    }
}
