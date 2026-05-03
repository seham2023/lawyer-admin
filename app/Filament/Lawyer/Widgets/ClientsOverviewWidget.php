<?php

namespace App\Filament\Lawyer\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Qestass\User;
use App\Support\LawyerUserAccess;
use Filament\Widgets\TableWidget as BaseWidget;

class ClientsOverviewWidget extends BaseWidget
{
    // public static function canView(): bool
    // {
    //     return auth()->user()->checkPermissionTo('view Dashboard');
    // }

    protected static ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('Recent Users');
    }

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LawyerUserAccess::applyToUserQuery(User::query(), auth()->id(), 'client')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Mobile'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label(__('Gender'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    }),
                // Tables\Columns\TextColumn::make('company')
                //     ->label('Company')
                //     ->limit(25)
                //     ->toggleable(),
                // Tables\Columns\TextColumn::make('category.name')
                //     ->label('Category')
                //     ->badge()
                //     ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Registered'))
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn(User $record): string => route('filament.admin.resources.clients.edit', $record)),
            ]);
    }
}
