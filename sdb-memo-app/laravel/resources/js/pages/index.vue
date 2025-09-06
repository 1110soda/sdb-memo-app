<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import PlusSvg from "../components/svgs/PlusSvg.vue";
import TextareaForm from "../components/TextareaForm.vue";
import Button from "../components/Button.vue";

const memoTitle = ref('');
const memoContent = ref('');

const isContentEntered = computed(() => {
    return memoContent.value.length > 0;
});

const saveMemo = async() => {
    if (!isContentEntered.value) return;

    if (memoTitle.value.length == 0) {
        memoTitle.value = memoContent.value.split('\n')[0]; // タイトル欄に入力がない場合、メモ内容の最初の行を見出しとして表示する。
    }

    const memoData = {
        title: memoTitle.value,
        content: memoContent.value,
    };

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'); //CSRFトークンを取得

        // HTTPリクエストヘッダーを定義
        const headers: HeadersInit = {
            'Content-Type': 'application/json', //送信するデータの形式
            'X-Requested-With': 'XMLHttpRequest', //LaravelがAjaxリクエストであることを認識するために必要
        }
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken; //セキュリティトークン
        }

        const response = await fetch('/api/memos', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(memoData),
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            memoTitle.value = '';
            memoContent.value = '';
        } else {
            alert(`保存に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの保存中にエラーが発生しました。');
    }
};
</script>

<template>
    <div class="p-4 md:p-8 flex justify-center items-start">
        <div class="w-full max-w-lg bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-2 mb-6">
                <PlusSvg class="w-6 h-6 text-accent-800" />
                <h2 class="text-xl font-medium text-secondary-900">
                    新しいメモ
                </h2>
            </div>

            <form @submit.prevent="saveMemo">
                <div class="mb-4">
                    <label for="memoTitle" class="block text-secondary-700 text-sm font-medium mb-2">
                        タイトル
                    </label>
                    <input
                        id="memoTitle"
                        type="text"
                        v-model="memoTitle"
                        class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-secondary-300 focus:border-accent-800"
                        placeholder="タイトルを入力してください…（空でも可）"
                    />
                </div>
                <div class="mb-4">
                    <label for="memoContent" class="block text-secondary-700 text-sm font-medium mb-2">
                        内容
                    </label>
                    <TextareaForm v-model="memoContent" @saveMemo="saveMemo" />
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="!isContentEntered">
                        + メモを保存
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
