<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\ExamSubmissionResource\Pages;
use App\Models\ExamSubmission;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

final class ExamSubmissionResource extends Resource
{
    protected static ?string $model = ExamSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Gestion de formation';

    protected static ?string $label = 'Examens'; // Nom de la ressource

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable()
                    ->label('Etudiant'),
                Tables\Columns\TextColumn::make('chapter.title')
                    ->sortable()
                    ->searchable()
                    ->label('Chapitre'),
                Tables\Columns\TextColumn::make('examination.title')
                    ->sortable()
                    ->searchable()
                    ->label('Examen'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->searchable()
                    ->sortable()
                    ->label('Date de soumission'),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Fichier')
                    ->url(fn(ExamSubmission $record): ?string => $record->file_path ? Storage::url($record->file_path) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship(
                        'user',
                        'name',
                        fn($query) => $query->where('role', UserRoleEnum::STUDENT)
                    )
                    ->label('Etudiant')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('chapter')
                    ->relationship('chapter', 'title')
                    ->label('Chapitre')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('examination')
                    ->relationship('examination', 'title')
                    ->label('Examen')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('submitted_at')
                    ->label('Validated Date')
                    ->form([
                        DatePicker::make('date')
                            ->native(false)
                            ->placeholder('Select Date')
                            ->label('Select Date'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['date'])) {
                            $query->whereDate('submitted_at', '=', $data['date']);
                        }
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('chapter_id')
                    ->label('Chapitre')
                    ->getTitleFromRecordUsing(fn($record) => data_get($record, 'chapter.title'))
                    ->collapsible(),
                Tables\Grouping\Group::make('user_id')
                    ->label('Etudiant')
                    ->getTitleFromRecordUsing(fn($record) => data_get($record, 'user.name'))
                    ->collapsible(),
            ])
            ->defaultGroup('chapter_id')
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListExamSubmissions::route('/'),
            'view' => Pages\ViewExamSubmission::route('/{record}/view'),
        ];
    }
}
