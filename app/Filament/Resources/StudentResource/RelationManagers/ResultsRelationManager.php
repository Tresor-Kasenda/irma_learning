<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Enums\ExamResultEnum;
use App\Models\Examination;
use App\Models\ExamResult;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    public function form(Form $form): Form
    {
        $studentId = $this->getOwnerRecord()->id;
        $chaptersWithResults = ExamResult::where('student_id', $studentId)
            ->pluck('chapter_id')
            ->toArray();
        return $form
            ->schema([
                Forms\Components\Section::make('Publication des resultats')
                    ->schema([
                        Forms\Components\Select::make('chapter_id')
                            ->label('Chapitre')
                            ->required()
                            ->relationship('chapter', 'title', function (Builder $query) use ($chaptersWithResults) {
                                // Exclude chapters that already have results
                                if (!empty($chaptersWithResults)) {
                                    $query->whereNotIn('id', $chaptersWithResults);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $set('examination_id', null);

                                // Auto-select examination if only one exists
                                if ($state) {
                                    $examinations = Examination::where('chapter_id', $state)->get();
                                    if ($examinations->count() === 1) {
                                        $set('examination_id', $examinations->first()->id);
                                    }
                                }
                            }),

                        // Rest of your form fields remain unchanged
                        Forms\Components\Select::make('examination_id')
                            ->label('Examen')
                            ->required()
                            ->relationship('examination', 'title', function (Builder $query, callable $get) {
                                $chapterId = $get('chapter_id');
                                if ($chapterId) {
                                    $query->where('chapter_id', $chapterId);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->disabled() // Field is visually disabled
                            ->dehydrated() // Ensures value is still submitted with form data
                            ->exists('examinations', 'id')
                            ->helperText(function (callable $get) {
                                $chapterId = $get('chapter_id');
                                if (!$chapterId) {
                                    return 'Veuillez sélectionner un chapitre';
                                }

                                $count = Examination::where('chapter_id', $chapterId)->count();
                                if ($count === 0) {
                                    return 'Aucun examen n\'existe pour ce chapitre';
                                }

                                return null;
                            }),
                        Forms\Components\TextInput::make('score')
                            ->label('Note')
                            ->required()
                            ->placeholder('Note sur 10')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->placeholder('Statut')
                            ->required()
                            ->options(ExamResultEnum::class),
                        Forms\Components\FileUpload::make('path')
                            ->directory('chapters')
                            ->label('Copie d\'examen')
                            ->downloadable()
                            ->previewable()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // Taille maximale de 10MB
                            ->deletable()
                            ->uploadingMessage('Uploading certification...')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('feedback')
                            ->label('Commentaire')
                            ->placeholder('Commentaire')
                            ->rows(4)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('score')
            ->columns([
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('Chapitre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('evaluator.name')
                    ->label('Correcteur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Note')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'passed' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Date de publication')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, $livewire): array {
                        $data['student_id'] = $livewire->ownerRecord->id;
                        $data['evaluated_by'] = Auth::user()->id;
                        $data['published_at'] = Carbon::now();
                        return $data;
                    })
                    ->slideOver()
                    ->label("Ajouter un résultat")
                    ->icon('heroicon-s-plus-circle'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
