<template>
    <StudentLayout
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
                    <h2>Student Dashboard</h2>
                    <span class="gov-sy"
                        >School Year
                        {{
                            enrollment?.currentSchoolYear || "—"
                        }}</span
                    >
                </div>
            </div>

            <div
                v-if="user.admission_status && user.admission_status !== 'approved'"
                class="gov-panel"
            >
                <div class="gov-panel-bar">Admission Status</div>
                <p v-if="user.admission_status === 'rejected'" class="admission-note">
                    Your admission application was rejected.
                    <span v-if="user.admission_remarks">
                        Reason: {{ user.admission_remarks }}
                    </span>
                    You cannot enroll until the registrar approves a new
                    application.
                </p>
                <p v-else class="admission-note">
                    Your admission is pending registrar review. You cannot
                    enroll until it is approved.
                </p>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrolled Subjects</div>
                    <div class="gov-stat-value">{{ subjects.length }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Previous GWA</div>
                    <div class="gov-stat-value">
                        {{ user.previous_gwa || "—" }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrollment Status</div>
                    <div class="gov-stat-value gov-stat-text">
                        {{ enrollmentStatusLabel }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">School Year</div>
                    <div class="gov-stat-value gov-stat-text">
                        {{ enrollment?.currentSchoolYear || "—" }}
                    </div>
                </div>
            </div>

            <div class="gov-panel">
                <div class="gov-panel-bar">GWA Progress</div>
                <div v-if="gwaChartData" class="chart-container">
                    <Line :data="gwaChartData" :options="gwaChartOptions" />
                </div>
                <p v-else class="chart-empty">
                    No GWA records yet. Previous GWA and graded subjects will
                    appear here.
                </p>
            </div>

            <div class="gov-two-col">
                <div class="gov-panel">
                    <div class="gov-panel-bar">Class Schedule</div>
                    <table class="gov-table">
                        <thead>
                            <tr>
                                <th>Schedule</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in schedule"
                                :key="item.id"
                            >
                                <td>{{ item.schedule || "TBA" }}</td>
                                <td>{{ item.subject_name }}</td>
                                <td>{{ item.teacher_name }}</td>
                            </tr>
                            <tr v-if="!schedule || schedule.length === 0">
                                <td colspan="3">No schedule available yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="gov-panel">
                    <div class="gov-panel-bar">Announcements</div>
                    <table class="gov-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Notice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Jan 5, 2026</td>
                                <td>
                                    Classes for Term 2 of the school year are
                                    now underway.
                                </td>
                            </tr>
                            <tr>
                                <td>Jan 3, 2026</td>
                                <td>
                                    Late enrollment is accepted until January
                                    10.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

<script setup>
import { computed } from "vue";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
);

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    enrollment: {
        type: Object,
        default: () => ({
            current: null,
            currentSchoolYear: "",
        }),
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    schedule: {
        type: Array,
        default: () => [],
    },
    gwaProgress: {
        type: Array,
        default: () => [],
    },
});

const enrollmentStatusLabel = computed(() => {
    if (props.user?.admission_status === "pending") {
        return "Admission Pending";
    }
    if (props.user?.admission_status === "rejected") {
        return "Admission Rejected";
    }
    const status = props.enrollment?.current?.status;
    if (status === "enrolled") {
        return "Enrolled";
    }
    if (status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
    return "Not Enrolled";
});

const gwaChartData = computed(() => {
    if (!props.gwaProgress.length) {
        return null;
    }

    return {
        labels: props.gwaProgress.map((point) => point.label),
        datasets: [
            {
                label: "GWA",
                data: props.gwaProgress.map((point) => point.gwa),
                borderColor: "#003366",
                backgroundColor: "#003366",
                borderWidth: 2,
                fill: false,
                tension: 0,
                pointBackgroundColor: "#003366",
                pointBorderColor: "#003366",
                pointBorderWidth: 1,
                pointRadius: 3,
                pointHoverRadius: 4,
            },
        ],
    };
});

const gwaChartOptions = computed(() => {
    const values = props.gwaProgress.map((point) => Number(point.gwa));
    const lowest = values.length ? Math.min(...values) : 75;
    const min = lowest < 75 ? Math.max(60, Math.floor(lowest / 5) * 5) : 75;

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: "#003366",
                titleFont: { size: 14, weight: "bold" },
                bodyFont: { size: 13 },
                padding: 8,
                cornerRadius: 0,
                callbacks: {
                    label: (context) => `GWA: ${context.raw}`,
                },
            },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 12, weight: "500" }, color: "#666" },
            },
            y: {
                min,
                max: 100,
                grid: { color: "rgba(0, 0, 0, 0.05)" },
                ticks: {
                    font: { size: 12 },
                    color: "#666",
                    callback: (value) => value + "%",
                },
            },
        },
    };
});
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

.gov-stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.gov-stat-box {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.gov-stat-label {
    background: #003366;
    color: #fff;
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.gov-stat-value {
    padding: 0.75rem 0.6rem 0.85rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #003366;
    text-align: center;
}

.gov-stat-text {
    font-size: 0.95rem;
    line-height: 1.3;
    text-transform: capitalize;
}

.gov-panel {
    background: #fff;
    border: 1px solid #c5c5c5;
    margin-bottom: 0.85rem;
}

.gov-panel-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.admission-note {
    margin: 0;
    padding: 0.75rem 0.85rem;
    font-size: 0.9rem;
    color: #333;
    line-height: 1.45;
}

.chart-container {
    padding: 0.75rem 0.85rem 1rem;
    height: 260px;
}

.chart-empty {
    margin: 0;
    padding: 1.25rem 0.85rem;
    font-size: 0.9rem;
    color: #555;
}

.gov-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

.gov-two-col .gov-panel {
    margin-bottom: 0;
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

@media (max-width: 900px) {
    .gov-stat-row,
    .gov-two-col {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .gov-stat-row,
    .gov-two-col {
        grid-template-columns: 1fr;
    }

    .gov-pagehead-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
