import { type UnwrapRef, inject } from 'vue';
import type emblaCarouselVue from 'embla-carousel-vue';
import type { EmblaCarouselType, EmblaOptionsType, EmblaPluginType } from 'embla-carousel';

export type CarouselApi = EmblaCarouselType;

export interface CarouselProps {
    opts?: EmblaOptionsType;
    plugins?: EmblaPluginType[];
    orientation?: 'horizontal' | 'vertical';
}

export interface CarouselEmits {
    (e: 'init-api', payload: CarouselApi): void;
}

export interface CarouselContext {
    carouselRef: ReturnType<typeof emblaCarouselVue>[0];
    carouselApi: UnwrapRef<CarouselApi>;
    scrollPrev: () => void;
    scrollNext: () => void;
    canScrollPrev: boolean;
    canScrollNext: boolean;
    orientation: 'horizontal' | 'vertical';
}

export function useCarousel() {
    const carouselState = inject<CarouselContext>('carousel');

    if (!carouselState) {
        throw new Error('useCarousel must be used within a <Carousel />');
    }

    return carouselState;
}
