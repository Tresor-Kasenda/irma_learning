@props(['imageHalf' => false])
<section class="flex sm:px-1 pt-1 lg:px-1 xl:px-3 xl:pt-3">
    <div class="relative xl:max-w-[160rem] mx-auto w-full flex">
        @if ($imageHalf)
            <div class="absolute top-0 left-0 inset-x-0 h-40 flex">
                <span class="flex w-60 h-36 bg-gradient-to-tr from-primary rounded-full blur-2xl opacity-65"></span>
            </div>
            <div style="background-image: url({{ asset('/image_1.webp') }})"
                class="absolute bg-no-repeat bg-cover animate-dbackground-pan rounded-xl w-full  h-4/5 left-0 top-0 before:absolute before:inset-0 before:bg-gray-800/10 before:rounded-xl after:absolute after:inset-0 after:flex after:bg-gradient-to-br after:from-gray-800 after:rounded-xl">
            </div>
        @else
            <div style="background-image: url({{ asset('/image_1.webp') }})"
                class="absolute bg-no-repeat bg-cover animate-dbackground-pan rounded-xl w-full h-full left-0 top-0 before:absolute before:inset-0 before:bg-gray-800/10 before:rounded-xl after:absolute after:inset-0 after:flex after:bg-gradient-to-br after:from-gray-800 after:rounded-xl">
            </div>
        @endif


        <div class="w-full flex flex-col items-center relative">
            {{ $slot }}
        </div>
    </div>
</section>
