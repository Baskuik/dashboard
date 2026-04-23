<?php

namespace App\Filament\Admin\Resources\Records\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('upload.bestand_id')
                    ->label('Upload'),
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('action'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('worker'),
                TextEntry::make('time')
                    ->numeric(),
                TextEntry::make('costs')
                    ->numeric(),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
