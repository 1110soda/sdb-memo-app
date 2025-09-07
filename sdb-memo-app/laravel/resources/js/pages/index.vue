<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import DocumentSvg from "../components/svgs/DocumentSvg.vue";
import PlusSvg from "../components/svgs/PlusSvg.vue";
import TrashSvg from "../components/svgs/TrashSvg.vue";
import TextareaForm from "../components/TextareaForm.vue";
import Button from "../components/Button.vue";

const memoTitle = ref('');
const memoContent = ref('');
const expandedMemos = ref<number[]>([]); //展開されているメモカードのIDを保持

const isContentEntered = computed(() => {
    return memoContent.value.length > 0;
});

const tempMemos = ref([
    { id: 1, title: 'タイトル', content: 'コンテンツ', date: '2025/9/6 13:39:41' },
    { id: 2, title: 'タイトルのないコンテンツ1行', content: 'タイトルのないコンテンツ1行', date: '2025/9/6 13:40:25' },
    { id: 3, title: 'タイトルのないコンテンツ4行以上', content: 'タイトルのないコンテンツ4行以上\nあああ\nいいい\n4行目以降のコンテンツ', date: '2025/9/6 13:42:11' },
    { id: 4, title: '1行が長いタイトルああああああああああああああああああああああああああああああ', content: 'Hello World aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', date: '2025/9/6 13:44:10' },
])

// メモカードのIDがexpandedMemosに存在すれば削除し、しないなら追加する
const toggleExpand = (id: number) => {
    const index = expandedMemos.value.indexOf(id);
    if (index === -1) {
        expandedMemos.value.push(id);
    } else {
        expandedMemos.value.splice(index, 1);
    }
}

const isExpanded = (id: number) => {
    return expandedMemos.value.includes(id);
}

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
    <div class="p-4 md:p-8 flex justify-center items-start min-h-screen">
        <div class="w-full max-w-lg flex flex-col space-y-8">
            <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 hover:shadow-secondary-400 transition-all duration-300">
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
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-2">
                    <DocumentSvg class="w-6 h-6 text-accent-800" />
                    <h2 class="text-xl font-medium text-secondary-900">
                        保存されたメモ
                    </h2>
                </div>
                <div class="bg-primary-200 text-sm text-secondary-700 rounded-full px-3 py-1">
                    {{ tempMemos.length }} 件
                </div>
            </div>
            <div class="grid gap-4">
                <p v-if="tempMemos.length === 0" class="text-secondary-900 text-center">
                    まだメモがありません。
                </p>
                <div v-for="memo in tempMemos" :key="memo.id" @click="toggleExpand(memo.id)" class="group relative bg-white min-w-0 p-4 rounded-lg shadow-lg cursor-pointer hover:scale-105 hover:shadow-secondary-400 transition-all duration-300">
                    <div class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                        <button @click.stop="" class="text-secondary-700 hover:text-secondary-900 hover:bg-secondary-100 rounded-full p-2 transition-colors">
                            <TrashSvg class="w-4 h-4" />
                        </button>
                    </div>
                    <h3 :class="{ 'line-clamp-1 break-words': !isExpanded(memo.id) }" class="font-semibold text-secondary-900 whitespace-pre-wrap break-words transition-all duration-300 mb-1">
                        {{ memo.title }}
                    </h3>
                    <p :class="{ 'line-clamp-3 break-words': !isExpanded(memo.id) }" class="text-sm text-secondary-700 whitespace-pre-wrap break-words transition-all duration-300">
                        {{ memo.content }}
                    </p>
                    <p class="text-sm font-medium text-secondary-700 mt-2">
                        {{ memo.date }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
