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

                <div class="nav-group">
                    <span class="nav-group-label">Account</span>
                    <Link
                        :href="route('registrar.password')"
                        class="nav-item"
                        :class="{ active: currentPage === 'password' }"
                    >
                        <KeyRound class="nav-icon" :size="20" />
                        <span class="nav-text">Change Password</span>
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
                        type="button"
                        class="mobile-menu-btn"
                        :aria-label="isMobileMenuOpen ? 'Close menu' : 'Open menu'"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                    >
                        <Menu :size="24" />
                    </button>
                    <div class="header-content">
                        <h1>{{ pageTitle }}</h1>
                    </div>
                </div>
                <div class="header-right">
                    <TeacherAssistant role="registrar" :page-title="pageTitle" />
                    <TeacherAccountMenu :user="user" role-label="Registrar" />
                    <button
                        type="button"
                        class="header-logout-btn"
                        aria-label="Log out"
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
import { ref, watch } from "vue";
import { router, Head, Link } from "@inertiajs/vue3";
import {
    LayoutDashboard,
    Users,
    ClipboardList,
    Layers,
    Building,
    FolderSearch,
    BarChart3,
    KeyRound,
    LogOut,
    Menu,
} from "lucide-vue-next";
import TeacherAccountMenu from "@/Components/TeacherAccountMenu.vue";
import TeacherAssistant from "@/Components/TeacherAssistant.vue";
import {
    talkBackGuide,
    useTalkBack,
    useTalkBackHost,
} from "@/composables/useTalkBack";

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
const { setPage } = useTalkBack();
useTalkBackHost();

watch(
    () => [props.pageTitle, props.currentPage],
    () => {
        setPage(
            props.pageTitle,
            talkBackGuide(props.currentPage, props.pageTitle, "registrar"),
        );
    },
    { immediate: true },
);

const logout = () => {
    router.post("/logout");
};
</script>

<style>
@import "@/Styles/portal-layout.css";
</style>
