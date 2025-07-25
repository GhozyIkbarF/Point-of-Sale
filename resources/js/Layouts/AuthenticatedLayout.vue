<script setup>
import { ref, computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link, router } from "@inertiajs/vue3";
import { Document, Menu as IconMenu, Box, ShoppingCart, Setting, Expand, Fold } from "@element-plus/icons-vue";
import { cn } from "@/lib/utils";
import { useSidebar } from "@/composables/useSidebar.js";
import { usePage } from "@inertiajs/vue3";

// Get current user data
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Use sidebar composable untuk persistent state
const { isCollapse, toggleSidebar, setSidebarCollapse } = useSidebar();

// Check if user is admin
const isAdmin = computed(() => user.value?.role === 'admin');
const isCashier = computed(() => user.value?.role === 'cashier');

const handleOpen = (key, keyPath) => {
    console.log(key, keyPath);
};

const handleClose = (key, keyPath) => {
    console.log(key, keyPath);
};

const activeMenu = ref(
    route().current("dashboard")
        ? "dashboard"
        : route().current("sales.index") ||
          route().current("sales.create") ||
          route().current("sales.show") ||
          route().current("sales.all") ||
          route().current("sales.my")
        ? route().current("sales.all")
            ? "sales.all"
            : route().current("sales.my")
            ? "sales.my"
            : "sales.all" // default untuk sales.index
        : route().current("products.index") ||
          route().current("products.create") ||
          route().current("products.show") ||
          route().current("products.edit")
        ? "products.index"
        : route().current("reports.index")
        ? "reports.index"
        : route().current("reports.admin")
        ? "reports.admin"
        : route().current("setting.index")
        ? "setting.index"
        : ""
);

function handleMenuSelect(index) {
    router.visit(route(index));
}
</script>

<template>
    <div class="h-screen w-full overflow-hidden">
        <el-container class="bg-gray-100 flex h-full min-h-screen">
            <el-aside
                class="bg-white max-w-min flex flex-col sticky left-0 top-0 h-screen z-10"
            >
                <div
                    :class="
                        cn('logo-container p-4 pr-0', {
                            'min-w-60': !isCollapse,
                        })
                    "
                >
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo
                            class="block h-9 w-auto fill-current text-gray-800"
                        />
                        <transition name="fade-dashboard" mode="out-in">
                            <span
                                v-if="!isCollapse"
                                class="ml-2 text-lg font-semibold"
                                >Point of Sale</span
                            >
                        </transition>
                    </Link>
                </div>
                <el-menu
                    style="border: none"
                    class="h-full"
                    :collapse="isCollapse"
                    @open="handleOpen"
                    @close="handleClose"
                    :default-active="activeMenu"
                    @select="handleMenuSelect"
                >
                    <el-menu-item index="dashboard">
                        <el-icon><icon-menu /></el-icon>
                        <template #title>Dashboard</template>
                    </el-menu-item>
                    
                    <!-- Admin: Hanya Sales (All Sales) -->
                    <el-menu-item v-if="isAdmin" index="sales.all">
                        <el-icon><ShoppingCart /></el-icon>
                        <template #title>Sales</template>
                    </el-menu-item>
                    
                    <!-- Cashier: Sub-menu dengan All Sales dan My Sales -->
                    <el-sub-menu v-if="isCashier" index="sales">
                        <template #title>
                            <el-icon><ShoppingCart /></el-icon>
                            <span>Sales</span>
                        </template>
                        <el-menu-item-group>
                            <el-menu-item index="sales.all">All Sales</el-menu-item>
                            <el-menu-item index="sales.my">My Sales</el-menu-item>
                        </el-menu-item-group>
                    </el-sub-menu>
                    
                    <el-menu-item v-if="isAdmin" index="products.index">
                        <el-icon><Box /></el-icon>
                        <template #title>Products</template>
                    </el-menu-item>
                    
                    <el-menu-item v-if="isAdmin" index="reports.admin">
                        <el-icon><Document /></el-icon>
                        <template #title>Analytics & Reports</template>
                    </el-menu-item>
                    
                    <el-menu-item v-if="isCashier" index="reports.index">
                        <el-icon><Document /></el-icon>
                        <template #title>My Daily Report</template>
                    </el-menu-item>
                    
                    <el-menu-item v-if="isAdmin" index="setting.index">
                        <el-icon><setting /></el-icon>
                        <template #title>User Management</template>
                    </el-menu-item>
                </el-menu>
                <div class="p-2 flex justify-center">
                    <el-button 
                        @click="toggleSidebar"
                        :type="isCollapse ? 'primary' : 'default'"
                        size="small"
                        :class="{
                            'toggle-sidebar-btn': true,
                            'collapsed': isCollapse,
                            'expanded': !isCollapse,
                            'w-8 h-8': isCollapse,
                            'w-full': !isCollapse
                        }"
                        :circle="isCollapse"
                        :title="isCollapse ? 'Expand Sidebar' : 'Collapse Sidebar'"
                    >
                        <template v-if="isCollapse">
                            <el-icon size="16">
                                <Expand />
                            </el-icon>
                        </template>
                        <template v-else>
                            <el-icon size="16">
                                <Fold />
                            </el-icon>
                            <span class="ml-2">Collapse</span>
                        </template>
                    </el-button>
                </div>
            </el-aside>
            <container
                id="main-content"
                class="w-full flex flex-col border h-screen overflow-y-auto"
            >
                <main class="overflow-y-auto p-0">
                    <header>
                        <nav class="p-2 py-4 border-b border-gray-100 bg-white">
                            <div class="flex justify-end">
                                <div
                                    class="hidden sm:ms-6 sm:flex sm:items-center"
                                >
                                    <div class="relative ms-3">
                                        <el-dropdown placement="bottom-start" class="mr-3">
                                            <el-button circle class="bg-transparent">
                                                <el-avatar
                                                    src="https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png"
                                                />
                                            </el-button>
                                            <template #dropdown>
                                                <el-dropdown-menu>
                                                    <el-dropdown-item v-if="isAdmin">
                                                        <Link
                                                            :href="route('profile.edit')"
                                                            class="text-gray-700"
                                                        >Profile</Link>
                                                    </el-dropdown-item>
                                                    <el-dropdown-item>
                                                        <Link
                                                            :href="route('logout')"
                                                            method="post"
                                                            as="button"
                                                            class="text-gray-700"
                                                        >Log Out</Link>
                                                    </el-dropdown-item>
                                                </el-dropdown-menu>
                                            </template>
                                        </el-dropdown>
                                    </div>
                                </div>
                            </div>
                        </nav>
                    </header>
                    <div class="p-6 bg-gray-100">
                        <div class="bg-white rounded-lg p-4">
                            <slot />
                        </div>
                    </div>
                </main>
            </container>
        </el-container>
    </div>
</template>

<style scoped>
.el-menu-vertical-demo:not(.el-menu--collapse) {
    width: 200px;
    min-height: 400px;
}
el-aside {
    position: sticky;
    top: 0;
    height: 100vh;
    z-index: 10;
}
#main-content {
    height: 100vh;
    overflow-y: auto;
}
.fade-dashboard-enter-active,
.fade-dashboard-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}
.fade-dashboard-enter-from,
.fade-dashboard-leave-to {
    opacity: 0;
    transform: translateX(10px);
}
.fade-dashboard-enter-to,
.fade-dashboard-leave-from {
    opacity: 1;
    transform: translateX(0);
}

/* Custom styling untuk toggle button */
.toggle-sidebar-btn {
    transition: all 0.3s ease;
}

.toggle-sidebar-btn.collapsed {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-sidebar-btn.expanded {
    width: 100%;
    justify-content: flex-start;
}
</style>
