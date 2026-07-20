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
    public const DEFAULT_CONTACT_ADDRESS_PRIMARY = '07, Av. OUA, Kinshasa - Ngaliema, RD Congo';

    public const DEFAULT_CONTACT_ADDRESS_SECONDARY = '07, Av. OUA, Kinshasa - Ngaliema, RD Congo';

    public const DEFAULT_CONTACT_EMAIL = 'info@btpcma.org';

    public const DEFAULT_CONTACT_PHONE = '+243 974 078 656';

    public const DEFAULT_HOME_HERO_TITLE = 'Formation BTP & Artisanat en RDC';

    public const DEFAULT_HOME_HERO_SUBTITLE = "Développez vos compétences dans le Bâtiment et les Travaux Publics avec le Club BTP & CMA";

    public const DEFAULT_HOME_FEATURES = [
        "Club BTP — Dédié aux entreprises structurées (PME, grandes entreprises, institutions, investisseurs) : formations en gestion de projet, normes de construction, sécurité sur chantier et management.",
        "CMA (Chambre des Métiers et de l'Artisanat) — Dédiée aux artisans, techniciens, coopératives et petites unités de production : formations pratiques en maçonnerie, électricité, plomberie et finitions.",
        'Formations certifiantes et continues — Programmes courts et parcours professionnalisants pour acquérir, actualiser ou améliorer vos compétences tout au long de votre carrière dans le BTP.',
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
            fn (): array => self::query()->firstOrCreate()->refresh()->getRawOriginal()
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
