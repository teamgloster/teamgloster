<template>
    <RegistrarLayout
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
                    <h2>Registrar Dashboard</h2>
                    <span class="gov-sy"
                        >School Year {{ currentSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Year Levels</div>
                    <div class="gov-stat-value">{{ stats.yearLevels }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Sections</div>
                    <div class="gov-stat-value">{{ stats.sections }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrolled Students</div>
                    <div class="gov-stat-value">{{ stats.enrolled }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Grade Records</div>
                    <div class="gov-stat-value">{{ stats.gradeRecords }}</div>
                </div>
            </div>

            <div class="gov-panel">
                <div class="gov-panel-bar">Enrollment by Year Level</div>
                <table class="gov-table">
                    <thead>
                        <tr>
                            <th>Year Level</th>
                            <th>Sections</th>
                            <th>Enrolled Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="level in enrollmentByYearLevel"
                            :key="level.id"
                        >
                            <td>{{ level.name }}</td>
                            <td>{{ level.sections_count }}</td>
                            <td>{{ level.enrollments_count }}</td>
                        </tr>
                        <tr v-if="enrollmentByYearLevel.length === 0">
                            <td colspan="3">No year level data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </RegistrarLayout>
</template>

<script setup>
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

defineProps({
    user: { type: Object, required: true },
    stats: { type: Object, required: true },
    enrollmentByYearLevel: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});
</script>

<style scoped>
@import "@/Styles/admin-common.css";

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
</style>
