<template>
    <AdminLayout
        title="Requirements"
        pageTitle="Student Requirements"
        currentPage="requirements"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Student Requirements</h2>
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Submitted</div>
                    <div class="gov-stat-value">{{ submittedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Verified</div>
                    <div class="gov-stat-value">{{ verifiedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Pending</div>
                    <div class="gov-stat-value">{{ pendingCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Rejected</div>
                    <div class="gov-stat-value">{{ rejectedCount }}</div>
                </div>
            </div>

            <!-- Header Actions -->
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
                            <option value="submitted">Submitted</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Requirements Table -->
            <div class="data-table-container">
                <table class="data-table collapsible-table">
                    <thead>
                        <tr>
                            <th style="width: 40px"></th>
                            <th>Student</th>
                            <th>Form 137</th>
                            <th>Good Moral</th>
                            <th>Birth Certificate</th>
                            <th>Accomplishment</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template
                            v-for="student in filteredStudents"
                            :key="student.user_id"
                        >
                            <tr
                                class="student-row"
                                :class="{
                                    expanded: expandedStudents.has(
                                        student.user_id,
                                    ),
                                }"
                            >
                                <td>
                                    <button
                                        class="expand-btn"
                                        @click="toggleStudent(student.user_id)"
                                    >
                                        <ChevronDown
                                            v-if="
                                                expandedStudents.has(
                                                    student.user_id,
                                                )
                                            "
                                            :size="18"
                                        />
                                        <ChevronRight v-else :size="18" />
                                    </button>
                                </td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">
                                            {{ getInitials(student.user) }}
                                        </div>
                                        <div class="user-info-cell">
                                            <span class="user-name-cell">
                                                {{ student.user?.last_name }},
                                                {{ student.user?.first_name }}
                                                {{
                                                    student.user?.middle_name
                                                        ? student.user.middle_name.charAt(
                                                              0,
                                                          ) + "."
                                                        : ""
                                                }}
                                            </span>
                                            <span class="lrn-badge">{{
                                                student.user?.lrn
                                            }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="req-status-badge"
                                        :class="
                                            getRequirementStatus(
                                                student,
                                                'form_137',
                                            )
                                        "
                                    >
                                        {{
                                            formatStatus(
                                                getRequirementStatus(
                                                    student,
                                                    "form_137",
                                                ),
                                            )
                                        }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="req-status-badge"
                                        :class="
                                            getRequirementStatus(
                                                student,
                                                'good_moral_certificate',
                                            )
                                        "
                                    >
                                        {{
                                            formatStatus(
                                                getRequirementStatus(
                                                    student,
                                                    "good_moral_certificate",
                                                ),
                                            )
                                        }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="req-status-badge"
                                        :class="
                                            getRequirementStatus(
                                                student,
                                                'birth_certificate',
                                            )
                                        "
                                    >
                                        {{
                                            formatStatus(
                                                getRequirementStatus(
                                                    student,
                                                    "birth_certificate",
                                                ),
                                            )
                                        }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="req-status-badge"
                                        :class="
                                            getRequirementStatus(
                                                student,
                                                'accomplishment_credentials',
                                            )
                                        "
                                    >
                                        {{
                                            formatStatus(
                                                getRequirementStatus(
                                                    student,
                                                    "accomplishment_credentials",
                                                ),
                                            )
                                        }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress-cell">
                                        <div class="progress-bar">
                                            <div
                                                class="progress-fill"
                                                :style="{
                                                    width:
                                                        getProgressPercentage(
                                                            student,
                                                        ) + '%',
                                                }"
                                                :class="
                                                    getProgressClass(student)
                                                "
                                            ></div>
                                        </div>
                                        <span class="progress-text">
                                            {{ getCompletedCount(student) }}/{{
                                                requiredTypesForStudent(student)
                                                    .length
                                            }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-if="expandedStudents.has(student.user_id)"
                                class="expanded-row"
                            >
                                <td colspan="7">
                                    <div class="requirements-detail">
                                        <div class="requirement-cards">
                                            <div
                                                v-for="reqType in requiredTypesForStudent(
                                                    student,
                                                )"
                                                :key="reqType.key"
                                                class="requirement-card"
                                                :class="
                                                    getRequirementStatus(
                                                        student,
                                                        reqType.key,
                                                    )
                                                "
                                            >
                                                <div class="req-card-header">
                                                    <FileText
                                                        :size="20"
                                                        class="req-icon"
                                                    />
                                                    <span class="req-name">{{
                                                        reqType.label
                                                    }}</span>
                                                </div>
                                                <div class="req-card-body">
                                                    <span
                                                        class="req-status-badge large"
                                                        :class="
                                                            getRequirementStatus(
                                                                student,
                                                                reqType.key,
                                                            )
                                                        "
                                                    >
                                                        {{
                                                            formatStatus(
                                                                getRequirementStatus(
                                                                    student,
                                                                    reqType.key,
                                                                ),
                                                            )
                                                        }}
                                                    </span>
                                                    <p
                                                        v-if="
                                                            getRequirement(
                                                                student,
                                                                reqType.key,
                                                            )?.remarks
                                                        "
                                                        class="req-remarks"
                                                    >
                                                        {{
                                                            getRequirement(
                                                                student,
                                                                reqType.key,
                                                            ).remarks
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="req-card-actions">
                                                    <button
                                                        v-if="
                                                            getRequirement(
                                                                student,
                                                                reqType.key,
                                                            )?.file_path
                                                        "
                                                        class="btn-icon view"
                                                        @click="
                                                            viewFile(
                                                                getRequirement(
                                                                    student,
                                                                    reqType.key,
                                                                ),
                                                            )
                                                        "
                                                        title="View File"
                                                    >
                                                        <Eye :size="16" />
                                                    </button>
                                                    <button
                                                        v-if="
                                                            getRequirement(
                                                                student,
                                                                reqType.key,
                                                            )?.file_path
                                                        "
                                                        class="btn-icon download"
                                                        @click="
                                                            downloadFile(
                                                                getRequirement(
                                                                    student,
                                                                    reqType.key,
                                                                ),
                                                            )
                                                        "
                                                        title="Download File"
                                                    >
                                                        <Download :size="16" />
                                                    </button>
                                                    <button
                                                        v-if="
                                                            getRequirementStatus(
                                                                student,
                                                                reqType.key,
                                                            ) === 'submitted'
                                                        "
                                                        class="btn-icon approve"
                                                        @click="
                                                            openVerifyModal(
                                                                getRequirement(
                                                                    student,
                                                                    reqType.key,
                                                                ),
                                                                student,
                                                            )
                                                        "
                                                        title="Verify"
                                                    >
                                                        <Check :size="16" />
                                                    </button>
                                                    <button
                                                        v-if="
                                                            getRequirementStatus(
                                                                student,
                                                                reqType.key,
                                                            ) === 'submitted'
                                                        "
                                                        class="btn-icon reject"
                                                        @click="
                                                            openRejectModal(
                                                                getRequirement(
                                                                    student,
                                                                    reqType.key,
                                                                ),
                                                                student,
                                                            )
                                                        "
                                                        title="Reject"
                                                    >
                                                        <XCircle :size="16" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="7" class="empty-table">
                                <div class="empty-message">
                                    <FileCheck :size="40" />
                                    <p>No student requirements found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredStudents.length }} of
                    {{ studentRequirements.length }} students
                </span>
            </div>
        </div>

        <!-- View File Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showViewModal"
                    class="modal-overlay"
                    @click.self="closeViewModal"
                >
                    <div class="modal-container fullscreen">
                        <div class="modal-header requirement-header">
                            <h3>
                                <Eye :size="22" />
                                View Requirement
                            </h3>
                            <button class="close-btn" @click="closeViewModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body file-preview-body">
                            <div class="file-info">
                                <p>
                                    <strong>File:</strong>
                                    {{ selectedRequirement?.original_filename }}
                                </p>
                                <p>
                                    <strong>Type:</strong>
                                    {{
                                        formatRequirementType(
                                            selectedRequirement?.requirement_type,
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    <span
                                        class="req-status-badge"
                                        :class="selectedRequirement?.status"
                                    >
                                        {{
                                            formatStatus(
                                                selectedRequirement?.status,
                                            )
                                        }}
                                    </span>
                                </p>
                            </div>
                            <div class="file-preview">
                                <!-- Image Preview with Zoom -->
                                <template
                                    v-if="
                                        isImageFile(
                                            selectedRequirement?.file_path,
                                        )
                                    "
                                >
                                    <div class="zoom-controls">
                                        <button
                                            class="zoom-btn"
                                            @click="zoomOut"
                                            :disabled="zoomLevel <= 25"
                                            title="Zoom Out"
                                        >
                                            <ZoomOut :size="18" />
                                        </button>
                                        <span class="zoom-level"
                                            >{{ zoomLevel }}%</span
                                        >
                                        <button
                                            class="zoom-btn"
                                            @click="zoomIn"
                                            :disabled="zoomLevel >= 300"
                                            title="Zoom In"
                                        >
                                            <ZoomIn :size="18" />
                                        </button>
                                        <span class="controls-divider"></span>
                                        <button
                                            class="zoom-btn"
                                            @click="rotateLeft"
                                            title="Rotate Left"
                                        >
                                            <RotateCcw :size="18" />
                                        </button>
                                        <button
                                            class="zoom-btn"
                                            @click="rotateRight"
                                            title="Rotate Right"
                                        >
                                            <RotateCw :size="18" />
                                        </button>
                                        <span class="controls-divider"></span>
                                        <button
                                            class="zoom-btn"
                                            @click="resetView"
                                            title="Reset View"
                                        >
                                            <RefreshCw :size="18" />
                                        </button>
                                    </div>
                                    <div class="image-container">
                                        <img
                                            :src="
                                                '/storage/' +
                                                selectedRequirement?.file_path
                                            "
                                            :style="{
                                                transform: `scale(${zoomLevel / 100}) rotate(${rotationDegree}deg)`,
                                            }"
                                            class="preview-image"
                                            alt="Requirement preview"
                                        />
                                    </div>
                                </template>
                                <!-- PDF Preview -->
                                <iframe
                                    v-else-if="
                                        isPdfFile(
                                            selectedRequirement?.file_path,
                                        )
                                    "
                                    :src="
                                        '/storage/' +
                                        selectedRequirement?.file_path
                                    "
                                    class="preview-iframe"
                                ></iframe>
                                <!-- Fallback -->
                                <div v-else class="preview-placeholder">
                                    <FileText :size="60" />
                                    <p>
                                        Preview not available for this file type
                                    </p>
                                    <button
                                        class="btn-primary"
                                        @click="
                                            downloadFile(selectedRequirement)
                                        "
                                    >
                                        <Download :size="18" />
                                        Download File
                                    </button>
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

        <!-- Verify Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showVerifyModal"
                    class="modal-overlay"
                    @click.self="closeVerifyModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header success-header">
                            <h3>
                                <FileCheck :size="22" />
                                Verify Requirement
                            </h3>
                            <button class="close-btn" @click="closeVerifyModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <div class="confirm-icon success">
                                    <Check :size="40" />
                                </div>
                                <p>
                                    Are you sure you want to verify this
                                    requirement?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{ selectedStudent?.user?.first_name }}
                                        {{
                                            selectedStudent?.user?.last_name
                                        }}</strong
                                    >
                                    <span>{{
                                        formatRequirementType(
                                            requirementToVerify?.requirement_type,
                                        )
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeVerifyModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="verifyRequirement"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Verify
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Reject Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showRejectModal"
                    class="modal-overlay"
                    @click.self="closeRejectModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header danger">
                            <h3>
                                <XCircle :size="22" />
                                Reject Requirement
                            </h3>
                            <button class="close-btn" @click="closeRejectModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="reject-form">
                                <div class="reject-info">
                                    <p>
                                        You are about to reject the requirement
                                        for:
                                    </p>
                                    <div class="delete-student-info">
                                        <strong
                                            >{{
                                                selectedStudent?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                selectedStudent?.user?.last_name
                                            }}</strong
                                        >
                                        <span>{{
                                            formatRequirementType(
                                                requirementToReject?.requirement_type,
                                            )
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
                                @click="rejectRequirement"
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
    FileCheck,
    Search,
    Eye,
    Check,
    XCircle,
    X,
    Loader2,
    ChevronDown,
    ChevronRight,
    Download,
    FileText,
    ZoomIn,
    ZoomOut,
    RotateCcw,
    RotateCw,
    RefreshCw,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    studentRequirements: {
        type: Array,
        default: () => [],
    },
});

// Requirement types
const requirementTypes = [
    { key: "form_137", label: "Form 137" },
    { key: "good_moral_certificate", label: "Good Moral Certificate" },
    { key: "birth_certificate", label: "Birth Certificate" },
    { key: "accomplishment_credentials", label: "Accomplishment Credentials" },
];

const isSeniorHighYearLevel = (yearLevel) => {
    const value = (yearLevel || "").toLowerCase();
    return value.includes("grade 11") || value.includes("grade 12") || value.includes("senior");
};

const requiredTypesForStudent = (student) => {
    const yearLevel = student.user?.year_level_applying;
    const keys = isSeniorHighYearLevel(yearLevel)
        ? ["accomplishment_credentials", "birth_certificate"]
        : [
              "form_137",
              "good_moral_certificate",
              "birth_certificate",
          ];

    return requirementTypes.filter((type) => keys.includes(type.key));
};

// State
const searchQuery = ref("");
const statusFilter = ref("all");
const isSubmitting = ref(false);
const expandedStudents = ref(new Set());

// View Modal
const showViewModal = ref(false);
const selectedRequirement = ref(null);
const zoomLevel = ref(100);
const rotationDegree = ref(0);

// Verify Modal
const showVerifyModal = ref(false);
const requirementToVerify = ref(null);
const selectedStudent = ref(null);

// Reject Modal
const showRejectModal = ref(false);
const requirementToReject = ref(null);
const rejectRemarks = ref("");

// Computed - Count all requirements across all students
const submittedCount = computed(() => {
    return props.studentRequirements.reduce((count, student) => {
        return (
            count +
            (student.requirements?.filter((r) => r.status === "submitted")
                .length || 0)
        );
    }, 0);
});

const verifiedCount = computed(() => {
    return props.studentRequirements.reduce((count, student) => {
        return (
            count +
            (student.requirements?.filter((r) => r.status === "verified")
                .length || 0)
        );
    }, 0);
});

const pendingCount = computed(() => {
    return props.studentRequirements.reduce((count, student) => {
        return (
            count +
            (student.requirements?.filter((r) => r.status === "pending")
                .length || 0)
        );
    }, 0);
});

const rejectedCount = computed(() => {
    return props.studentRequirements.reduce((count, student) => {
        return (
            count +
            (student.requirements?.filter((r) => r.status === "rejected")
                .length || 0)
        );
    }, 0);
});

const filteredStudents = computed(() => {
    let result = props.studentRequirements;

    // Filter by status
    if (statusFilter.value !== "all") {
        result = result.filter((student) => {
            return student.requirements?.some(
                (r) => r.status === statusFilter.value,
            );
        });
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((student) => {
            const fullName =
                `${student.user?.first_name} ${student.user?.middle_name || ""} ${student.user?.last_name}`.toLowerCase();
            const lrn = student.user?.lrn?.toLowerCase() || "";
            return fullName.includes(query) || lrn.includes(query);
        });
    }

    return result;
});

// Methods
const getInitials = (user) => {
    if (!user) return "?";
    return (
        (user.first_name?.charAt(0) || "") + (user.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const toggleStudent = (studentId) => {
    if (expandedStudents.value.has(studentId)) {
        expandedStudents.value.delete(studentId);
    } else {
        expandedStudents.value.add(studentId);
    }
    // Trigger reactivity
    expandedStudents.value = new Set(expandedStudents.value);
};

const getRequirement = (student, type) => {
    return student.requirements?.find((r) => r.requirement_type === type);
};

const isRequirementComplete = (req) => {
    if (!req) return false;
    if (req.file_path) return true;
    return req.status === "submitted" || req.status === "verified";
};

const getRequirementStatus = (student, type) => {
    const required = requiredTypesForStudent(student).some(
        (item) => item.key === type,
    );
    if (!required) return "na";
    const req = getRequirement(student, type);
    if (!req) return "pending";
    if (req.status === "pending" && req.file_path) return "submitted";
    return req.status || "pending";
};

const getCompletedCount = (student) => {
    return requiredTypesForStudent(student).filter((type) =>
        isRequirementComplete(getRequirement(student, type.key)),
    ).length;
};

const getProgressPercentage = (student) => {
    const total = requiredTypesForStudent(student).length || 1;
    return (getCompletedCount(student) / total) * 100;
};

const getProgressClass = (student) => {
    const percentage = getProgressPercentage(student);
    if (percentage === 100) return "complete";
    if (percentage >= 50) return "half";
    return "low";
};

const formatStatus = (status) => {
    if (!status) return "Pending";
    if (status === "na") return "N/A";
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatRequirementType = (type) => {
    const found = requirementTypes.find((r) => r.key === type);
    return found?.label || type;
};

const isViewableFile = (filePath) => {
    if (!filePath) return false;
    const ext = filePath.split(".").pop().toLowerCase();
    return ["pdf", "jpg", "jpeg", "png", "gif"].includes(ext);
};

const isImageFile = (filePath) => {
    if (!filePath) return false;
    const ext = filePath.split(".").pop().toLowerCase();
    return ["jpg", "jpeg", "png", "gif", "webp"].includes(ext);
};

const isPdfFile = (filePath) => {
    if (!filePath) return false;
    const ext = filePath.split(".").pop().toLowerCase();
    return ext === "pdf";
};

// View File Modal
const viewFile = (requirement) => {
    selectedRequirement.value = requirement;
    zoomLevel.value = 100;
    rotationDegree.value = 0;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedRequirement.value = null;
    zoomLevel.value = 100;
    rotationDegree.value = 0;
};

// Zoom Controls
const zoomIn = () => {
    if (zoomLevel.value < 300) {
        zoomLevel.value += 25;
    }
};

const zoomOut = () => {
    if (zoomLevel.value > 25) {
        zoomLevel.value -= 25;
    }
};

// Rotation Controls
const rotateLeft = () => {
    rotationDegree.value = (rotationDegree.value - 90) % 360;
};

const rotateRight = () => {
    rotationDegree.value = (rotationDegree.value + 90) % 360;
};

const resetView = () => {
    zoomLevel.value = 100;
    rotationDegree.value = 0;
};

// Download File
const downloadFile = (requirement) => {
    if (!requirement?.file_path) return;
    const link = document.createElement("a");
    link.href = "/storage/" + requirement.file_path;
    link.download = requirement.original_filename || "download";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Verify Modal
const openVerifyModal = (requirement, student) => {
    requirementToVerify.value = requirement;
    selectedStudent.value = student;
    showVerifyModal.value = true;
};

const closeVerifyModal = () => {
    showVerifyModal.value = false;
    requirementToVerify.value = null;
    selectedStudent.value = null;
};

const verifyRequirement = () => {
    if (!requirementToVerify.value) return;
    isSubmitting.value = true;

    router.put(
        `/admin/requirements/${requirementToVerify.value.id}/verify`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Requirement verified successfully!");
                closeVerifyModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to verify requirement.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

// Reject Modal
const openRejectModal = (requirement, student) => {
    requirementToReject.value = requirement;
    selectedStudent.value = student;
    rejectRemarks.value = "";
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    requirementToReject.value = null;
    selectedStudent.value = null;
    rejectRemarks.value = "";
};

const rejectRequirement = () => {
    if (!requirementToReject.value || !rejectRemarks.value.trim()) return;
    isSubmitting.value = true;

    router.put(
        `/admin/requirements/${requirementToReject.value.id}/reject`,
        {
            remarks: rejectRemarks.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Requirement rejected.");
                closeRejectModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to reject requirement.");
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

.collapsible-table .student-row {
    cursor: pointer;
}

.collapsible-table .student-row:hover {
    background: #f4f7fb;
}

.collapsible-table .student-row.expanded {
    background: #e8eef4;
    border-bottom: none;
}

.collapsible-table .student-row.expanded td {
    border-bottom: none;
}

.expand-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: #003366;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.expand-btn:hover {
    background: #e8eef4;
}

.expanded-row {
    background: #f7f7f7;
}

.expanded-row td {
    padding: 0 !important;
    border-bottom: 1px solid #c5c5c5;
}

.requirements-detail {
    padding: 1rem 1.25rem 1.25rem;
    background: #f4f7fb;
    border-top: 1px solid #c5c5c5;
}

.requirement-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

.requirement-card,
.requirement-card.pending,
.requirement-card.submitted,
.requirement-card.verified,
.requirement-card.rejected {
    background: #fff;
    padding: 0.85rem;
    border: 1px solid #c5c5c5;
}

.requirement-card.pending {
    border-left: 4px solid #9a6700;
}

.requirement-card.submitted {
    border-left: 4px solid #003366;
}

.requirement-card.verified {
    border-left: 4px solid #1f6b3a;
}

.requirement-card.rejected {
    border-left: 4px solid #9b1c1c;
}

.req-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #ddd;
}

.req-icon {
    color: #003366;
}

.req-name {
    font-weight: 600;
    color: #003366;
    font-size: 0.9rem;
}

.req-card-body {
    margin-bottom: 0.75rem;
}

.req-remarks {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #555;
    font-style: italic;
    padding: 0.5rem;
    background: #f7f7f7;
    border: 1px solid #ddd;
}

.req-card-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.progress-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background: #e8eef4;
    overflow: hidden;
    min-width: 80px;
    border: 1px solid #c5c5c5;
}

.progress-fill {
    height: 100%;
    width: 0;
    background: #9a6700;
    transition: width 0.25s ease;
}

.progress-fill.half {
    background: #c9a227;
}

.progress-fill.complete {
    background: #1f6b3a;
}

.progress-text {
    font-size: 0.8rem;
    color: #555;
    font-weight: 600;
    white-space: nowrap;
}

.btn-icon.download {
    background: #fff;
    color: #003366;
    border: 1px solid #003366;
}

.btn-icon.download:hover {
    background: #003366;
    color: #fff;
}

.file-preview-body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.file-info {
    padding: 0.85rem;
    background: #f7f7f7;
    border: 1px solid #ddd;
}

.file-info p {
    margin: 0.25rem 0;
    font-size: 0.9rem;
    color: #333;
}

.file-preview {
    flex: 1;
    min-height: 400px;
    border: 1px solid #c5c5c5;
    overflow: hidden;
}

.preview-iframe {
    width: 100%;
    height: 100%;
    min-height: 400px;
    border: none;
}

.preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 400px;
    color: #555;
    gap: 1rem;
}

.modal-container.fullscreen {
    width: 100vw;
    height: 100vh;
    max-width: 100vw;
    max-height: 100vh;
    display: flex;
    flex-direction: column;
}

.modal-container.fullscreen .modal-body {
    flex: 1;
    overflow: auto;
}

.modal-container.fullscreen .file-preview-body {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.modal-container.fullscreen .file-preview {
    flex: 1;
    min-height: 0;
    height: 100%;
}

.preview-placeholder p {
    margin: 0;
}

.zoom-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #e8eef4;
    border-bottom: 1px solid #c5c5c5;
}

.zoom-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1px solid #c5c5c5;
    background: #fff;
    cursor: pointer;
    color: #003366;
}

.zoom-btn:hover:not(:disabled) {
    background: #003366;
    color: #fff;
}

.zoom-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.zoom-level {
    min-width: 60px;
    text-align: center;
    font-weight: 600;
    color: #003366;
    font-size: 0.9rem;
}

.controls-divider {
    width: 1px;
    height: 24px;
    background: #c5c5c5;
    margin: 0 0.25rem;
}

.image-container {
    flex: 1;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ececec;
    padding: 1rem;
}

.preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transform-origin: center center;
}

.modal-container.fullscreen .image-container {
    height: calc(100% - 53px);
}

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

.confirm-icon {
    display: none;
}

@media (max-width: 768px) {
    .requirement-cards {
        grid-template-columns: 1fr;
    }
}
</style>
