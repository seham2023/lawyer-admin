<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_visit')
                ->label(__('Add Visit'))
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DateTimePicker::make('visit_date')
                        ->label(__('Visit Date'))
                        ->required()
                        ->default(now()),
                    \Filament\Forms\Components\TextInput::make('purpose')
                        ->label(__('Purpose'))
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    \App\Models\Visit::create([
                        'user_id' => auth()->id(),
                        'client_id' => $this->record->id,
                        'visit_date' => $data['visit_date'],
                        'purpose' => $data['purpose'],
                        'notes' => $data['notes'] ?? null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title(__('Visit created successfully'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('add_case')
                ->label(__('Add Case'))
                ->icon('heroicon-o-briefcase')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('category_id')
                        ->label(__('Category'))
                        ->options(\App\Models\Category::where('type', 'case')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),
                    \Filament\Forms\Components\Select::make('status_id')
                        ->label(__('Status'))
                        ->options(\App\Models\Status::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),
                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label(__('Start Date'))
                        ->required()
                        ->default(now()),
                    \Filament\Forms\Components\TextInput::make('subject')
                        ->label(__('Subject'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Textarea::make('subject_description')
                        ->label(__('Description'))
                        ->rows(3)
                        ->columnSpanFull(),
                    \Filament\Forms\Components\TextInput::make('court_name')
                        ->label(__('Court Name'))
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('court_number')
                        ->label(__('Court Number'))
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    \App\Models\CaseRecord::create([
                        'user_id' => auth()->id(),
                        'client_id' => $this->record->id,
                        'category_id' => $data['category_id'],
                        'status_id' => $data['status_id'],
                        'start_date' => $data['start_date'],
                        'subject' => $data['subject'],
                        'subject_description' => $data['subject_description'] ?? null,
                        'court_name' => $data['court_name'] ?? null,
                        'court_number' => $data['court_number'] ?? null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title(__('Case created successfully'))
                        ->success()
                        ->send();
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
