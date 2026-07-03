<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProgressResource\Pages;

use App\Filament\Resources\UserProgressResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUserProgress extends CreateRecord
{
    protected static string $resource = UserProgressResource::class;
}
