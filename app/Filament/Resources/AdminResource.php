<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\Qestass\User;
use App\Models\Qestass\AdminPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function getNavigationLabel(): string
    {
        return __('admins');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admins');
    }

    public static function getModelLabel(): string
    {
        return __('admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'admin')
            ->where('parent_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin_information'))
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('first_name'))
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('last_name'))
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('email')
                            ->label(__('email'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Forms\Components\TextInput::make('country_key')
                            ->label(__('country_key'))
                            ->required()
                            ->default('+966'),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('phone'))
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Forms\Components\TextInput::make('password')
                            ->label(__('password'))
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8),
                    ])->columns(2),
                
                Forms\Components\Section::make(__('permissions'))
                    ->schema([
                        Forms\Components\CheckboxList::make('adminPermissions')
                            ->relationship('adminPermissions', 'name')
                            ->label(__('permissions'))
                            ->columns(2)
                            // ->gridDirection('vertical')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('first_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('last_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('phone'))
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('status')
                //     ->label(__('status'))
                //     ->badge()
                //     ->color(fn (string $state): string => match ($state) {
                //         'active' => 'success',
                //         'inactive' => 'danger',
                //         default => 'gray',
                //     }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
