<script setup>
import { ref, computed } from 'vue';
import { MessageSquare, User } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: 'Comments' },
    show_avatar: { type: Boolean, default: true },
    show_reply: { type: Boolean, default: true },
    show_date: { type: Boolean, default: true },
    padding: { type: String, default: 'py-8' },
    bgColor: { type: String, default: '' }
});

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

// Demo comments since we don't have a backend comments API yet
const comments = ref([
    {
        id: 1,
        author: 'John Doe',
        avatar: '',
        date: '2 hours ago',
        content: 'This is a great article! Thanks for sharing this information.',
        replies: []
    },
    {
        id: 2,
        author: 'Jane Smith',
        avatar: '',
        date: '5 hours ago',
        content: 'I have a question about the second point. Could you elaborate more?',
        replies: [
            {
                id: 3,
                author: 'Author',
                avatar: '',
                date: '4 hours ago',
                content: 'Sure! The second point basically means that...',
                isAuthor: true
            }
        ]
    }
]);

const newComment = ref('');

const submitComment = () => {
    if (!newComment.value.trim()) return;
    
    // Simulate submission
    comments.value.unshift({
        id: Date.now(),
        author: 'Guest User',
        avatar: '',
        date: 'Just now',
        content: newComment.value,
        replies: []
    });
    
    newComment.value = '';
};
</script>

<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div class="container mx-auto px-6 max-w-4xl">
            <h3 v-if="title" class="text-2xl font-bold mb-8 flex items-center gap-2">
                <MessageSquare class="w-6 h-6" />
                {{ title }} <span class="text-muted-foreground text-lg font-normal">({{ comments.length }})</span>
            </h3>
            
            <!-- Comment Form -->
            <div class="bg-card border rounded-xl p-6 mb-10 shadow-sm">
                <h4 class="font-bold mb-4">Leave a Reply</h4>
                <textarea 
                    v-model="newComment"
                    rows="3"
                    class="w-full rounded-lg border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary min-h-[100px]"
                    placeholder="Write your comment here..."
                ></textarea>
                <div class="mt-4 flex justify-end">
                    <button 
                        @click="submitComment"
                        class="px-6 py-2 bg-primary text-primary-foreground font-bold rounded-lg hover:opacity-90 transition-all text-sm"
                    >
                        Post Comment
                    </button>
                </div>
            </div>
            
            <!-- Comments List -->
            <div class="space-y-8">
                <div v-for="comment in comments" :key="comment.id" class="flex gap-4">
                    <!-- Avatar -->
                    <div v-if="show_avatar" class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-muted flex items-center justify-center overflow-hidden">
                            <img v-if="comment.avatar" :src="comment.avatar" :alt="comment.author" class="w-full h-full object-cover">
                            <User v-else class="w-5 h-5 text-muted-foreground" />
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <div class="bg-muted/30 rounded-xl p-4">
                            <div class="flex items-baseline justify-between mb-1">
                                <h5 class="font-bold text-sm">{{ comment.author }} <span v-if="comment.isAuthor" class="ml-2 px-1.5 py-0.5 bg-primary/10 text-primary text-[10px] rounded tracking-wider">Author</span></h5>
                                <span v-if="show_date" class="text-xs text-muted-foreground">{{ comment.date }}</span>
                            </div>
                            <p class="text-sm leading-relaxed">{{ comment.content }}</p>
                        </div>
                        
                        <div v-if="show_reply" class="mt-2 pl-2">
                            <button class="text-xs font-bold text-muted-foreground hover:text-foreground transition-colors">Reply</button>
                        </div>
                        
                        <!-- Replies -->
                        <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 space-y-4">
                            <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-4">
                                <div v-if="show_avatar" class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-muted flex items-center justify-center overflow-hidden">
                                        <img v-if="reply.avatar" :src="reply.avatar" :alt="reply.author" class="w-full h-full object-cover">
                                        <User v-else class="w-4 h-4 text-muted-foreground" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-muted/30 rounded-xl p-3">
                                        <div class="flex items-baseline justify-between mb-1">
                                            <h5 class="font-bold text-xs">{{ reply.author }} <span v-if="reply.isAuthor" class="ml-2 px-1.5 py-0.5 bg-primary/10 text-primary text-[10px] rounded tracking-wider">Author</span></h5>
                                            <span v-if="show_date" class="text-[10px] text-muted-foreground">{{ reply.date }}</span>
                                        </div>
                                        <p class="text-xs leading-relaxed">{{ reply.content }}</p>
                                    </div>
                                    <div v-if="show_reply" class="mt-1 pl-2">
                                        <button class="text-[10px] font-bold text-muted-foreground hover:text-foreground transition-colors">Reply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
