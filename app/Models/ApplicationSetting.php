<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsAllActivity;
use Database\Factories\ApplicationSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class ApplicationSetting extends Model
{
    /** @use HasFactory<ApplicationSettingFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $fillable = [
        'app_name',
        'app_tagline',
        'support_email',
        'logo_path',
        'primary_color',
        'default_currency',
        'allow_registration',
        'maintenance_message',
        'certificate_signature_name',
    ];

    public static function current(): self
    {
        $attributes = Cache::rememberForever(
            'application_settings',
            fn (): array => self::query()->firstOrCreate()->refresh()->attributesToArray()
        );

        return (new self())->newFromBuilder($attributes);
    }

    protected static function booted(): void
    {
        self::saved(fn (): bool => Cache::forget('application_settings'));
        self::deleted(fn (): bool => Cache::forget('application_settings'));
    }

    protected function casts(): array
    {
        return ['allow_registration' => 'boolean'];
    }
}
