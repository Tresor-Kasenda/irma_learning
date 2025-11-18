<div class="min-h-screen bg-gray-900">
    {{-- En-tête de l'examen --}}
    <header class="bg-gray-950 border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-white">{{ $exam->title }}</h1>
                    <p class="text-sm text-gray-400">{{ $exam->examable->title }}</p>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Timer --}}
                    @if($timeRemaining !== null)
                        <div class="flex items-center gap-2 px-4 py-2 bg-gray-800 rounded-lg">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-white font-mono" id="timer">
                                {{ gmdate('H:i:s', $timeRemaining) }}
                            </span>
                        </div>
                    @endif

                    {{-- Progress --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-400">Progression:</span>
                        <div class="w-32 bg-gray-800 rounded-full h-2">
                            <div class="bg-primary-600 h-full rounded-full transition-all duration-500"
                                 style="width: {{ $this->progress }}%">
                            </div>
                        </div>
                        <span class="text-sm text-white">{{ round($this->progress) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Navigation des questions (sidebar) --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg p-4 sticky top-24">
                    <h3 class="text-sm font-semibold text-white mb-4">Navigation</h3>
                    <div class="grid grid-cols-5 lg:grid-cols-4 gap-2">
                        @foreach($this->questions() as $index => $question)
                            <button
                                wire:click="goToQuestion({{ $index }})"
                                @class([
                                    'w-10 h-10 rounded-lg text-sm font-medium transition-all',
                                    'bg-primary-600 text-white' => $index === $currentQuestionIndex,
                                    'bg-green-600 text-white' => $index !== $currentQuestionIndex && isset($answers[$question->id]) && !empty($answers[$question->id]),
                                    'bg-gray-700 text-gray-300 hover:bg-gray-600' => $index !== $currentQuestionIndex && (!isset($answers[$question->id]) || empty($answers[$question->id])),
                                ])
                            >
                                {{ $index + 1 }}
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4 space-y-2 text-xs text-gray-400">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-primary-600 rounded"></div>
                            <span>Question actuelle</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-600 rounded"></div>
                            <span>Répondue</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-gray-700 rounded"></div>
                            <span>Non répondue</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zone de la question --}}
            <div class="lg:col-span-3">
                @if($this->currentQuestion)
                    <div class="bg-gray-800 rounded-lg p-6 mb-6">
                        {{-- Numéro et type de question --}}
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium text-primary-400">
                                Question {{ $currentQuestionIndex + 1 }} sur {{ $this->questions()->count() }}
                            </span>
                            <span class="px-3 py-1 bg-gray-700 rounded-full text-xs text-gray-300">
                                {{ $this->currentQuestion->points }} {{ $this->currentQuestion->points > 1 ? 'points' : 'point' }}
                            </span>
                        </div>

                        {{-- Texte de la question --}}
                        <div class="prose prose-invert prose-lg max-w-none mb-6">
                            <h3 class="text-white">{{ $this->currentQuestion->question_text }}</h3>
                        </div>

                        {{-- Image de la question (si présente) --}}
                        @if($this->currentQuestion->image)
                            <div class="mb-6">
                                <img src="{{ Storage::url($this->currentQuestion->image) }}"
                                     alt="Question image"
                                     class="rounded-lg max-w-full h-auto">
                            </div>
                        @endif

                        {{-- Options de réponse --}}
                        <div class="space-y-3">
                            @if($this->currentQuestion->question_type === \App\Enums\QuestionTypeEnum::SINGLE_CHOICE)
                                {{-- Choix unique --}}
                                @foreach($this->currentQuestion->options as $option)
                                    <label class="flex items-start gap-3 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors">
                                        <input
                                            type="radio"
                                            wire:model.live="answers.{{ $this->currentQuestion->id }}"
                                            value="{{ $option->id }}"
                                            class="mt-1 w-4 h-4 text-primary-600 bg-gray-900 border-gray-600 focus:ring-primary-500"
                                        >
                                        <div class="flex-1">
                                            <span class="text-white">{{ $option->option_text }}</span>
                                            @if($option->image)
                                                <img src="{{ Storage::url($option->image) }}"
                                                     alt="Option image"
                                                     class="mt-2 rounded max-w-sm h-auto">
                                            @endif
                                        </div>
                                    </label>
                                @endforeach

                            @elseif($this->currentQuestion->question_type === \App\Enums\QuestionTypeEnum::MULTIPLE_CHOICE)
                                {{-- Choix multiple --}}
                                <p class="text-sm text-gray-400 mb-2">Sélectionnez toutes les réponses correctes</p>
                                @foreach($this->currentQuestion->options as $option)
                                    <label class="flex items-start gap-3 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors">
                                        <input
                                            type="checkbox"
                                            wire:model.live="answers.{{ $this->currentQuestion->id }}"
                                            value="{{ $option->id }}"
                                            class="mt-1 w-4 h-4 text-primary-600 bg-gray-900 border-gray-600 rounded focus:ring-primary-500"
                                        >
                                        <div class="flex-1">
                                            <span class="text-white">{{ $option->option_text }}</span>
                                            @if($option->image)
                                                <img src="{{ Storage::url($option->image) }}"
                                                     alt="Option image"
                                                     class="mt-2 rounded max-w-sm h-auto">
                                            @endif
                                        </div>
                                    </label>
                                @endforeach

                            @elseif($this->currentQuestion->question_type === \App\Enums\QuestionTypeEnum::TRUE_FALSE)
                                {{-- Vrai/Faux --}}
                                @foreach($this->currentQuestion->options as $option)
                                    <label class="flex items-start gap-3 p-4 bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-600 transition-colors">
                                        <input
                                            type="radio"
                                            wire:model.live="answers.{{ $this->currentQuestion->id }}"
                                            value="{{ $option->id }}"
                                            class="mt-1 w-4 h-4 text-primary-600 bg-gray-900 border-gray-600 focus:ring-primary-500"
                                        >
                                        <span class="text-white">{{ $option->option_text }}</span>
                                    </label>
                                @endforeach

                            @elseif($this->currentQuestion->question_type === \App\Enums\QuestionTypeEnum::TEXT)
                                {{-- Réponse courte --}}
                                <input
                                    type="text"
                                    wire:model.blur="answers.{{ $this->currentQuestion->id }}"
                                    placeholder="Votre réponse..."
                                    class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >

                            @elseif($this->currentQuestion->question_type === \App\Enums\QuestionTypeEnum::ESSAY)
                                {{-- Réponse longue --}}
                                <textarea
                                    wire:model.blur="answers.{{ $this->currentQuestion->id }}"
                                    placeholder="Votre réponse détaillée..."
                                    rows="8"
                                    class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                ></textarea>
                            @endif
                        </div>
                    </div>

                    {{-- Boutons de navigation --}}
                    <div class="flex items-center justify-between">
                        <button
                            wire:click="previousQuestion"
                            @if($currentQuestionIndex === 0) disabled @endif
                            @class([
                                'px-6 py-3 rounded-lg font-medium transition-colors',
                                'bg-gray-700 text-white hover:bg-gray-600' => $currentQuestionIndex > 0,
                                'bg-gray-800 text-gray-500 cursor-not-allowed' => $currentQuestionIndex === 0,
                            ])
                        >
                            ← Précédent
                        </button>

                        @if($currentQuestionIndex === $this->questions()->count() - 1)
                            <button
                                wire:click="submitExam"
                                wire:confirm="Êtes-vous sûr de vouloir soumettre votre examen ? Vous ne pourrez plus modifier vos réponses."
                                class="px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors"
                            >
                                Soumettre l'examen
                            </button>
                        @else
                            <button
                                wire:click="nextQuestion"
                                class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
                            >
                                Suivant →
                            </button>
                        @endif
                    </div>
                @else
                    <div class="bg-gray-800 rounded-lg p-6 text-center">
                        <p class="text-gray-400">Aucune question disponible</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Script pour le timer --}}
    @if($timeRemaining !== null)
        @script
        <script>
            let timeRemaining = @js($timeRemaining);
            const timerElement = document.getElementById('timer');

            const countdown = setInterval(() => {
                timeRemaining--;

                if (timeRemaining <= 0) {
                    clearInterval(countdown);
                    $wire.submitExam();
                    return;
                }

                const hours = Math.floor(timeRemaining / 3600);
                const minutes = Math.floor((timeRemaining % 3600) / 60);
                const seconds = timeRemaining % 60;

                timerElement.textContent =
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');

                // Alert à 5 minutes
                if (timeRemaining === 300) {
                    alert('Il vous reste 5 minutes !');
                }

                // Alert à 1 minute
                if (timeRemaining === 60) {
                    alert('Il vous reste 1 minute !');
                }
            }, 1000);
        </script>
        @endscript
    @endif
</div>
