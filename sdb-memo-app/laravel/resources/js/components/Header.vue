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
    <header class="p-4 flex flex-col items-center border-b bg-theme-bg-card border-theme-border-subtle shadow-lg pb-6 transition-colors duration-300">
        <div class="w-full flex justify-end items-center mb-2 space-x-4">
            <button @click="themeStore.toggleTheme" class="p-2 bg-theme-bg-card rounded-full hover-dim transition-colors">
                <SunSvg v-if="themeStore.isDarkMode" class="w-5 h-5 text-theme-text-secondary" />
                <MoonSvg v-else class="w-5 h-5 text-theme-text-secondary" />
            </button>
            <nav>
                <ul class="flex space-x-4">
                    <template v-if="isLoggedIn">
                        <li><router-link to="/DeletedMemos" class="inline-block text-theme-text-secondary border-r border-theme-border-subtle pr-4 hover:text-theme-text-primary">
                            削除されたメモ
                        </router-link></li>
                        <li><a href="#" @click.prevent="handleLogout" class="inline-block text-theme-text-secondary hover:text-theme-text-primary">
                            ログアウト
                        </a></li>
                    </template>
                    <template v-else>
                        <li><router-link to="/createUser" class="inline-block text-theme-text-secondary border-r border-theme-border-subtle pr-4 hover:text-theme-text-primary">
                            新規登録
                        </router-link></li>
                        <li><router-link to="/login" class="inline-block text-theme-text-secondary hover:text-theme-text-primary">
                            ログイン
                        </router-link></li>
                    </template>
                </ul>
            </nav>
        </div>
        <router-link to="/" class="text-center no-underline">
            <div class="flex items-center space-x-2">
                <DocumentSvg class="w-8 h-8 text-theme-interactive-primary hover:text-theme-interactive-primary-hover" />
                <h1 class="text-3xl font-light bg-gradient-to-r from-theme-gradient-start to-theme-gradient-end hover-dim bg-clip-text text-transparent">
                    {{ title }}
                </h1>
            </div>
        </router-link>
    </header>
</template>

<style scoped>

</style>
