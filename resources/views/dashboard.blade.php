<div>
    <div class="absolute inset-x-0 -top-2 h-56 bg-bg-lighter rounded-b-xl"></div>

    <div class="px-4 sm:px-10 lg:px-5 xl:px-8 xl:max-w-[88rem] w-full mx-auto">
        <!-- Content -->
        <div class="relative pt-8 grid gap-8">
            <div class="flex flex-col">
                <h1 class="text-xl font-semibold text-fg-subtitle">Mes statistiques</h1>
            </div>


            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Mes formations</h3>
                        <div class="p-2 bg-primary-100 rounded-lg">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">{{ $this->statistics()['total'] }}</p>
                        <p class="text-fg-subtext">Participations</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Masterclass en cours</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">{{ $this->statistics()['in_progress'] }}</p>
                        <p class="text-fg-subtext">En cours</p>
                    </div>
                </div>
                <div class="bg-bg border border-border-light rounded-md p-6 shadow-sm shadow-gray-100/40">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-fg-title">Masterclass completees</h3>
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl sm:text-4xl font-semibold text-fg-subtitle">{{ $this->statistics()['completed'] }}</p>
                        <p class="text-fg-subtext">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-16 grid gap-8">
            <div class="flex flex-col">
                <h1 class="text-xl font-semibold text-fg-subtitle">Liste des cours</h1>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6" wire:poll.keep-alive>
                @foreach($this->masterClasses() as $masterClass)
                    <a
                        wire:key="{{ $masterClass->id }}"
                        href="{{ route('master-class', $masterClass) }}"
                        class="bg-bg border border-border-light rounded-lg p-0.5 shadow-sm shadow-gray-100/40 group hover:shadow-gray-200/50">
                        <div class="p-4 sm:p-5">
                            <h2 class="text-lg sm:text-xl font-semibold text-fg-subtitle group-hover:text-primary-600 ease-linear duration-200">
                                {{ $masterClass->title }}
                            </h2>
                            <div class="flex mt-3">
                            <span class="flex items-center gap-1 text-sm text-fg-subtext">
                              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                   class="size-4"
                                   viewBox="0 0 256 256">
                                <path
                                    d="M232,48H160a40,40,0,0,0-32,16A40,40,0,0,0,96,48H24a8,8,0,0,0-8,8V200a8,8,0,0,0,8,8H96a24,24,0,0,1,24,24,8,8,0,0,0,16,0,24,24,0,0,1,24-24h72a8,8,0,0,0,8-8V56A8,8,0,0,0,232,48ZM96,192H32V64H96a24,24,0,0,1,24,24V200A39.81,39.81,0,0,0,96,192Zm128,0H160a39.81,39.81,0,0,0-24,8V88a24,24,0,0,1,24-24h64Z">
                                </path>
                              </svg>
                              {{ $masterClass->chapters_count }} Chapitres
                            </span>
                            </div>
                            <div class="mt-3 flex flex-col gap-1.5">
                                <div class="justify-between w-full flex items-center text-sm text-fg-subtext">
                                    <span>{{ $masterClass->completed_chapters_count }}/{{ $masterClass->chapters_count }}</span>
                                    <span>{{ $masterClass->progress }}%</span>
                                </div>
                                <div class="w-full flex bg-bg-high rounded-full h-1">
                                    <span class="bg-primary-600 h-full rounded-full"
                                          style="width: {{ $masterClass->progress }}%;"></span>
                                </div>
                            </div>
                            <div class="flex w-full mt-7 pb-2">
                                <div
                                    class="w-full btn btn-md justify-center before:bg-primary-600 btn-styled-y group rounded before:rounded border border-border-light shadow-lg shadow-gray-50 text-fg-subtitle hover:text-white">
                                    <span class="relative">Certifier</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
