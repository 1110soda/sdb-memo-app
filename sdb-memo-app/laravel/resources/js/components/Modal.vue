<script setup lang="ts">
defineProps({
    isOpen: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-secondary-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm">
            <h3 class="text-lg font-semibold mb-4 text-secondary-900">
                <slot name="header"></slot>
            </h3>
            <p class="text-secondary-700 mb-6">
                <slot name="body"></slot>
            </p>
            <div class="flex justify-end space-x-4">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
