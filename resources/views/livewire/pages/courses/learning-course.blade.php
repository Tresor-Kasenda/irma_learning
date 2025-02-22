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
        </article>
        <div
            class="md:sticky h-max z-[20] md:w-72 lg:w-80 xl:w-[22.5rem] bg-bg w-full shadow-xl shadow-bg-high/50 rounded-md p-5 md:p-6 border border-border-light/40">
            <span class="font-medium text-fg text-sm mb-4 pb-3 border-b w-full flex">Details du cours </span>
            <ul class="flex flex-col divide-y divide-border-light *:py-3 first:*:pt-0 last:*:pb-0 mt-4">
                <li class="flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-fg-title">
                            {{ $masterClass->title }}
                        </div>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <div class="flex items-center text-fg-subtext text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z"/>
                        </svg>
                        <span>Date</span>
                    </div>
                    <div class="font-semibold text-fg-title text-right">
                        {{ $masterClass->created_at->format('d/m/Y') }}
                    </div>
                </li>
                <li class="flex flex-col">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-fg-subtext text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="size-4 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <span>Durée </span>
                        </div>
                        <div class="font-semibold text-fg-title text-right">
                            {{ $masterClass->duration }} H
                        </div>
                    </div>
                </li>
                <li class="flex flex-col">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                        </svg>
                        <span class="text-fg-subtext text-sm">Tarif</span>
                    </div>
                    <div class="flex flex-col flex-1">
                        <ul
                            class="bg-bg-lighter border border-border-light rounded-md px-2 py-1 mt-2 text-fg list-disc flex flex-col gap-1">
                            <li class="flex justify-between items-center">
                                <span class="text-sm text-fg-subtext">Prix: </span>
                                <span class="text-right text-fg-subtitle font-semibold">{{ $masterClass->price }}</span>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <div class="flex items-center text-fg-subtext text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25"/>
                        </svg>
                        <span>Certification</span>
                    </div>
                    <div class="font-semibold text-right flex items-center">
                        @if($masterClass->certifiable)
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                 class="size-5 mr-1">
                                <path fill-rule="evenodd"
                                      d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <span>Oui</span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                 class="size-5 mr-1">
                                <path fill-rule="evenodd"
                                      clip-rule="evenodd"/>
                            </svg>
                            <span>Non</span>
                        @endif
                    </div>
                </li>
                <li class="flex flex-wrap gap-2">
                    @foreach($masterClass->chapters()->take(3)->get() as $chapter)
                        <span class="bg-bg-lighter border border-border-light text-fg px-2 py-1 rounded text-sm">
                            {{ str($chapter->title)->ucfirst() }}
                        </span>
                    @endforeach
                </li>
                <li>
                    <button
                        @if(auth()->check())
                            wire:click.prevent="{{ $isSubscribed ? 'accessCourse' : 'subscribeToCourses' }}({{$masterClass}})"
                        @else
                            onclick="window.location.href='{{ route('login') }}'"
                        @endif
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
                        <span data-btn-layer
                              class="before:{{ $isSubscribed ? 'bg-green-800' : 'bg-primary-800' }}"></span>
                    </button>
                </li>
            </ul>
        </div>
    </section>

</div>
