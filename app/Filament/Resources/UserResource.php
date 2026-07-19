<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::count());
    }

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->label('Password'),
                    ])->columns(3),

                Forms\Components\Section::make('Billing Information')
                    ->schema([
                        Forms\Components\TextInput::make('company'),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('address'),
                        Forms\Components\TextInput::make('city'),
                        Forms\Components\TextInput::make('state'),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(2),
                        Forms\Components\TextInput::make('zip_code'),
                    ])->columns(4),

                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_admin')
                            ->label('Admin Access'),
                        Forms\Components\TextInput::make('balance')
                            ->numeric()
                            ->prefix(fn () => \App\Models\Currency::defaultSymbol())
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('services_count')
                    ->counts('services')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->counts('orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Admin'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Record')
                    ->modalDescription('Are you sure you want to delete this record? This action cannot be undone.'),
                Tables\Actions\Action::make('loginAs')
                    ->label('Login As')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Login as User')
                    ->modalDescription(fn ($record) => "You will be logged in as {$record->name}. Continue?")
                    ->visible(fn ($record) => !$record->is_admin)
                    ->action(function ($record) {
                        Auth::login($record);
                        return redirect('/client');
                    }),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ServicesRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
            RelationManagers\TransactionsRelationManager::class,
            RelationManagers\TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
