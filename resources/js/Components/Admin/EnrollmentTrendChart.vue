<script lang="ts" setup>
import {Chart, type ChartConfiguration, registerables} from 'chart.js';
import {nextTick, onBeforeUnmount, onMounted, ref, watch} from 'vue';

Chart.register(...registerables);

const props = defineProps<{ values: {label: string; value: number}[] }>();
const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

function render(): void {
    if (!canvas.value) return;
    chart?.destroy();
    const configuration: ChartConfiguration<'line'> = {
        type: 'line',
        data: {
            labels: props.values.map((item) => item.label),
            datasets: [{
                label: 'Inscriptions',
                data: props.values.map((item) => item.value),
                borderColor: '#c23a72',
                backgroundColor: 'rgba(194, 58, 114, 0.14)',
                pointBackgroundColor: '#ef477d',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2,
                tension: 0.35,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {mode: 'index', intersect: false},
            plugins: {legend: {display: false}, tooltip: {displayColors: false}},
            scales: {
                x: {grid: {display: false}, ticks: {color: '#94a3b8', maxRotation: 0}, border: {color: 'rgba(148, 163, 184, 0.18)'}},
                y: {beginAtZero: true, ticks: {color: '#94a3b8', precision: 0}, grid: {color: 'rgba(148, 163, 184, 0.10)'}, border: {display: false}},
            },
        },
    };
    chart = new Chart(canvas.value, configuration);
}

watch(() => props.values, async () => {
    await nextTick();
    render();
}, {deep: true});
onMounted(render);
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="h-72 min-w-0" role="img" aria-label="Courbe du nombre d’inscriptions sur la période sélectionnée">
        <canvas ref="canvas"/>
    </div>
</template>
