<template>
    <AdminLayout
        title="Admissions"
        pageTitle="Admissions"
        currentPage="admissions"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Admissions</h2>
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Pending</div>
                    <div class="gov-stat-value">{{ pendingCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Approved</div>
                    <div class="gov-stat-value">{{ approvedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Rejected</div>
                    <div class="gov-stat-value">{{ rejectedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total Applicants</div>
                    <div class="gov-stat-value">{{ applicants.length }}</div>
                </div>
            </div>

            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by student name or LRN..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select v-model="statusFilter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Year Level Applying</th>
                            <th>School Year</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="applicant in filteredApplicants"
                            :key="applicant.id"
                        >
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm">
                                        {{ getInitials(applicant) }}
                                    </div>
                                    <div class="user-info-cell">
                                        <span class="user-name-cell">
                                            {{ applicant.last_name }},
                                            {{ applicant.first_name }}
                                            {{
                                                applicant.middle_name
                                                    ? applicant.middle_name.charAt(
                                                          0,
                                                      ) + "."
                                                    : ""
                                            }}
                                        </span>
                                        <span class="lrn-badge">{{
                                            applicant.lrn || "No LRN"
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="year-level-badge">
                                    {{ applicant.year_level_applying || "-" }}
                                </span>
                            </td>
                            <td>
                                {{ applicant.school_year_applying || "-" }}
                            </td>
                            <td>{{ formatDate(applicant.created_at) }}</td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="applicant.admission_status"
                                >
                                    {{ applicant.admission_status }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="remarks-cell"
                                    :title="applicant.admission_remarks || ''"
                                >
                                    {{ applicant.admission_remarks || "-" }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        title="View Details"
                                        @click="viewApplicant(applicant)"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        v-if="
                                            applicant.admission_status ===
                                                'pending' ||
                                            applicant.admission_status ===
                                                'rejected'
                                        "
                                        class="btn-icon approve"
                                        title="Approve"
                                        @click="openApproveModal(applicant)"
                                    >
                                        <Check :size="16" />
                                    </button>
                                    <button
                                        v-if="
                                            applicant.admission_status ===
                                            'pending'
                                        "
                                        class="btn-icon reject"
                                        title="Reject"
                                        @click="openRejectModal(applicant)"
                                    >
                                        <XCircle :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredApplicants.length === 0">
                            <td colspan="7" class="empty-table">
                                <div class="empty-message">
                                    <Users :size="40" />
                                    <p>No admission applications found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredApplicants.length }} of
                    {{ applicants.length }} applicants
                </span>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showViewModal"
                    class="modal-overlay"
                    @click.self="closeViewModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header enrollment-header">
                            <h3>
                                <Eye :size="22" />
                                Admission Details
                            </h3>
                            <button class="close-btn" @click="closeViewModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="view-details">
                                <div class="detail-header">
                                    <div class="detail-avatar">
                                        {{ getInitials(selectedApplicant) }}
                                    </div>
                                    <div class="detail-title">
                                        <h4>
                                            {{ selectedApplicant?.first_name }}
                                            {{ selectedApplicant?.middle_name }}
                                            {{ selectedApplicant?.last_name }}
                                        </h4>
                                        <span class="lrn-badge large"
                                            >LRN:
                                            {{
                                                selectedApplicant?.lrn || "N/A"
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Status</label>
                                        <span
                                            class="status-badge"
                                            :class="
                                                selectedApplicant?.admission_status
                                            "
                                        >
                                            {{
                                                selectedApplicant?.admission_status
                                            }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Email</label>
                                        <span>{{
                                            selectedApplicant?.email || "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Contact No.</label>
                                        <span>{{
                                            selectedApplicant?.phone_no || "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Gender</label>
                                        <span>{{
                                            selectedApplicant?.gender || "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Date of Birth</label>
                                        <span>{{
                                            formatDate(
                                                selectedApplicant?.date_of_birth,
                                            ) || "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Address</label>
                                        <span>{{
                                            formatAddress(selectedApplicant)
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Year Level Applying</label>
                                        <span>{{
                                            selectedApplicant?.year_level_applying ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Preferred Strand</label>
                                        <span>{{
                                            selectedApplicant?.preferred_strand ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>School Year</label>
                                        <span>{{
                                            selectedApplicant?.school_year_applying ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Previous School</label>
                                        <span>{{
                                            selectedApplicant?.previous_school ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Year Graduated</label>
                                        <span>{{
                                            selectedApplicant?.year_graduated ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Previous GWA</label>
                                        <span>{{
                                            selectedApplicant?.previous_gwa ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Guardian</label>
                                        <span>{{
                                            selectedApplicant?.guardian_full_name ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Guardian Contact</label>
                                        <span>{{
                                            selectedApplicant?.guardian_contact_no ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Date Applied</label>
                                        <span>{{
                                            formatDate(
                                                selectedApplicant?.created_at,
                                            )
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="
                                            selectedApplicant?.admission_reviewed_at
                                        "
                                        class="detail-item"
                                    >
                                        <label>Reviewed At</label>
                                        <span>{{
                                            formatDate(
                                                selectedApplicant.admission_reviewed_at,
                                            )
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="
                                            selectedApplicant?.admission_reviewed_by
                                        "
                                        class="detail-item"
                                    >
                                        <label>Reviewed By</label>
                                        <span>
                                            {{
                                                selectedApplicant
                                                    .admission_reviewed_by
                                                    .first_name
                                            }}
                                            {{
                                                selectedApplicant
                                                    .admission_reviewed_by
                                                    .last_name
                                            }}
                                        </span>
                                    </div>
                                    <div class="detail-item full-width">
                                        <label>Remarks</label>
                                        <span>{{
                                            selectedApplicant?.admission_remarks ||
                                            "-"
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeViewModal"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showApproveModal"
                    class="modal-overlay"
                    @click.self="closeApproveModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header success-header">
                            <h3>
                                <CheckCircle :size="22" />
                                Approve Admission
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeApproveModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="reject-form">
                                <div class="reject-info">
                                    <p>
                                        Are you sure you want to approve this
                                        admission? The student will be allowed
                                        to enroll.
                                    </p>
                                    <div class="delete-student-info">
                                        <strong
                                            >{{
                                                applicantToApprove?.first_name
                                            }}
                                            {{
                                                applicantToApprove?.last_name
                                            }}</strong
                                        >
                                        <span>{{
                                            applicantToApprove?.year_level_applying ||
                                            "Applicant"
                                        }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea
                                        v-model="approveRemarks"
                                        placeholder="Enter remarks (optional)..."
                                        rows="4"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeApproveModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="approveAdmission"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Approve
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showRejectModal"
                    class="modal-overlay"
                    @click.self="closeRejectModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header">
                            <h3>Reject Admission</h3>
                            <button class="close-btn" @click="closeRejectModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="reject-form">
                                <div class="reject-info">
                                    <p>
                                        You are about to reject the admission
                                        for:
                                    </p>
                                    <div class="delete-student-info">
                                        <strong
                                            >{{
                                                applicantToReject?.first_name
                                            }}
                                            {{
                                                applicantToReject?.last_name
                                            }}</strong
                                        >
                                        <span>{{
                                            applicantToReject?.year_level_applying ||
                                            "Applicant"
                                        }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label
                                        >Remarks
                                        <span class="required">*</span></label
                                    >
                                    <textarea
                                        v-model="rejectRemarks"
                                        placeholder="Enter reason for rejection..."
                                        rows="4"
                                        required
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeRejectModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="
                                    isSubmitting || !rejectRemarks.trim()
                                "
                                @click="rejectAdmission"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    Users,
    Search,
    Eye,
    Check,
    XCircle,
    CheckCircle,
    X,
    Loader2,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    applicants: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref("");
const statusFilter = ref("pending");
const isSubmitting = ref(false);

const showViewModal = ref(false);
const selectedApplicant = ref(null);

const showApproveModal = ref(false);
const applicantToApprove = ref(null);
const approveRemarks = ref("");

const showRejectModal = ref(false);
const applicantToReject = ref(null);
const rejectRemarks = ref("");

const pendingCount = computed(() => {
    return props.applicants.filter((a) => a.admission_status === "pending")
        .length;
});

const approvedCount = computed(() => {
    return props.applicants.filter((a) => a.admission_status === "approved")
        .length;
});

const rejectedCount = computed(() => {
    return props.applicants.filter((a) => a.admission_status === "rejected")
        .length;
});

const filteredApplicants = computed(() => {
    let result = props.applicants;

    if (statusFilter.value !== "all") {
        result = result.filter(
            (a) => a.admission_status === statusFilter.value,
        );
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((a) => {
            const fullName =
                `${a.first_name} ${a.middle_name || ""} ${a.last_name}`.toLowerCase();
            const lrn = a.lrn?.toLowerCase() || "";
            const email = a.email?.toLowerCase() || "";
            return (
                fullName.includes(query) ||
                lrn.includes(query) ||
                email.includes(query)
            );
        });
    }

    return result;
});

const getInitials = (user) => {
    if (!user) return "?";
    return (
        (user.first_name?.charAt(0) || "") + (user.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
};

const formatAddress = (applicant) => {
    if (!applicant) return "-";
    const parts = [
        applicant.barangay,
        applicant.municipality,
        applicant.province,
    ].filter(Boolean);
    return parts.length ? parts.join(", ") : "-";
};

const viewApplicant = (applicant) => {
    selectedApplicant.value = applicant;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedApplicant.value = null;
};

const openApproveModal = (applicant) => {
    applicantToApprove.value = applicant;
    approveRemarks.value = applicant.admission_remarks || "";
    showApproveModal.value = true;
};

const closeApproveModal = () => {
    showApproveModal.value = false;
    applicantToApprove.value = null;
    approveRemarks.value = "";
};

const approveAdmission = () => {
    if (!applicantToApprove.value) return;
    isSubmitting.value = true;

    router.post(
        `/admin/admissions/${applicantToApprove.value.id}/approve`,
        {
            remarks: approveRemarks.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    "Admission approved. The student may now enroll.",
                );
                closeApproveModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to approve admission.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openRejectModal = (applicant) => {
    applicantToReject.value = applicant;
    rejectRemarks.value = "";
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    applicantToReject.value = null;
    rejectRemarks.value = "";
};

const rejectAdmission = () => {
    if (!applicantToReject.value || !rejectRemarks.value.trim()) return;
    isSubmitting.value = true;

    router.post(
        `/admin/admissions/${applicantToReject.value.id}/reject`,
        {
            remarks: rejectRemarks.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Admission rejected.");
                closeRejectModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to reject admission.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.reject-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.reject-info {
    text-align: left;
}

.reject-info p {
    margin: 0 0 1rem 0;
    color: #333;
}

.remarks-cell {
    display: inline-block;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #333;
}
</style>
