<?php

namespace App\Filament\Resources\TicketThreadResource\Pages;

use App\Filament\Resources\TicketThreadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketThread extends CreateRecord
{
    protected static string $resource = TicketThreadResource::class;

    protected static ?string $title = 'Create Ticket';

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Ticket created successfully';
    }
}
