<?php

namespace App\Filament\Resources;

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
use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Client Management';

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
                            ->relationship('client', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->first_name . ' ' . $record->last_name),

                        Forms\Components\DateTimePicker::make('visit_date')
                            ->label(__('Visit Date'))
                            ->required()
                            ->default(now())
                            ->native(false),

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

                Forms\Components\Section::make(__('Payment Information'))
                    ->schema([
                        Forms\Components\Select::make('currency_id')
                            ->label(__('Currency'))
                            ->options(Currency::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->default(1)
                            ->reactive(),

                        Forms\Components\TextInput::make('amount')
                            ->label(__('Amount'))
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $tax = $get('tax') ?? 0;
                                $amount = $state ?? 0;
                                $total = $amount + ($amount * $tax / 100);
                                $set('total_after_tax', $total);
                            }),

                        Forms\Components\TextInput::make('tax')
                            ->label(__('Tax') . ' (%)')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $amount = $get('amount') ?? 0;
                                $tax = $state ?? 0;
                                $total = $amount + ($amount * $tax / 100);
                                $set('total_after_tax', $total);
                            }),

                        Forms\Components\TextInput::make('total_after_tax')
                            ->label(__('Total After Tax'))
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('pay_method_id')
                            ->label(__('Payment Method'))
                            ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                            ->searchable()
                            ->native(false),

                        Forms\Components\Select::make('payment_status_id')
                            ->label(__('Payment Status'))
                            ->options(Status::where('type', 'payment')->pluck('name', 'id'))
                            ->searchable()
                            ->default(1)
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.first_name')
                    ->label(__('Client'))
                    ->formatStateUsing(fn($record) => $record->client->first_name . ' ' . $record->client->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('visit_date')
                    ->label(__('Visit Date'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('purpose')
                    ->label(__('Purpose'))
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label(__('Total Amount'))
                    ->money(fn() => Currency::first()->code ?? 'USD')
                    ->default('-')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('payment.total_paid')
                    ->label(__('Paid'))
                    ->money(fn() => Currency::first()->code ?? 'USD')
                    ->default('-')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment.remaining_payment')
                    ->label(__('Remaining'))
                    ->money(fn() => Currency::first()->code ?? 'USD')
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

                Tables\Filters\Filter::make('has_payment')
                    ->label(__('Has Payment'))
                    ->query(fn($query) => $query->whereHas('payment')),

                Tables\Filters\Filter::make('unpaid')
                    ->label(__('Unpaid'))
                    ->query(fn($query) => $query->whereDoesntHave('payment')
                        ->orWhereHas('payment', function ($q) {
                            $q->whereRaw('amount > (SELECT COALESCE(SUM(amount), 0) FROM payment_details WHERE payment_id = payments.id)');
                        })),
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
            ->where('user_id', auth()->id())
            ->with(['client', 'payment.paymentDetails']);
    }
}
