# 11 — PRD : Paramètres

## 1. Objectif
Migrer la page **Settings** Filament (page personnalisée, `wire:submit="save"`) vers une page Inertia.

## 2. Existant Filament
- Page `app/Filament/Pages/Settings.php` + vue `filament/pages/settings.blade.php` (formulaire Livewire).
- Stocke des réglages applicatifs (table `settings` / clé‑valeur).

## 3. Cible Inertia/Vue
- **Routes** : `admin.settings.{edit,update}`.
- **Contrôleur** : `SettingsController` (`edit` rend le formulaire, `update` persiste).
- **Form Request** : `UpdateSettingsRequest`.
- **Page Vue** : `Admin/Settings/Edit.vue` (formulaire simple via `ResourceFormModal` ou page dédiée).
- Réutiliser le modèle/service `Settings` existant pour lire/écrire les clés.

## 4. Cas d'utilisation
```mermaid
flowchart TD
  A((Admin)) --> V[Consulter les paramètres]
  A --> U[Modifier et enregistrer]
```

## 5. Classes participantes
```mermaid
classDiagram
  class SettingsController { +edit() +update() }
  class UpdateSettingsRequest
  class Settings
  SettingsController --> Settings
  SettingsController --> UpdateSettingsRequest
```

## 6. Séquence
```mermaid
sequenceDiagram
  actor Admin
  participant V as Settings/Edit.vue
  participant C as SettingsController
  participant DB
  Admin->>V: GET /admin/settings
  V->>C: charge valeurs
  Admin->>V: modifie + enregistre
  V->>C: PATCH /admin/settings
  C->>DB: persiste les clés
  C-->>V: redirect + flash success
```

## 7. Critères d'acceptation
- [ ] Lecture/écriture des paramètres sans Livewire.
- [ ] Validation via Form Request.
