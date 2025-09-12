<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import DocumentSvg from "../components/svgs/DocumentSvg.vue";
import PlusSvg from "../components/svgs/PlusSvg.vue";
import TrashSvg from "../components/svgs/TrashSvg.vue";
import EditSvg from "../components/svgs/EditSvg.vue";
import StatusSvg from "../components/svgs/StatusSvg.vue";
import TextareaForm from "../components/TextareaForm.vue";
import Button from "../components/Button.vue";
import Modal from "../components/Modal.vue";
import IconWithText from "../components/IconWithText.vue";
import TagIcon from "../components/TagIcon.vue";

interface Category {
    id: number;
    name: string;
    color_code: string;
}

interface Memo {
    id: number;
    title: string;
    content: string;
    categories: Category[];
    deadline_at: string | null;
    created_at: string;
    updated_at: string;
}

const memoTitle = ref('');
const memoContent = ref('');
const memos = ref<Memo[]>([]); //APIから取得したメモ一覧
const expandedMemos = ref<number[]>([]); //展開されているメモカードのIDを保持
const isFetchingAPI = ref(false);
const isSavingAPI = ref(false);

// メモ編集処理
const isEditModalOpen = ref(false);
const memoToEdit = ref<Memo | null>(null);
const isUpdatingAPI = ref(false);

// メモ削除処理
const isDeleteModalOpen = ref(false);
const memoToDelete = ref<Memo | null>(null);
const isDeletingAPI = ref(false);

// メモタグ用処理
const isCategoryModalOpen = ref(false);
const memoToCategorize = ref<Memo | null>(null);
const availableCategories = ref<Category[]>([]);
const selectedCategoryIds = ref<number[]>([]); //モーダルで選択中のカテゴリーID
const modalDeadlineAt = ref<string | null>(null); //モーダルで設定中の期日

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
        const end = start + memos.value.length - 1;
        if (start === end) {
            return `${start} / ${totalMemosCount.value} 件`;
        } else {
            return `${start} - ${end} / ${totalMemosCount.value} 件`;
        }
    } else {
        return `${totalMemosCount.value} 件`;
    }
});

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
});

const isContentEntered = computed(() => {
    return memoContent.value.length > 0;
});

const isEditContentEntered = computed(() => {
    return memoToEdit.value && memoToEdit.value.content && memoToEdit.value.content.length > 0;
});

// メモ取得API
const fetchMemos = async(page = 1) => {  //ページネーション有効化時は最初のページを表示
    if (isFetchingAPI.value) return;

    isFetchingAPI.value = true;

    try {
        let url = '';
        if (isPaginationEnabled.value) {
            url = `/api/memos/paginate?page=${page}`;
        } else {
            url = '/api/memos/all';
        }
        const response = await fetch(url);
        const result = await response.json();

        if (response.ok) {
            if (isPaginationEnabled.value) {
                memos.value = result.data;
                currentPage.value = result.current_page;
                lastPage.value = result.last_page;
                totalMemosCount.value = result.total;
            } else {
                memos.value = result;
                // 全件表示時はページネーション情報をクリア
                currentPage.value = 1;
                lastPage.value = 1;
                totalMemosCount.value = result.length;
            }
        } else {
            alert(`メモの取得に失敗しました: ${result.message || 'Unknown error'}`);
            memos.value = []; //エラー時にメモリストをクリア
            totalMemosCount.value = 0;
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの取得中にエラーが発生しました。');
        memos.value = [];
        totalMemosCount.value = 0;
    } finally {
        isFetchingAPI.value = false;
    }
};

const togglePagination = () => {
    isPaginationEnabled.value = !isPaginationEnabled.value;
    fetchMemos();
};

// メモカードのIDがexpandedMemosに存在すれば削除し、しないなら追加する
const toggleExpand = (id: number) => {
    const index = expandedMemos.value.indexOf(id);
    if (index === -1) {
        expandedMemos.value.push(id);
    } else {
        expandedMemos.value.splice(index, 1);
    }
};

const isExpanded = (id: number) => {
    return expandedMemos.value.includes(id);
};

// メモ保存API
const saveMemo = async() => {
    if (!isContentEntered.value || isSavingAPI.value) return;

    isSavingAPI.value = true;

    if (memoTitle.value.length === 0) {
        memoTitle.value = memoContent.value.split('\n')[0]; // タイトル欄に入力がない場合、メモ内容の最初の行を見出しとして表示する。
    }

    const memoData = {
        title: memoTitle.value,
        content: memoContent.value,
        category_ids: [],
        deadline_at: null,
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
            await fetchMemos(currentPage.value); //新しいメモを保存後、リストを再取得し、表示画面を更新
        } else {
            alert(`保存に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの保存中にエラーが発生しました。');
    } finally {
        isSavingAPI.value = false;
    }
};

// メモ更新API
const updateMemo = async() => {
    if (!isEditContentEntered.value || isUpdatingAPI.value || memoToEdit.value === null) return;

    isUpdatingAPI.value = true;

    if (memoToEdit.value.title.length === 0) {
        memoToEdit.value.title = memoToEdit.value.content.split('\n')[0];
    }

    const memoData = {
        title: memoToEdit.value.title,
        content: memoToEdit.value.content,
        category_ids: memoToEdit.value.categories ? memoToEdit.value.categories.map(cat => cat.id) : [],
        deadline_at: memoToEdit.value.deadline_at,
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

        const response = await fetch(`/api/memos/${memoToEdit.value.id}`, {
            method: 'PUT',
            headers: headers,
            body: JSON.stringify(memoData),
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            await fetchMemos(currentPage.value); //新しいメモを保存後、リストを再取得し、表示画面を更新
        } else {
            alert(`更新に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの更新中にエラーが発生しました。');
    } finally {
        isUpdatingAPI.value = false;
        cancelEdit();
    }
};

const confirmEdit = (memo: Memo) => {
    memoToEdit.value = JSON.parse(JSON.stringify(memo));
    isEditModalOpen.value = true;
};

const cancelEdit = () => {
    memoToEdit.value = null;
    isEditModalOpen.value = false;
};

// メモ削除API
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

        const response = await fetch(`/api/memos/${memoToDelete.value.id}`, {
            method: 'DELETE',
            headers: headers,
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            await fetchMemos(currentPage.value);
        } else {
            alert(`削除に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('メモの削除中にエラーが発生しました。')
    } finally {
        isDeletingAPI.value = false;
        cancelDelete();
    }
};

const confirmDelete = (memo: Memo) => {
    memoToDelete.value = memo;
    isDeleteModalOpen.value = true;
};

const cancelDelete = () => {
    memoToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const fetchCategories = async() => {
    try {
        const response = await fetch('/api/categories');
        const result = await response.json();

        if (response.ok) {
            availableCategories.value = result.data;
        } else {
            console.error('カテゴリーの取得に失敗しました:', result.message || 'Unknown error');
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
    }
}

const handleCategorySave = async() => {
    if (!memoToCategorize.value || isUpdatingAPI.value) return;

    isUpdatingAPI.value = true;

    const memoData = {
        title: memoToCategorize.value.title,
        content: memoToCategorize.value.content,
        category_ids: selectedCategoryIds.value,
        deadline_at: modalDeadlineAt.value,
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

        const response = await fetch(`/api/memos/${memoToCategorize.value.id}`, {
            method: 'PUT',
            headers: headers,
            body: JSON.stringify(memoData),
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            await fetchMemos(currentPage.value); //新しいメモを保存後、リストを再取得し、表示画面を更新
        } else {
            alert(`カテゴリー設定の更新に失敗しました: ${result.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('APIリクエストエラー:', error);
        alert('カテゴリー設定の更新中にエラーが発生しました。');
    } finally {
        isUpdatingAPI.value = false;
        cancelCategorize();
    }
};

const confirmCategorize = (memo: Memo) => {
    memoToCategorize.value = JSON.parse(JSON.stringify(memo));
    selectedCategoryIds.value = memo.categories ? memo.categories.map(cat => cat.id) : [];
    modalDeadlineAt.value = memo.deadline_at;
    isCategoryModalOpen.value = true;
};

const cancelCategorize = () => {
    memoToCategorize.value = null;
    selectedCategoryIds.value = [];
    modalDeadlineAt.value = null;
    isCategoryModalOpen.value = false;
};

const getCardBorderStyle = (memo: Memo) => {
    if (memo.categories && memo.categories.length > 0) {
        const colors = memo.categories.map(cat => cat.color_code);

        if (colors.length === 1) {
            return {
                borderWidth: '1px',
                borderStyle: 'solid',
                borderColor: colors[0],
            };
        } else {
            const gradientColors = colors.join(', ');
            return {
                borderWidth: `${colors.length}px`,
                borderStyle: 'solid',
                borderImage: `linear-gradient(to right, ${gradientColors}) 1`,
            };
        }
    }
    return { borderWidth: '0px' };
};

onMounted(() => {
    fetchMemos();
    fetchCategories();
});
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
                メモを取得中...
            </div>
            <div v-else class="grid gap-4">
                <p v-if="memos.length === 0" class="text-secondary-900 text-center">
                    まだメモがありません。
                </p>
                <div
                    v-for="memo in memos"
                    :key="memo.id"
                    @click="toggleExpand(memo.id)"
                    class="group relative bg-white min-w-0 p-4 rounded-lg shadow-lg cursor-pointer hover:scale-105 hover:shadow-secondary-400 transition-all duration-300"
                    :style="getCardBorderStyle(memo)"
                >
                    <div v-if="memo.categories.length > 0 || memo.deadline_at" class="absolute top-2 right-2 flex flex-col items-end space-y-1 z-10">
                        <TagIcon
                            v-for="category in memo.categories"
                            :key="category.id"
                            :tag-name="category.name"
                            :tag-color="category.color_code"
                            :deadline-date="category.name === '期限付き' ? memo.deadline_at : null"
                        />
                    </div>
                    <div class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
                        <button @click.stop="confirmCategorize(memo)" class="text-secondary-700 hover:text-secondary-900 hover:bg-secondary-100 rounded-full p-2 transition-colors">
                            <IconWithText>
                                <template #icon>
                                    <StatusSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    タグ
                                </template>
                            </IconWithText>
                        </button>
                        <button @click.stop="confirmEdit(memo)" class="text-secondary-700 hover:text-secondary-900 hover:bg-secondary-100 rounded-full p-2 transition-colors">
                            <IconWithText>
                                <template #icon>
                                    <EditSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    編集
                                </template>
                            </IconWithText>
                        </button>
                        <button @click.stop="confirmDelete(memo)" class="text-secondary-700 hover:text-secondary-900 hover:bg-secondary-100 rounded-full p-2 transition-colors">
                            <IconWithText>
                                <template #icon>
                                    <TrashSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    削除
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
                    @click="fetchMemos()"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &lt;&lt;
                </button>
                <button
                    @click="fetchMemos(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &lt;
                </button>
                <template v-for="page in displayedPages" :key="page">
                    <button
                        v-if="page !== '...'"
                        @click="fetchMemos(Number(page))"
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
                    @click="fetchMemos(currentPage + 1)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &gt;
                </button>
                <button
                    @click="fetchMemos(lastPage)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 rounded-full text-secondary-700 bg-black bg-opacity-0 transition-all duration-300"
                    :class="[currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-secondary-900 hover:bg-opacity-5']">
                    &gt;&gt;
                </button>
            </div>
        </div>
    </div>
<!--ポップアップ用-->
    <Modal :is-open="isCategoryModalOpen">
        <template #header>
            メモのカテゴリーを設定: {{ memoToCategorize?.title }}
        </template>
        <template #body>
            <div class="mb-4">
                <label class="block text-secondary-700 text-sm font-medium mb-2">カテゴリー:</label>
                <div class="grid grid-cols-2 gap-2">
                    <div v-for="category in availableCategories" :key="category.id" class="flex items-center">
                        <input
                            type="checkbox"
                            :id="`category-${category.id}`"
                            :value="category.id"
                            v-model="selectedCategoryIds"
                            class="mr-2 rounded text-accent-600 focus:ring-accent-500"
                            :style="{ 'accent-color': category.color_code }"
                        />
                        <label :for="`category-${category.id}`" class="text-secondary-700 flex items-center">
                            <span class="inline-block w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: category.color_code }"></span>
                            {{ category.name }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="deadline_at" class="block text-secondary-700 text-sm font-medium mb-2">期日:</label>
                <input
                    type="date"
                    id="deadline_at"
                    v-model="modalDeadlineAt"
                    class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-secondary-300 focus:border-accent-800"
                />
            </div>
        </template>
        <template #footer>
            <button @click="cancelCategorize" class="py-2 px-4 rounded bg-secondary-300 text-secondary-700 hover:bg-secondary-400 transition-colors">
                キャンセル
            </button>
            <button @click="handleCategorySave" :disabled="isUpdatingAPI" class="py-2 px-4 rounded bg-primary-600 text-white hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isUpdatingAPI">保存中...</span>
                <span v-else>保存</span>
            </button>
        </template>
    </Modal>

    <Modal :is-open="isEditModalOpen">
        <template #header>
            メモを編集: {{ memoToEdit?.title }}
        </template>
        <template #body>
            <form @submit.prevent="updateMemo">
                <div class="mb-4">
                    <label for="editMemoTitle" class="block text-secondary-700 text-sm font-medium mb-2">
                        タイトル
                    </label>
                    <input
                        id="editMemoTitle"
                        type="text"
                        v-model="memoToEdit.title"
                        class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-secondary-300 focus:border-accent-800"
                        placeholder="タイトルを入力してください…（空でも可）"
                    />
                </div>
                <div class="mb-4">
                    <label for="editMemoContent" class="block text-secondary-700 text-sm font-medium mb-2">
                        内容
                    </label>
                    <TextareaForm v-model="memoToEdit.content" @saveMemo="updateMemo" />
                </div>
            </form>
        </template>
        <template #footer>
            <button @click="cancelEdit" class="py-2 px-4 rounded bg-secondary-300 text-secondary-700 hover:bg-secondary-400 transition-colors">
                キャンセル
            </button>
            <button @click="updateMemo" :disabled="!isEditContentEntered || isUpdatingAPI" class="py-2 px-4 rounded bg-primary-600 text-white hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isUpdatingAPI">更新中...</span>
                <span v-else>更新</span>
            </button>
        </template>
    </Modal>
    <Modal :is-open="isDeleteModalOpen">
        <template #header>
            削除の確認: {{ memoToDelete?.title }}
        </template>
        <template #body>
            本当にこのメモを削除しますか？
        </template>
        <template #footer>
            <button @click="cancelDelete" class="py-2 px-4 rounded bg-secondary-300 text-secondary-700 hover:bg-secondary-400 transition-colors">
                キャンセル
            </button>
            <button @click="deleteMemo" :disabled="isDeletingAPI" class="py-2 px-4 rounded bg-red-600 text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="isDeletingAPI">削除中...</span>
                <span v-else>削除</span>
            </button>
        </template>
    </Modal>
</template>
