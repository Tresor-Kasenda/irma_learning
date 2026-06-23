# Audit et roadmap du projet IRMA Learning

Date de l'audit : 2 mai 2026

## Resume executif

Le projet dispose deja d'une base fonctionnelle importante : application Laravel 12, interface admin Filament, pages Livewire/Volt, gestion des formations, sections, chapitres, inscriptions, paiements, examens, certificats, factures et quelques tests metier.

L'etat actuel n'est toutefois pas encore suffisamment stable pour une mise en production. La priorite n'est pas d'ajouter de nouvelles fonctionnalites, mais de corriger les incoherences de routes, d'aligner le schema de base de donnees avec le code, puis de rendre la suite de tests fiable.

## Etat actuel observe

### Stack technique

- Laravel 12.39.0
- PHP requis par le projet : `^8.4`
- PHP CLI detecte localement pendant l'audit : `8.5.5`
- Filament v3.3.45
- Livewire v3.7.0
- Base de donnees : PostgreSQL
- Sessions, cache et queue configures en base de donnees
- `public/storage` lie correctement

### Modules presents

- Site public : accueil, certifications, detail formation, tarifs.
- Authentification : login, register, forgot/reset password, verification email, confirmation password.
- Espace etudiant : dashboard, profil, formations inscrites, progression.
- Cours : lecteur de formation, chapitres, progression utilisateur.
- Examens : passage d'examen, resultats, tentatives, questions et options.
- Admin Filament : utilisateurs, formations, sections, chapitres, examens, questions, inscriptions, certificats, parametres.
- Paiement et inscription : paiement et inscription gratuite partiellement presents.
- Facturation : generation de facture d'inscription.
- Conversion documentaire : services de conversion PDF/Markdown et extraction de contenu.

### Migrations

Toutes les migrations etaient marquees comme executees dans l'environnement local au moment de l'audit.

### Tests

La suite complete ne passe pas actuellement.

Resultat observe :

```text
Tests: 41 deprecated, 47 failed, 1 passed
```

Les deprecations viennent principalement de l'utilisation de `PDO::MYSQL_ATTR_SSL_CA` avec PHP 8.5. Elles ne bloquent pas forcement le fonctionnement avec PHP 8.4, mais elles polluent les sorties de test et doivent etre traitees si l'environnement reste en PHP 8.5.

## Priorites de stabilisation

### Priorite 1 - Corriger les routes cassees

La navigation authentifiee appelle une route inexistante :

```php
route('formations-lists')
```

Fichier concerne :

```text
resources/views/livewire/layout/navigation.blade.php
```

Effet observe :

- Plusieurs pages authentifiees retournent une erreur 500.
- Les tests du dashboard, du profil, du logout et de certaines pages Livewire echouent.

Decision a prendre :

- Soit creer une vraie route `formations-lists` pour la page "Mes formations".
- Soit remplacer les liens par une route existante comme `dashboard` ou `certifications`.
- Soit renommer proprement les references existantes selon une convention unique.

Actions recommandees :

1. Rechercher toutes les references a `formations-lists`.
2. Choisir la route cible officielle pour "Mes formations".
3. Corriger les vues concernees.
4. Ajouter ou ajuster les tests de navigation authentifiee.

### Priorite 2 - Aligner le schema `enrollments` avec le code

Le modele `Enrollment` declare et utilise plusieurs champs qui ne sont pas presents dans la migration initiale observee.

Champs utilises cote modele/code :

- `payment_method`
- `payment_transaction_id`
- `payment_gateway`
- `payment_gateway_response`
- `payment_processed_at`
- `refunded_at`
- `refund_amount`
- `refund_reason`
- `refund_transaction_id`
- `payment_notes`

Fichiers concernes :

```text
app/Models/Enrollment.php
database/migrations/2025_08_08_150001_create_enrollments_table.php
app/Http/Controllers/EnrollmentController.php
app/Filament/Resources/EnrollmentResource.php
```

Risque :

- Erreurs SQL lors du paiement, remboursement, generation de facture ou affichage admin.
- Tests impossibles a stabiliser tant que le schema et le code ne parlent pas le meme langage.

Actions recommandees :

1. Verifier le schema reel PostgreSQL.
2. Ajouter une migration corrective pour les colonnes manquantes.
3. Verifier les casts du modele `Enrollment`.
4. Couvrir les cas : inscription gratuite, paiement marque comme paye, facture, remboursement.

### Priorite 3 - Stabiliser les tests du service de conversion documentaire

Les tests instancient directement le service :

```php
new DocumentConversionService
```

Or le constructeur attend maintenant :

```php
PdfThumbnailService $thumbnailService
MarkdownFileService $markdownFileService
```

Fichiers concernes :

```text
tests/Feature/Services/DocumentConversionServiceTest.php
app/Services/DocumentConversionService.php
```

Actions recommandees :

1. Remplacer l'instanciation directe par `app(DocumentConversionService::class)`.
2. Mocker les dependances si necessaire.
3. Eviter d'ecrire des fichiers temporaires directement dans le repo pendant les tests.
4. Verifier la presence ou non des fixtures PDF.

### Priorite 4 - Harmoniser les routes et les identifiants formation

Le projet melange parfois `id`, `slug` et modele complet pour les routes de formation.

Exemples observes :

- `route('formation.show', $formation)`
- `route('formation.show', $formation->slug)`
- `route('course.player', ['formation' => $this->formation->id])`
- `route('course.player', ['formation' => $chapter->section->formation->slug])`

Risque :

- Route model binding incoherent.
- Redirections qui fonctionnent dans certains cas et cassent dans d'autres.
- Tests fragiles et comportements differents entre fixtures et donnees reelles.

Actions recommandees :

1. Choisir une convention : `id` ou `slug`.
2. Si le `slug` est choisi, ajouter ou confirmer `getRouteKeyName()` dans `Formation`.
3. Corriger toutes les generations d'URL.
4. Ajouter des tests pour :
   - detail formation,
   - inscription,
   - acces au lecteur de cours,
   - redirection apres paiement ou inscription gratuite.

### Priorite 5 - Finaliser le parcours etudiant

Parcours cible :

```text
Visiteur -> formation -> inscription -> paiement ou gratuit -> cours -> progression -> examen -> certificat
```

Points a stabiliser :

- Inscription gratuite quand `price` est `null` ou `0`.
- Inscription payante avec statut coherent.
- Acces au cours uniquement si l'inscription est valide.
- Progression mise a jour au fil des chapitres.
- Passage d'examen conditionne par l'acces.
- Generation ou activation du certificat apres reussite.

Tests a renforcer :

- Formation gratuite.
- Formation payante.
- Utilisateur non inscrit.
- Utilisateur inscrit mais paiement en attente.
- Formation terminee.
- Tentatives d'examen depassant la limite.

### Priorite 6 - Stabiliser les examens et certificats

Le module examen est deja avance, mais les tests `TakeExam`, `ExamResults` et `CoursePlayer` echouent actuellement.

Zones a verifier :

- Creation de tentative.
- Reprise d'une tentative en cours.
- Questions a choix unique.
- Questions a choix multiple.
- Questions texte/essai si elles sont réellement supportees dans le workflow.
- Calcul du score.
- Respect du nombre maximum de tentatives.
- Redirection vers les resultats.
- Generation ou validation du certificat.

Actions recommandees :

1. Corriger d'abord les routes et le schema `enrollments`.
2. Relancer uniquement les tests examens/cours.
3. Corriger les echecs restants par comportement metier.
4. Ajouter des tests de bout en bout sur une formation avec chapitres et examen final.

### Priorite 7 - Clarifier le paiement

Le code contient une logique de paiement, de facture et de remboursement, mais l'integration semble encore partielle.

Points observes :

- Creation d'inscription payee dans certaines classes.
- Statuts `paid`, `free`, `pending`, `refunded`.
- Generation de facture PDF.
- Remboursement avec identifiant placeholder `refund_id_here`.

Actions recommandees :

1. Decider si le paiement est manuel/admin ou via passerelle externe.
2. Retirer les placeholders ou les isoler derriere une interface de service.
3. Ajouter une couche metier claire pour :
   - marquer comme paye,
   - refuser,
   - rembourser,
   - generer la facture.
4. Ajouter des tests autour des transitions de statut.

### Priorite 8 - Nettoyer les anciennes routes et references mortes

References potentiellement heritees ou absentes :

- `student.learning`
- `student.formation.show`
- `student.formations.validate-code`
- `student.course.learning`
- `formations.index`
- `formations.show`
- `formations-lists`

Certaines sont protegees par `Route::has()`, d'autres non.

Actions recommandees :

1. Lister toutes les routes nommees attendues par les vues et composants.
2. Supprimer les references mortes.
3. Remplacer les anciennes routes par les routes actuelles.
4. Ajouter un test qui rend les layouts principaux sans erreur.

### Priorite 9 - Preparer l'environnement de production

Etat observe avec `php artisan about` :

- `Debug Mode`: active.
- Config non cachee.
- Routes non cachees.
- Views cachees.
- Environment local.

Actions recommandees :

1. Definir une checklist `.env` production.
2. Verifier `APP_DEBUG=false`.
3. Configurer cache config/routes/events/views.
4. Verifier queue worker.
5. Verifier mailer.
6. Verifier stockage public.
7. Verifier logs et monitoring.
8. Executer `php artisan filament:optimize` en production.

### Priorite 10 - Nettoyer la documentation et le repo

Le `README.md` actuel semble etre un ancien dump de code et ne reflete pas l'etat reel de l'application.

Autres elements a nettoyer :

- Fichiers `.DS_Store`.
- Documentation obsoletement placee a la racine.
- Commentaires ou routes de compatibilite qui ne sont plus utiles.
- Eventuels tests `ExampleTest` generiques qui ne valident pas une regle metier claire.

Actions recommandees :

1. Remplacer le README par une documentation projet utile.
2. Ajouter une section installation/dev/test.
3. Ajouter une section modules metier.
4. Ajouter une section comptes/roles.
5. Ajouter une section workflow admin.
6. Ajouter `.DS_Store` au `.gitignore` si necessaire.

## Feuille de route proposee

### Phase 1 - Retrouver une base verte

Objectif : supprimer les erreurs 500 et rendre les tests exploitables.

Taches :

1. Corriger `formations-lists`.
2. Corriger les routes mortes les plus visibles.
3. Aligner le schema `enrollments`.
4. Corriger `DocumentConversionServiceTest`.
5. Relancer la suite de tests.

Critere de sortie :

- Plus d'erreur 500 sur dashboard/profil/navigation.
- Les echecs restants sont des vrais cas metier isoles.

### Phase 2 - Stabiliser le parcours etudiant

Objectif : garantir le parcours principal utilisateur.

Taches :

1. Stabiliser detail formation.
2. Stabiliser inscription gratuite.
3. Stabiliser inscription payante.
4. Stabiliser acces au lecteur de cours.
5. Stabiliser progression.
6. Stabiliser examen et resultats.

Critere de sortie :

- Un test de bout en bout couvre une formation complete.

### Phase 3 - Stabiliser l'admin Filament

Objectif : permettre a l'admin de gerer le contenu sans casser les donnees.

Taches :

1. Verifier CRUD formations.
2. Verifier CRUD sections et chapitres.
3. Verifier CRUD examens, questions et options.
4. Verifier inscriptions.
5. Verifier certificats.
6. Verifier permissions admin/root/student.

Critere de sortie :

- Les tests Filament principaux passent.

### Phase 4 - Paiement, facture et certificat

Objectif : rendre fiable la partie valeur metier.

Taches :

1. Clarifier le flux paiement.
2. Stabiliser les statuts d'inscription.
3. Stabiliser facture.
4. Stabiliser remboursement.
5. Stabiliser certificat.

Critere de sortie :

- Les transitions d'inscription sont testees.
- Les factures ne cassent pas si des donnees optionnelles sont absentes.
- Le certificat est emis selon une regle explicite.

### Phase 5 - Preparation production

Objectif : deployer proprement.

Taches :

1. Nettoyer README et documentation.
2. Nettoyer fichiers parasites.
3. Configurer caches.
4. Configurer queue/mail/storage.
5. Verifier monitoring/logging.
6. Faire un test complet sur une base fraiche.

Critere de sortie :

- Installation reproductible.
- Tests principaux verts.
- Parcours public et authentifie verifies.

## Commandes utiles

Audit routes :

```bash
/opt/homebrew/bin/php artisan route:list --except-vendor
```

Etat migrations :

```bash
/opt/homebrew/bin/php artisan migrate:status
```

Informations application :

```bash
/opt/homebrew/bin/php artisan about
```

Tests complets :

```bash
/opt/homebrew/bin/php artisan test
```

Tests cibles accueil/certifications :

```bash
/opt/homebrew/bin/php artisan test tests/Feature/Livewire/HomePageTest.php tests/Feature/Livewire/CertificationsTest.php
```

Formatage :

```bash
/opt/homebrew/bin/php vendor/bin/pint --dirty
```

## Prochaine action recommandee

Commencer par la phase 1, car elle debloque le reste :

1. Corriger la route `formations-lists`.
2. Relancer les tests dashboard/profil/auth.
3. Ajouter la migration corrective `enrollments`.
4. Relancer les tests paiement/cours/examens.

Ce travail doit etre fait progressivement, avec des tests cibles apres chaque correction, afin d'eviter de masquer plusieurs problemes sous une seule grosse modification.
