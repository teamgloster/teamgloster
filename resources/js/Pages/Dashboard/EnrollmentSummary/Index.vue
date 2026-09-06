<template>
    <component
        :is="layoutComponent"
        title="Enrollment Summary"
        pageTitle="Enrollment Summary"
        currentPage="enrollment-summary"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — School Year Report
                </p>
                <div class="gov-pagehead-row">
                    <h2>Enrollment Summary</h2>
                    <div class="head-actions">
                        <select
                            v-model="selectedYear"
                            class="year-select"
                            @change="changeYear"
                        >
                            <option
                                v-for="year in schoolYears"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                        <button type="button" class="btn-primary" @click="printSummary">
                            Print
                        </button>
                    </div>
                </div>
            </div>

            <p class="intro">
                Counts and rates for SY {{ summary.school_year }}. Promotion,
                completion, and graduation use the next school year's placement
                when it exists; otherwise they use teacher-encoded final grades
                (75 and above in all subjects).
            </p>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrolled</div>
                    <div class="gov-stat-value">{{ totals.enrolled }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Drop-outs</div>
                    <div class="gov-stat-value">{{ totals.dropped }}</div>
                    <div class="gov-stat-rate">{{ formatRate(totals.dropout_rate) }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Promoted</div>
                    <div class="gov-stat-value">{{ totals.promoted }}</div>
                    <div class="gov-stat-rate">{{ formatRate(totals.promotion_rate) }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">JHS Completion</div>
                    <div class="gov-stat-value">{{ totals.completed }}</div>
                    <div class="gov-stat-rate">{{ formatRate(totals.completion_rate) }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Graduation</div>
                    <div class="gov-stat-value">{{ totals.graduated }}</div>
                    <div class="gov-stat-rate">{{ formatRate(totals.graduation_rate) }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Retained / Failed</div>
                    <div class="gov-stat-value">{{ totals.retained }}</div>
                </div>
            </div>

            <div class="data-table-container">
                <div class="grades-table-bar">By year level</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Year level</th>
                            <th>Enrolled</th>
                            <th>Drop-outs</th>
                            <th>Dropout rate</th>
                            <th>Promoted</th>
                            <th>Promotion rate</th>
                            <th>Completed</th>
                            <th>Graduated</th>
                            <th>Retained</th>
                            <th>Incomplete grades</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in summary.by_year_level" :key="row.year_level_id">
                            <td>{{ row.name }}</td>
                            <td>{{ row.enrolled }}</td>
                            <td>{{ row.dropped }}</td>
                            <td>{{ formatRate(row.dropout_rate) }}</td>
                            <td>{{ row.promoted }}</td>
                            <td>{{ formatRate(row.promotion_rate) }}</td>
                            <td>{{ row.completed }}</td>
                            <td>{{ row.graduated }}</td>
                            <td>{{ row.retained }}</td>
                            <td>{{ row.incomplete }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>School total</th>
                            <th>{{ totals.enrolled }}</th>
                            <th>{{ totals.dropped }}</th>
                            <th>{{ formatRate(totals.dropout_rate) }}</th>
                            <th>{{ totals.promoted }}</th>
                            <th>{{ formatRate(totals.promotion_rate) }}</th>
                            <th>{{ totals.completed }}</th>
                            <th>{{ totals.graduated }}</th>
                            <th>{{ totals.retained }}</th>
                            <th>{{ totals.incomplete }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <p class="footnote">
                Dropout rate = drop-outs ÷ (enrolled + drop-outs). Promotion
                rate uses Grades 7–11. Completion rate uses Grade 10. Graduation
                rate uses Grade 12.
                <span v-if="!summary.has_next_year_records">
                    No {{ summary.next_school_year }} enrollments yet, so
                    promotion and completion are estimated from encoded grades.
                </span>
            </p>
        </div>
    </component>
</template>

<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    viewer: { type: String, default: "admin" },
    schoolYear: { type: String, default: "" },
    schoolYears: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
});

const layoutComponent = computed(() =>
    props.viewer === "registrar" ? RegistrarLayout : AdminLayout,
);
const selectedYear = ref(props.schoolYear);
const totals = computed(() => props.summary?.totals || {});
const basePath = computed(() =>
    props.viewer === "registrar" ? "/registrar" : "/admin",
);

const formatRate = (value) => (value === null || value === undefined ? "—" : `${value}%`);

const changeYear = () => {
    router.get(
        `${basePath.value}/enrollment-summary`,
        { school_year: selectedYear.value },
        { preserveState: false },
    );
};

const printSummary = () => {
    window.print();
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.intro,
.footnote {
    color: #555;
    font-size: 0.86rem;
    margin: 0 0 0.85rem;
}

.footnote {
    margin-top: 0.85rem;
}

.head-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.year-select,
.btn-primary {
    border: 1px solid #003366;
    padding: 0.4rem 0.7rem;
    font-weight: 700;
}

.btn-primary {
    background: #003366;
    color: #fff;
    cursor: pointer;
}

.gov-stat-rate {
    font-size: 0.78rem;
    color: #555;
    margin-top: 0.15rem;
}

.grades-table-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-weight: 600;
}

tfoot th {
    background: #e8eef4;
    color: #003366;
}

@media print {
    .head-actions {
        display: none;
    }
}
</style>
