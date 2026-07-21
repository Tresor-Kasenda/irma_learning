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

    public const DEFAULT_CONTACT_ADDRESS_PRIMARY = '07, Av. OUA, Kinshasa - Ngaliema, RD Congo';

    public const DEFAULT_CONTACT_ADDRESS_SECONDARY = '07, Av. OUA, Kinshasa - Ngaliema, RD Congo';

    public const DEFAULT_CONTACT_EMAIL = 'info@btpcma.org';

    public const DEFAULT_CONTACT_PHONE = '+243 974 078 656';

    public const DEFAULT_HOME_HERO_TITLE = 'Formation BTP & Artisanat en RDC';

    public const DEFAULT_HOME_HERO_SUBTITLE = 'Développez vos compétences dans le Bâtiment et les Travaux Publics avec le Club BTP & CMA';

    public const DEFAULT_HOME_FEATURES = [
        'Club BTP — Dédié aux entreprises structurées (PME, grandes entreprises, institutions, investisseurs) : formations en gestion de projet, normes de construction, sécurité sur chantier et management.',
        "CMA (Chambre des Métiers et de l'Artisanat) — Dédiée aux artisans, techniciens, coopératives et petites unités de production : formations pratiques en maçonnerie, électricité, plomberie et finitions.",
        'Formations certifiantes et continues — Programmes courts et parcours professionnalisants pour acquérir, actualiser ou améliorer vos compétences tout au long de votre carrière dans le BTP.',
    ];

    public const DEFAULT_AUTH_PAGE_SUBTITLE = 'Bâtissez votre avenir professionnel dans le BTP et l\'Artisanat';

    public const DEFAULT_AUTH_LOGIN_TITLE = 'Bienvenue sur {app_name}';

    public const DEFAULT_AUTH_REGISTER_TITLE = 'Bienvenue sur {app_name}';

    public const DEFAULT_AUTH_LOGIN_SUBTITLE = 'Connectez-vous à votre espace de formation BTP & CMA';

    public const DEFAULT_AUTH_REGISTER_SUBTITLE = 'Rejoignez le Club BTP & CMA et développez vos compétences';

    public const DEFAULT_CATALOG_INFORMATION_HEADING = 'Informations importantes';

    /**
     * @var array<int, array{title: string, content: string}>
     */
    public const DEFAULT_CATALOG_INFORMATION_ITEMS = [
        [
            'title' => 'Validation des compétences',
            'content' => 'Les évaluations permettent de valider le niveau de compétence et les acquis des participants. Elles constituent une preuve de progression et de crédibilité professionnelle.',
        ],
        [
            'title' => "Modalités d'accès et de passation",
            'content' => 'Les évaluations sont accessibles en ligne à la fin des sections concernées. Suivez les consignes affichées dans votre parcours pour les réaliser.',
        ],
        [
            'title' => 'Publication des résultats et certifications',
            'content' => 'Les résultats sont publiés dans votre espace apprenant. Les certificats sont disponibles dès que les conditions de réussite de la formation sont réunies.',
        ],
    ];

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
        'auth_page_subtitle',
        'auth_login_title',
        'auth_register_title',
        'auth_login_subtitle',
        'auth_register_subtitle',
        'catalog_information_heading',
        'catalog_information_items',
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
            'catalog_information_items' => 'array',
        ];
    }
}
