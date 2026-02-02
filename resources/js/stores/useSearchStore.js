import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useApi } from "../composables/useApi.js";
import { ElMessage } from "element-plus";

export const useSearchStore = defineStore("search", () => {
    const PAGE_SIZE = 20;

    // ============ STATE ============

    // Loading states
    const loadingInit = ref(false);
    const loading = ref(false);
    const loadingMore = ref(false);

    const isLoadingMore = ref(false);
    const noMoreScrollData = ref(false);

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

    // Delay API for testing
    const delayApi = ref(0);

    // Fields (filters)
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
            min: 0,
            max: 0,
            value: [0, 0],
        },
        {
            key: "bedrooms",
            label: "Bedrooms",
            type: "selector",
            min: 0,
            max: 0,
            value: [0, 0],
        },
        {
            key: "bathrooms",
            label: "Bathrooms",
            type: "selector",
            min: 0,
            max: 0,
            value: [0, 0],
        },
        {
            key: "storeys",
            label: "Storeys",
            type: "selector",
            min: 0,
            max: 0,
            value: [0, 0],
        },
        {
            key: "garages",
            label: "Garages",
            type: "selector",
            min: 0,
            max: 0,
            value: [0, 0],
        },
    ]);

    // ============ GETTERS ============

    const filtersKey = computed(() => {
        return JSON.stringify(buildPayloadBase());
    });

    const canLoadOrRefetch = computed(() => {
        return (
            searched.value &&
            !loading.value &&
            !loadingMore.value &&
            lastSearchKey.value &&
            filtersKey.value === lastSearchKey.value &&
            lastPayloadBase.value
        );
    });

    // ============ HELPERS ============

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

    function clearResultsOnly() {
        results.value = [];
        totalFound.value = 0;
        hasMore.value = false;
        searched.value = false;
    }

    function resetResultsState() {
        clearResultsOnly();
        lastSearchKey.value = "";
        lastPayloadBase.value = null;
        sortDir.value = "";
    }

    // ============ ACTIONS ============
    function delay(ms = 0) {
        if (ms <= 0) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            setTimeout(resolve, ms);
        });
    }

    async function loadStartData() {
        loadingInit.value = true;

        try {
            const data = await useApi({
                url: "/api/getStartData",
            });

            totalInDb.value = Number(data?.total ?? 0);

            for (const f of fields.value) {
                if (f.type === "autocomplete") continue;

                const min = Number(data?.[f.key]?.min ?? 0);
                const max = Number(data?.[f.key]?.max ?? 0);

                f.min = min;
                f.max = max;
                f.value = [min, max];
            }

            // Auto search after init
            await search();
        } catch (e) {
            ElMessage.error({
                message: e.message,
                showClose: true,
                duration: 10000,
            });
        } finally {
            loadingInit.value = false;
        }
    }

    async function search() {
        noMoreScrollData.value = false;
        loading.value = true;
        await delay(delayApi.value);
        const myId = ++reqId;
        const payloadBase = buildPayloadBase();

        // Save snapshot for pagination + sorting
        lastSearchKey.value = JSON.stringify(payloadBase);
        lastPayloadBase.value = payloadBase;

        clearResultsOnly();

        try {
            const data = await useApi({
                url: "/api/search",
                data: {
                    ...payloadBase,
                    offset: 0,
                    limit: PAGE_SIZE,
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

    async function loadMore() {
        if (isLoadingMore.value || noMoreScrollData.value) return;

        isLoadingMore.value = true;
        await delay(delayApi.value);

        const myId = ++reqId;

        try {
            const offset = results.value.length;

            const data = await useApi({
                url: "/api/search",
                data: {
                    ...lastPayloadBase.value,
                    offset,
                    limit: PAGE_SIZE,
                    sort: sortDir.value ? "price" : undefined,
                    dir: sortDir.value || undefined,
                },
            });

            if (myId !== reqId) return;

            const items = Array.isArray(data?.items) ? data.items : [];
            results.value.push(...items);

            if (typeof data?.total !== "undefined") {
                totalFound.value = Number(data.total);
            }

            // Проверяем, есть ли ещё данные
            const hasMoreData =
                data?.hasMore ?? results.value.length < totalFound.value;
            if (!hasMoreData) {
                noMoreScrollData.value = true;
            }
        } catch (e) {
            ElMessage.error({
                message: e.message,
                showClose: true,
                duration: 10000,
            });
        } finally {
            isLoadingMore.value = false;
        }
    }

    function applySort(dir) {
        if (sortDir.value === dir) return;

        sortDir.value = dir;

        if (!canLoadOrRefetch.value) return;

        search();
    }

    function resetFilters() {
        for (const f of fields.value) {
            if (f.type === "autocomplete") {
                f.value = "";
            } else {
                f.value = [f.min, f.max];
            }
        }

        resetResultsState();
    }

    // Autocomplete suggestions
    let debounceTimer = null;
    let suggestReqId = 0;

    function fetchSuggestions(query, cb) {
        const q = (query || "").trim();
        if (q.length < 1) return cb([]);

        clearTimeout(debounceTimer);
        const myId = ++suggestReqId;

        debounceTimer = setTimeout(async () => {
            try {
                const data = await useApi({
                    url: "/api/suggestions",
                    data: { q },
                });
                if (myId !== suggestReqId) return;

                cb((data || []).map((name) => ({ value: name })));
            } catch {
                cb([]);
            }
        }, 300);
    }

    function clearDebounce() {
        clearTimeout(debounceTimer);
    }

    // ============ RETURN ============

    return {
        // State
        loadingInit,
        loading,
        loadingMore,
        isLoadingMore,
        noMoreScrollData,
        totalInDb,
        results,
        totalFound,
        hasMore,
        searched,
        sortDir,
        delayApi,
        fields,

        // Getters
        filtersKey,
        canLoadOrRefetch,

        // Actions
        loadStartData,
        search,
        loadMore,
        applySort,
        resetFilters,
        fetchSuggestions,
        clearDebounce,
    };
});
