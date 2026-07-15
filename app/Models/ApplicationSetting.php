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
    public const DEFAULT_CONTACT_ADDRESS_PRIMARY = '269, Av. KASONGO NYEMBO, Q/ Baudouin, Lubumbashi, RD Congo';

    public const DEFAULT_CONTACT_ADDRESS_SECONDARY = '2, Avenue Père Boka, Commune de la Gombe, Kinshasa, RD Congo';

    public const DEFAULT_CONTACT_EMAIL = 'communication@irmardc.org';

    public const DEFAULT_CONTACT_PHONE = '+243 819 742 171';

    public const DEFAULT_HOME_HERO_TITLE = "Devenez Leader du Risque en RD Congo avec l'iRMA";

    public const DEFAULT_HOME_HERO_SUBTITLE = "L'iRMA offre 3 types de formation";

    public const DEFAULT_HOME_FEATURES = [
        "Les certifications professionnelles à travers des formations menant à des Titres Professionnels qui témoignent de l'autorité et de la crédibilité professionnelles des membres. Chaque membre doit totaliser au moins 25 points DPC par période de douze mois afin de conserver son titre professionnel.",
        "La Formation Continue à travers de courts programmes qui permettent aux membres d'acquérir, d'actualiser ou d'améliorer rapidement leur compétence tout au long de leur vie professionnelle. Chaque membre doit cumuler au moins 20 Unités de Formation Continue (UFC) par période de référence de 12 mois.",
        'La Formation en Entreprise livrées en respectant les besoins spécifiques des sociétés à travers une démarche partenariale sur mesure.',
    ];

    /** @use HasFactory<ApplicationSettingFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $fillable = [
        'app_name',
        'app_tagline',
        'support_email',
        'contact_email',
        'contact_phone',
        'contact_address_primary',
        'contact_address_secondary',
        'home_hero_title',
        'home_hero_subtitle',
        'home_features',
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
        return [
            'allow_registration' => 'boolean',
            'home_features' => 'array',
        ];
    }
}
