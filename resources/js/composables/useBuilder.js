import { ref, computed } from 'vue';
import {
    LayoutTemplate, Type, Image as ImageIcon, Grid,
    MousePointer2, Clapperboard, Smartphone, Tablet, Monitor,
    List, Columns as ColumnsIcon, Images, CreditCard, MoveVertical, MessageSquareQuote
} from 'lucide-vue-next';

// Shared state
export const availableBlocks = [
    {
        name: 'hero',
        label: 'Hero Header',
        icon: LayoutTemplate,
        description: 'Large hero banner with title and background.',
        defaultSettings: { title: 'New Hero Header', subtitle: 'Experience the next generation of visual editing.', bgImage: '', padding: 'py-32', bgColor: 'transparent', radius: 'rounded-none', animation: 'animate-in fade-in duration-700', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    {
        name: 'text',
        label: 'Rich Text',
        icon: Type,
        description: 'Rich text area for your page body.',
        defaultSettings: { title: 'Section Title', content: 'Design beautiful layouts with zero compromise on performance. JA-Builder gives you the power of professional tools directly in your browser.', alignment: 'text-left', padding: 'py-16', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    {
        name: 'image',
        label: 'Smooth Image',
        icon: ImageIcon,
        description: 'Display high-quality images with custom effects.',
        defaultSettings: { title: '', url: '', width: 'max-w-5xl', padding: 'py-16', bgColor: 'transparent', radius: 'rounded-2xl', animation: 'animate-in zoom-in-95 duration-1000', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    {
        name: 'features',
        label: 'Feature Grid',
        icon: Grid,
        description: 'Display features in a responsive grid.',
        defaultSettings: {
            title: 'Core Features', items: [
                { title: 'Visual Editor', description: 'Drag and drop elements with real-time feedback.' },
                { title: 'Responsive', description: 'Perfect look on every screen size.' },
                { title: 'Lightning Fast', description: 'Optimized for speed and SEO.' }
            ], padding: 'py-20', bgColor: 'transparent', radius: 'rounded-none', animation: '', visibility: { mobile: true, tablet: true, desktop: true }
        }
    },
    {
        name: 'cta',
        label: 'Action Bar',
        icon: MousePointer2,
        description: 'Eye-catching section with a primary button.',
        defaultSettings: { title: 'Start Building Today', subtitle: 'Join thousands of creators using JA-Builder.', buttonText: 'Get Started Now', buttonUrl: '#', padding: 'py-32', bgColor: '#4f46e5', radius: 'rounded-2xl', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    {
        name: 'video',
        label: 'Video Player',
        icon: Clapperboard,
        description: 'Embed cinematic video content.',
        defaultSettings: { title: '', videoUrl: '', autoplay: false, padding: 'py-16', bgColor: 'transparent', radius: 'rounded-3xl', animation: '', visibility: { mobile: true, tablet: true, desktop: true } }
    },
    {
        name: 'accordion',
        label: 'Accordion',
        icon: List,
        description: 'Expandable list for FAQs or details.',
        defaultSettings: {
            title: 'Frequently Asked Questions',
            items: [
                { question: 'What is JA-Builder?', answer: 'A powerful visual page builder for modern web apps.' },
                { question: 'Is it responsive?', answer: 'Yes, it works perfectly on all devices.' },
                { question: 'Can I customize it?', answer: 'Absolutely! It is built with Vue and Tailwind CSS.' }
            ],
            width: 'max-w-5xl',
            padding: 'py-20',
            bgColor: 'transparent',
            radius: 'rounded-none',
            animation: '',
            visibility: { mobile: true, tablet: true, desktop: true }
        }
    },
    {
        name: 'columns',
        label: 'Columns',
        icon: ColumnsIcon,
        description: 'Create multi-column layouts.',
        defaultSettings: {
            layout: '1-1', // 1-1, 1-2, 2-1, 1-1-1, 1-1-1-1
            columns: [{ blocks: [] }, { blocks: [] }], // Default 2 columns
            padding: 'py-16',
            width: 'max-w-7xl',
            bgColor: 'transparent',
            radius: 'rounded-none',
            animation: '',
            visibility: { mobile: true, tablet: true, desktop: true }
        }
    },
    {
        name: 'gallery',
        label: 'Image Gallery',
        icon: Images,
        description: 'Grid of images with captions.',
        defaultSettings: {
            title: 'Our Gallery',
            images: [
                { url: '', caption: 'Project One' },
                { url: '', caption: 'Project Two' },
                { url: '', caption: 'Project Three' }
            ],
            columns: 3,
            width: 'max-w-6xl',
            padding: 'py-16',
            bgColor: 'transparent',
            radius: 'rounded-none',
            animation: '',
            visibility: { mobile: true, tablet: true, desktop: true }
        }
    },
    {
        name: 'pricing',
        label: 'Pricing Table',
        icon: CreditCard,
        description: 'Compare plans and pricing.',
        defaultSettings: {
            title: 'Simple Pricing',
            items: [
                { name: 'Starter', price: '$29', features: ['All Core Features', '5 Projects', 'Community Support'], buttonText: 'Start Free' },
                { name: 'Pro', price: '$99', features: ['Everything in Starter', 'Unlimited Projects', 'Priority Support', 'Advanced Analytics'], buttonText: 'Go Pro' },
                { name: 'Enterprise', price: '$299', features: ['Custom Solutions', 'Dedicated Manager', 'SLA', 'SSO'], buttonText: 'Contact Us' }
            ],
            width: 'max-w-6xl',
            padding: 'py-20',
            bgColor: 'transparent',
            radius: 'rounded-none',
            animation: '',
            visibility: { mobile: true, tablet: true, desktop: true }
        }
    },
    {
        name: 'testimonial',
        label: 'Testimonials',
        icon: MessageSquareQuote,
        description: 'Showcase social proof.',
        defaultSettings: {
            title: 'What Clients Say',
            items: [
                { quote: 'This tool completely changed how we work. Highly recommended!', author: 'Sarah Johnson', role: 'Product Manager', avatar: '' },
                { quote: 'The best builder experience I have ever had. Smooth and intuitive.', author: 'Mike Chen', role: 'Developer', avatar: '' },
                { quote: 'Incredible flexibility and performance. A game changer.', author: 'Emily Davis', role: 'Designer', avatar: '' }
            ],
            width: 'max-w-7xl',
            padding: 'py-20',
            bgColor: 'transparent',
            radius: 'rounded-none',
            animation: '',
            visibility: { mobile: true, tablet: true, desktop: true }
        }
    },
    {
        name: 'spacer',
        label: 'Spacer / Divider',
        icon: MoveVertical,
        description: 'Add vertical space or lines.',
        defaultSettings: {
            height: 'h-24',
            showLine: false,
            padding: 'py-0',
            bgColor: 'transparent',
            animation: '',
            visibility: { mobile: true, tablet: true, desktop: true }
        }
    }
];

export function useBuilder() {
    // State
    const deviceMode = ref('desktop');
    const editingIndex = ref(null);
    const activeTab = ref('content');
    const isSidebarOpen = ref(true); // Default open (left sidebar)
    const isRightSidebarOpen = ref(true);
    const activeRightSidebarTab = ref('properties'); // 'properties' | 'layers'
    const widgetSearch = ref('');
    const showMediaPicker = ref(false);
    const showTemplateLibrary = ref(false);
    const isPreview = ref(false);

    // Media Picker Context
    const activeMediaField = ref(null);
    const activeBlockId = ref(null);

    // Data
    const blocks = ref([]); // We will sync this with props in the root component

    // Helpers
    const getBlockLabel = (type) => availableBlocks.find(b => b.name === type)?.label || 'Generic Block';
    const getBlockComponent = (type) => availableBlocks.find(b => b.name === type);

    const componentMap = {
        HeroHeader: () => import('@/components/builder/blocks/HeroBlock.vue'),
        RichText: () => import('@/components/builder/blocks/TextBlock.vue'),
        SmoothImage: () => import('@/components/builder/blocks/ImageBlock.vue'),
        FeatureGrid: () => import('@/components/builder/blocks/FeatureGridBlock.vue'),
        ActionBar: () => import('@/components/builder/blocks/CTABlock.vue'),
        VideoPlayer: () => import('@/components/builder/blocks/VideoBlock.vue'),
        Accordion: () => import('@/components/builder/blocks/AccordionBlock.vue'),
        Columns: () => import('@/components/builder/blocks/ColumnsBlock.vue'),
        Gallery: () => import('@/components/builder/blocks/GalleryBlock.vue'),
        Pricing: () => import('@/components/builder/blocks/PricingBlock.vue'),
        Testimonial: () => import('@/components/builder/blocks/TestimonialBlock.vue'),
        Spacer: () => import('@/components/builder/blocks/SpacerBlock.vue'),
    };

    const cloneBlock = (block) => {
        return {
            id: crypto.randomUUID(),
            type: block.name,
            settings: JSON.parse(JSON.stringify(block.defaultSettings))
        };
    };

    // Actions
    const addBlock = (block, index = null) => {
        if (index !== null) {
            blocks.value.splice(index, 0, block);
        } else {
            blocks.value.push(block);
        }
    };

    const removeBlock = (index) => {
        blocks.value.splice(index, 1);
        if (editingIndex.value === index) editingIndex.value = null;
    };

    const duplicateBlock = (index) => {
        const original = blocks.value[index];
        const clone = {
            ...JSON.parse(JSON.stringify(original)),
            id: crypto.randomUUID()
        };
        blocks.value.splice(index + 1, 0, clone);
    };

    const updateBlock = (index, newBlock) => {
        blocks.value[index] = newBlock;
    };

    return {
        // State
        blocks, // Exposed for components to read/write (reactive)
        deviceMode,
        editingIndex,
        activeTab,
        widgetSearch,
        showMediaPicker,
        showTemplateLibrary,
        isPreview,
        isSidebarOpen,
        isRightSidebarOpen,
        activeRightSidebarTab,
        activeMediaField,
        activeBlockId,
        availableBlocks,

        // Methods
        getBlockLabel,
        getBlockComponent,
        cloneBlock,
        addBlock,
        removeBlock,
        duplicateBlock,
        updateBlock
    };
}
