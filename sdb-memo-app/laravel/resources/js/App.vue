<template>
    <div>
        <Header />
        <main class="flex-1 overflow-y-auto">
            <template v-if="user || isPublicPage">
                <router-view />
            </template>
            <template v-else>
                <div class="p-4 md:p-8 flex justify-center items-start">
                    <div class="w-full max-w-lg text-center p-8 bg-white rounded-lg shadow">
                        <h2 class="text-xl text-secondary-900 mb-4">
                            ログインが必要です
                        </h2>
                        <p class="text-secondary-700">
                            この機能を利用するには、ログインしてください。
                        </p>
                    </div>
                </div>
            </template>
        </main>
    </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Header from './components/Header.vue';
import { useAuth } from './composables/useAuth';

const { user } = useAuth();
const route = useRoute();

const isPublicPage = computed(() => {
    const publicPaths = ['/login']; //, '/createUser'
    return publicPaths.includes(route.path);
});
</script>
