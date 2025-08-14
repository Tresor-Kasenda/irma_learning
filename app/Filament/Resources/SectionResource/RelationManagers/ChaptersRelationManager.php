<?php

namespace App\Filament\Resources\SectionResource\RelationManagers;

use App\Enums\ChapterTypeEnum;
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

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('content_type')
                            ->label('Type de contenu')
                            ->options(ChapterTypeEnum::class)
                            ->required()
                            ->native(false),

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
                    ]),

                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu principal')
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

                Tables\Columns\TextColumn::make('progress_count')
                    ->label('Progression')
                    ->counts('progress')
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('preview')
                    ->label('Aperçu')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    //->modalContent(fn (Chapter $record) => view('filament.resources.chapter.preview', ['chapter' => $record]))
                    ->modalWidth('5xl'),

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
