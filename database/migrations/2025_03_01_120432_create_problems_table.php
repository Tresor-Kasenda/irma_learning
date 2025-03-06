<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::disableForeignKeyConstraints();
        DB::table('problems')->truncate();
        Schema::enableForeignKeyConstraints();

        $problems = [
            [
                'name' => 'Problèmes d\'accès au compte',
                'description' => 'Problèmes de connexion, mots de passe oubliés ou récupération de compte',
                'active' => true,
            ],
            [
                'name' => 'Échecs de traitement des paiements',
                'description' => 'Problèmes pour finaliser les paiements, les frais d\'abonnement ou les remboursements',
                'active' => true,
            ],
            [
                'name' => 'Erreurs techniques',
                'description' => 'Erreurs inattendues, échecs de chargement de page ou plantages système',
                'active' => true,
            ],
            [
                'name' => 'Fonctionnalités manquantes',
                'description' => 'Fonctionnalités qui semblent indisponibles ou ne fonctionnant pas comme prévu',
                'active' => true,
            ],
            [
                'name' => 'Problèmes de synchronisation des données',
                'description' => 'Problèmes de données n\'apparaissant pas sur tous les appareils ou après les mises à jour',
                'active' => true,
            ],
            [
                'name' => 'Problèmes de performance',
                'description' => 'Temps de chargement lents, latence ou éléments non réactifs',
                'active' => true,
            ],
            [
                'name' => 'Compatibilité mobile',
                'description' => 'Problèmes d\'accès ou d\'utilisation de la plateforme sur appareils mobiles',
                'active' => true,
            ],
            [
                'name' => 'Problèmes de notifications',
                'description' => 'Alertes manquantes, notifications excessives ou problèmes de configuration',
                'active' => true,
            ],
            [
                'name' => 'Problèmes d\'affichage du contenu',
                'description' => 'Problèmes d\'affichage des textes, images ou médias sur la plateforme',
                'active' => true,
            ],
            [
                'name' => 'Préoccupations de sécurité',
                'description' => 'Questions sur la confidentialité des données, accès non autorisés ou activités suspectes',
                'active' => true,
            ],
        ];

        DB::table('problems')->insert($problems);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
