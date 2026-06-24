<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Formation;
use Illuminate\Database\Seeder;

final class FormationSeeder extends Seeder
{
    public function run(): void
    {
        Formation::query()->create([
            'title' => 'Développement Web Full Stack',
            'short_description' => 'Apprenez à créer des applications web modernes de A à Z avec Laravel, React et Tailwind CSS.',
            'description' => 'Cette formation complète vous guide du débutant jusqu\'au développeur Full Stack professionnel. Vous maîtriserez le frontend avec React et Tailwind CSS, le backend avec Laravel, la gestion des bases de données, l\'authentification, les API RESTful, et le déploiement. Chaque module contient des projets pratiques pour consolider vos apprentissages.',
            'price' => 150000.00,
            'duration_hours' => 120,
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'is_featured' => true,
            'tags' => ['Laravel', 'React', 'Tailwind CSS', 'PHP', 'JavaScript', 'Full Stack'],
        ]);

        Formation::query()->create([
            'title' => 'Marketing Digital & Stratégie Social Media',
            'short_description' => 'Maîtrisez les stratégies marketing digitales et la gestion des réseaux sociaux.',
            'description' => 'Plongez dans l\'univers du marketing digital. Cette formation couvre le SEO, le SEA, le marketing de contenu, la gestion des réseaux sociaux, l\'email marketing, l\'analyse de données, et les stratégies de conversion. Vous apprendrez à créer des campagnes efficaces et à mesurer leur performance avec des outils professionnels.',
            'price' => 120000.00,
            'duration_hours' => 80,
            'difficulty_level' => 'intermediate',
            'is_active' => true,
            'is_featured' => true,
            'tags' => ['Marketing Digital', 'SEO', 'Social Media', 'Google Ads', 'Content Marketing'],
        ]);

        Formation::query()->create([
            'title' => 'Data Science & Analyse de Données avec Python',
            'short_description' => 'Devenez expert en analyse de données et machine learning avec Python.',
            'description' => 'Une formation intensive qui vous apprend à manipuler, analyser et visualiser des données avec Python. Vous explorerez NumPy, Pandas, Matplotlib, Scikit-learn, et les fondamentaux du machine learning. Idéal pour les analystes, ingénieurs et toute personne souhaitant se lancer dans la data science.',
            'price' => 180000.00,
            'duration_hours' => 100,
            'difficulty_level' => 'advanced',
            'is_active' => true,
            'is_featured' => false,
            'tags' => ['Python', 'Data Science', 'Machine Learning', 'Pandas', 'NumPy', 'Analyse de données'],
        ]);
    }
}
