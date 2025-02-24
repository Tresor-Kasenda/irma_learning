<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\Pages;

use App\Filament\Resources\MasterClassResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
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
            Actions\Action::make('Formation continue')
                ->label('Ajouter une formation')
                ->icon("heroicon-o-plus-circle")
                ->modalWidth(MaxWidth::ThreeExtraLarge)
                ->form([
                    TextInput::make('name')
                ])
                ->slideOver()
                ->action(function (array $data): void {
//                    $this->getRecord()->experiences()->create([
//                        'title' => $data['title'],
//                        'business' => $data['business'],
//                        'from' => $data['from'],
//                        'to' => $data['to'],
//                        'message' => $data['message'],
//                    ]);

                    Notification::make()
                        ->success()
                        ->title('Experience saved')
                        ->body('Les informations de l\'expérience ont été sauvegardées.')
                        ->send();
                })
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Modification du cours')
            ->body('Le cours a ete modifier avec success');
    }
}
