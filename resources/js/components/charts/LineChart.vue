<template>
    <Line :data="chartData" :options="chartOptions" />
</template>

<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    label: {
        type: String,
        default: 'Visits',
    },
    color: {
        type: String,
        default: '#4F46E5', // indigo-600
    },
    fillColor: {
        type: String,
        default: 'rgba(79, 70, 229, 0.1)',
    },
});

const chartData = computed(() => {
    return {
        labels: props.data.map(item => item.period),
        datasets: [
            {
                label: props.label,
                backgroundColor: props.fillColor,
                borderColor: props.color,
                data: props.data.map(item => item.visits),
                fill: true,
                tension: 0.4,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            mode: 'index',
            intersect: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
};
</script>
