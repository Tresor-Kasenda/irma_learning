# 12 — Diagrammes globaux

## 1. Diagramme de cas d'utilisation (administration)
```mermaid
flowchart TD
  Admin((Administrateur))
  Root((Root))
  subgraph Catalogue
    UC1[Gérer formations]
    UC2[Gérer sections & chapitres]
  end
  subgraph Évaluations
    UC3[Gérer examens & questions]
    UC4[Consulter tentatives]
  end
  subgraph Apprenants
    UC5[Gérer inscriptions & paiements]
    UC6[Suivre la progression]
    UC7[Gérer certificats]
    UC8[Gérer codes d'accès]
  end
  subgraph Administration
    UC9[Gérer utilisateurs]
    UC10[Paramètres]
    UC11[Tableau de bord]
  end
  Admin --> UC1 & UC2 & UC3 & UC4 & UC5 & UC6 & UC7 & UC8 & UC9 & UC10 & UC11
  Root --> UC9
  UC1 -. inclut .-> UC2
  UC2 -. inclut .-> UC3
```

## 2. Modèle du domaine (classes participantes globales)
```mermaid
classDiagram
  class User
  class Formation
  class Section
  class Chapter
  class Exam
  class Question
  class QuestionOption
  class ExamAttempt
  class UserAnswer
  class Enrollment
  class UserProgress
  class Certificate
  class FormationAccessCode

  User "1" --> "*" Enrollment
  User "1" --> "*" ExamAttempt
  User "1" --> "*" Certificate
  Formation "1" --> "*" Section
  Section "1" --> "*" Chapter
  Section "1" --> "0..1" Exam : MorphOne
  Formation "1" --> "0..1" Exam : MorphOne (final, optionnel)
  Exam "1" --> "*" Question
  Question "1" --> "*" QuestionOption
  Exam "1" --> "*" ExamAttempt
  ExamAttempt "1" --> "*" UserAnswer
  Formation "1" --> "*" Enrollment
  Formation "1" --> "*" Certificate
  Formation "1" --> "*" FormationAccessCode
  Chapter "1" --> "*" UserProgress : trackable (morph)
```

## 3. Architecture des composants front (cible)
```mermaid
graph TD
  AL[AdminLayout] --> SB[AdminSidebar]
  AL --> TB[AdminTopbar]
  AL --> PG[Page Index/Show]
  PG --> DT[DataTable]
  DT --> COL[Cells: Text/Badge/Boolean/Date/Image]
  DT --> FB[FilterBar]
  DT --> BB[BulkActionBar]
  PG --> RFM[ResourceFormModal]
  RFM --> FLD[Fields: Text/Select/Toggle/File/RichText/Tags/Date]
  PG --> RP[RelationPanel]
  PG --> CA[ConfirmAction]
  PG --> NOTIF[Notification]
```

## 4. Diagramme d'interaction — flux « construire un cours » (de bout en bout)
```mermaid
sequenceDiagram
  actor Admin
  participant FO as Formations
  participant SE as Section
  participant CH as Chapitres
  participant EX as Examen
  participant QU as Questions
  Admin->>FO: créer une formation
  Admin->>FO: ajouter Section 1, 2, …
  Admin->>SE: ouvrir Section 1
  Admin->>CH: ajouter chapitres (vidéo/texte/pdf)
  Admin->>EX: créer l'examen de la Section 1
  Admin->>QU: ajouter questions + options + bonne réponse
  Note over Admin,QU: Répéter pour chaque section
  Admin->>FO: activer la formation
```

## 5. Diagramme d'état — statut d'une inscription
```mermaid
stateDiagram-v2
  [*] --> pending : inscription (payant)
  [*] --> active : inscription (gratuit/code)
  pending --> active : markPaid
  active --> completed : toutes sections réussies
  active --> suspended : refund
  pending --> [*] : abandon
```

## 6. Diagramme d'état — cycle d'un certificat
```mermaid
stateDiagram-v2
  [*] --> active : émission auto (examens réussis)
  active --> revoked : révocation admin
  active --> expired : date d'expiration atteinte
```
