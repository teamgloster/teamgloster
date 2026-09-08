<template>
    <AdminLayout
        title="Teacher Assignments"
        pageTitle="Teacher Assignments"
        currentPage="teacher-assignments"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Teacher Assignments</h2>
                </div>
            </div>

            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="teacherSearch"
                            type="text"
                            placeholder="Search teachers..."
                            class="search-input"
                        />
                    </div>
                </div>
            </div>

            <!-- Teachers Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Assigned Subjects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="teacher in filteredTeachersWithSubjects"
                            :key="teacher.id"
                        >
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm teacher">
                                        {{ getInitials(teacher) }}
                                    </div>
                                    <div class="user-info-cell">
                                        <span class="user-name-cell">
                                            {{ teacher.last_name }},
                                            {{ teacher.first_name }}
                                            {{
                                                teacher.middle_name
                                                    ? teacher.middle_name.charAt(
                                                          0,
                                                      ) + "."
                                                    : ""
                                            }}
                                        </span>
                                        <span class="user-meta">{{
                                            teacher.email
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="subject-chips">
                                    <span
                                        v-for="subject in getTeacherSubjectsPreview(
                                            teacher,
                                        )"
                                        :key="subject.id"
                                        class="subject-chip"
                                        :class="subject.subject_type"
                                    >
                                        {{ subject.code }}
                                    </span>
                                    <span
                                        v-if="
                                            getTeacherSubjectCount(teacher) > 3
                                        "
                                        class="subject-chip more"
                                    >
                                        +{{
                                            getTeacherSubjectCount(teacher) - 3
                                        }}
                                        more
                                    </span>
                                    <span
                                        v-if="
                                            getTeacherSubjectCount(teacher) ===
                                            0
                                        "
                                        class="text-muted"
                                    >
                                        No subjects assigned
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewTeacherSubjects(teacher)"
                                        title="View Subjects"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openManageSubjectsModal(teacher)
                                        "
                                        title="Manage Subjects"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredTeachersWithSubjects.length === 0">
                            <td colspan="3" class="empty-table">
                                <div class="empty-message">
                                    <Users :size="40" />
                                    <p>No teachers found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredTeachersWithSubjects.length }} of
                    {{ teachersWithSubjects.length }} teachers
                </span>
            </div>
        </div>

        <!-- View Teacher Subjects Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showViewSubjectsModal"
                    class="modal-overlay"
                    @click.self="closeViewSubjectsModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header teacher-header">
                            <h3>
                                <Eye :size="22" />
                                Teacher Subjects
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeViewSubjectsModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="view-details">
                                <div class="detail-header">
                                    <div class="detail-avatar teacher">
                                        {{ getInitials(selectedTeacher) }}
                                    </div>
                                    <div class="detail-title">
                                        <h4>
                                            {{ selectedTeacher?.first_name }}
                                            {{ selectedTeacher?.middle_name }}
                                            {{ selectedTeacher?.last_name }}
                                        </h4>
                                        <span class="role-badge teacher"
                                            >Teacher</span
                                        >
                                    </div>
                                </div>
                                <div class="subjects-list-section">
                                    <h5>
                                        Assigned Subjects ({{
                                            getTeacherSubjectCount(
                                                selectedTeacher,
                                            )
                                        }})
                                    </h5>
                                    <div
                                        v-if="
                                            getTeacherSubjectCount(
                                                selectedTeacher,
                                            ) > 0
                                        "
                                        class="subjects-grid"
                                    >
                                        <div
                                            v-for="subject in getTeacherAllSubjects(
                                                selectedTeacher,
                                            )"
                                            :key="subject.id"
                                            class="subject-card"
                                            :class="subject.subject_type"
                                        >
                                            <BookOpen :size="20" />
                                            <div class="subject-card-info">
                                                <span
                                                    class="subject-card-name"
                                                    >{{ subject.name }}</span
                                                >
                                                <span
                                                    class="subject-card-code"
                                                    >{{ subject.code }}</span
                                                >
                                                <span
                                                    class="subject-card-level"
                                                    >{{
                                                        subject.year_level?.name
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="empty-state">
                                        <BookOpen :size="32" />
                                        <p>
                                            No subjects assigned to this teacher
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeViewSubjectsModal"
                            >
                                Close
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                @click="openManageSubjectsFromView"
                            >
                                <Pencil :size="16" />
                                Manage Subjects
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Manage Subjects Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showManageSubjectsModal"
                    class="modal-overlay"
                    @click.self="closeManageSubjectsModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header teacher-header">
                            <h3>
                                <UserCog :size="22" />
                                Manage Subject Assignments
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeManageSubjectsModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="manage-subjects-form">
                                <div class="teacher-info-bar">
                                    <div class="user-avatar-sm teacher">
                                        {{ getInitials(selectedTeacher) }}
                                    </div>
                                    <div class="teacher-info-text">
                                        <strong>
                                            {{ selectedTeacher?.first_name }}
                                            {{ selectedTeacher?.last_name }}
                                        </strong>
                                        <span>{{
                                            selectedTeacher?.email
                                        }}</span>
                                    </div>
                                </div>

                                <div class="subjects-selection">
                                    <div class="selection-header">
                                        <h5>Select Subjects</h5>
                                        <div class="selection-actions">
                                            <button
                                                type="button"
                                                class="btn-link"
                                                @click="selectAllSubjects"
                                            >
                                                Select All
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-link"
                                                @click="clearAllSubjects"
                                            >
                                                Clear All
                                            </button>
                                        </div>
                                    </div>

                                    <div class="year-level-groups">
                                        <div
                                            v-for="level in yearLevels"
                                            :key="level.id"
                                            class="year-level-group"
                                        >
                                            <div
                                                class="year-level-group-header"
                                            >
                                                <span
                                                    class="year-level-badge"
                                                    >{{ level.name }}</span
                                                >
                                                <span class="subject-count">
                                                    {{
                                                        getSubjectsByYearLevel(
                                                            level.id,
                                                        ).length
                                                    }}
                                                    subjects
                                                </span>
                                            </div>
                                            <div class="subjects-checkbox-grid">
                                                <label
                                                    v-for="subject in getSubjectsByYearLevel(
                                                        level.id,
                                                    )"
                                                    :key="subject.id"
                                                    class="subject-checkbox"
                                                    :class="{
                                                        checked:
                                                            selectedSubjectIds.includes(
                                                                subject.id,
                                                            ),
                                                        taken: isSubjectTakenByAnother(
                                                            subject.id,
                                                        ),
                                                    }"
                                                    :title="
                                                        subjectTakenTooltip(
                                                            subject.id,
                                                        )
                                                    "
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :value="subject.id"
                                                        :disabled="
                                                            isSubjectTakenByAnother(
                                                                subject.id,
                                                            )
                                                        "
                                                        v-model="
                                                            selectedSubjectIds
                                                        "
                                                    />
                                                    <div
                                                        class="subject-checkbox-content"
                                                    >
                                                        <span
                                                            class="subject-checkbox-name"
                                                            >{{
                                                                subject.name
                                                            }}</span
                                                        >
                                                        <span
                                                            class="subject-checkbox-code"
                                                            >{{
                                                                subject.code
                                                            }}</span
                                                        >
                                                        <span
                                                            v-if="
                                                                isSubjectTakenByAnother(
                                                                    subject.id,
                                                                )
                                                            "
                                                            class="subject-checkbox-owner"
                                                        >
                                                            Assigned to
                                                            {{
                                                                assignedTeacherLabel(
                                                                    subject.id,
                                                                )
                                                            }}
                                                        </span>
                                                    </div>
                                                    <Check
                                                        v-if="
                                                            selectedSubjectIds.includes(
                                                                subject.id,
                                                            )
                                                        "
                                                        :size="16"
                                                        class="check-icon"
                                                    />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="selected-summary">
                                    <span
                                        >{{
                                            selectedSubjectIds.length
                                        }}
                                        subject(s) selected</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeManageSubjectsModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="saveTeacherSubjects"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Save Assignments
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
    UserCog,
    BookOpen,
    Users,
    Search,
    Eye,
    Pencil,
    X,
    Loader2,
    Check,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    teachersWithSubjects: {
        type: Array,
        default: () => [],
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

const teacherSearch = ref("");
const showViewSubjectsModal = ref(false);
const showManageSubjectsModal = ref(false);
const selectedTeacher = ref(null);
const selectedSubjectIds = ref([]);
const isSubmitting = ref(false);

const filteredTeachersWithSubjects = computed(() => {
    if (!teacherSearch.value) return props.teachersWithSubjects;
    const search = teacherSearch.value.toLowerCase();
    return props.teachersWithSubjects.filter((teacher) => {
        const fullName =
            `${teacher.first_name} ${teacher.middle_name || ""} ${teacher.last_name}`.toLowerCase();
        const email = teacher.email?.toLowerCase() || "";
        return fullName.includes(search) || email.includes(search);
    });
});

// Helper Functions
const getInitials = (user) => {
    if (!user) return "?";
    return (
        (user.first_name?.charAt(0) || "") + (user.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const getTeacherSubjectsPreview = (teacher) => {
    if (!teacher?.subjects) return [];
    return teacher.subjects.slice(0, 3);
};

const getTeacherSubjectCount = (teacher) => {
    return teacher?.subjects?.length || 0;
};

const getTeacherAllSubjects = (teacher) => {
    return teacher?.subjects || [];
};

const getSubjectsByYearLevel = (yearLevelId) => {
    return props.subjects.filter((s) => s.year_level_id === yearLevelId);
};

const assignedTeacherBySubjectId = computed(() => {
    const map = {};
    for (const teacher of props.teachersWithSubjects) {
        for (const subject of teacher.subjects || []) {
            map[subject.id] = teacher;
        }
    }
    return map;
});

const teacherDisplayName = (teacher) => {
    if (!teacher) return "";
    return `${teacher.last_name || ""}, ${teacher.first_name || ""}`.replace(
        /^,\s*|,\s*$/g,
        "",
    );
};

const isSubjectTakenByAnother = (subjectId) => {
    const owner = assignedTeacherBySubjectId.value[subjectId];
    if (!owner || !selectedTeacher.value) return false;
    return String(owner.id) !== String(selectedTeacher.value.id);
};

const assignedTeacherLabel = (subjectId) => {
    return teacherDisplayName(assignedTeacherBySubjectId.value[subjectId]);
};

const subjectTakenTooltip = (subjectId) => {
    if (!isSubjectTakenByAnother(subjectId)) return "";
    const name = assignedTeacherLabel(subjectId);
    return name
        ? `Already assigned to ${name}`
        : "Already assigned to another teacher";
};

const selectableSubjectIds = () => {
    return props.subjects
        .filter((subject) => !isSubjectTakenByAnother(subject.id))
        .map((subject) => subject.id);
};

// Teacher Subjects Modal Functions
const viewTeacherSubjects = (teacher) => {
    selectedTeacher.value = teacher;
    showViewSubjectsModal.value = true;
};

const closeViewSubjectsModal = () => {
    showViewSubjectsModal.value = false;
    selectedTeacher.value = null;
};

const openManageSubjectsModal = (teacher) => {
    selectedTeacher.value = teacher;
    selectedSubjectIds.value = teacher.subjects?.map((s) => s.id) || [];
    showManageSubjectsModal.value = true;
};

const openManageSubjectsFromView = () => {
    showViewSubjectsModal.value = false;
    selectedSubjectIds.value =
        selectedTeacher.value?.subjects?.map((s) => s.id) || [];
    showManageSubjectsModal.value = true;
};

const closeManageSubjectsModal = () => {
    showManageSubjectsModal.value = false;
    selectedTeacher.value = null;
    selectedSubjectIds.value = [];
};

const selectAllSubjects = () => {
    selectedSubjectIds.value = selectableSubjectIds();
};

const clearAllSubjects = () => {
    selectedSubjectIds.value = [];
};

const saveTeacherSubjects = () => {
    if (!selectedTeacher.value) return;
    isSubmitting.value = true;

    const subjectIds = selectedSubjectIds.value.filter(
        (subjectId) => !isSubjectTakenByAnother(subjectId),
    );

    router.post(
        "/admin/teacher-subjects/bulk",
        {
            teacher_id: selectedTeacher.value.id,
            subject_ids: subjectIds,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Subject assignments saved successfully!");
                closeManageSubjectsModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(
                    firstError || "Failed to save subject assignments.",
                );
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

/* Subject Chips */
.subject-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.subject-chip {
    display: inline-block;
    padding: 0.15rem 0.45rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #fff;
    color: #003366;
    border: 1px solid #c5c5c5;
}

.subject-chip.core,
.subject-chip.specialized,
.subject-chip.applied,
.subject-chip.elective {
    background: #fff;
    color: #003366;
    border: 1px solid #c5c5c5;
}

.subject-chip.more {
    background: #eef2f6;
    color: #003366;
    border: 1px solid #c5c5c5;
}

.subjects-list-section {
    margin-top: 1rem;
}

.subjects-list-section h5 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    color: #003366;
    font-weight: 600;
}

.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
}

.subject-card,
.subject-card.core,
.subject-card.specialized,
.subject-card.applied,
.subject-card.elective {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #fff;
    border: 1px solid #c5c5c5;
    color: #003366;
}

.subject-card-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.subject-card-name {
    font-weight: 600;
    color: #003366;
    font-size: 0.9rem;
}

.subject-card-code {
    font-size: 0.8rem;
    color: #555;
    font-family: monospace;
}

.subject-card-level {
    font-size: 0.75rem;
    color: #555;
}

.manage-subjects-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.teacher-info-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f7f7f7;
    border: 1px solid #ddd;
}

.teacher-info-text {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.teacher-info-text strong {
    color: #003366;
    font-size: 1rem;
}

.teacher-info-text span {
    color: #555;
    font-size: 0.875rem;
}

.subjects-selection {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.selection-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selection-header h5 {
    margin: 0;
    font-size: 1rem;
    color: #003366;
    font-weight: 600;
}

.selection-actions {
    display: flex;
    gap: 1rem;
}

.btn-link {
    background: none;
    border: none;
    color: #003366;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.875rem;
    padding: 0;
}

.btn-link:hover {
    text-decoration: underline;
}

.year-level-groups {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.year-level-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.year-level-group-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #c5c5c5;
}

.subject-count {
    font-size: 0.8rem;
    color: #555;
}

.subjects-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.5rem;
}

.subject-checkbox {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem;
    background: #fff;
    border: 1px solid #c5c5c5;
    cursor: pointer;
    position: relative;
}

.subject-checkbox:hover {
    background: #f4f7fb;
    border-color: #003366;
}

.subject-checkbox.checked {
    background: #e8eef4;
    border-color: #003366;
}

.subject-checkbox.taken {
    cursor: not-allowed;
    background: #f5f5f5;
    opacity: 0.78;
}

.subject-checkbox.taken:hover {
    background: #f5f5f5;
    border-color: #c5c5c5;
}

.subject-checkbox input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.subject-checkbox-content {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    flex: 1;
}

.subject-checkbox-name {
    font-weight: 600;
    color: #003366;
    font-size: 0.85rem;
}

.subject-checkbox-code {
    font-size: 0.75rem;
    color: #555;
    font-family: monospace;
}

.subject-checkbox-owner {
    font-size: 0.7rem;
    color: #9b1c1c;
    font-weight: 600;
}

.check-icon {
    color: #003366;
    flex-shrink: 0;
}

.selected-summary {
    padding: 0.75rem 1rem;
    background: #e8eef4;
    border: 1px solid #c5c5c5;
    text-align: left;
    font-weight: 600;
    color: #003366;
}

.text-muted {
    color: #555;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .subjects-checkbox-grid {
        grid-template-columns: 1fr;
    }

    .subjects-grid {
        grid-template-columns: 1fr;
    }
}
</style>
