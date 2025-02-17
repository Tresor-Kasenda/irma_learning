<div>
    <section class="pt-32 flex flex-col gap-10">
        <div
            class="mx-auto max-w-7xl w-full px-5 sm:px-10 border border-border-lighter bg-bg/30 rounded-md h-14 sticky top-2 z-20 backdrop-blur-sm flex justify-between items-center">
            <div class="text-sm md:text-base text-fg-subtext">
                Nos formations
            </div>
            <div class="flex gap-3 items-center">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                         class="size-4 text-fg-subtext absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                         viewBox="0 0 256 256">
                        <path
                            d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z">
                        </path>
                    </svg>
                    <input type="text" name="app_user_name" id="app_user_name" placeholder="Rechercher"
                           class="ui-form-input px-4 h-9 rounded-md peer w-full ps-9"/>
                </div>
                <select class="ui-form-input px-4 h-9 rounded-md peer pe-9 text-fg">
                    <option>Type de formations</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                    <option value="type">Type 1</option>
                </select>
            </div>
        </div>
        <div class="mx-auto max-w-7xl w-full px-5 sm:px-10 grid">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($formations as $formation)
                    <div
                        class="bg-bg border border-border-light overflow-hidden shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50 rounded-lg">
                        <div class="rounded bg-bg-light aspect-video">
                            <img
                                src="{{ asset('storage/'. $formation->path) }}"
                                alt="{{ $formation->title  }}"
                                width="2000"
                                height="1333"
                                class="w-full h-full rounded-md object-cover">
                        </div>
                        <div class="p-6">
                            <h2>
                                <a href="{{ route('master-class', ['masterClass' => $formation]) }}" wire:navigate
                                   class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                    {{ $formation->title }}
                                </a>
                            </h2>
                            <p class="my-4 text-fg-subtext line-clamp-1">
                                {!! str($formation->description)->limit(80) !!}
                            </p>
                            <div class="flex items-center justify-between text-sm text-fg-subtext">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                         class="size-4" viewBox="0 0 256 256">
                                        <path
                                            d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z">
                                        </path>
                                    </svg>
                                    {{ $formation->duration }}H
                                </div>
                                <div class="flex items-center gap-1 font-semibold text-fg-subtitle text-xl">
                                    {{ $formation?->price }}
                                </div>
                            </div>
                            <a href="{{ route('master-class', ['masterClass' => $formation]) }}" wire:navigate
                               class="btn btn-md rounded w-full justify-center mt-7 btn-solid bg-primary-600 text-white group">
                                <span class="relative z-10">
                                    Suivre la formation
                                </span>
                                <span data-btn-layer class=" before:bg-primary-800"></span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-bg border border-border-light overflow-hidden shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50 rounded-lg">
                        <div class="rounded bg-bg-light aspect-video">
                            <img src="/image1.webp" alt="Image" width="2000" height="1333"
                                 class="w-full h-full rounded-md object-cover">
                        </div>
                        <div class="p-6">
                            <h2>
                                <a href="./details.html"
                                   class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                    Formation title
                                </a>
                            </h2>
                            <p class="my-4 text-fg-subtext line-clamp-1">
                                Lorem ipsum dolor sit, amet consectetur adipisicing elit
                            </p>
                            <div class="flex items-center justify-between text-sm text-fg-subtext">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                         class="size-4" viewBox="0 0 256 256">
                                        <path
                                            d="M128,40a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,40Zm0,176a80,80,0,1,1,80-80A80.09,80.09,0,0,1,128,216ZM173.66,90.34a8,8,0,0,1,0,11.32l-40,40a8,8,0,0,1-11.32-11.32l40-40A8,8,0,0,1,173.66,90.34ZM96,16a8,8,0,0,1,8-8h48a8,8,0,0,1,0,16H104A8,8,0,0,1,96,16Z">
                                        </path>
                                    </svg>
                                    12H
                                </div>
                                <div class="flex items-center gap-1 font-semibold text-fg-subtitle text-xl">
                                    120$
                                </div>
                            </div>
                            <a href="./adhesion.html"
                               class="btn btn-md rounded w-full justify-center mt-7 btn-solid bg-primary-600 text-white group">
                                <span class="relative z-10">
                                    Suivre la formation
                                </span>
                                <span data-btn-layer class=" before:bg-primary-800"></span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
