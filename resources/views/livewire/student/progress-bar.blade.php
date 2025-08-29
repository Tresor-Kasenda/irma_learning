<div>
    <div x-data="{ 
        progress: {{ $progress }},
        animate: false,
        init() {
            this.$nextTick(() => this.animate = true);
            this.$wire.on('progress-updated', newProgress => {
                this.animateProgress(newProgress);
            });
        },
        animateProgress(newValue) {
            let start = this.progress;
            let end = newValue;
            let startTimestamp = null;
            const duration = 1000;
            
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const elapsed = timestamp - startTimestamp;
                
                const progress = Math.min(elapsed / duration, 1);
                this.progress = start + (end - start) * progress;
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            
            window.requestAnimationFrame(step);
        }
    }" class="relative pt-1">
        <div class="flex mb-2 items-center justify-between">
            <div>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full"
                      :class="{
                          'text-green-600 bg-green-200': progress >= 100,
                          'text-blue-600 bg-blue-200': progress < 100
                      }">
                    Progression
                </span>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold inline-block"
                      :class="{
                          'text-green-600': progress >= 100,
                          'text-blue-600': progress < 100
                      }">
                    <span x-text="Math.round(progress)"></span>%
                </span>
            </div>
        </div>
        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
            <div :style="'width: ' + progress + '%'"
                 :class="{
                     'bg-green-500': progress >= 100,
                     'bg-blue-500': progress < 100,
                     'transition-all duration-1000': animate
                 }"
                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center">
            </div>
        </div>
    </div>
</div>
