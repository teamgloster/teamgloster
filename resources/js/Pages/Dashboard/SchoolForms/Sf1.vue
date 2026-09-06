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
                        <span class="sf-fill-label">Grade / Section:</span>
                        <span class="sf-fill-value"
                            >{{ dash(class_info.grade) }} —
                            {{ dash(class_info.section) }}</span
                        >
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Class Adviser:</span>
                        <span class="sf-fill-value">{{
                            dash(class_info.adviser)
                        }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Registered Learners:</span>
                        <span class="sf-fill-value"
                            >Male {{ totals.male }} · Female
                            {{ totals.female }} · Total
                            {{ totals.total }}</span
                        >
                    </div>
                </div>

                <h2 class="sf-section-title">List of Learners</h2>
                <table class="sf-table sf-compact">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>LRN</th>
                            <th>Name (Last, First, Middle)</th>
                            <th>Sex</th>
                            <th>Birth Date</th>
                            <th>Age</th>
                            <th>Mother Tongue</th>
                            <th>IP</th>
                            <th>Religion</th>
                            <th>Address</th>
                            <th>Father</th>
                            <th>Mother</th>
                            <th>Guardian / Contact</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="learner in learners" :key="learner.no">
                            <td class="sf-center">{{ learner.no }}</td>
                            <td>{{ dash(learner.lrn) }}</td>
                            <td>{{ dash(learner.name) }}</td>
                            <td class="sf-center">{{ dash(learner.sex) }}</td>
                            <td>{{ dash(learner.birth_date) }}</td>
                            <td class="sf-center">{{ dash(learner.age) }}</td>
                            <td>{{ dash(learner.mother_tongue) }}</td>
                            <td>{{ dash(learner.ip) }}</td>
                            <td>{{ dash(learner.religion) }}</td>
                            <td>{{ dash(learner.address) }}</td>
                            <td>{{ dash(learner.father) }}</td>
                            <td>{{ dash(learner.mother) }}</td>
                            <td>
                                {{ dash(learner.guardian) }}
                                <span v-if="learner.contact">
                                    / {{ learner.contact }}</span
                                >
                            </td>
                            <td>{{ dash(learner.remarks) }}</td>
                        </tr>
                        <tr v-if="learners.length === 0">
                            <td colspan="14" class="sf-center sf-muted">
                                No enrolled learners in this section.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="sf-note">
                    Mother Tongue, IP, and Religion are left blank until those
                    learner data fields are encoded. Age is as of August 1 of
                    the school year.
                </p>

                <div class="sf-sign-row">
                    <div class="sf-sign">
                        <div class="sf-sign-line">
                            {{ dash(class_info.adviser) }}
                        </div>
                        <div class="sf-sign-role">Prepared by: Class Adviser</div>
                    </div>
                    <div class="sf-sign">
                        <div class="sf-sign-line">
                            {{ dash(school_head || school.school_head) }}
                        </div>
                        <div class="sf-sign-role">Certified Correct: School Head</div>
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

@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
    }
}
</style>
