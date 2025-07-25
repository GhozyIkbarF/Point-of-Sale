<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { watchEffect, nextTick, ref, watch } from "vue";
import { Search } from '@element-plus/icons-vue';
import Swal from 'sweetalert2';
import { PriceFormatter } from "@/lib/utils";

const props = defineProps({
    products: {
        type: Object,
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
    router.get(route('products.index'), {
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

const editProduct = (id) => {
    router.visit(route('products.edit', id));
};

const deleteProduct = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "This product will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('products.destroy', id));
        }
    });
};

const handlePageChange = (page) => {
    router.get(route('products.index'), {
        page,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <div class="space-y-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Products</h2>
                <p class="mt-2 text-gray-600">Manage your products here.</p>
            </div>
            
            <!-- Search and Actions Row -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                <!-- Search Input -->
                <div class="flex-1 max-w-md">
                    <el-input
                        v-model="searchQuery"
                        placeholder="Search by SKU or product name..."
                        clearable
                        @clear="clearSearch"
                        class="w-full"
                    >
                        <template #prefix>
                            <el-icon><Search /></el-icon>
                        </template>
                    </el-input>
                </div>
                
                <!-- Add Product Button -->
                <el-button type="primary" @click="router.visit(route('products.create'))">
                    Add New Product
                </el-button>
            </div>
            <el-table :data="products.data" style="width: 100%" class="mt-4">
                <el-table-column prop="sku" label="SKU" min-width="100">
                    <template #default="{ row }">
                        <span>{{ row.sku }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="name" label="Name" min-width="120">
                    <template #default="{ row }">
                        <span>{{ row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="price" label="Price" min-width="100">
                    <template #default="{ row }">
                        <span>
                            {{ PriceFormatter(row.price) }}
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="stock" label="Stock" min-width="80">
                    <template #default="{ row }">
                        <el-tag :type="row.stock > 10 ? 'success' : row.stock > 5 ? 'warning' : 'danger'">
                            {{ row.stock }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="Actions" min-width="120">
                    <template #default="{ row }">
                        <el-button @click="editProduct(row.id)" size="small">Edit</el-button>
                        <el-button @click="deleteProduct(row.id)" type="danger" size="small">Delete</el-button>
                    </template>
                </el-table-column>
            </el-table>
            
            <!-- Search Results Info -->
            <div v-if="searchQuery && products.data.length > 0" class="text-sm text-gray-600 mt-2">
                Found {{ products.total }} result(s) for "{{ searchQuery }}"
            </div>
            
            <!-- No Results Message -->
            <div v-if="searchQuery && products.data.length === 0" class="text-center py-8">
                <div class="text-gray-500">
                    <el-icon size="48" class="mb-2"><Search /></el-icon>
                    <p class="text-lg font-medium">No products found</p>
                    <p class="text-sm">Try searching with different keywords</p>
                    <el-button @click="clearSearch" class="mt-3" size="small">
                        Clear Search
                    </el-button>
                </div>
            </div>
            
            <div class="flex justify-center mt-6" v-if="products.last_page > 1">
                <el-pagination
                    background
                    v-model:current-page="products.current_page"
                    :page-size="products.per_page"
                    :total="products.total"
                    :page-count="products.last_page"
                    layout="prev, pager, next, jumper, total"
                    @current-change="handlePageChange"
                    class="mt-4"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
