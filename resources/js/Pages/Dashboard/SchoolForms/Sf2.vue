<template>
    <SchoolFormShell
        :user="user"
        :viewer="viewer"
        :title="`${form.code} · ${form.title}`"
        :page-title="form.code"
        :back-url="backUrl"
    >
        <section class="sf-sheet landscape">
                <FormLetterhead :school="school" :form-code="form.code" />
                <h1 class="sf-form-title">{{ form.title }}</h1>
                <p class="sf-form-sub">{{ form.legal }}</p>

                <div class="sf-fill-grid">
                    <div class="sf-fill">
                        <span class="sf-fill-label">School Year:</span>
                        <span class="sf-fill-value">{{
                            dash(class_info.school_year)
                        }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Month:</span>
                        <span class="sf-fill-value"
                            >{{ dash(class_info.month) }}
                            {{ dash(class_info.year) }}</span
                        >
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Grade / Section:</span>
                        <span class="sf-fill-value"
                            >{{ dash(class_info.grade) }} —
                            {{ dash(class_info.section) }}</span
                        >
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Enrollment:</span>
                        <span class="sf-fill-value"
                            >Male {{ totals.male }} · Female
                            {{ totals.female }} · Total
                            {{ totals.total }}</span
                        >
                    </div>
                </div>

                <h2 class="sf-section-title">Daily Attendance</h2>
                <table class="sf-table sf-compact sf-attendance">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Sex</th>
                            <th
                                v-for="day in days"
                                :key="day.day"
                                class="day-col"
                                :class="{
                                    weekend: day.weekend,
                                    invalid: !day.valid,
                                }"
                            >
                                {{ day.day }}
                            </th>
                            <th>Absent</th>
                            <th>Tardy</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="learner in learners" :key="learner.no">
                            <td class="sf-center">{{ learner.no }}</td>
                            <td class="name-col">{{ dash(learner.name) }}</td>
                            <td class="sf-center">{{ dash(learner.sex) }}</td>
                            <td
                                v-for="day in days"
                                :key="`${learner.no}-${day.day}`"
                                class="day-col"
                                :class="{
                                    weekend: day.weekend,
                                    invalid: !day.valid,
                                }"
                            ></td>
                            <td></td>
                            <td></td>
                            <td>{{ dash(learner.remarks) }}</td>
                        </tr>
                        <tr v-if="learners.length === 0">
                            <td colspan="36" class="sf-center sf-muted">
                                No enrolled learners in this section.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="sf-note">
                    Legend: blank — Present; X — Absent; T — Tardy. Shaded
                    columns are weekends or dates that do not exist in this
                    month. Daily attendance is left blank until encoded in the
                    system.
                </p>

                <div class="sf-sign-row">
                    <div class="sf-sign">
                        <div class="sf-sign-line">
                            {{ dash(class_info.adviser) }}
                        </div>
                        <div class="sf-sign-role">Prepared by: Class Adviser</div>
                    </div>
                    <div class="sf-sign">
                        <div class="sf-sign-line">{{ dash(school_head) }}</div>
                        <div class="sf-sign-role">Checked by: School Head</div>
                    </div>
                    <div class="sf-seal-box">Dry seal</div>
                </div>
            </section>
    </SchoolFormShell>
</template>

<script setup>
import SchoolFormShell from "./SchoolFormShell.vue";
import FormLetterhead from "./FormLetterhead.vue";

defineProps({
    user: { type: Object, required: true },
    form: { type: Object, required: true },
    school: { type: Object, required: true },
    class_info: { type: Object, required: true },
    days: { type: Array, default: () => [] },
    learners: { type: Array, default: () => [] },
    totals: { type: Object, default: () => ({ male: 0, female: 0, total: 0 }) },
    school_head: { type: String, default: null },
    backUrl: { type: String, default: "/" },
    viewer: { type: String, default: "admin" },
});

const dash = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }
    return value;
};
</script>

<style>
@import "@/Styles/school-form.css";

.sf-attendance th,
.sf-attendance td {
    padding: 2px 2px;
    font-size: 8px;
}

.sf-attendance .name-col {
    min-width: 28mm;
    font-size: 8.5px;
}

.sf-attendance .day-col {
    width: 4.2mm;
    min-width: 4.2mm;
    text-align: center;
}

.sf-attendance .weekend {
    background: #eee;
}

.sf-attendance .invalid {
    background: #ccc;
}

@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }
}
</style>
