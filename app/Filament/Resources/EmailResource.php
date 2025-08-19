<?php

namespace App\Filament\Resources;

use App\Models\Email;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\EmailResource\Pages;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;

class EmailResource extends Resource
{
    protected static ?string $model = Email::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Communication';

    public static function getNavigationLabel(): string
    {
        return __('Emails');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Email');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TranslatableContainer::make(
                    Forms\Components\Section::make(__('Email Information'))
                        ->schema([
                            Select::make('email_template_id')
                                ->label(__('Email Template'))
                                ->relationship('emailTemplate', 'name')
                                ->required(),

                            Select::make('client_id')
                                ->label(__('Client'))
                                ->relationship('client', 'name')
                                ->required(),

                            TextInput::make('subject')
                                ->label(__('Subject'))
                                ->required()
                                ->maxLength(255),

                            Textarea::make('content')
                                ->label(__('Content'))
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->label(__('Notes'))
                                ->columnSpanFull(),

                            Toggle::make('is_starred')
                                ->label(__('Starred'))
                                ->default(false),

                            FileUpload::make('file_path')
                                ->label(__('Attachment'))
                                ->directory('emails')
                                ->maxSize(10240) // 10MB
                                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                        ])->columns(2),
                )->columnSpanFull(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label(__('Client'))
                    ->sortable(),

                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('emailTemplate.name')
                    ->label(__('Template'))
                    ->sortable(),

                TextColumn::make('is_starred')
                    ->label(__('Starred'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Yes') : __('No')),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEmails::route('/'),
            'create' => Pages\CreateEmail::route('/create'),
            'edit' => Pages\EditEmail::route('/{record}/edit'),
        ];
    }
}
