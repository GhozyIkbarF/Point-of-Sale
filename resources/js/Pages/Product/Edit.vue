<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    sku: props.product.sku,
    name: props.product.name,
    price: props.product.price,
    stock: props.product.stock,
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};

const cancel = () => {
    router.visit(route('products.index'));
};
</script>

<template>
    <Head title="Edit Product" />
    <AuthenticatedLayout>
        <div>
            <h2 class="text-2xl font-semibold mb-4">Edit Product</h2>
            <el-form @submit.prevent="submit" :model="form" label-width="100px">
                <el-form-item label="SKU" :error="form.errors.sku">
                    <el-input v-model="form.sku" autocomplete="off" />
                </el-form-item>
                <el-form-item label="Name" :error="form.errors.name">
                    <el-input v-model="form.name" autocomplete="off" />
                </el-form-item>
                <el-form-item label="Price" :error="form.errors.price">
                    <el-input v-model="form.price" type="number" autocomplete="off" />
                </el-form-item>
                <el-form-item label="Stock" :error="form.errors.stock">
                    <el-input v-model="form.stock" type="number" autocomplete="off" />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="submit" :loading="form.processing">Update</el-button>
                    <el-button @click="cancel" class="ml-2">Cancel</el-button>
                </el-form-item>
            </el-form>
        </div>
    </AuthenticatedLayout>
</template>
