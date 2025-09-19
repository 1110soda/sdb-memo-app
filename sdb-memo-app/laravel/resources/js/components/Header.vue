<script setup lang="ts">
import { computed } from "vue";
import { useAuth } from '../composables/useAuth';
import { useThemeStore } from "../stores/useThemeStore.ts";
import { useRouter } from 'vue-router';
import DocumentSvg from './svgs/DocumentSvg.vue';
import SunSvg from './svgs/SunSvg.vue';
import MoonSvg from './svgs/MoonSvg.vue';

const { user, logout: performLogout } = useAuth();
const themeStore = useThemeStore();
const router = useRouter();

const isLoggedIn = computed(() => !!user.value);
const userName =  computed(() => user.value?.name || '');

const title = computed(() => {
    return isLoggedIn.value ? `${userName.value}'s memo` : `1110soda's memo app`;
});

const handleLogout = async() => {
    await performLogout();
    router.push('/');
};
</script>

<template>
    <header class="p-4 flex flex-col items-center border-b bg-white border-secondary-200 shadow-lg pb-6 transition-colors duration-300">
        <div class="w-full flex justify-end items-center mb-2 space-x-4">
            <button @click="themeStore.toggleTheme" class="p-2 rounded-full hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors">
                <SunSvg v-if="themeStore.isDarkMode" class="w-5 h-5 text-secondary-700 dark:text-secondary-200" />
                <SunSvg v-else class="w-5 h-5 text-secondary-700 dark:text-secondary-200" />
            </button>
            <nav>
                <ul class="flex space-x-4">
                    <template v-if="isLoggedIn">
                        <li><router-link to="/DeletedMemos" class="inline-block text-secondary-500 border-r border-secondary-200 pr-4 hover:text-secondary-900">
                            削除されたメモ
                        </router-link></li>
                        <li><a href="#" @click.prevent="handleLogout" class="inline-block text-secondary-500 hover:text-secondary-900">
                            ログアウト
                        </a></li>
                    </template>
                    <template v-else>
                        <li><router-link to="/createUser" class="inline-block text-secondary-500 border-r border-secondary-200 pr-4 hover:text-secondary-900">
                            新規登録
                        </router-link></li>
                        <li><router-link to="/login" class="inline-block text-secondary-500 hover:text-secondary-900">
                            ログイン
                        </router-link></li>
                    </template>
                </ul>
            </nav>
        </div>
        <router-link to="/" class="text-center no-underline">
            <div class="flex items-center space-x-2">
                <DocumentSvg class="w-8 h-8 text-accent-600 hover:text-accent-800" />
                <h1 class="text-3xl font-light bg-gradient-to-r from-accent-600 to-primary-600 hover:from-accent-800 hover:to-primary-800 bg-clip-text text-transparent">
                    {{ title }}
                </h1>
            </div>
        </router-link>
    </header>
</template>

<style scoped>

</style>
