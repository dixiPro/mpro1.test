<!-- components/RangeSlider.vue -->
<script setup>
import { computed } from "vue";

const model = defineModel({ type: Array, required: true }); // [min, max]

const props = defineProps({
    label: { type: String, required: true },
    min: { type: Number, required: true },
    max: { type: Number, required: true },
    disabled: { type: Boolean, default: false },
    money: { type: Boolean, default: false },

    // 'edges' | 'auto' | 'all'
    marksType: { type: String, default: "edges" },
});

function fmt(val) {
    const n = Number(val ?? 0);
    const s = n.toLocaleString();
    return props.money ? `$${s}` : s;
}

const marks = computed(() => {
    const min = props.min;
    const max = props.max;

    if (min >= max) return undefined;

    // only start and end
    if (props.marksType === "edges") {
        return {
            [min]: fmt(min),
            [max]: fmt(max),
        };
    }

    // start / middle / end
    if (props.marksType === "auto") {
        const mid = Math.round((min + max) / 2);
        return {
            [min]: fmt(min),
            [mid]: fmt(mid),
            [max]: fmt(max),
        };
    }

    // all integer values (ONLY for small ranges)
    if (props.marksType === "all") {
        const out = {};
        const limit = 20; // idiot-proof guard

        if (max - min > limit) {
            return {
                [min]: fmt(min),
                [max]: fmt(max),
            };
        }

        for (let i = min; i <= max; i++) {
            out[i] = fmt(i);
        }
        return out;
    }

    return undefined;
});
</script>

<template>
    <el-form-item :label="label" style="width: 100%">
        <el-slider
            v-model="model"
            range
            :min="min"
            :max="max"
            :marks="marks"
            :disabled="disabled"
            :format-tooltip="money ? fmt : undefined"
            style="width: 100%"
        />
    </el-form-item>
</template>

<style scoped>
:deep(.el-form-item__label) {
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
    margin-top: 36px !important;
    line-height: 1.1;
}
/* edge marks labels */
:deep(.el-slider__marks-text:first-child) {
    transform: translateX(0);
    text-align: left;
}

:deep(.el-slider__marks-text:last-child) {
    transform: translateX(-100%);
    text-align: right;
}
</style>
