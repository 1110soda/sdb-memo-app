<script setup lang="ts">

const props = defineProps<{
    tagName: string;
    tagColor: string;
    deadlineDate?: string | null,
}>();
</script>

<template>
    <div
        class="flex items-center space-x-1 px-2 py-0.5 rounded-full text-xs"
        :style="{ backgroundColor: props.tagColor }"
    >
        <div class="w-2 h-2 rounded-full border border-white"></div>
        <span class="text-white font-medium">
            <template v-if="props.tagName === '期限付き' && props.deadlineDate">
                {{ props.deadlineDate }}
            </template>
            <template v-else>
                {{ props.tagName }}
            </template>
        </span>
    </div>
</template>

<style scoped>

</style>
