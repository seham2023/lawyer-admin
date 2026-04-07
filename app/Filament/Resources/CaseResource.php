<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Level;
use App\Models\Status;
use App\Models\Category;
use App\Models\Currency;
use App\Models\CaseRecord;
use App\Models\Nationality;
use App\Models\Qestass\User;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Support\LawyerUserAccess;
use App\Filament\Resources\CaseResource\Pages;
use App\Filament\Actions\SendTabbyPaymentLinkAction;
use App\Filament\Resources\CaseResource\RelationManagers;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class CaseResource extends Resource
{
    protected static ?string $model = CaseRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationLabel(): string
    {
        return __('cases');
    }
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->parent_id) {
            // Sub-lawyer: only see cases owned by parent AND assigned to them
            return $query->where('user_id', $user->parent_id)
                         ->where('assigned_lawyer_id', $user->id)
                         ->latest();
        }

        // Main lawyer: see all cases they own
        return $query->where('user_id', $user->id)->latest();
    }
    public static function getNavigationGroup(): ?string
    {
        return __('client_management');
    }
    public static function getPluralModelLabel(): string
    {
        return __('cases');
    }

    public static function getModelLabel(): string
    {
        return __('case');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Wizard\Step::make(__('client_information'))
                            ->schema([
                                Select::make('client_id')
                                    ->label(__('client'))
                                    ->options(fn () => \App\Support\LawyerUserAccess::optionsForLawyer(auth()->user()->parent_id ?? auth()->id(), 'client'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('client_type_id')
                                    ->label(__('client_type'))
                                    ->relationship('category', 'name')
                                    ->options(Category::where('type', 'client_type')->pluck('name', 'id'))

                                    ->searchable(),
                            ]),

                       

                        Forms\Components\Wizard\Step::make(__('case_details'))
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label(__('start_date'))
                                    ->required(),

                                Select::make('category_id')
                                    ->label(__('category'))
                                    ->options(Category::where('type', 'case')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                // Select::make('level_id')
                                //     ->label(__('level'))
                                //     ->options(Level::all()->pluck('name', 'id'))
                                //     ->searchable()
                                //     ->required(),

                                Select::make('status_id')
                                    ->label(__('status'))
                                    ->options(Status::where('type', 'case')->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                // Select::make('court_id')
                                //     ->label(__('court'))
                                //     ->options(\App\Models\Court::all()->pluck('name', 'id'))->dehydrated(false)
                                //     ->searchable()
                                //     ->preload()
                                //     ->createOptionForm([
                                //         Forms\Components\TextInput::make('name')
                                //             ->label(__('name'))
                                //             ->required()
                                //             ->maxLength(255),
                                //         Forms\Components\TextInput::make('location')
                                //             ->label(__('location'))
                                //             ->maxLength(255),
                                //         Forms\Components\TextInput::make('court_number')
                                //             ->label(__('court_number'))
                                //             ->maxLength(255),
                                //         Forms\Components\Select::make('category_id')
                                //             ->label(__('category'))
                                //             ->relationship('category', 'name')
                                //             ->options(\App\Models\Category::where('type', 'court')->pluck('name', 'id'))
                                //             ->searchable()
                                //             ->preload(),
                                //         Hidden::make('user_id')
                                //             ->default(auth()->user()->id),
                                //     ])
                                //     ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                //         return $action
                                //             ->modalHeading(__('Create Court'))
                                //             ->modalSubmitActionLabel(__('Create'))
                                //             ->modalWidth('lg');
                                //     }),





                                TextInput::make('subject')
                                    ->label(__('subject'))
                                    ->required()
                                    ->maxLength(255),

                                Textarea::make('subject_description')
                                    ->label(__('subject_description'))
                                    ->columnSpanFull(),

                                // RichEditor::make('notes')
                                //     ->label(__('notes'))
                                //     ->columnSpanFull(),

                                RichEditor::make('contract')
                                    ->label(__('contract'))
                                    ->columnSpanFull(),

                                Select::make('assigned_lawyer_id')
                                    ->label(__('assigned_lawyer'))
                                    ->options(fn () => \App\Support\LawyerUserAccess::optionsForLawyer(auth()->user()->parent_id ?? auth()->id(), 'sub_lawyer'))
                                    ->searchable()
                                    ->preload()
                                    ->hint(__('If left empty, only the main lawyer can manage this case.')),
                            ]),

                        Forms\Components\Wizard\Step::make(__('financial_details'))
                            ->schema([
                                Select::make('currency_id')
                                    ->label(__('currency'))
                                    ->options(Currency::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                TextInput::make('amount')
                                    ->label(__('amount'))
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $tax = $get('tax') ?? 0;
                                        $amount = $state ?? 0;
                                        $total = $amount + ($amount * $tax / 100);
                                        $set('total_after_tax', $total);
                                    }),

                                TextInput::make('tax')
                                    ->label(__('tax'))
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $amount = $get('amount') ?? 0;
                                        $tax = $state ?? 0;
                                        $total = $amount + ($amount * $tax / 100);
                                        $set('total_after_tax', $total);
                                    }),

                                TextInput::make('total_after_tax')
                                    ->label(__('total_after_tax'))
                                    ->numeric()
                                    ->disabled(),

                                Select::make('pay_method_id')
                                    ->label(__('payment_method'))
                                    ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->preload(),

                                Select::make('payment_status_id')
                                    ->label(__('payment_status'))
                                    ->options(Status::where('type', 'payment')->pluck('name', 'id'))
                                    ->searchable()
                                    ->default(1), // Default to 'Pending'
                            ]),


                             Forms\Components\Wizard\Step::make(__('opponent_information'))
                            ->schema([
                                TextInput::make('opponent_name')
                                    ->label(__('opponent_name'))
                                    ->maxLength(255),

                                TextInput::make('opponent_mobile')
                                    ->label(__('opponent_mobile'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('opponent_email')
                                    ->label(__('opponent_email'))
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('opponent_location')
                                    ->label(__('opponent_location'))
                                    ->maxLength(255),

                                Select::make('opponent_nationality_id')
                                    ->label(__('opponent_nationality'))
                                    ->options(Nationality::all()->pluck('name', 'id'))
                                    ->searchable(),
                            ]),

                        Forms\Components\Wizard\Step::make(__('opponent_lawyer'))
                            ->schema([
                                TextInput::make('opponent_lawyer_name')
                                    ->label(__('lawyer_name'))
                                    ->maxLength(255),

                                TextInput::make('opponent_lawyer_mobile')
                                    ->label(__('lawyer_mobile'))
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('opponent_lawyer_email')
                                    ->label(__('lawyer_email'))
                                    ->email()
                                    ->maxLength(255),
                            ]),
                    ]),


                Hidden::make('user_id')
                    ->default(fn () => auth()->user()->parent_id ?? auth()->id())
            ]);
}

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('category'))
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label(__('client'))
                    ->getStateUsing(fn($record) => $record->client?->getFilamentName() ?? '-')
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
                    ->sortable(query: function ($query, $direction) {
                        return $query->join('users', 'case_records.client_id', '=', 'users.id')
                            ->orderBy('users.first_name', $direction);
                    }),

                TextColumn::make('subject')
                    ->label(__('subject'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('currentCourt.court.name')
                    ->label(__('court_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label(__('start_date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status_id')
                    ->label(__('status'))
                    ->options(Status::where('type', 'case')->pluck('name', 'id'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('payment_status')
                    ->label(__('payment_status'))
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

                TextColumn::make('latestSession.court.name')
                    ->label(__('Latest Session Court'))
                    ->placeholder('-')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('assignedLawyer.name')
                    ->label(__('assigned_lawyer'))
                    ->getStateUsing(fn($record) => $record->assignedLawyer ? $record->assignedLawyer->first_name . ' ' . $record->assignedLawyer->last_name : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment.amount')
                    ->label(__('Total Amount'))
                    ->money(fn () => \App\Support\Money::getCurrencyCode())
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('payment.total_paid')
                    ->label(__('Paid'))
                    ->money(fn () => \App\Support\Money::getCurrencyCode())
                    ->badge()
                    ->color('success'),

                TextColumn::make('payment.remaining_payment')
                    ->label(__('Remaining'))
                    ->money(fn () => \App\Support\Money::getCurrencyCode())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('created_at')
                    ->label(__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('add_session')
                        ->label(__('Add Session'))
                        ->icon('heroicon-o-calendar-days')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('title')
                                ->label(__('title'))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('details')
                                ->label(__('details'))
                                ->columnSpanFull(),
                            Forms\Components\DateTimePicker::make('datetime')
                                ->label(__('datetime'))
                                ->required()
                                ->default(now()),
                            Forms\Components\Select::make('priority')
                                ->label(__('priority'))
                                ->options([
                                    'low' => __('priority_low'),
                                    'medium' => __('priority_medium'),
                                    'high' => __('priority_high'),
                                ])
                                ->default('medium')
                                ->required(),
                            Forms\Components\Select::make('court_id')
                                ->label(__('court'))
                                ->relationship('court', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\TextInput::make('judge_name')
                                ->label(__('judge_name'))
                                ->maxLength(255),
                        ])
                        ->action(function ($record, array $data) {
                            $data['user_id'] = auth()->id();
                            $record->sessions()->create($data);
                            \Filament\Notifications\Notification::make()
                                ->title(__('Session added successfully'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('add_document')
                        ->label(__('Add Document'))
                        ->icon('heroicon-o-document-plus')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('name')
                                ->label(__('name'))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->label(__('description'))
                                ->required(),
                            Forms\Components\FileUpload::make('file_path')
                                ->label(__('file_path'))
                                ->required()
                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                ->directory('case-documents'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->documents()->create($data);
                            \Filament\Notifications\Notification::make()
                                ->title(__('Document added successfully'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('add_payment_detail')
                        ->label(__('Add Payment'))
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn($record) => $record->payment !== null)
                        ->form([
                            Forms\Components\TextInput::make('name')
                                ->label(__('Payment Name'))
                                ->placeholder(__('e.g., First Installment'))
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Select::make('payment_type')
                                ->label(__('Payment Type'))
                                ->options([
                                    'installment' => __('Installment'),
                                    'deposit' => __('Deposit'),
                                    'final' => __('Final Payment'),
                                    'partial' => __('Partial Payment'),
                                ])
                                ->required()
                                ->default('installment'),

                            Forms\Components\TextInput::make('amount')
                                ->label(__('Amount'))
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->rules([
                                    function ($record) {
                                        return function (string $attribute, $value, \Closure $fail) use ($record) {
                                            if (!$record->payment) return;
                                            
                                            $remaining = $record->payment->remaining_payment;
                                            if ($value > $remaining) {
                                                $fail(__('Amount cannot exceed the remaining balance of :amount', ['amount' => $remaining]));
                                            }
                                        };
                                    }
                                ])
                                ->helperText(fn($record) => __('Remaining balance') . ': ' . ($record->payment->remaining_payment ?? 0)),

                            Forms\Components\DateTimePicker::make('paid_at')
                                ->label(__('Payment Date'))
                                ->required()
                                ->default(now()),

                            Forms\Components\Select::make('pay_method_id')
                                ->label(__('Payment Method'))
                                ->options(\App\Models\PayMethod::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            Forms\Components\Textarea::make('details')
                                ->label(__('Payment Details'))
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data) {
                            $data['payment_id'] = $record->payment->id;
                            $record->payment->paymentDetails()->create($data);
                            \Filament\Notifications\Notification::make()
                                ->title(__('Payment added successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
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
            RelationManagers\SessionsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            // RelationManagers\CourtHistoryRelationManager::class,
            // RelationManagers\AuditsRelationManager::class,
            // RelationManagers\PaymentsRelationManager::class,
            RelationManagers\PaymentDetailRelationManager::class,
            RelationManagers\PaymentSessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCases::route('/'),
            'create' => Pages\CreateCase::route('/create'),
            'view' => Pages\ViewCase::route('/{record}'),
            'edit' => Pages\EditCase::route('/{record}/edit'),
        ];
    }


    public function getTabs(): array
    {
        return [
            'details' => __('Details'),
            'sessions' => __('sessions'),
            'documents' => __('Documents'),
            'payments' => __('Payments'),
        ];
    }
}
