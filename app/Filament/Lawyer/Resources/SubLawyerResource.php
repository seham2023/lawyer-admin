<?php

namespace App\Filament\Lawyer\Resources;

use App\Filament\Lawyer\Resources\SubLawyerResource\Pages;
use App\Models\Qestass\User;
use App\Models\Qestass\AdminPermission;
use App\Models\LawyerUser;
use App\Support\LawyerUserAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubLawyerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationLabel(): string
    {
        return __('sub_lawyers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('sub_lawyer_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sub_lawyers');
    }

    public static function getModelLabel(): string
    {
        return __('sub_lawyer');
    }

    public static function getEloquentQuery(): Builder
    {
        $lawyerId = auth()->id();
        $subLawyerIds = LawyerUserAccess::userIdsForLawyer($lawyerId, 'sub_lawyer');

        return parent::getEloquentQuery()
            ->where('type', 'admin')
            ->whereIn('id', $subLawyerIds);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('sub_lawyer_information'))
                    ->description(__('sub_lawyer.basic_details_help'))
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('first_name'))
                            ->required()
                            ->maxLength(191)
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\TextInput::make('last_name')
                            ->label(__('last_name'))
                            ->required()
                            ->maxLength(191)
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\TextInput::make('email')
                            ->label(__('email'))
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191)
                            ->prefixIcon('heroicon-o-envelope'),

                        Forms\Components\TextInput::make('password')
                            ->label(__('password'))
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->prefixIcon('heroicon-o-lock-closed'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('country_key')
                                    ->label(__('country_key'))
                                    ->options([
                                        '00966' => '+966 (SA)',
                                        '00971' => '+971 (AE)',
                                        '00973' => '+973 (BH)',
                                        '002' => '+2 (EG)',
                                    ])
                                    ->default('00966')
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-globe-alt')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('phone')
                                    ->label(__('mobile'))
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(191)
                                    ->prefixIcon('heroicon-o-phone')
                                    ->columnSpan(2),
                            ])->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make(__('permissions'))
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label(__('roles')),
                        Forms\Components\Select::make('permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label(__('permissions')),
                    ])->columns(2),
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
                    ->label(__('name'))
                    ->getStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('phone'))
                    ->getStateUsing(fn ($record) => $record->country_key . ' ' . $record->phone)
                    ->searchable(['phone', 'country_key'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialist_type')
                    ->label(__('role'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'lawyer' => 'info',
                        'assistant' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => __($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
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
                Tables\Actions\Action::make('delete_sub_lawyer')
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        LawyerUser::query()
                            ->where('lawyer_id', auth()->id())
                            ->where('user_id', $record->id)
                            ->where('user_type', 'sub_lawyer')
                            ->delete();

                        \Filament\Notifications\Notification::make()
                            ->title(__('Sub Lawyer deleted.'))
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListSubLawyers::route('/'),
            'create' => Pages\CreateSubLawyer::route('/create'),
            'edit' => Pages\EditSubLawyer::route('/{record}/edit'),
        ];
    }
}
