<?php
//
//declare(strict_types=1);
//
//namespace App\Filament\Resources;
//
//use App\Enums\ExamResultEnum;
//use App\Filament\Resources\ExamResultResource\Pages;
//use App\Models\ExamResult;
//use Filament\Resources\Resource;
//use Filament\Tables;
//use Filament\Tables\Table;
//
//final class ExamResultResource extends Resource
//{
//    protected static ?string $model = ExamResult::class;
//
//    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
//
//    protected static ?string $navigationGroup = 'Gestion de formation';
//
//    protected static ?string $label = 'Résulats'; // Nom de la ressource
//
//    protected static ?int $navigationSort = 4;
//
//    public static function table(Table $table): Table
//    {
//        return $table
//            ->columns([
//                Tables\Columns\TextColumn::make('student.name')
//                    ->label('Étudiant')
//                    ->searchable()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('chapter.title')
//                    ->label('Chapitre')
//                    ->searchable()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('score')
//                    ->label('Note')
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('status')
//                    ->label('Statut')
//                    ->badge()
//                    ->color(fn(string $state): string => match ($state) {
//                        'passed' => 'success',
//                        'failed' => 'danger',
//                        default => 'warning',
//                    }),
//                Tables\Columns\TextColumn::make('published_at')
//                    ->label('Date de publication')
//                    ->dateTime(),
//            ])
//            ->filters([
//                Tables\Filters\SelectFilter::make('chapter_id')
//                    ->relationship('chapter', 'title', function ($query) {
//                        $query->whereHas('examination');
//                    })
//                    ->label('Chapitre')
//                    ->searchable()
//                    ->preload(),
//                Tables\Filters\SelectFilter::make('student_id')
//                    ->relationship('student', 'name', function ($query) {
//                        $query->whereHas('submissions');
//                    })
//                    ->label('Étudiant')
//                    ->searchable()
//                    ->preload(),
//                Tables\Filters\SelectFilter::make('status')
//                    ->options(ExamResultEnum::class)
//                    ->label('Statut')
//                    ->searchable(),
//            ])
//            ->groups([
//                Tables\Grouping\Group::make('chapter_id')
//                    ->label('Chapitre')
//                    ->getTitleFromRecordUsing(fn($record) => data_get($record, 'chapter.title'))
//                    ->collapsible(),
//                Tables\Grouping\Group::make('student_id')
//                    ->label('Etudiant')
//                    ->getTitleFromRecordUsing(fn($record) => data_get($record, 'user.name'))
//                    ->collapsible(),
//            ])
//            ->actions([
//                Tables\Actions\EditAction::make(),
//            ])
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
//            ]);
//    }
//
//    public static function getRelations(): array
//    {
//        return [
//            //
//        ];
//    }
//
//    public static function getPages(): array
//    {
//        return [
//            'index' => Pages\ListExamResults::route('/'),
//            'create' => Pages\CreateExamResult::route('/create'),
//            'edit' => Pages\EditExamResult::route('/{record}/edit'),
//        ];
//    }
//}
