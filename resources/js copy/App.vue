<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useApi } from "./composables/useApi.js";
import { Loading } from "@element-plus/icons-vue";
import { ElMessage, ElLoading } from "element-plus";

import CardEstate from "./components/CardEstate.vue";
import SearchCard from "./components/SearchCard.vue";

const PAGE_SIZE = 20;

// Loading states
const loadingInit = ref(false);
const loading = ref(false);
const loadingMore = ref(false);

// Intro state
const totalInDb = ref(0);

// Results
const results = ref([]);
const totalFound = ref(0);
const hasMore = ref(false);
const searched = ref(false);

// Sorting
const sortDir = ref(""); // "" | "asc" | "desc"

// Request ordering (ignore stale)
let reqId = 0;

// Snapshot of last "Search" click
const lastSearchKey = ref("");
const lastPayloadBase = ref(null);

// Current filters key (changes when user tweaks filters)
const currentFiltersKey = ref("");

// delay Api for testing============= !!!!!!!!!!!!
const delayApi = ref(1000);

// ----- UI Events from SearchCard -----
function handleInitLoading(v) {
    loadingInit.value = Boolean(v);
}

function handleFiltersKey(key) {
    currentFiltersKey.value = String(key || "");
}

function resetResultsState() {
    results.value = [];
    totalFound.value = 0;
    hasMore.value = false;
    searched.value = false;

    lastSearchKey.value = "";
    lastPayloadBase.value = null;

    sortDir.value = "";
}

function clearResultsOnly() {
    results.value = [];
    totalFound.value = 0;
    hasMore.value = false;
    searched.value = false;
}

function handleDataLoaded(payload) {
    totalInDb.value = Number(payload?.totalInDb ?? 0);
    resetResultsState();
}

function handleReset() {
    resetResultsState();
}

function canLoadOrRefetch() {
    return (
        searched.value &&
        !loading.value &&
        !loadingMore.value &&
        lastSearchKey.value &&
        currentFiltersKey.value === lastSearchKey.value &&
        lastPayloadBase.value
    );
}

async function handleSearch(payloadBase, searchKey) {
    loading.value = true;

    const myId = ++reqId;

    // Save snapshot for pagination + sorting
    lastSearchKey.value = String(searchKey || "");
    lastPayloadBase.value = payloadBase || {};

    // UI state

    clearResultsOnly();

    try {
        const data = await useApi({
            url: "/api/search",
            data: {
                ...(lastPayloadBase.value || {}),
                offset: 0,
                limit: PAGE_SIZE,

                // sorting
                sort: sortDir.value ? "price" : undefined,
                dir: sortDir.value || undefined,
            },
        });

        if (myId !== reqId) return;

        searched.value = true;
        results.value = Array.isArray(data?.items) ? data.items : [];
        totalFound.value = Number(data?.total ?? results.value.length);

        hasMore.value = Boolean(
            data?.hasMore ?? results.value.length < totalFound.value,
        );
    } catch (e) {
        ElMessage.error({
            message: e.message,
            showClose: true,
            duration: 10000,
        });
    } finally {
        if (myId === reqId) loading.value = false;
    }
}

function applySort(dir) {
    if (sortDir.value === dir) return;

    sortDir.value = dir;

    // re-fetch only after the first search and only if the filters have not changed

    if (!canLoadOrRefetch()) return;

    handleSearch(lastPayloadBase.value, lastSearchKey.value);
}

async function loadMore() {
    if (loadingMore.value || !hasMore.value || !searched.value) return;

    // If user changed filters but didn't click Search — don't load more
    if (!lastSearchKey.value || currentFiltersKey.value !== lastSearchKey.value)
        return;

    // No base payload — nothing to load
    if (!lastPayloadBase.value) return;

    loadingMore.value = true;
    const myId = ++reqId;

    try {
        const offset = results.value.length;

        const data = await useApi({
            url: "/api/search",
            data: {
                ...(lastPayloadBase.value || {}),
                offset,
                limit: PAGE_SIZE,

                // sorting
                sort: sortDir.value ? "price" : undefined,
                dir: sortDir.value || undefined,
            },
        });

        if (myId !== reqId) return;

        const items = Array.isArray(data?.items) ? data.items : [];
        results.value.push(...items);

        if (typeof data?.total !== "undefined") {
            totalFound.value = Number(data.total ?? totalFound.value);
        }

        hasMore.value = Boolean(
            data?.hasMore ?? results.value.length < totalFound.value,
        );
    } catch (e) {
        ElMessage.error({
            message: e.message,
            showClose: true,
            duration: 10000,
        });
    } finally {
        if (myId === reqId) loadingMore.value = false;
    }
}

// Scroll handler (simple throttle)
let scrollTicking = false;
function handleScroll() {
    if (scrollTicking) return;
    scrollTicking = true;

    requestAnimationFrame(() => {
        scrollTicking = false;

        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const docHeight = document.documentElement.scrollHeight;

        if (scrollTop + windowHeight >= docHeight - 200) {
            loadMore();
        }
    });
}

onMounted(() => {
    window.addEventListener("scroll", handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});

const drawer = ref(false);
</script>

<template>
    <el-config-provider>
        <el-container direction="vertical">
            <!-- Filters -->
            <el-card class="sticky">
                <el-row :gutter="12" class="row-gap">
                    <el-col :sm="3">
                        <SearchCard
                            v-model:delayApi="delayApi"
                            :loading="loading"
                            :loading-more="loadingMore"
                            @init-loading="handleInitLoading"
                            @filters-key="handleFiltersKey"
                            @data-loaded="handleDataLoaded"
                            @search="handleSearch"
                            @reset="handleReset"
                        ></SearchCard>
                    </el-col>
                    <el-col :md="4">
                        <el-form-item
                            :label="
                                'Found:' +
                                totalFound +
                                '(showing ' +
                                results.length +
                                ')'
                            "
                        ></el-form-item>
                    </el-col>
                    <el-col :md="6" class="sort-bar">
                        <!-- Sorting (only after results are shown) -->

                        <span class="sort-label">Price</span>

                        <button
                            type="button"
                            class="sort-btn"
                            :class="{ active: sortDir === 'desc' }"
                            @click="applySort('desc')"
                            title="Price: high to low"
                        >
                            ↓
                        </button>

                        <button
                            type="button"
                            class="sort-btn"
                            :class="{ active: sortDir === 'asc' }"
                            @click="applySort('asc')"
                            title="Price: low to high"
                        >
                            ↑
                        </button>
                    </el-col>
                    <el-col :sm="3">
                        <el-form-item
                            :label="'Total in DB:' + totalInDb"
                        ></el-form-item>
                    </el-col>
                    <el-col :sm="5">
                        <el-form-item label="Simulated API latency">
                            <el-input-number
                                v-model="delayApi"
                                :min="0"
                                :step="100"
                                controls-position="right"
                            /> </el-form-item
                    ></el-col>
                </el-row>
            </el-card>

            <h1>SPA Search</h1>

            <!-- Searching (after click Search) -->
            <el-text v-if="loading && results.length === 0" tag="div">
                <el-icon class="is-loading"><Loading /></el-icon>
                Searching...
            </el-text>

            <!-- Empty -->
            <el-empty
                v-else-if="searched && totalFound === 0"
                description="No results. Try widening the filters."
            />

            <!-- List -->

            <el-row :gutter="12">
                <el-col
                    v-for="p in results"
                    :key="p.id"
                    :xs="24"
                    :sm="12"
                    :md="12"
                    :lg="12"
                    :xl="8"
                >
                    <CardEstate :property="p" />
                </el-col>
            </el-row>

            <el-divider />

            <el-text v-if="loadingMore" tag="div">
                <el-icon class="is-loading"><Loading /></el-icon>
                Loading...
            </el-text>

            <el-text
                v-else-if="searched && results.length && !hasMore"
                tag="div"
                type="info"
            >
                No more results
            </el-text>
        </el-container>

        <el-backtop :right="10" :bottom="10" />
    </el-config-provider>
</template>

<style scoped>
.row-gap {
    row-gap: 12px;
}

@media (min-width: 769px) {
    .sticky {
        position: sticky;
        top: 0;
        z-index: 10;
    }
}

/* cards grid */
.results-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
}

/* sorting */
.sort-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}

.sort-label {
    font-weight: 600;
}

.sort-btn {
    border: 1px solid var(--el-border-color);
    background: var(--el-fill-color-blank);
    border-radius: 6px;
    padding: 2px 10px;
    cursor: pointer;
    line-height: 1.4;
}

.sort-btn.active {
    border-color: var(--el-color-primary);
    color: var(--el-color-primary);
    font-weight: 700;
}

.sticky {
    position: sticky;
    background: #fff;
}
</style>
