<?php

namespace App\Filament\Resources;

use App\Models\Qestass\Time;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LawyerTimeResource\Pages;

class LawyerTimeResource extends Resource
{
    protected static ?string $model = Time::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationLabel(): string
    {
        return __('Lawyer Times');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Lawyer Management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Lawyer Times');
    }

    public static function getModelLabel(): string
    {
        return __('Lawyer Time');
    }

    // Scope query to authenticated user
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Type Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('Online'))
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Section::make(__('Online Availability'))
                                    ->description(__('Manage your online consultation times'))
                                    ->schema([
                                        Forms\Components\Repeater::make('online_days')
                                            ->label(__('Days'))
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('day_label')
                                                            ->label(__('Day'))
                                                            ->content(fn(Forms\Get $get) => __(ucfirst($get('day') ?? '')))
                                                            ->columnSpan(2),

                                                        Forms\Components\Hidden::make('day'),

                                                        Forms\Components\Toggle::make('enabled')
                                                            ->label(__('Active'))
                                                            ->default(true)
                                                            ->inline(false)
                                                            ->live()
                                                            ->columnSpan(1),
                                                    ]),

                                                Forms\Components\Repeater::make('shifts')
                                                    ->label(__('Shifts'))
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\TimePicker::make('start_time')
                                                                    ->label(__('Start Time'))
                                                                    ->required()
                                                                    ->seconds(false)
                                                                    ->format('H:i'),

                                                                Forms\Components\TimePicker::make('end_time')
                                                                    ->label(__('End Time'))
                                                                    ->required()
                                                                    ->seconds(false)
                                                                    ->format('H:i')
                                                                    ->after('start_time'),
                                                            ]),

                                                        Forms\Components\Placeholder::make('interval_info')
                                                            ->label(__('Auto-Generated Intervals'))
                                                            ->content(function (Forms\Get $get) {
                                                                $start = $get('start_time');
                                                                $end = $get('end_time');

                                                                if (!$start || !$end) {
                                                                    return __('Select start and end times to see interval count');
                                                                }

                                                                try {
                                                                    $startTime = \Carbon\Carbon::parse($start);
                                                                    $endTime = \Carbon\Carbon::parse($end);
                                                                    $diffMinutes = $startTime->diffInMinutes($endTime);
                                                                    $intervalCount = floor($diffMinutes / 30);

                                                                    return __(':count intervals of 30 minutes each will be generated', ['count' => $intervalCount]);
                                                                } catch (\Exception $e) {
                                                                    return '';
                                                                }
                                                            })
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(fn(Forms\Get $get) => $get('enabled') ? 1 : 0)
                                                    ->addActionLabel(__('Add Shift'))
                                                    ->reorderable(false)
                                                    ->collapsible()
                                                    ->hidden(fn(Forms\Get $get) => !$get('enabled'))
                                                    ->live()
                                                    ->itemLabel(
                                                        fn(array $state): ?string =>
                                                        isset($state['start_time']) && isset($state['end_time'])
                                                            ? "{$state['start_time']} - {$state['end_time']}"
                                                            : null
                                                    ),
                                            ])
                                            ->defaultItems(7)
                                            ->default([
                                                ['day' => 'saturday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'sunday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'monday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'tuesday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'wednesday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'thursday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'friday', 'enabled' => false, 'shifts' => []],
                                            ])
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                isset($state['day'])
                                                    ? __(ucfirst($state['day'])) . ($state['enabled'] ?? false ? ' ✓' : ' ✗')
                                                    : null
                                            ),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Offline'))
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make(__('Offline Availability'))
                                    ->description(__('Manage your in-person consultation times'))
                                    ->schema([
                                        Forms\Components\Repeater::make('offline_days')
                                            ->label(__('Days'))
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('day_label')
                                                            ->label(__('Day'))
                                                            ->content(fn(Forms\Get $get) => __(ucfirst($get('day') ?? '')))
                                                            ->columnSpan(2),

                                                        Forms\Components\Hidden::make('day'),

                                                        Forms\Components\Toggle::make('enabled')
                                                            ->label(__('Active'))
                                                            ->default(true)
                                                            ->inline(false)
                                                            ->live()
                                                            ->columnSpan(1),
                                                    ]),

                                                Forms\Components\Repeater::make('shifts')
                                                    ->label(__('Shifts'))
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\TimePicker::make('start_time')
                                                                    ->label(__('Start Time'))
                                                                    ->required()
                                                                    ->seconds(false)
                                                                    ->format('H:i'),

                                                                Forms\Components\TimePicker::make('end_time')
                                                                    ->label(__('End Time'))
                                                                    ->required()
                                                                    ->seconds(false)
                                                                    ->format('H:i')
                                                                    ->after('start_time'),
                                                            ]),

                                                        Forms\Components\Placeholder::make('interval_info')
                                                            ->label(__('Auto-Generated Intervals'))
                                                            ->content(function (Forms\Get $get) {
                                                                $start = $get('start_time');
                                                                $end = $get('end_time');

                                                                if (!$start || !$end) {
                                                                    return __('Select start and end times to see interval count');
                                                                }

                                                                try {
                                                                    $startTime = \Carbon\Carbon::parse($start);
                                                                    $endTime = \Carbon\Carbon::parse($end);
                                                                    $diffMinutes = $startTime->diffInMinutes($endTime);
                                                                    $intervalCount = floor($diffMinutes / 30);

                                                                    return __(':count intervals of 30 minutes each will be generated', ['count' => $intervalCount]);
                                                                } catch (\Exception $e) {
                                                                    return '';
                                                                }
                                                            })
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(fn(Forms\Get $get) => $get('enabled') ? 1 : 0)
                                                    ->addActionLabel(__('Add Shift'))
                                                    ->reorderable(false)
                                                    ->collapsible()
                                                    ->hidden(fn(Forms\Get $get) => !$get('enabled'))
                                                    ->live()
                                                    ->itemLabel(
                                                        fn(array $state): ?string =>
                                                        isset($state['start_time']) && isset($state['end_time'])
                                                            ? "{$state['start_time']} - {$state['end_time']}"
                                                            : null
                                                    ),
                                            ])
                                            ->defaultItems(7)
                                            ->default([
                                                ['day' => 'saturday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'sunday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'monday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'tuesday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'wednesday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'thursday', 'enabled' => false, 'shifts' => []],
                                                ['day' => 'friday', 'enabled' => false, 'shifts' => []],
                                            ])
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                isset($state['day'])
                                                    ? __(ucfirst($state['day'])) . ($state['enabled'] ?? false ? ' ✓' : ' ✗')
                                                    : null
                                            ),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day')
                    ->label(__('Day'))
                    ->formatStateUsing(fn(string $state): string => __(ucfirst($state)))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn(string $state): string => __(ucfirst($state)))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('intervals_count')
                    ->label(__('Intervals'))
                    ->counts('intervals')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('intervals.from')
                    ->label(__('Time Slots'))
                    ->listWithLineBreaks()
                    ->formatStateUsing(function ($record) {
                        return $record->intervals->map(function ($interval) {
                            return $interval->from . ' - ' . $interval->to;
                        })->join(', ');
                    })
                    ->wrap(),

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
            ->defaultSort('day', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'online' => __('Online'),
                        'offline' => __('Offline'),
                    ]),

                Tables\Filters\SelectFilter::make('day')
                    ->label(__('Day'))
                    ->options([
                        'saturday' => __('Saturday'),
                        'sunday' => __('Sunday'),
                        'monday' => __('Monday'),
                        'tuesday' => __('Tuesday'),
                        'wednesday' => __('Wednesday'),
                        'thursday' => __('Thursday'),
                        'friday' => __('Friday'),
                    ]),
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
            'index' => Pages\ListLawyerTimes::route('/'),
            'create' => Pages\CreateLawyerTime::route('/create'),
            'edit' => Pages\EditLawyerTime::route('/{record}/edit'),
        ];
    }
}
