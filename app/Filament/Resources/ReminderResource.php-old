<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReminderResource\Pages;
use App\Filament\Resources\ReminderResource\RelationManagers;
use App\Models\Reminder;
use App\Services\ReminderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'System Settings';

    protected static ?int $navigationSort = 90;

    public static function getNavigationLabel(): string
    {
        return __('reminders.title');
    }

    public static function getModelLabel(): string
    {
        return __('reminders.title');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('reminders.title'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('reminders.user'))
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('reminder_type')
                            ->label(__('reminders.type'))
                            ->options([
                                'session' => __('settings.session_reminders'),
                                'event' => __('settings.event_reminders'),
                                'order' => __('settings.order_reminders'),
                                'payment' => __('settings.payment_reminders'),
                                'deadline' => __('settings.deadline_reminders'),
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label(__('reminders.scheduled_at'))
                            ->required()
                            ->seconds(false),

                        Forms\Components\Select::make('status')
                            ->label(__('reminders.status'))
                            ->options([
                                'pending' => __('reminders.pending'),
                                'sent' => __('reminders.sent'),
                                'failed' => __('reminders.failed'),
                                'cancelled' => __('reminders.cancelled'),
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\CheckboxList::make('channels')
                            ->label(__('reminders.channels'))
                            ->options([
                                'email' => __('settings.email_channel'),
                                'sms' => __('settings.sms_channel'),
                                'push' => __('settings.push_channel'),
                                'in_app' => __('settings.in_app_channel'),
                            ])
                            ->required()
                            ->columns(2),

                        Forms\Components\Textarea::make('metadata')
                            ->label(__('reminders.metadata'))
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('reminders.user'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reminder_type')
                    ->label(__('reminders.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'session' => 'info',
                        'event' => 'success',
                        'payment' => 'warning',
                        'deadline' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => __(
                        'settings.' . $state . '_reminders'
                    ))
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label(__('reminders.scheduled_at'))
                    ->dateTime()
                    ->sortable()
                    ->description(
                        fn(Reminder $record): string =>
                        $record->scheduled_at->diffForHumans()
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('reminders.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => __('reminders.' . $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('channels')
                    ->label(__('reminders.channels'))
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('Sent At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('reminders.status'))
                    ->options([
                        'pending' => __('reminders.pending'),
                        'sent' => __('reminders.sent'),
                        'failed' => __('reminders.failed'),
                        'cancelled' => __('reminders.cancelled'),
                    ]),

                Tables\Filters\SelectFilter::make('reminder_type')
                    ->label(__('reminders.type'))
                    ->options([
                        'session' => __('settings.session_reminders'),
                        'event' => __('settings.event_reminders'),
                        'order' => __('settings.order_reminders'),
                        'payment' => __('settings.payment_reminders'),
                        'deadline' => __('settings.deadline_reminders'),
                    ]),

                Tables\Filters\Filter::make('scheduled')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from')
                            ->label(__('From')),
                        Forms\Components\DatePicker::make('scheduled_until')
                            ->label(__('To')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('send_now')
                    ->label(__('reminders.send_now'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn(Reminder $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Reminder $record) {
                        $service = app(ReminderService::class);
                        if ($service->sendReminder($record)) {
                            Notification::make()
                                ->success()
                                ->title(__('Success'))
                                ->body(__('Reminder sent successfully'))
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title(__('Error'))
                                ->body(__('Failed to send reminder'))
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label(__('reminders.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Reminder $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Reminder $record) {
                        $record->markAsCancelled();
                        Notification::make()
                            ->success()
                            ->title(__('Success'))
                            ->body(__('Reminder cancelled'))
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('cancel_selected')
                        ->label(__('Cancel Selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->markAsCancelled();
                            Notification::make()
                                ->success()
                                ->title(__('Success'))
                                ->body(__('Selected reminders cancelled'))
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReminders::route('/'),
            'create' => Pages\CreateReminder::route('/create'),
            'view' => Pages\ViewReminder::route('/{record}'),
            'edit' => Pages\EditReminder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
