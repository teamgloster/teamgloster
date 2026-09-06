<template>
    <RegistrarLayout
        title="Enrollment Details"
        pageTitle="Enrollment Details"
        currentPage="students"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Registrar Records
                </p>
                <div class="gov-pagehead-row">
                    <h2>Enrollment Details</h2>
                    <span class="gov-sy">School Year {{ currentSchoolYear }}</span>
                </div>
            </div>

            <div class="page-actions">
                <Link
                    v-if="currentEnrollment?.section_id"
                    class="btn-secondary"
                    :href="`/registrar/sections/${currentEnrollment.section_id}/enrollment`"
                >
                    Back to Enrolment
                </Link>
                <Link href="/registrar/students" class="btn-secondary">
                    Back to Students
                </Link>
                <Link
                    class="btn-secondary"
                    :href="`/registrar/students/${student.id}/sf9`"
                >
                    View SF9
                </Link>
                <Link
                    class="btn-secondary"
                    :href="`/registrar/students/${student.id}/sf10`"
                >
                    View SP-10
                </Link>
            </div>

            <div class="landscape-details">
                <div class="detail-header">
                    <div class="detail-avatar">{{ initials }}</div>
                    <div class="detail-title">
                        <h4>
                            {{ student.last_name }}, {{ student.first_name }}
                            {{ student.middle_name }} {{ student.suffix }}
                        </h4>
                        <span class="lrn-badge large">
                            LRN: {{ student.lrn || "—" }}
                        </span>
                    </div>
                </div>

                <div class="landscape-columns">
                    <section class="landscape-pane">
                        <h3 class="pane-title">Learner</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Email</label>
                                <span>{{ student.email || "—" }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Contact</label>
                                <span>{{
                                    student.phone_no || "Not provided"
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Gender</label>
                                <span>{{ formatGender(student.gender) }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Admission Status</label>
                                <span
                                    class="status-badge"
                                    :class="student.admission_status || 'pending'"
                                >
                                    {{ student.admission_status || "—" }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Guardian</label>
                                <span>{{
                                    student.guardian_full_name || "Not provided"
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Guardian Contact</label>
                                <span>{{
                                    student.guardian_contact_no ||
                                    "Not provided"
                                }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="landscape-pane">
                        <h3 class="pane-title">
                            Current enrollment — SY {{ currentSchoolYear }}
                        </h3>
                        <div v-if="currentEnrollment" class="detail-grid">
                            <div class="detail-item">
                                <label>Enrollment Status</label>
                                <span
                                    class="status-badge"
                                    :class="currentEnrollment.status"
                                >
                                    {{
                                        enrollmentStatusLabel(
                                            currentEnrollment.status,
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Year Level</label>
                                <span>{{
                                    currentEnrollment.year_level?.name || "—"
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Section</label>
                                <span>{{
                                    currentEnrollment.section?.name ||
                                    "Not assigned"
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Semester</label>
                                <span>{{
                                    semesterLabel(currentEnrollment.semester)
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Enrollment Type</label>
                                <span>{{
                                    enrollmentTypeLabel(
                                        currentEnrollment.enrollment_type,
                                    )
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Previous GWA</label>
                                <span>{{
                                    formatGwa(currentEnrollment.previous_gwa)
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Previous School</label>
                                <span>{{
                                    currentEnrollment.previous_school ||
                                    student.previous_school ||
                                    "—"
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Enrolled At</label>
                                <span>{{
                                    formatDate(currentEnrollment.enrolled_at)
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Approved By</label>
                                <span>{{
                                    approvedByName(currentEnrollment.approved_by)
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Promotion</label>
                                <span>{{
                                    student.promotion?.message ||
                                    "No promotion record"
                                }}</span>
                            </div>
                            <div
                                v-if="currentEnrollment.remarks"
                                class="detail-item full-width"
                            >
                                <label>Remarks</label>
                                <p class="description-text">
                                    {{ currentEnrollment.remarks }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="empty-note">
                            This student has no enrollment record for school
                            year {{ currentSchoolYear }}.
                        </p>
                    </section>
                </div>

                <section class="landscape-pane">
                    <h3 class="pane-title">Enrollment history</h3>
                    <div v-if="enrollments.length" class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>School year</th>
                                    <th>Year level</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>GWA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="enrollment in enrollments"
                                    :key="enrollment.id"
                                >
                                    <td>{{ enrollment.school_year }}</td>
                                    <td>
                                        {{ enrollment.year_level?.name || "—" }}
                                    </td>
                                    <td>
                                        {{
                                            enrollment.section?.name ||
                                            "Not assigned"
                                        }}
                                    </td>
                                    <td>
                                        <span
                                            class="status-badge"
                                            :class="enrollment.status"
                                        >
                                            {{
                                                enrollmentStatusLabel(
                                                    enrollment.status,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td>
                                        {{
                                            enrollmentTypeLabel(
                                                enrollment.enrollment_type,
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{ formatGwa(enrollment.previous_gwa) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="empty-note">
                        No enrollment history is on file for this learner.
                    </p>
                </section>
            </div>
        </div>
    </RegistrarLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    student: { type: Object, required: true },
    currentEnrollment: { type: Object, default: null },
    enrollments: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});

const initials = computed(() =>
    `${props.student.first_name?.charAt(0) || ""}${props.student.last_name?.charAt(0) || ""}`.toUpperCase(),
);

const enrollmentStatusLabel = (status) =>
    ({
        pending: "Pending",
        approved: "Approved",
        enrolled: "Enrolled",
        rejected: "Rejected",
        dropped: "Dropped",
    })[status] || status || "No record";

const enrollmentTypeLabel = (type) =>
    ({
        new: "New",
        old: "Old / Continuing",
        transferee: "Transferee",
        returnee: "Returnee",
    })[type] || type || "—";

const semesterLabel = (semester) =>
    ({
        first: "First Semester",
        second: "Second Semester",
        full_year: "Full Year",
    })[semester] || semester || "—";

const formatGender = (gender) => {
    if (!gender) return "Not provided";
    return gender.charAt(0).toUpperCase() + gender.slice(1);
};

const formatGwa = (gwa) => {
    if (gwa === null || gwa === undefined || gwa === "") return "—";
    const value = Number(gwa);
    return Number.isNaN(value) ? "—" : value.toFixed(2);
};

const formatDate = (value) => {
    if (!value) return "—";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "—";
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const approvedByName = (approver) => {
    if (!approver) return "—";
    return `${approver.first_name || ""} ${approver.last_name || ""}`.trim() || "—";
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.page-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.85rem;
}

.page-actions .btn-secondary {
    text-decoration: none;
}

.landscape-details {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.landscape-columns {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 0.85rem;
}

.landscape-pane {
    border: 1px solid #c5c5c5;
    background: #fff;
    padding: 0.85rem 1rem;
}

.pane-title {
    margin: 0 0 0.75rem;
    color: #003366;
    font-size: 1rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.empty-note {
    margin: 0;
    color: #555;
    font-size: 0.88rem;
}

@media (max-width: 860px) {
    .landscape-columns,
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
