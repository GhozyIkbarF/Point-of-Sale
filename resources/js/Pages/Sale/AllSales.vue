<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { watchEffect, nextTick, computed, ref, watch } from 'vue';
import { Search, User, ShoppingCart } from '@element-plus/icons-vue';
import Swal from 'sweetalert2';
import { usePage } from '@inertiajs/vue3';
import { PriceFormatter } from "@/lib/utils";

// Get current user data
const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const isCashier = computed(() => user.value?.role === 'cashier');

watchEffect(() => {
    const flash = usePage().props.flash;
    
    if (flash?.success) {
        nextTick(() => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: flash.success,
                timer: 3000,
                showConfirmButton: false
            });
        });
    }
});

const props = defineProps({
    sales: {
        type: [Object, Array],
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Search functionality
const searchQuery = ref(props.filters.search || '');
let searchTimeout;

const performSearch = () => {
    router.get(route('sales.all'), {
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Debounced search
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 500);
});

const clearSearch = () => {
    searchQuery.value = '';
    performSearch();
};

const addSale = () => {
    router.visit(route('sales.create'));
};

const handlePageChange = (page) => {
    router.get(route('sales.all'), {
        page,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const salesData = computed(() => {
    if (Array.isArray(props.sales)) {
        return props.sales;
    }
    return props.sales.data || [];
});

const paginationInfo = computed(() => {
    if (Array.isArray(props.sales)) {
        return null;
    }
    return props.sales;
});

const totalTransactions = computed(() => {
    return salesData.value.length;
});
</script>

<template>
    <Head title="All Sales" />
    <AuthenticatedLayout>
        <div class="space-y-4">

            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">
                            <span v-if="isAdmin">Sales Management</span>
                            <span v-else>All Sales Transactions</span>
                        </h2>
                        <p class="mt-2 text-blue-100">
                            <span v-if="isAdmin">Complete overview of all sales transactions in the system.</span>
                            <span v-else>View all sales transactions from all cashiers.</span>
                        </p>
                        <div v-if="isCashier" class="mt-3 flex items-center">
                            <el-icon class="mr-2" size="16"><User /></el-icon>
                            <span class="text-blue-100">Viewing as: <strong class="text-white">{{ user.name }} (Cashier)</strong></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">{{ paginationInfo ? paginationInfo.total : totalTransactions }}</div>
                        <div class="text-blue-100">Total Sales</div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center bg-white rounded-lg shadow p-4">
                <div class="flex-1 max-w-md">
                    <el-input
                        v-model="searchQuery"
                        placeholder="Search by invoice, customer, or cashier name..."
                        clearable
                        @clear="clearSearch"
                        class="w-full"
                    >
                        <template #prefix>
                            <el-icon><Search /></el-icon>
                        </template>
                    </el-input>
                </div>
                
                <el-button type="primary" @click="addSale">
                    Add New Sale
                </el-button>
            </div>

            <div class="bg-white rounded-lg shadow">
                <el-table :data="salesData" style="width: 100%">
                    <el-table-column prop="invoice_number" label="Invoice" min-width="120">
                        <template #default="{ row }">
                            <span class="font-medium text-blue-600">{{ row.invoice_number }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="customer_name" label="Customer" min-width="120">
                        <template #default="{ row }">
                            <span class="text-gray-900">{{ row.customer_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="cashier.name" label="Cashier" min-width="100">
                        <template #default="{ row }">
                            <el-tag type="info" size="small">{{ row.cashier.name }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_price" label="Total" min-width="100">
                        <template #default="{ row }">
                            <span class="font-semibold text-green-600">
                                {{ PriceFormatter(row.total_price) }} 
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="paid_amount" label="Paid" min-width="100">
                        <template #default="{ row }">
                            <span>
                                {{ PriceFormatter(row.paid_amount) }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="change" label="Change" min-width="100">
                        <template #default="{ row }">
                            <span class="text-gray-600">
                                {{ PriceFormatter(row.change) }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="created_at" label="Date" min-width="150">
                        <template #default="{ row }">
                            <div class="text-sm">
                                <div>{{ new Date(row.created_at).toLocaleDateString('id-ID') }}</div>
                                <div class="text-gray-500">{{ new Date(row.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="Actions" min-width="120">
                        <template #default="{ row }">
                            <el-button @click="router.visit(route('sales.show', row.id))" size="small" type="primary">
                                Detail
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                
                <div class="flex justify-between items-center p-4 border-t" v-if="paginationInfo && paginationInfo.last_page > 1">
                    <div class="text-sm text-gray-600">
                        Showing {{ paginationInfo.from || 0 }} to {{ paginationInfo.to || 0 }} of {{ paginationInfo.total || 0 }} results
                    </div>
                    <el-pagination
                        background
                        :current-page="paginationInfo.current_page"
                        :page-size="paginationInfo.per_page"
                        :total="paginationInfo.total"
                        :page-count="paginationInfo.last_page"
                        layout="prev, pager, next"
                        @current-change="handlePageChange"
                        small
                    />
                </div>
                
                <div v-if="searchQuery && salesData.length > 0" class="px-4 py-2 text-sm text-gray-600 border-t">
                    Found {{ paginationInfo ? paginationInfo.total : salesData.length }} result(s) for "{{ searchQuery }}"
                </div>
                
                <div v-if="searchQuery && salesData.length === 0" class="text-center py-12">
                    <div class="text-gray-500">
                        <el-icon size="48" class="mb-4 text-gray-400"><Search /></el-icon>
                        <p class="text-lg font-medium">No sales found</p>
                        <p class="text-sm">Try searching with different keywords</p>
                        <el-button @click="clearSearch" class="mt-4" size="small" type="primary">
                            Clear Search
                        </el-button>
                    </div>
                </div>

                <div v-if="!searchQuery && salesData.length === 0" class="text-center py-12">
                    <div class="text-gray-500">
                        <el-icon size="48" class="mb-4 text-gray-400"><ShoppingCart /></el-icon>
                        <p class="text-lg font-medium">No sales transactions yet</p>
                        <p class="text-sm">Start by creating your first sale transaction</p>
                        <el-button @click="addSale" class="mt-4" size="small" type="primary">
                            Create First Sale
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
