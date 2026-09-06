<template>
    <SchoolFormShell
        :user="user"
        :viewer="viewer"
        :title="`${form.code} · ${form.title}`"
        :page-title="form.code"
        :back-url="backUrl"
    >
        <section class="sf-sheet booklet">
            <div class="sf9-page">
                <div class="sf9-letterhead">
                    <img
                        src="/images/deped-seal.svg"
                        alt="Department of Education seal"
                        class="sf9-deped"
                    />
                    <div class="sf9-head-text">
                        <div>Republic of the Philippines</div>
                        <div>Department of Education</div>
                        <div>{{ dash(school.region) }}</div>
                        <div class="sf9-office-line">
                            <span>SCHOOLS DIVISION OFFICE OF</span>
                            <strong>{{ dash(divisionName) }}</strong>
                        </div>
                        <div class="sf9-office-line short">
                            <span>District</span>
                            <strong>{{ dash(school.district) }}</strong>
                        </div>
                        <div>Municipality/City, Province</div>
                        <div>{{ localityLine }}</div>
                    </div>
                    <img
                        src="/images/311494412_220590550318716_333223840059485017_n.jpg"
                        alt="School seal"
                        class="sf9-seal"
                    />
                </div>
                <div class="sf9-school-line">
                    <span>School:</span>
                    <strong>{{ dash(school.school_name) }}</strong>
                </div>

                <h1 class="sf9-title">Learner's Performance Report</h1>
                <p class="sf9-sy">
                    School Year {{ dash(class_info.school_year) }}
                </p>

                <div class="sf9-learner">
                    <div class="sf9-field long">
                        <span>Name:</span>
                        <strong>{{ dash(learner.full_name) }}</strong>
                    </div>
                    <div class="sf9-field">
                        <span>Age:</span>
                        <strong>{{ dash(learner.age) }}</strong>
                    </div>
                    <div class="sf9-field">
                        <span>Sex:</span>
                        <strong>{{ dash(learner.sex) }}</strong>
                    </div>
                    <div class="sf9-field long">
                        <span>LRN:</span>
                        <strong>{{ dash(learner.lrn) }}</strong>
                    </div>
                    <div class="sf9-field">
                        <span>Grade:</span>
                        <strong>{{ dash(class_info.grade) }}</strong>
                    </div>
                    <div class="sf9-field">
                        <span>Section:</span>
                        <strong>{{ dash(class_info.section) }}</strong>
                    </div>
                    <div class="sf9-field track">
                        <span>Track (SHS only)</span>
                        <strong>{{ dash(class_info.track_strand) }}</strong>
                    </div>
                </div>

                <div class="sf9-parents">
                    <div>Dear Parents,</div>
                    <p>
                        This Performance Report shows the ability and progress
                        your child has made in the different learning areas as
                        well as his/her core values.
                    </p>
                    <p>
                        The school welcomes you should you desire to know more
                        about your child's progress.
                    </p>
                </div>

                <table class="sf-table sf9-grades">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sf9-area">Learning Areas</th>
                            <th colspan="3">TERM</th>
                            <th rowspan="2">Final Grade</th>
                            <th rowspan="2">Remarks</th>
                        </tr>
                        <tr>
                            <th>1</th>
                            <th>2</th>
                            <th>3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template
                            v-for="(group, groupIndex) in groupedAreas"
                            :key="groupIndex"
                        >
                            <tr v-if="group.type" class="sf9-group">
                                <td colspan="6">{{ group.type }}</td>
                            </tr>
                            <tr
                                v-for="(area, index) in group.rows"
                                :key="`${groupIndex}-${index}`"
                            >
                                <td>{{ area.name }}</td>
                                <td class="sf-center">{{ dash(area.term_1) }}</td>
                                <td class="sf-center">{{ dash(area.term_2) }}</td>
                                <td class="sf-center">{{ dash(area.term_3) }}</td>
                                <td class="sf-center">{{ dash(area.final) }}</td>
                                <td class="sf-center">{{ dash(area.remarks) }}</td>
                            </tr>
                        </template>
                        <tr v-if="learning_areas.length === 0">
                            <td colspan="6" class="sf-center sf-muted">
                                No learning areas on record for this school
                                year.
                            </td>
                        </tr>
                        <tr class="sf9-average">
                            <td colspan="4" class="sf-right sf-value">
                                General Average
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

                <div class="sf9-descriptors">
                    <h2 class="sf9-block-title">Performance Descriptors</h2>
                    <div class="sf9-desc-head">
                        <span>Grading Scale</span>
                        <span>Description</span>
                        <span>Remarks</span>
                    </div>
                    <div
                        v-for="item in descriptors"
                        :key="item.description"
                        class="sf9-desc-row"
                    >
                        <span>{{ item.grade }}</span>
                        <span>{{ item.description }}</span>
                        <span>{{ item.remarks }}</span>
                    </div>
                </div>
            </div>

            <div class="sf9-page">
                <h2 class="sf9-block-title">Attendance Record</h2>
                <table class="sf-table sf9-attendance">
                    <thead>
                        <tr>
                            <th>Month</th>
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
                            <td>No. of Class Days</td>
                            <td
                                v-for="month in attendance_months"
                                :key="`d-${month}`"
                            ></td>
                        </tr>
                        <tr>
                            <td>No. of Days Present</td>
                            <td
                                v-for="month in attendance_months"
                                :key="`p-${month}`"
                            ></td>
                        </tr>
                        <tr>
                            <td>No. of Days Absent</td>
                            <td
                                v-for="month in attendance_months"
                                :key="`a-${month}`"
                            ></td>
                        </tr>
                    </tbody>
                </table>

                <h2 class="sf9-block-title">Teacher's Comments / Remarks</h2>
                <div class="sf9-comment-stack">
                    <div class="sf9-comment">
                        <div class="sf9-comment-label">Term 1</div>
                    </div>
                    <div class="sf9-comment">
                        <div class="sf9-comment-label">Term 2</div>
                    </div>
                    <div class="sf9-comment">
                        <div class="sf9-comment-label">Term 3</div>
                    </div>
                </div>

                <hr class="sf9-rule" />
                <h2 class="sf9-block-title">Parents/Guardian's Signature</h2>
                <div class="sf9-parent-stack">
                    <div class="sf9-parent-line">
                        <span>Term 1</span>
                        <em></em>
                    </div>
                    <div class="sf9-parent-line">
                        <span>Term 2</span>
                        <em></em>
                    </div>
                    <div class="sf9-parent-line">
                        <span>Term 3</span>
                        <em></em>
                    </div>
                </div>

                <h2 class="sf9-block-title">Certificate of Transfer</h2>
                <p class="sf9-cert">
                    This is to certify that the above-named learner has
                    satisfactorily completed the requirements for the grade
                    level indicated.
                </p>
                <div class="sf9-transfer-fields">
                    <div class="sf9-field">
                        <span>Admitted to Grade:</span>
                        <strong>{{ dash(class_info.grade) }}</strong>
                    </div>
                    <div class="sf9-field">
                        <span>Eligible for Admission to Grade:</span>
                        <strong>{{ dash(next_grade) }}</strong>
                    </div>
                </div>
                <p class="sf9-approved">Approved:</p>
                <div class="sf9-sign-pair">
                    <div class="sf9-sign">
                        <div class="sf9-name">
                            {{ dash(school_head || school.school_head) }}
                        </div>
                        <div class="sf9-line"></div>
                        <div>School Head</div>
                    </div>
                    <div class="sf9-sign">
                        <div class="sf9-name">
                            {{ dash(class_info.adviser) }}
                        </div>
                        <div class="sf9-line"></div>
                        <div>Adviser</div>
                    </div>
                </div>

                <h2 class="sf9-block-title">
                    Cancellation of Eligibility to Transfer
                </h2>
                <div class="sf9-cancel-row">
                    <div class="sf9-field">
                        <span>Admitted in:</span>
                        <strong></strong>
                    </div>
                    <div class="sf9-field">
                        <span>Date:</span>
                        <strong></strong>
                    </div>
                </div>
                <div class="sf9-sign sf9-sign-left">
                    <div class="sf9-name">
                        {{ dash(school_head || school.school_head) }}
                    </div>
                    <div class="sf9-line"></div>
                    <div>School Head</div>
                </div>
            </div>
        </section>
    </SchoolFormShell>
</template>

<script setup>
import { computed } from "vue";
import SchoolFormShell from "./SchoolFormShell.vue";

const props = defineProps({
    user: { type: Object, required: true },
    form: { type: Object, required: true },
    school: { type: Object, required: true },
    learner: { type: Object, required: true },
    class_info: { type: Object, required: true },
    learning_areas: { type: Array, default: () => [] },
    general_average: { type: [Number, String], default: null },
    general_average_remarks: { type: String, default: null },
    attendance_months: { type: Array, default: () => [] },
    descriptors: { type: Array, default: () => [] },
    school_head: { type: String, default: null },
    next_grade: { type: String, default: null },
    backUrl: { type: String, default: "/" },
    viewer: { type: String, default: "admin" },
});

const divisionName = computed(() => {
    const division = (props.school.division || "").trim();
    if (!division) {
        return "";
    }
    return division
        .replace(/^schools\s+division\s+(office\s+)?(of\s+)?/i, "")
        .trim()
        .toUpperCase();
});

const localityLine = computed(() => {
    const parts = [props.school.municipality, props.school.province].filter(
        Boolean,
    );
    return parts.length ? parts.join(", ") : "—";
});

const groupedAreas = computed(() => {
    const groups = [];
    props.learning_areas.forEach((area) => {
        const type = area.type || "";
        const last = groups[groups.length - 1];
        if (!last || last.type !== type) {
            groups.push({ type, rows: [area] });
            return;
        }
        last.rows.push(area);
    });
    return groups;
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
