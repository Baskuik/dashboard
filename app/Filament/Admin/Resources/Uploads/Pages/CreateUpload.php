<?php

namespace App\Filament\Admin\Resources\Uploads\Pages;

use App\Filament\Admin\Resources\Uploads\UploadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUpload extends CreateRecord
{
    protected static string $resource = UploadResource::class;
}
