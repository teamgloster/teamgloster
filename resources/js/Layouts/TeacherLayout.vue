<template>
    <Head :title="title + ' - TNHS Teacher'" />
    <div class="dashboard-layout">
        <aside class="sidebar" :class="{ 'mobile-open': isMobileMenuOpen }">
            <div class="sidebar-header">
                <img :src="logo" alt="TNHS Logo" class="logo" />
                <div class="school-info">
                    <h2>TNHS</h2>
                    <p>Teacher Portal</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-group">
                    <span class="nav-group-label">Main</span>
                    <Link
                        href="/dashboard/teacher"
                        class="nav-item"
                        :class="{ active: currentPage === 'dashboard' }"
                    >
                        <LayoutDashboard class="nav-icon" :size="20" />
                        <span class="nav-text">Dashboard</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Classes</span>
                    <Link
                        href="/dashboard/teacher?nav=advisory"
                        class="nav-item"
                        :class="{ active: currentPage === 'advisory' }"
                    >
                        <Users class="nav-icon" :size="20" />
                        <span class="nav-text">Advisory Class</span>
                    </Link>
                    <Link
                        href="/dashboard/teacher?nav=subjects"
                        class="nav-item"
                        :class="{ active: currentPage === 'subjects' }"
                    >
                        <BookOpen class="nav-icon" :size="20" />
                        <span class="nav-text">Subjects Handled</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Records</span>
                    <Link
                        href="/dashboard/teacher?nav=grades"
                        class="nav-item"
                        :class="{ active: currentPage === 'grades' }"
                    >
                        <ClipboardList class="nav-icon" :size="20" />
                        <span class="nav-text">Student Grades</span>
                    </Link>
                    <Link
                        href="/dashboard/teacher?nav=school-forms"
                        class="nav-item"
                        :class="{ active: currentPage === 'school-forms' }"
                    >
                        <FileText class="nav-icon" :size="20" />
                        <span class="nav-text">School Forms</span>
                    </Link>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button @click="logout" class="logout-btn">
                    <LogOut class="nav-icon" :size="20" />
                    <span class="nav-text">Logout</span>
                </button>
            </div>
        </aside>

        <div
            class="mobile-overlay"
            :class="{ active: isMobileMenuOpen }"
            @click="isMobileMenuOpen = false"
        ></div>

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
                <div class="user-info">
                    <div class="user-meta">
                        <span class="user-role">Teacher</span>
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
    BookOpen,
    ClipboardList,
    FileText,
    LogOut,
    Menu,
} from "lucide-vue-next";

const props = defineProps({
    title: { type: String, default: "Dashboard" },
    pageTitle: { type: String, default: "Dashboard" },
    currentPage: { type: String, default: "dashboard" },
    user: { type: Object, required: true },
});

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";
const isMobileMenuOpen = ref(false);

const profilePhotoUrl = computed(() => props.user?.profile_photo_url || null);
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
