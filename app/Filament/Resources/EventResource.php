<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Event;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EventResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EventResource\RelationManagers;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationLabel(): string
    {
        return __('Events');
    }

    public static function getModelLabel(): string
    {
        return __('Event');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Events');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('legal_management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('Event Title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('Event Description'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('start')
                    ->label(__('Start Date & Time'))
                    ->required(),
                Forms\Components\DateTimePicker::make('end')
                    ->label(__('End Date & Time'))
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label(__('Event Type'))
                    ->options([
                        'general' => __('general'),
                        'meeting' => __('meeting'),
                        'holiday' => __('holiday'),
                        'deadline' => __('deadline'),
                        'appointment' => __('appointment'),
                    ])
                    ->default('general')
                    ->required(),
                Forms\Components\ColorPicker::make('color')
                    ->label(__('Event Color'))
                    ->default('#3b82f6'),
                Forms\Components\Toggle::make('all_day')
                    ->label(__('All Day Event'))
                    ->default(false),
                Hidden::make('user_id')
                    ->default(auth()->id())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Event Title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start')
                    ->label(__('Start Date & Time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->label(__('End Date & Time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Event Type'))
                    ->formatStateUsing(fn(string $state): string => __($state))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'meeting' => 'info',
                        'holiday' => 'success',
                        'deadline' => 'danger',
                        'appointment' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('all_day')
                    ->label(__('All Day Event'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
