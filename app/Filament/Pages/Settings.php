<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class Settings extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Paramètres';
    protected static ?int $navigationSort = 5;
    public ?array $data = [];
    protected string $view = 'filament.pages.settings';

    public function mount(): void
    {
        $this->data = [
            'site_name' => Setting::get('site_name', config('app.name')),
            'theme' => Setting::get('theme', 'system'),
        ];
    }

    public function save(): void
    {
        Setting::set('site_name', $this->data['site_name'] ?? null);
        Setting::set('theme', $this->data['theme'] ?? 'system');

        Notification::make()
            ->success()
            ->title('Settings updated')
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('site_name')
                ->label('Site Name')
                ->required(),

            Select::make('theme')
                ->label('Theme')
                ->options([
                    'system' => 'System',
                    'light' => 'Light',
                    'dark' => 'Dark',
                ])
                ->required(),
        ];
    }
}
