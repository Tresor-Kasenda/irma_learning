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

        // === Formation 4 / Section 13: Fondamentaux de la Cybersécurité ===
        Chapter::query()->create([
            'section_id' => 13,
            'title' => 'Introduction à la Cybersécurité',
            'description' => 'Les concepts clés de la sécurité informatique.',
            'content' => 'La cybersécurité est l\'ensemble des pratiques visant à protéger les systèmes informatiques contre les attaques. Ce chapitre couvre les types de menaces (malwares, phishing, DDoS), les principes de sécurité (CIA : Confidentialité, Intégrité, Disponibilité), et le paysage actuel des cyberattaques.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 13,
            'title' => 'Cryptographie & Chiffrement',
            'description' => 'Les bases du chiffrement et de la cryptographie.',
            'content' => 'La cryptographie est essentielle à la sécurité moderne. Vous découvrirez le chiffrement symétrique et asymétrique, les fonctions de hachage, les certificats SSL/TLS, et la signature électronique. Des exemples pratiques avec OpenSSL et GPG.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 4 / Section 14: Ethical Hacking & Pentest ===
        Chapter::query()->create([
            'section_id' => 14,
            'title' => 'Méthodologie du Pentest',
            'description' => 'Les phases d\'un test d\'intrusion professionnel.',
            'content' => 'Un pentest suit une méthodologie stricte : reconnaissance, scanning, exploitation, post-exploitation et reporting. Vous apprendrez à utiliser Kali Linux, Nmap, Metasploit et à structurer vos tests avec la norme PTES (Penetration Testing Execution Standard).',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 14,
            'title' => 'Exploitation de Vulnérabilités',
            'description' => 'Identifiez et exploitez les failles de sécurité.',
            'content' => 'Mettez en pratique vos connaissances avec des exercices sur des systèmes vulnérables. Vous utiliserez Burp Suite pour les tests web, Metasploit pour l\'exploitation, et analyserez des vulnérabilités courantes : injections SQL, XSS, CSRF, et buffer overflows.',
            'content_type' => 'video',
            'duration_minutes' => 120,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 4 / Section 15: Sécurité des Réseaux ===
        Chapter::query()->create([
            'section_id' => 15,
            'title' => 'Architecture Réseau Sécurisée',
            'description' => 'Concevez des réseaux résistants aux attaques.',
            'content' => 'Une architecture réseau sécurisée repose sur le cloisonnement, les VLANs, les DMZ, et les pare-feux. Vous apprendrez à configurer des règles de filtrage, à sécuriser les accès distants avec VPN, et à mettre en place la détection d\'intrusion avec Snort et Suricata.',
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 15,
            'title' => 'Analyse de Trafic & Forensique',
            'description' => 'Analysez le trafic réseau et enquêtez sur les incidents.',
            'content' => 'Wireshark est l\'outil indispensable pour l\'analyse de trafic réseau. Vous apprendrez à capturer et filtrer le trafic, à identifier des comportements suspects, et à mener une investigation forensique post-incident. Des scénarios réels d\'attaques réseaux seront analysés.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 4 / Section 16: Sécurité des Applications Web ===
        Chapter::query()->create([
            'section_id' => 16,
            'title' => 'OWASP Top 10',
            'description' => 'Les 10 vulnérabilités web les plus critiques.',
            'content' => 'L\'OWASP Top 10 est la référence des vulnérabilités web. Nous détaillons chaque catégorie : injections, XSS, broken authentication, sensitive data exposure, XXE, broken access control, etc. Pour chaque faille, vous verrez comment l\'identifier et la corriger.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 16,
            'title' => 'Sécurisation d\'une Application Laravel',
            'description' => 'Protégez votre application Laravel contre les attaques.',
            'content' => 'Laravel intègre de nombreuses protections de série. Ce chapitre montre comment sécuriser une application Laravel : protection CSRF, validation des entrées, échappement des sorties, authentification robuste, rate limiting, et durcissement de la configuration serveur.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 5 / Section 17: Introduction au Cloud Computing ===
        Chapter::query()->create([
            'section_id' => 17,
            'title' => 'Concepts du Cloud Computing',
            'description' => 'Les fondamentaux du cloud : IaaS, PaaS, SaaS.',
            'content' => 'Le cloud computing transforme la façon dont les applications sont déployées et gérées. Ce chapitre explique les modèles de service (IaaS, PaaS, SaaS), les modèles de déploiement (public, privé, hybride), et les avantages du cloud : scalabilité, élasticité, et paiement à l\'usage.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 17,
            'title' => 'AWS - Services Essentiels',
            'description' => 'Découvrez les services clés d\'AWS.',
            'content' => 'Amazon Web Services est le leader du cloud. Vous découvrirez EC2 (machines virtuelles), S3 (stockage objet), RDS (bases de données gérées), Lambda (serverless), et IAM (gestion des identités et accès). Des exercices pratiques pour chaque service.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 5 / Section 18: Docker & Conteneurisation ===
        Chapter::query()->create([
            'section_id' => 18,
            'title' => 'Docker - Prise en main',
            'description' => 'Les bases de Docker et des conteneurs.',
            'content' => 'Docker révolutionne le déploiement des applications. Vous apprendrez à créer des images avec des Dockerfiles, à gérer les conteneurs, les volumes, les réseaux, et à utiliser Docker Compose pour des applications multi-conteneurs. Des exemples concrets avec Laravel et PostgreSQL.',
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 18,
            'title' => 'Docker en Production',
            'description' => 'Bonnes pratiques et optimisation des conteneurs.',
            'content' => 'Passer Docker en production demande des bonnes pratiques : images multistage, sécurité des conteneurs, gestion des secrets, logging centralisé, monitoring avec Prometheus, et orchestration des déploiements blue/green.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 5 / Section 19: Kubernetes & Orchestration ===
        Chapter::query()->create([
            'section_id' => 19,
            'title' => 'Kubernetes - Concepts Fondamentaux',
            'description' => 'L\'essentiel de Kubernetes pour orchestrer vos conteneurs.',
            'content' => 'Kubernetes est la plateforme d\'orchestration de conteneurs la plus populaire. Ce chapitre couvre les pods, les deployments, les services, les namespaces, et les configmaps. Vous déploierez votre première application sur un cluster Kubernetes avec Minikube.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 19,
            'title' => 'Kubernetes Avancé',
            'description' => 'Ingress, Helm, et gestion des stocks.',
            'content' => 'Approfondissez Kubernetes avec les Ingress controllers, le gestionnaire de paquets Helm, les volumes persistants, les StatefulSets, et le auto-scaling (HPA). Vous apprendrez à gérer des applications d\'état et à automatiser les déploiements sur le cloud.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 5 / Section 20: CI/CD & Infrastructure as Code ===
        Chapter::query()->create([
            'section_id' => 20,
            'title' => 'Pipelines CI/CD avec GitHub Actions',
            'description' => 'Automatisez vos tests et déploiements.',
            'content' => 'GitHub Actions permet de créer des pipelines CI/CD directement depuis votre dépôt. Vous configurerez des workflows pour exécuter les tests, analyser la qualité du code, builder les images Docker, et déployer automatiquement sur AWS ou DigitalOcean.',
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 20,
            'title' => 'Terraform & Infrastructure as Code',
            'description' => 'Gérez votre infrastructure avec Terraform.',
            'content' => 'Terraform permet de décrire votre infrastructure sous forme de code (IaC). Vous apprendrez à écrire des configurations Terraform pour provisionner des ressources AWS (EC2, RDS, VPC), à gérer l\'état distant, et à intégrer Terraform dans vos pipelines CI/CD.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 6 / Section 21: Concepts Fondamentaux ===
        Chapter::query()->create([
            'section_id' => 21,
            'title' => 'Introduction à l\'Algorithmique',
            'description' => 'Qu\'est-ce qu\'un algorithme ?',
            'content' => 'Un algorithme est une suite d\'instructions permettant de résoudre un problème. Ce chapitre introductif présente les concepts de base : variables, constantes, opérateurs, expressions, et la représentation d\'algorithmes avec des organigrammes et du pseudo-code.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 21,
            'title' => 'Structures de Contrôle',
            'description' => 'Conditions et boucles en algorithmique.',
            'content' => 'Les structures de contrôle sont essentielles : conditions (if/else, switch), boucles (for, while, do-while), et les structures conditionnelles complexes. Vous résoudrez des exercices pratiques pour maîtriser chaque concept.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 6 / Section 22: Structures de Données ===
        Chapter::query()->create([
            'section_id' => 22,
            'title' => 'Listes, Piles et Files',
            'description' => 'Les structures linéaires fondamentales.',
            'content' => 'Les structures de données linéaires sont la base de l\'algorithmique : listes chaînées, piles (LIFO), files (FIFO). Vous implémenterez chaque structure et découvrirez leurs applications concrètes : évaluation d\'expressions, parcours en largeur, undo/redo.',
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 22,
            'title' => 'Arbres et Graphes',
            'description' => 'Les structures non linéaires et leurs parcours.',
            'content' => 'Les arbres (binaires, AVL, B-trees) et les graphes sont au cœur de nombreux algorithmes. Vous étudierez les parcours (DFS, BFS), les arbres de recherche, et les algorithmes de plus court chemin (Dijkstra, Bellman-Ford).',
            'content_type' => 'text',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 6 / Section 23: Algorithmes de Tri et Recherche ===
        Chapter::query()->create([
            'section_id' => 23,
            'title' => 'Algorithmes de Tri',
            'description' => 'Les méthodes de tri classiques.',
            'content' => 'Le tri est un problème fondamental. Vous étudierez le tri à bulles, le tri par sélection, le tri par insertion, le tri fusion, le tri rapide (quicksort). Pour chaque algorithme, nous analyserons la complexité temporelle et spatiale avec la notation Big O.',
            'content_type' => 'video',
            'duration_minutes' => 90,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 23,
            'title' => 'Algorithmes de Recherche',
            'description' => 'Recherche séquentielle, binaire et par interpolation.',
            'content' => 'La recherche dans les données est une opération courante. Vous maîtriserez la recherche séquentielle, la recherche binaire (sur tableaux triés), et la recherche par interpolation. Des exercices sur des jeux de données réels pour consolider vos acquis.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 7 / Section 24: Les bases d\'Excel ===
        Chapter::query()->create([
            'section_id' => 24,
            'title' => 'Prise en main d\'Excel',
            'description' => 'Interface, navigation et premiers pas.',
            'content' => 'Découvrez l\'interface d\'Excel : le ruban, les onglets, la barre de formule, les classeurs et feuilles de calcul. Vous apprendrez à saisir des données, à naviguer efficacement, à utiliser les raccourcis clavier, et à personnaliser votre environnement de travail.',
            'content_type' => 'video',
            'duration_minutes' => 30,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 24,
            'title' => 'Mise en Forme et Impression',
            'description' => 'Présentez vos données de manière professionnelle.',
            'content' => 'La mise en forme est cruciale pour la lisibilité. Vous maîtriserez les polices, les couleurs, les bordures, la mise en forme conditionnelle, les styles de cellule, et les options d\'impression (sauts de page, entêtes, pieds de page).',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 7 / Section 25: Formules et Fonctions ===
        Chapter::query()->create([
            'section_id' => 25,
            'title' => 'Fonctions de Base',
            'description' => 'SOMME, MOYENNE, SI, RECHERCHEV et autres fonctions essentielles.',
            'content' => 'Les fonctions sont le cœur d\'Excel. Vous apprendrez les fonctions logiques (SI, ET, OU), les fonctions de recherche (RECHERCHEV, INDEX, EQUIV), les fonctions texte (GAUCHE, DROITE, STXT, CONCATENER), et les fonctions date (AUJOURDHUI, DATEDIF).',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 25,
            'title' => 'Fonctions Avancées',
            'description' => 'SOMME.SI, NB.SI, INDEX/EQUIV, et formules matricielles.',
            'content' => 'Passez au niveau supérieur avec les fonctions conditionnelles (SOMME.SI, NB.SI, MOYENNE.SI), la combinaison INDEX/EQUIV (alternative plus puissante à RECHERCHEV), les formules matricielles, et les nouvelles fonctions dynamiques d\'Excel 365.',
            'content_type' => 'text',
            'duration_minutes' => 75,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 7 / Section 26: Tableaux Croisés Dynamiques ===
        Chapter::query()->create([
            'section_id' => 26,
            'title' => 'Création de TCD',
            'description' => 'Analysez vos données avec les tableaux croisés dynamiques.',
            'content' => 'Les TCD sont l\'outil d\'analyse le plus puissant d\'Excel. Vous apprendrez à créer un TCD, à organiser les champs (lignes, colonnes, valeurs, filtres), à regrouper les données, et à ajouter des segments et des chronologies pour filtrer interactivement.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 26,
            'title' => 'Graphiques et Rapports',
            'description' => 'Visualisez vos analyses avec des graphiques percutants.',
            'content' => 'Les graphiques croisés dynamiques complètent les TCD. Vous créerez des tableaux de bord interactifs, des graphiques sparkline, des slicers, et des rapports professionnels prêts à être présentés à votre direction.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 8 / Section 27: Introduction au Design Thinking ===
        Chapter::query()->create([
            'section_id' => 27,
            'title' => 'Qu\'est-ce que le Design Thinking ?',
            'description' => 'Origines, principes et processus.',
            'content' => 'Le Design Thinking est une approche centrée sur l\'humain pour résoudre des problèmes complexes. Vous découvrirez l\'histoire de cette méthode développée par IDEO et Stanford, les 5 étapes du processus, et pourquoi elle est adoptée par les plus grandes entreprises innovantes.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 27,
            'title' => 'Les Esprits du Design Thinking',
            'description' => 'Adoptez la mentalité du designer.',
            'content' => 'Le Design Thinking repose sur des mindsets spécifiques : empathie, collaboration, itération, optimisme, et tolérance à l\'échec. Vous apprendrez à cultiver ces attitudes et à constituer une équipe pluridisciplinaire pour maximiser la créativité.',
            'content_type' => 'text',
            'duration_minutes' => 30,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 8 / Section 28: Phase d\'Empathie et Définition ===
        Chapter::query()->create([
            'section_id' => 28,
            'title' => 'Recherche Utilisateur',
            'description' => 'Techniques d\'enquête pour comprendre vos utilisateurs.',
            'content' => 'La phase d\'empathie vise à comprendre profondément les utilisateurs. Vous maîtriserez les techniques de recherche qualitative : entretiens individuels, observations, focus groups, et shadowing. Vous créerez des cartes d\'empathie et des personas pour synthétiser vos découvertes.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 28,
            'title' => 'Définir le Problème',
            'description' => 'Formulez un point de vue clair et actionnable.',
            'content' => 'La définition du problème est cruciale. Vous apprendrez à formuler un Point of View (POV) : "Comment pourrions-nous... ?". Vous utiliserez les outils du design thinking comme le sarcelle, la matrice 2x2, et le diagramme d\'affinité pour structurer vos insights.',
            'content_type' => 'text',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 8 / Section 29: Idéation et Prototypage ===
        Chapter::query()->create([
            'section_id' => 29,
            'title' => 'Techniques d\'Idéation',
            'description' => 'Générez un maximum d\'idées créatives.',
            'content' => 'L\'idéation est la phase de génération d\'idées. Vous pratiquerez le brainstorming, le brainwriting, le mind mapping, la technique SCAMPER, et l\'inversion des hypothèses. L\'objectif : produire un grand volume d\'idées sans s\'autocensurer.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 29,
            'title' => 'Prototypage Rapide',
            'description' => 'Concrétisez vos idées avec des prototypes basse fidélité.',
            'content' => 'Le prototypage permet de matérialiser une idée rapidement et à moindre coût. Vous apprendrez à créer des prototypes papier, des maquettes, des storyboards, et des wireframes. L\'objectif : échouer rapidement pour apprendre et itérer.',
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 8 / Section 30: Tests et Implémentation ===
        Chapter::query()->create([
            'section_id' => 30,
            'title' => 'Tests Utilisateurs',
            'description' => 'Testez vos prototypes avec de vrais utilisateurs.',
            'content' => 'Les tests utilisateurs sont le cœur du Design Thinking. Vous organiserez des sessions de test, recueillerez des feedbacks, identifierez les points de friction, et itérerez sur votre prototype. Des techniques comme le A/B testing et les tests d\'utilisabilité.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 30,
            'title' => 'Passer à l\'Implémentation',
            'description' => 'De l\'idée au produit concret.',
            'content' => 'La dernière étape consiste à transformer votre prototype en solution réelle. Vous apprendrez à créer une feuille de route produit, à prioriser les fonctionnalités, à constituer une équipe de développement, et à mesurer l\'impact de votre solution avec des KPI.',
            'content_type' => 'text',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 9 / Section 31: Fondamentaux de la Communication ===
        Chapter::query()->create([
            'section_id' => 31,
            'title' => 'Les Piliers de la Communication',
            'description' => 'Comprendre le processus de communication.',
            'content' => 'La communication est un processus complexe. Ce chapitre décrypte le schéma de communication (émetteur, récepteur, message, canal, feedback), les filtres et les biais qui perturbent le message, et les principes de la communication non-violente (CNV).',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 31,
            'title' => 'Communication Écrite et Digitale',
            'description' => 'Rédigez des messages clairs et impactants.',
            'content' => 'À l\'ère du digital, la communication écrite est omniprésente. Vous apprendrez à rédiger des emails professionnels, des messages sur Slack/Teams, des rapports concis, et à adapter votre ton selon votre interlocuteur. La règle d\'or : clarté et concision.',
            'content_type' => 'text',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 9 / Section 32: Prise de Parole en Public ===
        Chapter::query()->create([
            'section_id' => 32,
            'title' => 'Préparer son Intervention',
            'description' => 'Structurez et préparez votre discours.',
            'content' => 'Une bonne préparation est la clé d\'une intervention réussie. Vous apprendrez à définir votre objectif, à analyser votre auditoire, à structurer votre discours (introduction, développement, conclusion), et à préparer des supports visuels percutants avec la méthode Pecha Kucha.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 32,
            'title' => 'Gestion du Trac et du Stress',
            'description' => 'Techniques pour rester serein face à l\'auditoire.',
            'content' => 'Le trac est naturel, mais il peut être maîtrisé. Vous découvrirez des techniques de respiration, de visualisation positive, d\'ancrage, et de préparation mentale. Des exercices pratiques pour transformer votre stress en énergie positive.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 9 / Section 33: Communication Non Verbale ===
        Chapter::query()->create([
            'section_id' => 33,
            'title' => 'Le Langage Corporel',
            'description' => 'Posture, gestes et expressions faciales.',
            'content' => 'La communication non verbale représente 70% de notre message. Vous apprendrez à maîtriser votre posture, à utiliser des gestes ouverts et confiants, à contrôler vos expressions faciales, et à lire le langage corporel de votre interlocuteur.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 33,
            'title' => 'Le Paralangage',
            'description' => 'Voix, rythme, intonation et silences.',
            'content' => 'Le paralangage englobe tout ce qui accompagne la parole : le ton, le débit, le volume, les pauses, et les silences. Vous travaillerez votre voix avec des exercices de diction, de respiration, et de modulation pour captiver votre auditoire.',
            'content_type' => 'text',
            'duration_minutes' => 45,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 10 / Section 34: Introduction à l\'Agilité ===
        Chapter::query()->create([
            'section_id' => 34,
            'title' => 'Le Manifeste Agile',
            'description' => 'Les valeurs et principes du développement agile.',
            'content' => 'Le manifeste agile a révolutionné la gestion de projet. Vous découvrirez les 4 valeurs fondamentales (individus et interactions, logiciel fonctionnel, collaboration client, adaptation au changement) et les 12 principes qui guident les méthodes agiles comme Scrum et Kanban.',
            'content_type' => 'video',
            'duration_minutes' => 45,
            'order_position' => 1,
            'is_free' => true,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 34,
            'title' => 'Agile vs Traditionnel',
            'description' => 'Comparaison des approches prédictives et adaptatives.',
            'content' => 'Quand utiliser l\'agile plutôt que le cycle en V ou le waterfall ? Ce chapitre compare les approches, explique les critères de choix, et présente les frameworks agiles les plus populaires : Scrum, Kanban, XP, et SAFe pour les grandes organisations.',
            'content_type' => 'text',
            'duration_minutes' => 60,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 10 / Section 35: Scrum Framework ===
        Chapter::query()->create([
            'section_id' => 35,
            'title' => 'Les Rôles Scrum',
            'description' => 'Scrum Master, Product Owner et Developers.',
            'content' => 'Scrum définit trois rôles avec des responsabilités claires. Le Product Owner maximise la valeur du produit, le Scrum Master facilite le processus, et les Developers s\'auto-organisent pour livrer l\'incrément. Vous comprendrez les interactions et les responsabilités de chaque rôle.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 35,
            'title' => 'Les Événements Scrum',
            'description' => 'Sprint, Daily Scrum, Sprint Review et Rétrospective.',
            'content' => 'Les événements Scrum créent du rythme et de la transparence. Vous détaillerez chaque événement : le Sprint Planning (quoi et comment), le Daily Scrum (synchronisation), la Sprint Review (inspection et adaptation du produit), et la Rétrospective (amélioration continue).',
            'content_type' => 'video',
            'duration_minutes' => 75,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);

        // === Formation 10 / Section 36: Cérémonies et Artefacts Scrum ===
        Chapter::query()->create([
            'section_id' => 36,
            'title' => 'Les Artefacts Scrum',
            'description' => 'Product Backlog, Sprint Backlog et Incrément.',
            'content' => 'Les artefacts Scrum sont des engagements qui garantissent la transparence. Le Product Backlog est la liste ordonnée des fonctionnalités, le Sprint Backlog est le plan pour le sprint, et l\'Incrément est le résultat livrable. Vous apprendrez à les gérer efficacement.',
            'content_type' => 'video',
            'duration_minutes' => 60,
            'order_position' => 1,
            'is_free' => false,
            'is_active' => true,
        ]);

        Chapter::query()->create([
            'section_id' => 36,
            'title' => 'Préparation à la Certification Scrum Master',
            'description' => 'Passez la certification PSM I avec succès.',
            'content' => 'Ce chapitre vous prépare à l\'examen Professional Scrum Master I. Vous trouverez des quiz, des études de cas, des simulations d\'examen, et des conseils pour réussir du premier coup. La certification Scrum Master est un atout majeur sur le marché du travail.',
            'content_type' => 'text',
            'duration_minutes' => 90,
            'order_position' => 2,
            'is_free' => false,
            'is_active' => true,
        ]);
    }
}
