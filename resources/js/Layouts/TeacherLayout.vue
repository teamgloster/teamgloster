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
                        :href="route('teacher.attendance')"
                        class="nav-item"
                        :class="{ active: currentPage === 'attendance' }"
                    >
                        <ClipboardCheck class="nav-icon" :size="20" />
                        <span class="nav-text">Attendance</span>
                    </Link>
                    <Link
                        :href="route('teacher.school-forms')"
                        class="nav-item"
                        :class="{ active: currentPage === 'school-forms' }"
                    >
                        <FileText class="nav-icon" :size="20" />
                        <span class="nav-text">School Forms</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Account</span>
                    <Link
                        :href="route('teacher.password')"
                        class="nav-item"
                        :class="{ active: currentPage === 'password' }"
                    >
                        <KeyRound class="nav-icon" :size="20" />
                        <span class="nav-text">Change Password</span>
                    </Link>
                </div>
            </nav>
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
                    <TeacherAssistant role="teacher" :page-title="pageTitle" />
                    <TeacherAccountMenu :user="user" />
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
    BookOpen,
    ClipboardList,
    ClipboardCheck,
    FileText,
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
    title: { type: String, default: "Dashboard" },
    pageTitle: { type: String, default: "Dashboard" },
    currentPage: { type: String, default: "dashboard" },
    user: { type: Object, required: true },
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
            talkBackGuide(props.currentPage, props.pageTitle),
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
