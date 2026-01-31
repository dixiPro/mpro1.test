<!-- components/RangeDigit.vue -->
<script setup>
const model = defineModel({ type: Array, required: true }); // [min, max]

const props = defineProps({
    label: { type: String, required: true },
    min: { type: Number, required: true },
    max: { type: Number, required: true },
    disabled: { type: Boolean, default: false },

    // If true: show $ and thousands separators (use for Price)
    money: { type: Boolean, default: false },
});

function fmt(val) {
    const n = Number(val ?? 0);
    const s = n.toLocaleString();
    return props.money ? `$${s}` : s;
}
</script>

<template>
    <el-form-item :label="label" style="width: 100%">
        <el-slider
            v-model="model"
            range
            :min="min"
            :max="max"
            :disabled="disabled"
            :format-tooltip="money ? fmt : undefined"
            style="width: 100%"
        />
        <div class="range-values">
            <span>{{ fmt(model[0]) }}</span>
            <span>{{ fmt(model[1]) }}</span>
        </div>
    </el-form-item>
</template>

<style scoped>
.range-values {
    display: flex;
    justify-content: space-between;
    /* margin-top: 6px; */
    font-size: 12px;
    color: var(--el-text-color-secondary);
    width: 100%;
}
</style>
