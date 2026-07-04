# Design QA — administration IRMA Learning

## Source visuelle

- Annotations navigateur fournies sur les formulaires Chapitre et Section, le détail Section, la navigation Apprenants et l’éditeur d’examen.
- Direction conservée : design system admin existant, palette bordeaux, panneaux carrés, densité et typographie actuelles.

## Comparaison et corrections

- Formulaire Chapitre : les colonnes et panneaux sont contraints avec `min-w-0`, `max-w-full` et `overflow-hidden`. Vérifié à 1280 px sans débordement horizontal (`scrollWidth = clientWidth`).
- Formulaire Section : l’examen a été retiré de la colonne de 360 px. L’action est dans l’en-tête et ouvre un modal large, centré et scrollable.
- Éditeur d’examen : informations, règles, questions et options sont hiérarchisées en cartes responsives. Les actions de déplacement, duplication, suppression et sélection de bonne réponse sont visibles et cohérentes.
- Éditeur Markdown : la zone grandit jusqu’à 640 px, puis l’éditeur et l’aperçu utilisent une hauteur synchronisée avec défilement interne.
- Détail Section : la carte vide d’examen a été supprimée et l’action est placée dans l’en-tête.
- Navigation Apprenants : les entrées Progression et Codes d’accès ont été supprimées. Inscriptions et Certificats pointent vers des écrans fonctionnels.

## Vérifications

- Build Vue/Vite réussi.
- 39 tests Pest ciblés réussis, 303 assertions.
- Vérification visuelle du formulaire Chapitre et du formulaire Section en thème sombre.
- Vérification interactive du modal de création d’examen : ouverture, contenu, largeur et absence de débordement horizontal.
- Contrôle mobile à 390 × 844 px : aucune largeur excédentaire (`scrollWidth = clientWidth = 390`) et action Examen correctement empilée sous l’en-tête.

final result: passed
