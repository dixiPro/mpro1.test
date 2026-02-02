<script setup>
import { onMounted, onUnmounted } from "vue";
import { storeToRefs } from "pinia";
import { useSearchStore } from "./stores/useSearchStore.js";
import { Loading } from "@element-plus/icons-vue";

import CardEstate from "./components/CardEstate.vue";
import SearchCard from "./components/SearchCard.vue";

const store = useSearchStore();

const {
    loading,
    loadingMore,
    results,
    totalFound,
    hasMore,
    searched,
    sortDir,
    totalInDb,
    delayApi,
    isLoadingMore,
    noMoreScrollData,
} = storeToRefs(store);

// Scroll handler (simple throttle)
let scrollTicking = false;

async function handleScroll() {
    if (scrollTicking) return;
    scrollTicking = true;

    requestAnimationFrame(() => {
        scrollTicking = false;

        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const docHeight = document.documentElement.scrollHeight;

        if (scrollTop + windowHeight >= docHeight - 200) {
            store.loadMore();
        }
    });
}

onMounted(() => {
    window.addEventListener("scroll", handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>

<template>

    <el-config-provider>
        <el-container direction="vertical">
            <!-- Filters -->

            <el-card class="sticky">
                <el-row :gutter="12" class="row-gap">
                    <el-col :xs="10" :md="3">
                        <SearchCard />
                    </el-col>

                    <el-col :xs="14" :md="4">
                        <el-form-item :label="'Found:' +
                            totalFound +
                            ' (showing ' +
                            results.length +
                            ')'
                            "></el-form-item>
                    </el-col>

                    <el-col :xs="10" :md="3" class="sort-bar">
                        <span class="sort-label">Price</span>

                        <button type="button" class="sort-btn" :class="{ active: sortDir === 'desc' }"
                            @click="store.applySort('desc')" title="Price: high to low">
                            ↓
                        </button>

                        <button type="button" class="sort-btn" :class="{ active: sortDir === 'asc' }"
                            @click="store.applySort('asc')" title="Price: low to high">
                            ↑
                        </button>
                    </el-col>

                    <el-col :xs="14" :md="3">
                        <el-form-item :label="'Total in DB:' + totalInDb"></el-form-item>
                    </el-col>

                    <el-col :xs="14" :md="6">
                        <el-form-item label="Simulated API latency">
                            <el-input-number v-model="delayApi" :min="0" :step="100" controls-position="right" />
                        </el-form-item>
                    </el-col>
                    <el-col :xs="20" :md="4"><a
                            href="https://dixisolution.ru/Ishhu_rabotu_Laravel_Vue_Alpine_js_chistyj_JS_HTML_CSS">©
                            dixiPro 2026</a></el-col>
                </el-row>
            </el-card>

            <h1>SPA Search</h1>

            <!-- Searching (after click Search) -->
            <el-text v-if="loading" tag="div">
                <el-icon class="is-loading">
                    <Loading />
                </el-icon>
                Searching...
            </el-text>

            <!-- Empty -->
            <el-empty v-else-if="searched && totalFound === 0" description="No results. Try widening the filters." />

            <!-- List -->
            <el-row :gutter="12">
                <el-col v-for="p in results" :key="p.id" :xs="24" :sm="12" :md="12" :lg="12" :xl="8">
                    <CardEstate :property="p" />
                </el-col>
            </el-row>

            <el-divider />

            <el-text v-if="isLoadingMore" tag="div">
                <el-icon class="is-loading">
                    <Loading />
                </el-icon>
                Loading...
            </el-text>

            <el-text v-if="noMoreScrollData" tag="div" type="info">
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

.results-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
}

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
    top: 0;
    position: sticky;
    z-index: 1000;
    background: #ccc;
}
</style>
