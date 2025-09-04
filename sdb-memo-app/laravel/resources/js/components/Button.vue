<script setup lang="ts">
    defineProps({
        disabled: {
            type: Boolean,
            default: true
        }
    });
</script>

<template>
    <button
        :disabled="disabled"
        class="flex items-center justify-center rounded w-full space-x-2 py-3 px-4 transition-colors"
        :class="disabled ? 'bg-gradient-to-r from-secondary-400 to-secondary-600 text-white cursor-not-allowed' : 'bg-gradient-to-r from-accent-600 to-primary-600 text-white'">
        <span><slot></slot></span>
    </button>
</template>

<style scoped>

</style>
