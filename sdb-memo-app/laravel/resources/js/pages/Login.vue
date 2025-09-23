<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../lib/axios';
import Button from '../components/Button.vue';

const router = useRouter();
const email = ref('');
const password = ref('');
const isLoading = ref(false);

const isFormEntered = computed(() => email.value.length > 0 && password.value.length > 0);

const handleLogin = async() => {
    if (!isFormEntered.value || isLoading.value) return;

    isLoading.value = true;

    try {
        await axios.get('/sanctum/csrf-cookie', { baseURL: '/' });
        await axios.post('/login', {
            email: email.value,
            password: password.value,
        });

        window.location.href = '/';
    } catch (error: any) {
        if (error.response && error.response.status === 422) { //バリデーションエラー
            alert(error.response.data.message);
        } else {
            alert('ログインに失敗しました。再度お試しください。');
        }
        console.log('Login failed:', error);
    } finally {
        isLoading.value = false;
    }
}

</script>

<template>
    <div class="p-4 md:p-8 flex justify-center items-start min-h-screen bg-theme-bg-accent">
        <div class="w-full max-w-lg flex flex-col space-y-8">
            <div class="bg-theme-bg-card p-6 rounded-lg shadow-lg hover:scale-105 transition-all duration-300">
                <h1 class="text-2xl font-bold text-center text-theme-text-primary mb-6">
                    ログイン
                </h1>
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-theme-text-secondary">
                            メールアドレス
                        </label>
                        <input
                            type="email"
                            id="email"
                            v-model="email"
                            required
                            autocomplete="email"
                            class="appearance-none border rounded w-full py-2 px-3 mt-1 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline border-secondary-300 focus:border-accent-800 transition-colors"
                        />
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-theme-text-secondary">
                            パスワード
                        </label>
                        <input
                            type="password"
                            id="password"
                            v-model="password"
                            required
                            autocomplete="current-password"
                            class="appearance-none border rounded w-full py-2 px-3 mt-1 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline border-secondary-300 focus:border-accent-800 transition-colors"
                        />
                    </div>
                    <div>
                        <Button type="submit" :disabled="!isFormEntered || isLoading">
                            <span v-if="isLoading">ログイン中...</span>
                            <span v-else>ログイン</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
