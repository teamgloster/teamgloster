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
                </div>
            </nav>

            <div class="sidebar-footer">
                <button @click="logout" class="logout-btn">
                    <LogOut class="nav-icon" :size="20" />
                    <span class="nav-text">Logout</span>
                </button>
            </div>
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

<style scoped>
.dashboard-layout {
    display: flex;
    min-height: 100vh;
    background: #ececec;
}

.sidebar {
    width: 230px;
    background: #003366;
    color: white;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 100;
    border-right: 1px solid #002244;
}

.sidebar-header {
    padding: 0.9rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 3px solid #c9a227;
}

.logo {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}

.school-info h2 {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0;
    color: #fff;
}

.school-info p {
    font-size: 0.75rem;
    opacity: 0.85;
    margin: 0.15rem 0 0 0;
}

.sidebar-nav {
    flex: 1;
    padding: 0.35rem 0 0.75rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.nav-group {
    padding: 0.15rem 0 0.25rem;
}

.nav-group + .nav-group {
    margin-top: 0.2rem;
    border-top: 1px solid #1a4a73;
    padding-top: 0.35rem;
}

.nav-group-label {
    display: block;
    padding: 0.4rem 1rem 0.2rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #c9a227;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.5rem 1rem;
    color: #e8eef4;
    text-decoration: none;
    font-weight: 500;
    border-left: 4px solid transparent;
}

.nav-item:hover {
    background: #00264d;
    color: white;
}

.nav-item.active {
    background: #002244;
    color: white;
    border-left-color: #c9a227;
}

.nav-icon {
    flex-shrink: 0;
}

.nav-text {
    font-size: 0.86rem;
}

.sidebar-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #00264d;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0;
    width: 100%;
    border: none;
    background: transparent;
    color: #e8eef4;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.86rem;
}

.logout-btn:hover {
    color: #fff;
    text-decoration: underline;
}

.main-wrapper {
    flex: 1;
    margin-left: 230px;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.dashboard-header {
    background: white;
    padding: 0.7rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #c9a227;
    position: sticky;
    top: 0;
    z-index: 50;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: #003366;
    cursor: pointer;
    padding: 0.5rem;
}

.header-content h1 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #003366;
    margin: 0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.user-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.user-role {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #666;
}

.user-name {
    font-weight: 600;
    color: #003366;
    font-size: 0.88rem;
}

.user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 0;
    background: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.78rem;
    overflow: hidden;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dashboard-main {
    flex: 1;
    padding: 1.25rem 1.5rem 2rem;
}

.mobile-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 99;
}

@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.2s ease;
    }

    .sidebar.mobile-open {
        transform: translateX(0);
    }

    .main-wrapper {
        margin-left: 0;
    }

    .mobile-menu-btn {
        display: block;
    }

    .mobile-overlay.active {
        display: block;
    }
}

@media (max-width: 768px) {
    .dashboard-main {
        padding: 1rem;
    }

    .dashboard-header {
        padding: 0.7rem 1rem;
    }

    .user-name,
    .user-role {
        display: none;
    }
}
</style>
