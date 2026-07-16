<?php

namespace App\Filament\Resources\TicketThreadResource\Pages;

use App\Filament\Resources\TicketThreadResource;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketThread;
use App\Notifications\TicketReply as TicketReplyNotification;
use App\Services\NotificationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EditTicketThread extends EditRecord
{
    protected static string $resource = TicketThreadResource::class;

    protected static ?string $title = 'Ticket';

    protected static string $view = 'filament.resources.ticket-thread-resource.pages.edit-ticket-thread';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->refreshTicket();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Ticket Details')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\TextInput::make('number')
                                    ->label('Ticket Number')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('subject')
                                    ->label('Subject')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('user_id')
                                    ->label('Customer')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name')
                                    ->nullable()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                Forms\Components\Select::make('service_id')
                                    ->label('Related Service')
                                    ->relationship('service', 'uuid')
                                    ->nullable()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name ?? $record->uuid)
                                    ->columnSpan(1),
                            ])->columns(3),

                        Forms\Components\Section::make('Status & Priority')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'open' => 'Open',
                                        'answered' => 'Answered',
                                        'closed' => 'Closed',
                                    ])
                                    ->default('open')
                                    ->native(false)
                                    ->columnSpan(1),
                                Forms\Components\Select::make('priority')
                                    ->label('Priority')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'urgent' => 'Urgent',
                                    ])
                                    ->default('medium')
                                    ->native(false)
                                    ->columnSpan(1),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Quick Info')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Created')
                                    ->content(fn (?TicketThread $record): string => $record?->created_at?->diffForHumans() ?? '-'),
                                Forms\Components\Placeholder::make('messages_count')
                                    ->label('Total Messages')
                                    ->content(fn (?TicketThread $record): string => (string) ($record?->messages?->count() ?? $record?->messages_count ?? 0)),
                                Forms\Components\Placeholder::make('attachments_count')
                                    ->label('Total Attachments')
                                    ->content(fn (?TicketThread $record): string => (string) ($record?->messages?->sum(fn ($m) => $m->attachments->count()) ?? 0)),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reply')
                ->label('Send Reply')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading('Reply to Ticket')
                ->modalWidth(MaxWidth::TwoExtraLarge)
                ->modalSubmitActionLabel('Send Reply')
                ->form([
                    Forms\Components\RichEditor::make('message')
                        ->label('Message')
                        ->required()
                        ->placeholder('Type your reply...')
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Attachments (optional)')
                        ->multiple()
                        ->maxFiles(5)
                        ->maxSize(10240)
                        ->acceptedFileTypes([
                            'image/jpeg', 'image/png', 'image/gif',
                            'application/pdf', 'text/plain', 'application/zip',
                        ])
                        ->directory('ticket-attachments')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $message = TicketMessage::create([
                        'ticket_thread_id' => $this->record->id,
                        'user_id' => Auth::id(),
                        'message' => $data['message'],
                        'is_staff' => true,
                    ]);

                    if (! empty($data['attachments']) && is_array($data['attachments'])) {
                        foreach ($data['attachments'] as $path) {
                            $fullPath = storage_path('app/public/' . $path);
                            TicketAttachment::create([
                                'ticket_message_id' => $message->id,
                                'filename' => basename($path),
                                'path' => $path,
                                'mime_type' => file_exists($fullPath)
                                    ? (mime_content_type($fullPath) ?: 'application/octet-stream')
                                    : 'application/octet-stream',
                                'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                            ]);
                        }
                    }

                    if ($this->record->status === 'closed') {
                        $this->record->update(['status' => 'answered']);
                    }

                    $ticketUser = $this->record->user;
                    if ($ticketUser) {
                        app(NotificationService::class)->notify($ticketUser, new TicketReplyNotification($this->record, $message));
                    }

                    Notification::make()
                        ->title('Reply sent')
                        ->success()
                        ->send();

                    $this->refreshTicket();
                }),

            Actions\ActionGroup::make([
                Actions\Action::make('changeStatus')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('gray')
                    ->modalHeading('Change Ticket Status')
                    ->modalWidth(MaxWidth::Small)
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Open',
                                'answered' => 'Answered',
                                'closed' => 'Closed',
                            ])
                            ->default(fn () => $this->record->status)
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data): void {
                        $this->record->update(['status' => $data['status']]);
                        Notification::make()->title('Status updated')->success()->send();
                        $this->refreshTicket();
                    }),

                Actions\Action::make('changePriority')
                    ->label('Change Priority')
                    ->icon('heroicon-o-flag')
                    ->color('gray')
                    ->modalHeading('Change Ticket Priority')
                    ->modalWidth(MaxWidth::Small)
                    ->form([
                        Forms\Components\Select::make('priority')
                            ->label('Priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default(fn () => $this->record->priority)
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (array $data): void {
                        $this->record->update(['priority' => $data['priority']]);
                        Notification::make()->title('Priority updated')->success()->send();
                        $this->refreshTicket();
                    }),
            ])
                ->dropdownWidth(MaxWidth::ExtraSmall)
                ->label('Actions')
                ->icon('heroicon-o-chevron-down'),

            Actions\Action::make('toggleStatus')
                ->label($this->record->status === 'closed' ? 'Reopen' : 'Close')
                ->icon($this->record->status === 'closed' ? 'heroicon-o-lock-open' : 'heroicon-o-check-circle')
                ->color($this->record->status === 'closed' ? 'success' : 'warning')
                ->requiresConfirmation()
                ->modalHeading($this->record->status === 'closed' ? 'Reopen Ticket' : 'Close Ticket')
                ->modalDescription(
                    $this->record->status === 'closed'
                        ? 'Are you sure you want to reopen this ticket?'
                        : 'Are you sure you want to close this ticket?'
                )
                ->action(function (): void {
                    $newStatus = $this->record->status === 'closed' ? 'open' : 'closed';
                    $this->record->update(['status' => $newStatus]);
                    Notification::make()
                        ->title($newStatus === 'closed' ? 'Ticket closed' : 'Ticket reopened')
                        ->success()
                        ->send();
                    $this->refreshTicket();
                }),

            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Ticket')
                ->modalDescription('Permanently delete this ticket and all messages. This action cannot be undone.')
                ->action(function (): void {
                    $this->record->delete();
                    Notification::make()->title('Ticket deleted')->success()->send();
                    $this->redirect(TicketThreadResource::getUrl('index'));
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form'),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Ticket updated';
    }

    public function sendQuickReply(string $message): void
    {
        $message = trim($message);
        if (empty($message)) {
            return;
        }

        $msg = TicketMessage::create([
            'ticket_thread_id' => $this->record->id,
            'user_id' => Auth::id(),
            'message' => $message,
            'is_staff' => true,
        ]);

        if ($this->record->status === 'closed') {
            $this->record->update(['status' => 'answered']);
        }

        $ticketUser = $this->record->user;
        if ($ticketUser) {
            app(NotificationService::class)->notify($ticketUser, new TicketReplyNotification($this->record, $msg));
        }

        $this->refreshTicket();
    }

    protected function refreshTicket(): void
    {
        $this->record = TicketThread::with([
            'user',
            'department',
            'service.product',
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.user',
            'messages.attachments',
        ])->findOrFail($this->record->id);
    }

    public function getViewData(): array
    {
        $this->record->loadMissing([
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.user',
            'messages.attachments',
        ]);

        $messages = $this->record->messages;

        return [
            'ticket' => $this->record,
            'messages' => $messages,
            'groupedMessages' => $messages->groupBy(fn ($m) => $m->created_at->format('Y-m-d')),
        ];
    }
}
