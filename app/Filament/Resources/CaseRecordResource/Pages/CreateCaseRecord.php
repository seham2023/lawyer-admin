<?php

namespace App\Filament\Resources\CaseRecordResource\Pages;

use App\Filament\Resources\CaseRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms;

class CreateCaseRecord extends CreateRecord
{
    use HasWizard;

    protected static string $resource = CaseRecordResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Case Information')
                ->description('Basic case and client details')
                ->icon('heroicon-m-information-circle')
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->relationship('client', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select a client for this case')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required(),
                            Forms\Components\TextInput::make('mobile')
                                ->required(),
                        ]),
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('client_type_id')
                        ->label('Client Type')
                        ->relationship('client_type', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('status_id')
                        ->label('Status')
                        ->relationship('status', 'name')
                        ->searchable()
                        ->preload()
                        ->default(function () {
                            return \App\Models\Status::where('name', 'Active')->first()?->id;
                        }),
                    Forms\Components\Select::make('level_id')
                        ->label('Level')
                        ->relationship('level', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required()
                        ->default(now())
                        ->displayFormat('d/m/Y'),
                ])
                ->columns(2),

            Step::make('Opponent Details')
                ->description('Information about opposing parties')
                ->icon('heroicon-m-users')
                ->schema([
                    Forms\Components\Select::make('opponent_id')
                        ->label('Opponent')
                        ->relationship('opponent', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                            Forms\Components\TextInput::make('phone')
                                ->tel(),
                            Forms\Components\TextInput::make('email')
                                ->email(),
                        ]),
                    Forms\Components\Select::make('opponent_lawyer_id')
                        ->label('Opponent Lawyer')
                        ->relationship('opponent_lawyer', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                            Forms\Components\TextInput::make('phone')
                                ->tel(),
                            Forms\Components\TextInput::make('email')
                                ->email(),
                        ]),
                ])
                ->columns(2),

            Step::make('Court Information')
                ->description('Court and legal proceedings details')
                ->icon('heroicon-m-scale')
                ->schema([
                    Forms\Components\TextInput::make('court_name')
                        ->label('Court Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Enter court name'),
                    Forms\Components\TextInput::make('court_number')
                        ->label('Court Number')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Enter court number/reference'),
                    Forms\Components\TextInput::make('lawyer_name')
                        ->label('Lawyer Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Representing lawyer name'),
                    Forms\Components\TextInput::make('judge_name')
                        ->label('Judge Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Presiding judge name'),
                    Forms\Components\TextInput::make('location')
                        ->label('Location')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Court location/address'),
                    Forms\Components\Select::make('payment_id')
                        ->label('Payment')
                        ->relationship('payment', 'id')
                        ->searchable()
                        ->preload()
                        ->placeholder('Associate payment record'),
                ])
                ->columns(2),

            Step::make('Case Details')
                ->description('Subject, description and additional notes')
                ->icon('heroicon-m-document-text')
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder('Brief case subject/title'),
                    Forms\Components\Textarea::make('subject_description')
                        ->label('Subject Description')
                        ->columnSpanFull()
                        ->rows(4)
                        ->placeholder('Detailed description of the case subject'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpanFull()
                        ->rows(4)
                        ->placeholder('Additional notes and observations'),
                    Forms\Components\Textarea::make('contract')
                        ->label('Contract')
                        ->columnSpanFull()
                        ->rows(4)
                        ->placeholder('Contract details and terms'),
                ]),
        ];
    }
}
