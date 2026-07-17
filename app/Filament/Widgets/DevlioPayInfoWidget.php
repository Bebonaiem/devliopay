<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use Filament\Widgets\Widget;

class DevlioPayInfoWidget extends Widget
{
    protected static string $view = 'filament.widgets.devliopay-info';

    public function getBrandName(): string
    {
        return Setting::get('company_name', config('app.name', 'DevlioPay'));
    }

    public function getVersion(): string
    {
        return 'v1.0.0';
    }
}
