# Architecture du Modèle IA — Génération d'Examens

> Proposition d'architecture pour un système IA capable d'analyser les sections et chapitres d'une formation et de générer automatiquement des examens pertinents.

---

## 1. Objectif

Créer un système modulaire qui :

1. **Analyse** le contenu pédagogique (chapitres en Markdown, descriptions de vidéos, textes extraits de PDF)
2. **Extrait** les concepts clés, objectifs d'apprentissage et niveaux de difficulté
3. **Génère** des examens complets (questions + réponses) adaptés au contenu
4. **Valide** la pertinence et la qualité des questions produites
5. **S'intègre** proprement au système d'examen existant (Exam, Question, QuestionOption)

---

## 2. Vue d'Ensemble de l'Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    AIEngine (Orchestrateur)                     │
│                                                                 │
│   ┌─────────────┐  ┌──────────────┐  ┌──────────────────┐     │
│   │  Content     │  │  Concept     │  │  Difficulty      │     │
│   │  Extractor   │──│  Analyzer    │──│  Analyzer        │     │
│   └─────────────┘  └──────────────┘  └──────────────────┘     │
│         │                                                      │
│         ▼                                                      │
│   ┌──────────────────────────────────────────────────┐        │
│   │           Exam Structure Generator               │        │
│   │  (Type de questions, nombre, points, répartition) │        │
│   └──────────────────────────────────────────────────┘        │
│         │                                                      │
│         ▼                                                      │
│   ┌──────────────────────┐   ┌────────────────────────┐      │
│   │  Question Generator  │──▶│    Validator Chain      │      │
│   │  (SC / MC / TF)      │   │  (pertinence, qualité) │      │
│   └──────────────────────┘   └────────────────────────┘      │
│         │                                                      │
│         ▼                                                      │
│   ┌──────────────────────────────────────────────────┐        │
│   │              AI Provider Interface                │        │
│   │                                                  │        │
│   │  ┌──────────┐  ┌───────────┐  ┌───────────┐     │        │
│   │  │  OpenAI   │  │ Anthropic │  │  Ollama   │     │        │
│   │  │  GPT-4o   │  │ Claude 4  │  │ (local)   │     │        │
│   │  └──────────┘  └───────────┘  └───────────┘     │        │
│   └──────────────────────────────────────────────────┘        │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Persistence Layer                           │
│                                                                 │
│   Exam (existante) ← Question (existante) ← QuestionOption   │
│   ai_generated = true                        (existante)       │
│                                                                 │
│   ai_generation_logs (nouvelle table)                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Structure de Fichiers Proposée

```
app/Services/AI/
├── AIEngine.php                         # Orchestrateur principal
├── AIServiceProvider.php                # ServiceProvider Laravel
├── config/ai.php                        # Configuration (à publier)
│
├── Contracts/
│   ├── AIProviderInterface.php          # Interface fournisseur LLM
│   ├── ContentAnalyzerInterface.php     # Interface analyseur contenu
│   └── QuestionGeneratorInterface.php   # Interface générateur questions
│
├── Providers/
│   ├── OpenAIProvider.php               # Intégration OpenAI GPT-4o
│   ├── AnthropicProvider.php            # Intégration Claude
│   └── OllamaProvider.php               # Fournisseur local (dev gratuit)
│
├── Analyzers/
│   ├── ContentExtractor.php             # Extraction et preprocessing
│   ├── ConceptExtractor.php             # Extraction des concepts clés
│   ├── DifficultyAnalyzer.php           # Analyse de difficulté
│   └── LearningObjectiveAnalyzer.php    # Analyse objectifs pédagogiques
│
├── Generators/
│   ├── ExamStructureGenerator.php       # Structure globale de l'examen
│   ├── SingleChoiceGenerator.php        # Questions choix unique
│   ├── MultipleChoiceGenerator.php      # Questions choix multiple
│   ├── TrueFalseGenerator.php           # Questions vrai/faux
│   └── QuestionGenerator.php           # Router vers le bon générateur
│
├── Validators/
│   ├── QuestionValidator.php            # Validation qualité question
│   ├── AnswerValidator.php              # Validation exactitude réponses
│   └── RelevanceValidator.php           # Validation pertinence contenu
│
├── ValueObjects/
│   ├── GenerationRequest.php            # DTO requête
│   ├── GenerationResult.php             # DTO résultat
│   ├── AIQuestion.php                   # Question générée
│   ├── AIQuestionOption.php             # Option générée
│   ├── AIConcept.php                    # Concept extrait
│   └── ExamStructure.php               # Structure d'examen générée
│
├── Prompts/
│   ├── exam-structure.prompt.php        # Prompt structure examen
│   ├── single-choice.prompt.php         # Prompt question choix unique
│   ├── multiple-choice.prompt.php       # Prompt question choix multiple
│   ├── true-false.prompt.php            # Prompt question vrai/faux
│   └── concept-analysis.prompt.php      # Prompt analyse concepts
│
├── Jobs/
│   ├── GenerateExamJob.php              # Job file d'attente
│   └── GenerateQuestionsJob.php         # Job sous-tâche
│
└── Console/
    └── GenerateExamCommand.php          # Artisan command
```

---

## 4. Data Flow Détaillé

### 4.1 Génération d'un Examen Complet

```
1. DÉCLENCHEUR
   ├── Manuel : Bouton "Générer examen IA" dans le panneau admin
   └── Automatique : Artisan command `php artisan ai:generate-exam {formation}`
   
2. AIEngine::generate(GenerationRequest $request)
   │
   ├── 2.1 ContentExtractor::extract(Formation $formation)
   │   ├── Récupère toutes les sections actives avec leurs chapitres
   │   ├── Extrait le texte des chapitres (content, description)
   │   ├── Extrait le texte des PDF (via le pipeline existant)
   │   ├── Calcule les métriques : nombre de mots, temps de lecture
   │   └── Retourne : AnalyzableContent (texte structuré + métadonnées)
   │
   ├── 2.2 ConceptExtractor::extract(AnalyzableContent $content)
   │   ├── Appelle le LLM (via AIProvider) avec prompt d'analyse
   │   ├── Identifie 5-15 concepts clés du contenu
   │   ├── Associe chaque concept à son niveau taxonomique (Bloom)
   │   └── Retourne : Collection<AIConcept>
   │
   ├── 2.3 DifficultyAnalyzer::analyze(AnalyzableContent $content)
   │   ├── Analyse la complexité du vocabulaire
   │   ├── Détecte la densité technique
   │   ├── Croise avec FormationLevelEnum (beginner/intermediate/advanced)
   │   └── Retourne : DifficultyProfile (score 1-10)
   │
   ├── 2.4 LearningObjectiveAnalyzer::analyze(Collection<AIConcept>)
   │   ├── Groupe les concepts en objectifs pédagogiques
   │   ├── Assigne un niveau Bloom à chaque objectif
   │   └── Retourne : Collection<LearningObjective>
   │
   ├── 2.5 ExamStructureGenerator::generate(...)
   │   ├── Reçoit : concepts, difficulté, objectifs, niveau formation
   │   ├── Détermine : nombre de questions (5-20 selon le niveau)
   │   ├── Répartit : X% single_choice, Y% multiple_choice, Z% true_false
   │   ├── Assigne les points par question
   │   └── Retourne : ExamStructure (plan détaillé)
   │
   ├── 2.6 QuestionGenerator::generate(ExamStructure $structure)
   │   ├── Pour chaque question planifiée :
   │   │   ├── QuestionGenerator router → générateur spécifique
   │   │   ├── Appelle LLM avec prompt spécialisé
   │   │   ├── Reçoit : question, options, réponse correcte, explication
   │   │   └── Applique le ValidatorChain
   │   ├── Gère les tentatives (retry si validation échoue)
   │   └── Retourne : Collection<AIQuestion>
   │
   ├── 2.7 Validation (post-génération)
   │   ├── RelevanceValidator::validate(Collection<AIQuestion>, content)
   │   │   └── Vérifie que chaque question est liée au contenu source
   │   ├── QuestionValidator::validate(AIQuestion $question)
   │   │   ├── Vérifie : pas de doublon, cohérence, pas de réponse "toutes"
   │   │   ├── Vérifie : 1 seule correcte pour SC, 2+ pour MC
   │   │   └── Vérifie : explication non vide
   │   └── AnswerValidator::validate(AIQuestion $question, $sourceContent)
   │       └── Vérifie que la réponse correcte est justifiée par le contenu
   │
   └── 2.8 Persistance
       ├── Crée Exam (examable_type + examable_id, ai_generated = true)
       ├── Crée Question(s) avec leurs QuestionOption(s)
       ├── Log dans ai_generation_logs
       └── Dispatch notification à l'admin
```

### 4.2 Génération par Niveau

```
NIVEAU FORMATION  (Exam → Formation)
├── Scope : toutes les sections + tous les chapitres
├── Questions : 15-20 (panorama complet)
├── Répartition : 40% MC, 40% SC, 20% TF
└── Difficulté : suit le niveau global de la Formation

NIVEAU SECTION   (Exam → Section)
├── Scope : une seule section + ses chapitres
├── Questions : 8-12 (ciblé section)
├── Répartition : 50% SC, 30% MC, 20% TF
└── Difficulté : adaptée aux chapitres de la section

NIVEAU CHAPITRE  (Exam → Chapter)
├── Scope : un seul chapitre
├── Questions : 3-6 (vérification rapide)
├── Répartition : 60% SC, 20% TF, 20% MC
└── Difficulté : basée sur le chapitre uniquement
```

---

## 5. Value Objects (DTOs)

### 5.1 GenerationRequest

```php
class GenerationRequest
{
    public function __construct(
        public readonly Formation $formation,
        public readonly ?Section $section = null,       // Null = formation-level
        public readonly ?Chapter $chapter = null,        // Null = section or formation level
        public readonly int $questionCount = 0,           // 0 = auto-calculate
        public readonly string $provider = 'openai',      // openai | anthropic | ollama
        public readonly string $model = 'gpt-4o',        // ou claude-4, llama3, etc.
        public readonly bool $includeExplanations = true,
        public readonly ?string $language = 'fr',         // fr | en
        public readonly array $questionTypes = ['single_choice', 'multiple_choice', 'true_false'],
        public readonly bool $validateOnly = false,       // true = dry-run validation
    ) {}
}
```

### 5.2 GenerationResult

```php
class GenerationResult
{
    public function __construct(
        public readonly ExamStructure $structure,
        public readonly Collection $questions,          // Collection<AIQuestion>
        public readonly Collection $concepts,            // Collection<AIConcept>
        public readonly array $metrics,                  // tokens_used, time_taken, etc.
        public readonly bool $success,
        public readonly ?string $error = null,
        public readonly ?Exam $exam = null,              // after persistence
    ) {}
}
```

### 5.3 AIQuestion

```php
class AIQuestion
{
    public function __construct(
        public readonly string $questionText,
        public readonly QuestionTypeEnum $questionType,
        public readonly int $points,
        public readonly string $explanation,
        public readonly Collection $options,             // Collection<AIQuestionOption>
        public readonly ?string $sourceConcept = null,   // Concept clé source
        public readonly float $confidenceScore = 1.0,    // Score de confiance IA
        public readonly ?string $bloomLevel = null,      // Mémoriser | Comprendre | Appliquer...
    ) {}
}
```

### 5.4 AIQuestionOption

```php
class AIQuestionOption
{
    public function __construct(
        public readonly string $optionText,
        public readonly bool $isCorrect,
        public readonly int $orderPosition,
        public readonly ?string $explanation = null,
    ) {}
}
```

### 5.5 AIConcept

```php
class AIConcept
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly float $relevanceScore,          // 0.0 - 1.0
        public readonly string $bloomLevel,             // remember | understand | apply | analyze | evaluate | create
        public readonly string $sourceType,             // chapter | section
        public readonly int $sourceId,                  // chapter_id or section_id
        public readonly int $frequency,                 // occurrences dans le texte
    ) {}
}
```

---

## 6. Contrats (Interfaces)

### 6.1 AIProviderInterface

```php
interface AIProviderInterface
{
    /** Envoyer un prompt et recevoir une réponse structurée */
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): AIResponse;

    /** Envoyer un prompt avec format JSON forcé */
    public function generateStructured(string $systemPrompt, string $userPrompt, string $schema): array;

    /** Vérifier que le provider est accessible */
    public function isAvailable(): bool;

    /** Obtenir le nom du modèle actif */
    public function getModel(): string;

    /** Obtenir le coût estimé (tokens) */
    public function estimateCost(string $prompt): array;
}

class AIResponse
{
    public function __construct(
        public readonly string $content,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly float $durationMs,
    ) {}
}
```

### 6.2 ContentAnalyzerInterface

```php
interface ContentAnalyzerInterface
{
    public function analyze(Formation $formation, ?Section $section = null, ?Chapter $chapter = null): AnalyzableContent;
    public function extractConcepts(AnalyzableContent $content): Collection; // Collection<AIConcept>
    public function assessDifficulty(AnalyzableContent $content): int;       // 1-10
    public function extractObjectives(Collection $concepts): Collection;     // Collection<LearningObjective>
}
```

### 6.3 QuestionGeneratorInterface

```php
interface QuestionGeneratorInterface
{
    public function generate(
        QuestionTypeEnum $type,
        string $sourceText,
        ?AIConcept $concept = null,
        int $difficulty = 5,
        string $language = 'fr'
    ): AIQuestion;

    public function supportsType(QuestionTypeEnum $type): bool;
    public function getConfidenceScore(AIQuestion $question): float;
}
```

---

## 7. Prompts Système

### 7.1 Extraction de Concepts

```
Tu es un expert pédagogique spécialisé dans l'analyse de contenu de formation.
Analyse le texte suivant et identifie les concepts clés, définitions importantes,
et notions fondamentales à maîtriser.

Pour chaque concept, fournis :
- Nom du concept
- Description courte (1 phrase)
- Niveau taxonomique de Bloom (remember/understand/apply/analyze/evaluate/create)
- Score de pertinence (0.0 à 1.0)

Format de réponse : JSON structuré avec tableau de concepts.

Texte à analyser :
{contenu_du_chapitre}
```

### 7.2 Génération Question Choix Unique

```
Tu es un générateur de questions d'examen pour une plateforme de formation professionnelle.
Génère une question à choix unique (une seule bonne réponse) basée strictement sur le contenu fourni.

Règles :
- La question doit être claire et sans ambiguïté
- Exactement UNE option correcte parmi 4 propositions
- Les distracteurs doivent être plausibles mais faux
- Les distracteurs doivent être basés sur des erreurs fréquentes / confusions communes
- Inclus une explication qui justifie la bonne réponse en citant le contenu source
- La question doit tester la compréhension, pas juste la mémorisation
- Utilise un niveau de difficulté adapté ({difficulty}/10)

Format de réponse JSON :
{{
  "question_text": "...",
  "options": [
    {{ "option_text": "...", "is_correct": false }},
    {{ "option_text": "...", "is_correct": true }},
    ...
  ],
  "explanation": "...",
  "concept": "...",
  "bloom_level": "..."
}}

Contenu source : {contenu}
```

### 7.3 Génération Structure Examen

```
Tu dois concevoir la structure optimale d'un examen pour une formation.
Analyse les métadonnées et conçois un examen équilibré.

Paramètres :
- Nombre total de questions : {count} (auto-calculé sinon)
- Types disponibles : {types}
- Durée suggérée : {duration} minutes
- Niveau formation : {level}
- Concepts extraits : {concepts}

Règles de conception :
- Couvre tous les concepts clés
- Progression facile → difficile
- Équilibre entre les sections
- Pas plus de 30% true/false

Format JSON :
{{
  "title": "...",
  "description": "...",
  "instructions": "...",
  "duration_minutes": ...,
  "passing_score": ...,
  "questions": [
    {{ "type": "single_choice", "concept": "...", "difficulty": ... }},
    ...
  ]
}}
```

---

## 8. Providers (Implémentations)

### 8.1 OpenAIProvider

```php
class OpenAIProvider implements AIProviderInterface
{
    // Utilise le SDK openai-php/client
    // Supporte : gpt-4o, gpt-4o-mini, o3-mini
    // Response format : JSON mode (response_format: { type: "json_object" })
    
    public function generateStructured(string $systemPrompt, string $userPrompt, string $schema): array
    {
        $response = $this->client->chat()->create([
            'model' => config('ai.openai.model', 'gpt-4o'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3, // Basse = reproductibilité
            'max_tokens' => config('ai.openai.max_tokens', 4096),
        ]);
        
        return [
            'content' => json_decode($response->choices[0]->message->content, true),
            'input_tokens' => $response->usage->promptTokens,
            'output_tokens' => $response->usage->completionTokens,
        ];
    }
}
```

### 8.2 OllamaProvider (Développement local gratuit)

```php
class OllamaProvider implements AIProviderInterface
{
    // Utilise les appels HTTP directs à l'API Ollama
    // Modèles : llama3, mistral, deepseek-coder, etc.
    // Parfait pour le développement sans frais d'API
    
    public function generateStructured(string $systemPrompt, string $userPrompt, string $schema): array
    {
        $response = Http::timeout(120)
            ->post(config('ai.ollama.url', 'http://localhost:11434/api/chat'), [
                'model' => config('ai.ollama.model', 'llama3'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt . "\n\nRéponds UNIQUEMENT en JSON valide."],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'format' => 'json',
                'stream' => false,
                'options' => [
                    'temperature' => 0.3,
                ],
            ]);
        
        return [
            'content' => json_decode($response->json('message.content'), true),
            'input_tokens' => $response->json('prompt_eval_count', 0),
            'output_tokens' => $response->json('eval_count', 0),
        ];
    }
}
```

---

## 9. Base de Données

### 9.1 Nouvelle table : `ai_generation_logs`

```php
Schema::create('ai_generation_logs', function (Blueprint $table) {
    $table->id();
    
    // Cible de la génération
    $table->foreignId('exam_id')->nullable()->constrained();
    $table->foreignId('formation_id')->constrained();
    $table->foreignId('section_id')->nullable()->constrained();
    $table->foreignId('chapter_id')->nullable()->constrained();
    
    // Métadonnées
    $table->string('provider');              // openai | anthropic | ollama
    $table->string('model');                 // gpt-4o, claude-4, llama3
    $table->integer('input_tokens')->default(0);
    $table->integer('output_tokens')->default(0);
    $table->float('cost_estimate')->default(0);
    
    // Statut
    $table->string('status')->default('pending');
    // pending | processing | completed | failed | partial
    $table->text('error_message')->nullable();
    
    // Contenu généré (sauvegarde du résultat complet)
    $table->json('generated_structure')->nullable();   // ExamStructure
    $table->json('generated_questions')->nullable();    // Questions brutes
    $table->json('extracted_concepts')->nullable();     // Concepts extraits
    
    // Timing
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    
    // Utilisateur ayant déclenché
    $table->foreignId('user_id')->nullable()->constrained();
    
    $table->timestamps();
    
    // Index
    $table->index(['formation_id', 'status']);
    $table->index(['exam_id', 'status']);
});
```

### 9.2 Colonne additionnelle sur `exams`

```php
Schema::table('exams', function (Blueprint $table) {
    $table->boolean('ai_generated')->default(false)->after('is_active');
    $table->foreignId('ai_generation_log_id')->nullable()->after('ai_generated')
        ->constrained('ai_generation_logs');
    $table->string('ai_provider')->nullable()->after('ai_generation_log_id');
    $table->string('ai_model')->nullable()->after('ai_provider');
});
```

---

## 10. Jobs et File d'Attente

### 10.1 GenerateExamJob

```php
class GenerateExamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;    // 5 minutes pour une génération complète
    public int $tries = 2;

    public function __construct(
        public readonly Formation $formation,
        public readonly ?Section $section = null,
        public readonly ?Chapter $chapter = null,
        public readonly ?User $user = null,     // Qui a demandé
    ) {}

    public function handle(AIEngine $engine): void
    {
        $request = new GenerationRequest(
            formation: $this->formation,
            section: $this->section,
            chapter: $this->chapter,
        );

        $result = $engine->generate($request);

        if (! $result->success) {
            Log::error('AI exam generation failed', [
                'formation_id' => $this->formation->id,
                'error' => $result->error,
            ]);
        }
    }
}
```

---

## 11. Intégration avec le Système Existant

### 11.1 Filament Action (Bouton "Générer par IA")

Dans la resource Exam ou Formation :

```php
// Dans ExamResource ou FormationResource
Action::make('generate_ai_exam')
    ->label('Générer examen par IA')
    ->icon('heroicon-o-sparkles')
    ->color('success')
    ->requiresConfirmation()
    ->modalHeading('Génération automatique d\'examen')
    ->modalDescription('L\'IA va analyser le contenu pédagogique et générer un examen adapté. Cela peut prendre quelques instants.')
    ->action(function (Formation $record) {
        GenerateExamJob::dispatch($record);
        Notification::make()
            ->title('Génération en cours')
            ->body('L\'examen sera disponible dans quelques minutes.')
            ->success()
            ->send();
    });
```

### 11.2 Artisan Command

```bash
# Générer un examen pour une formation
php artisan ai:generate-exam {formation}

# Générer les examens pour toutes les formations
php artisan ai:generate-exam --all

# Générer uniquement pour les sections (pas l'examen final)
php artisan ai:generate-exam {formation} --level=section

# Utiliser un provider local (Ollama) sans frais
php artisan ai:generate-exam {formation} --provider=ollama --dry-run
```

---

## 12. Diagramme de Classes

```
┌────────────────────────────┐
│      AIEngine              │
├────────────────────────────┤
│ + generate(GenerationRequest): GenerationResult
│ + generateFormationExam(Formation): GenerationResult
│ + generateSectionExam(Section): GenerationResult
│ + generateChapterExam(Chapter): GenerationResult
│ + validate(GenerationResult): ValidationResult
└────────────────────────────┘
         │
         │ utilise
         ▼
┌────────────────────────────┐     ┌──────────────────────────────┐
│   AIProviderInterface      │◄────│    OpenAIProvider            │
├────────────────────────────┤     ├──────────────────────────────┤
│ + generate()               │     │ - client: OpenAI\Client      │
│ + generateStructured()     │     │ + generate(): AIResponse     │
│ + isAvailable(): bool      │     │ + isAvailable(): bool        │
│ + getModel(): string       │     └──────────────────────────────┘
│ + estimateCost()           │
└────────────────────────────┘     ┌──────────────────────────────┐
         ▲                        │    AnthropicProvider          │
         │                        ├──────────────────────────────┤
         │                        │ - client: Anthropic\SDK      │
         │                        └──────────────────────────────┘
         │
         │                        ┌──────────────────────────────┐
         │                        │    OllamaProvider            │
         │                        ├──────────────────────────────┤
         │                        │ + generate(): AIResponse     │
         │                        │ (HTTP -> localhost:11434)     │
         │                        └──────────────────────────────┘

┌────────────────────────────┐
│ ContentExtractor           │
├────────────────────────────┤
│ + extract(Formation): AnalyzableContent
│ + extractSection(Section): AnalyzableContent
│ + extractChapter(Chapter): AnalyzableContent
│ - extractTextFromChapter(Chapter): string
│ - extractTextFromPdf(Chapter): string
│ - cleanContent(string): string
│ - chunkContent(string, int): array
└────────────────────────────┘

┌────────────────────────────┐
│ ConceptExtractor           │
├────────────────────────────┤
│ + extract(Content): Collection<AIConcept>
│ - callLLMForConcepts(string): array
│ - mapToBloomLevel(string): string
│ - calculateRelevance(array): float
└────────────────────────────┘

┌────────────────────────────┐
│ QuestionGenerator          │
├────────────────────────────┤
│ - generators: array        │
│ + generate(ExamStructure): Collection<AIQuestion>
│ + generateForConcept(AIConcept): AIQuestion
│ - selectGenerator(QuestionTypeEnum): QuestionGeneratorInterface
│ - generateSingleQuestion(ExamStructure, int): AIQuestion
└────────────────────────────┘
         │
         ├── SingleChoiceGenerator
         ├── MultipleChoiceGenerator
         └── TrueFalseGenerator

┌────────────────────────────┐
│ ValidatorChain             │
├────────────────────────────┤
│ - validators: array        │
│ + validate(Collection<AIQuestion>): ValidationResult
│ + validateSingle(AIQuestion): ValidationResult
│ - checkDuplicates(array): array
│ - checkRelevance(AIQuestion, string): bool
│ - checkAnswerConsistency(AIQuestion): bool
└────────────────────────────┘
```

---

## 13. Pipeline de Traitement (Séquentiel)

```
                    ┌──────────┐
                    │  DÉBUT   │
                    └────┬─────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Choisir le scope    │
              │  Formation / Section │
              │  / Chapter            │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Extraire le contenu │
              │  (ContentExtractor)  │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Découper en chunks  │
              │  (si contenu long)   │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Extraire concepts   │
              │  via LLM             │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Analyser difficulté │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Générer structure   │
              │  d'examen via LLM    │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Pour chaque question│
              │  planifiée :         │
              └──────────┬───────────┘
                         │
              ┌──────────▼───────────┐
              │  Générer question    │◄────── Retry (max 3)
              │  via LLM             │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Valider question    │────── Si échec ────┐
              │  (Validator Chain)   │                    │
              └──────────┬───────────┘                    │
                         │ Validé                         │
                         ▼                                │
              ┌──────────────────────┐                    │
              │  Question suivante   │────────────────────┘
              └──────────┬───────────┘
                         │ Terminé
                         ▼
              ┌──────────────────────┐
              │  Valider ensemble    │
              │  (global)            │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Persister (DB)      │
              │  Exam + Questions    │
              └──────────┬───────────┘
                         │
                         ▼
              ┌──────────────────────┐
              │  Logger l'opération  │
              │  (ai_generation_logs)│
              └──────────┬───────────┘
                         │
                         ▼
                    ┌──────────┐
                    │   FIN    │
                    └──────────┘
```

---

## 14. Configuration

### config/ai.php

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Provider par défaut
    |--------------------------------------------------------------------------
    | openai | anthropic | ollama
    */
    'default' => env('AI_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'max_tokens' => 4096,
        'temperature' => 0.3,
        'timeout' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic (Claude)
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
        'max_tokens' => 4096,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama (local - gratuit)
    |--------------------------------------------------------------------------
    | Utile pour le développement et les tests
    */
    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3'),
        'timeout' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Génération
    |--------------------------------------------------------------------------
    */
    'generation' => [
        'default_question_count' => 10,
        'max_question_count' => 50,
        'min_question_count' => 3,
        'retry_on_failure' => 3,          // Tentatives de regénération
        'chunk_size' => 4000,              // Caractères par chunk LLM
        'temperature' => 0.3,              // Créativité (0.0 = déterministe)
        'exclude_types' => [],            // Types de questions à exclure
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Évite de re-générer pour du contenu inchangé
    */
    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'ttl' => 604800,  // 7 jours
        'key_prefix' => 'ai_generation_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_section_questions' => 15,
        'max_chapter_questions' => 8,
        'max_formation_questions' => 30,
        'min_confidence_score' => 0.7,     // En dessous = regénération
    ],

    /*
    |--------------------------------------------------------------------------
    | Langue par défaut des prompts
    |--------------------------------------------------------------------------
    */
    'language' => 'fr',
];
```

---

## 15. Implémentation Recommandée (Ordre)

### Phase 1 — Fondation (Jour 1-2)
1. Créer `ValueObjects/` (DTOs)
2. Créer `Contracts/` (interfaces)
3. Créer `OllamaProvider` (gratuit, pas de clé API nécessaire)
4. Créer `ContentExtractor`
5. Créer `config/ai.php`
6. Créer `AIServiceProvider`

### Phase 2 — Analyse (Jour 3-4)
7. Créer `ConceptExtractor`
8. Créer `DifficultyAnalyzer`
9. Créer `LearningObjectiveAnalyzer`
10. Créer les prompts d'analyse
11. Tester l'extraction de concepts

### Phase 3 — Génération (Jour 5-7)
12. Créer `ExamStructureGenerator`
13. Créer `QuestionGenerator` + générateurs spécifiques
14. Créer les prompts de génération
15. Tester la génération de questions

### Phase 4 — Validation & Persistance (Jour 8-9)
16. Créer `Validator` chain
17. Migration `ai_generation_logs`
18. Migration colonnes `exams`
19. Créer `AIEngine` (orchestrateur)

### Phase 5 — Interface & Jobs (Jour 10-11)
20. Créer `GenerateExamJob`
21. Créer `GenerateExamCommand`
22. Ajouter bouton Filament "Générer par IA"
23. Notifications

### Phase 6 — Providers additionnels (Jour 12-13)
24. Créer `OpenAIProvider`
25. Créer `AnthropicProvider`
26. Tests comparatifs de qualité

---

## 16. Tests Recommandés

```php
// 1. Test unitaire : ContentExtractor
it('extracts content from formation with sections and chapters', function () {
    $formation = Formation::factory()->has(
        Section::factory()->has(Chapter::factory(), 'chapters'), 'sections'
    )->create();
    
    $extractor = app(ContentExtractor::class);
    $content = $extractor->extract($formation);
    
    expect($content->getText())->not->toBeEmpty();
    expect($content->getMetrics())->toHaveKeys(['word_count', 'reading_time']);
});

// 2. Test avec OllamaProvider (mocké)
it('generates valid single choice question', function () {
    $provider = Mockery::mock(OllamaProvider::class);
    $provider->shouldReceive('generateStructured')
        ->andReturn([
            'question_text' => 'Quel est le concept clé du chapitre 1 ?',
            'options' => [
                ['option_text' => 'Réponse A', 'is_correct' => false],
                ['option_text' => 'Réponse B', 'is_correct' => true],
                ['option_text' => 'Réponse C', 'is_correct' => false],
                ['option_text' => 'Réponse D', 'is_correct' => false],
            ],
            'explanation' => 'Le texte explique clairement que B est correct car...',
        ]);
    
    $generator = new SingleChoiceGenerator($provider);
    $question = $generator->generate('contenu du chapitre...');
    
    expect($question->questionType)->toBe(QuestionTypeEnum::SINGLE_CHOICE);
    expect($question->options->filter(fn($o) => $o->isCorrect)->count())->toBe(1);
    expect($question->options->count())->toBe(4);
});

// 3. Test d'intégration : génération complète
it('generates and persists a complete exam for a formation', function () {
    Queue::fake();
    
    $formation = Formation::factory()->create();
    GenerateExamJob::dispatch($formation);
    
    Queue::assertPushed(GenerateExamJob::class);
});

// 4. Test de validation
it('rejects question with multiple correct answers for single_choice', function () {
    $question = new AIQuestion(
        questionText: 'Test?',
        questionType: QuestionTypeEnum::SINGLE_CHOICE,
        points: 1,
        explanation: 'Test',
        options: collect([
            new AIQuestionOption('A', true, 1),
            new AIQuestionOption('B', true, 2), // Deux bonnes réponses !
            new AIQuestionOption('C', false, 3),
        ])
    );
    
    $validator = new QuestionValidator();
    $result = $validator->validate($question);
    
    expect($result->fails())->toBeTrue();
});
```

---

## 17. Exemple d'Utilisation (Filament)

```php
// Dans app/Filament/Resources/FormationResource.php

public static function getPages(): array
{
    return [
        'index' => Pages\ListFormations::route('/'),
        'create' => Pages\CreateFormation::route('/create'),
        'edit' => Pages\EditFormation::route('/{record}/edit'),
        'view' => Pages\ViewFormation::route('/{record}'),
        'ai-exam' => Pages\GenerateAIExam::route('/{record}/ai-exam'),
    ];
}

// Et dans le ViewFormation ou EditFormation
use Filament\Actions\Action;

protected function getHeaderActions(): array
{
    return [
        Action::make('generate_formation_exam')
            ->label('Générer examen final (IA)')
            ->icon('heroicon-o-sparkles')
            ->color('success')
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-sparkles')
            ->modalHeading('Génération IA de l\'examen final')
            ->modalDescription("L'IA va analyser l'ensemble des chapitres de cette formation et générer un examen complet avec questions, réponses et explications.")
            ->action(function () {
                GenerateExamJob::dispatch($this->record, user: auth()->user());
                
                Notification::make()
                    ->title('Génération démarrée')
                    ->body('L\'examen sera prêt dans quelques instants. Vous recevrez une notification.')
                    ->success()
                    ->send();
            }),
            
        Action::make('generate_section_exams')
            ->label('Générer examens sections (IA)')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->requiresConfirmation()
            ->action(function () {
                $this->record->sections->each(
                    fn ($section) => GenerateExamJob::dispatch(
                        formation: $this->record, 
                        section: $section, 
                        user: auth()->user()
                    )
                );
                
                Notification::make()
                    ->title('Génération des examens de section démarrée')
                    ->success()
                    ->send();
            }),
    ];
}
```

---

## 18. Résumé des Choix Techniques

| Choix | Option retenue | Raison |
|-------|---------------|--------|
| **Approche** | LLM + Extraction locale | Pas de modèle à entraîner, résultats exploitables immédiatement |
| **Provider principal** | Ollama (dev), OpenAI (prod) | Gratuit en dev, scalable en prod |
| **Format LLM** | JSON structuré (`response_format: json_object`) | Parsing fiable, validation stricte |
| **Génération** | Asynchrone (Job queue) | Les formations longues peuvent prendre 2-5 minutes |
| **Cache** | 7 jours basé sur hash du contenu | Évite de re-générer pour du contenu inchangé |
| **Température** | 0.3 (basse) | Reproductibilité, moins d'hallucinations |
| **Langue** | `fr` par défaut dans les prompts | Adaptation au public francophone |
| **Validation** | Chaîne de validateurs avec retry | Qualité garantie avant persistance |
| **Pas d'entraînement** | Aucun fine-tuning nécessaire | L'infrastructure LLM existante suffit |

---

## 19. Coûts Estimés (API)

| Fournisseur | Modèle | Coût / 1K tokens input | Coût / 1K tokens output | Coût / examen moyen |
|-------------|--------|----------------------|-----------------------|-------------------|
| **Ollama** | Llama 3 70B | Gratuit (local) | Gratuit | **0 €** |
| **OpenAI** | GPT-4o | ~0.0025 $ | ~0.01 $ | **~0.05-0.10 $** |
| **OpenAI** | GPT-4o-mini | ~0.00015 $ | ~0.0006 $ | **~0.01-0.02 $** |
| **Anthropic** | Claude 4 Haiku | ~0.00025 $ | ~0.00125 $ | **~0.02-0.04 $** |

> **Recommandation :** Utiliser Ollama en développement (gratuit, local, privé) et GPT-4o-mini en production (coût négligeable, excellente qualité).

---

## 20. Prochaines Évolutions Possibles

1. **Fine-tuning** sur un modèle open source avec vos propres questions validées manuellement
2. **RAG (Retrieval Augmented Generation)** : Indexer vectoriellement le contenu des formations pour générer des questions plus précises
3. **Adaptive Learning** : Adapter les questions générées au niveau de l'étudiant basé sur ses résultats précédents
4. **Génération de feedback** : Expliquer pourquoi une réponse est incorrecte en citant le contenu source
5. **Export du dataset** : Exporter les questions générées et validées pour fine-tuning
6. **Interface de relecture** : Permettre aux formateurs de valider/modifier les questions avant publication
