<template>
    <AdminLayout
        title="Dashboard"
        pageTitle="Dashboard"
        currentPage="dashboard"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Administrator Dashboard</h2>
                    <span class="gov-sy"
                        >School Year {{ currentSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-section">
                <h3 class="gov-section-title">School Summary</h3>
                <div class="gov-stat-row">
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Total Students</div>
                        <div class="gov-stat-value">
                            {{ stats.totalStudents }}
                        </div>
                    </div>
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Total Teachers</div>
                        <div class="gov-stat-value">
                            {{ stats.totalTeachers }}
                        </div>
                    </div>
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Enrolled Students</div>
                        <div class="gov-stat-value">
                            {{ stats.totalEnrolled }}
                        </div>
                    </div>
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Pending Admissions</div>
                        <div class="gov-stat-value">
                            {{ stats.pendingAdmissions }}
                        </div>
                    </div>
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Pending Enrollments</div>
                        <div class="gov-stat-value">
                            {{ stats.pendingEnrollments }}
                        </div>
                    </div>
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Total Subjects</div>
                        <div class="gov-stat-value">
                            {{ stats.totalSubjects }}
                        </div>
                    </div>
                    <div class="gov-stat-box">
                        <div class="gov-stat-label">Active Sections</div>
                        <div class="gov-stat-value">
                            {{ stats.totalSections }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="gov-two-col">
                <div class="gov-panel">
                    <div class="gov-panel-bar">Enrollment by Year Level</div>
                    <table class="gov-table">
                        <thead>
                            <tr>
                                <th>Year Level</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="level in enrollmentByYearLevel"
                                :key="level.id"
                            >
                                <td>{{ level.name }}</td>
                                <td>{{ level.enrollments_count }}</td>
                            </tr>
                            <tr v-if="enrollmentByYearLevel.length === 0">
                                <td colspan="2">No year level data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="gov-panel">
                    <div class="gov-panel-bar gov-panel-bar-split">
                        <span>Recent Enrollments</span>
                        <Link
                            :href="route('admin.enrollments')"
                            class="gov-bar-link"
                            >View All</Link
                        >
                    </div>
                    <table class="gov-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Year Level</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="enrollment in recentEnrollments.slice(
                                    0,
                                    5,
                                )"
                                :key="enrollment.id"
                            >
                                <td>
                                    {{ enrollment.user?.last_name }},
                                    {{ enrollment.user?.first_name }}
                                </td>
                                <td>{{ enrollment.year_level?.name }}</td>
                                <td>{{ formatDate(enrollment.created_at) }}</td>
                                <td>{{ enrollment.status }}</td>
                            </tr>
                            <tr v-if="recentEnrollments.length === 0">
                                <td colspan="4">No recent enrollments.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="gov-section">
                <h3 class="gov-section-title">Go to</h3>
                <div class="gov-link-row">
                    <Link :href="route('admin.students')" class="gov-link-box"
                        >Students</Link
                    >
                    <Link :href="route('admin.admissions')" class="gov-link-box"
                        >Admissions</Link
                    >
                    <Link :href="route('admin.teachers')" class="gov-link-box"
                        >Teachers</Link
                    >
                    <Link
                        :href="route('admin.enrollments')"
                        class="gov-link-box"
                        >Enrollments</Link
                    >
                    <Link :href="route('admin.sections')" class="gov-link-box"
                        >Sections</Link
                    >
                    <Link
                        :href="route('admin.school-forms')"
                        class="gov-link-box"
                        >School Forms</Link
                    >
                    <Link :href="route('admin.settings')" class="gov-link-box"
                        >Settings</Link
                    >
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps({
    user: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({
            totalStudents: 0,
            totalTeachers: 0,
            totalEnrolled: 0,
            pendingEnrollments: 0,
            pendingAdmissions: 0,
            totalSubjects: 0,
            totalSections: 0,
        }),
    },
    recentEnrollments: {
        type: Array,
        default: () => [],
    },
    enrollmentByYearLevel: {
        type: Array,
        default: () => [],
    },
    currentSchoolYear: {
        type: String,
        default: "",
    },
});

const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};
</script>

<style scoped>
.gov-pagehead {
    margin-bottom: 1.35rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #cfcfcf;
}

.gov-kicker {
    margin: 0 0 0.25rem;
    font-size: 0.78rem;
    color: #555;
}

.gov-pagehead-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
}

.gov-pagehead h2 {
    margin: 0;
    color: #003366;
    font-size: 1.2rem;
}

.gov-sy {
    font-size: 0.85rem;
    color: #333;
    font-weight: 600;
}

.gov-section {
    margin-bottom: 1.75rem;
}

.gov-section-title {
    margin: 0 0 0.65rem;
    color: #003366;
    font-size: 0.95rem;
    font-weight: 700;
    padding-bottom: 0.3rem;
    border-bottom: 2px solid #c9a227;
}

.gov-stat-row {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 0.5rem;
}

.gov-stat-box {
    background: #fff;
    border: 1px solid #c5c5c5;
    min-width: 0;
}

.gov-stat-label {
    background: #003366;
    color: #fff;
    padding: 0.35rem 0.4rem;
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.25;
    text-align: center;
}

.gov-stat-value {
    padding: 0.65rem 0.4rem 0.75rem;
    font-size: 1.35rem;
    font-weight: 700;
    color: #003366;
    text-align: center;
}

.gov-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
    margin-bottom: 1.75rem;
}

.gov-panel {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.gov-panel-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.gov-panel-bar-split {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.gov-bar-link {
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: underline;
}

.gov-table {
    width: 100%;
    border-collapse: collapse;
}

.gov-table th,
.gov-table td {
    padding: 0.5rem 0.75rem;
    border-top: 1px solid #e0e0e0;
    font-size: 0.88rem;
    text-align: left;
}

.gov-table th {
    background: #e8eef4;
    color: #003366;
    font-weight: 700;
    border-top: none;
}

.gov-table td {
    color: #222;
}

.gov-link-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.gov-link-box {
    background: #fff;
    border: 1px solid #c5c5c5;
    color: #003366;
    padding: 0.9rem 0.75rem;
    font-size: 0.92rem;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
}

.gov-link-box:hover {
    background: #003366;
    color: #fff;
    border-color: #003366;
}

@media (max-width: 1100px) {
    .gov-stat-row {
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.4rem;
    }

    .gov-stat-label {
        font-size: 0.62rem;
        padding: 0.3rem 0.25rem;
    }
}

@media (max-width: 900px) {
    .gov-two-col {
        grid-template-columns: 1fr;
    }

    .gov-link-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .gov-stat-row,
    .gov-link-row {
        grid-template-columns: 1fr;
    }

    .gov-pagehead-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
