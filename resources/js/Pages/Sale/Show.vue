<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { PriceFormatter } from '@/lib/utils';

const props = defineProps({
    sale: Object,
});

const saleInfo = computed(() => [
    { label: 'Invoice', value: props.sale.invoice_number },
    { label: 'Date', value: props.sale.created_at ? new Date(props.sale.created_at).toLocaleString('id-ID') : '-' },
    { label: 'Customer', value: props.sale.customer_name || '-' },
    { label: 'Cashier', value: props.sale.cashier?.name || '-' },
    { label: 'Total', value: PriceFormatter(props.sale.total_price) },
    { label: 'Paid', value: PriceFormatter(props.sale.paid_amount) },
    { label: 'Change', value: PriceFormatter(props.sale.change) },
]);

const goBack = () => {
    router.visit(route('sales.index'));
};
</script>

<template>
    <Head title="Sale Detail" />
    <AuthenticatedLayout>
        <div class="">
            <h2 class="text-2xl font-semibold mb-4">Sale Detail</h2>
            <el-table :data="saleInfo" :show-header="false" class="mb-4" table-layout="auto">
                <el-table-column width="120">
                    <template #default="{ row }">
                        <span class="font-semibold text-gray-700">{{ row.label }}</span>
                    </template>
                </el-table-column>
                <el-table-column>
                    <span class="text-gray-700">:</span>
                </el-table-column>
                <el-table-column>
                    <template #default="{ row }">
                        <span class="text-gray-900">{{ row.value }}</span>
                    </template>
                </el-table-column>
            </el-table>

            <h3 class="text-lg font-semibold mt-6">Purchased Products</h3>
            <el-table :data="sale.details" style="width: 100%" class="mb-4">
                <el-table-column label="#" type="index" width="50" />
                <el-table-column label="Product">
                    <template #default="{ row }">
                        {{ row.product?.name || '-' }}
                    </template>
                </el-table-column>
                <el-table-column prop="qty" label="Qty" width="80" />
                <el-table-column prop="price_at_time" label="Price" width="100">
                    <template #default="{ row }">
                        {{ PriceFormatter(row.price_at_time) }}
                    </template>
                </el-table-column>
                <el-table-column label="Subtotal">
                    <template #default="{ row }">
                        {{ PriceFormatter(row.qty * row.price_at_time) }}
                    </template>
                </el-table-column>
            </el-table>
            <el-button @click="goBack">Back</el-button>
        </div>
    </AuthenticatedLayout>
</template>
