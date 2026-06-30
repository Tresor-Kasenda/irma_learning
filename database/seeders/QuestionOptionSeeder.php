<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

final class QuestionOptionSeeder extends Seeder
{
    public function run(): void
    {
        // === Q1: Quel langage pour structurer une page web ? ===
        QuestionOption::query()->create(['question_id' => 1, 'option_text' => 'HTML', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 1, 'option_text' => 'CSS', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 1, 'option_text' => 'JavaScript', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 1, 'option_text' => 'PHP', 'is_correct' => false, 'order_position' => 4]);

        // === Q2: Qu'est-ce que Flexbox ? ===
        QuestionOption::query()->create(['question_id' => 2, 'option_text' => 'Un module de mise en page CSS', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 2, 'option_text' => 'Une bibliothèque JavaScript', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 2, 'option_text' => 'Un framework PHP', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 2, 'option_text' => 'Un outil de débogage', 'is_correct' => false, 'order_position' => 4]);

        // === Q3: Déclarer une énumération en PHP 8 ===
        QuestionOption::query()->create(['question_id' => 3, 'option_text' => 'enum Status { case Active; }', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 3, 'option_text' => 'class Status extends Enum { }', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 3, 'option_text' => 'define(\'STATUS_ACTIVE\', \'active\')', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 3, 'option_text' => 'const STATUS_ACTIVE = \'active\';', 'is_correct' => false, 'order_position' => 4]);

        // === Q4: Rôle d'un middleware Laravel ===
        QuestionOption::query()->create(['question_id' => 4, 'option_text' => 'Filtrer les requêtes HTTP entrantes', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 4, 'option_text' => 'Créer des tables en base de données', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 4, 'option_text' => 'Compiler les assets CSS', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 4, 'option_text' => 'Générer des vues Blade', 'is_correct' => false, 'order_position' => 4]);

        // === Q5: Hooks React fondamentaux (multiple) ===
        QuestionOption::query()->create(['question_id' => 5, 'option_text' => 'useState', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 5, 'option_text' => 'useEffect', 'is_correct' => true, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 5, 'option_text' => 'useContext', 'is_correct' => true, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 5, 'option_text' => 'useFetch', 'is_correct' => false, 'order_position' => 4]);
        QuestionOption::query()->create(['question_id' => 5, 'option_text' => 'useReducer', 'is_correct' => true, 'order_position' => 5]);
        QuestionOption::query()->create(['question_id' => 5, 'option_text' => 'useRoute', 'is_correct' => false, 'order_position' => 6]);

        // === Q6: Tailwind CSS utility-first (V/F) ===
        QuestionOption::query()->create(['question_id' => 6, 'option_text' => 'Vrai', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 6, 'option_text' => 'Faux', 'is_correct' => false, 'order_position' => 2]);

        // === Q7: GET vs POST (V/F) ===
        QuestionOption::query()->create(['question_id' => 7, 'option_text' => 'Vrai', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 7, 'option_text' => 'Faux', 'is_correct' => false, 'order_position' => 2]);

        // === Q8: Commande Artisan migration ===
        QuestionOption::query()->create(['question_id' => 8, 'option_text' => 'php artisan make:migration', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 8, 'option_text' => 'php artisan create:migration', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 8, 'option_text' => 'php artisan generate:migration', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 8, 'option_text' => 'php artisan new:migration', 'is_correct' => false, 'order_position' => 4]);

        // === Q9: Vite HMR (V/F) ===
        QuestionOption::query()->create(['question_id' => 9, 'option_text' => 'Vrai', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 9, 'option_text' => 'Faux', 'is_correct' => false, 'order_position' => 2]);

        // === Q10: Cloud pour Forge ===
        QuestionOption::query()->create(['question_id' => 10, 'option_text' => 'DigitalOcean', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 10, 'option_text' => 'Google Drive', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 10, 'option_text' => 'Dropbox', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 10, 'option_text' => 'iCloud', 'is_correct' => false, 'order_position' => 4]);

        // === Q11: Que signifie SEO ? ===
        QuestionOption::query()->create(['question_id' => 11, 'option_text' => 'Search Engine Optimization', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 11, 'option_text' => 'Social Engagement Online', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 11, 'option_text' => 'Simple Email Option', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 11, 'option_text' => 'Site Efficiency Optimizer', 'is_correct' => false, 'order_position' => 4]);

        // === Q12: Objectif marketing de contenu ===
        QuestionOption::query()->create(['question_id' => 12, 'option_text' => 'Attirer et engager une audience cible', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 12, 'option_text' => 'Vendre directement des produits', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 12, 'option_text' => 'Réduire les coûts publicitaires', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 12, 'option_text' => 'Augmenter le nombre d\'employés', 'is_correct' => false, 'order_position' => 4]);

        // === Q13: Facteurs ranking Google (multiple) ===
        QuestionOption::query()->create(['question_id' => 13, 'option_text' => 'Qualité du contenu', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 13, 'option_text' => 'Backlinks de qualité', 'is_correct' => true, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 13, 'option_text' => 'Nombre de pages du site', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 13, 'option_text' => 'Vitesse de chargement', 'is_correct' => true, 'order_position' => 4]);
        QuestionOption::query()->create(['question_id' => 13, 'option_text' => 'Couleur du logo', 'is_correct' => false, 'order_position' => 5]);

        // === Q14: CPC pour le SEO (V/F) ===
        QuestionOption::query()->create(['question_id' => 14, 'option_text' => 'Vrai', 'is_correct' => false, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 14, 'option_text' => 'Faux', 'is_correct' => true, 'order_position' => 2]);

        // === Q15: Question essai - pas d'options ===

        // === Q16: Taux d'engagement ===
        QuestionOption::query()->create(['question_id' => 16, 'option_text' => 'Le ratio interactions / impressions ou abonnés', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 16, 'option_text' => 'Le nombre total de likes', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 16, 'option_text' => 'Le nombre de nouveaux abonnés par jour', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 16, 'option_text' => 'Le temps passé sur le site', 'is_correct' => false, 'order_position' => 4]);

        // === Q17: Question text - pas d'options ===

        // === Q18: Retargeting ===
        QuestionOption::query()->create(['question_id' => 18, 'option_text' => 'Cibler les visiteurs déjà venus sur votre site', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 18, 'option_text' => 'Cibler une nouvelle audience similaire', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 18, 'option_text' => 'Optimiser le budget publicitaire', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 18, 'option_text' => 'Analyser les concurrents', 'is_correct' => false, 'order_position' => 4]);

        // === Q19: Bibliothèque Python calcul numérique ===
        QuestionOption::query()->create(['question_id' => 19, 'option_text' => 'NumPy', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 19, 'option_text' => 'Pandas', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 19, 'option_text' => 'Matplotlib', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 19, 'option_text' => 'Scikit-learn', 'is_correct' => false, 'order_position' => 4]);

        // === Q20: groupby() dans Pandas ===
        QuestionOption::query()->create(['question_id' => 20, 'option_text' => 'Regrouper les données pour appliquer des agrégations', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 20, 'option_text' => 'Supprimer les doublons', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 20, 'option_text' => 'Trier les données par ordre alphabétique', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 20, 'option_text' => 'Fusionner deux DataFrames', 'is_correct' => false, 'order_position' => 4]);

        // === Q21: Types d'apprentissage automatique (multiple) ===
        QuestionOption::query()->create(['question_id' => 21, 'option_text' => 'Apprentissage supervisé', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 21, 'option_text' => 'Apprentissage non supervisé', 'is_correct' => true, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 21, 'option_text' => 'Apprentissage par renforcement', 'is_correct' => true, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 21, 'option_text' => 'Apprentissage supervisé automatique', 'is_correct' => false, 'order_position' => 4]);

        // === Q22: Profondeur arbre de décision (V/F) ===
        QuestionOption::query()->create(['question_id' => 22, 'option_text' => 'Vrai', 'is_correct' => false, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 22, 'option_text' => 'Faux', 'is_correct' => true, 'order_position' => 2]);

        // === Q23: Question essai - pas d'options ===

        // === Q24: Validation croisée ===
        QuestionOption::query()->create(['question_id' => 24, 'option_text' => 'Diviser les données en plis pour évaluer le modèle', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 24, 'option_text' => 'Valider que les données sont correctes', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 24, 'option_text' => 'Croiser les résultats avec une autre étude', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 24, 'option_text' => 'Vérifier les types de données', 'is_correct' => false, 'order_position' => 4]);

        // === Q25: Métrique pour données déséquilibrées ===
        QuestionOption::query()->create(['question_id' => 25, 'option_text' => 'F1-score', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 25, 'option_text' => 'Accuracy', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 25, 'option_text' => 'Rappel uniquement', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 25, 'option_text' => 'Précision uniquement', 'is_correct' => false, 'order_position' => 4]);

        // === Q26: Question text - pas d'options ===

        // === Q27: Variable catégorielle vs continue ===
        QuestionOption::query()->create(['question_id' => 27, 'option_text' => 'Catégorielle : valeurs discrètes / Continue : valeurs dans un intervalle', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::query()->create(['question_id' => 27, 'option_text' => 'Catégorielle : nombres / Continue : textes', 'is_correct' => false, 'order_position' => 2]);
        QuestionOption::query()->create(['question_id' => 27, 'option_text' => 'Catégorielle : continues dans le temps / Continue : fixes', 'is_correct' => false, 'order_position' => 3]);
        QuestionOption::query()->create(['question_id' => 27, 'option_text' => 'Il n\'y a pas de différence', 'is_correct' => false, 'order_position' => 4]);

        // === Q28: Question essai - pas d'options ===
    }
}
