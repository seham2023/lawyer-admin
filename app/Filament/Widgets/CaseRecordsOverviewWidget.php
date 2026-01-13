<?php

namespace App\Filament\Widgets;

use App\Models\CaseRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CaseRecordsOverviewWidget extends BaseWidget
{
    protected static ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('Recent Cases');
    }

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CaseRecord::query()
                    ->where('user_id', auth()->user()->id)
                    ->with(['client', 'category', 'status'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('client.first_name')
                    ->label(__('Client'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('court.name')
                    ->label(__('Court'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status.name')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        __('Active') => 'success',
                        __('Pending') => 'warning',
                        __('Closed') => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                // Tables\Actions\Action::make('view')
                //     ->icon('heroicon-m-eye')
                //     ->url(fn (CaseRecord $record): string => route('filament.admin.resources.case-records.edit', $record)),
            ]);
    }
}
