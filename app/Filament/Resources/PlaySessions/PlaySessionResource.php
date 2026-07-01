<?php

namespace App\Filament\Resources\PlaySessions;

use App\Filament\Resources\PlaySessions\Pages\CreatePlaySession;
use App\Filament\Resources\PlaySessions\Pages\EditPlaySession;
use App\Filament\Resources\PlaySessions\Pages\ListPlaySessions;
use App\Filament\Resources\PlaySessions\Pages\ViewPlaySession;
use App\Filament\Resources\PlaySessions\Schemas\PlaySessionForm;
use App\Filament\Resources\PlaySessions\Schemas\PlaySessionInfolist;
use App\Filament\Resources\PlaySessions\Tables\PlaySessionsTable;
use App\Models\PlaySession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlaySessionResource extends Resource
{
    protected static ?string $model = PlaySession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function form(Schema $schema): Schema
    {
        return PlaySessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PlaySessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlaySessionsTable::configure($table);
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
            'index' => ListPlaySessions::route('/'),
            'view' => ViewPlaySession::route('/{record}'),
            'edit' => EditPlaySession::route('/{record}/edit'),
        ];
    }
}
