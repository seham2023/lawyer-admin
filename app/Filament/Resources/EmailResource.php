<?php

namespace App\Filament\Resources;

use App\Models\Email;
use App\Models\Client;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use App\Filament\Resources\EmailResource\Pages;
use Mvenghaus\FilamentPluginTranslatableInline\Forms\Components\TranslatableContainer;
use Illuminate\Support\Facades\Auth;

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
                Forms\Components\Section::make(__('Email Information'))
                    ->schema([
                        Select::make('client_id')
                            ->label(__('Client'))
                            ->relationship('client', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $clientId = $get('client_id');
                                $templateId = $get('email_template_id');
                                if ($clientId && $templateId) {
                                    self::updateEmailPreview($set, $clientId, $templateId);
                                }
                            }),

                        Select::make('email_template_id')
                            ->label(__('Email Template'))
                            ->relationship('emailTemplate', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $clientId = $get('client_id');
                                $templateId = $get('email_template_id');
                                if ($clientId && $templateId) {
                                    self::updateEmailPreview($set, $clientId, $templateId);
                                }
                            }),

                        TextInput::make('subject')
                            ->label(__('Subject'))
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('content')
                            ->label(__('Content'))
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label(__('File'))
                            ->directory('emails'),

                        Select::make('priority')
                            ->label(__('Priority'))
                            ->options([
                                'low' => __('Low'),
                                'medium' => __('Medium'),
                                'high' => __('High'),
                            ])
                            ->default('low')
                            ->required(),
                    ])->columns(2),
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

    protected static function updateEmailPreview(callable $set, $clientId, $templateId)
    {
        $client = Client::find($clientId);
        $template = EmailTemplate::find($templateId);

        if ($client && $template) {
            $templateContent = $template->content;
            $admin = Auth::user();

            $body = str_replace(
                ['{{name}}', '{{date}}', '{{country}}', '{{city}}', '{{admin_name}}', '{{phone}}'],
                [
                    $client->name,
                    now()->format('Y-m-d'),
                    $client->country?->name ?? '',
                    $client->city?->name ?? '',
                    $admin->name ?? 'Admin',
                    $admin->phone ?? '123456789'
                ],
                $templateContent
            );

            $set('subject', $template->subject);
            $set('content', $body);
        }
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
