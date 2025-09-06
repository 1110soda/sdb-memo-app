<script setup lang="ts">
    import { computed } from 'vue';

    const props = defineProps({
        modelValue: String,
    });

    const emits = defineEmits(['update:modelValue', 'saveMemo']);

    const inputClass = computed(() => {
        return props.modelValue
    });

    const handleInput = (e: Event) => {
        emits('update:modelValue', (e.target as HTMLInputElement).value);
    }

    const handleEnter = (e: Event) => {
        if (e.shiftKey) {
            emits('update:modelValue', (e.target as HTMLTextAreaElement).value + '\n');
        } else {
        //  日本語入力の変換中のEnter入力を保存のEnter入力と認識させない
            if (e.isComposing) {
                e.preventDefault(); //フォームの送信を防ぐ
            } else {
                e.preventDefault(); //送信前の改行を防ぐ
                emits('saveMemo');
            }

        }
    };

</script>

<template>
    <textarea
        :value="modelValue"
        @input="handleInput"
        @keydown.enter.prevent="handleEnter"
        class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-secondary-300 focus:border-accent-800"
        :class="inputClass"
        rows="6"
        placeholder="メモを入力してください…&#10;（Enterで保存、Shift+Enterで改行）"
    ></textarea>
</template>

<style scoped>

</style>
