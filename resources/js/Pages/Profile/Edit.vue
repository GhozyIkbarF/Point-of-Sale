<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { InfoFilled } from '@element-plus/icons-vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Profile Settings</h2>
                <p class="mt-2 text-gray-600">Manage your account information and security settings.</p>
                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <el-icon class="text-blue-600 mr-2" size="16">
                            <InfoFilled />
                        </el-icon>
                        <span class="text-sm text-blue-800">
                            Profile management is restricted to administrators only.
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
