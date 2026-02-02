<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { storeToRefs } from "pinia";
import { useSearchStore } from "../stores/useSearchStore.js";
import { Filter } from "@element-plus/icons-vue";
import RangeSlider from "./RangeSlider.vue";
import Selector from "./Selector.vue";
const store = useSearchStore();

const { loadingInit, loading, loadingMore, fields } = storeToRefs(store);

const drawerState = ref(false);

function handleSearch() {
    store.search();
    drawerState.value = false;
}

function handleReset() {
    store.resetFilters();
}

onMounted(() => {
    store.loadStartData();
});

onBeforeUnmount(() => {
    store.clearDebounce();
});
</script>

<template>
    <el-button plain round :icon="Filter" @click="drawerState = !drawerState">
        <strong>Filters</strong>
    </el-button>

    <el-drawer v-model="drawerState" direction="rtl" class="drawer">
        <el-skeleton v-if="loadingInit" :rows="6" animated />

        <el-form label-position="top" v-else>
            <template v-for="field in fields" :key="field.key">
                <!-- Autocomplete -->
                <el-form-item
                    v-if="field.type === 'autocomplete'"
                    :label="field.label"
                >
                    <el-autocomplete
                        v-model="field.value"
                        :fetch-suggestions="store.fetchSuggestions"
                        :placeholder="field.placeholder"
                        clearable
                        :disabled="loadingInit"
                        style="width: 100%"
                    />
                </el-form-item>

                <!-- Selector -->
                <Selector
                    v-if="field.type === 'selector'"
                    v-model="field.value"
                    :label="field.label"
                    :min="field.min"
                    :max="field.max"
                    :disabled="loadingInit"
                />

                <!-- Slider -->
                <RangeSlider
                    v-if="field.type === 'slider'"
                    v-model="field.value"
                    :label="field.label"
                    :min="field.min"
                    :max="field.max"
                    :disabled="loadingInit"
                />
            </template>

            <el-form-item style="margin-top: 32px">
                <el-space>
                    <el-button
                        type="primary"
                        :loading="loading"
                        :disabled="loadingInit || loading"
                        @click="handleSearch"
                    >
                        Search
                    </el-button>

                    <el-button
                        :disabled="loadingInit || loading || loadingMore"
                        @click="handleReset"
                    >
                        Reset
                    </el-button>
                </el-space>
            </el-form-item>
        </el-form>
    </el-drawer>
</template>

<style>
@media (max-width: 576px) {
    .drawer {
        width: 90% !important;
    }
}

@media (min-width: 577px) and (max-width: 800px) {
    .drawer {
        width: 60% !important;
    }
}
</style>
