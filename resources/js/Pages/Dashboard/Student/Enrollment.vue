<template>
    <StudentLayout
        title="Enrollment"
        pageTitle="Enrollment"
        currentPage="enrollment"
        :user="user"
    >
        <div class="content-section">
            <div class="enrollment-container">
                <!-- Enrollment Banner -->
                <div class="enrollment-banner">
                    <div class="banner-content">
                        <div class="banner-icon">
                            <GraduationCap :size="40" />
                        </div>
                        <div class="banner-text">
                            <h2>Student Enrollment</h2>
                            <p>
                                Manage your enrollment status and applications
                            </p>
                        </div>
                    </div>
                    <div class="school-year-badge">
                        <Calendar :size="16" />
                        <span>S.Y. {{ enrollment.currentSchoolYear }}</span>
                    </div>
                </div>

                <!-- Current Enrollment Status -->
                <div v-if="enrollment.current" class="enrollment-status-card">
                    <div class="status-card-header">
                        <div
                            class="status-indicator"
                            :class="enrollment.current.status"
                        >
                            <div class="status-icon-wrapper">
                                <CheckCircle
                                    v-if="
                                        enrollment.current.status === 'enrolled'
                                    "
                                    :size="32"
                                />
                                <Clock
                                    v-else-if="
                                        enrollment.current.status === 'pending'
                                    "
                                    :size="32"
                                />
                                <X v-else :size="32" />
                            </div>
                            <div class="status-text">
                                <span class="status-label"
                                    >Enrollment Status</span
                                >
                                <span
                                    class="status-value"
                                    :class="enrollment.current.status"
                                >
                                    {{
                                        enrollment.current.status
                                            .charAt(0)
                                            .toUpperCase() +
                                        enrollment.current.status.slice(1)
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="enrollment-details-grid">
                        <div class="detail-card">
                            <div class="detail-icon blue">
                                <BookOpen :size="20" />
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Year Level</span>
                                <span class="detail-value">{{
                                    enrollment.current.year_level?.name || "N/A"
                                }}</span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon purple">
                                <User :size="20" />
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Section</span>
                                <span class="detail-value">{{
                                    enrollment.current.section?.name ||
                                    "To be assigned"
                                }}</span>
                            </div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon green">
                                <ClipboardList :size="20" />
                            </div>
                            <div class="detail-content">
                                <span class="detail-label"
                                    >Enrollment Type</span
                                >
                                <span class="detail-value">{{
                                    formatEnrollmentType(
                                        enrollment.current.enrollment_type,
                                    )
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="enrollment.current.status === 'pending'"
                        class="status-actions"
                    >
                        <div class="pending-notice">
                            <Bell :size="18" />
                            <span
                                >Your enrollment application is being reviewed.
                                You will be notified once approved.</span
                            >
                        </div>
                        <button
                            class="cancel-enrollment-btn"
                            @click="showCancelModal = true"
                        >
                            <X :size="18" /> Cancel Application
                        </button>
                    </div>
                </div>

                <!-- Admission not approved -->
                <div
                    v-else-if="!admissionApproved"
                    class="enrollment-form-card"
                >
                    <div class="form-card-header">
                        <div class="form-header-icon">
                            <Clock
                                v-if="user.admission_status === 'pending'"
                                :size="28"
                            />
                            <X v-else :size="28" />
                        </div>
                        <div class="form-header-text">
                            <h3 v-if="user.admission_status === 'rejected'">
                                Admission Rejected
                            </h3>
                            <h3 v-else>Admission Pending</h3>
                            <p v-if="user.admission_status === 'rejected'">
                                Your admission application was not approved.
                                You cannot enroll at this time.
                                <span v-if="user.admission_remarks">
                                    Reason: {{ user.admission_remarks }}
                                </span>
                            </p>
                            <p v-else>
                                Your admission application is being reviewed by
                                the registrar. You can enroll only after your
                                admission is approved.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Enrollment closed -->
                <div
                    v-else-if="enrollment.enrollmentOpen === false"
                    class="enrollment-form-card"
                >
                    <div class="form-card-header">
                        <div class="form-header-icon">
                            <X :size="28" />
                        </div>
                        <div class="form-header-text">
                            <h3>Enrollment is Closed</h3>
                            <p>
                                New applications for S.Y.
                                {{ enrollment.currentSchoolYear }} are not being
                                accepted. Please contact the school registrar.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Form (if not enrolled) -->
                <div v-else class="enrollment-form-card">
                    <div class="form-card-header">
                        <div class="form-header-icon"><Send :size="28" /></div>
                        <div class="form-header-text">
                            <h3>New Enrollment Application</h3>
                            <p>
                                Complete the form below to submit your
                                enrollment for S.Y.
                                {{ enrollment.currentSchoolYear }}
                            </p>
                        </div>
                    </div>

                    <form
                        @submit.prevent="submitEnrollment"
                        class="enrollment-form"
                    >
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <span class="section-number">1</span> Basic
                                Information
                            </h4>
                            <div class="form-grid form-grid-3">
                                <div class="form-group">
                                    <label for="year_level"
                                        >Year Level
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        id="year_level"
                                        v-model="enrollmentForm.year_level_id"
                                        required
                                        class="form-select"
                                    >
                                        <option value="">
                                            Select Year Level
                                        </option>
                                        <option
                                            v-for="level in enrollment.yearLevels"
                                            :key="level.id"
                                            :value="level.id"
                                            :disabled="!isYearLevelAllowed(level)"
                                        >
                                            {{ level.name }}
                                        </option>
                                    </select>
                                    <span
                                        v-if="previousYearLevel"
                                        class="input-hint"
                                    >
                                        Previous year level:
                                        {{ previousYearLevel.name }}. You may
                                        enroll in
                                        {{ previousYearLevel.name }} or higher.
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="enrollment_type"
                                        >Enrollment Type
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        id="enrollment_type"
                                        v-model="enrollmentForm.enrollment_type"
                                        required
                                        class="form-select"
                                    >
                                        <option value="">Select Type</option>
                                        <option value="new">New Student</option>
                                        <option value="old">
                                            Old/Continuing Student
                                        </option>
                                        <option value="transferee">
                                            Transferee
                                        </option>
                                        <option value="returnee">
                                            Returnee
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="school_year"
                                        >School Year
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        id="school_year"
                                        class="form-select"
                                        required
                                        :value="enrollment.currentSchoolYear"
                                    >
                                        <option
                                            v-if="enrollment.currentSchoolYear"
                                            :value="enrollment.currentSchoolYear"
                                        >
                                            {{ enrollment.currentSchoolYear }}
                                        </option>
                                        <option v-else value="">
                                            Not set by administrator
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="form-section-title">
                                <span class="section-number">2</span> Academic
                                Information
                            </h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="previous_gwa"
                                        >Previous GWA</label
                                    >
                                    <input
                                        type="number"
                                        id="previous_gwa"
                                        v-model="enrollmentForm.previous_gwa"
                                        class="form-input"
                                        placeholder="e.g., 88.50"
                                        step="0.01"
                                        min="70"
                                        max="100"
                                    />
                                    <span class="input-hint"
                                        >Your General Weighted Average from
                                        previous school year</span
                                    >
                                </div>
                                <div
                                    v-if="requiresPreviousSchool"
                                    class="form-group"
                                >
                                    <label for="previous_school"
                                        >Previous School
                                        <span class="required">*</span></label
                                    >
                                    <input
                                        type="text"
                                        id="previous_school"
                                        v-model="enrollmentForm.previous_school"
                                        class="form-input"
                                        placeholder="Enter your previous / old school name"
                                        required
                                    />
                                    <span class="input-hint"
                                        >Required for new, transferee, and
                                        returnee students</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="enrollment-notice">
                            <div class="notice-icon"><Bell :size="22" /></div>
                            <div class="notice-content">
                                <strong>Important Reminders:</strong>
                                <ul>
                                    <li>
                                        Ensure all your requirements are
                                        submitted before enrolling
                                    </li>
                                    <li>
                                        Section assignment will be based on your
                                        previous GWA and available slots
                                    </li>
                                    <li>
                                        You will be notified via email once your
                                        enrollment is approved
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button
                                type="submit"
                                class="submit-enrollment-btn"
                                :disabled="isSubmittingEnrollment"
                            >
                                <span
                                    v-if="isSubmittingEnrollment"
                                    class="btn-loading"
                                >
                                    <Loader2 :size="16" class="spin" />
                                    Submitting...
                                </span>
                                <span v-else class="btn-content">
                                    Submit Enrollment Application
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Enrollment History -->
                <div
                    v-if="enrollment.history && enrollment.history.length > 0"
                    class="enrollment-history"
                >
                    <div class="history-header">
                        <h3><History :size="22" /> Enrollment History</h3>
                        <span class="history-count"
                            >{{ enrollment.history.length }} record(s)</span
                        >
                    </div>
                    <div class="history-table-wrapper">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>School Year</th>
                                    <th>Year Level</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="record in enrollment.history"
                                    :key="record.id"
                                >
                                    <td>
                                        <span class="school-year-cell"
                                            ><Calendar :size="16" />
                                            {{ record.school_year }}</span
                                        >
                                    </td>
                                    <td>
                                        {{ record.year_level?.name || "N/A" }}
                                    </td>
                                    <td>{{ record.section?.name || "N/A" }}</td>
                                    <td>
                                        <span
                                            class="status-badge small"
                                            :class="record.status"
                                            >{{
                                                record.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                record.status.slice(1)
                                            }}</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Enrollment Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showCancelModal"
                    class="modal-overlay"
                    @click.self="showCancelModal = false"
                >
                    <div class="modal-container">
                        <h3>Cancel Enrollment?</h3>
                        <p>
                            Are you sure you want to cancel your enrollment
                            application?
                        </p>
                        <div class="modal-actions">
                            <button
                                class="btn-secondary"
                                @click="showCancelModal = false"
                            >
                                No, Keep It
                            </button>
                            <button
                                class="btn-danger"
                                @click="cancelEnrollment"
                            >
                                Yes, Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </StudentLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import {
    User,
    ClipboardList,
    BookOpen,
    BarChart3,
    CheckCircle,
    Calendar,
    Clock,
    Bell,
    X,
    GraduationCap,
    Send,
    History,
    Loader2,
} from "lucide-vue-next";

const toast = useToast();
const page = usePage();

const props = defineProps({
    user: { type: Object, required: true },
    enrollment: {
        type: Object,
        default: () => ({
            current: null,
            yearLevels: [],
            history: [],
            currentSchoolYear: "",
            enrollmentOpen: true,
            previousYearLevel: null,
        }),
    },
});

const showCancelModal = ref(false);
const isSubmittingEnrollment = ref(false);
const enrollmentForm = ref({
    year_level_id: "",
    enrollment_type: "",
    previous_gwa: "",
    previous_school: "",
});

const previousYearLevel = computed(
    () => props.enrollment?.previousYearLevel || null,
);

const admissionApproved = computed(
    () => props.user?.admission_status === "approved",
);

const isYearLevelAllowed = (level) => {
    if (!previousYearLevel.value) {
        return true;
    }
    const selectedRank = Number(level?.rank ?? 0);
    const previousRank = Number(previousYearLevel.value.rank ?? 0);
    return selectedRank >= previousRank;
};

const requiresPreviousSchool = computed(() => {
    const type = enrollmentForm.value.enrollment_type;
    return type && type !== "old";
});

watch(
    () => enrollmentForm.value.enrollment_type,
    (type) => {
        if (type === "old") {
            enrollmentForm.value.previous_school = "";
        }
    },
);

const formatEnrollmentType = (type) => {
    const types = {
        new: "New Student",
        old: "Old/Continuing Student",
        transferee: "Transferee",
        returnee: "Returnee",
    };
    return types[type] || type;
};

const submitEnrollment = () => {
    if (!admissionApproved.value) {
        toast.error(
            "You cannot enroll until your admission is approved by the registrar.",
        );
        return;
    }
    if (
        !enrollmentForm.value.year_level_id ||
        !enrollmentForm.value.enrollment_type
    ) {
        toast.error("Please fill in all required fields.");
        return;
    }
    const selectedLevel = props.enrollment.yearLevels.find(
        (level) =>
            String(level.id) === String(enrollmentForm.value.year_level_id),
    );
    if (selectedLevel && !isYearLevelAllowed(selectedLevel)) {
        toast.error(
            `You cannot enroll below your previous year level (${previousYearLevel.value.name}). Please select ${previousYearLevel.value.name} or higher.`,
        );
        return;
    }
    if (
        requiresPreviousSchool.value &&
        !enrollmentForm.value.previous_school?.trim()
    ) {
        toast.error("Please enter your previous / old school.");
        return;
    }
    isSubmittingEnrollment.value = true;
    router.post("/enrollment/submit", enrollmentForm.value, {
        onSuccess: () => {
            isSubmittingEnrollment.value = false;
            toast.success("Enrollment submitted!");
            enrollmentForm.value = {
                year_level_id: "",
                enrollment_type: "",
                previous_gwa: "",
                previous_school: "",
            };
        },
        onError: (errors) => {
            isSubmittingEnrollment.value = false;
            toast.error(Object.values(errors)[0] || "Failed to submit");
        },
    });
};

const cancelEnrollment = () => {
    router.post(
        "/enrollment/cancel",
        {},
        {
            onSuccess: () => {
                showCancelModal.value = false;
                toast.success("Enrollment cancelled");
            },
            onError: () => toast.error("Failed to cancel"),
        },
    );
};

onMounted(() => {
    if (page.props.flash?.success) toast.success(page.props.flash.success);
    if (page.props.flash?.error) toast.error(page.props.flash.error);
    if (props.user.previous_gwa)
        enrollmentForm.value.previous_gwa = props.user.previous_gwa;
    if (props.user.previous_school)
        enrollmentForm.value.previous_school = props.user.previous_school;
});
</script>

<style scoped>
.enrollment-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.enrollment-banner {
    background: #003366;
    color: white;
    padding: 0.85rem 1rem;
    border-bottom: 3px solid #c9a227;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
}

.banner-text p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.9;
}

.school-year-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #fff;
    padding: 0.3rem 0.55rem;
    font-weight: 600;
    font-size: 0.8rem;
}

.enrollment-status-card,
.enrollment-form-card,
.enrollment-history {
    background: white;
    border: 1px solid #c5c5c5;
}

.status-card-header,
.form-card-header,
.history-header {
    padding: 0.45rem 0.75rem;
    background: #003366;
    color: #fff;
    border-bottom: 3px solid #c9a227;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.status-icon-wrapper {
    display: none;
}

.status-text {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.status-label {
    font-size: 0.75rem;
    color: #d8e4f0;
    font-weight: 600;
}

.status-value {
    font-size: 1.05rem;
    font-weight: 700;
    color: #fff;
    text-transform: capitalize;
}

.enrollment-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0;
}

.detail-card {
    display: flex;
    flex-direction: column;
    padding: 0;
    background: #fff;
    border-right: 1px solid #e0e0e0;
    border-top: 1px solid #e0e0e0;
}

.detail-icon {
    display: none;
}

.detail-content {
    display: flex;
    flex-direction: column;
}

.detail-label {
    background: #e8eef4;
    color: #003366;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.35rem 0.65rem;
}

.detail-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: #222;
    padding: 0.55rem 0.65rem 0.65rem;
}

.status-actions {
    padding: 0.75rem 0.9rem;
    background: #fff;
    border-top: 1px solid #c5c5c5;
}

.pending-notice {
    color: #9a6700;
    font-size: 0.88rem;
}

.cancel-enrollment-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.8rem;
    background: #fff;
    color: #9b1c1c;
    border: 1px solid #9b1c1c;
    font-weight: 600;
    cursor: pointer;
}

.cancel-enrollment-btn:hover {
    background: #9b1c1c;
    color: #fff;
}

.form-header-icon {
    display: none;
}

.form-header-text h3 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}

.form-header-text p {
    margin: 0.15rem 0 0;
    color: #d8e4f0;
    font-size: 0.78rem;
}

.enrollment-form {
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-section-title {
    margin: 0;
    color: #003366;
    font-size: 0.95rem;
    font-weight: 700;
    padding-bottom: 0.25rem;
    border-bottom: 2px solid #c9a227;
}

.section-number {
    display: none;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.85rem;
}

.form-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
}

.form-group label .required {
    color: #9b1c1c;
}

.form-select,
.form-input {
    padding: 0.45rem 0.55rem;
    border: 1px solid #bdbdbd;
    font-size: 0.9rem;
}

.form-select:focus,
.form-input:focus {
    outline: none;
    border-color: #003366;
}

.input-hint {
    font-size: 0.78rem;
    color: #555;
}

.enrollment-notice {
    display: block;
    padding: 0.75rem 0.85rem;
    background: #fff;
    border: 1px solid #c5c5c5;
}

.notice-icon {
    display: none;
}

.notice-content strong {
    display: block;
    color: #003366;
    margin-bottom: 0.35rem;
}

.notice-content ul {
    margin: 0;
    padding-left: 1.2rem;
    color: #333;
    font-size: 0.88rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.75rem;
    border-top: 1px solid #d8d8d8;
}

.submit-enrollment-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    background: #003366;
    color: white;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.submit-enrollment-btn:hover:not(:disabled) {
    background: #00264d;
}

.submit-enrollment-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-content,
.btn-loading {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
}

.spin {
    animation: spin 0.8s linear infinite;
    flex-shrink: 0;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: inline-block;
    flex-shrink: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.history-header h3 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}

.history-count {
    font-size: 0.78rem;
    color: #d8e4f0;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table th,
.history-table td {
    padding: 0.5rem 0.75rem;
    text-align: left;
    border-top: 1px solid #e0e0e0;
}

.history-table th {
    background: #e8eef4;
    font-size: 0.75rem;
    font-weight: 700;
    color: #003366;
    text-transform: uppercase;
    border-top: none;
}

.history-table td {
    font-size: 0.88rem;
    color: #222;
}

.status-badge {
    display: inline-block;
    padding: 0.15rem 0.45rem;
    border: 1px solid #c5c5c5;
    background: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    color: #003366;
    text-transform: capitalize;
}

.status-badge.pending {
    color: #9a6700;
    border-color: #9a6700;
}

.status-badge.enrolled {
    color: #1f6b3a;
    border-color: #1f6b3a;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-container {
    background: white;
    border: 1px solid #c5c5c5;
    padding: 0;
    max-width: 420px;
    width: 90%;
    text-align: left;
}

.modal-container h3 {
    margin: 0;
    color: #fff;
    background: #9b1c1c;
    padding: 0.55rem 0.85rem;
    font-size: 0.95rem;
    border-bottom: 3px solid #c9a227;
}

.modal-container p {
    margin: 0;
    color: #333;
    padding: 0.85rem 1rem;
}

.modal-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    padding: 0.7rem 1rem;
    border-top: 1px solid #e0e0e0;
}

.btn-secondary {
    padding: 0.5rem 0.9rem;
    background: #fff;
    color: #333;
    border: 1px solid #bdbdbd;
    cursor: pointer;
    font-weight: 600;
}

.btn-danger {
    padding: 0.5rem 0.9rem;
    background: #9b1c1c;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

@media (max-width: 768px) {
    .enrollment-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .form-grid,
    .form-grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>
