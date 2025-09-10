<script setup lang="ts">
import { ref } from 'vue';

const isHovered = ref(false);
</script>

<template>
    <div
        class="flex item-center overflow-hidden transition-all duration-500 ease-in-out relative"
        :class="{'w-4': !isHovered, 'w-12': isHovered}"
        @mouseover="isHovered = true"
        @mouseleave="isHovered = false">
        <div class="flex-shrink-0 w-4 h-4 transition-transform duration-500 ease-in-out">
            <slot name="icon"></slot>
        </div>
        <span
            class="left-5 text-sm whitespace-nowrap transition-opacity duration-500 ease-in-out absolute"
            :class="{'opacity-0': !isHovered, 'opacity-100': isHovered}">
            <slot name="text"></slot>
        </span>
    </div>
</template>

<style scoped>

</style>
