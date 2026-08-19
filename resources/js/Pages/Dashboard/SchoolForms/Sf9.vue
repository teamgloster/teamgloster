<template>
    <SchoolFormShell
        :user="user"
        :viewer="viewer"
        :title="`${form.code} · ${form.title}`"
        :page-title="form.code"
        :back-url="backUrl"
    >
        <section class="sf-sheet">
                <FormLetterhead :school="school" :form-code="form.code" />
                <h1 class="sf-form-title">{{ form.title }}</h1>
                <p class="sf-form-sub">
                    {{ form.former }} · {{ form.legal }}
                    <span v-if="generated_term">
                        · Generated as of Term {{ generated_term }}
                    </span>
                </p>

                <div class="sf-fill-grid">
                    <div class="sf-fill">
                        <span class="sf-fill-label">LRN:</span>
                        <span class="sf-fill-value">{{ dash(learner.lrn) }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">School Year:</span>
                        <span class="sf-fill-value">{{
                            dash(class_info.school_year)
                        }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Learner's Name:</span>
                        <span class="sf-fill-value">{{
                            dash(learner.full_name)
                        }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Grade &amp; Section:</span>
                        <span class="sf-fill-value">{{ gradeSection }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Sex:</span>
                        <span class="sf-fill-value">{{ dash(learner.sex) }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Age (as of August 1):</span>
                        <span class="sf-fill-value">{{ dash(learner.age) }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Date of Birth:</span>
                        <span class="sf-fill-value">{{
                            dash(learner.birth_date)
                        }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">{{
                            class_info.track_strand
                                ? "Track / Strand:"
                                : "Adviser:"
                        }}</span>
                        <span class="sf-fill-value">{{
                            class_info.track_strand
                                ? dash(class_info.track_strand)
                                : dash(class_info.adviser)
                        }}</span>
                    </div>
                    <div class="sf-fill">
                        <span class="sf-fill-label">Grading Period:</span>
                        <span class="sf-fill-value">{{
                            dash(class_info.term_label)
                        }}</span>
                    </div>
                </div>

                <h2 class="sf-section-title">
                    Report on Learning Progress and Achievement
                </h2>
                <table class="sf-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 34%">
                                Learning Areas
                            </th>
                            <th colspan="3">Grading Period</th>
                            <th rowspan="2">Final Rating</th>
                            <th rowspan="2">Remarks</th>
                        </tr>
                        <tr>
                            <th>Term 1</th>
                            <th>Term 2</th>
                            <th>Term 3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(area, index) in learning_areas"
                            :key="index"
                        >
                            <td>
                                {{ area.name }}
                                <span
                                    v-if="area.semester && area.semester !== 'Full Year'"
                                    class="sf-muted"
                                >
                                    ({{ area.semester }})
                                </span>
                            </td>
                            <td class="sf-center">{{ dash(area.term_1) }}</td>
                            <td class="sf-center">{{ dash(area.term_2) }}</td>
                            <td class="sf-center">{{ dash(area.term_3) }}</td>
                            <td class="sf-center">{{ dash(area.final) }}</td>
                            <td class="sf-center">{{ dash(area.remarks) }}</td>
                        </tr>
                        <tr v-if="learning_areas.length === 0">
                            <td colspan="6" class="sf-center sf-muted">
                                No learning areas on record for this school
                                year.
                            </td>
                        </tr>
                        <tr>
                            <td class="sf-right sf-value">General Average</td>
                            <td colspan="3" class="sf-center sf-muted">
                                {{
                                    general_average_descriptor
                                        ? general_average_descriptor
                                        : "—"
                                }}
                            </td>
                            <td class="sf-center sf-value">
                                {{ dash(general_average) }}
                            </td>
                            <td class="sf-center sf-value">
                                {{ dash(general_average_remarks) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="sf-note">
                    This school uses three grading periods (Terms 1–3) under its
                    approved school calendar. Final ratings and the general
                    average are whole numbers, following DepEd Order No. 8, s.
                    2015. Passing mark is 75.
                    <span v-if="generated_term && generated_term < 3">
                        This copy is generated as of Term {{ generated_term }}.
                        Later terms are left blank. The standing shown is the
                        average of completed terms, not yet the official final
                        rating.
                    </span>
                    <span v-else-if="!general_average_complete">
                        The general average is computed only from learning areas
                        with a final rating.
                    </span>
                </p>

                <h2 class="sf-section-title">
                    Report on Learner's Observed Values
                </h2>
                <table class="sf-table">
                    <thead>
                        <tr>
                            <th style="width: 18%">Core Values</th>
                            <th>Behavior Statements</th>
                            <th>AO</th>
                            <th>SO</th>
                            <th>RO</th>
                            <th>NO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, index) in observed_values"
                            :key="index"
                        >
                            <td>{{ row.core_value }}</td>
                            <td>{{ row.behavior }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <p class="sf-note">
                    Marking: AO — Always Observed; SO — Sometimes Observed; RO —
                    Rarely Observed; NO — Not Observed (DepEd Order No. 8, s.
                    2015). Encode marks when observed-values records are
                    available.
                </p>

                <h2 class="sf-section-title">Attendance Record</h2>
                <table class="sf-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th
                                v-for="month in attendance_months"
                                :key="month"
                            >
                                {{ month }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Days of School</td>
                            <td
                                v-for="month in attendance_months"
                                :key="`d-${month}`"
                            ></td>
                        </tr>
                        <tr>
                            <td>Days Present</td>
                            <td
                                v-for="month in attendance_months"
                                :key="`p-${month}`"
                            ></td>
                        </tr>
                        <tr>
                            <td>Days Absent</td>
                            <td
                                v-for="month in attendance_months"
                                :key="`a-${month}`"
                            ></td>
                        </tr>
                    </tbody>
                </table>
                <p class="sf-note">
                    Attendance is left blank until daily attendance is encoded
                    in the system.
                </p>

                <table class="sf-table sf-legend">
                    <thead>
                        <tr>
                            <th colspan="2">
                                Descriptors and Grading Scale (DO 8, s. 2015)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in descriptors" :key="item.label">
                            <td style="width: 55%">{{ item.label }}</td>
                            <td class="sf-center">{{ item.scale }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="sf-sign-row">
                    <div class="sf-sign">
                        <div class="sf-sign-line">
                            {{ dash(class_info.adviser) }}
                        </div>
                        <div class="sf-sign-role">Class Adviser</div>
                    </div>
                    <div class="sf-sign">
                        <div class="sf-sign-line">{{ dash(parent) }}</div>
                        <div class="sf-sign-role">Parent / Guardian</div>
                    </div>
                    <div class="sf-sign">
                        <div class="sf-sign-line">{{ dash(school_head) }}</div>
                        <div class="sf-sign-role">School Head</div>
                    </div>
                </div>
            </section>
    </SchoolFormShell>
</template>

<script setup>
import { computed } from "vue";
import SchoolFormShell from "./SchoolFormShell.vue";
import FormLetterhead from "./FormLetterhead.vue";

const props = defineProps({
    user: { type: Object, required: true },
    form: { type: Object, required: true },
    school: { type: Object, required: true },
    learner: { type: Object, required: true },
    class_info: { type: Object, required: true },
    learning_areas: { type: Array, default: () => [] },
    general_average: { type: [Number, String], default: null },
    general_average_descriptor: { type: String, default: null },
    general_average_remarks: { type: String, default: null },
    general_average_complete: { type: Boolean, default: false },
    observed_values: { type: Array, default: () => [] },
    attendance_months: { type: Array, default: () => [] },
    descriptors: { type: Array, default: () => [] },
    parent: { type: String, default: null },
    school_head: { type: String, default: null },
    backUrl: { type: String, default: "/" },
    viewer: { type: String, default: "admin" },
    generated_term: { type: [Number, String], default: null },
});

const gradeSection = computed(() => {
    const grade = props.class_info.grade || "";
    const section = props.class_info.section || "";
    if (grade && section) {
        return `${grade} — ${section}`;
    }
    return grade || section || "—";
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
</style>
