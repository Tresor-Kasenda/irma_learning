<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'Tous' => Tab::make()
                ->badge(UserResource::getModel()::count()),
            'Etudiants' => Tab::make()
                ->query(fn($query) => $query->where('role', UserRoleEnum::STUDENT->value))
                ->badge(UserResource::getModel()::where('role', UserRoleEnum::STUDENT->value)->count()),
            'Administrateurs' => Tab::make()
                ->query(fn($query) => $query->where('role', UserRoleEnum::ADMIN->value))
                ->badge(UserResource::getModel()::where('role', UserRoleEnum::ADMIN->value)->count()),
            'Manager' => Tab::make()
                ->query(fn($query) => $query->where('role', UserRoleEnum::MANAGER->value))
                ->badge(UserResource::getModel()::where('role', UserRoleEnum::MANAGER->value)->count()),
            'Support' => Tab::make()
                ->query(fn($query) => $query->where('role', UserRoleEnum::SUPPORT->value))
                ->badge(UserResource::getModel()::where('role', UserRoleEnum::SUPPORT->value)->count()),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return 'Liste des utilisateurs';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label("Ajouter un utilisateur")
                ->icon('heroicon-o-plus-circle')
                ->visible(fn() => in_array(auth()->user()->role, [
                    UserRoleEnum::ROOT->value,
                    UserRoleEnum::ADMIN->value
                ])),
        ];
    }
}
