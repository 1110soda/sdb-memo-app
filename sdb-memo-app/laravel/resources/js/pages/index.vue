<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import PlusSvg from "../components/svgs/PlusSvg.vue";
import TextareaForm from "../components/TextareaForm.vue";
import Button from "../components/Button.vue";

const memoTitle = ref('');
const memoContent = ref('');
const userId = ref<string | null>(null);

onMounted(() => {
    if (document.body.hasAttribute('data-user-id')) {
        userId.value = document.body.getAttribute('data-user-id');
    }
});

const isContentEntered = computed(() => {
    return memoContent.value.length > 0;
});

const saveMemo = () => {
    if (!isContentEntered.value) return;

    let titleForAlert = '';
    let contentForAlert = '';

    // タイトル欄に入力がない場合、メモ内容の最初の行を見出しとして表示する。
    if (memoTitle.value.length > 0) {
        titleForAlert = `[${memoTitle.value}]`;
        contentForAlert = memoContent.value;
    } else {
        const contentLines = memoContent.value.split('\n');
        titleForAlert = `[${contentLines[0]}]`;
        contentForAlert = memoContent.value;
    }

    alert(
        `［保存されたメモ］\nタイトル: ${titleForAlert}\n内容: \n${contentForAlert}\n`
    );
    // 後にサーバーへ以下の情報を送り保存する
    console.log({
        user_id: userId.value,
        title: memoTitle.value.length > 0 ? memoTitle.value : memoContent.value.split('\n')[0],
        content: memoContent.value,
    });

    memoTitle.value = '';
    memoContent.value = '';
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
