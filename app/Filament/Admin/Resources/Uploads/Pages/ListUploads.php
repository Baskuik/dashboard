<?php

namespace App\Filament\Admin\Resources\Uploads\Pages;

use App\Filament\Admin\Resources\Uploads\UploadResource;
use Filament\Resources\Pages\ListRecords;

class ListUploads extends ListRecords
{
    protected static string $resource = UploadResource::class;
}
