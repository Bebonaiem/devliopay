<?php

namespace App\Filament\Resources\TicketDepartmentResource\Pages;

use App\Filament\Resources\TicketDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketDepartment extends EditRecord
{
    protected static string $resource = TicketDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Record')
                ->modalDescription('Are you sure you want to delete this record? This action cannot be undone.'),
        ];
    }
}
