<template>
    <AdminLayout
        title="Subjects"
        pageTitle="Subjects"
        currentPage="subjects"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Subjects</h2>
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Core</div>
                    <div class="gov-stat-value">{{ coreCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Specialized</div>
                    <div class="gov-stat-value">{{ specializedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Applied</div>
                    <div class="gov-stat-value">{{ appliedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Elective</div>
                    <div class="gov-stat-value">{{ electiveCount }}</div>
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
                            placeholder="Search subjects by name or code..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select
                            v-model="subjectTypeFilter"
                            class="filter-select"
                        >
                            <option value="all">All Types</option>
                            <option value="core">Core</option>
                            <option value="specialized">Specialized</option>
                            <option value="applied">Applied</option>
                            <option value="elective">Elective</option>
                        </select>
                        <select v-model="yearLevelFilter" class="filter-select">
                            <option value="all">All Year Levels</option>
                            <option
                                v-for="level in yearLevels"
                                :key="level.id"
                                :value="level.id"
                            >
                                {{ level.name }}
                            </option>
                        </select>
                    </div>
                    <button
                        class="btn-primary"
                        @click="openSubjectModal('add')"
                    >
                        <Plus :size="18" />
                        Add Subject
                    </button>
                </div>
            </div>

            <!-- Subjects Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Year Level</th>
                            <th>Type</th>
                            <th>Semester</th>
                            <th>Units</th>
                            <th>Hours/Week</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="subject in filteredSubjects"
                            :key="subject.id"
                        >
                            <td>
                                <div class="subject-cell">
                                    <div
                                        class="subject-icon-sm"
                                        :class="subject.subject_type"
                                    >
                                        <BookOpen :size="18" />
                                    </div>
                                    <div class="subject-info-cell">
                                        <span class="subject-name-cell">{{
                                            subject.name
                                        }}</span>
                                        <span class="subject-code">{{
                                            subject.code
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="year-level-badge">
                                    {{ subject.year_level?.name || "-" }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="type-badge"
                                    :class="subject.subject_type"
                                >
                                    {{
                                        formatSubjectType(subject.subject_type)
                                    }}
                                </span>
                            </td>
                            <td>
                                <span class="semester-badge">
                                    {{ formatSemester(subject.semester) }}
                                </span>
                            </td>
                            <td>{{ subject.units }}</td>
                            <td>{{ subject.hours_per_week }}</td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        subject.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        subject.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewSubject(subject)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openSubjectModal('edit', subject)
                                        "
                                        title="Edit Subject"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon delete"
                                        @click="confirmDeleteSubject(subject)"
                                        title="Delete Subject"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredSubjects.length === 0">
                            <td colspan="8" class="empty-table">
                                <div class="empty-message">
                                    <BookOpen :size="40" />
                                    <p>No subjects found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredSubjects.length }} of
                    {{ subjects.length }} subjects
                </span>
            </div>
        </div>

        <!-- Subject Modal (Add/Edit/View) -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showSubjectModal"
                    class="modal-overlay"
                    @click.self="closeSubjectModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header subject-header">
                            <h3>
                                <Plus
                                    v-if="subjectModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="subjectModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    subjectModalMode === "add"
                                        ? "Add New Subject"
                                        : subjectModalMode === "edit"
                                          ? "Edit Subject"
                                          : "Subject Details"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeSubjectModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- View Mode -->
                            <div
                                v-if="subjectModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div
                                        class="detail-avatar subject"
                                        :class="selectedSubject.subject_type"
                                    >
                                        <BookOpen :size="28" />
                                    </div>
                                    <div class="detail-title">
                                        <h4>{{ selectedSubject.name }}</h4>
                                        <span class="code-badge">{{
                                            selectedSubject.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Year Level</label>
                                        <span>{{
                                            selectedSubject.year_level?.name ||
                                            "Not set"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Subject Type</label>
                                        <span
                                            class="type-badge"
                                            :class="
                                                selectedSubject.subject_type
                                            "
                                        >
                                            {{
                                                formatSubjectType(
                                                    selectedSubject.subject_type,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Semester</label>
                                        <span>{{
                                            formatSemester(
                                                selectedSubject.semester,
                                            )
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Units</label>
                                        <span>{{ selectedSubject.units }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Hours per Week</label>
                                        <span>{{
                                            selectedSubject.hours_per_week
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status</label>
                                        <span
                                            class="status-badge"
                                            :class="
                                                selectedSubject.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            "
                                        >
                                            {{
                                                selectedSubject.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="detail-item full-width"
                                        v-if="selectedSubject.description"
                                    >
                                        <label>Description</label>
                                        <span>{{
                                            selectedSubject.description
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add/Edit Form -->
                            <form
                                v-else
                                @submit.prevent="submitSubjectForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Subject Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="subjectForm.name"
                                            type="text"
                                            required
                                            placeholder="Enter subject name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Subject Code
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="subjectForm.code"
                                            type="text"
                                            required
                                            placeholder="e.g., MATH101"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Year Level
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="subjectForm.year_level_id"
                                            required
                                        >
                                            <option value="">
                                                Select year level
                                            </option>
                                            <option
                                                v-for="level in yearLevels"
                                                :key="level.id"
                                                :value="level.id"
                                            >
                                                {{ level.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Subject Type
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="subjectForm.subject_type"
                                            required
                                        >
                                            <option value="">
                                                Select type
                                            </option>
                                            <option value="core">Core</option>
                                            <option value="specialized">
                                                Specialized
                                            </option>
                                            <option value="applied">
                                                Applied
                                            </option>
                                            <option value="elective">
                                                Elective
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Units
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="subjectForm.units"
                                            type="number"
                                            min="1"
                                            required
                                            placeholder="Number of units"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Hours per Week
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="subjectForm.hours_per_week"
                                            type="number"
                                            min="1"
                                            required
                                            placeholder="Hours per week"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Semester
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="subjectForm.semester"
                                            required
                                        >
                                            <option value="">
                                                Select semester
                                            </option>
                                            <option value="first">
                                                First Semester
                                            </option>
                                            <option value="second">
                                                Second Semester
                                            </option>
                                            <option value="full_year">
                                                Full Year
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select v-model="subjectForm.is_active">
                                            <option :value="true">
                                                Active
                                            </option>
                                            <option :value="false">
                                                Inactive
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group full-width">
                                        <label>Description</label>
                                        <textarea
                                            v-model="subjectForm.description"
                                            rows="3"
                                            placeholder="Enter subject description (optional)"
                                        ></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeSubjectModal"
                            >
                                {{
                                    subjectModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="subjectModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitSubjectForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    subjectModalMode === "add"
                                        ? "Create Subject"
                                        : "Save Changes"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showDeleteModal"
                    class="modal-overlay"
                    @click.self="showDeleteModal = false"
                >
                    <div class="modal-container small">
                        <div class="modal-header danger">
                            <h3>
                                <AlertTriangle :size="22" />
                                Confirm Delete
                            </h3>
                            <button
                                class="close-btn"
                                @click="showDeleteModal = false"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="delete-warning">
                                <div class="warning-icon">
                                    <Trash2 :size="40" />
                                </div>
                                <p>
                                    Are you sure you want to delete this
                                    subject?
                                </p>
                                <div class="delete-subject-info">
                                    <strong>{{ subjectToDelete?.name }}</strong>
                                    <span
                                        >Code: {{ subjectToDelete?.code }}</span
                                    >
                                </div>
                                <p class="warning-text">
                                    This action cannot be undone. All related
                                    data will be permanently deleted.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="showDeleteModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="isSubmitting"
                                @click="deleteSubject"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Subject
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
    BookOpen,
    Search,
    Plus,
    Eye,
    Pencil,
    Trash2,
    X,
    Loader2,
    AlertTriangle,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    yearLevels: {
        type: Array,
        default: () => [],
    },
});

// Search & Filter State
const searchQuery = ref("");
const subjectTypeFilter = ref("all");
const yearLevelFilter = ref("all");

// Modal State
const showSubjectModal = ref(false);
const subjectModalMode = ref("add");
const selectedSubject = ref(null);
const showDeleteModal = ref(false);
const subjectToDelete = ref(null);
const isSubmitting = ref(false);

// Form State
const subjectForm = ref({
    name: "",
    code: "",
    description: "",
    year_level_id: "",
    subject_type: "",
    units: "",
    hours_per_week: "",
    semester: "",
    is_active: true,
});

// Computed Stats
const coreCount = computed(() => {
    return props.subjects.filter((s) => s.subject_type === "core").length;
});

const specializedCount = computed(() => {
    return props.subjects.filter((s) => s.subject_type === "specialized")
        .length;
});

const appliedCount = computed(() => {
    return props.subjects.filter((s) => s.subject_type === "applied").length;
});

const electiveCount = computed(() => {
    return props.subjects.filter((s) => s.subject_type === "elective").length;
});

// Filtered Subjects
const filteredSubjects = computed(() => {
    let filtered = props.subjects;

    // Search filter
    if (searchQuery.value) {
        const search = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (subject) =>
                subject.name?.toLowerCase().includes(search) ||
                subject.code?.toLowerCase().includes(search),
        );
    }

    // Subject type filter
    if (subjectTypeFilter.value !== "all") {
        filtered = filtered.filter(
            (subject) => subject.subject_type === subjectTypeFilter.value,
        );
    }

    // Year level filter
    if (yearLevelFilter.value !== "all") {
        filtered = filtered.filter(
            (subject) => subject.year_level_id === yearLevelFilter.value,
        );
    }

    return filtered;
});

// Helper Functions
const formatSubjectType = (type) => {
    if (!type) return "-";
    return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatSemester = (semester) => {
    if (!semester) return "-";
    const semesterMap = {
        first: "1st Semester",
        second: "2nd Semester",
        full_year: "Full Year",
    };
    return semesterMap[semester] || semester;
};

const resetSubjectForm = () => {
    subjectForm.value = {
        name: "",
        code: "",
        description: "",
        year_level_id: "",
        subject_type: "",
        units: "",
        hours_per_week: "",
        semester: "",
        is_active: true,
    };
};

// Modal Functions
const openSubjectModal = (mode, subject = null) => {
    subjectModalMode.value = mode;
    if (mode === "edit" && subject) {
        selectedSubject.value = subject;
        subjectForm.value = {
            name: subject.name || "",
            code: subject.code || "",
            description: subject.description || "",
            year_level_id: subject.year_level_id || "",
            subject_type: subject.subject_type || "",
            units: subject.units || "",
            hours_per_week: subject.hours_per_week || "",
            semester: subject.semester || "",
            is_active: subject.is_active ?? true,
        };
    } else if (mode === "add") {
        resetSubjectForm();
        selectedSubject.value = null;
    }
    showSubjectModal.value = true;
};

const viewSubject = (subject) => {
    selectedSubject.value = subject;
    subjectModalMode.value = "view";
    showSubjectModal.value = true;
};

const closeSubjectModal = () => {
    showSubjectModal.value = false;
    resetSubjectForm();
    selectedSubject.value = null;
};

// Form Submission
const submitSubjectForm = () => {
    isSubmitting.value = true;

    if (subjectModalMode.value === "add") {
        router.post("/admin/subjects", subjectForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Subject created successfully!");
                closeSubjectModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to create subject.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else if (subjectModalMode.value === "edit" && selectedSubject.value) {
        router.put(
            `/admin/subjects/${selectedSubject.value.id}`,
            subjectForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Subject updated successfully!");
                    closeSubjectModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError || "Failed to update subject.");
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

// Delete Functions
const confirmDeleteSubject = (subject) => {
    subjectToDelete.value = subject;
    showDeleteModal.value = true;
};

const deleteSubject = () => {
    if (!subjectToDelete.value) return;
    isSubmitting.value = true;

    router.delete(`/admin/subjects/${subjectToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Subject deleted successfully!");
            showDeleteModal.value = false;
            subjectToDelete.value = null;
        },
        onError: () => {
            toast.error("Failed to delete subject.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.subject-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.subject-icon-sm,
.subject-icon-sm.core,
.subject-icon-sm.specialized,
.subject-icon-sm.applied,
.subject-icon-sm.elective {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2f6;
    color: #003366;
}

.subject-info-cell {
    display: flex;
    flex-direction: column;
}

.subject-name-cell {
    font-weight: 600;
    color: #003366;
}

.subject-code {
    font-size: 0.8rem;
    color: #555;
}

.detail-avatar.subject,
.detail-avatar.subject.core,
.detail-avatar.subject.specialized,
.detail-avatar.subject.applied,
.detail-avatar.subject.elective {
    background: #003366;
    color: #fff;
}

.delete-subject-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.7rem 0.85rem;
    background: #f7f7f7;
    border: 1px solid #ddd;
    margin: 1rem 0;
}

.delete-subject-info strong {
    color: #003366;
}

.delete-subject-info span {
    font-size: 0.875rem;
    color: #555;
}
</style>
