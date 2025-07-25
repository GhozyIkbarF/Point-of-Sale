<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watchEffect, nextTick } from 'vue';
import Swal from 'sweetalert2';

import { usePage } from '@inertiajs/vue3';

watchEffect(() => {
    const flash = usePage().props.flash;
    
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
    products: Array,
    cashiers: Array,
    currentUser: Object,
});

const form = useForm({
    customer_name: '',
    cashier_id: props.currentUser?.role === 'cashier' ? props.currentUser.id : '',
    paid_amount: '',
    items: [
        { product_id: '', qty: 1 },
    ],
});

// Check if current user is cashier
const isCashier = computed(() => props.currentUser?.role === 'cashier');

const addItem = () => {
    form.items.push({ product_id: '', qty: 1 });
};
const removeItem = (idx) => {
    if (form.items.length > 1) form.items.splice(idx, 1);
};

const totalPrice = computed(() => {
    return form.items.reduce((sum, item) => {
        const prod = props.products.find(p => p.id === Number(item.product_id));
        return sum + (prod ? prod.price * item.qty : 0);
    }, 0);
});

const submit = () => {
    form.post(route('sales.store'));
};

const cancel = () => {
    router.visit(route('sales.index'));
};
</script>

<template>
    <Head title="Create Sale" />
    <AuthenticatedLayout>
        <div class="mx-auto p-6">
            <h2 class="text-2xl font-semibold mb-4">Create Sale</h2>
            
            <el-form @submit.prevent="submit" :model="form" label-width="120px">
                <el-form-item label="Customer Name" :error="form.errors.customer_name">
                    <el-input v-model="form.customer_name" autocomplete="off" />
                </el-form-item>
                <el-form-item label="Cashier" :error="form.errors.cashier_id">
                    <el-select 
                        v-model="form.cashier_id" 
                        placeholder="Select cashier"
                        :disabled="isCashier"
                    >
                        <el-option v-for="c in cashiers" :key="c.id" :label="c.name" :value="c.id" />
                    </el-select>
                    <div v-if="isCashier" class="text-sm text-gray-500 mt-1">
                        Anda otomatis terpilih sebagai kasir
                    </div>
                </el-form-item>
                <el-form-item label="Items" :error="form.errors.items">
                    <div class="w-full max-w-full flex flex-col space-y-2">
                        <div class="flex flex-col space-y-2">
                            <div v-for="(item, idx) in form.items" :key="idx" class="flex items-center gap-2 mb-2 w-full">
                                <el-select v-model="item.product_id" placeholder="Select product" class="w-full">
                                    <el-option v-for="p in products" :key="p.id" :label="`${p.name} (${p.price}/pcs)`" :value="p.id" />
                                </el-select>
                                <el-input-number v-model="item.qty" :min="1" :max="getMaxStock(item.product_id)" class="w-32" />
                                <el-button @click="() => removeItem(idx)" type="danger" :disabled="form.items.length === 1">Remove</el-button>
                            </div>
                        </div>
                        <el-button @click="addItem" type="primary" plain>Add Item</el-button>
                    </div>
                </el-form-item>
                <el-form-item label="Total">
                    <span class="font-bold">{{ totalPrice }}</span>
                </el-form-item>
                <el-form-item label="Paid Amount" :error="form.errors.paid_amount">
                    <el-input v-model="form.paid_amount" type="number" autocomplete="off" />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="submit" :loading="form.processing">Save</el-button>
                    <el-button @click="cancel" class="ml-2">Cancel</el-button>
                </el-form-item>
            </el-form>
        </div>
    </AuthenticatedLayout>
</template>

<script>
// Helper for max stock per product
export default {
    methods: {
        getMaxStock(product_id) {
            const prod = this.$props.products.find(p => p.id === Number(product_id));
            return prod ? prod.stock : 9999;
        }
    }
}
</script>
