<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ChapterTypeEnum;
use App\Enums\FormationLevelEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Question;
use App\Models\Section;
use Illuminate\Database\Seeder;

final class AiContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->creerFormationIA();
    }

    private function section(Formation $formation, int $position, string $title, int $duration): Section
    {
        return $formation->sections()->updateOrCreate(
            ['title' => $title],
            [
                'description' => 'Module '.$position.' de la formation.',
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

    private function creerFormationIA(): void
    {
        $formation = Formation::updateOrCreate(
            ['slug' => 'ia-agents-autonomes-protocole-mcp'],
            [
                'title' => 'Intelligence Artificielle, Agents Autonomes & Protocole MCP',
                'short_description' => 'Maîtrisez les fondamentaux de l\'IA générative, concevez des agents autonomes et implémentez des serveurs MCP avec Laravel.',
                'description' => "## Objectifs de la formation\n\nComprendre et maîtriser l'écosystème moderne de l'intelligence artificielle :\n\n- **Fondamentaux des LLMs** — Architecture Transformer, tokenisation, mécanismes d'attention.\n- **RAG et embeddings** — Recherche sémantique, vectorisation et augmentation contextuelle.\n- **Agents autonomes** — Conception d'agents avec outils, mémoire et planification.\n- **Model Context Protocol (MCP)** — Création de serveurs MCP avec Laravel, exposition d'outils et de ressources.\n- **Déploiement sécurisé** — Authentification, rate limiting, monitoring et intégration avec les clients MCP.\n\nÀ l'issue de cette formation, vous serez capable de développer des solutions IA complètes, des chatbots RAG aux serveurs MCP interopérables.",
                'price' => 0,
                'duration_hours' => 60,
                'difficulty_level' => FormationLevelEnum::INTERMEDIATE,
                'is_certifying' => true,
                'is_active' => true,
                'is_featured' => false,
                'tags' => ['IA', 'Intelligence Artificielle', 'MCP', 'Agents', 'LLM', 'Laravel', 'RAG', 'Automatisation'],
            ],
        );

        $s1 = $this->section($formation, 1, 'Fondamentaux de l\'IA Générative', 15);
        $s2 = $this->section($formation, 2, 'Architecture des Agents Autonomes', 15);
        $s3 = $this->section($formation, 3, 'Model Context Protocol (MCP)', 20);
        $s4 = $this->section($formation, 4, 'Déploiement, Sécurité & Intégration', 10);

        $s1c1 = $this->chapitre($s1,
            'Introduction aux Grands Modèles de Langage (LLMs)',
            'Comprendre le fonctionnement interne des LLMs, leur entraînement et leurs capacités.',
            "# Introduction aux Grands Modèles de Langage (LLMs)\n\n## Qu'est-ce qu'un LLM ?\n\nUn **Large Language Model (LLM)** est un réseau de neurones entraîné sur des quantités massives de texte. Il apprend à prédire le mot suivant dans une séquence, ce qui lui permet de générer du texte cohérent, de résumer, de traduire et de raisonner.\n\n## Architecture Transformer\n\nL'architecture **Transformer** (Vaswani et al., 2017) est le fondement de tous les LLMs modernes. Ses composants clés :\n\n- **Self-attention** — Permet au modèle de pondérer l'importance de chaque mot par rapport aux autres.\n- **Multi-head attention** — Plusieurs têtes d'attention capturent différentes relations linguistiques.\n- **Feed-forward layers** — Couches de transformation non-linéaires.\n- **Positional encoding** — Ajoute une information de position dans la séquence.\n\n## Tokenisation\n\nLes LLMs ne lisent pas du texte brut mais des **tokens** :\n- Un token peut être un mot, une partie de mot ou un caractère.\n- GPT-4 utilise un vocabulaire d'environ 100 000 tokens.\n- La tokenisation impacte le coût et la latence (entrée/sortie comptée en tokens).\n\n## Entraînement\n\n1. **Pre-training** — Apprentissage non supervisé sur des téraoctets de texte (coût : plusieurs millions de dollars).\n2. **Fine-tuning supervisé (SFT)** — Ajustement sur des paires question/réponse.\n3. **RLHF (Reinforcement Learning from Human Feedback)** — Alignement avec les préférences humaines.\n\n## Modèles populaires (2024-2026)\n\n| Modèle | Créateur | Particularité |\n|--------|----------|---------------|\n| GPT-4o / o3 | OpenAI | Multimodal, raisonnement avancé |\n| Claude 3.5 / 4 | Anthropic | Long contexte, sécurité, MCP natif |\n| Gemini 2.0 | Google | Intégration Google, multimodal |\n| Llama 3 / 4 | Meta (Mozilla) | Open source, déployable on-premise |\n| DeepSeek | DeepSeek | Performance coût, open-weight |\n| Mistral / Mixtral | Mistral AI | Efficacité, français, open-source |\n\n## Capacités clés\n\n- Génération de texte et code\n- Résumé et extraction\n- Traduction\n- Analyse de sentiment\n- Raisonnement multi-étapes (chain-of-thought)\n- Apprentissage en contexte (few-shot, zero-shot)",
            25, 1, true);

        $s1c2 = $this->chapitre($s1,
            'Embeddings, Vectorisation & RAG',
            'Comprenez comment transformer le texte en vecteurs et implémenter un système RAG.',
            "# Embeddings, Vectorisation & RAG\n\n## Qu'est-ce qu'un embedding ?\n\nUn **embedding** est une représentation vectorielle (liste de nombres flottants) d'un texte, d'une image ou d'un son. Deux textes sémantiquement proches auront des embeddings proches dans l'espace vectoriel.\n\n## Modèles d'embeddings\n\n- **OpenAI** (`text-embedding-3-small`, `text-embedding-3-large`) — 512 à 3072 dimensions.\n- **Voyage AI** — Optimisé pour le RAG.\n- **Cohere** — Multilingue.\n- **Sentence Transformers** — Open source, déployable localement.\n\n## Mesures de similarité\n\n- **Cosinus** — Mesure l'angle entre deux vecteurs (le plus utilisé).\n- **Produit scalaire** — Pour les vecteurs normalisés.\n- **Distance euclidienne** — Distance géométrique.\n\n## Vector Database\n\nUne base vectorielle stocke et indexe les embeddings pour une recherche rapide :\n\n| Solution | Type | Particularité |\n|----------|------|---------------|\n| pgvector | Extension Postgres | Pas de stack supplémentaire |\n| Qdrant | Dédiée | Performante, open-source |\n| Milvus | Dédiée | Distribuée, scale |\n| Chroma | Embarquée | Simple, pour le développement |\n| Pinecone | SaaS | Clé en main, gérée |\n\n## RAG (Retrieval-Augmented Generation)\n\nLe RAG combine recherche documentaire et génération LLM :\n\n1. **Indexation** : Documents → chunks → embeddings → vector DB.\n2. **Requête** : Question de l'utilisateur → embedding → recherche des chunks similaires.\n3. **Augmentation** : Les chunks pertinents sont injectés dans le prompt.\n4. **Génération** : Le LLM répond en se basant sur le contexte fourni.\n\n```\nQuestion → [Embedding] → [Vector DB] → Contexte\n                                            ↓\n                                   [Prompt final] → LLM → Réponse\n```\n\n## Implémentation pratique avec Laravel\n\n```php\nuse OpenAI\\Laravel\\Facades\\OpenAI;\n\n$response = OpenAI::embeddings()->create([\n    'model' => 'text-embedding-3-small',\n    'input' => $texte,\n]);\n\n$embedding = $response->embeddings[0]->embedding;\n```\n\nPour la recherche :\n\n```php\n$questionEmbedding = OpenAI::embeddings()->create([\n    'model' => 'text-embedding-3-small',\n    'input' => $question,\n])->embeddings[0]->embedding;\n\n// Avec pgvector\n$chunks = Chunk::query()\n    ->orderByRaw('embedding <=> ?', [$questionEmbedding])\n    ->limit(5)\n    ->get();\n```",
            30, 2, true);

        $s1c3 = $this->chapitre($s1,
            'Ingénierie des Prompts & Fine-Tuning',
            'Maîtrisez les techniques avancées de prompt engineering et les stratégies d\'adaptation des modèles.',
            "# Ingénierie des Prompts & Fine-Tuning\n\n## Prompt Engineering — Principes fondamentaux\n\n### Structure d'un prompt efficace\n\n1. **Rôle** — Définissez le persona du modèle.\n2. **Contexte** — Fournissez le cadre et les contraintes.\n3. **Tâche** — Décrivez précisément ce qui est attendu.\n4. **Format** — Spécifiez le format de sortie (JSON, markdown, etc.).\n5. **Exemples (few-shot)** — Montrez des exemples réussis.\n\n### Techniques avancées\n\n- **Chain-of-Thought (CoT)** : Demandez au modèle de raisonner étape par étape.\n- **Tree-of-Thought (ToT)** : Explorez plusieurs chemins de raisonnement en parallèle.\n- **ReAct** : Raisonnement + Action (le modèle décide s'il doit répondre ou utiliser un outil).\n- **Self-Consistency** : Générez plusieurs réponses et votez.\n\n## Fine-tuning\n\nLe **fine-tuning** adapte un LLM pré-entraîné à une tâche spécifique avec un petit jeu de données.\n\n### Quand fine-tuner ?\n\n| Situation | Solution recommandée |\n|-----------|---------------------|\n| Connaissances spécifiques à un domaine | RAG (moins coûteux) |\n| Format de sortie spécifique | Prompt engineering |\n| Style / ton particulier | Few-shot prompting |\n| Tâche répétitive et bien définie | Fine-tuning |\n| Volume important (>1000 exemples) | Fine-tuning |\n\n### Types de fine-tuning\n\n- **Full fine-tuning** — Tous les poids sont mis à jour (coûteux).\n- **LoRA (Low-Rank Adaptation)** — Ajuste des matrices de faible rang, peu de paramètres.\n- **QLoRA** — LoRA + quantification 4-bit, exécutable sur GPU grand public.\n\n## Bonnes pratiques\n\n- **Testez systématiquement** : Un petit changement de formulation peut tout changer.\n- **Utilisez des templates** : Centralisez vos prompts (ex: `app/Prompts/`).\n- **Loggez les entrées/sorties** : Pour debug et amélioration continue.\n- **Évaluez avec des métriques** : Exactitude, pertinence, fluidité, sécurité.",
            25, 3);

        $this->exam($s1, 'Évaluation — Fondamentaux de l\'IA', [
            ['Quel mécanisme est au cœur de l\'architecture Transformer ?', QuestionTypeEnum::SINGLE_CHOICE, ['Self-attention', 'Convolution', 'RNN', 'LSTM'], [0]],
            ['Quels sont les avantages du RAG par rapport au fine-tuning ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Pas de réentraînement', 'Données à jour', 'Moins coûteux', 'Nécessite GPU'], [0, 1, 2]],
            ['Un embedding transforme un texte en vecteur numérique.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);

        $s2c1 = $this->chapitre($s2,
            'Concepts fondamentaux des Agents Autonomes',
            'Découvrez ce qu\'est un agent IA, ses composants et son cycle de vie.',
            "# Concepts fondamentaux des Agents Autonomes\n\n## Qu'est-ce qu'un agent IA ?\n\nUn **agent IA** est un système qui perçoit son environnement, prend des décisions et exécute des actions de manière autonome pour atteindre un objectif.\n\n## Composants d'un agent\n\n1. **Modèle (LLM)** — Le cerveau, prend les décisions.\n2. **Outils (Tools)** — Capacités d'interaction avec le monde extérieur.\n3. **Mémoire** — Stocke le contexte et l'historique.\n4. **Planification** — Décompose les objectifs en étapes.\n5. **Boucle perception-action** — Observe → Raisonne → Agit.\n\n## Types d'agents\n\n| Type | Description | Exemple |\n|------|-------------|---------|\n| Simple reflex | Répond à des conditions prédéfinies | Chatbot FAQ |\n| Goal-based | Planifie pour atteindre un objectif | Assistant de réservation |\n| Utility-based | Optimise selon une fonction d'utilité | Trading bot |\n| Multi-agent | Plusieurs agents collaborent | Équipe d'assistants |\n\n## Architecture d'un agent\n\n```\n[Utilisateur] → [Orchestrateur / LLM]\n                          |\n        ┌─────────────────┼─────────────────┐\n        ↓                 ↓                 ↓\n   [Tool Search]   [Tool Calculator]   [Tool Database]\n        ↓                 ↓                 ↓\n        └─────────────────┼─────────────────┘\n                          ↓\n                   [Mémoire / Historique]\n                          ↓\n                   [Réponse finale]\n```\n\n## Boucle agentique (ReAct)\n\n1. **Thought** : Le modèle réfléchit à ce qu'il doit faire.\n2. **Action** : Il appelle un outil (API, base de données, calcul).\n3. **Observation** : Il reçoit le résultat de l'outil.\n4. **Répète** jusqu'à avoir assez d'informations pour répondre.\n5. **Answer** : Il produit la réponse finale.",
            25, 1);

        $s2c2 = $this->chapitre($s2,
            'Outils, Mémoire & Planification',
            'Apprenez à doter vos agents d\'outils, de mémoire persistante et de capacités de planification.',
            "# Outils, Mémoire & Planification\n\n## Créer des outils (Tools)\n\nUn outil est une fonction que l'agent peut invoquer. Chaque outil a :\n- Un **nom** unique\n- Une **description** (utilisée par le LLM pour choisir l'outil)\n- Un **schéma d'entrée** (paramètres attendus)\n- Une **implémentation** (le code exécuté)\n\n### Exemple d'outil métier\n\n```php\nclass SearchFormationsTool extends Tool\n{\n    public function handle(Request $request): Response\n    {\n        // Validation, logique, retour\n    }\n\n    public function schema(JsonSchema $schema): array\n    {\n        return [\n            'query' => $schema->string()\n                ->description('Recherche dans le catalogue'),\n        ];\n    }\n}\n```\n\n## Mémoire agentique\n\n| Type | Portée | Persistance | Usage |\n|------|--------|-------------|-------|\n| Court terme | Session | Volatile | Historique de conversation |\n| Long terme | Multi-session | Base de données | Préférences utilisateur |\n| Procédurale | Permanente | Code | Compétences fixes |\n| Épisodique | Multi-session | Vector DB | Expériences passées |\n\n## Planification\n\n### Stratégies\n\n- **Planification hiérarchique** : Objectif → sous-objectifs → actions.\n- **Planification dynamique** : Ajuste le plan en fonction des observations.\n- **Monte-Carlo Tree Search** : Explore plusieurs chemins, choisit le meilleur.\n\n### Prompt de planification\n\n```\nObjectif : {description}\nContexte : {état_actuel}\n\nÉlabore un plan étape par étape pour atteindre cet objectif.\nPour chaque étape, indique :\n1. L'action à réaliser\n2. L'outil à utiliser\n3. Le critère de succès\n```",
            30, 2);

        $s2c3 = $this->chapitre($s2,
            'Frameworks d\'Orchestration & Agents Multi-systèmes',
            'Explorez les frameworks LangChain, CrewAI et les architectures multi-agents.',
            "# Frameworks d'Orchestration & Agents Multi-systèmes\n\n## LangChain\n\n**LangChain** est le framework le plus populaire pour développer des applications LLM.\n\n### Composants clés\n\n- **Models** — Interface unifiée pour tous les LLMs.\n- **Prompts** — Templates et gestion des prompts.\n- **Chains** — Séquence d'appels LLM et d'outils.\n- **Agents** — Boucle décisionnelle avec outils.\n- **Memory** — Gestion de l'historique.\n- **Callbacks** — Logging, monitoring, tracing.\n\n### Exemple d'agent LangChain\n\n```python\nfrom langchain.agents import Tool, AgentExecutor, create_react_agent\nfrom langchain_openai import ChatOpenAI\n\ntools = [\n    Tool(name=\"search\", func=search_formation, description=\"...\"),\n    Tool(name=\"calculator\", func=calculer, description=\"...\"),\n]\n\nllm = ChatOpenAI(model=\"gpt-4o\")\nagent = create_react_agent(llm, tools, prompt)\nagent_executor = AgentExecutor(agent=agent, tools=tools)\n```\n\n## CrewAI\n\n**CrewAI** orchestre des équipes d'agents spécialisés.\n\n```python\nfrom crewai import Agent, Task, Crew\n\nchercheur = Agent(role=\"Chercheur de formations\", ...)\nredacteur = Agent(role=\"Rédacteur pédagogique\", ...)\n\ncrew = Crew(agents=[chercheur, redacteur], tasks=[...])\nresult = crew.kickoff()\n```\n\n## Architecture multi-agents\n\n| Pattern | Description | Cas d'usage |\n|---------|-------------|-------------|\n| Supervisor | Un agent coordonne les autres | Support client |\n| Sequential | Les agents travaillent en pipeline | Traitement document |\n| Debate | Les agents débattent pour améliorer | Prise de décision |\n| RAG Multi-agent | Chaque agent a sa propre source | Recherche d'information |\n\n## Laravel + LLM : packages utiles\n\n- **openai-php/laravel** — Client OpenAI officiel.\n- **llama-cpp/laravel** — Exécution locale de modèles.\n- **echo-labs/laravel-mcp** — Serveur MCP natif Laravel (utilisé sur cette plateforme).",
            30, 3);

        $this->exam($s2, 'Évaluation — Agents Autonomes', [
            ['Quels sont les composants essentiels d\'un agent IA ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Modèle (LLM)', 'Outils', 'Base de données SQL', 'Mémoire'], [0, 1, 3]],
            ['Dans la boucle ReAct, que signifie l\'étape "Observation" ?', QuestionTypeEnum::SINGLE_CHOICE, ['Analyser le résultat de l\'outil', 'Envoyer la réponse', 'Planifier les étapes', 'Choisir un outil'], [0]],
            ['La mémoire à long terme est stockée dans une base vectorielle.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);

        $s3c1 = $this->chapitre($s3,
            'Introduction au Model Context Protocol (MCP)',
            'Comprenez le protocole MCP, son architecture et ses cas d\'usage.',
            "# Introduction au Model Context Protocol (MCP)\n\n## Qu'est-ce que MCP ?\n\nLe **Model Context Protocol (MCP)** est un protocole ouvert développé par Anthropic qui standardise la communication entre les modèles d'IA (LLMs) et les sources de données ou services externes.\n\n## Pourquoi MCP ?\n\nAvant MCP, chaque intégration IA était artisanale :\n- API REST personnalisée par service.\n- Pas de standard pour la découverte de capacités.\n- Chaque client devait connaître l'interface de chaque serveur.\n\nMCP apporte :\n- **Standardisation** — Tous les serveurs parlent le même langage.\n- **Découverte** — Le client découvre automatiquement les outils disponibles.\n- **Sécurité** — Authentification et permissions intégrées.\n- **Interopérabilité** — Un serveur MCP fonctionne avec tout client MCP.\n\n## Architecture MCP\n\n```\n┌──────────────┐     JSON-RPC 2.0      ┌──────────────┐\n│   Client     │ ◄───────────────►    │   Serveur    │\n│  (Claude,    │    (HTTP/SSE)        │  (Laravel,    │\n│   Cursor,    │                      │   Python,     │\n│   OpenCode)  │                      │   Node.js)    │\n└──────────────┘                      └──────────────┘\n```\n\n## Concepts clés\n\n### Tools (Outils)\nFonctions exécutables que le modèle peut invoquer :\n- Déclarés avec un nom, une description et un schéma JSON.\n- Le modèle décide quand les utiliser.\n- Idempotents ou non, read-only ou mutables.\n\n### Resources (Ressources)\nDonnées exposées en lecture :\n- Fichiers, documents, contenu de base de données.\n- Identifiées par une URI (`learning://formation/1`).\n\n### Prompts\nTemplates de prompts prêts à l'emploi :\n- Scénarios d'utilisation pré-définis.\n- Arguments personnalisables.\n\n## Transports\n\n| Transport | Usage | Description |\n|-----------|-------|-------------|\n| HTTP POST | Standard | Requête/réponse unique |\n| SSE (Server-Sent Events) | Streaming | Réponses en continu |\n| STDIO | Local | Communication via entrée/sortie standard |\n\n## MCP vs API REST\n\n| Aspect | REST | MCP |\n|--------|------|-----|\n| Découverte | Documentation externe | Schema intégré |\n| Typage | Souvent informel | JSON Schema strict |\n| Authentification | Variable | Standardisée |\n| Idempotence | Non garantie | Annotations explicites |\n| Versionnement | Header ou URL | Intégré au protocole |",
            30, 1, true);

        $s3c2 = $this->chapitre($s3,
            'Créer un Serveur MCP avec Laravel',
            'Apprenez à implémenter un serveur MCP complet avec le package Laravel MCP.',
            "# Créer un Serveur MCP avec Laravel\n\n## Installation\n\n```bash\ncomposer require laravel/mcp\n```\n\n## Créer le serveur\n\n```php\nuse Laravel\\Mcp\\Server;\nuse Laravel\\Mcp\\Server\\Attributes\\Instructions;\nuse Laravel\\Mcp\\Server\\Attributes\\Name;\nuse Laravel\\Mcp\\Server\\Attributes\\Version;\n\n#[Name('Mon Serveur')]\n#[Version('1.0.0')]\n#[Instructions('Description du serveur')]\nfinal class MonServeur extends Server\n{\n    protected array $tools = [\n        MonOutil::class,\n    ];\n}\n```\n\n## Créer un outil\n\n```php\nuse Laravel\\Mcp\\Response;\nuse Laravel\\Mcp\\Server\\Tool;\nuse Laravel\\Mcp\\Server\\Attributes\\Description;\nuse Laravel\\Mcp\\Server\\Tools\\Annotations\\IsReadOnly;\n\n#[Description('Description de l\\'outil')]\n#[IsReadOnly]\nfinal class MonOutil extends Tool\n{\n    public function handle(Request $request): Response\n    {\n        $args = $request->validate([\n            'param' => ['required', 'string'],\n        ]);\n\n        // Logique métier\n        $result = ...;\n\n        return Response::structured($result);\n    }\n\n    public function schema(JsonSchema $schema): array\n    {\n        return [\n            'param' => $schema->string()\n                ->description('Description du paramètre'),\n        ];\n    }\n}\n```\n\n## Enregistrer la route\n\n```php\nuse Laravel\\Mcp\\Facades\\Mcp;\n\nMcp::web('/mcp/mon-serveur', MonServeur::class)\n    ->middleware(['auth:sanctum', 'throttle:60,1']);\n```\n\n## Authentification\n\nLe serveur MCP utilise Laravel Sanctum :\n\n```php\n// Créer un token\n$token = $user->createToken('mcp-client', ['mcp:read']);\n\n// Dans le client (HTTP Header)\nAuthorization: Bearer {$token->plainTextToken}\n```\n\n## Middleware personnalisé\n\n```php\nMcp::web('/mcp/learning', LearningServer::class)\n    ->middleware([\n        'auth:sanctum',\n        'abilities:mcp:read',\n        'throttle:60,1',\n    ]);\n```\n\n## Bonnes pratiques\n\n- Un outil = une responsabilité (principe SRP).\n- Validez toujours les entrées avec `$request->validate()`.\n- Utilisez `#[IsReadOnly]` pour les outils sans effet de bord.\n- Documentez chaque paramètre dans `schema()`.\n- Structurez les réponses complexes avec `Response::structured()`.\n- Ajoutez des middlewares de throttling et d'authentification.",
            35, 2);

        $s3c3 = $this->chapitre($s3,
            'Connecter et Consommer un Serveur MCP',
            'Configurez Claude Desktop, Cursor et d\'autres clients MCP pour utiliser votre serveur.',
            "# Connecter et Consommer un Serveur MCP\n\n## Configuration Claude Desktop\n\n```json\n{\n  \"mcpServers\": {\n    \"irma-learning\": {\n      \"url\": \"https://formation.example.com/mcp/learning\",\n      \"headers\": {\n        \"Authorization\": \"Bearer votre_token_sanctum\"\n      }\n    }\n  }\n}\n```\n\n## Configuration Cursor\n\nDans **Cursor Settings → MCP Servers → Add Server** :\n\n```json\n{\n  \"name\": \"IRMA Learning\",\n  \"transport\": \"http\",\n  \"url\": \"https://formation.example.com/mcp/learning\",\n  \"headers\": {\n    \"Authorization\": \"Bearer votre_token_sanctum\"\n  }\n}\n```\n\n## Configuration OpenCode (CLI)\n\n```json\n{\n  \"mcpServers\": {\n    \"irma-learning\": {\n      \"url\": \"https://formation.example.com/mcp/learning\",\n      \"headers\": {\n        \"Authorization\": \"Bearer votre_token_sanctum\"\n      }\n    }\n  }\n}\n```\n\n## Communication directe avec curl\n\n### Initialisation\n\n```bash\ncurl -X POST \"https://formation.example.com/mcp/learning\" \\\n  -H \"Authorization: Bearer token\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\n    \"jsonrpc\": \"2.0\",\n    \"id\": 1,\n    \"method\": \"initialize\",\n    \"params\": {\n      \"protocolVersion\": \"2024-11-05\",\n      \"capabilities\": {},\n      \"clientInfo\": {\"name\": \"MyApp\", \"version\": \"1.0\"}\n    }\n  }'\n```\n\n### Liste des outils\n\n```bash\ncurl -X POST ... -d '{\n  \"jsonrpc\": \"2.0\",\n  \"id\": 2,\n  \"method\": \"tools/list\"\n}'\n```\n\n### Appeler un outil\n\n```bash\ncurl -X POST ... -d '{\n  \"jsonrpc\": \"2.0\",\n  \"id\": 3,\n  \"method\": \"tools/call\",\n  \"params\": {\n    \"name\": \"nom-outil\",\n    \"arguments\": {\"param\": \"valeur\"}\n  }\n}'\n```\n\n## Dépannage\n\n| Symptôme | Cause possible | Solution |\n|----------|---------------|----------|\n| `Unauthenticated` | Token invalide/expiré | Régénérer le token |\n| `Not Found` | Mauvais chemin | Vérifier l'URL de la route |\n| `Method Not Allowed` | GET au lieu de POST | Utiliser POST |\n| WAF bloque | Pare-feu serveur | Ajouter une exception |\n| Timeout | Pas de réponse | Vérifier le serveur Laravel |\n| `Internal error` | Exception PHP | Consulter les logs Laravel |",
            25, 3);

        $s3c4 = $this->chapitre($s3,
            'Ressources, Prompts & Fonctionnalités Avancées MCP',
            'Explorez les fonctionnalités avancées de MCP : ressources, prompts templates et notifications.',
            "# Ressources, Prompts & Fonctionnalités Avancées MCP\n\n## Resources MCP\n\nLes **resources** exposent des données structurées au client MCP.\n\n```php\nuse Laravel\\Mcp\\Server\\Resource;\n\nfinal class FormationResource extends Resource\n{\n    public function uri(): string\n    {\n        return 'learning://formation/{id}';\n    }\n\n    public function handle(Request $request): Response\n    {\n        $formation = Formation::find($request->route('id'));\n\n        return Response::structured($formation->toArray());\n    }\n}\n```\n\n## Prompts templates\n\nLes **prompts** sont des templates réutilisables que le client peut proposer à l'utilisateur.\n\n```php\nuse Laravel\\Mcp\\Server\\Prompt;\n\nfinal class ResumeFormationPrompt extends Prompt\n{\n    public function handle(Request $request): Response\n    {\n        $arguments = $request->validate([\n            'formation_id' => ['required', 'integer'],\n        ]);\n\n        return Response::structured([\n            'messages' => [\n                [\n                    'role' => 'user',\n                    'content' => [\n                        'type' => 'text',\n                        'text' => \"Résume la formation n°{$arguments['formation_id']} en 3 phrases.\",\n                    ],\n                ],\n            ],\n        ]);\n    }\n}\n```\n\n## Notifications (Server-Sent Events)\n\nAvec le transport SSE, le serveur peut notifier le client en temps réel :\n\n- `notifications/tools/list_changed` — La liste des outils a changé.\n- `notifications/resources/list_changed` — Les ressources ont changé.\n\n## Pagination\n\nPour les listes volumineuses, MCP supporte le curseur de pagination :\n\n```php\nreturn Response::structured([\n    'tools' => $tools,\n    'nextCursor' => $hasMore ? base64_encode((string) $lastId) : null,\n]);\n```\n\n## Logging\n\nMCP définit un niveau de log standardisé :\n\n```php\nuse Laravel\\Mcp\\Response;\n\nreturn Response::structured($data)\n    ->withNotification('logging/message', [\n        'level' => 'info',\n        'data' => 'Outil exécuté avec succès',\n    ]);\n```\n\n## Bonnes pratiques avancées\n\n- Versionnez votre protocole pour gérer les évolutions.\n- Utilisez les annotations `#[IsIdempotent]` pour les outils sans effet de bord.\n- Structurez les données complexes avec `Response::structured()`.\n- Ajoutez des instructions claires dans `#[Instructions]`.",
            30, 4);

        $this->exam($s3, 'Évaluation — Protocole MCP', [
            ['Quel protocole de communication MCP utilise-t-il ?', QuestionTypeEnum::SINGLE_CHOICE, ['JSON-RPC 2.0', 'REST', 'GraphQL', 'SOAP'], [0]],
            ['Quels transports MCP sont disponibles ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['HTTP POST', 'SSE', 'WebSocket', 'STDIO'], [0, 1, 3]],
            ['Un outil MCP peut être déclaré lecture seule avec #[IsReadOnly].', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);

        $s4c1 = $this->chapitre($s4,
            'Sécurité & Authentification des Serveurs MCP',
            'Sécurisez vos serveurs MCP avec Sanctum, permissions et rate limiting.',
            "# Sécurité & Authentification des Serveurs MCP\n\n## Authentification avec Laravel Sanctum\n\n```php\nRoute::post('/mcp/learning', [McpController::class, '__invoke'])\n    ->middleware('auth:sanctum');\n```\n\n## Permissions par capacité (Ability)\n\n```php\n$token = $user->createToken('mcp-client', ['mcp:read']);\n\n// Vérification dans le middleware\nRoute::post('/mcp/learning', ...)\n    ->middleware(['auth:sanctum', 'abilities:mcp:read']);\n```\n\n## Vérification du statut utilisateur\n\n```php\nfinal class EnsureMcpUserIsActive\n{\n    public function handle(Request $request, Closure $next): Response\n    {\n        if ($request->user()->status !== UserStatusEnum::ACTIVE) {\n            return response()->json([\n                'message' => 'Compte désactivé.',\n            ], 403);\n        }\n\n        return $next($request);\n    }\n}\n```\n\n## Rate Limiting\n\n```php\nRoute::post('/mcp/learning', ...)\n    ->middleware(['auth:sanctum', 'throttle:60,1']);\n```\n\n## Validation des entrées\n\nToujours valider les arguments des outils :\n\n```php\n$args = $request->validate([\n    'query' => ['nullable', 'string', 'max:100'],\n    'id' => ['required', 'integer', 'min:1', 'exists:formations,id'],\n]);\n```\n\n## Bonnes pratiques\n\n- **Tokens dédiés** : Créez des tokens avec des capacités restreintes (`mcp:read`).\n- **Expiration** : Définissez une durée de vie pour les tokens.\n- **Rate limit** : Limitez le nombre d'appels par minute.\n- **Logging** : Tracez les appels MCP pour l'audit.\n- **Validation** : Validez TOUJOURS les paramètres d'entrée.\n- **Pas d'effet de bord** : Utilisez `#[IsReadOnly]` quand c'est possible.",
            20, 1);

        $s4c2 = $this->chapitre($s4,
            'Intégration avec les Écosystèmes (Claude, Cursor, OpenCode)',
            'Déployez et intégrez vos serveurs MCP avec les principaux clients du marché.',
            "# Intégration avec les Écosystèmes\n\n## Claude Desktop\n\nFichier de configuration :\n- **macOS** : `~/Library/Application Support/Claude/claude_desktop_config.json`\n- **Windows** : `%APPDATA%\\Claude\\claude_desktop_config.json`\n\n```json\n{\n  \"mcpServers\": {\n    \"mon-serveur\": {\n      \"url\": \"https://api.example.com/mcp/mon-serveur\",\n      \"headers\": {\n        \"Authorization\": \"Bearer token_ici\"\n      }\n    }\n  }\n}\n```\n\nClaude Desktop découvre automatiquement les outils, ressources et prompts exposés.\n\n## Cursor IDE\n\nDans Cursor, les serveurs MCP apparaissent comme des outils supplémentaires dans l'interface de l'agent IA. Ils permettent au modèle de Cursor d'interagir avec vos API et bases de données métier.\n\n## OpenCode CLI\n\nOutil en ligne de commande qui supporte également MCP.\n\n## Cas d'usage métier\n\n| Secteur | Exemple MCP |\n|---------|------------|\n| Formation | Catalogue, progression, certificats |\n| E-commerce | Catalogue produits, commandes, stocks |\n| Support client | Base de connaissance, tickets, FAQ |\n| Développement | Documentation, déploiement, monitoring |\n| Finance | Transactions, rapports, conformité |\n\n## Déploiement\n\n```bash\n# Sur un serveur mutualisé (o2switch, Namecheap)\ngit pull\nphp artisan optimize\n\n# Avec Docker\ncompose up -d\n\n# Vérification\ncurl -X POST https://domaine.com/mcp/mon-serveur \\\n  -H \"Authorization: Bearer token\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"jsonrpc\":\"2.0\",\"id\":1,\"method\":\"initialize\",\"params\":{}}'\n```",
            20, 2);

        $s4c3 = $this->chapitre($s4,
            'Projet Pratique : Assistant IA pour Formation',
            'Mettez en pratique toutes les compétences acquises dans un projet complet.',
            "# Projet Pratique : Assistant IA pour Formation\n\n## Objectif\n\nCréer un assistant IA qui aide les apprenants à naviguer dans leur parcours de formation.\n\n## Fonctionnalités\n\n1. **Recherche de formations** — Trouver une formation par mot-clé.\n2. **Détail de formation** — Afficher le programme complet.\n3. **Progression personnelle** — Consulter sa progression.\n4. **Prochaine étape** — Savoir quoi faire ensuite.\n5. **Certificats** — Obtenir ses certificats.\n\n## Architecture\n\n```\n[Apprenant] → [Claude/Cursor] → [MCP Server Laravel] → [Base de données]\n                                  ↓\n                            Outils :\n                            - search_formations\n                            - formation_detail\n                            - my_progress\n                            - next_step\n                            - my_certificates\n```\n\n## Étapes de réalisation\n\n1. **Créer le serveur Laravel** avec le package `laravel/mcp`.\n2. **Définir les outils** : un par fonctionnalité.\n3. **Configurer l'authentification** avec Sanctum.\n4. **Déployer** sur le serveur de production.\n5. **Connecter Claude Desktop** via la configuration JSON.\n6. **Tester** chaque outil avec curl.\n7. **Itérer** : améliorer les descriptions, ajouter des prompts.\n\n## Résultat attendu\n\nUn apprenant peut demander en langage naturel :\n> \"Quelle est ma prochaine formation à faire ?\"\n> \"Montre-moi le contenu du chapitre 2 de la maçonnerie\"\n> \"Quels certificats ai-je obtenus ?\"\n\nEt l'assistant répond en utilisant les outils MCP sans que l'utilisateur ait à naviguer dans l'interface.",
            30, 3);

        $this->exam($s4, 'Évaluation — Déploiement & Sécurité', [
            ['Quel package Laravel permet de créer un serveur MCP ?', QuestionTypeEnum::SINGLE_CHOICE, ['laravel/mcp', 'spatie/laravel-mcp', 'openai-php/laravel', 'laravel/sanctum'], [0]],
            ['Bonnes pratiques de sécurité pour un serveur MCP ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Tokens dédiés avec capacités', 'Rate limiting', 'Validation des entrées', 'Pas d\'authentification'], [0, 1, 2]],
            ['Un serveur MCP peut être connecté à Claude Desktop ET Cursor simultanément.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);

        $this->exam($formation, 'Examen final — IA, Agents & MCP', [
            ['Quel mécanisme d\'attention est au cœur des Transformers ?', QuestionTypeEnum::SINGLE_CHOICE, ['Self-attention', 'Cross-attention', 'Sparse attention', 'Global attention'], [0]],
            ['Étapes du RAG ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Indexation des documents', 'Recherche vectorielle', 'Augmentation du prompt', 'Fine-tuning du modèle'], [0, 1, 2]],
            ['Que signifie l\'acronyme MCP ?', QuestionTypeEnum::SINGLE_CHOICE, ['Model Context Protocol', 'Multi-Chain Processing', 'Message Control Protocol', 'Model Communication Package'], [0]],
            ['Composants d\'un agent IA autonome ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Modèle (LLM)', 'Outils (Tools)', 'Mémoire', 'Disque dur'], [0, 1, 2]],
            ['Le transport SSE permet des notifications push du serveur vers le client.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
            ['Quel transport MCP est utilisé pour la communication locale avec STDIO ?', QuestionTypeEnum::SINGLE_CHOICE, ['STDIO', 'HTTP', 'WebSocket', 'gRPC'], [0]],
            ['Pourquoi MCP standardise-t-il la communication IA ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Découverte automatique des outils', 'Format d\'authentification unique', 'Interopérabilité entre clients', 'Remplace les API REST'], [0, 2]],
            ['Un outil marqué #[IsReadOnly] ne doit pas modifier l\'état.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ], 75, 3);

        $formation->update(['is_active' => true]);
    }
}
