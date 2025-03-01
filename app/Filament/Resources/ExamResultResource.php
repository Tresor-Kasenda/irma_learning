<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ExamResultEnum;
use App\Filament\Resources\ExamResultResource\Pages;
use App\Models\ExamResult;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class ExamResultResource extends Resource
{
    protected static ?string $model = ExamResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Gestion de formation';

    protected static ?string $label = 'Résulats'; // Nom de la ressource

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Étudiant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('chapter.title')
                    ->label('Chapitre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Note')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'passed' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Date de publication')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('chapter_id')
                    ->relationship('chapter', 'title', function ($query) {
                        $query->whereHas('examination');
                    })
                    ->label('Chapitre')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('student_id')
                    ->relationship('student', 'name', function ($query) {
                        $query->whereHas('submissions');
                    })
                    ->label('Étudiant')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(ExamResultEnum::class)
                    ->label('Statut')
                    ->searchable(),
            ])
            ->groups([
                Tables\Grouping\Group::make('chapter_id')
                    ->label('Chapitre')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'chapter.title'))
                    ->collapsible(),
                Tables\Grouping\Group::make('student_id')
                    ->label('Etudiant')
                    ->getTitleFromRecordUsing(fn ($record) => data_get($record, 'user.name'))
                    ->collapsible(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Select::make('chapter_id')
                            ->label('Chapitre')
                            ->required()
                            ->relationship('chapter', 'title', function ($query) {
                                $query->whereHas('examination');
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('examination_id', null)),
                        Select::make('examination_id')
                            ->label('Examen')
                            ->required()
                            ->relationship('examination', 'title', function ($query, $get) {
                                $chapterId = $get('chapter_id');
                                if ($chapterId) {
                                    $query->where('chapter_id', $chapterId);
                                }
                            })
                            ->searchable()
                            ->visible(fn ($get) => filled($get('chapter_id'))),

                        Select::make('student_id')
                            ->label('Étudiant')
                            ->required()
                            ->relationship('student', 'name', function ($query, $get) {
                                $examinationId = $get('examination_id');
                                if ($examinationId) {
                                    $query->whereHas('submissions', function ($subquery) use ($examinationId) {
                                        $subquery->where('examination_id', $examinationId);
                                    });
                                }
                            })
                            ->getSearchResultsUsing(function (string $search, $get) {
                                $examinationId = $get('examination_id');
                                $query = \App\Models\User::query()
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('surname', 'like', "%{$search}%")
                                            ->orWhere('first_name', 'like', "%{$search}%")
                                            ->orWhere('reference_code', 'like', "%{$search}%");
                                    });

                                if ($examinationId) {
                                    $query->whereHas('submissions', function ($subquery) use ($examinationId) {
                                        $subquery->where('examination_id', $examinationId);
                                    });
                                }

                                return $query->limit(50)->pluck('name', 'id');
                            })
                            ->searchable(),
                        TextInput::make('score')
                            ->label('Note')
                            ->required()
                            ->placeholder('Note sur 10')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10),
                        Select::make('status')
                            ->label('Statut')
                            ->placeholder('Statut')
                            ->required()
                            ->options(ExamResultEnum::class),
                        Textarea::make('feedback')
                            ->label('Commentaire')
                            ->placeholder('Commentaire')
                            ->rows(4)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
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
            'index' => Pages\ListExamResults::route('/'),
            'create' => Pages\CreateExamResult::route('/create'),
            'edit' => Pages\EditExamResult::route('/{record}/edit'),
        ];
    }
}
