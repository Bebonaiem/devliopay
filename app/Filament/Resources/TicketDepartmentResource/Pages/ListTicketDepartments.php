<?php

namespace App\Filament\Resources\TicketDepartmentResource\Pages;

use App\Filament\Resources\TicketDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketDepartments extends ListRecords
{
    protected static string $resource = TicketDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
