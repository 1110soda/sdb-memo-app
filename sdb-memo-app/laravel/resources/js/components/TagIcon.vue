<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    tagName: string;
    tagColor: string;
    deadlineDate?: string | null,
}>();
</script>

<template>
    <div
        class="flex items-center space-x-1 px-2 py-0.5 rounded-full text-xs"
        :style="{ backgroundColor: tagColor }"
    >
        <div class="w-2 h-2 rounded-full border border-white"></div>
        <span class="text-white font-medium">
            <template v-if="tagName === '期限付き' && deadlineDate">
                {{ deadlineDate }}
            </template>
            <template v-else>
                {{ tagName }}
            </template>
        </span>
    </div>
</template>

<style scoped>

</style>
