<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamSubmissionResource\Pages;

use App\Filament\Resources\ExamSubmissionResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

final class ViewExamSubmission extends ViewRecord
{
    protected static string $resource = ExamSubmissionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Informations de soumission')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Étudiant'),
                                        TextEntry::make('examination.title')
                                            ->label('Examen'),
                                        TextEntry::make('submitted_at')
                                            ->label('Date de soumission')
                                            ->dateTime(),
                                        TextEntry::make('score')
                                            ->label('Score')
                                            ->suffix('/100'),
                                    ]),
                                TextEntry::make('comment')
                                    ->label('Commentaire')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Fichier soumis')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('file_path')
                                    ->label('Fichier')
                                    ->copyable()
                                    ->badge(),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $filePath = $this->record->file_path;

                    return Storage::download($filePath);
                })
                ->visible(fn () => $this->record->file_path !== null),
        ];
    }
}
