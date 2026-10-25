<template>
    <Head :title="title + ' - TNHS Admin'" />
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'mobile-open': isMobileMenuOpen }">
            <div class="sidebar-header">
                <img :src="logo" alt="TNHS Logo" class="logo" />
                <div class="school-info">
                    <h2>TNHS</h2>
                    <p>Admin Portal</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-group">
                    <span class="nav-group-label">Main</span>
                    <Link
                        :href="route('admin.dashboard')"
                        class="nav-item"
                        :class="{ active: currentPage === 'dashboard' }"
                    >
                        <LayoutDashboard class="nav-icon" :size="20" />
                        <span class="nav-text">Dashboard</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Student Records</span>
                    <Link
                        :href="route('admin.students')"
                        class="nav-item"
                        :class="{ active: currentPage === 'students' }"
                    >
                        <Users class="nav-icon" :size="20" />
                        <span class="nav-text">Students</span>
                    </Link>
                    <Link
                        :href="route('admin.admissions')"
                        class="nav-item"
                        :class="{ active: currentPage === 'admissions' }"
                    >
                        <UserCheck class="nav-icon" :size="20" />
                        <span class="nav-text">Admissions</span>
                    </Link>
                    <Link
                        :href="route('admin.enrollments')"
                        class="nav-item"
                        :class="{ active: currentPage === 'enrollments' }"
                    >
                        <ClipboardList class="nav-icon" :size="20" />
                        <span class="nav-text">Enrollments</span>
                    </Link>
                    <Link
                        :href="route('admin.requirements')"
                        class="nav-item"
                        :class="{ active: currentPage === 'requirements' }"
                    >
                        <FileCheck class="nav-icon" :size="20" />
                        <span class="nav-text">Student Requirements</span>
                    </Link>
                    <Link
                        :href="route('admin.school-forms')"
                        class="nav-item"
                        :class="{ active: currentPage === 'school-forms' }"
                    >
                        <FileText class="nav-icon" :size="20" />
                        <span class="nav-text">School Forms</span>
                    </Link>
                    <Link
                        :href="route('admin.enrollment-summary')"
                        class="nav-item"
                        :class="{ active: currentPage === 'enrollment-summary' }"
                    >
                        <BarChart3 class="nav-icon" :size="20" />
                        <span class="nav-text">Enrollment Summary</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Personnel</span>
                    <Link
                        :href="route('admin.teachers')"
                        class="nav-item"
                        :class="{ active: currentPage === 'teachers' }"
                    >
                        <GraduationCap class="nav-icon" :size="20" />
                        <span class="nav-text">Teachers</span>
                    </Link>
                    <Link
                        :href="route('admin.teacher-assignments')"
                        class="nav-item"
                        :class="{ active: currentPage === 'teacher-assignments' }"
                    >
                        <UserCog class="nav-icon" :size="20" />
                        <span class="nav-text">Teacher Assignments</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Academic Setup</span>
                    <Link
                        :href="route('admin.year-levels')"
                        class="nav-item"
                        :class="{ active: currentPage === 'year-levels' }"
                    >
                        <Building class="nav-icon" :size="20" />
                        <span class="nav-text">Year Levels</span>
                    </Link>
                    <Link
                        :href="route('admin.strands')"
                        class="nav-item"
                        :class="{ active: currentPage === 'strands' }"
                    >
                        <Waypoints class="nav-icon" :size="20" />
                        <span class="nav-text">Academic Tracks</span>
                    </Link>
                    <Link
                        :href="route('admin.sections')"
                        class="nav-item"
                        :class="{ active: currentPage === 'sections' }"
                    >
                        <Layers class="nav-icon" :size="20" />
                        <span class="nav-text">Sections</span>
                    </Link>
                    <Link
                        :href="route('admin.subjects')"
                        class="nav-item"
                        :class="{ active: currentPage === 'subjects' }"
                    >
                        <BookOpen class="nav-icon" :size="20" />
                        <span class="nav-text">Subjects</span>
                    </Link>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">System</span>
                    <Link
                        :href="route('admin.accounts')"
                        class="nav-item"
                        :class="{ active: currentPage === 'accounts' }"
                    >
                        <Shield class="nav-icon" :size="20" />
                        <span class="nav-text">Accounts</span>
                    </Link>
                    <Link
                        :href="route('admin.settings')"
                        class="nav-item"
                        :class="{ active: currentPage === 'settings' }"
                    >
                        <Settings class="nav-icon" :size="20" />
                        <span class="nav-text">Settings</span>
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
                    <TeacherAssistant role="administrator" :page-title="pageTitle" />
                    <TeacherAccountMenu :user="user" role-label="Administrator" />
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
    GraduationCap,
    ClipboardList,
    BookOpen,
    Layers,
    Building,
    LogOut,
    Menu,
    UserCog,
    UserCheck,
    FileCheck,
    FileText,
    BarChart3,
    Settings,
    Shield,
    Waypoints,
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
            talkBackGuide(props.currentPage, props.pageTitle, "administrator"),
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
