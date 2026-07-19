<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Url;

class LogViewer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Log Viewer';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Laravel Log Viewer';

    protected static string $view = 'filament.pages.log-viewer';

    public array $logs = [];

    public array $logFiles = [];

    #[Url]
    public string $selectedLog = 'laravel.log';

    #[Url]
    public string $filterLevel = 'all';

    public function mount(): void
    {
        $this->loadLogFiles();
        $this->loadLogs();
    }

    public function loadLogFiles(): void
    {
        $logPath = storage_path('logs');
        $this->logFiles = collect(File::files($logPath))
            ->filter(fn ($file) => $file->getExtension() === 'log')
            ->map(fn ($file) => $file->getFilename())
            ->values()
            ->toArray();
    }

    public function loadLogs(): void
    {
        $logPath = storage_path('logs/'.$this->selectedLog);

        if (! File::exists($logPath)) {
            $this->logs = [];

            return;
        }

        $content = $this->readLastLines($logPath, 500);
        $this->logs = $this->parseLogEntries($content);

        if ($this->filterLevel !== 'all') {
            $this->logs = array_filter($this->logs, fn ($entry) => strtolower($entry['level']) === $this->filterLevel);
            $this->logs = array_values($this->logs);
        }
    }

    private function readLastLines(string $path, int $lines): string
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return '';
        }

        $buffer = '';
        $lineCount = 0;
        $pos = filesize($path);

        while ($pos > 0 && $lineCount < $lines) {
            $readSize = min(4096, $pos);
            $pos -= $readSize;
            fseek($handle, $pos);
            $buffer = fread($handle, $readSize).$buffer;
            $lineCount = substr_count($buffer, "\n");
        }

        fclose($handle);

        $bufferLines = explode("\n", $buffer);
        if (count($bufferLines) > $lines) {
            $bufferLines = array_slice($bufferLines, -$lines);
        }

        return implode("\n", $bufferLines);
    }

    private function parseLogEntries(string $content): array
    {
        $entries = [];
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}[^\]]*)\] (\w+)\.(\w+): (.+)/';

        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (preg_match($pattern, $line, $matches)) {
                $entries[] = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'channel' => $matches[3],
                    'message' => $matches[4],
                ];
            } elseif (! empty($entries)) {
                $entries[count($entries) - 1]['message'] .= "\n".$line;
            }
        }

        return array_reverse($entries);
    }

    public function updatedSelectedLog(): void
    {
        $this->loadLogs();
    }

    public function setFilterLevel(string $value): void
    {
        $this->filterLevel = $value;
        $this->loadLogs();
    }
}
