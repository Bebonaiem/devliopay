<?php

namespace App\Filament\Resources\ServiceUpgradeResource\Pages;

use App\Filament\Resources\ServiceUpgradeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceUpgrades extends ListRecords
{
    protected static string $resource = ServiceUpgradeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
