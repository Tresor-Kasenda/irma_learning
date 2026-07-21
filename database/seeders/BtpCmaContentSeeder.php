<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ChapterTypeEnum;
use App\Enums\FormationLevelEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Formation;
use App\Models\Question;
use App\Models\Section;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

final class BtpCmaContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->creerFormationMaconnerie();
        $this->creerFormationElectricite();
        $this->creerFormationPlomberie();
        $this->creerFormationMenuiserie();
        $this->creerFormationPeinture();
        $this->creerFormationSoudure();
        $this->creerFormationCarrelage();
        $this->creerFormationGestionChantier();
    }

    private function section(Formation $formation, int $position, string $title, int $duration): Section
    {
        return $formation->sections()->updateOrCreate(
            ['title' => $title],
            [
                'description' => 'Module ' . $position . ' de la formation.',
                'order_position' => $position,
                'duration' => $duration,
                'is_active' => true,
            ],
        );
    }

    private function chapitre(Section $section, string $title, string $description, string $content, int $duration, int $position, bool $isFree = false): Chapter
    {
        return $section->chapters()->updateOrCreate(
            ['title' => $title],
            [
                'description' => $description,
                'content_type' => ChapterTypeEnum::TEXT,
                'content' => $content,
                'duration_minutes' => $duration,
                'order_position' => $position,
                'is_free' => $isFree,
                'is_active' => true,
            ],
        );
    }

    private function exam(Formation|Section $parent, string $title, array $questions, int $passingScore = 70, int $maxAttempts = 3): void
    {
        $exam = $parent->exam()->updateOrCreate([],
            [
                'title' => $title,
                'description' => 'Évaluation obligatoire pour valider cette étape.',
                'instructions' => 'Répondez à toutes les questions. L\'ordre change à chaque nouvelle tentative.',
                'duration_minutes' => 20,
                'passing_score' => $passingScore,
                'max_attempts' => $maxAttempts,
                'randomize_questions' => true,
                'show_results_immediately' => true,
                'is_active' => true,
                'available_from' => now()->subDay(),
                'available_until' => now()->addYears(2),
            ],
        );

        $exam->questions()->each(fn (Question $q) => $q->options()->delete());
        $exam->questions()->delete();

        foreach ($questions as $qi => [$text, $type, $options, $correctIndexes]) {
            $question = $exam->questions()->create([
                'question_text' => $text,
                'question_type' => $type,
                'points' => 5,
                'order_position' => $qi + 1,
                'explanation' => 'Consultez le support de la section pour revoir ce point.',
                'is_required' => true,
            ]);

            foreach ($options as $oi => $option) {
                $question->options()->create([
                    'option_text' => $option,
                    'is_correct' => in_array($oi, $correctIndexes, true),
                    'order_position' => $oi + 1,
                ]);
            }
        }
    }

    private function creerFormationMaconnerie(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Maçonnerie Générale & Techniques de Construction'],
            [
                'short_description' => 'Maîtrisez les techniques fondamentales de la maçonnerie : fondations, élévation des murs, planchers et dallages.',
                'description' => 'Cette formation complète vous forme aux gestes et techniques essentiels du métier de maçon. Vous apprendrez à réaliser des fondations solides, à élever des murs en parpaings, briques et pierres, à couler des dallages et à poser des planchers.',
                'price' => null,
                'duration_hours' => 80,
                'difficulty_level' => FormationLevelEnum::BEGINNER,
                'is_active' => false,
                'is_featured' => true,
                'is_certifying' => true,
                'tags' => ['maçonnerie', 'fondations', 'construction', 'mur', 'dallage', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Fondations et Terrassement', 180);
        $s2 = $this->section($formation, 2, 'Élévation des Murs', 240);
        $s3 = $this->section($formation, 3, 'Planchers et Dallages', 180);

        $this->chapitre($s1, 'Types de sols et études géotechniques',
            'Comprendre les sols avant de construire.',
            "# Types de sols\n\n## Classification\n- **Sols rocheux** : granite, calcaire, schiste — excellente portance.\n- **Sols meubles** : argile, sable, limon, gravier — portance variable.\n- **Sols organiques** : tourbe, vase — à éviter.\n\n## Étude géotechnique\nNorme NF P 94-500 :\n1. **G1** : reconnaissance sommaire.\n2. **G2** : investigation détaillée (sondages, essais).\n3. **G3** : suivi géotechnique d'exécution.\n\n## Essais\n| Essai | Mesure |\n|---|---|\n| Pénétromètre | Résistance de pointe |\n| Pressiomètre | Module de déformation |",
            35, 1, true);

        $this->chapitre($s1, 'Réalisation des fondations',
            'Semelles, radier et pieux.',
            "# Réalisation des fondations\n\n## Fondations superficielles\n- **Semelle filante** : sous murs porteurs, largeur ≥ 40 cm.\n- **Semelle isolée** : sous poteaux.\n- **Radier** : dalle épaisse, idéal pour sols hétérogènes.\n\n## Fondations profondes\n- **Pieux battus** : préfabriqués, enfoncés par battage.\n- **Pieux forés** : réalisés dans le sol, Ø 40–120 cm.\n\n## Étapes\n1. Implantation et piquetage.\n2. Fouille en tranchée.\n3. Béton de propreté (5 cm).\n4. Ferraillage (enrobage ≥ 5 cm).\n5. Coulage béton C25/30.\n6. Cure et décoffrage (7 jours min).",
            45, 2);

        $this->chapitre($s1, 'Drainage et étanchéité',
            'Protéger les fondations contre l\'humidité.',
            "# Drainage et étanchéité\n\n## Drainage périphérique\n- **Drain vertical** : membrane rugueuse sur mur enterré.\n- **Drain horizontal** : tube DN 100, enveloppé de géotextile.\n- **Couche drainante** : gravier 20/40 mm.\n\n## Étanchéité\n- Enduit bitumineux à froid ou à chaud.\n- Membrane PVC/EPDM pour sous-sols habitables.\n- Cuvelage par mortiers hydrofuges.",
            30, 3);

        $this->chapitre($s2, 'Techniques de maçonnerie',
            'Parpaings, briques et pierres.',
            "# Techniques de maçonnerie\n\n## Parpaings\n- Dimensions : 20×20×50 cm.\n- Joints verticaux décalés.\n- Épaisseur joints : 1–1,5 cm.\n\n## Briques\n- **Pleine** : pour murs porteurs.\n- **Creuse** : pour cloisons.\n- **Monomur** : isolation intégrée.\n\n## Pierres\n- **Moellon** : brute, joints larges.\n- **Pierre de taille** : régulière, joints minces.\n- **Pierre sèche** : sans mortier.\n\n## Règles\n- Mouiller les supports.\n- Respecter l'équerrage (niveau à bulle).\n- Araser chaque rangée.",
            50, 1);

        $this->chapitre($s2, 'Chaînages et ferraillage',
            'Assurer la tenue mécanique de la structure.',
            "# Chaînages et ferraillage\n\n## Types\n- **Horizontal** : en tête de mur (ceinture).\n- **Vertical** : aux extrémités des murs.\n- **En redans** : murs de soutènement.\n\n## Ferraillage minimal\n| Élément | Aciers |\n|---|---|\n| Chaînage horizontal | 4 HA 10 + cadres HA 6 t. 25 cm |\n| Chaînage vertical | 4 HA 12 + cadres HA 6 t. 20 cm |\n| Linteau | 2 HA 8 en partie inférieure |\n\n## Dispositions\n- Enrobage : ≥ 2,5 cm intérieur, ≥ 5 cm extérieur.\n- Recouvrement : 40–50 × diamètre.\n- Norme : DTU 23.1 et Eurocode 6.",
            40, 2);

        $this->chapitre($s2, 'Ouvertures et linteaux',
            'Créer des trémies dans les murs porteurs.',
            "# Ouvertures et linteaux\n\n## Types\n- Béton armé préfabriqué (le plus répandu).\n- Métallique (UPE, HEA) pour grandes portées.\n- Bois pour ossature bois.\n\n## Pose\n1. Étairement provisoire (tous les 1,5 m).\n2. Saignée de chaque côté.\n3. Pose sur appuis ≥ 20 cm.\n4. Béton de calage.\n5. Dépose étais après 7 jours.",
            30, 3);

        $this->chapitre($s3, 'Types de planchers',
            'Hourdis, dalle pleine, prédalle.',
            "# Types de planchers\n\n## Plancher à hourdis\n- Poutrelles + entrevous + coulage.\n- Portée jusqu'à 6 m.\n\n## Dalle pleine\n- Épaisseur 15–25 cm.\n- Portée jusqu'à 8 m.\n- Grande rigidité.\n\n## Prédalle\n- Préfabriquée, épaisseur 5–8 cm + couche compression 15–20 cm.\n- Rapide à poser.\n- Portée jusqu'à 12 m.",
            35, 1);

        $this->chapitre($s3, 'Réalisation d\'un dallage',
            'Les étapes clés du dallage sur terre-plein.',
            "# Dallage sur terre-plein\n\n1. Terrassement (30–50 cm).\n2. Couche de forme (grave compactée 20–30 cm).\n3. Film polyane (pare-vapeur).\n4. Isolation thermique (PSE/XPS).\n5. Ferraillage (treillis soudé ST 25C, enrobage ≥ 3 cm).\n6. Coulage béton C25/30 (15–20 cm).\n7. Joint de dilatation (tous les 25–30 m²).\n8. Cure (7 jours minimum).",
            40, 2);

        $this->chapitre($s3, 'Isolation des planchers',
            'Confort thermique et acoustique.',
            "# Isolation des planchers\n\n## Thermique\n- Sous dalle : PSE ou XPS.\n- Chape flottante : isolant + treillis + chape 5–6 cm.\n- Plancher chauffant : tubes PEX noyés dans la chape.\n\n## Acoustique\n- Bruits d'impact : résilients sous chape (laine minérale, caoutchouc).\n- Bruits aériens : masse surfacique élevée.\n- Bandes résilientes en périmètre.\n\n## Réglementation\n- RE 2020 : R ≥ 3 m².K/W pour planchers bas.\n- NRA : L'nT,w ≤ 58 dB.",
            30, 3);

        $this->exam($s1, 'Évaluation — Fondations et Terrassement', [
            ['Quelle étude permet de connaître la nature d\'un sol avant construction ?', QuestionTypeEnum::SINGLE_CHOICE, ['Étude géotechnique', 'Étude de marché', 'Analyse comptable', 'Diagnostic thermique'], [0]],
            ['Quel type de fondation est adapté aux sols hétérogènes ?', QuestionTypeEnum::SINGLE_CHOICE, ['Semelle filante', 'Radier', 'Semelle isolée', 'Chaînage vertical'], [1]],
            ['Le drainage horizontal protège les fondations des remontées capillaires.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Élévation des Murs', [
            ['Quel élément reporte les charges au-dessus d\'une ouverture ?', QuestionTypeEnum::SINGLE_CHOICE, ['Le linteau', 'Le chaînage vertical', 'La semelle', 'L\'étai'], [0]],
            ['Quels sont les types de chaînages obligatoires ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Horizontal', 'Vertical', 'En diagonale', 'En redans'], [0, 1, 3]],
            ['Les briques monomur offrent une isolation thermique intégrée.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s3, 'Évaluation — Planchers et Dallages', [
            ['Quel type de plancher offre la plus grande portée ?', QuestionTypeEnum::SINGLE_CHOICE, ['Hourdis', 'Dalle pleine', 'Prédalle', 'Plancher bois'], [2]],
            ['Quel film est posé sous le dallage pour éviter les remontées d\'humidité ?', QuestionTypeEnum::SINGLE_CHOICE, ['Film polyane', 'Papier kraft', 'Géotextile', 'Feutre bitumeux'], [0]],
            ['R ≥ 3 m².K/W est l\'exigence RE 2020 pour les planchers bas.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($formation, 'Examen final — Maçonnerie Générale', [
            ['Largeur minimale d\'une semelle filante sous mur porteur ?', QuestionTypeEnum::SINGLE_CHOICE, ['20 cm', '40 cm', '60 cm', '80 cm'], [1]],
            ['Quels sont les types de fondations profondes ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Pieux battus', 'Pieux forés', 'Semelle isolée', 'Radier'], [0, 1]],
            ['L\'enrobage des aciers en extérieur doit être ≥ 5 cm.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationElectricite(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Électricité Bâtiment & Courants Faibles'],
            [
                'short_description' => 'Devenez électricien qualifié : installation résidentielle, courants faibles et domotique.',
                'description' => 'Formation complète aux métiers de l\'électricité du bâtiment : fondamentaux, câblage résidentiel et tertiaire, courants faibles (VDI, alarmes) et domotique. Conforme à la norme NF C 15-100.',
                'price' => null,
                'duration_hours' => 75,
                'difficulty_level' => FormationLevelEnum::BEGINNER,
                'is_active' => false,
                'is_featured' => true,
                'is_certifying' => true,
                'tags' => ['électricité', 'courants faibles', 'domotique', 'NF C 15-100', 'installation', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Fondamentaux de l\'Électricité', 150);
        $s2 = $this->section($formation, 2, 'Installation Électrique Résidentielle', 210);
        $s3 = $this->section($formation, 3, 'Courants Faibles et Domotique', 150);

        $this->chapitre($s1, 'Lois de base de l\'électricité',
            'Tension, courant, résistance et puissance.',
            "# Lois de base\n\n## Grandeurs\n- **Tension (U)** : volts (V).\n- **Courant (I)** : ampères (A).\n- **Résistance (R)** : ohms (Ω).\n\n## Lois fondamentales\n- Loi d'Ohm : **U = R × I**\n- Puissance : **P = U × I**\n- Joule : **W = R × I² × t**\n\n## Applications\n| Formule | Exemple |\n|---|---|\n| U = R × I | 230 V = 10 Ω × 23 A |\n| I = P / U | 10 A = 2300 W / 230 V |\n| R = U / I | 23 Ω = 230 V / 10 A |",
            35, 1, true);

        $this->chapitre($s1, 'Schémas et symboles normalisés',
            'Lire et réaliser des schémas d\'installation.',
            "# Schémas électriques\n\n## Types\n- **Unifilaire** : vision simplifiée.\n- **Multifilaire** : tous les conducteurs.\n- **Architectural** : implanté sur le plan.\n\n## Codes couleurs (NF C 15-100)\n- Phase : rouge/marron.\n- Neutre : bleu.\n- Terre : vert-jaune.\n\n## Règles\n- Section des conducteurs adaptée.\n- Repérage des circuits au tableau.",
            30, 2);

        $this->chapitre($s1, 'Protection des circuits',
            'Disjoncteurs, fusibles et différentiels.',
            "# Protection des circuits\n\n## Disjoncteurs\n- **Magnétique** : court-circuit.\n- **Thermique** : surcharge.\n- **Magnéto-thermique** : les deux.\n\n## Interrupteurs différentiels\n- **Type AC** : circuits standard.\n- **Type A** : circuits électroniques.\n- **Type F** : variateurs.\n\n## Fusibles\n- À cartouche ou à couteau.",
            35, 3);

        $this->chapitre($s2, 'Tableau électrique et distribution',
            'Conception et câblage du tableau.',
            "# Tableau électrique\n\n## Éléments\n- Interrupteur général (DG) : 30–60 A.\n- Interrupteurs différentiels : 2–4 par tableau.\n- Disjoncteurs divisionnaires : 1 par circuit.\n- Bornier de terre.\n\n## Circuits (NF C 15-100)\n| Circuit | Section | Protection |\n|---|---|---|\n| Prises | 2,5 mm² | 20 A |\n| Éclairage | 1,5 mm² | 16 A |\n| Cuisinière | 6 mm² | 32 A |\n| Lave-linge | 2,5 mm² | 20 A |\n\n## Règles\n- Repérage par couleur.\n- Longueur max : 100 m (2,5 mm²).",
            45, 1);

        $this->chapitre($s2, 'Prise de terre et mise à la masse',
            'Protection contre les défauts d\'isolement.',
            "# Prise de terre\n\n## Électrodes\n- Boucle à fond de fouille (cuivre nu 25 mm²).\n- Piquet de terre (≥ 2 m).\n\n## Valeurs\n- 100 Ω max (logement individuel).\n- Mesure au telluromètre.\n\n## Mise à la masse\nToutes les masses métalliques accessibles reliées à la terre : boîtiers, huisseries, canalisations, goulottes.",
            35, 2);

        $this->chapitre($s2, 'Éclairage et prises',
            'Pose des points d\'usage courants.',
            "# Éclairage et prises\n\n## Éclairage\n- 1 point pour 4 m de périmètre.\n- Interrupteur à 1,20 m du sol.\n- Va-et-vient pour dégagements > 3 m.\n\n## Prises\n- 3 prises minimum par pièce + 1/4 m².\n- Hauteur : 15–30 cm du sol.\n- 8 prises max par circuit en 2,5 mm².\n- 8 points d'éclairage max par circuit en 1,5 mm².",
            30, 3);

        $this->chapitre($s3, 'Réseaux VDI',
            'Voix, données, images : câblage structuré.',
            "# Réseaux VDI\n\n## Supports\n- Paire torsadée RJ45 (cat. 6a/7).\n- Fibre optique FTTH.\n- Coaxial TV/satellite.\n\n## Normes\n- NF C 15-100 : 1 point communication/pièce.\n- EIA/TIA 568 : câblage structuré.\n\n## Installation\n1. Tirage en gaines séparées des conducteurs électriques.\n2. Rayon courbure ≥ 4 × Ø.\n3. Longueur max : 90 m.\n4. Test de certification.",
            35, 1);

        $this->chapitre($s3, 'Systèmes de sécurité',
            'Alarme, vidéosurveillance et contrôle d\'accès.',
            "# Systèmes de sécurité\n\n## Alarme intrusion\n- Détecteurs d'ouverture (contacts magnétiques).\n- Détecteurs volumétriques (infrarouge).\n- Centrale communicante GSM/IP.\n\n## Vidéosurveillance\n- Caméras IP HD/4K, vision nocturne.\n- Enregistreur NVR (local/cloud).\n- Alimentation PoE.\n\n## Contrôle d'accès\n- Badge RFID, code digital, biométrie.",
            40, 2);

        $this->chapitre($s3, 'Domotique et GTB',
            'Gestion technique du bâtiment.',
            "# Domotique\n\n## Protocoles\n- **KNX** : standard international GTB.\n- **Zigbee/Z-Wave** : maison connectée.\n- **Modbus/BACnet** : bâtiment tertiaire.\n\n## Fonctions\n- Éclairage : variation, scénarios.\n- Chauffage : programmation, zones.\n- Volets : motorisation solaire.\n- Supervision : écran tactile/smartphone.",
            35, 3);

        $this->exam($s1, 'Évaluation — Fondamentaux de l\'Électricité', [
            ['Formule de la loi d\'Ohm ?', QuestionTypeEnum::SINGLE_CHOICE, ['U = R × I', 'P = U × I', 'W = R × I² × t', 'U = P / I'], [0]],
            ['Quel dispositif protège contre les surcharges ?', QuestionTypeEnum::SINGLE_CHOICE, ['Disjoncteur thermique', 'Interrupteur différentiel', 'Parafoudre', 'Contacteur'], [0]],
            ['Le neutre doit être de couleur bleue.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Installation Résidentielle', [
            ['Section d\'un circuit de prises ?', QuestionTypeEnum::SINGLE_CHOICE, ['1,5 mm²', '2,5 mm²', '4 mm²', '6 mm²'], [1]],
            ['Éléments obligatoires d\'un tableau ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Interrupteur différentiel', 'Disjoncteur divisionnaire', 'Parafoudre', 'Bornier de terre'], [0, 1, 3]],
            ['Valeur max de la prise de terre : 100 Ω.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s3, 'Évaluation — Courants Faibles', [
            ['Standard international pour la GTB ?', QuestionTypeEnum::SINGLE_CHOICE, ['KNX', 'Zigbee', 'Modbus', 'Wi-Fi'], [0]],
            ['Équipements d\'alarme intrusion ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Détecteur d\'ouverture', 'Caméra IP', 'Sirène', 'Centrale communicante'], [0, 2, 3]],
            ['VDI peut être dans la même gaine que le 230V.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [1]],
        ]);
        $this->exam($formation, 'Examen final — Électricité Bâtiment', [
            ['Norme des installations électriques en France ?', QuestionTypeEnum::SINGLE_CHOICE, ['NF C 15-100', 'DTU 23.1', 'Eurocode 2', 'NF P 94-500'], [0]],
            ['Types d\'interrupteurs différentiels ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Type AC', 'Type A', 'Type F', 'Type D'], [0, 1, 2]],
            ['Éclairage 1,5 mm² : 8 points max.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationPlomberie(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Plomberie & Installation Sanitaire'],
            [
                'short_description' => 'Réseaux d\'eau, d\'évacuation et pose d\'appareils sanitaires.',
                'description' => 'Formation complète à la plomberie et au sanitaire : réseaux d\'alimentation (cuivre, PER, multicouche), évacuation et assainissement, pose et raccordement des appareils sanitaires. Conforme aux DTU applicables.',
                'price' => null,
                'duration_hours' => 70,
                'difficulty_level' => FormationLevelEnum::BEGINNER,
                'is_active' => false,
                'is_featured' => false,
                'is_certifying' => true,
                'tags' => ['plomberie', 'sanitaire', 'chauffage', 'eau', 'évacuation', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Réseau d\'Alimentation en Eau', 180);
        $s2 = $this->section($formation, 2, 'Évacuation et Assainissement', 150);
        $s3 = $this->section($formation, 3, 'Appareils Sanitaires et Robinetterie', 150);

        $this->chapitre($s1, 'Canalisations et matériaux',
            'Cuivre, PER, multicouche.',
            "# Canalisations\n\n## Cuivre\n- Durable, bactériostatique.\n- Assemblage par soudure (brasure).\n- Ø 12, 14, 16, 18, 22 mm.\n\n## PER\n- Flexible, résiste au gel.\n- Raccords à sertir ou bagues coulissantes.\n- Pour distribution enterrée et plancher chauffant.\n\n## Multicouche\n- Rigidité du cuivre + flexibilité du PER.\n- Sertissage avec pince adaptée.\n\n## Diamètres\n| Usage | Ø |\n|---|---|\n| Alimentation générale | 22–28 mm |\n| Distribution secondaire | 14–18 mm |\n| Alimentation appareil | 12–14 mm |",
            35, 1, true);

        $this->chapitre($s1, 'Dimensionnement et pose',
            'Calcul et règles de pose.',
            "# Dimensionnement\n\n## Débits forfaitaires\n| Appareil | Débit (L/min) |\n|---|---|\n| Lavabo | 10 |\n| Douche | 12 |\n| Baignoire | 15 |\n| WC | 6 |\n\n## Pose\n- Pente ≥ 1 % pour vidange.\n- Colliers : cuivre 12 mm → 1 m, 22 mm → 1,5 m.\n- Isolation eau chaude (≥ 10 mm).",
            35, 2);

        $this->chapitre($s1, 'Production d\'eau chaude',
            'Chauffe-eau et groupes de sécurité.',
            "# Production d'eau chaude\n\n## Types\n- Électrique à accumulation (50–300 L).\n- Thermodynamique (PAC intégrée).\n- Solaire (capteurs + ballon).\n\n## Groupe de sécurité (NF EN 1487)\n- Clapet anti-retour.\n- Soupape à 7 bars.\n- Dispositif de vidange.\n\n## Raccordement\n- Eau froide : groupe de sécurité sur le ballon.\n- Eau chaude : repéré en rouge.",
            30, 3);

        $this->chapitre($s2, 'Évacuation des eaux usées',
            'Conception et pose des collecteurs.',
            "# Évacuation\n\n## Réseaux\n- Eaux usées : lavabos, douches, éviers.\n- Eaux-vannes : WC.\n- Eaux pluviales : toitures.\n\n## Matériaux\n- PVC (Ø 32–110 mm).\n- PP (haute température).\n- Fonte (isolation phonique).\n\n## Pentes\n| Ø | Pente mini |\n|---|---|\n| 32–40 mm | 2 % |\n| 50–75 mm | 1,5 % |\n| 100–110 mm | 1 % |\n\n## Regards de visite\nTous les 15 m maximum, aux changements de direction.",
            35, 1);

        $this->chapitre($s2, 'Ventilation et colonnes de chute',
            'Équilibre du réseau d\'évacuation.',
            "# Ventilation\n\n## Primaire\nProlongement de la colonne de chute à l'air libre.\n\n## Secondaire\nCircuit parallèle connectant les appareils à la ventilation primaire.\n\n## Rôle\n1. Éviter la dépression (anti-siphonnage).\n2. Évacuer les gaz.\n3. Réduire le bruit.\n\n## Colonne de chute\n- Ø mini : 100 mm (WC), 75 mm (autres).\n- Distance max appareil → colonne : 3 m.\n- Pied de colonne à 45°.",
            30, 2);

        $this->chapitre($s3, 'Pose des appareils sanitaires',
            'WC, lavabo, douche, baignoire.',
            "# Appareils sanitaires\n\n## WC\n- Classique ou suspendu (bâti-support).\n- Hauteur cuvette : 40–42 cm.\n\n## Lavabo\n- Hauteur : 82–86 cm.\n- Mitigeur entraxe 15 cm.\n\n## Douche\n- Receveur ou carrelée à l'italienne.\n- Étanchéité obligatoire.\n- Pente ≥ 1,5 %.\n\n## Baignoire\n- Support réglable ou lit de mortier.\n- Vidage avec trop-plein.",
            40, 1);

        $this->chapitre($s3, 'Robinetterie et mitigeurs',
            'Choix, pose et entretien.',
            "# Robinetterie\n\n## Types\n- Tête céramique (1/4 tour).\n- Mitigeur mécanique.\n- Mitigeur thermostatique.\n\n## Normes\n- NF : conformité qualité.\n- ACS : conformité sanitaire eau potable.\n\n## Pose\n1. Tresses flexibles + joints.\n2. Serrage main + 1/4 tour à la pince.\n3. Eau chaude à gauche, froide à droite.\n4. Test en pression.",
            30, 2);

        $this->exam($s1, 'Évaluation — Réseau d\'Alimentation', [
            ['Matériau recommandé pour distribution enterrée ?', QuestionTypeEnum::SINGLE_CHOICE, ['Cuivre', 'PER', 'Multicouche', 'Acier galvanisé'], [1]],
            ['Dispositif obligatoire sur chauffe-eau à accumulation ?', QuestionTypeEnum::SINGLE_CHOICE, ['Groupe de sécurité', 'Vase d\'expansion', 'Circulateur', 'Thermostat'], [0]],
            ['Le cuivre s\'assemble par soudure au chalumeau.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Évacuation', [
            ['Pente minimale pour évacuation 100 mm ?', QuestionTypeEnum::SINGLE_CHOICE, ['0,5 %', '1 %', '1,5 %', '2 %'], [1]],
            ['Rôles de la ventilation primaire ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Éviter la dépression', 'Évacuer les gaz', 'Chauffer l\'eau', 'Réduire le bruit'], [0, 1, 3]],
            ['Eaux pluviales et usées doivent être séparées.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [1]],
        ]);
        $this->exam($s3, 'Évaluation — Appareils Sanitaires', [
            ['Hauteur standard d\'un lavabo ?', QuestionTypeEnum::SINGLE_CHOICE, ['70–74 cm', '82–86 cm', '90–94 cm', '100–104 cm'], [1]],
            ['Le mitigeur thermostatique stabilise la température.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
            ['ACS atteste de la conformité pour l\'eau potable.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($formation, 'Examen final — Plomberie', [
            ['Trois principaux matériaux de canalisation ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Cuivre', 'PER', 'Multicouche', 'Acier noir'], [0, 1, 2]],
            ['Distance max entre regards de visite ?', QuestionTypeEnum::SINGLE_CHOICE, ['10 m', '15 m', '20 m', '25 m'], [1]],
            ['Ventilation primaire = prolongement colonne à l\'air libre.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationMenuiserie(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Menuiserie & Ébénisterie'],
            [
                'short_description' => 'Travail du bois, assemblages, fabrication et pose de menuiseries.',
                'description' => 'Formation complète aux métiers du bois : essences, usinage et assemblage, fabrication de fenêtres, portes, escaliers et meubles sur mesure, finitions (vernis, cire, peinture).',
                'price' => null,
                'duration_hours' => 90,
                'difficulty_level' => FormationLevelEnum::INTERMEDIATE,
                'is_active' => false,
                'is_featured' => true,
                'is_certifying' => true,
                'tags' => ['menuiserie', 'ébénisterie', 'bois', 'assemblage', 'agencement', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Bois et Matériaux Dérivés', 150);
        $s2 = $this->section($formation, 2, 'Techniques de Menuiserie', 180);
        $s3 = $this->section($formation, 3, 'Fabrication et Pose', 210);

        $this->chapitre($s1, 'Essences de bois',
            'Connaître les bois pour mieux les travailler.',
            "# Essences de bois\n\n## Résineux\n- **Pin sylvestre** : charpente, menuiserie courante.\n- **Sapin/Épicéa** : ossature bois.\n- **Douglas** : menuiseries extérieures.\n\n## Feuillus\n- **Chêne** : meubles, parquets.\n- **Hêtre** : meubles, sièges.\n- **Noyer** : ébénisterie fine.\n\n## Exotiques\n- **Teck** : imputrescible, extérieur.\n- **Iroko** : menuiseries extérieures.\n\n## Critères\n| Critère | Résineux | Feuillus | Exotiques |\n|---|---|---|---|\n| Coût | Faible | Moyen | Élevé |\n| Dureté | Faible | Moy/Fort | Très fort |\n| Durabilité | Moyenne | Bonne | Excellente |",
            30, 1, true);

        $this->chapitre($s1, 'Panneaux dérivés du bois',
            'Contreplaqué, MDF, aggloméré, OSB.',
            "# Panneaux dérivés\n\n## Contreplaqué\n- CTBH (intérieur), CTBX (extérieur).\n- Plis de bois croisés collés.\n\n## MDF\n- Fibres comprimées, surface lisse.\n- Sensible à l'humidité.\n\n## Aggloméré\n- Particules collées, économique.\n- Pour meubles d'entrée de gamme.\n\n## OSB\n- Lamelles orientées, structurel.\n- Âmes de portes, murs, toiture.",
            25, 2);

        $this->chapitre($s2, 'Assemblage et quincaillerie',
            'Techniques traditionnelles et modernes.',
            "# Assemblage\n\n## Traditionnels\n- **Tenon-mortaise** : le plus solide.\n- **Queue-d'aronde** : tiroirs.\n- **À mi-bois** : cadres.\n\n## Modernes\n- Tourillons, Lamello.\n- Vis et confirmats (agencement).\n\n## Quincaillerie\n| Type | Usage |\n|---|---|\n| Paumelle | Porte d'armoire |\n| Charnière | Porte cuisine |\n| Glissière | Tiroir |\n| Crémone | Fenêtre |",
            35, 1);

        $this->chapitre($s2, 'Usinage du bois',
            'Sciage, rabotage, perçage, fraisage.',
            "# Usinage\n\n## Sciage\n- Circulaire : coupes rapides.\n- À ruban : coupes courbes.\n- Sauteuse : découpes de forme.\n\n## Rabotage\n- Rabot électrique : dégrossissage.\n- Dégauchisseuse : dressage.\n- Corroyeuse : épaisseur.\n\n## Fraisage (défonceuse)\n- Fraise à tenon, à queue-d'aronde, à moulure.",
            35, 2);

        $this->chapitre($s2, 'Finition du bois',
            'Ponçage, vernis, cire, peinture.',
            "# Finition\n\n## Ponçage\n| Grain | Usage |\n|---|---|\n| 40–60 | Dégrossissage |\n| 80–120 | Préparation |\n| 180–240 | Finition |\n| 320–400 | Entre couches |\n\n## Vernis\n- Polyuréthane (résistant).\n- Acrylique (à l'eau).\n- Marin (extérieur UV).\n\n## Cire\n- D'abeille (aspect satiné).\n- Dure à l'alcool.\n\n## Peinture\n- Sous-couche obligatoire.\n- Ponçage entre couches.",
            30, 3);

        $this->chapitre($s3, 'Menuiseries extérieures',
            'Fenêtres, portes et volets.',
            "# Menuiseries extérieures\n\n## Fenêtres bois\n- Massif ou lamellé-collé.\n- Double vitrage 4/16/4.\n- Quincaillerie : paumelles, crémone.\n\n## Portes d'entrée\n- Pleine ou vitrée.\n- Seuil aluminium avec rupture pont thermique.\n- Serrure 3 points (A2P).\n\n## Volets\n- Battants, pliants ou roulants.\n- Traitement autoclave classe 3/4.",
            35, 1);

        $this->chapitre($s3, 'Menuiseries intérieures',
            'Escaliers, parquets et placards.',
            "# Menuiseries intérieures\n\n## Escaliers\n- Droit, quart tournant, hélicoïdal.\n- Giron : 23–30 cm.\n- Hauteur marche : 16–21 cm.\n- Largeur mini : 70 cm.\n- Garde-corps ≥ 90 cm.\n\n## Parquets\n- Massif (≥ 14 mm) ou contrecollé.\n- Pose clouée, collée ou flottante.\n\n## Placards\n- Sous pente, dressing, bibliothèques.\n- Portes coulissantes ou battantes.",
            35, 2);

        $this->chapitre($s3, 'Agencement sur mesure',
            'Conception et fabrication de meubles.',
            "# Agencement sur mesure\n\n## Processus\n1. Prise de mesures.\n2. Plan CAO (TopSolid, Cabinet Vision).\n3. Validation client.\n4. Débit + optimisation.\n5. Usinage et assemblage.\n6. Finition + quincaillerie.\n7. Pose sur site.\n\n## Matériaux\n- MDF laqué (façades).\n- Contreplaqué (structure).\n- Mélaminé (intérieur).",
            25, 3);

        $this->exam($s1, 'Évaluation — Bois et Matériaux', [
            ['Bois recommandé pour menuiserie extérieure ?', QuestionTypeEnum::SINGLE_CHOICE, ['Hêtre', 'Douglas', 'MDF', 'Aggloméré'], [1]],
            ['Panneaux adaptés à l\'extérieur ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['CTBX', 'MDF', 'OSB', 'CTBH'], [0]],
            ['Le chêne est un bois feuillus dur et durable.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Techniques de Menuiserie', [
            ['Assemblage le plus solide ?', QuestionTypeEnum::SINGLE_CHOICE, ['Tourillons', 'Tenon-mortaise', 'Lamello', 'Vis à bois'], [1]],
            ['Outil pour moulure décorative ?', QuestionTypeEnum::SINGLE_CHOICE, ['Scie circulaire', 'Rabot', 'Défonceuse', 'Perceuse'], [2]],
            ['Vernis polyuréthane adapté aux parquets.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s3, 'Évaluation — Fabrication et Pose', [
            ['Hauteur minimale garde-corps escalier ?', QuestionTypeEnum::SINGLE_CHOICE, ['70 cm', '80 cm', '90 cm', '100 cm'], [2]],
            ['Types d\'escaliers en bois ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Droit', 'Quart tournant', 'Hélicoïdal', 'Escamotable'], [0, 1, 2]],
            ['Parquet massif : épaisseur mini 14 mm.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($formation, 'Examen final — Menuiserie', [
            ['Panneau le plus résistant à l\'humidité ?', QuestionTypeEnum::SINGLE_CHOICE, ['MDF', 'Aggloméré', 'Contreplaqué CTBX', 'OSB'], [2]],
            ['Critères réglementaires escalier ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Giron 23–30 cm', 'Hauteur marche 16–21 cm', 'Largeur mini 120 cm', 'Garde-corps ≥ 90 cm'], [0, 1, 3]],
            ['Lamellé-collé plus stable que massif pour fenêtre.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationPeinture(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Peinture & Finitions en Bâtiment'],
            [
                'short_description' => 'Préparation des supports, techniques d\'application et finitions décoratives.',
                'description' => 'Formation aux techniques professionnelles de peinture : préparation des supports, application peinture/papier peint, effets décoratifs (patine, stuc, béton ciré), finitions boiseries et sols.',
                'price' => null,
                'duration_hours' => 60,
                'difficulty_level' => FormationLevelEnum::BEGINNER,
                'is_active' => false,
                'is_featured' => false,
                'is_certifying' => true,
                'tags' => ['peinture', 'finition', 'enduit', 'décoration', 'revêtement', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Préparation des Supports', 150);
        $s2 = $this->section($formation, 2, 'Techniques d\'Application', 180);
        $s3 = $this->section($formation, 3, 'Finitions et Protections', 120);

        $this->chapitre($s1, 'Diagnostic des supports',
            'Analyser et préparer les surfaces.',
            "# Diagnostic des supports\n\n## Types\n- Plâtre : poreux, sous-couche.\n- Béton : lisse, à traiter.\n- Bois : noeuds à bloquer.\n- Métal : antirouille.\n\n## Traitements\n- Nettoyage, décapage.\n- Rebouchage des trous et fissures.\n- Traitement moisissures (eau de javel/fongicide).\n\n## Test d'adhérence\nQuadrillage + ruban adhésif : si décollement, support non sain.",
            30, 1, true);

        $this->chapitre($s1, 'Enduits de lissage et rebouchage',
            'Obtenir une surface parfaitement lisse.',
            "# Enduits\n\n## Types\n- **Rebouchage** : petits trous.\n- **Lissage** : grandes surfaces (1–3 mm).\n- **Garnissage** : défauts profonds (5–10 mm).\n- **Finition** : dernière passe.\n\n## Application\n1. Au couteau à enduire.\n2. Lisser en croisant.\n3. Ponçage (grain 120–180).\n4. Dépoussiérage.\n\n## Séchage\n- Poudre : 30–60 min.\n- Prêt à l'emploi : 2–4 h.",
            30, 2);

        $this->chapitre($s1, 'Sous-couches et primaires',
            'Garantir l\'adhérence de la peinture.',
            "# Sous-couches\n\n## Rôle\n1. Uniformiser l'absorption.\n2. Améliorer l'adhérence.\n3. Masquer les différences de couleur.\n\n## Par support\n| Support | Primaire |\n|---|---|\n| Plâtre neuf | Acrylique |\n| Béton | Accrochage |\n| Bois | Anti-nœuds |\n| Métal | Antirouille |\n| Ancienne peinture satinée | Adhérence |\n\n## Application\n1 couche, ponçage léger grain 180 après séchage.",
            25, 3);

        $this->chapitre($s2, 'Application au rouleau et pinceau',
            'Les gestes professionnels.',
            "# Application\n\n## Rouleau\n- Manchon à poils longs (plafond), courts (lisse).\n- Tracer un W, croiser sans recharger.\n\n## Pinceau\n- Plat (grandes surfaces), rond (détails).\n- Brosse à rechampir (finitions).\n\n## Pistolet\n- Airless (grandes surfaces).\n- Pneumatique (finition soignée).\n\n## Ordre\n1. Plafond.\n2. Murs (haut → bas).\n3. Boiseries.\n4. Sol.",
            35, 1);

        $this->chapitre($s2, 'Papiers peints et revêtements',
            'Pose de papier peint, intissé et toile de verre.',
            "# Revêtements muraux\n\n## Types\n- Papier traditionnel.\n- **Intissé** : colle au mur, se retire à sec.\n- Toile de verre : à peindre après pose.\n- Vinyle : lessivable.\n\n## Pose intissé\n1. Encollage du mur.\n2. Pose du lé.\n3. Marouflage (centre → bords).\n4. Découpe cutter.\n5. Raccord bord à bord.",
            30, 2);

        $this->chapitre($s2, 'Effets décoratifs',
            'Patine, stuc, béton ciré, tadelakt.',
            "# Effets décoratifs\n\n## Patine\n- À l'éponge, au chiffon ou à la brosse.\n\n## Stuc\n- À la chaux (aspect pierre) ou à l'huile (effet marbre).\n- 5–10 couches fines.\n\n## Béton ciré\n- 1–3 mm au couteau à enduire.\n- Finition satinée ou mate.\n\n## Tadelakt\n- Enduit chaux marocain.\n- 3 couches, lissage à la pierre d'agate.",
            35, 3);

        $this->chapitre($s3, 'Lasures et vernis',
            'Protéger et sublimer les boiseries.',
            "# Lasures et vernis\n\n## Lasure\n- Micro-poreuse (extérieur).\n- Satinée (intérieur).\n- Entretien : 3–5 ans.\n\n## Vernis\n- Brillant (parquet vitrifié).\n- Satiné (meubles).\n- Mat (ébénisterie).\n\n## Application vernis\n1. Ponçage grain 240.\n2. Dépoussiérage.\n3. Couche fine.\n4. Ponçage entre couches grain 320.\n5. 2–4 couches.",
            30, 1);

        $this->chapitre($s3, 'Peinture de sols et résines',
            'Revêtements techniques et décoratifs.',
            "# Sols et résines\n\n## Peinture sol\n- Acrylique ou polyuréthane.\n- Décapage + dégraissage préalable.\n\n## Résines\n- **Époxy** : haute résistance mécanique et chimique.\n- **Polyuréthane** : souple, résistante UV.\n- **Mortier autonivelant** : sols industriels.\n\n## Application époxy\n1. Primaire d'accrochage.\n2. Mélange résine + durcisseur.\n3. Raclette crantée.\n4. Débullage (rouleau à picots).\n5. Polymérisation 24–48 h.",
            30, 2);

        $this->exam($s1, 'Évaluation — Préparation des Supports', [
            ['Test pour vérifier l\'adhérence d\'une ancienne peinture ?', QuestionTypeEnum::SINGLE_CHOICE, ['Quadrillage', 'Test à l\'eau', 'Test thermique', 'Solvant'], [0]],
            ['Types d\'enduits de préparation ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Rebouchage', 'Lissage', 'Collage', 'Garnissage'], [0, 1, 3]],
            ['Une sous-couche est nécessaire sur support neuf.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Techniques d\'Application', [
            ['Ordre de travail recommandé ?', QuestionTypeEnum::SINGLE_CHOICE, ['Murs → Plafond → Sol', 'Plafond → Murs → Sol', 'Sol → Murs → Plafond', 'Murs → Sol → Plafond'], [1]],
            ['Revêtements pouvant être peints après pose ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Toile de verre', 'Intissé', 'Vinyle', 'Papier traditionnel'], [0, 1]],
            ['Le stuc nécessite 5 à 10 couches.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s3, 'Évaluation — Finitions', [
            ['Vernis recommandé pour parquet ?', QuestionTypeEnum::SINGLE_CHOICE, ['Acrylique', 'Polyuréthane', 'Lasure', 'Cire d\'abeille'], [1]],
            ['La résine époxy est utilisée pour les sols industriels.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
            ['Lasure micro-poreuse adaptée à l\'extérieur.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($formation, 'Examen final — Peinture et Finitions', [
            ['Temps séchage enduit prêt à l\'emploi ?', QuestionTypeEnum::SINGLE_CHOICE, ['30 min', '2–4 h', '12 h', '24 h'], [1]],
            ['Outils pour papier intissé ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Spatule à maroufler', 'Cutter', 'Table à tapisser', 'Pistolet'], [0, 1, 2]],
            ['Le tadelakt est d\'origine marocaine.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationSoudure(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Soudure & Métallerie'],
            [
                'short_description' => 'Procédés de soudage, métallerie du bâtiment et ferronnerie.',
                'description' => 'Formation aux techniques de soudage (arc, MIG/MAG, TIG, oxycoupage) et à la métallerie : portails, garde-corps, charpentes métalliques et ferronnerie décorative.',
                'price' => null,
                'duration_hours' => 85,
                'difficulty_level' => FormationLevelEnum::INTERMEDIATE,
                'is_active' => false,
                'is_featured' => false,
                'is_certifying' => true,
                'tags' => ['soudure', 'métallerie', 'ferronnerie', 'soudage', 'MIG', 'TIG', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Fondamentaux de la Soudure', 150);
        $s2 = $this->section($formation, 2, 'Techniques Avancées', 150);
        $s3 = $this->section($formation, 3, 'Métallerie du Bâtiment', 180);

        $this->chapitre($s1, 'Procédés de soudage',
            'Arc, MIG/MAG, TIG, oxycoupage.',
            "# Procédés\n\n## Arc (MMA/SMAW)\n- Électrode enrobée, arc électrique.\n- Acier carbone, simple et polyvalent.\n\n## MIG/MAG (GMAW)\n- MIG : gaz inerte (Ar) pour alu/inox.\n- MAG : gaz actif (CO₂) pour acier.\n- Fil dévidé en continu, productif.\n\n## TIG (GTAW)\n- Électrode tungstène non fusible.\n- Haute qualité, inox/alu/titane.\n\n## Oxycoupage\n- Découpe par oxydation.\n- Acier 5–300 mm.",
            35, 1, true);

        $this->chapitre($s1, 'Métaux d\'apport et gaz',
            'Choix du métal d\'apport et du gaz.',
            "# Métaux d'apport et gaz\n\n## Métaux d'apport\n| Procédé | Apport |\n|---|---|\n| Arc rutile | Acier général |\n| Arc basique | Acier épais |\n| MIG fil massif | Acier, alu |\n| TIG baguette | Inox, alu |\n\n## Gaz\n| Gaz | Usage |\n|---|---|\n| Argon 100 % | TIG, MIG alu/inox |\n| CO₂ 100 % | MAG acier |\n| Ar/CO₂ 82/18 | MAG acier |\n\n## Sécurité\n- Stockage debout, chaîné.\n- Détendeur adapté.\n- Ventilation obligatoire.",
            30, 2);

        $this->chapitre($s1, 'Sécurité en soudage',
            'EPI et prévention des risques.',
            "# Sécurité\n\n## EPI\n- Casque à filtre auto (DIN 5–13).\n- Gants cuir longs.\n- Vêtements ignifugés.\n- Chaussures sécurité.\n- Masque à cartouche (fumées).\n\n## Risques\n| Risque | Prévention |\n|---|---|\n| Brûlures | EPI + écran |\n| Fumées toxiques | Ventilation |\n| Incendie | Extincteur |\n| Électrocution | Câbles OK, mise à terre |\n\n## Règles\n- Zone ventilée.\n- Écran autour de la zone.\n- Attendre refroidissement.",
            25, 3);

        $this->chapitre($s2, 'Soudage des aciers',
            'Paramètres pour acier carbone et allié.',
            "# Soudage des aciers\n\n## Acier carbone\n- Préparation : brossage, meulage.\n- Préchauffage > 30 mm (150–300 °C).\n- Paramètres MAG : 18–22 V, 150–250 A.\n- Vitesse : 25–45 cm/min.\n\n## Inox\n- TIG baguette ER308L, Argon 100 %.\n- Risque : sensibilisation 450–850 °C.\n\n## Défauts\n| Défaut | Cause |\n|---|---|\n| Porosité | Manque gaz |\n| Manque pénétration | Intensité faible |\n| Crique | Refroidissement rapide |",
            35, 1);

        $this->chapitre($s2, 'Soudage de l\'aluminium',
            'Particularités de l\'aluminium.',
            "# Soudage aluminium\n\n## Particularités\n- Oxyde d'alumine (fusion à 2050 °C).\n- Conductivité thermique élevée.\n- Dilatation importante.\n\n## TIG\n- Courant alternatif (AC).\n- Tungstène thorié Ø 2,4 mm.\n- Baguette ER4043 ou ER5356.\n- Argon 10–15 L/min.\n\n## MIG\n- Pistolet push-pull.\n- Fil ER4043/ER5356 Ø 1,0–1,6 mm.\n- 20–24 V, 150–250 A.\n\n## Défauts\n- Craquelures (fissuration à chaud).\n- Porosité (hydrogène).",
            30, 2);

        $this->chapitre($s3, 'Serrurerie et ferronnerie',
            'Portails, rampes, garde-corps.',
            "# Serrurerie\n\n## Portails\n- Tube acier 40×40 ou 60×60 mm.\n- Galvanisé + thermolaquage.\n- Serrure 3 points, motorisation.\n\n## Garde-corps\n- Hauteur : ≥ 100 cm (tertiaire), ≥ 90 cm (logement).\n- Écartement ≤ 11 cm (sécurité enfants).\n- Main courante Ø 40–50 mm.\n\n## Ferronnerie décorative\n- Volutes cintrées à chaud/froid.\n- Fers plats 20×5 mm, carrés 12×12 mm.\n- Norme NF P 01-012, NF EN 1090.",
            35, 1);

        $this->chapitre($s3, 'Charpentes métalliques',
            'Profils, fabrication, montage.',
            "# Charpentes métalliques\n\n## Profils\n- **IPE** : portées jusqu'à 15 m.\n- **HEA/HEB** : fortes charges.\n- **UPN** : pannes, lisses.\n- **RHS/SHS** : poteaux, poutres.\n\n## Conception\n- Eurocode 3 (NF EN 1993).\n- Assemblages boulonnés (HR) ou soudés.\n- Contreventement (croix Saint-André).\n\n## Fabrication\n1. Tracé et débit.\n2. Perçage.\n3. Soudage atelier (MAG/arc).\n4. Grenaillage + peinture.\n5. Marquage et contrôle.",
            35, 2);

        $this->exam($s1, 'Évaluation — Fondamentaux', [
            ['Procédé avec électrode tungstène non fusible ?', QuestionTypeEnum::SINGLE_CHOICE, ['MIG/MAG', 'TIG', 'Arc (MMA)', 'Oxycoupage'], [1]],
            ['EPI obligatoires pour le soudage ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Casque à filtre', 'Gants cuir', 'Lunettes soleil', 'Vêtements ignifugés'], [0, 1, 3]],
            ['MIG utilise un gaz inerte (Argon).', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Techniques Avancées', [
            ['Pourquoi utilise-t-on le courant alternatif en TIG alu ?', QuestionTypeEnum::SINGLE_CHOICE, ['Casser alumine', 'Économiser', 'Refroidir', 'Meilleur aspect'], [0]],
            ['Défauts du soudage des aciers ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Porosité', 'Manque pénétration', 'Surchauffe', 'Crique'], [0, 1, 3]],
            ['L\'inox peut être soudé sans gaz.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [1]],
        ]);
        $this->exam($s3, 'Évaluation — Métallerie', [
            ['Hauteur mini garde-corps logement ?', QuestionTypeEnum::SINGLE_CHOICE, ['80 cm', '90 cm', '100 cm', '110 cm'], [1]],
            ['Profils standards charpente ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['IPE', 'HEA', 'IPN', 'UPN'], [0, 1, 3]],
            ['NF EN 1090 régit la fabrication des structures métalliques.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($formation, 'Examen final — Soudure et Métallerie', [
            ['Gaz utilisé en MAG pour l\'acier ?', QuestionTypeEnum::SINGLE_CHOICE, ['Argon pur', 'CO₂ pur', 'Hélium', 'Azote'], [1]],
            ['Éléments garde-corps conforme ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Main courante', 'Barreaudage ≤ 11 cm', 'Hauteur ≥ 90 cm', 'Marches antidérapantes'], [0, 1, 2]],
            ['TIG est plus rapide que MIG.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [1]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationCarrelage(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Carrelage & Revêtements de Sols'],
            [
                'short_description' => 'Pose de carrelage, chapes, étanchéité et revêtements souples.',
                'description' => 'Formation aux métiers du carrelage : préparation des supports (chapes, ragréage), pose du carrelage (collée, scellée, sur plots), jointoiement, et revêtements souples (moquette, PVC, parquet flottant).',
                'price' => null,
                'duration_hours' => 65,
                'difficulty_level' => FormationLevelEnum::BEGINNER,
                'is_active' => false,
                'is_featured' => false,
                'is_certifying' => true,
                'tags' => ['carrelage', 'revêtement', 'sol', 'chape', 'étanchéité', 'BTP'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Supports et Préparation', 150);
        $s2 = $this->section($formation, 2, 'Pose du Carrelage', 180);
        $s3 = $this->section($formation, 3, 'Revêtements de Sols Souples', 120);

        $this->chapitre($s1, 'Types de supports et diagnostic',
            'Analyser le support avant pose.',
            "# Supports\n\n## Types\n- Dalle béton : idéal.\n- Chape ciment : traditionnelle.\n- Chape anhydrite : primer obligatoire.\n- Plancher bois : désolidarisation.\n- Carrelage existant : dépolir + primaire.\n\n## Diagnostic\n- Quadrillage + sonorité.\n- Planéité : règle 2 m, tolérance 3–5 mm.\n- Humidité résiduelle.\n\n> DTU 52.1 : support propre, sec, plan, résistant.",
            30, 1, true);

        $this->chapitre($s1, 'Chapes et ragréage',
            'Support parfait pour le carrelage.',
            "# Chapes\n\n## Traditionnelle ciment\n- Dosage : 350 kg/m³.\n- Épaisseur mini : 5 cm (désolidarisée), 3 cm (adhérente).\n- Séchage : 1 semaine/cm.\n\n## Fluide\n- Ciment ou anhydrite.\n- Épaisseur 2–10 cm.\n- Surface parfaitement plane.\n\n## Ragréage\n- 1–10 mm par passe.\n- Ponçage après séchage.\n\n## Joints de fractionnement\nTous les 25–30 m² dans la chape.",
            35, 2);

        $this->chapitre($s1, 'Étanchéité sous carrelage',
            'Protéger les pièces humides.',
            "# Étanchéité\n\n## Réglementation\nDTU 52.1 et NF DTU 52.2 : étanchéité obligatoire sous carrelage des douches et salles de bain.\n\n## SEL (liquide)\n- Résine au rouleau, 2 couches croisées.\n- Bandes aux angles.\n\n## SEF (feuille)\n- Membrane PVC/bitumeuse.\n- Relevés périphériques 10 cm.\n\n## Zones\n- Douche italienne : sol + 20 cm murs.\n- Salle de bain : sol + 20 cm murs.",
            25, 3);

        $this->chapitre($s2, 'Méthodes de pose',
            'Collée, scellée, sur plots.',
            "# Méthodes de pose\n\n## Pose collée\n- Mortier-colle, peigne 6–12 mm.\n- Double encollage pour carreaux > 30×30.\n\n## Pose scellée\n- Lit mortier 2–4 cm.\n- Pour terrasses et grands carreaux.\n\n## Pose sur plots\n- Plots réglables 2–15 cm.\n- Pour terrasses extérieures.\n\n## Joints\n| Format | Joint mini |\n|---|---|\n| ≤ 30×30 cm | 2 mm |\n| 30–60 cm | 3 mm |\n| > 60 cm | 5 mm |\n| Extérieur | 5–8 mm |",
            35, 1);

        $this->chapitre($s2, 'Découpe et calepinage',
            'Optimiser la pose.',
            "# Découpe et calepinage\n\n## Calepinage\n- Éviter coupes < 1/3 carreau.\n- Centrer sur l'axe de la pièce.\n\n## Outils\n- Coupe-carreau manuel (céramique).\n- Coupe-carreau électrique (grès, porcelaine).\n- Trépan diamanté (découpes rondes).\n\n## Découpes\n- À 45° (onglet).\n- En L (angles de porte).\n- Au fil à eau (complexes).",
            30, 2);

        $this->chapitre($s2, 'Jointoiement et finitions',
            'Joints propres et durables.',
            "# Jointoiement\n\n## Mortiers\n- Ciment : économique.\n- Époxy : imperméable, anti-taches.\n- Souple polyuréthane : plancher chauffant.\n\n## Application\n1. Attendre 24 h après pose.\n2. Taloche caoutchouc (diagonale).\n3. Essuyer éponge humide (15–30 min).\n4. Nettoyage à sec (24–48 h).\n\n## Profils\n- Finition L, T, quart-de-rond.\n- Bandes de rive.\n- Seuil aluminium/bois/pierre.",
            30, 3);

        $this->chapitre($s3, 'Moquette et dalles textiles',
            'Pose de moquette.',
            "# Moquette\n\n## Types\n- À velours (lisse, bureau).\n- À bouclettes (texturé, passage).\n- Haute densité (fort passage).\n\n## Pose\n1. Support plan et sec.\n2. Sous-couche acoustique.\n3. Acclimatation 24 h.\n4. Découpe et ajustement.\n5. Fixation double-face ou colle.\n\n## Dalles\n- Remplacement facile.\n- Joints alternés.\n- Sens de pose fléché au dos.",
            30, 1);

        $this->chapitre($s3, 'Revêtements PVC et linoléum',
            'Sols vinyle et naturels.',
            "# PVC et linoléum\n\n## PVC\n- Lés larges (2–4 m) collés.\n- Dalles clipsables.\n- Classement UPEC.\n\n## Linoléum\n- Naturel : huile lin, jute, résines.\n- Antistatique, antibactérien.\n- Hôpitaux, écoles, bureaux.\n\n## Pose PVC lés\n1. Acclimatation 24 h.\n2. Découpe avec surépaisseur.\n3. Encollage.\n4. Marouflage.\n5. Soudure (à froid ou à chaud).\n6. Profilés de finition.",
            30, 2);

        $this->exam($s1, 'Évaluation — Supports', [
            ['Tolérance planéité support carrelage ?', QuestionTypeEnum::SINGLE_CHOICE, ['1 mm/2 m', '3–5 mm/2 m', '10 mm/2 m', '15 mm/2 m'], [1]],
            ['Types de chapes sous carrelage ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Ciment', 'Fluide', 'Bois', 'Anhydrite'], [0, 1, 3]],
            ['Étanchéité obligatoire sous carrelage douche.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Pose du Carrelage', [
            ['Méthode pour carreaux grand format ?', QuestionTypeEnum::SINGLE_CHOICE, ['Scellée', 'Collée double encollage', 'Sur plots', 'À la volée'], [1]],
            ['Outils découpe carrelage ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Coupe-carreau manuel', 'Coupe électrique', 'Scie cloche', 'Rabot'], [0, 1, 2]],
            ['Joint mini pour carreau 30×30 intérieur : 2 mm.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s3, 'Évaluation — Revêtements Souples', [
            ['Revêtement à base de matériaux naturels ?', QuestionTypeEnum::SINGLE_CHOICE, ['PVC', 'Linoléum', 'Moquette', 'Vinyle'], [1]],
            ['Linoléum antistatique et antibactérien.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
            ['Dalles moquette posées en joints alternés.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($formation, 'Examen final — Carrelage', [
            ['DTU régissant la pose du carrelage ?', QuestionTypeEnum::SINGLE_CHOICE, ['DTU 52.1', 'DTU 23.1', 'DTU 60.1', 'DTU 43.1'], [0]],
            ['Systèmes d\'étanchéité sous carrelage ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['SEL (liquide)', 'SEF (feuille)', 'Film polyane', 'Enduit bitumineux'], [0, 1]],
            ['Carreau 60×60 cm : joint ≥ 5 mm.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function creerFormationGestionChantier(): void
    {
        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Gestion de Chantier & Sécurité BTP'],
            [
                'short_description' => 'Planification, conduite de travaux, sécurité et prévention.',
                'description' => 'Formation à la gestion de chantier et à la sécurité BTP : organisation et planification, direction des équipes, suivi budgétaire, réglementation sécurité (PPSPS, DUERP), évaluation des risques, EPI/EPC.',
                'price' => null,
                'duration_hours' => 70,
                'difficulty_level' => FormationLevelEnum::INTERMEDIATE,
                'is_active' => false,
                'is_featured' => true,
                'is_certifying' => true,
                'tags' => ['gestion chantier', 'sécurité', 'BTP', 'conduite travaux', 'prévention', 'planification'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Organisation et Planification', 150);
        $s2 = $this->section($formation, 2, 'Conduite de Travaux', 150);
        $s3 = $this->section($formation, 3, 'Sécurité et Prévention', 180);

        $this->chapitre($s1, 'Étude de faisabilité',
            'Préparer le projet avant l\'ouverture du chantier.',
            "# Faisabilité\n\n## Analyse\n- Site : accès, réseaux, nuisances.\n- Plans : architecte, structure, CVC, électricité.\n- Délais : planning prévisionnel.\n- Budget : estimation coûts.\n\n## Dossier technique\n- DCE, CCTP, BPU, DPGF.\n- Notes de calcul, plans d'exécution.\n- DOE en fin de chantier.\n\n## Autorisations\n- Permis de construire.\n- Déclaration préalable.\n- Autorisations voirie.",
            35, 1, true);

        $this->chapitre($s1, 'Planification de chantier',
            'Gantt, PERT et chemin critique.',
            "# Planification\n\n## Gantt\n- Tâches en barres horizontales.\n- Durée, antériorités, jalons.\n\n## PERT\n- Réseau logique des tâches.\n- Chemin critique = durée minimale.\n\n## Logiciels\n- MS Project, Primavera.\n- GanttProject, OpenProject.\n\n## Phases\n1. Installation de chantier.\n2. Terrassement/fondations.\n3. Structure.\n4. Second oeuvre.\n5. Finitions.\n6. Réception.",
            35, 2);

        $this->chapitre($s1, 'Gestion des approvisionnements',
            'Logistique et suivi des matériaux.',
            "# Approvisionnements\n\n## Plan\n- Défini à partir du planning.\n- Délais : béton 48 h, armatures 1 sem, menuiseries 4–6 sem.\n\n## Stockage\n- Zone stable, drainée, sécurisée.\n- Couvert (ciment, plâtre, bois).\n- Rotation FIFO.\n\n## Réception\n- Vérification quantitative et qualitative.\n- Réserves sur bon de livraison.\n- Photo des anomalies.\n\n## Suivi\n- Tableau consommations.\n- Détection écarts (vol, perte, surconsommation).",
            30, 3);

        $this->chapitre($s2, 'Direction et coordination',
            'Animer les équipes et sous-traitants.',
            "# Direction des équipes\n\n## Rôles\n- Chef de chantier : exécution terrain.\n- Conducteur de travaux : gestion technique/financière.\n- Compagnons : exécution.\n\n## Communication\n- Réunion hebdomadaire.\n- Ordre de service (modifications).\n- Main courante (journal quotidien).\n\n## Sous-traitance\n- Sélection : références, capacité.\n- Contrat : prestations, délais, pénalités.\n- Coordination interfaces.\n\n## Conflits\n- Médiation active.\n- Réunion de crise.\n- Signalement hiérarchique.",
            30, 1);

        $this->chapitre($s2, 'Suivi budgétaire',
            'Maîtriser le budget chantier.',
            "# Suivi budgétaire\n\n## Budget prévisionnel\n- Décompte mensuel (quantités × PU).\n- Variation coûts par lot.\n- Révision de prix (indice BT).\n\n## Postes\n| Poste | Poids |\n|---|---|\n| Main-d'oeuvre | 30–40 % |\n| Matériaux | 25–35 % |\n| Sous-traitance | 15–25 % |\n| Location | 5–10 % |\n| Frais généraux | 5–10 % |\n\n## Réunions\n- Avancement, points bloquants.\n- Validation situations.\n- Planning actualisé.",
            35, 2);

        $this->chapitre($s2, 'Réunions et reporting',
            'Comptes-rendus efficaces.',
            "# Réunions\n\n## Types\n- Kick-off : présentation projet.\n- Hebdomadaire : suivi + sécurité.\n- Coordination : interfaces techniques.\n- Clôture : bilan et réception.\n\n## Ordre du jour\n1. Décisions précédentes.\n2. Avancement par lot.\n3. Sécurité/incidents.\n4. Planning.\n5. Modifications/avenants.\n6. Questions diverses.\n\n## CR\n- Diffusé sous 48 h.\n- Tableau : Action, Responsable, Date.",
            25, 3);

        $this->chapitre($s3, 'Réglementation sécurité',
            'PPSPS, registre, plan de prévention.',
            "# Réglementation\n\n## Textes\n- Code du travail L. 4121-1 à 5.\n- Décret 2008-244.\n- R. 4534-1 (BTP).\n\n## Documents\n- **PPSPS** : par entreprise.\n- **PGCSPS** : plusieurs entreprises.\n- Registre sécurité : accidents, incidents.\n- Registre vérification EPI/échafaudages.\n- **DUERP** : document unique.\n\n## Acteurs\n- Coordonnateur SPS (> 5000 h·j).\n- CARSAT, OPPBTP, Inspection travail.\n\n## Sanctions\n- Absence PPSPS : amende ≤ 10 000 €.\n- Absence DUERP : amende ≤ 1 500 €.",
            35, 1);

        $this->chapitre($s3, 'Évaluation des risques',
            'Identifier et hiérarchiser les risques.',
            "# Évaluation des risques\n\n## Démarche\n1. Identifier les dangers.\n2. Analyser : gravité × probabilité × exposition.\n3. Hiérarchiser.\n4. Planifier actions.\n5. Suivre et réévaluer.\n\n## Risques BTP\n| Risque | Gravité | Fréquence |\n|---|---|---|\n| Chute hauteur | Très élevée | Très fréquente |\n| Électrocution | Élevée | Rare |\n| Écrasement | Élevée | Peu fréquente |\n| Manutention | Moyenne | Très fréquente |\n| Silice | Élevée (LT) | Quotidienne |\n\n## Cotation\nCriticité = Gravité × Probabilité (seuil ≥ 8/16).",
            30, 2);

        $this->chapitre($s3, 'Équipements de protection',
            'EPC et EPI sur le chantier.',
            "# Équipements de protection\n\n## EPC (collective)\n- Garde-corps : ≥ 100 cm, lisse 45 cm, plinthe.\n- Filets sécurité : mailles ≤ 10 cm.\n- Échafaudages : montage par personnel qualifié.\n\n## EPI (individuelle)\n- Casque (norme EN 397).\n- Chaussures sécurité (EN 345).\n- Harnais anti-chute (EN 361).\n- Gants, lunettes, protections auditives.\n\n## Principes\n- EPC prioritaire sur EPI.\n- Vérification périodique obligatoire.\n- Formation des utilisateurs.",
            30, 3);

        $this->exam($s1, 'Évaluation — Organisation', [
            ['Document détaillant les prix unitaires des ouvrages ?', QuestionTypeEnum::SINGLE_CHOICE, ['CCTP', 'BPU', 'DPGF', 'DCE'], [1]],
            ['Étapes de la méthode du chemin critique ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Durée minimale projet', 'Tâches dépendantes', 'Budget prévisionnel', 'Séquence clé'], [0, 1, 3]],
            ['Le DOE est remis en fin de chantier.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s2, 'Évaluation — Conduite de Travaux', [
            ['Principal poste de dépense sur un chantier ?', QuestionTypeEnum::SINGLE_CHOICE, ['Matériaux', 'Main-d\'oeuvre', 'Sous-traitance', 'Location'], [1]],
            ['Éléments d\'une réunion de chantier ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Avancement', 'Sécurité', 'Budget marketing', 'Planning'], [0, 1, 3]],
            ['L\'ordre de service est un document contractuel.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($s3, 'Évaluation — Sécurité', [
            ['Document sécurité obligatoire par entreprise sur chantier ?', QuestionTypeEnum::SINGLE_CHOICE, ['PPSPS', ['PGCSPS', 'DOE', 'CCTP']], [0]],
            ['Risques principaux du BTP ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Chute hauteur', 'Électrocution', 'Noyade', 'Manutention'], [0, 1, 3]],
            ['Les EPI sont prioritaires sur les EPC.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [1]],
        ]);
        $this->exam($formation, 'Examen final — Gestion de Chantier', [
            ['Seuil déclenchant l\'obligation d\'un coordonnateur SPS ?', QuestionTypeEnum::SINGLE_CHOICE, ['1000 h·j', '5000 h·j', '10000 h·j', '20000 h·j'], [1]],
            ['Documents obligatoires sur un chantier ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['PPSPS', 'Registre sécurité', 'DUERP', 'Carnet de chèques'], [0, 1, 2]],
            ['Le DUERP doit être mis à jour annuellement.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }
}
