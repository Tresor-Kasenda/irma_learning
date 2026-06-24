<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Chapter;
use Illuminate\Database\Seeder;

final class ChapterSeeder extends Seeder
{
    public function run(): void
    {
        // === Formation 1 / Section 1: Introduction au Développement Web ===
        Chapter::query()->create([
            'section_id' => 1,
            'title' => 'Introduction à HTML5',
            'description' => 'Découvrez les bases du langage HTML5.',
            'content' => 'HTML5 est le langage standard pour créer des pages web. Dans ce chapitre, nous allons voir les balises essentielles : structure de base, titres, paragraphes, listes, liens et images. Vous apprendrez à organiser votre contenu avec des balises sémantiques comme header, nav, main, section, article et footer.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 1,
            'title' => 'CSS3 - Mise en page et design',
            'description' => 'Apprenez à styliser vos pages web avec CSS3.',
            'content' => 'Le CSS3 permet de donner du style à vos pages HTML. Nous aborderons les sélecteurs, le modèle de boîte, le flexbox, le grid, les animations et les media queries pour le responsive design. Vous serez capable de créer des mises en page modernes et adaptatives.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 1,
            'title' => 'JavaScript - Les Fondamentaux',
            'description' => 'Les bases de la programmation JavaScript.',
            'content' => 'JavaScript est le langage de programmation du web. Ce chapitre couvre les variables, les types de données, les fonctions, les objets, les tableaux, les boucles et les conditions. Vous apprendrez à manipuler le DOM et à gérer les événements utilisateur.',
            'content_type' => 'text',
            'duration_minutes' => 90,
            'order_position' => 3,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 1 / Section 2: PHP & Laravel ===
        Chapter::query()->create([
            'section_id' => 2,
            'title' => 'Introduction à PHP 8',
            'description' => 'Les bases de PHP moderne.',
            'content' => 'PHP 8 apporte des améliorations significatives. Nous verrons la syntaxe moderne, les types stricts, les attributs, les enumérations, et les fonctionnalités orientées objet. Vous comprendrez comment PHP fonctionne côté serveur.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 2,
            'title' => 'Laravel - Routing & Controllers',
            'description' => 'Les bases du framework Laravel.',
            'content' => 'Laravel est le framework PHP le plus populaire. Ce chapitre vous montre comment configurer les routes, créer des contrôleurs, gérer les requêtes et les réponses, et utiliser les middlewares. Vous construirez votre première API REST.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 2,
            'title' => 'Eloquent ORM & Migrations',
            'description' => 'La couche base de données de Laravel.',
            'content' => "Eloquent est l'ORM de Laravel. Vous apprendrez à créer des migrations, des modèles, des relations (belongsTo, hasMany, belongsToMany), et à effectuer des requêtes avancées. La gestion des bases de données n'aura plus de secrets pour vous.",
            'content_type' => 'text',
            'duration_minutes' => 75,
            'order_position' => 3,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 1 / Section 3: React & Tailwind CSS ===
        Chapter::query()->create([
            'section_id' => 3,
            'title' => 'Introduction à React',
            'description' => 'Les concepts fondamentaux de React.',
            'content' => "React est la bibliothèque JavaScript la plus utilisée pour les interfaces utilisateur. Nous verrons les composants, le JSX, les props, le state, et le cycle de vie. Vous comprendrez l'approche déclarative de React.",
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 3,
            'title' => 'Hooks & Gestion d\'état',
            'description' => 'Les hooks React pour une gestion d\'état avancée.',
            'content' => "Les hooks (useState, useEffect, useContext, useReducer) sont au cœur de React moderne. Vous apprendrez à gérer l'état local, les effets de bord, le contexte global, et à optimiser les performances avec useMemo et useCallback.",
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 3,
            'title' => 'Tailwind CSS - Design System',
            'description' => 'Créez des designs professionnels avec Tailwind CSS.',
            'content' => 'Tailwind CSS est un framework utility-first qui accélère le développement. Vous maîtriserez les classes utilitaires, la personnalisation du thème, le responsive design, le dark mode, et la création de composants réutilisables.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 3,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 1 / Section 4: Déploiement ===
        Chapter::query()->create([
            'section_id' => 4,
            'title' => 'Déploiement avec Laravel Forge',
            'description' => 'Mettez votre application en production.',
            'content' => 'Laravel Forge simplifie le déploiement. Vous apprendrez à configurer un serveur VPS, à déployer votre application, à gérer les tâches cron, les queues, et à configurer SSL. Votre site sera prêt pour la production.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 4,
            'title' => 'CI/CD avec GitHub Actions',
            'description' => 'Automatisez vos déploiements.',
            'content' => "L'intégration et le déploiement continus sont essentiels. Ce chapitre vous montre comment configurer GitHub Actions pour exécuter vos tests, analyser votre code, et déployer automatiquement sur votre serveur à chaque push.",
            'content_type' => 'text',
            'duration_minutes' => 30,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 2 / Section 1: Fondamentaux du Marketing Digital ===
        Chapter::query()->create([
            'section_id' => 5,
            'title' => 'L\'écosystème du Marketing Digital',
            'description' => 'Comprendre le paysage du marketing en ligne.',
            'content' => "Le marketing digital englobe toutes les stratégies de promotion sur internet. Nous verrons les différents canaux : SEO, SEA, social media, email marketing, et comment ils s'articulent dans une stratégie globale. Vous découvrirez les KPI essentiels à suivre.",
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 5,
            'title' => 'Stratégie de Contenu',
            'description' => 'Créez une stratégie de contenu efficace.',
            'content' => 'Le content marketing est au cœur du marketing digital. Apprenez à définir votre ligne éditoriale, à créer un calendrier de contenu, à produire des articles de blog, des vidéos et des infographies qui engagent votre audience.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 2 / Section 2: SEO ===
        Chapter::query()->create([
            'section_id' => 6,
            'title' => 'SEO Technique',
            'description' => 'Optimisez la technique de votre site.',
            'content' => "Le SEO technique est la base du référencement. Nous couvrons l'architecture du site, les balises meta, le balisage schema.org, la vitesse de chargement, le mobile-first, les sitemaps et le fichier robots.txt. Un site bien structuré est la clé du succès.",
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 6,
            'title' => 'SEO On-Page & Off-Page',
            'description' => 'Optimisez votre contenu et votre autorité.',
            'content' => "Le SEO on-page concerne l'optimisation du contenu : mots-clés, balises title, meta descriptions, structure Hn, maillage interne. Le SEO off-page traite du netlinking, des backlinks, et de l'autorité de domaine. Une stratégie complète pour grimper dans les SERP.",
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 2 / Section 3: Publicité Payante ===
        Chapter::query()->create([
            'section_id' => 7,
            'title' => 'Google Ads - Campagnes Search',
            'description' => 'Créez des campagnes Google Ads performantes.',
            'content' => 'Google Ads est la plateforme publicitaire la plus puissante. Ce chapitre vous montre comment configurer des campagnes Search, choisir les mots-clés, rédiger des annonces percutantes, définir un budget, et analyser les performances.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 7,
            'title' => 'Social Ads - Facebook & Instagram',
            'description' => 'Publicité sur les réseaux sociaux.',
            'content' => 'Facebook Ads et Instagram Ads offrent un ciblage précis. Vous apprendrez à créer des audiences, à concevoir des visuels accrocheurs, à configurer des campagnes de conversion, et à optimiser votre ROI avec le pixel Facebook.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 2 / Section 4: Réseaux Sociaux ===
        Chapter::query()->create([
            'section_id' => 8,
            'title' => 'Stratégie LinkedIn & Twitter',
            'description' => 'Développez votre marque personnelle.',
            'content' => "LinkedIn est le réseau professionnel par excellence. Apprenez à optimiser votre profil, à créer du contenu engageant, à développer votre réseau, et à utiliser LinkedIn pour le B2B. Twitter/X pour la veille et l'engagement communautaire.",
            'content_type' => 'text',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 8,
            'title' => 'TikTok & Instagram Reels',
            'description' => 'Maîtrisez le marketing vidéo court.',
            'content' => 'La vidéo courte est le format roi. Découvrez comment créer du contenu viral sur TikTok et Instagram Reels, utiliser les tendances, optimiser votre stratégie hashtag, et monétiser votre audience.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 3 / Section 1: Introduction à Python ===
        Chapter::query()->create([
            'section_id' => 9,
            'title' => 'Python - Les bases',
            'description' => 'Les fondamentaux de Python pour la data science.',
            'content' => 'Python est le langage incontournable de la data science. Nous couvrons la syntaxe de base, les types de données, les structures de contrôle, les fonctions, les compréhensions de liste, et la manipulation de fichiers. Même les débutants pourront suivre.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 9,
            'title' => 'Introduction à Jupyter Notebook',
            'description' => 'L\'outil essentiel pour la data science.',
            'content' => "Jupyter Notebook est l'environnement de travail des data scientists. Vous apprendrez à installer et configurer Jupyter, à créer des notebooks, à combiner code et documentation, et à visualiser vos résultats directement dans le notebook.",
            'content_type' => 'text',
            'duration_minutes' => 30,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 3 / Section 2: Manipulation de données ===
        Chapter::query()->create([
            'section_id' => 10,
            'title' => 'NumPy - Calculs Numériques',
            'description' => 'Les tableaux et opérations numériques avec NumPy.',
            'content' => "NumPy est la bibliothèque fondamentale pour le calcul scientifique en Python. Nous explorerons les tableaux multidimensionnels, les opérations vectorisées, les fonctions mathématiques, l'algèbre linéaire, et la génération de nombres aléatoires.",
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 10,
            'title' => 'Pandas - Analyse de données',
            'description' => 'Manipulez et analysez des données tabulaires.',
            'content' => "Pandas est l'outil incontournable pour l'analyse de données. Vous maîtriserez les DataFrames et Series, le nettoyage des données, le filtrage, le regroupement, les jointures, et l'import/export depuis CSV, Excel, JSON et SQL.",
            'content_type' => 'video',
            'duration_minutes' => 120,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 3 / Section 3: Visualisation ===
        Chapter::query()->create([
            'section_id' => 11,
            'title' => 'Matplotlib - Graphiques fondamentaux',
            'description' => 'Créez des graphiques professionnels.',
            'content' => 'Matplotlib est la bibliothèque de visualisation la plus ancienne et la plus flexible. Vous apprendrez à créer des graphiques en lignes, barres, secteurs, histogrammes, boîtes à moustaches, et à personnaliser chaque aspect de vos figures.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 11,
            'title' => 'Seaborn - Visualisations statistiques',
            'description' => 'Des graphiques statistiques élégants.',
            'content' => 'Seaborn simplifie la création de graphiques statistiques. Nous verrons les heatmaps, les pairplots, les boxplots, les violin plots, et comment visualiser les distributions et les relations entre variables.',
            'content_type' => 'text',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 3 / Section 4: Machine Learning ===
        Chapter::query()->create([
            'section_id' => 12,
            'title' => 'Apprentissage Supervisé',
            'description' => 'Les algorithmes de classification et régression.',
            'content' => "L'apprentissage supervisé est au cœur du machine learning. Nous couvrons la régression linéaire, la régression logistique, les arbres de décision, les forêts aléatoires, les SVM, et les k-plus proches voisins. Chaque algorithme est implémenté avec Scikit-learn.",
            'content_type' => 'video',
            'duration_minutes' => 120,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 12,
            'title' => 'Apprentissage Non Supervisé',
            'description' => 'Découvrez le clustering et la réduction de dimension.',
            'content' => "L'apprentissage non supervisé permet d'explorer des données sans étiquettes. Nous étudierons le K-Means, le clustering hiérarchique, le DBSCAN, l'ACP (analyse en composantes principales), et le t-SNE pour visualiser des données complexes.",
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 12,
            'title' => 'Évaluation & Optimisation des modèles',
            'description' => 'Validez et améliorez vos modèles de ML.',
            'content' => "Un bon modèle doit être évalué correctement. Nous verrons les métriques de performance (accuracy, precision, recall, F1, AUC), la validation croisée, la recherche d'hyperparamètres avec GridSearchCV, et les techniques pour éviter le surapprentissage.",
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 3,
            'is_free' => false,
            'is_active' => true,
        ]);
    }
}
