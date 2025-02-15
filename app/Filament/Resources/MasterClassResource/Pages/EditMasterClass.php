<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\Pages;

use App\Filament\Resources\MasterClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditMasterClass extends EditRecord
{
    protected static string $resource = MasterClassResource::class;

    public function getHeading(): string|Htmlable
    {
        return 'Modifier le cours';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Supprimer le cours')
                ->icon('heroicon-o-trash'),
        ];
    }
}
