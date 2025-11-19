<?php

declare(strict_types=1);

namespace App\Filament\Resources\SectionResource\RelationManagers;

use App\Enums\ChapterTypeEnum;
use App\Filament\Resources\ChapterResource;
use App\Models\Chapter;
use App\Services\ChapterPdfExtractionService;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

final class ChaptersRelationManager extends RelationManager
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
                                'pdf' => 'PDF',
                            ])
                            ->required()
                            ->default('text')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('media_url', null);
                            }),

                        Forms\Components\FileUpload::make('media_url')
                            ->label('Fichier PDF')
                            ->disk('public')
                            ->directory('chapters')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->acceptedFileTypes([
                                'application/pdf',
                            ])
                            ->maxSize(50 * 1024)
                            ->helperText('Uploadez un PDF. Le contenu sera extrait lors de la sauvegarde.')
                            ->visible(fn(Forms\Get $get) => $get('content_type') === 'pdf')
                            ->required(fn(Forms\Get $get) => $get('content_type') === 'pdf')
                            ->downloadable()
                            ->openable()
                            ->afterStateHydrated(function ($component, $state) {
                                if ($state && $component->getContainer()->getOperation() === 'edit') {
                                    if (!Storage::disk('public')->exists($state[0])) {
                                        Notification::make()
                                            ->title('Fichier manquant')
                                            ->body('Le fichier PDF original est introuvable.')
                                            ->warning()
                                            ->send();
                                    }
                                }
                            }),

                        Forms\Components\FileUpload::make('video_url')
                            ->label('Vidéo')
                            ->disk('public')
                            ->directory('chapters')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->acceptedFileTypes(['video/*'])
                            ->visible(
                                fn(Forms\Get $get) => $get('content_type') === 'video'
                            )
                            ->required(
                                fn(Forms\Get $get) => $get('content_type') === 'video'
                            ),

                        Forms\Components\Hidden::make('cover_image'),

                        Forms\Components\Hidden::make('markdown_file'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('order_position')
                                    ->label('Position')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(fn($livewire) => (Chapter::where('section_id', $livewire->getOwnerRecord()->id)
                                            ->max('order_position') ?? 0) + 1
                                    )
                                    ->helperText('Position automatique (prochaine disponible)'),

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
                            ->columnSpanFull()
                            ->helperText('Le contenu sera automatiquement rempli lors de l\'import PDF')
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
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Extraire le contenu du PDF si c'est un PDF
                        if (($data['content_type'] ?? 'text') === 'pdf' && !empty($data['media_url'])) {
                            try {
                                $pdfFile = $data['media_url'];
                                $extractionService = app(ChapterPdfExtractionService::class);

                                // Créer un setter temporaire pour capturer les données extraites
                                $extractedData = [];
                                $tempSet = function ($key, $value) use (&$extractedData) {
                                    $extractedData[$key] = $value;
                                };

                                $extractionService->extractAndSetFormData($pdfFile, $tempSet);

                                // Fusionner les données extraites avec les données du formulaire
                                $data = array_merge($data, $extractedData);

                                Notification::make()
                                    ->title('Extraction PDF réussie')
                                    ->body('Le contenu du PDF a été extrait avec succès.')
                                    ->success()
                                    ->send();
                            } catch (Exception $e) {
                                Notification::make()
                                    ->title('Avertissement')
                                    ->body('PDF uploadé mais extraction échouée: ' . $e->getMessage())
                                    ->warning()
                                    ->send();
                            }
                        }

                        return $data;
                    }),
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
                            // order_position sera calculé automatiquement par le modèle
                            $newChapter->save();

                            $this->mountedTableActionRecord = $newChapter->getKey();
                        })
                        ->successNotificationTitle('Chapitre dupliqué avec succès'),
                ]),
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
