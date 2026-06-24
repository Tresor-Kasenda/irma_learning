<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

final class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // === Exam 1: Développement Web Full Stack (10 questions) ===
        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Quel langage est utilisé pour structurer le contenu d\'une page web ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 1,
            'explanation' => 'HTML (HyperText Markup Language) est le langage de balisage standard pour créer des pages web.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Qu\'est-ce que le Flexbox en CSS ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 2,
            'explanation' => 'Flexbox est un module de mise en page CSS qui permet d\'aligner et de distribuer efficacement les éléments dans un conteneur.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'En PHP 8, comment déclarez-vous une énumération ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 3,
            'explanation' => 'Depuis PHP 8.1, on utilise le mot-clé "enum" suivi du nom et éventuellement du type.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Quel est le rôle d\'un middleware dans Laravel ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 4,
            'explanation' => 'Les middlewares filtrent les requêtes HTTP entrantes. Ils sont idéaux pour l\'authentification, la journalisation, etc.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Quels sont les hooks React fondamentaux ? (Plusieurs réponses possibles)',
            'question_type' => 'multiple_choice',
            'points' => 15,
            'order_position' => 5,
            'explanation' => 'useState, useEffect, useContext et useReducer sont les hooks fondamentaux de React.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Tailwind CSS est un framework CSS utility-first.',
            'question_type' => 'true_false',
            'points' => 5,
            'order_position' => 6,
            'explanation' => 'Vrai. Tailwind CSS adopte une approche utility-first contrairement à Bootstrap qui est basé sur des composants.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Expliquez la différence entre une requête GET et une requête POST en HTTP.',
            'question_type' => 'essay',
            'points' => 20,
            'order_position' => 7,
            'explanation' => 'GET récupère des données (visible dans l\'URL, limité en taille), POST envoie des données (dans le corps, pas de limite de taille).',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Quelle commande Artisan permet de créer une migration ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 8,
            'explanation' => 'php artisan make:migration est la commande pour créer une nouvelle migration.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Citez deux avantages de l\'utilisation de Vite comme bundler.',
            'question_type' => 'text',
            'points' => 10,
            'order_position' => 9,
            'explanation' => 'Vite offre un rechargement à chaud ultra-rapide (HMR) et des builds optimisés avec Rollup.',
            'is_required' => false,
        ]);

        Question::query()->create([
            'exam_id' => 1,
            'question_text' => 'Quel service cloud est utilisé pour déployer une application Laravel avec Forge ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 10,
            'explanation' => 'Laravel Forge supporte AWS, DigitalOcean, Linode, Vultr, et Hetzner comme fournisseurs cloud.',
            'is_required' => true,
        ]);

        // === Exam 2: Marketing Digital (8 questions) ===
        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Que signifie SEO ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 1,
            'explanation' => 'SEO signifie Search Engine Optimization (Optimisation pour les moteurs de recherche).',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Quel est l\'objectif principal du marketing de contenu ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 2,
            'explanation' => 'Le marketing de contenu vise à attirer et engager une audience cible en créant du contenu pertinent et de valeur.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Quels sont les facteurs de ranking Google les plus importants ? (Plusieurs réponses)',
            'question_type' => 'multiple_choice',
            'points' => 15,
            'order_position' => 3,
            'explanation' => 'La qualité du contenu, les backlinks, l\'expérience utilisateur et la vitesse de chargement sont des facteurs clés.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Le CPC (Coût Par Clic) est une métrique utilisée uniquement pour le SEO.',
            'question_type' => 'true_false',
            'points' => 5,
            'order_position' => 4,
            'explanation' => 'Faux. Le CPC est une métrique utilisée pour les campagnes publicitaires payantes (SEA), pas pour le SEO.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Décrivez les étapes pour créer une campagne Google Ads performante.',
            'question_type' => 'essay',
            'points' => 25,
            'order_position' => 5,
            'explanation' => 'Définir les objectifs, choisir les mots-clés, créer des annonces, définir le budget, cibler l\'audience, et analyser les performances.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Qu\'est-ce qu\'un taux d\'engagement sur les réseaux sociaux ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 6,
            'explanation' => 'Le taux d\'engagement mesure les interactions (likes, commentaires, partages) rapportées au nombre d\'abonnés ou d\'impressions.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Citez trois outils de gestion des réseaux sociaux.',
            'question_type' => 'text',
            'points' => 10,
            'order_position' => 7,
            'explanation' => 'Hootsuite, Buffer, Later, Sprout Social, et HubSpot sont des outils populaires.',
            'is_required' => false,
        ]);

        Question::query()->create([
            'exam_id' => 2,
            'question_text' => 'Qu\'est-ce que le retargeting ?',
            'question_type' => 'single_choice',
            'points' => 15,
            'order_position' => 8,
            'explanation' => 'Le retargeting consiste à cibler les utilisateurs qui ont déjà visité votre site avec des publicités personnalisées.',
            'is_required' => true,
        ]);

        // === Exam 3: Data Science (10 questions) ===
        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Quelle bibliothèque Python est utilisée pour le calcul numérique ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 1,
            'explanation' => 'NumPy est la bibliothèque fondamentale pour le calcul numérique en Python.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'À quoi sert la méthode groupby() dans Pandas ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 2,
            'explanation' => 'groupby() permet de regrouper les données selon une ou plusieurs colonnes pour appliquer des fonctions d\'agrégation.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Quels sont les types d\'apprentissage automatique ? (Plusieurs réponses)',
            'question_type' => 'multiple_choice',
            'points' => 15,
            'order_position' => 3,
            'explanation' => 'Les trois principaux types sont l\'apprentissage supervisé, non supervisé et par renforcement.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Dans un arbre de décision, plus la profondeur est grande, meilleur est le modèle.',
            'question_type' => 'true_false',
            'points' => 5,
            'order_position' => 4,
            'explanation' => 'Faux. Une trop grande profondeur entraîne du surapprentissage (overfitting). Il faut trouver le bon équilibre.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Expliquez la différence entre le surapprentissage (overfitting) et le sous-apprentissage (underfitting).',
            'question_type' => 'essay',
            'points' => 20,
            'order_position' => 5,
            'explanation' => 'Overfitting : le modèle apprend trop bien les données d\'entraînement mais généralise mal. Underfitting : le modèle est trop simple et ne capture pas les patterns.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Qu\'est-ce que la validation croisée (cross-validation) ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 6,
            'explanation' => 'La validation croisée divise les données en plusieurs plis pour évaluer la performance du modèle de manière robuste.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Quelle métrique est appropriée pour évaluer un modèle de classification binaire déséquilibré ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 7,
            'explanation' => 'Le F1-score est la meilleure métrique pour les données déséquilibrées car il combine précision et rappel.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Citez deux algorithmes de clustering.',
            'question_type' => 'text',
            'points' => 10,
            'order_position' => 8,
            'explanation' => 'K-Means, DBSCAN, Clustering hiérarchique, et Gaussian Mixture Models sont des algorithmes de clustering courants.',
            'is_required' => false,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Quelle est la différence entre une variable catégorielle et une variable continue ?',
            'question_type' => 'single_choice',
            'points' => 10,
            'order_position' => 9,
            'explanation' => 'Une variable catégorielle prend des valeurs discrètes (ex: couleur), une variable continue peut prendre n\'importe quelle valeur dans un intervalle.',
            'is_required' => true,
        ]);

        Question::query()->create([
            'exam_id' => 3,
            'question_text' => 'Qu\'est-ce que le théorème de Bayes et à quoi sert-il en ML ?',
            'question_type' => 'essay',
            'points' => 20,
            'order_position' => 10,
            'explanation' => 'Le théorème de Bayes décrit la probabilité conditionnelle. Il est utilisé dans les classifieurs naïfs de Bayes pour la classification.',
            'is_required' => true,
        ]);
    }
}
