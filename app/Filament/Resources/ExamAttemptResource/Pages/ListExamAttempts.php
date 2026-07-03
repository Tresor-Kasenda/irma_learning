<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamAttemptResource\Pages;

use App\Filament\Resources\ExamAttemptResource;
use Filament\Resources\Pages\ListRecords;

final class ListExamAttempts extends ListRecords
{
    protected static string $resource = ExamAttemptResource::class;
}
