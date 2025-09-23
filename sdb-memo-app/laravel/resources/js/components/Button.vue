<script setup lang="ts">
    defineProps({
        disabled: {
            type: Boolean,
            default: false
        }
    });
</script>

<template>
    <button
        :disabled="disabled"
        class="flex items-center justify-center rounded w-full space-x-2 py-3 px-4 transition-colors text-theme-text-interactive-primary bg-gradient-to-r from-theme-gradient-start to-theme-gradient-end btn-disabled"
    >
        <span><slot></slot></span>
    </button>
</template>

<style scoped>

</style>
