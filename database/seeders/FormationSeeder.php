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

        // === Formation 4 (payante) ===
        Formation::query()->create([
            'title' => 'Cybersécurité & Ethical Hacking',
            'short_description' => 'Protégez les systèmes et découvrez les techniques des hackers éthiques.',
            'description' => 'Cette formation complète vous plonge dans l\'univers de la cybersécurité. Vous apprendrez les fondamentaux de la sécurité informatique, les techniques de pentest, l\'analyse de vulnérabilités, la sécurisation des réseaux et des applications web. Vous utiliserez des outils comme Kali Linux, Metasploit, Wireshark et Burp Suite pour des missions pratiques.',
            'price' => 200000.00,
            'duration_hours' => 110,
            'difficulty_level' => 'advanced',
            'is_active' => true,
            'is_featured' => true,
            'tags' => ['Cybersécurité', 'Ethical Hacking', 'Pentest', 'Kali Linux', 'Sécurité Réseau'],
        ]);

        // === Formation 5 (payante) ===
        Formation::query()->create([
            'title' => 'Cloud Computing & DevOps',
            'short_description' => 'Maîtrisez le cloud AWS, Docker, Kubernetes et les pratiques DevOps.',
            'description' => 'Une formation intensive sur le cloud computing et les pratiques DevOps modernes. Vous découvrirez AWS (EC2, S3, Lambda), la conteneurisation avec Docker, l\'orchestration avec Kubernetes, et les pipelines CI/CD. Idéal pour les développeurs et administrateurs systèmes souhaitant monter en compétence.',
            'price' => 160000.00,
            'duration_hours' => 90,
            'difficulty_level' => 'advanced',
            'is_active' => true,
            'is_featured' => false,
            'tags' => ['Cloud', 'DevOps', 'AWS', 'Docker', 'Kubernetes', 'CI/CD'],
        ]);

        // === Formation 6 (gratuite) ===
        Formation::query()->create([
            'title' => 'Initiation à l\'Algorithmique & Logique de Programmation',
            'short_description' => 'Les bases de l\'algorithmique pour débuter en programmation.',
            'description' => 'Cette formation gratuite vous initie aux concepts fondamentaux de l\'algorithmique et de la logique de programmation. Vous apprendrez les structures de contrôle, les types de données, les algorithmes de tri et de recherche, et la résolution de problèmes. Aucun prérequis technique nécessaire.',
            'price' => null,
            'duration_hours' => 30,
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'is_featured' => false,
            'tags' => ['Algorithmique', 'Programmation', 'Logique', 'Débutant'],
        ]);

        // === Formation 7 (gratuite) ===
        Formation::query()->create([
            'title' => 'Excel & Analyse de Données pour les Affaires',
            'short_description' => 'Maîtrisez Excel pour l\'analyse de données professionnelle.',
            'description' => 'Une formation pratique et gratuite pour maîtriser Excel dans un contexte professionnel. Vous apprendrez les formules avancées, les tableaux croisés dynamiques, les graphiques, la mise en forme conditionnelle, et les macros VBA. Parfait pour les professionnels souhaitant améliorer leur productivité.',
            'price' => null,
            'duration_hours' => 25,
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'is_featured' => true,
            'tags' => ['Excel', 'Analyse de données', 'Tableaux croisés', 'VBA', 'Productivité'],
        ]);

        // === Formation 8 (gratuite) ===
        Formation::query()->create([
            'title' => 'Design Thinking & Innovation',
            'short_description' => 'Apprenez à innover avec la méthode Design Thinking.',
            'description' => 'Découvrez le Design Thinking, une méthode de résolution de problèmes centrée sur l\'humain. Cette formation gratuite vous guide à travers les 5 étapes : empathie, définition, idéation, prototypage et test. Idéal pour les entrepreneurs, chefs de projet et créatifs.',
            'price' => null,
            'duration_hours' => 20,
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'is_featured' => false,
            'tags' => ['Design Thinking', 'Innovation', 'Créativité', 'Prototypage', 'Méthode Agile'],
        ]);

        // === Formation 9 (gratuite) ===
        Formation::query()->create([
            'title' => 'Communication & Prise de Parole en Public',
            'short_description' => 'Développez votre aisance à l\'oral et votre charisme.',
            'description' => 'Apprenez à communiquer efficacement et à captiver votre auditoire. Cette formation couvre les techniques de prise de parole, la gestion du trac, la communication non verbale, l\'art du storytelling, et la préparation de présentations percutantes. Idéal pour les professionnels et les étudiants.',
            'price' => null,
            'duration_hours' => 15,
            'difficulty_level' => 'beginner',
            'is_active' => true,
            'is_featured' => false,
            'tags' => ['Communication', 'Prise de parole', 'Storytelling', 'Présentation', 'Charisme'],
        ]);

        // === Formation 10 (gratuite) ===
        Formation::query()->create([
            'title' => 'Gestion de Projet Agile avec Scrum',
            'short_description' => 'Maîtrisez la méthodologie Scrum pour gérer vos projets.',
            'description' => 'Une formation gratuite complète sur la gestion de projet agile avec Scrum. Vous découvrirez les rôles (Scrum Master, Product Owner, Developers), les cérémonies (Sprint Planning, Daily Scrum, Sprint Review, Rétrospective), et les artefacts (Product Backlog, Sprint Backlog, Incrément). Préparez-vous à passer la certification Scrum Master.',
            'price' => null,
            'duration_hours' => 20,
            'difficulty_level' => 'intermediate',
            'is_active' => true,
            'is_featured' => true,
            'tags' => ['Scrum', 'Agile', 'Gestion de projet', 'Certification', 'Productivité'],
        ]);
    }
}
