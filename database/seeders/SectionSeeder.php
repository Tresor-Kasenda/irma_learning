<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

final class SectionSeeder extends Seeder
{
    public function run(): void
    {
        // === Formation 1: Développement Web Full Stack ===
        Section::query()->create([
            'formation_id' => 1,
            'title' => 'Introduction au Développement Web',
            'description' => 'Les fondamentaux du web : HTML5, CSS3, et les principes de base.',
            'order_position' => 1,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 1,
            'title' => 'PHP & Laravel - Backend',
            'description' => 'Maîtrisez PHP et le framework Laravel pour construire des API puissantes.',
            'order_position' => 2,
            'duration' => 300,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 1,
            'title' => 'React & Tailwind CSS - Frontend',
            'description' => 'Créez des interfaces utilisateur modernes et réactives.',
            'order_position' => 3,
            'duration' => 250,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 1,
            'title' => 'Déploiement & Mise en Production',
            'description' => 'Apprenez à déployer vos applications sur le cloud.',
            'order_position' => 4,
            'duration' => 100,
            'is_active' => true,
        ]);

        // === Formation 2: Marketing Digital ===
        Section::query()->create([
            'formation_id' => 2,
            'title' => 'Fondamentaux du Marketing Digital',
            'description' => 'Les bases du marketing digital et l\'écosystème numérique.',
            'order_position' => 1,
            'duration' => 90,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 2,
            'title' => 'SEO & Référencement Naturel',
            'description' => 'Optimisez votre visibilité sur les moteurs de recherche.',
            'order_position' => 2,
            'duration' => 150,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 2,
            'title' => 'Publicité Payante & SEA',
            'description' => 'Maîtrisez Google Ads et les campagnes publicitaires.',
            'order_position' => 3,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 2,
            'title' => 'Réseaux Sociaux & Content Marketing',
            'description' => 'Stratégies de contenu et animation de communautés.',
            'order_position' => 4,
            'duration' => 120,
            'is_active' => true,
        ]);

        // === Formation 3: Data Science ===
        Section::query()->create([
            'formation_id' => 3,
            'title' => 'Introduction à Python pour la Data Science',
            'description' => 'Les bases de Python et les outils essentiels.',
            'order_position' => 1,
            'duration' => 180,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 3,
            'title' => 'Manipulation & Analyse de Données',
            'description' => 'Utilisation de Pandas et NumPy pour l\'analyse de données.',
            'order_position' => 2,
            'duration' => 240,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 3,
            'title' => 'Visualisation de Données',
            'description' => 'Créez des visualisations percutantes avec Matplotlib et Seaborn.',
            'order_position' => 3,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 3,
            'title' => 'Machine Learning',
            'description' => 'Les algorithmes de ML et leur implémentation avec Scikit-learn.',
            'order_position' => 4,
            'duration' => 300,
            'is_active' => true,
        ]);
    }
}
