# Migration de l'administration : Filament → Inertia + Vue 3

Cette documentation décrit **mot pour mot** comment remplacer tout le back‑office **Filament**
(`/admin`) par une administration **Inertia + Vue 3**, dans la continuité de la migration
Livewire → Inertia déjà réalisée côté étudiant.

## Principe directeur : rester simple (KISS)

L'erreur classique d'une migration « ressource par ressource » est de réécrire 9 fois la même
logique de table/formulaire. Pour l'éviter, **on définit d'abord une poignée de composants Vue
réutilisables** (table, formulaire, modale, filtres, actions groupées) décrits dans
[`01-architecture-cible.md`](01-architecture-cible.md). Chaque PRD de ressource n'a alors plus qu'à
**déclarer ses colonnes, ses champs et ses actions** — pas de code d'infrastructure répété.

> Règle d'or : si un PRD vous demande d'écrire de la « plomberie » (pagination, tri, recherche,
> ouverture de modale…), c'est qu'il faut l'extraire dans un composant partagé.

## Comment lire ces documents

| Fichier                                                                  | Contenu                                                         |
|--------------------------------------------------------------------------|-----------------------------------------------------------------|
| [`00-vue-d-ensemble-et-strategie.md`](00-vue-d-ensemble-et-strategie.md) | Périmètre, inventaire Filament, stratégie, lots, risques        |
| [`01-architecture-cible.md`](01-architecture-cible.md)                   | Architecture Inertia/Vue, auth, layout, **composants partagés** |
| [`02-prd-dashboard-statistiques.md`](02-prd-dashboard-statistiques.md)   | Tableau de bord + widgets/graphiques                            |
| [`03-prd-formations.md`](03-prd-formations.md)                           | CRUD Formations                                                 |
| [`04-prd-sections-chapitres.md`](04-prd-sections-chapitres.md)           | Sections + Chapitres (imbriqué, extraction PDF)                 |
| [`05-prd-examens-questions.md`](05-prd-examens-questions.md)             | Examens + Questions + Options + Tentatives                      |
| [`06-prd-inscriptions-paiements.md`](06-prd-inscriptions-paiements.md)   | Inscriptions, paiements, factures, remboursements               |
| [`07-prd-progression.md`](07-prd-progression.md)                         | Progression des apprenants                                      |
| [`08-prd-certificats.md`](08-prd-certificats.md)                         | Certificats (vérification, révocation, téléchargement)          |
| [`09-prd-codes-acces.md`](09-prd-codes-acces.md)                         | Codes d'accès formation                                         |
| [`10-prd-utilisateurs-roles.md`](10-prd-utilisateurs-roles.md)           | Utilisateurs & rôles                                            |
| [`11-prd-parametres.md`](11-prd-parametres.md)                           | Page Paramètres                                                 |
| [`12-diagrammes-globaux.md`](12-diagrammes-globaux.md)                   | Cas d'usage, classes, séquences globales                        |
| [`13-plan-de-migration.md`](13-plan-de-migration.md)                     | Plan par lots, ordre, check‑list, correspondances               |

## Convention de chaque PRD

1. **Objectif**
2. **Existant Filament** (champs, colonnes, filtres, actions — tel quel)
3. **Cible Inertia/Vue** (routes, contrôleur, Form Request, pages & composants Vue)
4. **Diagramme de cas d'utilisation** (Mermaid)
5. **Classes participantes** (Mermaid class diagram)
6. **Diagramme de séquence** des flux clés (Mermaid)
7. **Règles métier & validation**
8. **Critères d'acceptation**

Les diagrammes utilisent **Mermaid** (rendu natif sur GitHub/GitLab et la plupart des éditeurs Markdown).
