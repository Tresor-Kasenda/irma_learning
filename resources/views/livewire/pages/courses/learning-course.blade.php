@php
    $isSubscribed = $masterClass->subscription()->where('user_id', auth()->id())->exists();
@endphp
<div>
    <section class="my-32 mx-auto max-w-7xl w-full px-5 sm:px-10 flex flex-col md:flex-row gap-16">
        <article class="flex flex-col flex-1">
            <div class="flex flex-col">

                <h1 class="font-medium text-xl sm:text-2xl/snug lg:text-5xl text-fg-title">
                    {{ $masterClass->title }}
                </h1>
                <p class="font-medium mt-4 text-fg-subtext">
                    {{ $masterClass->sub_title }}
                </p>
            </div>
            <div class="mt-12 flex flex-col space-y-6">
                    <span class="text-lg font-semibold text-fg-title">
                        Presentation
                    </span>
                <div class="text-fg space-y-4">
                    <p>
                        {!!  $masterClass->presentation !!}
                    </p>
                </div>
            </div>
            <div class="mt-12 flex flex-col space-y-6">
                    <span class="text-lg font-semibold text-fg-title">
                        Aperçu
                    </span>
                <div class="text-fg space-y-4">
                    <p>
                        {!! $masterClass->description !!}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end">
                <button
                    wire:click.prevent="{{ $isSubscribed ? 'accessCourse' : 'subscribeToCourses' }}({{$masterClass}})"
                    class="group relative w-full btn btn-sm sm:btn-md justify-center overflow-hidden rounded-md btn-solid {{ $isSubscribed ? 'bg-green-600' : 'bg-primary-600' }} text-white">
                    <div class="flex items-center relative z-10">
                        <span>{{ $isSubscribed ? 'Accéder à la formation' : "S'inscrire maintenant" }}</span>
                        <div class="ml-1 transition duration-500 group-hover:rotate-[360deg]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </div>
                    </div>
                    <span data-btn-layer class="before:{{ $isSubscribed ? 'bg-green-800' : 'bg-primary-800' }}"></span>
                </button>
            </div>
        </article>
    </section>

</div>
