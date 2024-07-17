<?php

namespace App\Filament\Resources\Ord\TextResource\Pages;

use App\Filament\Resources\Ord\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTexts extends ListRecords
{
    protected static string $resource = TextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
