<?php

namespace App\Filament\Resources\TimePackages;

use App\Filament\Resources\TimePackages\Pages\CreateTimePackage;
use App\Filament\Resources\TimePackages\Pages\EditTimePackage;
use App\Filament\Resources\TimePackages\Pages\ListTimePackages;
use App\Filament\Resources\TimePackages\Schemas\TimePackageForm;
use App\Filament\Resources\TimePackages\Tables\TimePackagesTable;
use App\Models\TimePackage;
use BackedEnum;
use App\Filament\Concerns\ScopesToOutlet;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimePackageResource extends Resource
{
    use ScopesToOutlet;

    protected static ?string $model = TimePackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TimePackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimePackagesTable::configure($table);
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
            'index' => ListTimePackages::route('/'),
            'create' => CreateTimePackage::route('/create'),
            'edit' => EditTimePackage::route('/{record}/edit'),
        ];
    }
}
