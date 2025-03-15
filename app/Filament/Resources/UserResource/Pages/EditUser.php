<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label("Supprimer l'utilisateur")
                ->icon('heroicon-o-trash')
                ->visible(fn() => in_array(auth()->user()->role, [
                    UserRoleEnum::ROOT->value
                ])),
        ];
    }
}
