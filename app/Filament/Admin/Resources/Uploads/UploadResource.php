<?php

namespace App\Filament\Admin\Resources\Uploads;

use App\Filament\Admin\Resources\Uploads\Pages\ListUploads;
use App\Filament\Admin\Resources\Uploads\Schemas\UploadForm;
use App\Filament\Admin\Resources\Uploads\Tables\UploadsTable;
use App\Models\Upload;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UploadResource extends Resource
{
    protected static ?string $model = Upload::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    public static function form(Schema $schema): Schema
    {
        return UploadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UploadsTable::configure($table);
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
            'index' => ListUploads::route('/'),
        ];
    }
}
