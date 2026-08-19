<template>
    <StudentLayout
        title="Subjects"
        pageTitle="Enrolled Subjects"
        currentPage="subjects"
        :user="user"
    >
        <div class="content-section">
            <div class="subjects-container">
                <!-- Subjects Banner -->
                <div class="subjects-banner">
                    <div class="banner-content">
                        <div class="banner-icon"><BookOpen :size="40" /></div>
                        <div class="banner-text">
                            <h2>Enrolled Subjects</h2>
                            <p>
                                View your subjects for the current school year
                            </p>
                        </div>
                    </div>
                    <div class="semester-badge" v-if="enrollment.current">
                        <Calendar :size="16" />
                        <span
                            >{{
                                enrollment.current.year_level?.name || "N/A"
                            }}
                            • S.Y. {{ enrollment.currentSchoolYear }}</span
                        >
                    </div>
                </div>

                <!-- Subjects Content -->
                <div
                    v-if="
                        enrollment.current &&
                        enrollment.current.status === 'enrolled'
                    "
                    class="subjects-content"
                >
                    <!-- Subject Stats -->
                    <div class="subject-stats">
                        <div class="stat-item">
                            <div class="stat-icon blue">
                                <BookOpen :size="22" />
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{
                                    subjects.length
                                }}</span>
                                <span class="stat-label">Total Subjects</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon green">
                                <BarChart3 :size="22" />
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ totalUnits }}</span>
                                <span class="stat-label">Total Units</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon purple">
                                <Clock :size="22" />
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">{{ totalHours }}</span>
                                <span class="stat-label">Hours/Week</span>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects List by Type -->
                    <div v-if="coreSubjects.length > 0" class="subject-group">
                        <h3 class="group-title">
                            <span class="title-badge core">Core</span> Core
                            Subjects
                        </h3>
                        <div class="subjects-grid">
                            <div
                                v-for="subject in coreSubjects"
                                :key="subject.id"
                                class="subject-card"
                            >
                                <div class="subject-header">
                                    <div class="subject-icon">
                                        <BookOpen :size="20" />
                                    </div>
                                    <div class="subject-info">
                                        <h4>{{ subject.name }}</h4>
                                        <span class="subject-code">{{
                                            subject.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="subject-details">
                                    <div class="detail">
                                        <span class="label">Units:</span>
                                        {{ subject.units }}
                                    </div>
                                    <div class="detail">
                                        <span class="label">Hours:</span>
                                        {{ subject.hours_per_week }}/week
                                    </div>
                                    <div
                                        v-if="subject.teacher_name"
                                        class="detail"
                                    >
                                        <span class="label">Teacher:</span>
                                        {{ subject.teacher_name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="specializedSubjects.length > 0"
                        class="subject-group"
                    >
                        <h3 class="group-title">
                            <span class="title-badge specialized"
                                >Specialized</span
                            >
                            Specialized Subjects
                        </h3>
                        <div class="subjects-grid">
                            <div
                                v-for="subject in specializedSubjects"
                                :key="subject.id"
                                class="subject-card"
                            >
                                <div class="subject-header">
                                    <div class="subject-icon specialized">
                                        <BookOpen :size="20" />
                                    </div>
                                    <div class="subject-info">
                                        <h4>{{ subject.name }}</h4>
                                        <span class="subject-code">{{
                                            subject.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="subject-details">
                                    <div class="detail">
                                        <span class="label">Units:</span>
                                        {{ subject.units }}
                                    </div>
                                    <div class="detail">
                                        <span class="label">Hours:</span>
                                        {{ subject.hours_per_week }}/week
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="appliedSubjects.length > 0"
                        class="subject-group"
                    >
                        <h3 class="group-title">
                            <span class="title-badge applied">Applied</span>
                            Applied Subjects
                        </h3>
                        <div class="subjects-grid">
                            <div
                                v-for="subject in appliedSubjects"
                                :key="subject.id"
                                class="subject-card"
                            >
                                <div class="subject-header">
                                    <div class="subject-icon applied">
                                        <BookOpen :size="20" />
                                    </div>
                                    <div class="subject-info">
                                        <h4>{{ subject.name }}</h4>
                                        <span class="subject-code">{{
                                            subject.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="subject-details">
                                    <div class="detail">
                                        <span class="label">Units:</span>
                                        {{ subject.units }}
                                    </div>
                                    <div class="detail">
                                        <span class="label">Hours:</span>
                                        {{ subject.hours_per_week }}/week
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Not Enrolled Message -->
                <div v-else class="not-enrolled-card">
                    <div class="empty-icon"><BookOpen :size="48" /></div>
                    <h3>No Subjects Available</h3>
                    <p>You need to be enrolled to view your subjects.</p>
                    <Link
                        :href="route('student.enrollment')"
                        class="enroll-btn"
                    >
                        <GraduationCap :size="18" /> Go to Enrollment
                    </Link>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import {
    BookOpen,
    BarChart3,
    Calendar,
    Clock,
    GraduationCap,
} from "lucide-vue-next";

const props = defineProps({
    user: { type: Object, required: true },
    enrollment: {
        type: Object,
        default: () => ({ current: null, currentSchoolYear: "" }),
    },
    subjects: { type: Array, default: () => [] },
});

const coreSubjects = computed(() =>
    props.subjects.filter((s) => s.type === "core"),
);
const specializedSubjects = computed(() =>
    props.subjects.filter((s) => s.type === "specialized"),
);
const appliedSubjects = computed(() =>
    props.subjects.filter((s) => s.type === "applied"),
);
const totalUnits = computed(() =>
    props.subjects.reduce((sum, s) => sum + (s.units || 0), 0),
);
const totalHours = computed(() =>
    props.subjects.reduce((sum, s) => sum + (s.hours_per_week || 0), 0),
);
</script>

<style scoped>
.subjects-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.subjects-banner {
    background: #003366;
    color: white;
    padding: 0.85rem 1rem;
    border-bottom: 3px solid #c9a227;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.banner-content {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.banner-icon {
    display: none;
}

.banner-text h2 {
    margin: 0 0 0.2rem 0;
    font-size: 1.15rem;
}

.banner-text p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.9;
}

.semester-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #fff;
    padding: 0.3rem 0.55rem;
    font-weight: 600;
    font-size: 0.8rem;
}

.subjects-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.subject-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.stat-item {
    background: white;
    border: 1px solid #c5c5c5;
}

.stat-icon {
    display: none;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-label {
    background: #003366;
    color: #fff;
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.stat-value {
    padding: 0.75rem 0.6rem 0.85rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #003366;
    text-align: center;
}

.subject-group {
    background: white;
    border: 1px solid #c5c5c5;
}

.group-title {
    margin: 0;
    background: #003366;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.45rem 0.75rem;
    border-bottom: 3px solid #c9a227;
}

.title-badge {
    display: none;
}

.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 0;
}

.subject-card {
    border: none;
    border-top: 1px solid #e0e0e0;
    padding: 0.75rem 0.85rem;
}

.subject-card:hover {
    background: #f4f7fb;
}

.subject-header {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.subject-icon,
.subject-icon.specialized,
.subject-icon.applied {
    display: none;
}

.subject-info h4 {
    margin: 0 0 0.15rem 0;
    font-size: 0.95rem;
    color: #003366;
}

.subject-code {
    font-size: 0.78rem;
    color: #555;
}

.subject-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.subject-details .detail {
    font-size: 0.85rem;
    color: #444;
}

.subject-details .label {
    font-weight: 600;
    color: #333;
}

.not-enrolled-card {
    background: white;
    border: 1px solid #c5c5c5;
    padding: 1.1rem 1rem;
}

.empty-icon {
    display: none;
}

.not-enrolled-card h3 {
    margin: 0 0 0.35rem 0;
    color: #003366;
}

.not-enrolled-card p {
    margin: 0 0 0.85rem 0;
    color: #555;
}

.enroll-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    background: #003366;
    color: white;
    text-decoration: none;
    font-weight: 600;
}

.enroll-btn:hover {
    background: #00264d;
}

@media (max-width: 768px) {
    .subjects-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .subject-stats {
        grid-template-columns: 1fr;
    }
}
</style>
