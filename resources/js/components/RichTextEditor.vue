<template>
    <div class="rich-text-editor">
        <div ref="editor" class="editor-container" />
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
let quill = null;

onMounted(() => {
    quill = new Quill(editor.value, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image', 'video'],
            ],
        },
    });

    // Set initial content
    if (props.modelValue) {
        quill.root.innerHTML = props.modelValue;
    }

    // Listen for text changes
    quill.on('text-change', () => {
        emit('update:modelValue', quill.root.innerHTML);
    });
});

watch(() => props.modelValue, (newValue) => {
    if (quill && quill.root.innerHTML !== newValue) {
        quill.root.innerHTML = newValue || '';
    }
});

onBeforeUnmount(() => {
    if (quill) {
        quill = null;
    }
});
</script>

<style scoped>
.editor-container {
    min-height: 300px;
}
</style>

