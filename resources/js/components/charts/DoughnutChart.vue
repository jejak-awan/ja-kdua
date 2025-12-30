<template>
    <Doughnut :data="chartData" :options="chartOptions" />
</template>

<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

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
});

const chartData = computed(() => {
    const labels = props.data.map(item => item[props.labelKey] || 'Unknown');
    const values = props.data.map(item => item[props.valueKey]);

    return {
        labels: labels,
        datasets: [
            {
                backgroundColor: [
                    '#4F46E5', // indigo
                    '#10B981', // green
                    '#F59E0B', // amber
                    '#EF4444', // red
                    '#8B5CF6', // purple
                    '#EC4899', // pink
                    '#3B82F6', // blue
                    '#6366F1', // indigo
                ],
                data: values,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
             labels: {
                usePointStyle: true,
                padding: 20,
            }
        },
    },
    cutout: '60%',
};
</script>
