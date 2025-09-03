<script setup lang="ts">
import { computed } from "vue";
import DocumentSvg from './svgs/DocumentSvg.vue';

const props = defineProps({
    isLoggedIn: Boolean,
    userName: String,
});

const title = computed(() => {
    return props.isLoggedIn ? `${props.userName}'s memo` : `1110soda's memo app`;
});
</script>

<template>
    <header class="p-4 flex flex-col items-center border-b border-secondary-200">
        <div class="w-full flex justify-end mb-2">
            <nav>
                <ul class="flex space-x-4">
                    <template v-if="isLoggedIn">
                        <li><a href="/history" class="text-secondary-500 border-r border-secondary-200 pr-4 hover:text-secondary-900 transition-colors">
                            履歴
                        </a></li>
                        <li><a href="/logout" class="font-bold text-secondary-500 hover:text-secondary-900 transition-colors">
                            ログアウト
                        </a></li>
                    </template>
                    <template v-else>
                        <li><a href="/createUser" class="text-secondary-500 border-r border-secondary-200 pr-4 hover:text-secondary-900 transition-colors">
                            新規登録
                        </a></li>
                        <li><a href="/login" class="font-bold text-secondary-500 hover:text-secondary-900 transition-colors">
                            ログイン
                        </a></li>
                    </template>
                </ul>
            </nav>
        </div>
        <a href="/" class="text-center no-underline">
            <div class="flex items-center space-x-2">
                <DocumentSvg class="w-8 h-8 text-accent-800" />
                <h1 class="text-3xl font-thin text-accent-800">
                    {{ title }}
                </h1>
            </div>
        </a>
    </header>
</template>

<style scoped>

</style>
