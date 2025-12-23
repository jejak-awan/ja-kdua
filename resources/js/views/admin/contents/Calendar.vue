<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">Content Calendar</h1>
            <div class="flex items-center space-x-3">
                <select
                    v-model="statusFilter"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="published">Published</option>
                </select>
                <select
                    v-model="categoryFilter"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
                <router-link
                    :to="{ name: 'contents.create' }"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Content
                </router-link>
            </div>
        </div>

        <div class="bg-card shadow rounded-lg p-4">
            <FullCalendar
                ref="calendar"
                :options="calendarOptions"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import api from '../../../services/api';
import { parseResponse, ensureArray } from '../../../utils/responseParser';

const router = useRouter();
const calendar = ref(null);
const contents = ref([]);
const categories = ref([]);
const statusFilter = ref('');
const categoryFilter = ref('');

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,dayGridDay',
    },
    events: calendarEvents.value,
    editable: true,
    droppable: false,
    eventDrop: handleEventDrop,
    eventClick: handleEventClick,
    dateClick: handleDateClick,
    eventClassNames: getEventClassNames,
    eventContent: renderEventContent,
}));

const calendarEvents = computed(() => {
    return contents.value
        .filter(content => {
            if (statusFilter.value && content.status !== statusFilter.value) return false;
            if (categoryFilter.value && content.category_id != categoryFilter.value) return false;
            return true;
        })
        .map(content => ({
            id: content.id,
            title: content.title,
            start: content.published_at || content.created_at,
            allDay: true,
            backgroundColor: getStatusColor(content.status),
            borderColor: getStatusColor(content.status),
            extendedProps: {
                status: content.status,
                category: content.category,
                author: content.author,
            },
        }));
});

const getStatusColor = (status) => {
    const colors = {
        draft: '#6b7280',
        scheduled: '#f59e0b',
        published: '#10b981',
        archived: '#ef4444',
    };
    return colors[status] || '#6b7280';
};

const getEventClassNames = (arg) => {
    const status = arg.event.extendedProps.status;
    return [`status-${status}`];
};

const renderEventContent = (arg) => {
    const content = arg.event.extendedProps;
    const categoryName = content.category?.name || '';
    return {
        html: `
            <div class="fc-event-title-container">
                <div class="fc-event-title">${arg.event.title}</div>
                ${categoryName ? `<div class="fc-event-category">${categoryName}</div>` : ''}
            </div>
        `,
    };
};

const handleEventDrop = async (info) => {
    const contentId = info.event.id;
    const newDate = info.event.start;

    try {
        await api.put(`/admin/cms/contents/${contentId}`, {
            published_at: newDate.toISOString().split('T')[0],
        });
        await fetchContents();
    } catch (error) {
        console.error('Failed to reschedule content:', error);
        alert('Failed to reschedule content');
        info.revert();
    }
};

const handleEventClick = (info) => {
    router.push({ name: 'contents.edit', params: { id: info.event.id } });
};

const handleDateClick = (info) => {
    router.push({
        name: 'contents.create',
        query: { date: info.dateStr },
    });
};

const fetchContents = async () => {
    try {
        const response = await api.get('/admin/cms/contents', {
            params: {
                per_page: 1000, // Get all for calendar
            },
        });
        const { data } = parseResponse(response);
        contents.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch contents:', error);
        contents.value = [];
    }
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/admin/cms/categories');
        const { data } = parseResponse(response);
        categories.value = ensureArray(data);
    } catch (error) {
        console.error('Failed to fetch categories:', error);
        categories.value = [];
    }
};

watch([statusFilter, categoryFilter], () => {
    if (calendar.value) {
        calendar.value.getApi().refetchEvents();
    }
});

onMounted(() => {
    fetchContents();
    fetchCategories();
});
</script>

<style>
/* FullCalendar styles are imported via JavaScript */

.fc-event-title-container {
    padding: 2px 4px;
}

.fc-event-title {
    font-weight: 600;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.fc-event-category {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-top: 2px;
}

.fc-event.status-draft {
    opacity: 0.7;
}

.fc-event.status-scheduled {
    border-width: 2px;
}

.fc-event.status-published {
    font-weight: 600;
}
</style>

