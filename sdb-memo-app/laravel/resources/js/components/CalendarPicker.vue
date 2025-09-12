<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    modelValue: string | null;
    label?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const formattedDate = computed(() => {
    if (!props.modelValue) {
        return '';
    } else {
        return props.modelValue.replace(/\//g, '-'); //YYYY/MM/DD -> YYYY-MM-DD
    }
});

const handleChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.value) {
        emit('update:modelValue', target.value.replace(/\//g, '-'));
    } else {
        emit('update:modelValue', null);
    }
}
</script>

<template>
    <div class="calendar-picker">
        <label v-if="label" class="block text-secondary-700 text-sm font-medium mb-2">{{ label }}</label>
        <input
            type="date"
            :value="formattedDate"
            @change="handleChange"
            class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-secondary-300 focus:border-accent-800"
        />
    </div>
</template>

<style scoped>

</style>
