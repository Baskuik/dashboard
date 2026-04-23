<?php

namespace App\Filament\Admin\Resources\Uploads\Pages;

use App\Filament\Admin\Resources\Uploads\UploadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUpload extends EditRecord
{
    protected static string $resource = UploadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
