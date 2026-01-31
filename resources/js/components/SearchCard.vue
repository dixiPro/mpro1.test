<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { useApi } from "../composables/useApi.js";
import RangeSlider from "./RangeSlider.vue";

const emit = defineEmits([
    "init-loading",
    "filters-key",
    "data-loaded",
    "search",
    "reset",
]);

const props = defineProps({
    loading: { type: Boolean, default: false },
    loadingMore: { type: Boolean, default: false },
});

const delayApi = defineModel("delayApi", 0);

// ============ STATE ============
const loadingInit = ref(false);
const totalInDb = ref(0);

// ============ FIELDS ============
const fields = ref([
    {
        key: "name",
        label: "Name",
        type: "autocomplete",
        placeholder: "Type a name...",
        value: "",
    },
    {
        key: "price",
        label: "Price",
        type: "slider",
        money: true,
        marksType: "edges",
        min: 0,
        max: 0,
        value: [0, 0],
    },
    {
        key: "bedrooms",
        label: "Bedrooms",
        type: "slider",
        money: false,
        marksType: "all",
        min: 0,
        max: 0,
        value: [0, 0],
    },
    {
        key: "bathrooms",
        label: "Bathrooms",
        type: "slider",
        money: false,
        marksType: "all",
        min: 0,
        max: 0,
        value: [0, 0],
    },
    {
        key: "storeys",
        label: "Storeys",
        type: "slider",
        money: false,
        marksType: "all",
        min: 0,
        max: 0,
        value: [0, 0],
    },
    {
        key: "garages",
        label: "Garages",
        type: "slider",
        money: false,
        marksType: "all",
        min: 0,
        max: 0,
        value: [0, 0],
    },
]);

// ============ PAYLOAD (WITHOUT OFFSET) ============
function buildPayloadBase() {
    const payload = {};

    for (const f of fields.value) {
        if (f.type === "autocomplete") {
            payload[f.key] = f.value || "";
        } else {
            payload[`${f.key}_min`] = f.value?.[0] ?? f.min ?? 0;
            payload[`${f.key}_max`] = f.value?.[1] ?? f.max ?? 0;
        }
    }

    return payload;
}

function makeSearchKey() {
    return JSON.stringify(buildPayloadBase());
}

function emitFiltersKey() {
    emit("filters-key", makeSearchKey());
}

// Emit key whenever user changes filters (prevents stale loadMore)
watch(fields, () => emitFiltersKey(), { deep: true });

// ============ ACTIONS ============
function handleSearch() {
    const payloadBase = buildPayloadBase();
    emit("search", payloadBase, JSON.stringify(payloadBase));
}

function resetFilters() {
    for (const f of fields.value) {
        if (f.type === "autocomplete") {
            f.value = "";
        } else {
            f.value = [f.min, f.max];
        }
    }

    emit("reset");
    emitFiltersKey();
}

// ============ API: START DATA ============
async function loadStartData() {
    loadingInit.value = true;
    emit("init-loading", true);

    try {
        const data = await useApi("/api/getStartData", {}, delayApi.value);

        totalInDb.value = Number(data?.total ?? 0);

        for (const f of fields.value) {
            if (f.type !== "slider") continue;

            const min = Number(data?.[f.key]?.min ?? 0);
            const max = Number(data?.[f.key]?.max ?? 0);

            f.min = min;
            f.max = max;
            f.value = [min, max];
        }

        emit("data-loaded", { totalInDb: totalInDb.value });
        emitFiltersKey();
    } finally {
        loadingInit.value = false;
        emit("init-loading", false);
    }
}

// ============ AUTOCOMPLETE (>= 3 chars) ============
let debounceTimer = null;
let reqId = 0;

function fetchSuggestions(query, cb) {
    const q = (query || "").trim();
    if (q.length < 1) return cb([]);

    clearTimeout(debounceTimer);
    const myId = ++reqId;

    debounceTimer = setTimeout(async () => {
        try {
            const data = await useApi("/api/suggestions", { q }, 0);
            if (myId !== reqId) return;

            cb((data || []).map((name) => ({ value: name })));
        } catch {
            cb([]);
        }
    }, 300);
}

// ============ LIFECYCLE ============
onMounted(loadStartData);

onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
});
</script>

<template>
    <el-skeleton v-if="loadingInit" :rows="6" animated />
    <el-card v-else>
        <el-form label-position="top">
            <template v-for="field in fields" :key="field.key">
                <!-- Autocomplete -->
                <el-form-item
                    v-if="field.type === 'autocomplete'"
                    :label="field.label"
                >
                    <el-autocomplete
                        v-model="field.value"
                        :fetch-suggestions="fetchSuggestions"
                        :placeholder="field.placeholder"
                        clearable
                        :disabled="loadingInit"
                        style="width: 100%"
                    />
                </el-form-item>

                <!-- Slider -->
                <RangeSlider
                    v-else
                    v-model="field.value"
                    :label="field.label"
                    :min="field.min"
                    :max="field.max"
                    :disabled="loadingInit"
                    :money="field.money"
                    :marks-type="field.marksType"
                />
            </template>

            <el-form-item style="margin-top: 32px">
                <el-space>
                    <el-button
                        type="primary"
                        :loading="props.loading"
                        :disabled="loadingInit || props.loading"
                        @click="handleSearch"
                    >
                        Search
                    </el-button>

                    <el-button
                        :disabled="
                            loadingInit || props.loading || props.loadingMore
                        "
                        @click="resetFilters"
                    >
                        Reset
                    </el-button>
                </el-space>
            </el-form-item>
        </el-form>
    </el-card>
</template>
