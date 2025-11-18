<div class="min-h-screen bg-gray-900 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- En-tête des résultats --}}
        <div class="bg-gradient-to-r @if($this->passed) from-green-600 to-green-700 @else from-red-600 to-red-700 @endif rounded-lg p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        @if($this->passed)
                            🎉 Félicitations !
                        @else
                            Résultats de l'examen
                        @endif
                    </h1>
                    <p class="text-xl opacity-90">{{ $attempt->exam->title }}</p>
                </div>
                <div class="text-right">
                    <div class="text-5xl font-bold mb-1">{{ round($attempt->percentage) }}%</div>
                    <div class="text-sm opacity-90">
                        {{ $attempt->score }} / {{ $attempt->max_score }} points
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between text-sm opacity-90">
                <div>
                    Note de passage : {{ $attempt->exam->getPassingScore() }}%
                </div>
                <div>
                    Complété le {{ $attempt->completed_at->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        {{-- Statistiques --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">
                            {{ $this->userAnswers()->where('is_correct', true)->count() }}
                        </div>
                        <div class="text-sm text-gray-400">Réponses correctes</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">
                            {{ $this->userAnswers()->where('is_correct', false)->count() }}
                        </div>
                        <div class="text-sm text-gray-400">Réponses incorrectes</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">
                            {{ $attempt->started_at->diffInMinutes($attempt->completed_at) }}
                        </div>
                        <div class="text-sm text-gray-400">Minutes écoulées</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liste des questions et réponses --}}
        <div class="space-y-6">
            <h2 class="text-xl font-semibold text-white">Détail des réponses</h2>

            @foreach($this->userAnswers() as $index => $userAnswer)
                <div @class([
                    'bg-gray-800 rounded-lg p-6 border-l-4',
                    'border-green-500' => $userAnswer->is_correct,
                    'border-red-500' => !$userAnswer->is_correct,
                ])>
                    {{-- En-tête de la question --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-3">
                            <div @class([
                                'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                                'bg-green-600 text-white' => $userAnswer->is_correct,
                                'bg-red-600 text-white' => !$userAnswer->is_correct,
                            ])>
                                @if($userAnswer->is_correct)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-400 mb-1">
                                    Question {{ $index + 1 }}
                                </div>
                                <h3 class="text-white font-medium">{{ $userAnswer->question->question_text }}</h3>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-400">Points</div>
                            <div class="text-white font-semibold">
                                {{ $userAnswer->points_earned }} / {{ $userAnswer->question->points }}
                            </div>
                        </div>
                    </div>

                    {{-- Options de réponse --}}
                    @if(in_array($userAnswer->question->question_type, [\App\Enums\QuestionTypeEnum::SINGLE_CHOICE, \App\Enums\QuestionTypeEnum::MULTIPLE_CHOICE, \App\Enums\QuestionTypeEnum::TRUE_FALSE]))
                        <div class="space-y-2 mb-4">
                            @foreach($userAnswer->question->options as $option)
                                @php
                                    $isCorrect = $option->is_correct;
                                    $isSelected = false;

                                    if ($userAnswer->question->question_type === \App\Enums\QuestionTypeEnum::MULTIPLE_CHOICE) {
                                        $selectedOptions = json_decode($userAnswer->selected_options ?? '[]', true);
                                        $isSelected = in_array($option->id, $selectedOptions);
                                    } else {
                                        $isSelected = $userAnswer->selected_option_id === $option->id;
                                    }
                                @endphp

                                <div @class([
                                    'p-3 rounded-lg',
                                    'bg-green-900/30 border border-green-600' => $isCorrect && $isSelected,
                                    'bg-red-900/30 border border-red-600' => !$isCorrect && $isSelected,
                                    'bg-green-900/20 border border-green-600/50' => $isCorrect && !$isSelected,
                                    'bg-gray-700' => !$isCorrect && !$isSelected,
                                ])>
                                    <div class="flex items-center gap-2">
                                        @if($isSelected)
                                            @if($isCorrect)
                                                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            @endif
                                        @elseif($isCorrect)
                                            <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif

                                        <span @class([
                                            'text-white' => $isSelected || $isCorrect,
                                            'text-gray-300' => !$isSelected && !$isCorrect,
                                        ])>
                                            {{ $option->option_text }}
                                        </span>

                                        @if($isSelected)
                                            <span class="ml-auto text-xs text-gray-400">(Votre réponse)</span>
                                        @elseif($isCorrect)
                                            <span class="ml-auto text-xs text-green-400">(Réponse correcte)</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Réponse texte --}}
                        <div class="mb-4">
                            <div class="text-sm text-gray-400 mb-2">Votre réponse:</div>
                            <div class="p-3 bg-gray-700 rounded-lg text-white">
                                {{ $userAnswer->answer_text ?? 'Aucune réponse fournie' }}
                            </div>
                        </div>
                    @endif

                    {{-- Explication --}}
                    @if($userAnswer->question->explanation)
                        <div class="mt-4 p-4 bg-blue-900/20 border border-blue-600/50 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-blue-400 mb-1">Explication</div>
                                    <div class="text-sm text-gray-300">{{ $userAnswer->question->explanation }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex items-center justify-center gap-4">
            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Retour au tableau de bord
            </a>

            @if(!$this->passed && $attempt->exam->canUserAttempt(auth()->user()))
                <a href="{{ route('exam.take', $attempt->exam) }}"
                   class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Réessayer l'examen
                </a>
            @endif
        </div>
    </div>
</div>
