<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from '../lib/axios';
import Button from '../components/Button.vue';

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false);

const isFormEntered = computed(() => name.value.length > 0 && email.value.length > 0 && password.value.length > 0 && passwordConfirmation.value.length > 0);

const handleCreateUser = async() => {
    if (!isFormEntered.value || isLoading.value) return;

    if (password.value !== passwordConfirmation.value) {
        alert('パスワードと確認用パスワードが一致しません。');
        return;
    }

    isLoading.value = true;

    try {
        await axios.get('/sanctum/csrf-cookie', { baseURL: '/' });
        await axios.post('/createUser', {
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });

        window.location.href = '/';
    } catch (error: any) {
        if (error.response && error.response.status === 422) { //バリデーションエラー
            alert(error.response.data.message);
        } else {
            alert('ユーザー登録に失敗しました。再度お試しください。');
        }
        console.log('User Creation failed:', error);
    } finally {
        isLoading.value = false;
    }
}

</script>

<template>
    <div class="p-4 md:p-8 flex justify-center items-start min-h-screen bg-primary-100">
        <div class="w-full max-w-lg flex flex-col space-y-8">
            <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 hover:shadow-secondary-400 transition-all duration-300">
                <h1 class="text-2xl font-bold text-center text-secondary-900 mb-6">
                    新規ユーザー登録
                </h1>
                <form @submit.prevent="handleCreateUser" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-secondary-700">
                            ユーザー名
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="name"
                            required
                            autocomplete="name"
                            class="appearance-none border rounded w-full py-2 px-3 mt-1 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline border-secondary-300 focus:border-accent-800 transition-colors"
                        />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-secondary-700">
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
                        <label for="password" class="block text-sm font-medium text-secondary-700">
                            パスワード
                        </label>
                        <input
                            type="password"
                            id="password"
                            v-model="password"
                            required
                            autocomplete="new-password"
                            class="appearance-none border rounded w-full py-2 px-3 mt-1 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline border-secondary-300 focus:border-accent-800 transition-colors"
                        />
                    </div>
                    <div>
                        <label for="passwordConfirmation" class="block text-sm font-medium text-secondary-700">
                            パスワード（確認用）
                        </label>
                        <input
                            type="password"
                            id="passwordConfirmation"
                            v-model="passwordConfirmation"
                            required
                            autocomplete="new-password"
                            class="appearance-none border rounded w-full py-2 px-3 mt-1 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline border-secondary-300 focus:border-accent-800 transition-colors"
                        />
                    </div>
                    <div>
                        <Button type="submit" :disabled="!isFormEntered || isLoading">
                            <span v-if="isLoading">登録中...</span>
                            <span v-else>登録</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
