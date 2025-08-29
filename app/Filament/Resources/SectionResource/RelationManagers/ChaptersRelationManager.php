<?php

namespace App\Filament\Resources\SectionResource\RelationManagers;

use App\Enums\ChapterTypeEnum;
use App\Filament\Resources\ChapterResource;
use App\Models\Chapter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du chapitre')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('content_type')
                            ->label('Type de contenu')
                            ->options([
                                'text' => 'Texte',
                                'video' => 'Vidéo',
                                'audio' => 'Audio',
                                'pdf' => 'PDF',
                                'interactive' => 'Interactif',
                            ])
                            ->required()
                            ->default('text')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('metadata', []);
                                $set('media_url', null);
                            }),

                        Forms\Components\FileUpload::make('media_url')
                            ->label('Fichier de contenu')
                            ->disk('public')
                            ->directory('chapters')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->acceptedFileTypes(['application/pdf', 'video/*', 'audio/*'])
                            ->helperText('Uploadez un fichier selon le type de contenu.')
                            ->visible(fn(Forms\Get $get) => in_array($get('content_type'), ['pdf', 'video', 'audio'], true))
                            ->required(fn(Forms\Get $get) => in_array($get('content_type'), ['pdf', 'video', 'audio'], true))
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $meta = $get('metadata') ?? [];
                                $meta['source_file'] = $state;
                                $set('metadata', $meta);
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('order_position')
                                    ->label('Position')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('duration_minutes')
                                    ->label('Durée (minutes)')
                                    ->numeric()
                                    ->suffix('min'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description du contenu')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(4)
                            ->placeholder('Description de ce chapitre...'),
                    ]),

                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu principal')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ]),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Métadonnées')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter métadonnée')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_free')
                                    ->label('Gratuit')
                                    ->default(false)
                                    ->helperText('Accessible sans inscription payante'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Actif')
                                    ->default(true)
                                    ->helperText('Chapitre visible pour les étudiants'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('order_position')
                    ->label('Ordre')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('content_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        ChapterTypeEnum::VIDEO => 'info',
                        ChapterTypeEnum::TEXT => 'success',
                        ChapterTypeEnum::PDF => 'warning',
                        ChapterTypeEnum::INTERACTIVE => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durée')
                    ->suffix(' min')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_free')
                    ->label('Gratuit')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->label('Type de contenu')
                    ->options(ChapterTypeEnum::class)
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('Gratuit')
                    ->boolean()
                    ->trueLabel('Gratuits seulement')
                    ->falseLabel('Payants seulement')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Actifs seulement')
                    ->falseLabel('Inactifs seulement')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter un chapitre')
                    ->icon('heroicon-o-plus-circle')
                    ->modalWidth('xl')
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn(Chapter $record): string => ChapterResource::getUrl('view', ['record' => $record]))
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->slideOver(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('preview')
                        ->label('Aperçu')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->slideOver()
                        ->modalContent(fn(Chapter $record) => view('filament.resources.chapter.preview', ['chapter' => $record]))
                        ->modalWidth('xl'),

                    Tables\Actions\Action::make('duplicate')
                        ->label('Dupliquer')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->action(function (Chapter $record) {
                            $newChapter = $record->replicate();
                            $newChapter->title = $record->title . ' (Copie)';
                            $newChapter->order_position = Chapter::query()
                                    ->where('section_id', '=', $record->section_id)
                                    ->max('order_position') + 1;
                            $newChapter->save();

                            $this->mountedTableActionRecord = $newChapter->getKey();
                        })
                        ->successNotificationTitle('Chapitre dupliqué avec succès'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => false])),
                    Tables\Actions\BulkAction::make('make_free')
                        ->label('Rendre gratuit')
                        ->icon('heroicon-o-gift')
                        ->color('success')
                        ->action(fn(Collection $records) => $records->each->update(['is_free' => true])),
                    Tables\Actions\BulkAction::make('make_paid')
                        ->label('Rendre payant')
                        ->icon('heroicon-o-lock-closed')
                        ->color('warning')
                        ->action(fn(Collection $records) => $records->each->update(['is_free' => false])),
                ]),
            ])
            ->defaultSort('order_position')
            ->reorderable('order_position');
    }
}
