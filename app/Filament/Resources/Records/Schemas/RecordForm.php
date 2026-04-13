<?php

namespace App\Filament\Resources\Records\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Upload;

class RecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('upload_id')
                    ->label('Excel upload')
                    ->options(function () {
                        return Upload::pluck('filename', 'id');
                    })
                    ->disabled(fn(?\App\Models\Record $record) => $record?->id !== null)
                    ->required(),

                Textarea::make('data')
                    ->label('Record gegevens (JSON)')
                    ->required()
                    ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(function ($state) {
                        try {
                            return json_decode($state, true) ?? [];
                        } catch (\Exception $e) {
                            return [];
                        }
                    })
                    ->columnSpanFull(),
            ]);
    }
}
