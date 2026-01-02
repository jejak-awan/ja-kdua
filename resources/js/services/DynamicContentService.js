
import { ref } from 'vue';

class DynamicContentService {
    constructor() {
        this.sources = ref([]);
        this.initializeDefaultSources();
    }

    register(source) {
        // source: { id: 'post.title', label: 'Post Title', group: 'Post', handler: (context) => val }
        this.sources.value.push(source);
    }

    getSources() {
        return this.sources.value;
    }

    resolve(sourceId, context) {
        const source = this.sources.value.find(s => s.id === sourceId);
        if (!source) return null;
        try {
            return source.handler(context);
        } catch (e) {
            console.warn(`Failed to resolve dynamic content: ${sourceId}`, e);
            return null;
        }
    }

    initializeDefaultSources() {
        // Post Data
        this.register({
            id: 'post.title',
            label: 'Post Title',
            group: 'Post',
            icon: 'FileText',
            handler: (ctx) => ctx?.post?.title || 'Current Post Title'
        });

        this.register({
            id: 'post.excerpt',
            label: 'Post Excerpt',
            group: 'Post',
            icon: 'AlignLeft',
            handler: (ctx) => ctx?.post?.excerpt || 'Post excerpt goes here...'
        });

        this.register({
            id: 'post.date',
            label: 'Publish Date',
            group: 'Post',
            icon: 'Calendar',
            handler: (ctx) => ctx?.post?.published_at || new Date().toLocaleDateString()
        });

        this.register({
            id: 'post.author',
            label: 'Author Name',
            group: 'Post',
            icon: 'User',
            handler: (ctx) => ctx?.post?.author?.name || 'Author Name'
        });

        // Site Data
        this.register({
            id: 'site.name',
            label: 'Site Name',
            group: 'Site',
            icon: 'Globe',
            handler: (ctx) => ctx?.site?.name || 'My Website'
        });
    }
}

export const dynamicContent = new DynamicContentService();
