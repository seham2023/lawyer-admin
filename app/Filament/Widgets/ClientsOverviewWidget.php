<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ClientsOverviewWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Clients';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->with(['category', 'address'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('company')
                    ->label('Company')
                    ->limit(25)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Client $record): string => route('filament.admin.resources.clients.edit', $record)),
            ]);
    }
}
