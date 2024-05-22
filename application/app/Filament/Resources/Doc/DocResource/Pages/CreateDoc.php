<?php

namespace App\Filament\Resources\Doc\DocResource\Pages;

use App\Filament\Resources\Doc\DocResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDoc extends CreateRecord
{
    protected static string $resource = DocResource::class;
}
