<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\Pages;

use App\Filament\Resources\MasterClassResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListMasterClasses extends ListRecords
{
    protected static string $resource = MasterClassResource::class;

    public function getHeading(): string|Htmlable
    {
        return 'Liste des masterclass';
    }
}
