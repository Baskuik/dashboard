<?php

namespace App\Filament\Admin\Resources\Records\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('upload_id')
                    ->relationship('upload', 'bestand_id')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('action')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('worker')
                    ->required(),
                TextInput::make('time')
                    ->required()
                    ->numeric(),
                TextInput::make('costs')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
