<template>
    <div>
        <Header :is-loggedIn="isLoggedIn" :user-name="userName" />
        <main class="p-4">
            <router-view />
        </main>
    </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Header from './components/Header.vue';

const isLoggedIn = ref(false);
const userName = ref('');

onMounted(() => {
//  ユーザーのログイン状態とユーザー名をバックエンドから取得する
    const auth = document.querySelector('body[data-auth]')?.getAttribute('data-auth');
    if (auth) {
        isLoggedIn.value = true;
        userName.value = '山田太郎'; //ダミーのユーザー名
    }
})
</script>
