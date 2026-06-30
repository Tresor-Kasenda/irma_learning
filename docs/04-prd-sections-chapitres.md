# 04 — PRD : Sections & Chapitres (imbriqués)

## 1. Objectif
Migrer l'édition imbriquée **Formation → Sections → Chapitres** (relation managers Filament) et
conserver l'**extraction automatique de contenu PDF** des chapitres.

## 2. Existant Filament
- **SectionResource** : `formation_id`, `title` (unique), `description` (RichEditor),
  `order_position` (auto = max+1), `duration` (auto depuis la durée formation), `is_active`.
  Table réordonnable (`reorderable('order_position')`), action groupée activer/désactiver.
- **SectionsRelationManager** (sur Formation) : créer/réordonner les sections, action
  « Chapitres & examen » → page section.
- **ChaptersRelationManager** (sur Section) : `title`, `content_type` (video/text/pdf),
  `media_url`/`video_url` (FileUpload), `content` (RichEditor), `duration_minutes`, `is_free`,
  `is_active`, `order_position`. **Extraction PDF** via `ChapterPdfExtractionService` au create.
- **ExamRelationManager** (sur Section) : l'examen de la section *(cf. PRD 05)*.

## 3. Cible Inertia/Vue
- **Routes**
  - Sections : `admin.formations.sections.{index,store,update,destroy,reorder}` (imbriquées sous formation).
  - Chapitres : `admin.sections.chapters.{index,store,update,destroy,reorder}`.
- **Contrôleurs** : `SectionController`, `ChapterController`.
- **Form Requests** : `Store/UpdateSectionRequest`, `Store/UpdateChapterRequest`.
- **Pages Vue**
  - `Admin/Formations/Show.vue` → `RelationPanel` **Sections** (DataTable réordonnable + modale).
  - `Admin/Sections/Show.vue` → `RelationPanel` **Chapitres** (DataTable réordonnable + modale) +
    `RelationPanel` **Examen** (PRD 05).
- **Réordonnancement** : drag‑and‑drop → `POST reorder` avec `[{id, order_position}]`.
- **Extraction PDF** : à l'upload d'un chapitre `content_type = pdf`, le contrôleur appelle
  `ChapterPdfExtractionService` et remplit `content` automatiquement (réutilisation **stricte** du service).

### Champs Chapitre (déclaration)
| Champ | Type | Visible si | Règles |
|---|---|---|---|
| title | text | — | required |
| content_type | select(video/text/pdf) | — | required |
| video_url | file (video/*) | type=video | required si video |
| media_url | file (application/pdf) | type=pdf | required si pdf |
| content | richtext | type=text (ou auto‑rempli si pdf) | nullable |
| duration_minutes | number | — | nullable |
| is_free | toggle | — | bool |
| is_active | toggle | — | bool |

> `content_type` est limité à **video / text / pdf** (l'enum `ChapterTypeEnum` n'a plus `audio`).

## 4. Cas d'utilisation
```mermaid
flowchart TD
  A((Admin)) --> S1[Créer/réordonner sections d'une formation]
  A --> S2[Activer/désactiver une section]
  A --> C1[Créer/réordonner chapitres d'une section]
  A --> C2[Uploader un PDF -> extraction auto du contenu]
  A --> C3[Définir un chapitre gratuit]
  C2 -.réutilise.-> SVC[ChapterPdfExtractionService]
```

## 5. Classes participantes
```mermaid
classDiagram
  class SectionController { +store() +update() +destroy() +reorder() }
  class ChapterController { +store() +update() +destroy() +reorder() }
  class ChapterPdfExtractionService { +extractAndSetFormData(file, set) }
  class Section
  class Chapter
  class Formation
  Formation "1" --> "*" Section
  Section "1" --> "*" Chapter
  ChapterController --> ChapterPdfExtractionService
  SectionController --> Section
  ChapterController --> Chapter
```

## 6. Séquence — création d'un chapitre PDF avec extraction
```mermaid
sequenceDiagram
  actor Admin
  participant V as Sections/Show.vue
  participant M as ResourceFormModal (chapitre)
  participant C as ChapterController
  participant SVC as ChapterPdfExtractionService
  participant DB
  Admin->>M: type=pdf + upload fichier
  M->>C: POST /admin/sections/{id}/chapters (multipart)
  C->>C: store(pdf,'chapters','public')
  C->>SVC: extractAndSetFormData(path, setter)
  SVC-->>C: content (markdown/html), durée estimée
  C->>DB: Chapter::create(section, content, …)
  DB-->>C: ok
  C-->>V: redirect + flash « Extraction réussie »
```

## 7. Séquence — réordonnancement
```mermaid
sequenceDiagram
  actor Admin
  participant DT as DataTable (drag&drop)
  participant C as SectionController
  participant DB
  Admin->>DT: glisse une ligne
  DT->>C: POST reorder [{id, order_position}]
  C->>DB: update order_position (transaction)
  C-->>DT: 204 / flash
```

## 8. Règles métier & validation
- `Section.title` **unique** (contrainte BDD) → règle `unique` (ignore en édition).
- `order_position` auto = `max+1` à la création ; recalculé au drag‑and‑drop.
- `duration` de section : calcul auto possible (durée formation / nb sections) — conserver la logique
  existante (`Section::booted`).
- Chapitre `content_type = pdf` ⇒ extraction obligatoire ; en cas d'échec, garder le PDF et notifier.
- Suppression d'une section : confirmer (impacte chapitres + examen + progression).

## 9. Critères d'acceptation
- [ ] Sections gérées **depuis la formation** (liste, création, réordonnancement, activation).
- [ ] Chapitres gérés **depuis la section** (liste, création, réordonnancement, gratuit/actif).
- [ ] Upload PDF → contenu extrait automatiquement (service réutilisé, non réécrit).
- [ ] Types de chapitre limités à video/text/pdf.
- [ ] L'examen de la section est accessible depuis la même page (PRD 05).
