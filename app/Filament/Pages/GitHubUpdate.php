<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class GitHubUpdate extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 12;

    protected static ?string $title = 'GitHub Update';

    protected static string $view = 'filament.pages.github-update';

    public string $currentCommit = '';

    public string $currentBranch = '';

    public string $remoteUrl = '';

    public array $commits = [];

    public bool $isUpdating = false;

    public string $updateOutput = '';

    public function mount(): void
    {
        $this->loadGitInfo();
    }

    public function loadGitInfo(): void
    {
        $base = base_path();

        $this->currentCommit = trim(Process::run("git -C {$base} rev-parse --short HEAD")->output() ?: 'unknown');
        $this->currentBranch = trim(Process::run("git -C {$base} branch --show-current")->output() ?: 'unknown');
        $this->remoteUrl = trim(Process::run("git -C {$base} remote get-url origin")->output() ?: 'not set');

        $log = Process::run("git -C {$base} log --oneline -10")->output();
        $this->commits = array_filter(array_map('trim', explode("\n", $log)));
    }

    public function pullUpdates(): void
    {
        $this->isUpdating = true;
        $this->updateOutput = '';
        $base = base_path();

        $steps = [
            ['label' => 'Pulling from GitHub...', 'cmd' => "git -C {$base} pull origin {$this->currentBranch} 2>&1", 'required' => true],
            ['label' => 'Installing PHP dependencies...', 'cmd' => "cd {$base} && composer install --no-dev --optimize-autoloader --no-interaction 2>&1", 'required' => true],
            ['label' => 'Installing Node dependencies...', 'cmd' => "cd {$base} && npm install 2>&1", 'required' => false],
            ['label' => 'Building frontend assets...', 'cmd' => "cd {$base} && npm run build 2>&1", 'required' => false],
            ['label' => 'Running migrations...', 'cmd' => "cd {$base} && php artisan migrate --force --no-interaction 2>&1", 'required' => true],
            ['label' => 'Clearing caches...', 'cmd' => "cd {$base} && php artisan config:clear && php artisan route:clear && php artisan view:clear 2>&1", 'required' => true],
            ['label' => 'Caching routes...', 'cmd' => "cd {$base} && php artisan route:cache 2>&1", 'required' => true],
            ['label' => 'Caching views...', 'cmd' => "cd {$base} && php artisan view:cache 2>&1", 'required' => true],
        ];

        foreach ($steps as $step) {
            $result = Process::run($step['cmd']);
            $status = $result->successful() ? 'OK' : 'FAILED';
            $this->updateOutput .= "[{$status}] {$step['label']}\n";

            $output = trim($result->output());
            $errors = trim($result->errorOutput());
            $combined = trim($output . "\n" . $errors);

            if ($combined) {
                $this->updateOutput .= $combined . "\n";
            }

            if (!$result->successful() && ($step['required'] ?? true)) {
                $this->updateOutput .= "\nUpdate aborted due to critical failure.\n";
                break;
            }

            $this->updateOutput .= "\n";
        }

        $this->loadGitInfo();
        $this->isUpdating = false;

        Notification::make()
            ->title('Update Complete')
            ->success()
            ->send();
    }
}
