<!-- components/RangeSlider.vue -->
<script setup>
import { ref, watch } from "vue";

const model = defineModel({ type: Array, required: true }); // [min, max]

const props = defineProps({
    label: { type: String, required: true },
    min: { type: Number, required: true },
    max: { type: Number, required: true },
    disabled: { type: Boolean, default: false },
});

const minInput = ref("");
const maxInput = ref("");

watch(
    model,
    (newModel) => {
        minInput.value = newModel[0].toString();
        maxInput.value = newModel[1].toString();
    },
    { immediate: true },
);

const commitMin = () => {
    let num = Number(minInput.value);
    const max = Number(maxInput.value);

    if (
        !Number.isFinite(num) ||
        num < Number(props.min) ||
        num > Number(model.value[1]) ||
        num > Number(maxInput.value)
    ) {
        num = Number(model.value[0]);
    }
    model.value = [Number(num), model.value[1]];
};

const commitMax = () => {
    const num = Number(maxInput.value);

    if (
        !Number.isFinite(num) ||
        num > Number(props.max) ||
        num < Number(model.value[0]) ||
        num < Number(minInput.value)
    ) {
        // Откатываем поле к текущему значению модели (ничего не меняем)
        num = Number(model.value[1]);
    }

    model.value = [model.value[0], Number(num)];
};

function fmt(val) {
    const n = Number(val ?? 0);
    const s = n.toLocaleString();
    return `$${s}`;
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
            :format-tooltip="fmt"
            style="width: 100%"
        />
        <div class="range-inputs">
            <el-input
                v-model="minInput"
                :disabled="disabled"
                class="range-input"
                @blur="commitMin"
                @keyup.enter="commitMin"
            >
                <template #prefix>$</template>
            </el-input>

            <el-input
                v-model="maxInput"
                :disabled="disabled"
                class="range-input"
                @blur="commitMax"
                @keyup.enter="commitMax"
            >
                <template #prefix>$</template>
            </el-input>
        </div>
    </el-form-item>
</template>

<style scoped>
.range-inputs {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    width: 100%;
}

.range-input :deep(.el-input__prefix) {
    margin-right: 4px;
}
</style>
