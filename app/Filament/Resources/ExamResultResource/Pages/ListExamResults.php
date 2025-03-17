<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamResultResource\Pages;

use App\Filament\Resources\ExamResultResource;
use Filament\Resources\Pages\ListRecords;

final class ListExamResults extends ListRecords
{
    protected static string $resource = ExamResultResource::class;
}
