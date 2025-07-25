<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { watchEffect, nextTick, computed, ref, watch } from 'vue';
import { Search, Plus, Edit, Delete } from '@element-plus/icons-vue';
import Swal from 'sweetalert2';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

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

const searchQuery = ref(props.filters.search || '');
let searchTimeout;

const performSearch = () => {
    router.get(route('setting.index'), {
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

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

const handlePageChange = (page) => {
    router.get(route('setting.index'), {
        page,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const dialogVisible = ref(false);
const editMode = ref(false);
const currentUser = ref({});

const formData = ref({
    name: '',
    email: '',
    role: 'cashier',
    password: '',
});

const resetForm = () => {
    formData.value = {
        name: '',
        email: '',
        role: 'cashier',
        password: '',
    };
};

const openCreateDialog = () => {
    editMode.value = false;
    resetForm();
    dialogVisible.value = true;
};

const openEditDialog = (user) => {
    editMode.value = true;
    currentUser.value = user;
    formData.value = {
        name: user.name,
        email: user.email,
        role: user.role,
        password: '',
    };
    dialogVisible.value = true;
};

const submitForm = () => {
    const data = { ...formData.value };
    
    if (editMode.value) {
        router.put(route('setting.update', currentUser.value.id), data, {
            onSuccess: () => {
                dialogVisible.value = false;
                resetForm();
            }
        });
    } else {
        router.post(route('setting.store'), data, {
            onSuccess: () => {
                dialogVisible.value = false;
                resetForm();
            }
        });
    }
};

const deleteUser = (user) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `User "${user.name}" will be deleted permanently!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('setting.destroy', user.id));
        }
    });
};

const getRoleColor = (role) => {
    return role === 'admin' ? 'danger' : 'primary';
};

const currentUserId = computed(() => usePage().props.auth?.user?.id);
</script>

<template>
    <Head title="User Management" />
    <AuthenticatedLayout>
        <div class="space-y-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">User Management</h2>
                <p class="mt-2 text-gray-600">Manage system users and their roles.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                <div class="flex-1 max-w-md">
                    <el-input
                        v-model="searchQuery"
                        placeholder="Search by name, email, or role..."
                        clearable
                        @clear="clearSearch"
                        class="w-full"
                    >
                        <template #prefix>
                            <el-icon><Search /></el-icon>
                        </template>
                    </el-input>
                </div>
                
                <el-button type="primary" @click="openCreateDialog">
                    <el-icon><Plus /></el-icon>
                    Add New User
                </el-button>
            </div>

            <el-table :data="users.data" style="width: 100%" class="mt-4">
                <el-table-column prop="name" label="Name" min-width="150">
                    <template #default="{ row }">
                        <div class="flex items-center">
                            <el-avatar :size="32" class="mr-3">
                                {{ row.name.charAt(0).toUpperCase() }}
                            </el-avatar>
                            <span class="font-medium">{{ row.name }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="email" label="Email" min-width="200">
                    <template #default="{ row }">
                        <span class="text-gray-600">{{ row.email }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="role" label="Role" min-width="100">
                    <template #default="{ row }">
                        <el-tag :type="getRoleColor(row.role)" effect="plain">
                            {{ row.role.charAt(0).toUpperCase() + row.role.slice(1) }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="Created" min-width="150">
                    <template #default="{ row }">
                        {{ new Date(row.created_at).toLocaleDateString('id-ID') }}
                    </template>
                </el-table-column>
                <el-table-column label="Actions" min-width="120">
                    <template #default="{ row }">
                        <el-button @click="openEditDialog(row)" size="small" type="primary" text>
                            <el-icon><Edit /></el-icon>
                        </el-button>
                        <el-button 
                            @click="deleteUser(row)" 
                            size="small" 
                            type="danger" 
                            text
                            :disabled="row.id === currentUserId"
                        >
                            <el-icon><Delete /></el-icon>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            
            <div v-if="searchQuery && users.data.length > 0" class="text-sm text-gray-600 mt-2">
                Found {{ users.total }} result(s) for "{{ searchQuery }}"
            </div>
            
            <div v-if="searchQuery && users.data.length === 0" class="text-center py-8">
                <div class="text-gray-500">
                    <el-icon size="48" class="mb-2"><Search /></el-icon>
                    <p class="text-lg font-medium">No users found</p>
                    <p class="text-sm">Try searching with different keywords</p>
                    <el-button @click="clearSearch" class="mt-3" size="small">
                        Clear Search
                    </el-button>
                </div>
            </div>
            
            <div class="flex justify-center mt-6" v-if="users.last_page > 1">
                <el-pagination
                    background
                    v-model:current-page="users.current_page"
                    :page-size="users.per_page"
                    :total="users.total"
                    :page-count="users.last_page"
                    layout="prev, pager, next, jumper, total"
                    @current-change="handlePageChange"
                    class="mt-4"
                />
            </div>
        </div>

        <el-dialog 
            v-model="dialogVisible" 
            :title="editMode ? 'Edit User' : 'Create New User'"
            width="500px"
        >
            <el-form :model="formData" label-width="100px">
                <el-form-item label="Name" required>
                    <el-input v-model="formData.name" placeholder="Enter user name" />
                </el-form-item>
                <el-form-item label="Email" required>
                    <el-input v-model="formData.email" type="email" placeholder="Enter email address" />
                </el-form-item>
                <el-form-item label="Role" required>
                    <el-select v-model="formData.role" style="width: 100%">
                        <el-option label="Admin" value="admin" />
                        <el-option label="Cashier" value="cashier" />
                    </el-select>
                </el-form-item>
                <el-form-item :label="editMode ? 'New Password' : 'Password'" :required="!editMode">
                    <el-input 
                        v-model="formData.password" 
                        type="password" 
                        :placeholder="editMode ? 'Leave blank to keep current password' : 'Enter password'"
                        show-password
                    />
                </el-form-item>
            </el-form>
            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="dialogVisible = false">Cancel</el-button>
                    <el-button type="primary" @click="submitForm">
                        {{ editMode ? 'Update' : 'Create' }}
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </AuthenticatedLayout>
</template>
