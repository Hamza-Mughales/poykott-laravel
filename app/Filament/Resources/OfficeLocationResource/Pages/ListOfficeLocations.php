<?php

namespace App\Filament\Resources\OfficeLocationResource\Pages;

use App\Filament\Resources\OfficeLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficeLocations extends ListRecords
{
    protected static string $resource = OfficeLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
