<?php

namespace App\Filament\Resources\TicketThreadResource\Pages;

use App\Filament\Resources\TicketThreadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketThreads extends ListRecords
{
    protected static string $resource = TicketThreadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
