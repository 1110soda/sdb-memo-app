<template>
    <div>
        <Header :is-loggedIn="isLoggedIn" :user-name="userName" />
        <main class="flex-1 overflow-y-auto">
            <template v-if="isLoggedIn">
                <router-view />
            </template>
            <template v-else>
                <div class="text-center p-8">
                    <h2 class="text-xl text-secondary-900 mb-4">
                        ログインが必要です
                    </h2>
                    <p class="text-secondary-700">
                        メモ機能を利用するには、ログインしてください。
                    </p>
                </div>
            </template>
        </main>
    </div>
</template>
<script setup lang="ts">
import Header from './components/Header.vue';

const props = defineProps({
    isLoggedIn: {
        type: Boolean,
        required: true,
    },
    userName: {
        type: String,
        default: '',
    }
});
</script>
