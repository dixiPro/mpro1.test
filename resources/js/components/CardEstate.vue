<script setup>
import { computed } from "vue";

const props = defineProps({
    property: { type: Object, required: true },
});

const formattedPrice = computed(() =>
    new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 0,
    }).format(props.property.price ?? 0),
);
</script>

<template>
    <el-card class="property-card" shadow="hover" body-class="body">
        <div class="media" aria-hidden="true"></div>

        <div class="content">
            <div class="top">
                <div class="name">{{ property.name }}</div>
                <div class="price">{{ formattedPrice }}</div>
            </div>

            <div class="stats">
                <div>
                    Bedrooms: <b>{{ property.bedrooms }}</b>
                </div>
                <div>
                    Bathrooms: <b>{{ property.bathrooms }}</b>
                </div>
                <div>
                    Storeys: <b>{{ property.storeys }}</b>
                </div>
                <div>
                    Garages: <b>{{ property.garages }}</b>
                </div>
            </div>
        </div>
    </el-card>
</template>

<style scoped>
.property-card {
    width: 100%;
}

/* делаем внутренности карточки простыми и управляемыми */
:deep(.body) {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

/* квадратик под картинку */
.media {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    background: #f2f3f5;
    flex: 0 0 72px;
}

.content {
    min-width: 0;
    flex: 1;
}

.top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 8px;
}

.name {
    font-size: 16px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.price {
    font-size: 18px;
    font-weight: 700;
    color: var(--el-color-primary);
    white-space: nowrap;
}

.stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 16px;
    color: #606266;
}

/* мобила: карточка во всю ширину и поля в 1 колонку */
@media (max-width: 768px) {
    .stats {
        grid-template-columns: 1fr;
    }
}
</style>
