<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watchEffect, nextTick, computed } from "vue";
import Swal from "sweetalert2";
import { usePage } from "@inertiajs/vue3";
import {
    ShoppingCart,
    Money,
    Box,
    TrendCharts,
    Download,
    Filter,
    User,
    Calendar,
} from "@element-plus/icons-vue";

// Watch for flash messages
watchEffect(() => {
    const flash = usePage().props.flash;

    if (flash?.success) {
        nextTick(() => {
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: flash.success,
                timer: 3000,
                showConfirmButton: false,
            });
        });
    }

    if (flash?.error) {
        nextTick(() => {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: flash.error,
                confirmButtonText: "OK",
            });
        });
    }
});

const props = defineProps({
    period: String,
    cashier_id: [String, Number],
    start_date: String,
    end_date: String,
    report: Object,
    users: Array,
});

const filters = ref({
    period: props.period || "day",
    cashier_id: props.cashier_id ? parseInt(props.cashier_id) : "",
    start_date: props.start_date || "",
    end_date: props.end_date || "",
});

const applyFilters = () => {
    router.visit(route("reports.admin"), {
        data: {
            period: filters.value.period,
            cashier_id: filters.value.cashier_id || null,
            start_date: filters.value.start_date || null,
            end_date: filters.value.end_date || null,
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(amount);
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("id-ID").format(number);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID");
};

const formatPeriodLabel = (period) => {
    // Convert to string first to handle all data types safely
    const periodStr = String(period);
    
    // Check if period is a number (hour for daily trends)
    if (!isNaN(period) && filters.value.period === 'day') {
        return `${period}:00 - ${period}:59`;
    }
    
    // For other periods, return as is or format appropriately
    switch (filters.value.period) {
        case 'week':
        case 'month':
            // If it's a date, format it nicely
            if (periodStr.includes('-')) {
                return formatDate(periodStr);
            }
            return periodStr;
        case 'year':
            // For year period, might be in format YYYY-MM
            if (periodStr.includes('-')) {
                const [year, month] = periodStr.split('-');
                const monthNames = [
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];
                return `${monthNames[parseInt(month) - 1]} ${year}`;
            }
            return periodStr;
        default:
            return periodStr;
    }
};

const downloadExcel = () => {
    const params = new URLSearchParams({
        period: filters.value.period,
        format: "excel",
    });

    if (filters.value.cashier_id)
        params.append("cashier_id", filters.value.cashier_id);
    if (filters.value.start_date)
        params.append("start_date", filters.value.start_date);
    if (filters.value.end_date)
        params.append("end_date", filters.value.end_date);

    window.open(
        route("reports.admin.export") + "?" + params.toString(),
        "_blank"
    );
};

const downloadPdf = () => {
    const params = new URLSearchParams({
        period: filters.value.period,
        format: "pdf",
    });

    if (filters.value.cashier_id)
        params.append("cashier_id", filters.value.cashier_id);
    if (filters.value.start_date)
        params.append("start_date", filters.value.start_date);
    if (filters.value.end_date)
        params.append("end_date", filters.value.end_date);

    window.open(
        route("reports.admin.export") + "?" + params.toString(),
        "_blank"
    );
};

const periodOptions = [
    { label: "Daily", value: "day" },
    { label: "Weekly", value: "week" },
    { label: "Monthly", value: "month" },
    { label: "Yearly", value: "year" },
];

const selectedCashierName = computed(() => {
    if (!filters.value.cashier_id) return "All Cashiers";
    const cashier = props.users.find((u) => u.id === filters.value.cashier_id);
    return cashier ? cashier.name : "All Cashiers";
});

const periodLabel = computed(() => {
    const option = periodOptions.find((p) => p.value === filters.value.period);
    return option ? option.label : "Daily";
});
</script>

<template>
    <Head title="Admin Reports - Sales Analytics" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">
                    Sales Analytics & Reports
                </h2>
                <p class="mt-2 text-gray-600">
                    Comprehensive sales reports and performance analytics
                </p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3
                    class="text-lg font-semibold text-gray-800 mb-4 flex items-center"
                >
                    <el-icon class="mr-2"><Filter /></el-icon>
                    Report Filters
                </h3>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Period</label
                        >
                        <el-select v-model="filters.period" class="w-full">
                            <el-option
                                v-for="option in periodOptions"
                                :key="option.value"
                                :label="option.label"
                                :value="option.value"
                            />
                        </el-select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Cashier</label
                        >
                        <el-select
                            v-model="filters.cashier_id"
                            clearable
                            placeholder="All Cashiers"
                            class="w-full"
                        >
                            <el-option
                                v-for="user in users"
                                :key="user.id"
                                :label="user.name"
                                :value="user.id"
                            />
                        </el-select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Start Date</label
                        >
                        <el-date-picker
                            v-model="filters.start_date"
                            type="date"
                            format="YYYY-MM-DD"
                            value-format="YYYY-MM-DD"
                            style="width: 100%"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >End Date</label
                        >
                        <el-date-picker
                            v-model="filters.end_date"
                            type="date"
                            format="YYYY-MM-DD"
                            value-format="YYYY-MM-DD"
                            style="width: 100%"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Current Filter:</span>
                        {{ periodLabel }} | {{ selectedCashierName }}
                        <span v-if="filters.start_date && filters.end_date">
                            | {{ formatDate(filters.start_date) }} -
                            {{ formatDate(filters.end_date) }}
                        </span>
                    </div>

                    <div class="flex space-x-2">
                        <el-button
                            type="primary"
                            @click="applyFilters"
                            :icon="Filter"
                        >
                            Apply Filters
                        </el-button>
                        <el-button
                            type="success"
                            @click="downloadExcel"
                            :icon="Download"
                        >
                            Excel
                        </el-button>
                        <el-button
                            type="danger"
                            @click="downloadPdf"
                            :icon="Download"
                        >
                            PDF
                        </el-button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">
                                Total Transactions
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    formatNumber(
                                        report.summary.total_transactions
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <el-icon class="text-blue-600" size="24"
                                ><ShoppingCart
                            /></el-icon>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">
                                Total Revenue
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    formatCurrency(report.summary.total_revenue)
                                }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                        >
                            <el-icon class="text-green-600" size="24"
                                ><Money
                            /></el-icon>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">
                                Items Sold
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    formatNumber(
                                        report.summary.total_items_sold
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"
                        >
                            <el-icon class="text-purple-600" size="24"
                                ><Box
                            /></el-icon>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600">
                                Avg Transaction
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    formatCurrency(
                                        report.summary.average_transaction
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center"
                        >
                            <el-icon class="text-orange-600" size="24"
                                ><TrendCharts
                            /></el-icon>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sales by Cashier -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3
                        class="text-lg font-semibold text-gray-800 mb-4 flex items-center"
                    >
                        <el-icon class="mr-2"><User /></el-icon>
                        Sales by Cashier
                    </h3>
                    <el-table
                        :data="report.sales_by_cashier"
                        style="width: 100%"
                    >
                        <el-table-column label="Cashier" min-width="120">
                            <template #default="{ row }">
                                {{ row.cashier?.name || 'Unknown' }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Transactions" min-width="100">
                            <template #default="{ row }">
                                {{ formatNumber(row.transaction_count) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Revenue" min-width="120">
                            <template #default="{ row }">
                                {{ formatCurrency(row.revenue) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Items Sold" min-width="100">
                            <template #default="{ row }">
                                {{ formatNumber(row.total_items || 0) }}
                            </template>
                        </el-table-column>
                    </el-table>
                    <div
                        v-if="report.sales_by_cashier.length === 0"
                        class="text-center text-gray-500 py-8"
                    >
                        No sales data available
                    </div>
                </div>

                <!-- Period Trends -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3
                        class="text-lg font-semibold text-gray-800 mb-4 flex items-center"
                    >
                        <el-icon class="mr-2"><Calendar /></el-icon>
                        {{ periodLabel }} Trends
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="period in report.period_trends"
                            :key="period.period"
                            class="flex justify-between items-center p-3 bg-gray-50 rounded"
                        >
                            <span class="text-sm font-medium">
                                {{ formatPeriodLabel(period.period) }}
                            </span>
                            <div class="text-right">
                                <div class="text-sm font-semibold">
                                    {{
                                        formatNumber(period.transaction_count)
                                    }}
                                    transactions
                                </div>
                                <div class="text-xs text-gray-600">
                                    {{ formatCurrency(period.revenue) }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="report.period_trends.length === 0"
                            class="text-center text-gray-500 py-8"
                        >
                            No trend data available
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Top Selling Products
                    </h3>
                    <el-table :data="report.top_products" style="width: 100%">
                        <el-table-column
                            prop="product.name"
                            label="Product"
                            min-width="150"
                        />
                        <el-table-column
                            prop="product.sku"
                            label="SKU"
                            min-width="100"
                        />
                        <el-table-column label="Qty Sold" min-width="80">
                            <template #default="{ row }">
                                {{ formatNumber(row.total_qty) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Revenue" min-width="120">
                            <template #default="{ row }">
                                {{ formatCurrency(row.total_revenue) }}
                            </template>
                        </el-table-column>
                    </el-table>
                    <div
                        v-if="report.top_products.length === 0"
                        class="text-center text-gray-500 py-8"
                    >
                        No products sold in this period
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Recent Sales
                    </h3>
                    <el-table :data="report.recent_sales" style="width: 100%">
                        <el-table-column label="Sale ID" min-width="80">
                            <template #default="{ row }">
                                #{{ row.id }}
                            </template>
                        </el-table-column>
                        <el-table-column
                            prop="cashier.name"
                            label="Cashier"
                            min-width="100"
                        />
                        <el-table-column label="Total" min-width="100">
                            <template #default="{ row }">
                                {{ formatCurrency(row.total_price) }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Date" min-width="120">
                            <template #default="{ row }">
                                {{
                                    new Date(row.created_at).toLocaleDateString(
                                        "id-ID"
                                    )
                                }}
                            </template>
                        </el-table-column>
                    </el-table>
                    <div
                        v-if="report.recent_sales.length === 0"
                        class="text-center text-gray-500 py-8"
                    >
                        No recent sales available
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
