<template>
    <Bar :data="chartData" :options="chartOptions" />
</template>

<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    labelKey: {
        type: String,
        required: true,
    },
    valueKey: {
        type: String,
        default: 'count',
    },
    horizontal: {
        type: Boolean,
        default: true,
    },
});

const chartData = computed(() => {
    return {
        labels: props.data.map(item => item[props.labelKey] || 'Unknown').slice(0, 10),
        datasets: [
            {
                label: 'Visits',
                backgroundColor: '#3B82F6', // blue-500
                data: props.data.map(item => item[props.valueKey]).slice(0, 10),
            },
        ],
    };
});

const chartOptions = computed(() => ({
    indexAxis: props.horizontal ? 'y' : 'x',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        x: {
            beginAtZero: true,
        },
    },
}));
</script>
