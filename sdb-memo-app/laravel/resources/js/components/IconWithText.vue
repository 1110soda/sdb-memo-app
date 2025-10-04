<script setup lang="ts">
import { ref, computed, nextTick, watch } from 'vue';

const props = defineProps({
    isExpanded: {
        type: Boolean,
        default: false,
    }
});

const isHovered = ref(false);
const textSpan = ref<HTMLElement | null>(null);
const containerWidth = ref('24px'); //w-4 + px-1
let leaveTimer: ReturnType<typeof setTimeout> | null = null;

const shouldBeExpanded = computed(() => isHovered.value || props.isExpanded);

const calculateWidth = async () => {
    if (shouldBeExpanded.value) {
        await nextTick();
        if (textSpan.value) {
            //親コンテナのパディング（px-1） + アイコン幅 + 余白 + テキスト幅
            const padding = 8; //px-1 (0.25rem * 2)
            const iconWidth = 16; //w-4
            const gap = 4; //gap-x-1
            const textWidth = textSpan.value.scrollWidth;
            containerWidth.value = `${padding + iconWidth + gap + textWidth}px`;
        }
    } else {
        containerWidth.value = '24px';
    }
};

const handleMouseOver = async() => {
    if (leaveTimer) {
        clearTimeout(leaveTimer);
        leaveTimer = null;
    }
    isHovered.value = true;
};

const handleMouseLeave = () => {
    leaveTimer = setTimeout(() => {
        isHovered.value = false;
    }, 500);
};

watch(shouldBeExpanded, () => {
    calculateWidth();
});
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
            :class="{'opacity-0': !shouldBeExpanded, 'opacity-100': shouldBeExpanded}">
            <slot name="text"></slot>
        </span>
    </div>
</template>

<style scoped>

</style>
