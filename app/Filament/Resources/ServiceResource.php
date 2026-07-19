<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Services\ServerProvisioningService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'Services';

    protected static ?string $navigationLabel = 'Services';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::where('status', 'active')->count());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Details')
                    ->schema([
                        Forms\Components\TextInput::make('uuid')
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('pricing_id')
                            ->relationship('pricing', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'cancelled' => 'Cancelled',
                                'terminated' => 'Terminated',
                            ])
                            ->default('pending'),
                        Forms\Components\TextInput::make('server_extension')
                            ->disabled(),
                        Forms\Components\TextInput::make('external_id')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('activated_at'),
                        Forms\Components\DateTimePicker::make('next_billing_at'),
                    ])->columns(3),

                Forms\Components\Section::make('Server Properties')
                    ->schema([
                        Forms\Components\KeyValue::make('server_properties')
                            ->reorderable(),
                    ])
                    ->collapsible()
                    ->visible(fn (?Service $record) => $record && $record->server_extension === 'pterodactyl'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.users.edit', $record->user_id)),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.products.edit', $record->product_id)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'suspended' => 'danger',
                        'cancelled' => 'gray',
                        'terminated' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('server_extension')
                    ->sortable(),
                Tables\Columns\TextColumn::make('server_properties.ip_address')
                    ->label('IP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_billing_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                        'terminated' => 'Terminated',
                    ]),
                Tables\Filters\SelectFilter::make('server_extension')
                    ->options([
                        'pterodactyl' => 'Pterodactyl',
                    ])
                    ->label('Server Type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('provision')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Provision Service')
                    ->modalDescription('This will create a server on Pterodactyl for this service.')
                    ->action(fn (Service $record) => static::provisionService($record))
                    ->visible(fn (Service $record) => $record->status === 'pending' && $record->server_extension),
                Tables\Actions\Action::make('suspend')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Service $record) => static::suspendService($record))
                    ->visible(fn (Service $record) => $record->status === 'active'),
                Tables\Actions\Action::make('unsuspend')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Service $record) => static::unsuspendService($record))
                    ->visible(fn (Service $record) => $record->status === 'suspended'),
                Tables\Actions\Action::make('terminate')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Terminate Service')
                    ->modalDescription('This will permanently delete the server on Pterodactyl. This cannot be undone.')
                    ->action(fn (Service $record) => static::terminateService($record))
                    ->visible(fn (Service $record) => in_array($record->status, ['active', 'suspended'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Records')
                        ->modalDescription('Are you sure you want to delete the selected records? This action cannot be undone.'),
                ]),
            ]);
    }

    public static function provisionService(Service $record): void
    {
        $provisioning = new ServerProvisioningService;
        $result = $provisioning->provision($record);

        if ($result['success']) {
            Notification::make()->title('Service provisioned successfully')->success()->send();
        } else {
            Notification::make()->title('Provisioning failed')->body($result['error'] ?? 'Unknown error')->danger()->send();
        }
    }

    public static function suspendService(Service $record): void
    {
        $provisioning = new ServerProvisioningService;
        $result = $provisioning->suspend($record);

        if ($result['success']) {
            Notification::make()->title('Service suspended')->success()->send();
        } else {
            Notification::make()->title('Suspension failed')->body($result['error'] ?? 'Unknown error')->danger()->send();
        }
    }

    public static function unsuspendService(Service $record): void
    {
        $provisioning = new ServerProvisioningService;
        $result = $provisioning->unsuspend($record);

        if ($result['success']) {
            Notification::make()->title('Service unsuspended')->success()->send();
        } else {
            Notification::make()->title('Unsuspend failed')->body($result['error'] ?? 'Unknown error')->danger()->send();
        }
    }

    public static function terminateService(Service $record): void
    {
        $provisioning = new ServerProvisioningService;
        $result = $provisioning->terminate($record);

        if ($result['success']) {
            Notification::make()->title('Service terminated')->success()->send();
        } else {
            Notification::make()->title('Termination failed')->body($result['error'] ?? 'Unknown error')->danger()->send();
        }
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
