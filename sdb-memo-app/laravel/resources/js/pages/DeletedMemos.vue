<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import DocumentSvg from "../components/svgs/DocumentSvg.vue";
import TrashSvg from "../components/svgs/TrashSvg.vue";
import Modal from "../components/Modal.vue";
import RestoreSvg from "../components/svgs/RestoreSvg.vue";
import IconWithText from "../components/IconWithText.vue";

const deletedMemos = ref([]);
const expandedMemos = ref<number[]>([]); //展開されているメモカードのIDを保持
const isFetchingAPI = ref(false);
const isRestoringAPI = ref(false);
const isDeletingAPI = ref(false);
const isModalOpen = ref(false);
const memoToDelete = ref<any | null>(null);

// ページネーション処理
const currentPage = ref(1);
const lastPage = ref(1);
const isPaginationEnabled = ref(true);
const totalMemosCount = ref(0);
const itemsPerPage = 5; //後に1ページに表示する機能を追加する可能性がある; 現在はメインページに含むと操作が多くなるので設定画面に配置するか固定値でもいいと考えている

const displayMemoCount = computed(() => {
    if (isPaginationEnabled.value) {
        if (totalMemosCount.value === 0) {
            return '0 件';
        }
        const start = (currentPage.value - 1) * itemsPerPage + 1;
        const end = start + deletedMemos.value.length - 1;
        if (start === end) {
            return `${start} / ${totalMemosCount.value} 件`;
        } else {
            return `${start} - ${end} / ${totalMemosCount.value} 件`;
        }
    } else {
        return `${totalMemosCount.value} 件`;
    }
})

// 現在のページから左右2ページずつのみ表示、残りは"…"で表記
const displayedPages = computed(() => {
    const pages = [];
    if (lastPage.value <= 5) {
        for (let i = 1; i <= lastPage.value; i++) {
            pages.push(i);
        }
        return pages;
    }
    const startPage = Math.max(1, currentPage.value - 2);
    const endPage = Math.min(lastPage.value, currentPage.value + 2);
    if (startPage > 1) {
        pages.push('...');
    }
    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }
    if (endPage < lastPage.value) {
        pages.push('...');
    }
    return pages;
})

// 削除されたメモ取得API
const fetchDeletedMemos = async (page = 1) => {  //ページネーション有効化時は最初のページを表示
    if (isFetchingAPI.value) return;

    isFetchingAPI.value = true;

    try {
        let url = '';
        if (isPaginationEnabled.value) {
            url = `/api/memos/deleted/paginate?page=${page}`;
        } else {
            url = '/api/memos/deleted/all';
        }
        const response = await fetch(url);
        const result = await response.json();

        if (response.ok) {
            if (isPaginationEnabled.value) {
                deletedMemos.value = result.data;
                currentPage.value = result.current_page;
                lastPage.value = result.last_page;
                totalMemosCount.value = result.total;
            } else {
                deletedMemos.value = result;
                // 全件表示時はページネーション情報をクリア
                currentPage.value = 1;
                lastPage.value = 1;
                totalMemosCount.value = result.length;
            }
        } else {
            alert(`メモの取得に失敗しました: ${result.message || 'Unknown error'}`);
            deletedMemos.value = []; //エラー時にメモリストをクリア
            totalMemosCount.value = 0;
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの取得中にエラーが発生しました。');
        deletedMemos.value = [];
        totalMemosCount.value = 0;
    } finally {
        isFetchingAPI.value = false;
    }
}

const togglePagination = () => {
    isPaginationEnabled.value = !isPaginationEnabled.value;
    fetchDeletedMemos();
}

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

// メモ復元API
const restoreMemo = async(id: number) => {
    if (isRestoringAPI.value) return;

    isRestoringAPI.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const headers: HeadersInit = {
            'X-Requested-With': 'XMLHttpRequest',
        }
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }

        const response = await fetch(`/api/memos/deleted/restore/${id}`, {
            method: 'PATCH',
            headers: headers,
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            await fetchDeletedMemos(currentPage.value);
        } else {
            alert(`復元に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの復元中にエラーが発生しました。');
    } finally {
        isRestoringAPI.value = false;
    }
};

// メモ完全削除API
const deleteMemo = async() => {
    if (isDeletingAPI.value || memoToDelete.value === null) return;

    isDeletingAPI.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const headers: HeadersInit = {
            'X-Requested-With': 'XMLHttpRequest',
        }
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch(`/api/memos/deleted/${memoToDelete.value.id}`, {
            method: 'DELETE',
            headers: headers,
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            await fetchDeletedMemos(currentPage.value);
        } else {
            alert(`削除に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの削除中にエラーが発生しました。')
    } finally {
        isDeletingAPI.value = false;
        isModalOpen.value = false;
        memoToDelete.value = null;
    }
}

const confirmDelete = (memo: any) => {
    memoToDelete.value = memo;
    isModalOpen.value = true;
}

const cancelDelete = () => {
    memoToDelete.value = null;
    isModalOpen.value = false;
}

onMounted(() => {
    fetchDeletedMemos();
});
</script>

<template>
    <div class="p-4 md:p-8 flex justify-center items-start min-h-screen">
        <div class="w-full max-w-lg flex flex-col space-y-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-2">
                    <DocumentSvg class="w-6 h-6 text-accent-800" />
                    <h2 class="text-xl font-medium text-secondary-900">
                        削除されたメモ
                    </h2>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="togglePagination" class="text-sm text-secondary-700 bg-black bg-opacity-0 rounded-full px-3 py-1 mr-2 hover:text-secondary-900 hover:bg-opacity-5 transition-all duration-300">
                        <span v-if="isPaginationEnabled">全て表示</span>
                        <span v-else>ページ形式で表示</span>
                    </button>
                    <div class="bg-primary-200 text-sm text-secondary-700 rounded-full px-3 py-1">
                        {{ displayMemoCount }}
                    </div>
                </div>
            </div>
            <div v-if="isFetchingAPI" class="text-secondary-900 text-center">
                削除済みのメモを取得中...
            </div>
            <div v-else class="grid gap-4">
                <p v-if="deletedMemos.length === 0" class="text-secondary-900 text-center">
                    まだ削除済みのメモがありません。
                </p>
                <div v-for="memo in deletedMemos" :key="memo.id" @click="toggleExpand(memo.id)" class="group relative bg-white min-w-0 p-4 rounded-lg shadow-lg cursor-pointer hover:scale-105 hover:shadow-secondary-400 transition-all duration-300">
                    <div class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                        <button @click.stop="restoreMemo(memo.id)" class="text-secondary-700 hover:text-secondary-900 hover:bg-secondary-100 rounded-full p-2 transition-colors">
                            <IconWithText>
                                <template #icon>
                                    <RestoreSvg class="w-full h-full" />
                                </template>
                                <template #text>
                                    <span>復元</span>
                                </template>
                            </IconWithText>
                        </button>
                        <button @click.stop="confirmDelete(memo)" class="text-red-600 hover:text-red-700 hover:bg-red-100 rounded-full p-2 transition-colors">
                            <IconWithText>
                                <template #icon>
                                    <TrashSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    <span>
                                        削除
                                    </span>
                                </template>
                            </IconWithText>
                        </button>
                    </div>
                    <h3 :class="{ 'line-clamp-1 break-words': !isExpanded(memo.id) }" class="font-semibold text-secondary-900 whitespace-pre-wrap break-words transition-all duration-300 mb-1">
                        {{ memo.title }}
                    </h3>
                    <p :class="{ 'line-clamp-3 break-words': !isExpanded(memo.id) }" class="text-sm text-secondary-700 whitespace-pre-wrap break-words transition-all duration-300">
                        {{ memo.content }}
                    </p>
                    <p class="text-sm font-medium text-secondary-700 mt-2">
                        作成: {{ memo.created_at }}、更新: {{ memo.updated_at }}
                    </p>
                </div>
            </div>
            <div v-if="isPaginationEnabled && lastPage > 1" class="mt-8 flex justify-center items-center space-x-2">
                <button
                    @click="fetchDeletedMemos()"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &lt;&lt;
                </button>
                <button
                    @click="fetchDeletedMemos(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &lt;
                </button>
                <template v-for="page in displayedPages" :key="page">
                    <button
                        v-if="page !== '...'"
                        @click="fetchDeletedMemos(Number(page))"
                        :class="[
                            'px-3 py-1 rounded-full',
                            page === currentPage ? 'text-secondary-900 bg-primary-200 font-semibold hover:cursor-not-allowed' : 'text-secondary-700 bg-black bg-opacity-0 hover:text-secondary-900 hover:bg-opacity-5 transition-all duration-300'
                        ]">
                        {{ page}}
                    </button>
                    <span v-else class="px-3 py-1 rounded-full text-secondary-700">
                        {{ page }}
                    </span>
                </template>
                <button
                    @click="fetchDeletedMemos(currentPage + 1)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &gt;
                </button>
                <button
                    @click="fetchDeletedMemos(lastPage)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &gt;&gt;
                </button>
            </div>
        </div>
    </div>
    <!--ポップアップ用-->
    <Modal :is-open="isModalOpen">
        <template #header>
            完全削除の確認: {{ memoToDelete?.title }}
        </template>
        <template #body>
            完全削除したメモは復元できません。本当にこのメモを削除しますか？
        </template>
        <template #footer>
            <button @click="cancelDelete" class="py-2 px-4 rounded bg-secondary-300 text-secondary-700 hover:bg-secondary-400 transition-colors">
                キャンセル
            </button>
            <button @click="deleteMemo" :disabled="isDeletingAPI" class="py-2 px-4 rounded bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isDeletingAPI">削除中...</span>
                <span v-else>完全削除</span>
            </button>
        </template>
    </Modal>
</template>
