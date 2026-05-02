<?php

namespace App\Filament\Lawyer\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Visit;
use App\Models\Status;
use App\Models\Currency;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Support\LawyerUserAccess;
use App\Filament\Lawyer\Resources\VisitResource\Pages;
use App\Filament\Lawyer\Resources\VisitResource\RelationManagers;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

 public static function getNavigationGroup(): ?string
    {
        return __('client_management');
    }
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Visits');
    }

    public static function getModelLabel(): string
    {
        return __('Visit');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Visits');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Visit Information'))
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label(__('Client'))
                            ->options(fn () => LawyerUserAccess::optionsForLawyer(auth()->id(), 'client'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->native(false),

                        Forms\Components\DateTimePicker::make('visit_date')
                            ->label(__('Visit Date'))
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\Select::make('status_id')
                            ->label(__('Status'))
                            ->options(Status::where('type', 'visit')->pluck('name', 'id'))
                            ->default(fn() => Status::where('type', 'visit')->first()?->id)
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Forms\Components\Select::make('services')
                            ->multiple()
                            ->relationship(
                                'services',
                                'name',
                                fn (Builder $query) => $query->where('user_id', auth()->id())
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} (" . number_format($record->price, 2) . " " . \App\Support\Money::getCurrencyCode() . ")")
                            ->searchable()
                            ->preload()
                            ->label(__('Services'))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('purpose')
                            ->label(__('Purpose'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.first_name')
                    ->label(__('Client'))
                    ->formatStateUsing(fn($record) => $record->client->first_name . ' ' . $record->client->last_name)
                    ->searchable(query: function ($query, $search) {
                        $appDb = config('database.connections.qestass_app.database');
                        return $query->whereHas('client', function ($q) use ($search, $appDb) {
                            $q->from($appDb . '.users')
                                ->where(function ($qq) use ($search) {
                                    $qq->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('phone', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('visit_date')
                    ->label(__('Visit Date'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->label(__('Status'))
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('purpose')
                    ->label(__('Purpose'))
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label(__('Total Amount'))
                    ->money(fn() => \App\Support\Money::getCurrencyCode())
                    ->default('-')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('payment.total_paid')
                    ->label(__('Paid'))
                    ->money(fn() => \App\Support\Money::getCurrencyCode())
                    ->default('-')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment.remaining_payment')
                    ->label(__('Remaining'))
                    ->money(fn() => \App\Support\Money::getCurrencyCode())
                    ->default('-')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('payment_status')
                    ->label(__('Payment Status'))
                    ->getStateUsing(function ($record) {
                        if (!$record->payment) {
                            return 'unpaid';
                        }
                        $remaining = $record->payment->remaining_payment ?? 0;
                        if ($remaining <= 0) {
                            return 'paid';
                        }
                        if ($record->payment->total_paid > 0) {
                            return 'partial';
                        }
                        return 'unpaid';
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'paid' => 'heroicon-o-check-circle',
                        'partial' => 'heroicon-o-clock',
                        'unpaid' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('From Date')),
                        Forms\Components\DatePicker::make('until')
                            ->label(__('Until Date')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('visit_date', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('visit_date', '<=', $date)
                            );
                    }),
                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->options(\App\Models\Status::where('type', 'visit')->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(__('Payment Status'))
                    ->options([
                        'paid' => __('Paid'),
                        'partial' => __('Partial'),
                        'unpaid' => __('Unpaid'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'] === 'paid', function ($query) {
                            return $query->whereHas('payment', function ($q) {
                                $q->whereRaw('amount <= (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)');
                            });
                        })->when($data['value'] === 'partial', function ($query) {
                            return $query->whereHas('payment', function ($q) {
                                $q->whereRaw('amount > (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)')
                                  ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id) > 0');
                            });
                        })->when($data['value'] === 'unpaid', function ($query) {
                            return $query->whereDoesntHave('payment')
                                ->orWhereHas('payment', function ($q) {
                                    $q->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id) = 0');
                                });
                        });
                    }),
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('visit_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'view' => Pages\ViewVisit::route('/{record}'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->user()->id)
            ->with(['client', 'payment.paymentDetails']);
    }
}
