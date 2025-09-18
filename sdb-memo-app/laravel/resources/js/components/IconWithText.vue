<script setup lang="ts">
import { ref, nextTick } from 'vue';

const isHovered = ref(false);
const textSpan = ref<HTMLElement | null>(null);
const containerWidth = ref('20px'); //w-4 + px-1 - gap-x-1?
let leaveTimer: ReturnType<typeof setTimeout> | null = null;

const handleMouseOver = async() => {
    if (leaveTimer) {
        clearTimeout(leaveTimer);
        leaveTimer = null;
    }

    isHovered.value = true;
    await nextTick();
    if (textSpan.value) {
        //親コンテナのパディング（px-1） + アイコン幅 + 余白 + テキスト幅
        const padding = 8; //px-1 (0.25rem * 2)
        const iconWidth = 16; //w-4
        const gap = 4; //gap-x-1
        const textWidth = textSpan.value.scrollWidth;
        containerWidth.value = `${padding + iconWidth + gap + textWidth}px`;
    }
};

const handleMouseLeave = () => {
    leaveTimer = setTimeout(() => {
        isHovered.value = false;
        containerWidth.value = '20px';
    }, 500); //500ms = 0.5s
}
</script>

<template>
    <div
        class="flex items-center px-1 gap-x-1 overflow-hidden transition-all duration-300 relative"
        :style="{ width: containerWidth }"
        @mouseover="handleMouseOver"
        @mouseleave="handleMouseLeave">
        <div class="flex-shrink-0 w-4 h-4">
            <slot name="icon"></slot>
        </div>
        <span
            ref="textSpan"
            class="text-sm whitespace-nowrap transition-opacity duration-500"
            :class="{'opacity-0': !isHovered, 'opacity-100': isHovered}">
            <slot name="text"></slot>
        </span>
    </div>
</template>

<style scoped>

</style>
