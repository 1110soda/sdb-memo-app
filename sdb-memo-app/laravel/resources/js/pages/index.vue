<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "../lib/axios";
import DocumentSvg from "../components/svgs/DocumentSvg.vue";
import PlusSvg from "../components/svgs/PlusSvg.vue";
import TrashSvg from "../components/svgs/TrashSvg.vue";
import EditSvg from "../components/svgs/EditSvg.vue";
import StatusSvg from "../components/svgs/StatusSvg.vue";
import FilterSvg from "../components/svgs/FilterSvg.vue";
import SortSvg from "../components/svgs/SortSvg.vue";
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
const isUpdatingCategoryAPI = ref(false);
const availableCategories = ref<Category[]>([]);
const selectedCategoryIds = ref<number[]>([]); //モーダルで選択中のカテゴリーID
const modalDeadlineAt = ref<string | null>(null); //モーダルで設定中の期日

// メモのタグに期日が選択されていない場合、設定されていた期日をクリアする
watch(selectedCategoryIds, (newIds) => {
    const deadlineCategory = availableCategories.value.find(cat => cat.name === '期限付き');

    if (deadlineCategory && !newIds.includes(deadlineCategory.id)) {
        modalDeadlineAt.value = null;
    }
});

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

// メモフィルター/並び替え用処理
const isFilterModalOpen = ref(false);
const isSortDropdownOpen = ref(false);
const activeCategoryIds = ref<number[]>([]);
const currentSort = ref('updated_at_desc');
const sortOptions = ref([
    { value: 'created_at_desc', label: '作成が新しい順' },
    { value: 'created_at_asc', label: '作成が古い順' },
    { value: 'updated_at_desc', label: '更新が新しい順' },
    { value: 'updated_at_asc', label: '更新が古い順' },
    { value: 'deadline_at_desc', label: '期日が遠い順' },
    { value: 'deadline_at_asc', label: '期日が近い順' },
])

// 他
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
        const baseUrl = isPaginationEnabled.value ? '/memos/paginate' : '/memos/all';
        const params = new URLSearchParams();
        if (isPaginationEnabled.value) {
            params.append('page', page.toString());
        }
        if (activeCategoryIds.value.length > 0) {
            activeCategoryIds.value.forEach(id => {
                params.append('categoryIds[]', id.toString());
            });
        }
        params.append('sort', currentSort.value);

        const response = await axios.get(`${baseUrl}?${params.toString()}`);
        const result = response.data;

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
        const response = await axios.post('/memos', memoData);
        alert(response.data.message);
        memoTitle.value = '';
        memoContent.value = '';
        await fetchMemos(currentPage.value); //新しいメモを保存後、リストを再取得し、表示画面を更新
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            alert(Object.values(error.response.data.errors).flat().join('\n'));
        } else {
            alert('メモの保存中にエラーが発生しました。');
        }
        console.error('APIリクエストエラー:', error);
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
        const response = await axios.put(`/memos/${memoToEdit.value.id}`, memoData);
        alert(response.data.message);
        await fetchMemos(currentPage.value); //新しいメモを保存後、リストを再取得し、表示画面を更新
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            alert(Object.values(error.response.data.errors).flat().join('\n'));
        } else {
            alert('メモの更新中にエラーが発生しました。');
        }
        console.error('APIリクエストエラー:', error);
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
        const response = await axios.delete(`/memos/${memoToDelete.value.id}`);
        alert(response.data.message);
        await fetchMemos(currentPage.value);
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
        const response = await axios.get('/categories');
        availableCategories.value = response.data.data;
    } catch (error) {
        console.error('APIリクエストエラー:', error);
    }
}

const handleCategorySave = async() => {
    if (!memoToCategorize.value || isUpdatingCategoryAPI.value) return;

    isUpdatingCategoryAPI.value = true;

    const memoData = {
        title: memoToCategorize.value.title,
        content: memoToCategorize.value.content,
        category_ids: selectedCategoryIds.value,
        deadline_at: modalDeadlineAt.value,
    };

    if (memoData.deadline_at) {
        memoData.deadline_at = memoData.deadline_at.replace(/-/g, '/'); //'date' typeとして受け取ったので、Controllerへ送る前にYYYY-MM-DD...からYYYY/MM/DD...へ変換
    }

    try {
        const response = await axios.put(`/memos/${memoToCategorize.value.id}`, memoData);
        alert(response.data.message);
        await fetchMemos(currentPage.value); //新しいメモを保存後、リストを再取得し、表示画面を更新
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            alert(Object.values(error.response.data.errors).flat().join('\n'));
        } else {
            alert('カテゴリー設定の更新中にエラーが発生しました。');
        }
        console.error('APIリクエストエラー:', error);
    } finally {
        isUpdatingCategoryAPI.value = false;
        cancelCategorize();
    }
};

const confirmCategorize = (memo: Memo) => {
    memoToCategorize.value = JSON.parse(JSON.stringify(memo));
    selectedCategoryIds.value = memo.categories ? memo.categories.map(cat => cat.id) : [];
    modalDeadlineAt.value = memo.deadline_at ? memo.deadline_at.replace(/\//g, '-') : null;
    isCategoryModalOpen.value = true;
};

const cancelCategorize = () => {
    memoToCategorize.value = null;
    selectedCategoryIds.value = [];
    modalDeadlineAt.value = null;
    isCategoryModalOpen.value = false;
};

const openFilterModal = () => isFilterModalOpen.value = true;

const applyFilters = () => {
    isFilterModalOpen.value = false;
    fetchMemos();
};

const resetFilters = () => {
    activeCategoryIds.value = [];
    currentSort.value = 'updated_at_desc';
    applyFilters();
};

const currentSortLabel = computed(() => {
    return sortOptions.value.find(option => option.value === currentSort.value)?.label || '';
});

const toggleSortDropdown = () => {
    isSortDropdownOpen.value = !isSortDropdownOpen.value;
};

const selectSortOption = (value: string) => {
    currentSort.value = value;
    isSortDropdownOpen.value = false;
};

watch(currentSort, (newSortValue) => {
    // 期日でソートするとき、期限付きのメモのみを表示
    if (newSortValue.startsWith('deadline_at')) {
        const deadlineCategory = availableCategories.value.find(cat => cat.name === '期限付き');
        if (deadlineCategory && !activeCategoryIds.value.includes(deadlineCategory.id)) {
            activeCategoryIds.value.push(deadlineCategory.id);
        }
    }
    fetchMemos();
});

const getCardBorderStyle = (memo: Memo) => {
    if (memo.categories && memo.categories.length > 0) {
        const colors = memo.categories.map(cat => cat.color_code);

        if (colors.length === 1) {
            return {
                border: `2px solid ${colors[0]}`,
            };
        } else {
            const gradientColors = colors.join(', ');
            return {
                border: `2px solid transparent`,
                background: `linear-gradient(var(--color-bg-card), var(--color-bg-card)) padding-box, linear-gradient(to right, ${gradientColors}) border-box`,
            };
        }
    }
    return {};
};

onMounted(() => {
    fetchMemos();
    fetchCategories();
});
</script>

<template>
    <div class="p-4 md:p-8 flex justify-center items-start min-h-screen">
        <div class="w-full max-w-xl flex flex-col space-y-8">
            <div class="bg-theme-bg-card p-6 rounded-lg shadow-lg hover:scale-105 transition-all duration-300">
                <div class="flex items-center space-x-2 mb-6">
                    <PlusSvg class="w-6 h-6 text-theme-accent" />
                    <h2 class="text-xl font-medium text-theme-text-primary">
                        新しいメモ
                    </h2>
                </div>

                <form @submit.prevent="saveMemo">
                    <div class="mb-4">
                        <label for="memoTitle" class="block text-theme-text-secondary text-sm font-medium mb-2">
                            タイトル
                        </label>
                        <input
                            id="memoTitle"
                            type="text"
                            v-model="memoTitle"
                            class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-theme-border focus:border-theme-accent"
                            placeholder="タイトルを入力してください…（空でも可）"
                        />
                    </div>
                    <div class="mb-4">
                        <label for="memoContent" class="block text-theme-text-secondary text-sm font-medium mb-2">
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
                    <DocumentSvg class="w-6 h-6 text-theme-accent" />
                    <h2 class="text-xl font-medium text-theme-text-primary">
                        保存されたメモ
                    </h2>
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="togglePagination" class="text-sm text-theme-text-secondary bg-theme-bg-accent rounded-full px-3 py-1 mr-2 hover:text-theme-text-primary hover-dim transition-all duration-300">
                        <span v-if="isPaginationEnabled">全て表示</span>
                        <span v-else>ページ形式で表示</span>
                    </button>
                    <button @click="openFilterModal" class="text-theme-text-secondary hover:text-theme-text-primary bg-theme-bg-accent rounded-full p-2 transition-colors hover-dim">
                        <IconWithText>
                            <template #icon>
                                <FilterSvg class="w-4 h-4" />
                            </template>
                            <template #text>
                                フィルター
                            </template>
                        </IconWithText>
                    </button>
                    <div class="relative">
                        <button @click="toggleSortDropdown" class="text-theme-text-secondary hover:text-theme-text-primary bg-theme-bg-accent rounded-full p-2 transition-colors hover-dim">
                            <IconWithText :is-expanded="isSortDropdownOpen">
                                <template #icon>
                                    <SortSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    {{ currentSortLabel }}
                                </template>
                            </IconWithText>
                        </button>
                        <div v-if="isSortDropdownOpen" class="absolute top-full mt-2 w-48 bg-theme-bg-card rounded-md shadow-lg z-20">
                            <ul>
                                <li
                                    v-for="option in sortOptions"
                                    :key="option.value"
                                    @click="selectSortOption(option.value)"
                                    class="px-4 py-2 text-sm text-theme-text-secondary hover:bg-theme-bg-main cursor-pointer transition-colors"
                                >
                                    {{ option.label }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="bg-theme-bg-accent-subtle text-sm text-theme-text-secondary rounded-full px-3 py-1">
                        {{ displayMemoCount }}
                    </div>
                </div>
            </div>
            <div v-if="isFetchingAPI" class="text-theme-text-primary text-center">
                メモを取得中...
            </div>
            <div v-else class="grid gap-4">
                <p v-if="!memos.length" class="text-theme-text-primary text-center">
                    該当するメモがありません。
                </p>
                <div
                    v-for="memo in memos"
                    :key="memo.id"
                    @click="toggleExpand(memo.id)"
                    class="group relative bg-theme-bg-card min-w-0 p-4 rounded-lg shadow-lg cursor-pointer hover:scale-105 transition-all duration-300"
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
                        <button @click.stop="confirmCategorize(memo)" class="text-theme-text-secondary hover:text-theme-text-primary bg-theme-bg-card rounded-full p-2 transition-colors hover-dim">
                            <IconWithText>
                                <template #icon>
                                    <StatusSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    タグ
                                </template>
                            </IconWithText>
                        </button>
                        <button @click.stop="confirmEdit(memo)" class="text-theme-text-secondary hover:text-theme-text-primary bg-theme-bg-card rounded-full p-2 transition-colors hover-dim">
                            <IconWithText>
                                <template #icon>
                                    <EditSvg class="w-4 h-4" />
                                </template>
                                <template #text>
                                    編集
                                </template>
                            </IconWithText>
                        </button>
                        <button @click.stop="confirmDelete(memo)" class="text-theme-text-secondary hover:text-theme-text-primary bg-theme-bg-card rounded-full p-2 transition-colors hover-dim">
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
                    <h3 :class="{ 'line-clamp-1 break-words': !isExpanded(memo.id) }" class="font-semibold text-theme-text-primary whitespace-pre-wrap break-words transition-all duration-300 mb-1">
                        {{ memo.title }}
                    </h3>
                    <p :class="{ 'line-clamp-3 break-words': !isExpanded(memo.id) }" class="text-sm text-theme-text-secondary whitespace-pre-wrap break-words transition-all duration-300">
                        {{ memo.content }}
                    </p>
                    <p class="text-sm font-medium text-theme-text-secondary mt-2">
                        作成: {{ memo.created_at }}、更新: {{ memo.updated_at }}
                    </p>
                </div>
            </div>
            <div v-if="isPaginationEnabled && lastPage > 1" class="mt-8 flex justify-center items-center space-x-2">
                <button
                    @click="fetchMemos()"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded-full text-theme-text-secondary bg-theme-bg-accent transition-all duration-300"
                    :class="[currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-theme-text-primary hover-dim']">
                    &lt;&lt;
                </button>
                <button
                    @click="fetchMemos(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded-full text-theme-text-secondary bg-theme-bg-accent transition-all duration-300"
                    :class="[currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:text-theme-text-primary hover-dim']">
                    &lt;
                </button>
                <template v-for="page in displayedPages" :key="page">
                    <button
                        v-if="page !== '...'"
                        @click="fetchMemos(Number(page))"
                        :class="[
                            'px-3 py-1 rounded-full',
                            page === currentPage ? 'text-theme-text-primary bg-theme-bg-accent-subtle hover:cursor-not-allowed' : 'text-theme-text-secondary bg-theme-bg-accent hover:text-theme-text-primary transition-all duration-300 hover-dim'
                        ]">
                        {{ page }}
                    </button>
                    <span v-else class="px-3 py-1 rounded-full text-theme-text-secondary">
                        {{ page }}
                    </span>
                </template>
                <button
                    @click="fetchMemos(currentPage + 1)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 rounded-full text-theme-text-secondary bg-theme-bg-accent transition-all duration-300"
                    :class="[currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-theme-text-primary hover-dim']">
                    &gt;
                </button>
                <button
                    @click="fetchMemos(lastPage)"
                    :disabled="currentPage === lastPage"
                    class="px-3 py-1 rounded-full text-theme-text-secondary bg-theme-bg-accent transition-all duration-300"
                    :class="[currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : 'hover:text-theme-text-primary hover-dim']">
                    &gt;&gt;
                </button>
            </div>
        </div>
    </div>
<!--ポップアップ用-->
    <Modal :is-open="isFilterModalOpen">
        <template #header>
            フィルター
        </template>
        <template #body>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-theme-text-secondary">
                    カテゴリーで絞り込み:
                </label>
                <div v-for="category in availableCategories" :key="category.id" class="flex items-center">
                    <input
                        type="checkbox"
                        :id="`filter-category-${category.id}`"
                        :value="category.id"
                        v-model="activeCategoryIds"
                        class="h-4 w-4 rounded border-theme-border text-theme-accent focus:theme-interactive-focus-ring"
                    />
                    <label :for="`filter-category-${category.id}`" class="ml-2 text-sm text-theme-text-primary">
                        {{ category.name }}
                    </label>
                </div>
            </div>
        </template>
        <template #footer>
            <button @click="resetFilters" class="py-2 px-4 rounded bg-theme-interactive-secondary text-theme-text-secondary hover-dim transition-colors">
                リセット
            </button>
            <button @click="applyFilters" class="py-2 px-4 rounded bg-theme-interactive-primary text-theme-text-interactive-primary hover-dim transition-colors">
                適用
            </button>
        </template>
    </Modal>

    <Modal :is-open="isCategoryModalOpen">
        <template #header>
            メモのカテゴリーを設定: {{ memoToCategorize?.title }}
        </template>
        <template #body>
            <div class="mb-4">
                <label class="block text-theme-text-secondary text-sm font-medium mb-2">カテゴリー:</label>
                <div class="grid grid-cols-2 gap-2">
                    <div v-for="category in availableCategories" :key="category.id" class="flex items-center">
                        <input
                            type="checkbox"
                            :id="`category-${category.id}`"
                            :value="category.id"
                            v-model="selectedCategoryIds"
                            class="mr-2 rounded text-theme-accent focus:theme-interactive-focus-ring"
                            :style="{ 'accent-color': category.color_code }"
                        />
                        <label :for="`category-${category.id}`" class="text-theme-text-secondary flex items-center">
                            <span class="inline-block w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: category.color_code }"></span>
                            {{ category.name }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="deadline_at" class="block text-theme-text-secondary text-sm font-medium mb-2">期日:</label>
                <input
                    type="date"
                    id="deadline_at"
                    v-model="modalDeadlineAt"
                    class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-theme-border focus:border-theme-accent"
                />
            </div>
        </template>
        <template #footer>
            <button @click="cancelCategorize" class="py-2 px-4 rounded bg-theme-interactive-secondary text-theme-text-secondary hover-dim transition-colors">
                キャンセル
            </button>
            <button @click="handleCategorySave" :disabled="isUpdatingCategoryAPI" class="py-2 px-4 rounded bg-theme-interactive-primary text-theme-text-interactive-primary hover-dim transition-colors btn-disabled">
                <span v-if="isUpdatingCategoryAPI">保存中...</span>
                <span v-else>保存</span>
            </button>
        </template>
    </Modal>

    <Modal :is-open="isEditModalOpen" v-if="memoToEdit">
        <template #header>
            メモを編集: {{ memoToEdit.title }}
        </template>
        <template #body>
            <form @submit.prevent="updateMemo">
                <div class="mb-4">
                    <label for="editMemoTitle" class="block text-theme-text-secondary text-sm font-medium mb-2">
                        タイトル
                    </label>
                    <input
                        id="editMemoTitle"
                        type="text"
                        v-model="memoToEdit.title"
                        class="appearance-none border rounded w-full py-2 px-3 text-secondary-700 leading-tight focus:outline-none focus:shadow-outline transition-colors border-theme-border focus:border-theme-accent"
                        placeholder="タイトルを入力してください…（空でも可）"
                    />
                </div>
                <div class="mb-4">
                    <label for="editMemoContent" class="block text-theme-text-secondary text-sm font-medium mb-2">
                        内容
                    </label>
                    <TextareaForm v-model="memoToEdit.content" @saveMemo="updateMemo" />
                </div>
            </form>
        </template>
        <template #footer>
            <button @click="cancelEdit" class="py-2 px-4 rounded bg-theme-interactive-secondary text-theme-text-secondary hover-dim transition-colors">
                キャンセル
            </button>
            <button @click="updateMemo" :disabled="!isEditContentEntered || isUpdatingAPI" class="py-2 px-4 rounded bg-theme-interactive-primary text-theme-text-interactive-primary hover-dim transition-colors btn-disabled">
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
            <button @click="cancelDelete" class="py-2 px-4 rounded bg-theme-interactive-secondary text-theme-text-secondary hover-dim transition-colors">
                キャンセル
            </button>
            <button @click="deleteMemo" :disabled="isDeletingAPI" class="py-2 px-4 rounded bg-theme-interactive-danger text-theme-text-interactive-primary hover-dim transition-colors btn-disabled">
                <span v-if="isDeletingAPI">削除中...</span>
                <span v-else>削除</span>
            </button>
        </template>
    </Modal>
</template>
