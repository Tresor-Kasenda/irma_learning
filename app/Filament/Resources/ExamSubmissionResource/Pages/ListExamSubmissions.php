<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamSubmissionResource\Pages;

use App\Filament\Resources\ExamSubmissionResource;
use Filament\Resources\Pages\ListRecords;

final class ListExamSubmissions extends ListRecords
{
    protected static string $resource = ExamSubmissionResource::class;
}
