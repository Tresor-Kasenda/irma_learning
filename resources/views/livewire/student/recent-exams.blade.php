<div>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($attempts as $attempt)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div @class([
                                        'h-8 w-8 rounded-full flex items-center justify-center',
                                        'bg-green-100' => $attempt->percentage >= $attempt->exam->passing_score,
                                        'bg-red-100' => $attempt->percentage < $attempt->exam->passing_score,
                                    ])>
                                        <span @class([
                                            'text-sm font-medium',
                                            'text-green-800' => $attempt->percentage >= $attempt->exam->passing_score,
                                            'text-red-800' => $attempt->percentage < $attempt->exam->passing_score,
                                        ])>
                                            {{ round($attempt->percentage) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ $attempt->exam->title }}
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        {{ $attempt->exam->examable->title }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <span @class([
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                    'bg-green-100 text-green-800' => $attempt->percentage >= $attempt->exam->passing_score,
                                    'bg-red-100 text-red-800' => $attempt->percentage < $attempt->exam->passing_score,
                                ])>
                                    {{ $attempt->percentage >= $attempt->exam->passing_score ? 'Réussi' : 'Échoué' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <p class="flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $attempt->completed_at?->diffForHumans() }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    Score: {{ $attempt->score }}/{{ $attempt->max_score }} points
                                </p>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-5 sm:px-6">
                    <div class="text-center text-sm text-gray-500">
                        Aucun examen passé pour le moment.
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
</div>
