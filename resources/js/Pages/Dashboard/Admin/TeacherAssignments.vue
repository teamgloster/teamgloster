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

            <!-- Tabs -->
            <div class="tabs-container">
                <button
                    class="tab-btn"
                    :class="{ active: activeTab === 'teacher-subjects' }"
                    @click="activeTab = 'teacher-subjects'"
                >
                    <UserCog :size="18" />
                    Teacher-Subject
                </button>
                <button
                    class="tab-btn"
                    :class="{
                        active: activeTab === 'section-subject-teachers',
                    }"
                    @click="activeTab = 'section-subject-teachers'"
                >
                    <Link2 :size="18" />
                    Section-Subject-Teacher
                </button>
            </div>

            <!-- Tab 1: Teacher Subjects -->
            <div v-show="activeTab === 'teacher-subjects'" class="tab-content">
                <!-- Header Actions -->
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
                                                getTeacherSubjectCount(
                                                    teacher,
                                                ) > 3
                                            "
                                            class="subject-chip more"
                                        >
                                            +{{
                                                getTeacherSubjectCount(
                                                    teacher,
                                                ) - 3
                                            }}
                                            more
                                        </span>
                                        <span
                                            v-if="
                                                getTeacherSubjectCount(
                                                    teacher,
                                                ) === 0
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
                                            @click="
                                                viewTeacherSubjects(teacher)
                                            "
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
                            <tr
                                v-if="filteredTeachersWithSubjects.length === 0"
                            >
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

            <!-- Tab 2: Section Subject Teachers -->
            <div
                v-show="activeTab === 'section-subject-teachers'"
                class="tab-content"
            >
                <!-- Header Actions -->
                <div class="section-header">
                    <div class="header-actions">
                        <div class="search-box">
                            <Search :size="18" class="search-icon" />
                            <input
                                v-model="sectionSubjectSearch"
                                type="text"
                                placeholder="Search by section or subject..."
                                class="search-input"
                            />
                        </div>
                        <div class="filter-group">
                            <select
                                v-model="yearLevelFilter"
                                class="filter-select"
                            >
                                <option value="all">All Year Levels</option>
                                <option
                                    v-for="level in yearLevels"
                                    :key="level.id"
                                    :value="level.id"
                                >
                                    {{ level.name }}
                                </option>
                            </select>
                            <select
                                v-model="sectionFilter"
                                class="filter-select"
                            >
                                <option value="all">All Sections</option>
                                <option
                                    v-for="section in filteredSectionsForFilter"
                                    :key="section.id"
                                    :value="section.id"
                                >
                                    {{ section.name }}
                                </option>
                            </select>
                        </div>
                        <button
                            class="btn-primary"
                            @click="openSectionSubjectTeacherModal('add')"
                        >
                            <Plus :size="18" />
                            Assign Teacher
                        </button>
                    </div>
                </div>

                <!-- Section Subject Teachers Table -->
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="assignment in filteredSectionSubjectTeachers"
                                :key="assignment.id"
                            >
                                <td>
                                    <div class="section-cell">
                                        <div class="section-icon-sm">
                                            <Layers :size="18" />
                                        </div>
                                        <div class="section-info-cell">
                                            <span class="section-name-cell">{{
                                                assignment.section?.name
                                            }}</span>
                                            <span class="section-code">{{
                                                assignment.section?.year_level
                                                    ?.name
                                            }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="subject-cell">
                                        <div
                                            class="subject-icon-sm"
                                            :class="
                                                assignment.subject?.subject_type
                                            "
                                        >
                                            <BookOpen :size="16" />
                                        </div>
                                        <div class="subject-info-cell">
                                            <span class="subject-name-cell">{{
                                                assignment.subject?.name
                                            }}</span>
                                            <span class="subject-code">{{
                                                assignment.subject?.code
                                            }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div
                                        v-if="assignment.teacher"
                                        class="user-cell"
                                    >
                                        <div class="user-avatar-sm teacher">
                                            {{
                                                getInitials(assignment.teacher)
                                            }}
                                        </div>
                                        <div class="user-info-cell">
                                            <span class="user-name-cell">
                                                {{
                                                    assignment.teacher
                                                        ?.last_name
                                                }},
                                                {{
                                                    assignment.teacher
                                                        ?.first_name
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <span v-else class="text-muted"
                                        >Not assigned</span
                                    >
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button
                                            class="btn-icon edit"
                                            @click="
                                                openSectionSubjectTeacherModal(
                                                    'edit',
                                                    assignment,
                                                )
                                            "
                                            title="Edit Assignment"
                                        >
                                            <Pencil :size="16" />
                                        </button>
                                        <button
                                            class="btn-icon delete"
                                            @click="
                                                confirmDeleteSectionSubjectTeacher(
                                                    assignment,
                                                )
                                            "
                                            title="Delete Assignment"
                                        >
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    filteredSectionSubjectTeachers.length === 0
                                "
                            >
                                <td colspan="4" class="empty-table">
                                    <div class="empty-message">
                                        <Link2 :size="40" />
                                        <p>No assignments found</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="table-footer">
                    <span class="record-count">
                        Showing {{ filteredSectionSubjectTeachers.length }} of
                        {{ sectionSubjectTeachers.length }} assignments
                    </span>
                </div>
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
                                                    }"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :value="subject.id"
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

        <!-- Section Subject Teacher Modal (Add/Edit) -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showSectionSubjectTeacherModal"
                    class="modal-overlay"
                    @click.self="closeSectionSubjectTeacherModal"
                >
                    <div class="modal-container">
                        <div class="modal-header section-header">
                            <h3>
                                <Plus
                                    v-if="
                                        sectionSubjectTeacherModalMode === 'add'
                                    "
                                    :size="22"
                                />
                                <Pencil v-else :size="22" />
                                {{
                                    sectionSubjectTeacherModalMode === "add"
                                        ? "Assign Teacher to Section Subject"
                                        : "Edit Assignment"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeSectionSubjectTeacherModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <form
                                @submit.prevent="submitSectionSubjectTeacher"
                                class="modal-form"
                            >
                                <div class="form-group">
                                    <label
                                        >Section
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        v-model="
                                            sectionSubjectTeacherForm.section_id
                                        "
                                        required
                                        :disabled="
                                            sectionSubjectTeacherModalMode ===
                                            'edit'
                                        "
                                    >
                                        <option value="">
                                            Select a section
                                        </option>
                                        <option
                                            v-for="section in sections"
                                            :key="section.id"
                                            :value="section.id"
                                        >
                                            {{ section.name }} ({{
                                                section.year_level?.name
                                            }})
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label
                                        >Subject
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        v-model="
                                            sectionSubjectTeacherForm.subject_id
                                        "
                                        required
                                        :disabled="
                                            sectionSubjectTeacherModalMode ===
                                            'edit'
                                        "
                                    >
                                        <option value="">
                                            Select a subject
                                        </option>
                                        <option
                                            v-for="subject in availableSubjectsForSection"
                                            :key="subject.id"
                                            :value="subject.id"
                                        >
                                            {{ subject.name }} ({{
                                                subject.code
                                            }})
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label
                                        >Teacher
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        v-model="
                                            sectionSubjectTeacherForm.teacher_id
                                        "
                                        required
                                    >
                                        <option value="">
                                            Select a teacher
                                        </option>
                                        <option
                                            v-for="teacher in teachers"
                                            :key="teacher.id"
                                            :value="teacher.id"
                                        >
                                            {{ teacher.last_name }},
                                            {{ teacher.first_name }}
                                        </option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeSectionSubjectTeacherModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="isSubmitting || !isFormValid"
                                @click="submitSectionSubjectTeacher"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    sectionSubjectTeacherModalMode === "add"
                                        ? "Assign"
                                        : "Update"
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
                    @click.self="closeDeleteModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header danger">
                            <h3>
                                <Trash2 :size="22" />
                                Delete Assignment
                            </h3>
                            <button class="close-btn" @click="closeDeleteModal">
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
                                    assignment?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{ assignmentToDelete?.section?.name }}
                                        -
                                        {{
                                            assignmentToDelete?.subject?.name
                                        }}</strong
                                    >
                                    <span
                                        >Teacher:
                                        {{
                                            assignmentToDelete?.teacher
                                                ?.first_name
                                        }}
                                        {{
                                            assignmentToDelete?.teacher
                                                ?.last_name
                                        }}</span
                                    >
                                </div>
                                <p class="warning-text">
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeDeleteModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="isSubmitting"
                                @click="deleteSectionSubjectTeacher"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete
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
    Layers,
    Users,
    Search,
    Plus,
    Eye,
    Pencil,
    Trash2,
    X,
    Loader2,
    Check,
    Link2,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    teachers: {
        type: Array,
        default: () => [],
    },
    teachersWithSubjects: {
        type: Array,
        default: () => [],
    },
    subjects: {
        type: Array,
        default: () => [],
    },
    sections: {
        type: Array,
        default: () => [],
    },
    yearLevels: {
        type: Array,
        default: () => [],
    },
    sectionSubjectTeachers: {
        type: Array,
        default: () => [],
    },
    currentSchoolYear: {
        type: String,
        default: "",
    },
});

// Tab State
const activeTab = ref("teacher-subjects");

// Search and Filter State
const teacherSearch = ref("");
const sectionSubjectSearch = ref("");
const yearLevelFilter = ref("all");
const sectionFilter = ref("all");

// Modal States
const showViewSubjectsModal = ref(false);
const showManageSubjectsModal = ref(false);
const showSectionSubjectTeacherModal = ref(false);
const showDeleteModal = ref(false);

// Selected Items
const selectedTeacher = ref(null);
const selectedSubjectIds = ref([]);
const selectedAssignment = ref(null);
const assignmentToDelete = ref(null);

// Modal Modes
const sectionSubjectTeacherModalMode = ref("add");

// Form State
const sectionSubjectTeacherForm = ref({
    section_id: "",
    subject_id: "",
    teacher_id: "",
});

// Loading State
const isSubmitting = ref(false);

// Computed Properties
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

const filteredSectionsForFilter = computed(() => {
    if (yearLevelFilter.value === "all") return props.sections;
    return props.sections.filter(
        (s) => s.year_level_id === yearLevelFilter.value,
    );
});

const filteredSectionSubjectTeachers = computed(() => {
    let result = props.sectionSubjectTeachers;

    // Filter by year level
    if (yearLevelFilter.value !== "all") {
        result = result.filter(
            (a) => a.section?.year_level_id === yearLevelFilter.value,
        );
    }

    // Filter by section
    if (sectionFilter.value !== "all") {
        result = result.filter((a) => a.section_id === sectionFilter.value);
    }

    // Filter by search query
    if (sectionSubjectSearch.value) {
        const search = sectionSubjectSearch.value.toLowerCase();
        result = result.filter((a) => {
            const sectionName = a.section?.name?.toLowerCase() || "";
            const subjectName = a.subject?.name?.toLowerCase() || "";
            const subjectCode = a.subject?.code?.toLowerCase() || "";
            const teacherName =
                `${a.teacher?.first_name || ""} ${a.teacher?.last_name || ""}`.toLowerCase();
            return (
                sectionName.includes(search) ||
                subjectName.includes(search) ||
                subjectCode.includes(search) ||
                teacherName.includes(search)
            );
        });
    }

    return result;
});

const availableSubjectsForSection = computed(() => {
    if (!sectionSubjectTeacherForm.value.section_id) return props.subjects;
    const section = props.sections.find(
        (s) => s.id === sectionSubjectTeacherForm.value.section_id,
    );
    if (!section) return props.subjects;
    return props.subjects.filter(
        (s) => s.year_level_id === section.year_level_id,
    );
});

const isFormValid = computed(() => {
    return (
        sectionSubjectTeacherForm.value.section_id &&
        sectionSubjectTeacherForm.value.subject_id &&
        sectionSubjectTeacherForm.value.teacher_id
    );
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
    selectedSubjectIds.value = props.subjects.map((s) => s.id);
};

const clearAllSubjects = () => {
    selectedSubjectIds.value = [];
};

const saveTeacherSubjects = () => {
    if (!selectedTeacher.value) return;
    isSubmitting.value = true;

    router.post(
        "/admin/teacher-subjects/bulk",
        {
            teacher_id: selectedTeacher.value.id,
            subject_ids: selectedSubjectIds.value,
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

// Section Subject Teacher Modal Functions
const openSectionSubjectTeacherModal = (mode, assignment = null) => {
    sectionSubjectTeacherModalMode.value = mode;
    if (mode === "edit" && assignment) {
        selectedAssignment.value = assignment;
        sectionSubjectTeacherForm.value = {
            section_id: assignment.section_id,
            subject_id: assignment.subject_id,
            teacher_id: assignment.teacher_id,
        };
    } else {
        selectedAssignment.value = null;
        sectionSubjectTeacherForm.value = {
            section_id: "",
            subject_id: "",
            teacher_id: "",
        };
    }
    showSectionSubjectTeacherModal.value = true;
};

const closeSectionSubjectTeacherModal = () => {
    showSectionSubjectTeacherModal.value = false;
    selectedAssignment.value = null;
    sectionSubjectTeacherForm.value = {
        section_id: "",
        subject_id: "",
        teacher_id: "",
    };
};

const submitSectionSubjectTeacher = () => {
    if (!isFormValid.value) return;
    isSubmitting.value = true;

    if (sectionSubjectTeacherModalMode.value === "add") {
        router.post(
            "/admin/section-subject-teachers",
            {
                ...sectionSubjectTeacherForm.value,
                school_year: props.currentSchoolYear,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Teacher assigned successfully!");
                    closeSectionSubjectTeacherModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError || "Failed to assign teacher.");
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    } else if (selectedAssignment.value) {
        router.put(
            `/admin/section-subject-teachers/${selectedAssignment.value.id}`,
            sectionSubjectTeacherForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Assignment updated successfully!");
                    closeSectionSubjectTeacherModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError || "Failed to update assignment.");
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

// Delete Functions
const confirmDeleteSectionSubjectTeacher = (assignment) => {
    assignmentToDelete.value = assignment;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    assignmentToDelete.value = null;
};

const deleteSectionSubjectTeacher = () => {
    if (!assignmentToDelete.value) return;
    isSubmitting.value = true;

    router.delete(
        `/admin/section-subject-teachers/${assignmentToDelete.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Assignment deleted successfully!");
                closeDeleteModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to delete assignment.");
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

/* Tabs Container */
.tabs-container {
    display: flex;
    gap: 0;
    margin-bottom: 1rem;
    background: #fff;
    padding: 0;
    border: 1px solid #c5c5c5;
    width: fit-content;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.9rem;
    background: #fff;
    border: none;
    border-right: 1px solid #c5c5c5;
    font-weight: 600;
    color: #003366;
    cursor: pointer;
}

.tab-btn:last-child {
    border-right: none;
}

.tab-btn:hover {
    background: #f4f7fb;
    color: #003366;
}

.tab-btn.active {
    background: #003366;
    color: #fff;
}

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
    flex-shrink: 0;
    background: #eef2f6;
    color: #003366;
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
    .tabs-container {
        width: 100%;
        flex-direction: column;
    }

    .tab-btn {
        justify-content: center;
    }

    .subjects-checkbox-grid {
        grid-template-columns: 1fr;
    }

    .subjects-grid {
        grid-template-columns: 1fr;
    }
}
</style>
