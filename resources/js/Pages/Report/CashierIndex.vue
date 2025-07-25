<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watchEffect, nextTick } from 'vue';
import Swal from 'sweetalert2';
import { usePage } from '@inertiajs/vue3';
import { ShoppingCart, Money, Box, TrendCharts, Download, User } from '@element-plus/icons-vue';

// Watch for flash messages
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
    
    if (flash?.error) {
        nextTick(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: flash.error,
                confirmButtonText: 'OK'
            });
        });
    }
});

const props = defineProps({
    selectedDate: String,
    report: Object,
    cashier: Object
});

const selectedDate = ref(props.selectedDate);

const handleDateChange = () => {
    router.visit(route('reports.index', { date: selectedDate.value }));
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID').format(number);
};

const downloadExcel = () => {
    window.open(route('reports.export.excel', { date: selectedDate.value }), '_blank');
};

const downloadPdf = () => {
    const params = new URLSearchParams({
        date: selectedDate.value,
        format: 'pdf'
    });
    window.open(route('reports.export.pdf') + '?' + params.toString(), '_blank');
};
</script>

<template>
    <Head title="My Daily Sales Report" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">My Daily Sales Report</h2>
                <p class="mt-2 text-gray-600">Your personal daily sales summary and performance</p>
                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <el-icon class="text-blue-600 mr-2" size="16">
                            <User />
                        </el-icon>
                        <span class="text-sm text-blue-800">
                            Showing sales report for: <strong>{{ cashier.name }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Date Selector and Export -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700">Select Date:</label>
                        <el-date-picker
                            v-model="selectedDate"
                            type="date"
                            format="YYYY-MM-DD"
                            value-format="YYYY-MM-DD"
                            @change="handleDateChange"
                            class="w-48"
                        />
                    </div>
                    
                    <div class="flex space-x-2">
                        <el-button 
                            type="success" 
                            @click="downloadExcel"
                            :icon="Download"
                            size="small"
                        >
                            Download Excel
                        </el-button>
                        <el-button 
                            type="danger" 
                            @click="downloadPdf"
                            :icon="Download"
                            size="small"
                        >
                            Download PDF
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">My Transactions</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatNumber(report.summary.total_transactions) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <el-icon class="text-blue-600" size="24"><ShoppingCart /></el-icon>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">My Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatCurrency(report.summary.total_revenue) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <el-icon class="text-green-600" size="24"><Money /></el-icon>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Items Sold</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatNumber(report.summary.total_items_sold) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <el-icon class="text-purple-600" size="24"><Box /></el-icon>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">Avg Transaction</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatCurrency(report.summary.average_transaction) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <el-icon class="text-orange-600" size="24"><TrendCharts /></el-icon>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Message -->
            <div v-if="report.summary.total_transactions > 0" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <el-icon class="text-green-600 mr-2" size="20">
                        <TrendCharts />
                    </el-icon>
                    <div>
                        <p class="text-green-800 font-medium">Great job today!</p>
                        <p class="text-green-700 text-sm">
                            You processed {{ formatNumber(report.summary.total_transactions) }} transactions 
                            and generated {{ formatCurrency(report.summary.total_revenue) }} in revenue.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Hourly Performance -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">My Hourly Performance</h3>
                    <div class="space-y-2">
                        <div v-for="hour in report.hourly_transactions" :key="hour.hour" 
                             class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="text-sm font-medium">{{ hour.hour }}:00</span>
                            <div class="text-right">
                                <div class="text-sm font-semibold">{{ hour.transaction_count }} transactions</div>
                                <div class="text-xs text-gray-600">{{ formatCurrency(hour.revenue) }}</div>
                            </div>
                        </div>
                        <div v-if="report.hourly_transactions.length === 0" 
                             class="text-center text-gray-500 py-8">
                            No transactions for this date
                        </div>
                    </div>
                </div>

                <!-- Top Products I Sold -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Products I Sold</h3>
                    <el-table :data="report.top_products" style="width: 100%">
                        <el-table-column prop="product.name" label="Product" min-width="120" />
                        <el-table-column prop="product.sku" label="SKU" min-width="80" />
                        <el-table-column label="Qty Sold" min-width="80">
                            <template #default="{ row }">
                                {{ formatNumber(row.total_qty) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Revenue" min-width="100">
                            <template #default="{ row }">
                                {{ formatCurrency(row.total_revenue) }}
                            </template>
                        </el-table-column>
                    </el-table>
                    <div v-if="report.top_products.length === 0" 
                         class="text-center text-gray-500 py-8">
                        No products sold for this date
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
