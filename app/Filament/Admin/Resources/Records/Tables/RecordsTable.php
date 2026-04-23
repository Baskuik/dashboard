<?php

namespace App\Filament\Admin\Resources\Records\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecordsTable
{
    public function getTabs(): array
    {
        return [
            'all' => 'Standaard', // Toekomstige tabs kunnen hier worden toegevoegd, bijvoorbeeld per upload of per gebruiker
            // Toekomstige tabs kunnen hier worden toegevoegd, bijvoorbeeld per upload of per gebruiker
        ];
    }
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Upload Bestand
                TextColumn::make('upload.bestand_id')
                    ->label('Upload ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // Date
                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // Action
                TextColumn::make('action')
                    ->label('Actie')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // Worker
                TextColumn::make('worker')
                    ->label('Medewerker')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // Time
                TextColumn::make('time')
                    ->label('Tijd (minuten)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // Costs
                TextColumn::make('costs')
                    ->label('Kosten (€)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // User ID
                TextColumn::make('user_id')
                    ->label('Gebruiker ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Created At
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Updated At
                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filters kunnen hier worden toegevoegd
            ])
            ->paginationPageOptions([25, 50, 100, 250, 500])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                ViewAction::make()
                    ->label('Bekijken'),
            ]);
    }
}
