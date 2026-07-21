<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\FormationDetailTool;
use App\Mcp\Tools\MyCertificatesTool;
use App\Mcp\Tools\MyLearningProgressTool;
use App\Mcp\Tools\MyNextLearningStepTool;
use App\Mcp\Tools\SearchFormationsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('IRMA Learning')]
#[Version('1.0.0')]
#[Instructions('Ce serveur fournit uniquement des informations de formation et de progression à l’utilisateur authentifié. Il ne permet aucune inscription, modification, soumission d’examen ou accès aux contenus pédagogiques protégés.')]
final class LearningServer extends Server
{
    /**
     * @var array<int, class-string<Server\Tool>>
     */
    protected array $tools = [
        SearchFormationsTool::class,
        FormationDetailTool::class,
        MyLearningProgressTool::class,
        MyNextLearningStepTool::class,
        MyCertificatesTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
