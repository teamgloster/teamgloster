<template>
    <AdminLayout
        title="Sections"
        pageTitle="Sections"
        currentPage="sections"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Sections</h2>
                    <span class="gov-sy"
                        >School Year {{ selectedSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total Sections</div>
                    <div class="gov-stat-value">{{ totalSections }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Active</div>
                    <div class="gov-stat-value">{{ activeSections }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Inactive</div>
                    <div class="gov-stat-value">{{ inactiveSections }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total Capacity</div>
                    <div class="gov-stat-value">{{ totalCapacity }}</div>
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
                            placeholder="Search sections by name or code..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select
                            class="filter-select"
                            :value="selectedSchoolYear"
                            @change="changeSchoolYear"
                        >
                            <option
                                v-for="year in availableSchoolYears"
                                :key="year"
                                :value="year"
                            >
                                SY {{ year
                                }}{{
                                    year === currentSchoolYear
                                        ? " (current)"
                                        : ""
                                }}
                            </option>
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
                        <select v-model="statusFilter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button
                        class="btn-primary"
                        @click="openSectionModal('add')"
                    >
                        <Plus :size="18" />
                        Add Section
                    </button>
                </div>
            </div>

            <!-- Sections Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Year Level</th>
                            <th>School Year</th>
                            <th>Adviser</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="section in filteredSections"
                            :key="section.id"
                        >
                            <td>
                                <div class="section-cell">
                                    <div class="section-icon-sm">
                                        <Layers :size="18" />
                                    </div>
                                    <div class="section-info-cell">
                                        <span class="section-name-cell">{{
                                            section.name
                                        }}</span>
                                        <span class="section-code">{{
                                            section.code
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="year-level-badge">
                                    {{ section.year_level?.name || "-" }}
                                </span>
                            </td>
                            <td>
                                <span class="section-code">{{
                                    section.school_year || "—"
                                }}</span>
                            </td>
                            <td>
                                <div
                                    v-if="section.adviser"
                                    class="adviser-cell"
                                >
                                    <div class="adviser-avatar">
                                        {{
                                            getAdviserInitials(section.adviser)
                                        }}
                                    </div>
                                    <span>{{
                                        formatAdviserName(section.adviser)
                                    }}</span>
                                </div>
                                <span v-else class="text-muted"
                                    >Not assigned</span
                                >
                            </td>
                            <td>
                                <span class="capacity-badge">
                                    {{ getEnrollmentCount(section) }}/{{
                                        section.capacity
                                    }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        section.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        section.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewSection(section)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openSectionModal('edit', section)
                                        "
                                        title="Edit Section"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon delete"
                                        @click="confirmDeleteSection(section)"
                                        title="Delete Section"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredSections.length === 0">
                            <td colspan="7" class="empty-table">
                                <div class="empty-message">
                                    <Layers :size="40" />
                                    <p>No sections found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredSections.length }} of
                    {{ sections.length }} sections
                </span>
            </div>
        </div>

        <!-- Section Modal (Add/Edit/View) -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showSectionModal"
                    class="modal-overlay"
                    @click.self="closeSectionModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header section-header">
                            <h3>
                                <Plus
                                    v-if="sectionModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="sectionModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    sectionModalMode === "add"
                                        ? "Add New Section"
                                        : sectionModalMode === "edit"
                                          ? "Edit Section"
                                          : "Section Details"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeSectionModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- View Mode -->
                            <div
                                v-if="sectionModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div class="detail-avatar section">
                                        <Layers :size="28" />
                                    </div>
                                    <div class="detail-title">
                                        <h4>{{ selectedSection.name }}</h4>
                                        <span class="code-badge">{{
                                            selectedSection.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Year Level</label>
                                        <span>{{
                                            selectedSection.year_level?.name ||
                                            "Not set"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Adviser</label>
                                        <span>{{
                                            selectedSection.adviser
                                                ? formatAdviserName(
                                                      selectedSection.adviser,
                                                  )
                                                : "Not assigned"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Capacity</label>
                                        <span
                                            >{{
                                                selectedSection.capacity
                                            }}
                                            students</span
                                        >
                                    </div>
                                    <div class="detail-item">
                                        <label>Current Enrollment</label>
                                        <span
                                            >{{
                                                getEnrollmentCount(
                                                    selectedSection,
                                                )
                                            }}
                                            students</span
                                        >
                                    </div>
                                    <div class="detail-item">
                                        <label>School Year</label>
                                        <span>{{
                                            selectedSection.school_year
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status</label>
                                        <span
                                            class="status-badge"
                                            :class="
                                                selectedSection.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            "
                                        >
                                            {{
                                                selectedSection.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add/Edit Form -->
                            <form
                                v-else
                                @submit.prevent="submitSectionForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Section Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="sectionForm.name"
                                            type="text"
                                            required
                                            placeholder="e.g., Einstein"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Section Code
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="sectionForm.code"
                                            type="text"
                                            required
                                            placeholder="e.g., G7-EINSTEIN"
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
                                            v-model="sectionForm.year_level_id"
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
                                        <label>Adviser</label>
                                        <select
                                            v-model="sectionForm.adviser_id"
                                        >
                                            <option value="">
                                                Select adviser (optional)
                                            </option>
                                            <option
                                                v-for="teacher in teachers"
                                                :key="teacher.id"
                                                :value="teacher.id"
                                            >
                                                {{ formatAdviserName(teacher) }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Capacity
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model.number="
                                                sectionForm.capacity
                                            "
                                            type="number"
                                            min="1"
                                            required
                                            placeholder="e.g., 40"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >School Year
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="sectionForm.school_year"
                                            required
                                        >
                                            <option value="">
                                                Select school year
                                            </option>
                                            <option
                                                v-for="year in schoolYearOptions"
                                                :key="year"
                                                :value="year"
                                            >
                                                SY {{ year
                                                }}{{
                                                    year === currentSchoolYear
                                                        ? " (current)"
                                                        : ""
                                                }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <div class="checkbox-group">
                                            <label class="checkbox-label">
                                                <input
                                                    v-model="
                                                        sectionForm.is_active
                                                    "
                                                    type="checkbox"
                                                />
                                                <span>Active</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeSectionModal"
                            >
                                {{
                                    sectionModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="sectionModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitSectionForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    sectionModalMode === "add"
                                        ? "Create Section"
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
                                    section?
                                </p>
                                <div class="delete-student-info">
                                    <strong>{{ sectionToDelete?.name }}</strong>
                                    <span>{{ sectionToDelete?.code }}</span>
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
                                @click="deleteSection"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Section
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
    Layers,
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
    sections: {
        type: Array,
        default: () => [],
    },
    yearLevels: {
        type: Array,
        default: () => [],
    },
    teachers: {
        type: Array,
        default: () => [],
    },
    enrollments: {
        type: Array,
        default: () => [],
    },
    currentSchoolYear: {
        type: String,
        default: "",
    },
    selectedSchoolYear: {
        type: String,
        default: "",
    },
    availableSchoolYears: {
        type: Array,
        default: () => [],
    },
    officialSchoolYears: {
        type: Array,
        default: () => [],
    },
});

const schoolYearOptions = computed(() => {
    const years = new Set();
    const source = props.officialSchoolYears;
    const list = Array.isArray(source)
        ? source
        : source && typeof source === "object"
          ? Object.values(source)
          : [];
    list.forEach((year) => years.add(String(year)));
    if (props.currentSchoolYear) years.add(props.currentSchoolYear);
    if (props.selectedSchoolYear) years.add(props.selectedSchoolYear);
    return [...years].sort((a, b) => b.localeCompare(a));
});

// State
const searchQuery = ref("");
const yearLevelFilter = ref("all");
const statusFilter = ref("all");
const isSubmitting = ref(false);

// Modal State
const showSectionModal = ref(false);
const sectionModalMode = ref("add");
const selectedSection = ref(null);
const showDeleteModal = ref(false);
const sectionToDelete = ref(null);

// Form State
const sectionForm = ref({
    name: "",
    code: "",
    year_level_id: "",
    adviser_id: "",
    capacity: 40,
    school_year: "",
    is_active: true,
});

// Computed - Stats
const totalSections = computed(() => props.sections.length);

const activeSections = computed(() => {
    return props.sections.filter((s) => s.is_active).length;
});

const inactiveSections = computed(() => {
    return props.sections.filter((s) => !s.is_active).length;
});

const totalCapacity = computed(() => {
    return props.sections.reduce((sum, s) => sum + (s.capacity || 0), 0);
});

// Computed - Filtered Sections
const filteredSections = computed(() => {
    let result = props.sections;

    // Search filter
    if (searchQuery.value) {
        const search = searchQuery.value.toLowerCase();
        result = result.filter(
            (section) =>
                section.name?.toLowerCase().includes(search) ||
                section.code?.toLowerCase().includes(search),
        );
    }

    // Year level filter
    if (yearLevelFilter.value !== "all") {
        result = result.filter(
            (section) =>
                String(section.year_level_id) === String(yearLevelFilter.value),
        );
    }

    // Status filter
    if (statusFilter.value !== "all") {
        const isActive = statusFilter.value === "active";
        result = result.filter((section) => section.is_active === isActive);
    }

    return result;
});

// Helper Functions
const getAdviserInitials = (adviser) => {
    if (!adviser) return "?";
    return (
        (adviser.first_name?.charAt(0) || "") +
        (adviser.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const formatAdviserName = (adviser) => {
    if (!adviser) return "";
    return `${adviser.last_name}, ${adviser.first_name}${adviser.middle_name ? " " + adviser.middle_name.charAt(0) + "." : ""}`;
};

const getEnrollmentCount = (section) => section?.enrollments_count ?? 0;

const changeSchoolYear = (event) => {
    router.get(
        "/admin/sections",
        { sy: event.target.value },
        { preserveState: false, preserveScroll: true },
    );
};

// Reset Form
const resetSectionForm = () => {
    sectionForm.value = {
        name: "",
        code: "",
        year_level_id: "",
        adviser_id: "",
        capacity: 40,
        school_year:
            props.selectedSchoolYear || props.currentSchoolYear || "",
        is_active: true,
    };
};

// Modal Functions
const openSectionModal = (mode, section = null) => {
    sectionModalMode.value = mode;
    if (mode === "edit" && section) {
        selectedSection.value = section;
        sectionForm.value = {
            name: section.name || "",
            code: section.code || "",
            year_level_id: section.year_level_id || "",
            adviser_id: section.adviser_id || "",
            capacity: section.capacity || 40,
            school_year: section.school_year || props.currentSchoolYear || "",
            is_active: section.is_active ?? true,
        };
    } else if (mode === "add") {
        resetSectionForm();
        selectedSection.value = null;
    }
    showSectionModal.value = true;
};

const viewSection = (section) => {
    selectedSection.value = section;
    sectionModalMode.value = "view";
    showSectionModal.value = true;
};

const closeSectionModal = () => {
    showSectionModal.value = false;
    resetSectionForm();
    selectedSection.value = null;
};

// Form Submission
const submitSectionForm = () => {
    isSubmitting.value = true;

    if (sectionModalMode.value === "add") {
        router.post("/admin/sections", sectionForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Section created successfully!");
                closeSectionModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to create section.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else if (sectionModalMode.value === "edit" && selectedSection.value) {
        router.put(
            `/admin/sections/${selectedSection.value.id}`,
            sectionForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Section updated successfully!");
                    closeSectionModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError || "Failed to update section.");
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

// Delete Functions
const confirmDeleteSection = (section) => {
    sectionToDelete.value = section;
    showDeleteModal.value = true;
};

const deleteSection = () => {
    if (!sectionToDelete.value) return;
    isSubmitting.value = true;

    router.delete(`/admin/sections/${sectionToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Section deleted successfully!");
            showDeleteModal.value = false;
            sectionToDelete.value = null;
        },
        onError: () => {
            toast.error("Failed to delete section.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.text-muted {
    color: #555;
    font-style: italic;
}

.detail-avatar.section {
    background: #003366;
}
</style>
