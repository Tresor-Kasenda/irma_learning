<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProgressResource\Pages;

use App\Filament\Resources\UserProgressResource;
use Filament\Resources\Pages\ListRecords;

final class ListUserProgress extends ListRecords
{
    protected static string $resource = UserProgressResource::class;
}
