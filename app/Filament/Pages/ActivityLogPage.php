<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use Filament\Pages\Page;

class ActivityLogPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Activity Log';

    protected static string $view = 'filament.pages.activity-log';

    public ?string $filterType = null;

    public int $page = 1;

    public int $perPage = 25;

    public function getLogs()
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        return $query->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function getTypes(): array
    {
        return ActivityLog::distinct()->pluck('type')->sort()->toArray();
    }
}
