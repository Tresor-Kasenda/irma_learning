## Verdict

La plateforme possède un socle fonctionnel solide, mais elle n’est pas encore prête pour un test marketing complet. Elle
est surtout prête pour des tests techniques internes.

### P0 — Bloqueurs absolus

1. **Implémenter les paiements réels**
    - Stripe et Mobile Money sont simulés.
    - Un paiement est actuellement marqué « payé » sans transaction réelle.
    - Ajouter webhooks, idempotence, statuts `pending/failed/cancelled/paid`, références et reprise après erreur.
    -
   Voir [PaymentController.php](/Users/scott/Movies/PROJETS/irma_learning/app/Http/Controllers/Student/PaymentController.php:46).

2. **Sécuriser les factures**
    - Tout utilisateur authentifié peut potentiellement télécharger la facture d’une autre inscription par son ID.
    - Ajouter une Policy propriétaire/admin.
    -
   Voir [EnrollmentController.php](/Users/scott/Movies/PROJETS/irma_learning/app/Http/Controllers/Student/EnrollmentController.php:20).

3. **Corriger les factures**
    - Adresse fictive à Paris, fausse TVA, euros, PayPal, “Formation Academy”.
    - Remplacer par les vraies informations légales IRMA et la devise réellement utilisée.
    -
   Voir [enrollment.blade.php](/Users/scott/Movies/PROJETS/irma_learning/resources/views/invoices/enrollment.blade.php:197).

4. **Finaliser les certificats**
    - Téléchargement PDF absent.
    - URL publique de vérification absente.
    - Les méthodes retournent actuellement simplement `new`.
    - Ajouter partage, QR code, révocation et expiration.
    - Voir [Certificate.php](/Users/scott/Movies/PROJETS/irma_learning/app/Models/Certificate.php:60).

5. **Terminer les contenus de test**
    - 10 formations actives, mais aucune image de formation.
    - 36 sections sans examen de section.
    - 3 chapitres sans aucun matériel pédagogique.
    - Il faut au minimum une formation gratuite et une payante intégralement finalisées.

6. **Stabiliser le build**
    - Suite actuelle : 179 tests réussis, 10 échecs, 6 ignorés.
    - Les 10 échecs viennent du manifeste Vite qui ne contient pas encore les nouveaux écrans admin.
    - Ajouter `npm run build` dans le pipeline CI avant les tests ou neutraliser Vite dans les tests.
    - Committer les nombreux fichiers actuellement non suivis.

7. **Appliquer réellement les règles utilisateur**
    - Les middlewares de compte suspendu, mot de passe à changer et email vérifié existent, mais ne protègent pas les
      parcours étudiants.
    - Les routes utilisent uniquement `auth`.
    - Voir [web.php](/Users/scott/Movies/PROJETS/irma_learning/routes/web.php:28).

8. **Sécuriser les codes d’accès**
    - Ajouter limitation de tentatives, journalisation et blocage temporaire pour empêcher le brute force.

## P1 — Fonctionnalités indispensables

- Choisir définitivement entre Filament `/admin` et le nouvel admin `/manage`.
- Migrer examens, questions, utilisateurs, inscriptions, certificats, codes et paramètres vers le nouvel admin.
- Connecter un véritable SMTP.
- Envoyer réellement :
    - vérification email ;
    - bienvenue ;
    - confirmation d’inscription/paiement ;
    - facture ;
    - rappel de formation ;
    - certificat.
- Finaliser remboursement réel et historique financier.
- Ajouter reprise d’examen, gestion des coupures et validation serveur du chronomètre.
- Installer le worker de queue pour l’extraction PDF et les emails.
- Préparer Python, Tesseract OCR et Imagick sur le serveur.
- Tester PDF, vidéo et Markdown sur fichiers lourds et corrompus.
- Ajouter support/contact et gestion des demandes utilisateurs.

## Contenu et marketing

- Images de couverture cohérentes pour toutes les formations.
- Titres, descriptions, objectifs, prérequis, public cible et intervenants.
- Programme complet et durée réaliste.
- Prix, devise, taxes et politique de remboursement.
- Pages :
    - À propos ;
    - Contact ;
    - FAQ ;
    - Conditions d’utilisation ;
    - Confidentialité ;
    - Cookies ;
    - Annulation/remboursement.
- Corriger les textes et fautes, notamment « Acceuil ».
- Ajouter SEO : descriptions, Open Graph, images sociales, canonical, sitemap.
- Ajouter analytics et événements :
    - consultation formation ;
    - clic inscription ;
    - début/échec/succès paiement ;
    - démarrage et complétion ;
    - abandon.
- Prévoir UTM et attribution des campagnes.

Le `<head>` ne contient actuellement que le titre et le
viewport : [app.blade.php](/Users/scott/Movies/PROJETS/irma_learning/resources/views/app.blade.php:3).

## UX, accessibilité et QA

- Vérifier mobile, tablette et desktop.
- Tester Chrome, Safari, Firefox et Edge.
- Navigation clavier, focus visible, contraste, lecteur d’écran.
- États chargement, vide, erreur et hors connexion.
- Tester les parcours complets :
    - visiteur → inscription ;
    - formation gratuite ;
    - formation payante ;
    - code d’accès ;
    - progression ;
    - examen réussi/échoué ;
    - certificat ;
    - remboursement.
- Créer des comptes UAT dédiés : visiteur, étudiant, admin et compte suspendu.
- Ajouter tests E2E navigateur ; il n’existe actuellement que des tests PHP/Vue statiques.
- Compléter les 6 tests PDF actuellement ignorés.

## Production et exploitation

- Staging proche de la production.
- `APP_DEBUG=false`, HTTPS, cookies sécurisés, vraies URLs et vraies clés.
- Stockage persistant/S3 et sauvegardes restaurables.
- Worker supervisé et cron Laravel.
- CI/CD : installation, migrations, build, tests, déploiement et rollback.
- Monitoring, alertes erreurs, queues échouées et espace disque.
- Politique de sauvegarde DB/médias.
- CDN et optimisation des images/vidéos.
- Tests de charge.
- En-têtes CSP/HSTS et audit des permissions.
- Le scan actuel des dépendances PHP et npm ne signale aucune vulnérabilité connue.

## Ordre recommandé

1. Stabiliser build, tests et sécurité.
2. Paiement réel, factures et certificats.
3. Emails et environnement staging.
4. Compléter une formation gratuite et une payante.
5. SEO, analytics, pages légales et contenu.
6. QA fonctionnelle et visuelle.
7. Donner ensuite l’accès à l’équipe marketing.

Le prochain chantier recommandé est donc la phase P0 : sécurité des factures, build vert et finalisation du paiement.

User: lasogsyh_youness Database: lasogsyh_formation_btpcma


