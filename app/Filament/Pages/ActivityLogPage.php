<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ActivityLogPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Activity Log';

    protected static string $view = 'filament.pages.activity-log';

    #[Url]
    public ?string $filterType = null;

    #[Url(as: 'page')]
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

    public function updatedFilterType(): void
    {
        $this->page = 1;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
    }
}
