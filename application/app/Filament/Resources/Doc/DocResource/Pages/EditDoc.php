<?php

namespace App\Filament\Resources\Doc\DocResource\Pages;

use App\Filament\Resources\Doc\DocResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoc extends EditRecord
{
    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }
}
