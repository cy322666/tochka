<?php

namespace App\Filament\Resources\Doc\DocResource\Pages;

use App\Filament\Resources\Doc\DocResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocs extends ListRecords
{
    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
