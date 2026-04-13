<?php

namespace App\Filament\Resources\Uploads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class UploadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('filename')->label('Bestandsnaam')->searchable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                    ]),
                TextColumn::make('processed_rows')->label('Verwerkte rijen')->numeric(),
                TextColumn::make('user.name')->label('Gebruiker'),
                TextColumn::make('created_at')->label('Geüpload op')->dateTime('d-m-Y H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped();
    }
}
