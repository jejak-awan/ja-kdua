<script setup>
import { computed } from 'vue';
import { Check } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    items: { type: Array, default: () => [] },
    columns: { type: [Number, String], default: 3 },
    gap: { type: [Number, String], default: 24 },
    accentColor: { type: String, default: '#2059ea' },
    cardBackgroundColor: { type: String, default: '#ffffff' },
    featuredCardBackgroundColor: { type: String, default: '#ffffff' },
    padding: { type: String, default: 'py-20' },
    bgColor: { type: String, default: 'transparent' }
});

const gridStyles = computed(() => {
    const cols = parseInt(props.columns) || 3;
    const gapVal = parseInt(props.gap) || 24;
    return {
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: `${gapVal}px`
    };
});

const getCardStyles = (item) => ({
    backgroundColor: item.isFeatured ? props.featuredCardBackgroundColor : props.cardBackgroundColor,
    borderColor: item.isFeatured ? props.accentColor : '#f1f5f9'
});

const getButtonStyles = (item) => ({
    backgroundColor: item.isFeatured ? props.accentColor : '#f1f5f9',
    color: item.isFeatured ? '#ffffff' : '#1e293b'
});

const parseFeatures = (features) => {
    if (!features) return [];
    if (Array.isArray(features)) return features;
    return features.split('\n').filter(f => f.trim() !== '');
};
</script>

<template>
    <section 
        class="pricing-section"
        :class="[padding]"
        :style="{ backgroundColor: bgColor }"
    >
        <div class="container mx-auto px-6">
            <h2 v-if="title" class="pricing-section-title">{{ title }}</h2>
            
            <div class="pricing-grid" :style="gridStyles">
                <article 
                    v-for="(item, index) in items" 
                    :key="index"
                    class="pricing-card"
                    :class="{ 'pricing-card--featured': item.isFeatured }"
                    :style="getCardStyles(item)"
                >
                    <div v-if="item.isFeatured" class="pricing-badge" :style="{ backgroundColor: accentColor }">
                        Most Popular
                    </div>
                    
                    <div class="pricing-header">
                        <h3 class="plan-title">{{ item.title }}</h3>
                        <div class="plan-price">
                            <span class="currency">{{ item.currency || '$' }}</span>
                            <span class="amount">{{ item.price }}</span>
                            <span class="period">{{ item.period || '/mo' }}</span>
                        </div>
                    </div>

                    <ul class="plan-features">
                        <li v-for="(feature, fIndex) in parseFeatures(item.features)" :key="fIndex" class="plan-feature">
                            <Check class="check-icon" :size="16" :style="{ color: accentColor }" />
                            <span>{{ feature }}</span>
                        </li>
                    </ul>

                    <div class="pricing-footer">
                        <a 
                            :href="item.buttonUrl || '#'" 
                            class="pricing-button"
                            :style="getButtonStyles(item)"
                        >
                            {{ item.buttonText || 'Get Started' }}
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
.pricing-section { width: 100%; transition: all 0.5s ease; }
.pricing-section-title { font-size: 2.5rem; font-weight: 800; text-align: center; margin-bottom: 4rem; letter-spacing: -0.025em; }

.pricing-grid { width: 100%; }

.pricing-card { 
    position: relative; 
    padding: 2.5rem; 
    border-radius: 1.5rem; 
    border: 1px solid #f1f5f9; 
    display: flex; 
    flex-direction: column; 
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.pricing-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

.pricing-card--featured { 
    border-width: 2px;
    transform: scale(1.05);
    z-index: 10;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
.pricing-card--featured:hover { transform: scale(1.05) translateY(-4px); }

.pricing-badge { 
    position: absolute; 
    top: -1rem; 
    left: 50%; 
    transform: translateX(-50%); 
    color: white; 
    font-size: 0.75rem; 
    font-weight: 700; 
    padding: 0.25rem 0.75rem; 
    border-radius: 9999px;
}

.pricing-header { text-align: center; margin-bottom: 2rem; }
.plan-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; }
.plan-price { display: flex; align-items: baseline; justify-content: center; gap: 0.25rem; color: #0f172a; }
.currency { font-size: 1.5rem; font-weight: 600; }
.amount { font-size: 3rem; font-weight: 800; line-height: 1; }
.period { font-size: 0.875rem; opacity: 0.6; font-weight: 500; }

.plan-features { list-style: none; padding: 0; margin: 0 0 2.5rem; flex: 1; display: flex; flex-direction: column; gap: 1rem; }
.plan-feature { display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.875rem; color: #475569; }
.check-icon { flex-shrink: 0; margin-top: 0.125rem; }

.pricing-footer { margin-top: auto; }
.pricing-button { 
    display: block; 
    width: 100%; 
    padding: 1rem; 
    border-radius: 0.75rem; 
    text-align: center; 
    font-weight: 700; 
    text-decoration: none; 
    transition: all 0.3s ease;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}
.pricing-button:hover { filter: brightness(0.95); transform: scale(1.02); }
.pricing-button:active { transform: scale(0.98); }

@media (max-width: 768px) {
    .pricing-card--featured { transform: none; }
    .pricing-card--featured:hover { transform: translateY(-4px); }
}
</style>
