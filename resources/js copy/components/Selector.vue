<script setup>
import { changeGlobalNodesTarget } from "element-plus/es/utils/index.mjs";
import { ref, onMounted, computed } from "vue";

const model = defineModel({ type: Array, required: true });

const props = defineProps({
    label: { type: String, required: true },
    min: { type: Number, required: true },
    max: { type: Number, required: true },
    disabled: { type: Boolean, default: false },
    money: { type: Boolean, default: false },
    marksType: { type: String, default: "edges" },
});

const showArr = ref([]);
const selectArr = ref([]);

onMounted(() => {
    showArr.value = Array.from(
        { length: props.max - props.min + 1 },
        (v, i) => props.min + i,
    );
    selectArr.value = Array.from(
        { length: props.max - props.min + 1 },
        (v, i) => true,
    );
});

function changeState(num) {
    const idx = num - props.min;
    selectArr.value[idx] = !selectArr.value[idx];

    const hasGap = selectArr.value.some(
        (v, i) =>
            v === false &&
            selectArr.value.slice(0, i).includes(true) &&
            selectArr.value.slice(i + 1).includes(true),
    );

    if (hasGap) {
        const i = selectArr.value.indexOf(false);
        if (i !== -1) selectArr.value.fill(false, 0, i);
    }

    const allFalse = selectArr.value.every((v) => v === false);
    if (allFalse) selectArr.value[0] = true;

    const firstIdx = selectArr.value.indexOf(true);
    const lastIdx = selectArr.value.lastIndexOf(true);

    model.value[0] = showArr.value[firstIdx];
    model.value[1] = showArr.value[lastIdx];
}

const btnType = (num) => {
    const idx = num - props.min;
    return selectArr.value[idx] ? "primary" : "default";
};
const checkAny = computed(() =>
    selectArr.value.every((v) => v === true) ? "primary" : "default",
);
</script>

<template>
    <el-form-item style="width: 100%">
        <el-space>
            <span>{{ label }}</span>
            <el-button
                v-for="num in showArr"
                :key="num"
                :type="btnType(num)"
                @click="changeState(num)"
            >
                {{ num }}
            </el-button>
        </el-space>
    </el-form-item>
</template>
