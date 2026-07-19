<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketThreadResource\Pages;
use App\Models\TicketThread;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketThreadResource extends Resource
{
    protected static ?string $model = TicketThread::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Support';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::whereIn('status', ['open', 'customer_reply'])->count());
    }

    public static function form(Form $form): Form
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Ticket')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('latestMessage.message')
                    ->label('Last Message')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Replies')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'answered' => 'success',
                        'closed' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'answered' => 'Answered',
                        'closed' => 'Closed',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->label('Department')
                    ->multiple(),
                Tables\Filters\Filter::make('unanswered')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'open')->where('updated_at', '<', now()->subHours(24)))
                    ->label('Unanswered 24h+')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Tickets')
                        ->modalDescription('Are you sure you want to delete the selected tickets? This action cannot be undone.'),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('Customer')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Name')
                                    ->icon('heroicon-m-user'),
                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope')
                                    ->copyable(),
                            ])->columns(1),

                        Infolists\Components\Section::make('Ticket Details')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Infolists\Components\TextEntry::make('number')
                                    ->label('Ticket Number')
                                    ->copyable()
                                    ->fontFamily('mono'),
                                Infolists\Components\TextEntry::make('department.name')
                                    ->label('Department')
                                    ->placeholder('Not assigned'),
                                Infolists\Components\TextEntry::make('service.product.name')
                                    ->label('Related Service')
                                    ->placeholder('None'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->since(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->since(),
                            ])->columns(1),

                        Infolists\Components\Section::make('Summary')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Infolists\Components\TextEntry::make('messages_count')
                                    ->label('Messages')
                                    ->state(fn (?TicketThread $record): int => $record?->messages?->count() ?? 0),
                                Infolists\Components\TextEntry::make('messages')
                                    ->label('Attachments')
                                    ->state(fn (?TicketThread $record): int => $record?->messages?->sum(fn ($m) => $m->attachments->count()) ?? 0),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketThreads::route('/'),
            'create' => Pages\CreateTicketThread::route('/create'),
            'edit' => Pages\EditTicketThread::route('/{record}/edit'),
        ];
    }
}
