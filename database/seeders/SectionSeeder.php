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

        // === Formation 4: Cybersécurité & Ethical Hacking ===
        Section::query()->create([
            'formation_id' => 4,
            'title' => 'Fondamentaux de la Cybersécurité',
            'description' => 'Les bases de la sécurité informatique et des menaces.',
            'order_position' => 1,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 4,
            'title' => 'Ethical Hacking & Pentest',
            'description' => 'Techniques de test d\'intrusion et hacking éthique.',
            'order_position' => 2,
            'duration' => 250,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 4,
            'title' => 'Sécurité des Réseaux',
            'description' => 'Protection des infrastructures réseau.',
            'order_position' => 3,
            'duration' => 180,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 4,
            'title' => 'Sécurité des Applications Web',
            'description' => 'Sécurisez vos applications contre les attaques courantes.',
            'order_position' => 4,
            'duration' => 200,
            'is_active' => true,
        ]);

        // === Formation 5: Cloud Computing & DevOps ===
        Section::query()->create([
            'formation_id' => 5,
            'title' => 'Introduction au Cloud Computing',
            'description' => 'Les concepts clés du cloud et AWS.',
            'order_position' => 1,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 5,
            'title' => 'Docker & Conteneurisation',
            'description' => 'Maîtrisez Docker et la gestion des conteneurs.',
            'order_position' => 2,
            'duration' => 150,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 5,
            'title' => 'Kubernetes & Orchestration',
            'description' => 'Orchestrez vos conteneurs avec Kubernetes.',
            'order_position' => 3,
            'duration' => 200,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 5,
            'title' => 'CI/CD & Infrastructure as Code',
            'description' => 'Automatisez vos déploiements avec des pipelines CI/CD.',
            'order_position' => 4,
            'duration' => 150,
            'is_active' => true,
        ]);

        // === Formation 6: Initiation à l\'Algorithmique ===
        Section::query()->create([
            'formation_id' => 6,
            'title' => 'Concepts Fondamentaux',
            'description' => 'Les bases de l\'algorithmique.',
            'order_position' => 1,
            'duration' => 90,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 6,
            'title' => 'Structures de Données',
            'description' => 'Listes, piles, files, arbres et graphes.',
            'order_position' => 2,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 6,
            'title' => 'Algorithmes de Tri et Recherche',
            'description' => 'Les algorithmes classiques et leur complexité.',
            'order_position' => 3,
            'duration' => 100,
            'is_active' => true,
        ]);

        // === Formation 7: Excel & Analyse de Données ===
        Section::query()->create([
            'formation_id' => 7,
            'title' => 'Les bases d\'Excel',
            'description' => 'Prise en main et fondamentaux d\'Excel.',
            'order_position' => 1,
            'duration' => 60,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 7,
            'title' => 'Formules et Fonctions',
            'description' => 'Maîtrisez les fonctions avancées d\'Excel.',
            'order_position' => 2,
            'duration' => 90,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 7,
            'title' => 'Tableaux Croisés Dynamiques',
            'description' => 'Analysez vos données avec les TCD.',
            'order_position' => 3,
            'duration' => 60,
            'is_active' => true,
        ]);

        // === Formation 8: Design Thinking & Innovation ===
        Section::query()->create([
            'formation_id' => 8,
            'title' => 'Introduction au Design Thinking',
            'description' => 'Les principes fondamentaux du Design Thinking.',
            'order_position' => 1,
            'duration' => 60,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 8,
            'title' => 'Phase d\'Empathie et Définition',
            'description' => 'Comprendre les utilisateurs et définir le problème.',
            'order_position' => 2,
            'duration' => 90,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 8,
            'title' => 'Idéation et Prototypage',
            'description' => 'Générer des idées et créer des prototypes.',
            'order_position' => 3,
            'duration' => 90,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 8,
            'title' => 'Tests et Implémentation',
            'description' => 'Tester vos solutions et les mettre en œuvre.',
            'order_position' => 4,
            'duration' => 60,
            'is_active' => true,
        ]);

        // === Formation 9: Communication & Prise de Parole ===
        Section::query()->create([
            'formation_id' => 9,
            'title' => 'Fondamentaux de la Communication',
            'description' => 'Les bases d\'une communication efficace.',
            'order_position' => 1,
            'duration' => 60,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 9,
            'title' => 'Prise de Parole en Public',
            'description' => 'Techniques pour captiver votre auditoire.',
            'order_position' => 2,
            'duration' => 90,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 9,
            'title' => 'Communication Non Verbale',
            'description' => 'Maîtrisez le langage corporel et le paralangage.',
            'order_position' => 3,
            'duration' => 60,
            'is_active' => true,
        ]);

        // === Formation 10: Gestion de Projet Agile avec Scrum ===
        Section::query()->create([
            'formation_id' => 10,
            'title' => 'Introduction à l\'Agilité',
            'description' => 'Les principes et valeurs du manifeste agile.',
            'order_position' => 1,
            'duration' => 60,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 10,
            'title' => 'Scrum Framework',
            'description' => 'Rôles, événements et artefacts Scrum.',
            'order_position' => 2,
            'duration' => 120,
            'is_active' => true,
        ]);

        Section::query()->create([
            'formation_id' => 10,
            'title' => 'Cérémonies et Artefacts Scrum',
            'description' => 'Mettez en pratique Scrum au quotidien.',
            'order_position' => 3,
            'duration' => 80,
            'is_active' => true,
        ]);
    }
}
