<?php

namespace App\Filament\Resources\TicketThreadResource\RelationManagers;

use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Conversation';

    protected static ?string $recordTitleAttribute = 'message';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_staff')
                    ->label('Role')
                    ->boolean()
                    ->trueIcon('heroicon-o-briefcase')
                    ->falseIcon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('message')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('attachments_count')
                    ->counts('attachments')
                    ->label('Files')
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('0'),
                Tables\Columns\TextColumn::make('attachments')
                    ->label('Attachments')
                    ->html()
                    ->formatStateUsing(function ($record): string {
                        if ($record->attachments->isEmpty()) {
                            return '<span class="text-gray-400">-</span>';
                        }

                        return $record->attachments->map(function ($att) {
                            $url = Storage::disk('public')->url($att->path);
                            $size = $att->size ? ' <span class="text-gray-500 text-[10px]">'.round($att->size / 1024, 1).'KB</span>' : '';

                            return '<a href="'.$url.'" target="_blank" class="inline-flex items-center gap-1 text-primary-400 hover:text-primary-300 hover:underline">'.e($att->filename).$size.'</a>';
                        })->implode('<br>');
                    })
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->form([
                        Forms\Components\Section::make('New Reply')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Textarea::make('message')
                                    ->label('Message')
                                    ->required()
                                    ->rows(6)
                                    ->placeholder('Type your reply...')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('new_attachments')
                                    ->label('Attachments')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->maxSize(10240)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain', 'application/zip'])
                                    ->directory('ticket-attachments')
                                    ->columnSpanFull(),
                            ])->columns(1),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['is_staff'] = true;

                        return $data;
                    })
                    ->after(function (array $data): void {
                        $thread = $this->getOwnerRecord();

                        $message = TicketMessage::where('ticket_thread_id', $thread->id)
                            ->where('user_id', Auth::id())
                            ->latest()
                            ->first();

                        if ($message && ! empty($data['new_attachments']) && is_array($data['new_attachments'])) {
                            foreach ($data['new_attachments'] as $path) {
                                $fullPath = storage_path('app/public/'.$path);
                                TicketAttachment::create([
                                    'ticket_message_id' => $message->id,
                                    'filename' => basename($path),
                                    'path' => $path,
                                    'mime_type' => file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'application/octet-stream') : 'application/octet-stream',
                                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                                ]);
                            }
                        }

                        if ($thread->status === 'closed') {
                            $thread->update(['status' => 'answered']);
                        }

                        Notification::make()
                            ->title('Reply Sent')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Message')
                    ->modalDescription('Are you sure you want to delete this message? This cannot be undone.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Messages')
                        ->modalDescription('Are you sure you want to delete the selected messages? This cannot be undone.'),
                ]),
            ]);
    }
}
