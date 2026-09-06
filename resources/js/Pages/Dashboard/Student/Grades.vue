<template>
    <StudentLayout
        title="Grades"
        pageTitle="Grades"
        currentPage="grades"
        :user="user"
    >
        <div class="content-section">
            <div class="grades-container">
                <!-- Grades Banner -->
                <div class="grades-banner">
                    <div class="banner-content">
                        <div class="banner-icon"><BarChart3 :size="40" /></div>
                        <div class="banner-text">
                            <h2>Academic Grades</h2>
                            <p>
                                These ratings appear on both your SF9 report
                                card and your SP-10 / SF10 permanent record.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grades Content -->
                <div v-if="grades && grades.length > 0" class="grades-content">
                    <!-- Stats Summary -->
                    <div class="grades-stats">
                        <div class="stat-card total">
                            <div class="stat-icon">
                                <BookOpen :size="24" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Total Subjects</div>
                                <div class="stat-value">
                                    {{ grades.length }}
                                </div>
                            </div>
                        </div>
                        <div class="stat-card graded">
                            <div class="stat-icon">
                                <CheckCircle2 :size="24" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Graded Subjects</div>
                                <div class="stat-value">
                                    {{ gradedSubjectsCount }}
                                </div>
                            </div>
                        </div>
                        <div class="stat-card pending">
                            <div class="stat-icon">
                                <Clock :size="24" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">Pending Grades</div>
                                <div class="stat-value">
                                    {{ pendingGradesCount }}
                                </div>
                            </div>
                        </div>
                        <div class="stat-card gwa">
                            <div class="stat-icon">
                                <Award :size="24" />
                            </div>
                            <div class="stat-content">
                                <div class="stat-label">General Average</div>
                                <div class="stat-value">{{ computedGWA }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="grades-table-wrapper">
                        <div class="table-header">
                            <div class="table-header-row">
                                <div>
                                    <h3>Academic Performance</h3>
                                    <p>
                                        Your grades for the current school year
                                    </p>
                                </div>
                                <div class="sf-actions">
                                    <Link href="/student/sf9" class="sf-link">
                                        Generate SF9
                                    </Link>
                                    <Link href="/student/sf10" class="sf-link">
                                        Generate SP-10
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>1st Term</th>
                                    <th>2nd Term</th>
                                    <th>3rd Term</th>
                                    <th>Final Grade</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="grade in grades"
                                    :key="grade.subject_id"
                                    :class="{
                                        'pending-grade': !grade.final_grade,
                                    }"
                                >
                                    <td class="subject-cell">
                                        <div class="subject-name">
                                            {{ grade.subject_name }}
                                        </div>
                                        <div class="subject-code">
                                            {{ grade.subject_code }}
                                        </div>
                                    </td>
                                    <td>{{ grade.term_1 || "-" }}</td>
                                    <td>{{ grade.term_2 || "-" }}</td>
                                    <td>{{ grade.term_3 || "-" }}</td>
                                    <td class="final-grade">
                                        {{ grade.final_grade || "-" }}
                                    </td>
                                    <td>
                                        <span
                                            class="remarks-badge"
                                            :class="
                                                getRemarkClass(
                                                    grade.final_grade,
                                                )
                                            "
                                        >
                                            {{ getRemarks(grade.final_grade) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Grades Message -->
                <div v-else class="no-grades-card">
                    <div class="empty-icon"><BarChart3 :size="48" /></div>
                    <h3>No Subjects Available</h3>
                    <p>
                        You need to be enrolled in the current school year to
                        view your subjects and grades.
                    </p>
                    <div class="sf-actions empty-sf-actions">
                        <Link href="/student/sf9" class="sf-link"
                            >Generate SF9</Link
                        >
                        <Link href="/student/sf10" class="sf-link"
                            >Generate SP-10</Link
                        >
                    </div>
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
    BarChart3,
    BookOpen,
    CheckCircle2,
    Clock,
    Award,
} from "lucide-vue-next";

const props = defineProps({
    user: { type: Object, required: true },
    grades: { type: Array, default: () => [] },
    enrollment: { type: Object, default: null },
});

const gradedSubjectsCount = computed(() => {
    return props.grades.filter((g) => g.final_grade !== null).length;
});

const pendingGradesCount = computed(() => {
    return props.grades.filter((g) => g.final_grade === null).length;
});

const getRemarks = (grade) => {
    if (!grade) return "-";
    return grade >= 75 ? "Passed" : "Failed";
};

const getRemarkClass = (grade) => {
    if (!grade) return "";
    return grade >= 75 ? "passed" : "failed";
};

const computedGWA = computed(() => {
    if (!props.grades || props.grades.length === 0) return "-";
    const validGrades = props.grades.filter((g) => g.final_grade);
    if (validGrades.length === 0) return "-";
    const sum = validGrades.reduce(
        (acc, g) => acc + parseFloat(g.final_grade),
        0,
    );
    return (sum / validGrades.length).toFixed(2);
});
</script>

<style scoped>
.grades-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.grades-banner {
    background: #003366;
    color: white;
    padding: 0.85rem 1rem;
    border-bottom: 3px solid #c9a227;
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
    font-weight: 700;
}

.banner-text p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.9;
}

.grades-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.grades-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.stat-card {
    background: white;
    border: 1px solid #c5c5c5;
    display: flex;
    flex-direction: column;
}

.stat-card.total,
.stat-card.graded,
.stat-card.pending,
.stat-card.gwa {
    border-top: none;
}

.stat-icon {
    display: none;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    background: #003366;
    color: #fff;
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: none;
    letter-spacing: 0;
}

.stat-value {
    padding: 0.75rem 0.6rem 0.85rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #003366;
    text-align: center;
}

.grades-table-wrapper {
    background: white;
    border: 1px solid #c5c5c5;
    border-top: 3px solid #c9a227;
    overflow-x: auto;
}

.table-header {
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid #c5c5c5;
    background: #e8eef4;
}

.table-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.sf-actions {
    display: flex;
    gap: 0.4rem;
}

.sf-link {
    border: 1px solid #003366;
    background: #fff;
    color: #003366;
    padding: 0.3rem 0.55rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
}

.empty-sf-actions {
    justify-content: center;
    margin-top: 0.75rem;
}

.table-header h3 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #003366;
}

.table-header p {
    margin: 0.15rem 0 0;
    color: #555;
    font-size: 0.8rem;
}

.grades-table {
    width: 100%;
    border-collapse: collapse;
}

.grades-table th,
.grades-table td {
    padding: 0.5rem 0.7rem;
    text-align: center;
}

.grades-table th {
    background: #e8eef4;
    font-size: 0.75rem;
    font-weight: 700;
    color: #003366;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-bottom: 1px solid #c5c5c5;
}

.grades-table td {
    font-size: 0.88rem;
    color: #222;
    border-bottom: 1px solid #ddd;
}

.grades-table tbody tr:hover {
    background: #f4f7fb;
}

.grades-table tbody tr.pending-grade td {
    color: #777;
}

.subject-cell {
    text-align: left;
}

.subject-name {
    font-weight: 600;
    color: #222;
}

.subject-code {
    font-size: 0.78rem;
    color: #555;
}

.final-grade {
    font-weight: 700;
    color: #003366;
    font-size: 0.95rem;
}

.remarks-badge {
    display: inline-block;
    padding: 0.15rem 0.45rem;
    border: 1px solid #c5c5c5;
    background: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    color: #003366;
}

.remarks-badge.passed {
    color: #1f6b3a;
    border-color: #1f6b3a;
}

.remarks-badge.failed {
    color: #9b1c1c;
    border-color: #9b1c1c;
}

.no-grades-card {
    background: white;
    border: 1px solid #c5c5c5;
    padding: 1.25rem 1rem;
}

.empty-icon {
    display: none;
}

.no-grades-card h3 {
    margin: 0 0 0.35rem 0;
    color: #003366;
    font-size: 1rem;
}

.no-grades-card p {
    margin: 0;
    color: #555;
    font-size: 0.88rem;
}

@media (max-width: 768px) {
    .grades-stats {
        grid-template-columns: 1fr 1fr;
    }

    .grades-table {
        min-width: 700px;
    }
}

@media (max-width: 640px) {
    .grades-stats {
        grid-template-columns: 1fr;
    }
}
</style>
