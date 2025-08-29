@php
    use App\Enums\QuestionTypeEnum;$hasOptions = in_array($question->question_type, [
        QuestionTypeEnum::SINGLE_CHOICE,
        QuestionTypeEnum::MULTIPLE_CHOICE
    ]);
@endphp

<div class="p-4 space-y-6">
    {{-- Question Header --}}
    <div class="space-y-2">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Question #{{ $question->order_position }}
            </span>
            <x-filament::badge color="{{ match($question->question_type) {
                QuestionTypeEnum::SINGLE_CHOICE => 'success',
                QuestionTypeEnum::MULTIPLE_CHOICE => 'info',
                QuestionTypeEnum::TRUE_FALSE => 'warning',
                QuestionTypeEnum::ESSAY => 'gray',
                default => 'primary'
            } }}">
                {{ $question->question_type->getLabel() }}
            </x-filament::badge>
            @if($question->is_required)
                <x-filament::badge color="danger">Obligatoire</x-filament::badge>
            @endif
        </div>

        <h3 class="text-lg font-medium leading-6">
            {{ $question->question_text }}
        </h3>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $question->points }} point(s)
        </p>
    </div>

    {{-- Options --}}
    @if($hasOptions && $question->options->count() > 0)
        <div class="space-y-4">
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Options de réponse:</h4>

            <div class="space-y-2">
                @foreach($question->options->sortBy('order_position') as $option)
                    <div
                        class="flex items-center gap-2 p-2 rounded-lg {{ $option->is_correct ? 'bg-success-50 dark:bg-success-950' : 'bg-gray-50 dark:bg-gray-800' }}">
                        @if($question->question_type === QuestionTypeEnum::SINGLE_CHOICE)
                            <x-filament::icon
                                name="{{ $option->is_correct ? 'heroicon-m-check-circle' : 'heroicon-m-circle' }}"
                                class="{{ $option->is_correct ? 'text-success-500' : 'text-gray-400' }}"
                            />
                        @else
                            <x-filament::icon
                                name="{{ $option->is_correct ? 'heroicon-m-check-square' : 'heroicon-m-square-2-stack' }}"
                                class="{{ $option->is_correct ? 'text-success-500' : 'text-gray-400' }}"
                            />
                        @endif
                        <span class="text-sm">{{ $option->option_text }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Explanation --}}
    @if($question->explanation)
        <div class="space-y-2">
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Explication:</h4>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $question->explanation }}</p>
        </div>
    @endif
</div>
