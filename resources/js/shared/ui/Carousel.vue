<template>
  <div
    :class="cn('relative', props.class)"
    role="region"
    aria-roledescription="carousel"
    tabindex="0"
    @keydown="onKeyDown"
  >
    <slot :can-scroll-next="canScrollNext" :can-scroll-prev="canScrollPrev" :carousel-api="carouselApi" :orientation="orientation" :scroll-next="scrollNext" :scroll-prev="scrollPrev" />
  </div>
</template>

<script setup lang="ts">
import { type HTMLAttributes, type UnwrapRef, computed, provide, ref, onMounted } from 'vue';
import emblaCarouselVue from 'embla-carousel-vue';
import { type CarouselApi, type CarouselEmits, type CarouselProps, useCarousel } from './useCarousel';
import { cn } from '../../lib/utils';

const props = withDefaults(defineProps<CarouselProps & { class?: HTMLAttributes['class'] }>(), {
  orientation: 'horizontal',
});

const emits = defineEmits<CarouselEmits>();

const [emblaNode, emblaApi] = emblaCarouselVue({
  ...props.opts,
  axis: props.orientation === 'horizontal' ? 'x' : 'y',
}, props.plugins);

const carouselApi = ref<CarouselApi>();
const canScrollPrev = ref(false);
const canScrollNext = ref(false);

const onSelect = (api: CarouselApi) => {
  if (!api) return;
  canScrollPrev.value = api.canScrollPrev();
  canScrollNext.value = api.canScrollNext();
};

const onReInit = (api: CarouselApi) => {
  if (!api) return;
  onSelect(api);
};

onMounted(() => {
  if (!emblaApi.value) return;

  carouselApi.value = emblaApi.value;
  emblaApi.value.on('init', onSelect);
  emblaApi.value.on('reInit', onReInit);
  emblaApi.value.on('select', onSelect);

  emits('init-api', emblaApi.value);
});

function scrollPrev() {
  emblaApi.value?.scrollPrev();
}

function scrollNext() {
  emblaApi.value?.scrollNext();
}

function onKeyDown(event: KeyboardEvent) {
  if (event.key === 'ArrowLeft') {
    event.preventDefault();
    scrollPrev();
  } else if (event.key === 'ArrowRight') {
    event.preventDefault();
    scrollNext();
  }
}

provide('carousel', {
  carouselRef: emblaNode,
  carouselApi: carouselApi,
  canScrollPrev,
  canScrollNext,
  scrollPrev,
  scrollNext,
  orientation: computed(() => props.orientation),
});
</script>
