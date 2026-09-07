<template>
    <Head :title="title + ' - TNHS Registrar'" />
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'mobile-open': isMobileMenuOpen }">
            <div class="sidebar-header">
                <img :src="logo" alt="TNHS Logo" class="logo" />
                <div class="school-info">
                    <h2>TNHS</h2>
                    <p>Registrar Portal</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-group">
                    <span class="nav-group-label">Main</span>
                    <Link
                        :href="route('registrar.dashboard')"
                        class="nav-item"
                        :class="{ active: currentPage === 'dashboard' }"
                    >
                        <LayoutDashboard class="nav-icon" :size="20" />
                        <span class="nav-text">Dashboard</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Academic Records</span>
                    <Link
                        :href="route('registrar.year-levels')"
                        class="nav-item"
                        :class="{ active: currentPage === 'year-levels' }"
                    >
                        <Building class="nav-icon" :size="20" />
                        <span class="nav-text">Year Levels</span>
                    </Link>
                    <Link
                        :href="route('registrar.sections')"
                        class="nav-item"
                        :class="{ active: currentPage === 'sections' }"
                    >
                        <Layers class="nav-icon" :size="20" />
                        <span class="nav-text">Sections</span>
                    </Link>
                    <Link
                        :href="route('registrar.grades')"
                        class="nav-item"
                        :class="{ active: currentPage === 'grades' }"
                    >
                        <ClipboardList class="nav-icon" :size="20" />
                        <span class="nav-text">Grading Records</span>
                    </Link>
                    <Link
                        :href="route('registrar.students')"
                        class="nav-item"
                        :class="{ active: currentPage === 'students' }"
                    >
                        <Users class="nav-icon" :size="20" />
                        <span class="nav-text">Students</span>
                    </Link>
                    <Link
                        :href="route('registrar.permanent-records')"
                        class="nav-item"
                        :class="{ active: currentPage === 'permanent-records' }"
                    >
                        <FolderSearch class="nav-icon" :size="20" />
                        <span class="nav-text">SP-10 Records</span>
                    </Link>
                    <Link
                        :href="route('registrar.enrollment-summary')"
                        class="nav-item"
                        :class="{ active: currentPage === 'enrollment-summary' }"
                    >
                        <BarChart3 class="nav-icon" :size="20" />
                        <span class="nav-text">Enrollment Summary</span>
                    </Link>
                </div>
            </nav>
        </aside>

        <!-- Mobile Overlay -->
        <div
            class="mobile-overlay"
            :class="{ active: isMobileMenuOpen }"
            @click="isMobileMenuOpen = false"
        ></div>

        <!-- Main Content -->
        <div class="main-wrapper">
            <header class="dashboard-header">
                <div class="header-left">
                    <button
                        class="mobile-menu-btn"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                    >
                        <Menu :size="24" />
                    </button>
                    <div class="header-content">
                        <h1>{{ pageTitle }}</h1>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-meta">
                            <span class="user-role">Registrar</span>
                            <span class="user-name"
                                >{{ user.first_name }} {{ user.last_name }}</span
                            >
                        </div>
                        <div class="user-avatar">
                            <img
                                v-if="profilePhotoUrl"
                                :src="profilePhotoUrl"
                                alt="Profile"
                                class="avatar-img"
                            />
                            <span v-else>{{ userInitials }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="header-logout-btn"
                        @click="logout"
                    >
                        <LogOut :size="16" />
                        <span class="logout-text">Logout</span>
                    </button>
                </div>
            </header>

            <main class="dashboard-main">
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { router, Head, Link } from "@inertiajs/vue3";
import {
    LayoutDashboard,
    Users,
    ClipboardList,
    Layers,
    Building,
    FolderSearch,
    BarChart3,
    LogOut,
    Menu,
} from "lucide-vue-next";

const props = defineProps({
    title: {
        type: String,
        default: "Dashboard",
    },
    pageTitle: {
        type: String,
        default: "Dashboard",
    },
    currentPage: {
        type: String,
        default: "dashboard",
    },
    user: {
        type: Object,
        required: true,
    },
});

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";
const isMobileMenuOpen = ref(false);

const profilePhotoUrl = computed(() => {
    return props.user?.profile_photo_url || null;
});

const userInitials = computed(() => {
    if (!props.user) return "";
    return (
        (props.user.first_name?.charAt(0) || "") +
        (props.user.last_name?.charAt(0) || "")
    ).toUpperCase();
});

const logout = () => {
    router.post("/logout");
};
</script>

<style>
@import "@/Styles/portal-layout.css";
</style>
