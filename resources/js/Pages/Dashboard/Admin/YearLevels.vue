<template>
    <AdminLayout
        title="Year Levels"
        pageTitle="Year Levels"
        currentPage="year-levels"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Year Levels</h2>
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total</div>
                    <div class="gov-stat-value">{{ totalCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Junior High</div>
                    <div class="gov-stat-value">{{ juniorHighCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Senior High</div>
                    <div class="gov-stat-value">{{ seniorHighCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Active</div>
                    <div class="gov-stat-value">{{ activeCount }}</div>
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
                            placeholder="Search year levels by name or code..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select v-model="levelTypeFilter" class="filter-select">
                            <option value="all">All Types</option>
                            <option value="junior_high">Junior High</option>
                            <option value="senior_high">Senior High</option>
                        </select>
                        <select v-model="statusFilter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button
                        class="btn-primary"
                        @click="openYearLevelModal('add')"
                    >
                        <Plus :size="18" />
                        Add Year Level
                    </button>
                </div>
            </div>

            <!-- Year Levels Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Year Level</th>
                            <th>Type</th>
                            <th>Sections Count</th>
                            <th>Subjects Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="yearLevel in filteredYearLevels"
                            :key="yearLevel.id"
                        >
                            <td>
                                <div class="year-level-cell">
                                    <div
                                        class="year-level-icon-sm"
                                        :class="yearLevel.level_type"
                                    >
                                        <Building :size="18" />
                                    </div>
                                    <div class="year-level-info-cell">
                                        <span class="year-level-name-cell">{{
                                            yearLevel.name
                                        }}</span>
                                        <span class="year-level-code">{{
                                            yearLevel.code
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="type-badge"
                                    :class="yearLevel.level_type"
                                >
                                    {{ formatLevelType(yearLevel.level_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="count-badge">{{
                                    yearLevel.sections_count || 0
                                }}</span>
                            </td>
                            <td>
                                <span class="count-badge">{{
                                    yearLevel.subjects_count || 0
                                }}</span>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        yearLevel.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        yearLevel.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewYearLevel(yearLevel)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openYearLevelModal(
                                                'edit',
                                                yearLevel,
                                            )
                                        "
                                        title="Edit Year Level"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon delete"
                                        @click="
                                            confirmDeleteYearLevel(yearLevel)
                                        "
                                        title="Delete Year Level"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredYearLevels.length === 0">
                            <td colspan="7" class="empty-table">
                                <div class="empty-message">
                                    <Building :size="40" />
                                    <p>No year levels found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredYearLevels.length }} of
                    {{ yearLevels.length }} year levels
                </span>
            </div>
        </div>

        <!-- Year Level Modal (Add/Edit/View) -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showYearLevelModal"
                    class="modal-overlay"
                    @click.self="closeYearLevelModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header year-level-header">
                            <h3>
                                <Plus
                                    v-if="yearLevelModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="yearLevelModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    yearLevelModalMode === "add"
                                        ? "Add New Year Level"
                                        : yearLevelModalMode === "edit"
                                          ? "Edit Year Level"
                                          : "Year Level Details"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeYearLevelModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- View Mode -->
                            <div
                                v-if="yearLevelModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div
                                        class="detail-avatar year-level"
                                        :class="selectedYearLevel.level_type"
                                    >
                                        <Building :size="28" />
                                    </div>
                                    <div class="detail-title">
                                        <h4>{{ selectedYearLevel.name }}</h4>
                                        <span class="code-badge">{{
                                            selectedYearLevel.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Level Type</label>
                                        <span
                                            class="type-badge"
                                            :class="
                                                selectedYearLevel.level_type
                                            "
                                        >
                                            {{
                                                formatLevelType(
                                                    selectedYearLevel.level_type,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Sections Count</label>
                                        <span>{{
                                            selectedYearLevel.sections_count ||
                                            0
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Subjects Count</label>
                                        <span>{{
                                            selectedYearLevel.subjects_count ||
                                            0
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status</label>
                                        <span
                                            class="status-badge"
                                            :class="
                                                selectedYearLevel.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            "
                                        >
                                            {{
                                                selectedYearLevel.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="detail-item full-width"
                                        v-if="selectedYearLevel.description"
                                    >
                                        <label>Description</label>
                                        <span>{{
                                            selectedYearLevel.description
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add/Edit Form -->
                            <form
                                v-else
                                @submit.prevent="submitYearLevelForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="yearLevelForm.name"
                                            type="text"
                                            required
                                            placeholder="Enter year level name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Code
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="yearLevelForm.code"
                                            type="text"
                                            required
                                            placeholder="e.g., G7, G11"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Level Type
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <select
                                            v-model="yearLevelForm.level_type"
                                            required
                                        >
                                            <option value="">
                                                Select level type
                                            </option>
                                            <option value="junior_high">
                                                Junior High
                                            </option>
                                            <option value="senior_high">
                                                Senior High
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select
                                            v-model="yearLevelForm.is_active"
                                        >
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
                                            v-model="yearLevelForm.description"
                                            rows="3"
                                            placeholder="Enter year level description (optional)"
                                        ></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeYearLevelModal"
                            >
                                {{
                                    yearLevelModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="yearLevelModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitYearLevelForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    yearLevelModalMode === "add"
                                        ? "Create Year Level"
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
                                    Are you sure you want to delete this year
                                    level?
                                </p>
                                <div class="delete-year-level-info">
                                    <strong>{{
                                        yearLevelToDelete?.name
                                    }}</strong>
                                    <span
                                        >Code:
                                        {{ yearLevelToDelete?.code }}</span
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
                                @click="deleteYearLevel"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Year Level
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
    Building,
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
    yearLevels: {
        type: Array,
        default: () => [],
    },
});

// Search & Filter State
const searchQuery = ref("");
const levelTypeFilter = ref("all");
const statusFilter = ref("all");

// Modal State
const showYearLevelModal = ref(false);
const yearLevelModalMode = ref("add");
const selectedYearLevel = ref(null);
const showDeleteModal = ref(false);
const yearLevelToDelete = ref(null);
const isSubmitting = ref(false);

// Form State
const yearLevelForm = ref({
    name: "",
    code: "",
    description: "",
    level_type: "",
    is_active: true,
});

// Computed Stats
const totalCount = computed(() => {
    return props.yearLevels.length;
});

const juniorHighCount = computed(() => {
    return props.yearLevels.filter((y) => y.level_type === "junior_high")
        .length;
});

const seniorHighCount = computed(() => {
    return props.yearLevels.filter((y) => y.level_type === "senior_high")
        .length;
});

const activeCount = computed(() => {
    return props.yearLevels.filter((y) => y.is_active).length;
});

// Filtered Year Levels
const filteredYearLevels = computed(() => {
    let filtered = props.yearLevels;

    // Search filter
    if (searchQuery.value) {
        const search = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (yearLevel) =>
                yearLevel.name?.toLowerCase().includes(search) ||
                yearLevel.code?.toLowerCase().includes(search),
        );
    }

    // Level type filter
    if (levelTypeFilter.value !== "all") {
        filtered = filtered.filter(
            (yearLevel) => yearLevel.level_type === levelTypeFilter.value,
        );
    }

    // Status filter
    if (statusFilter.value !== "all") {
        filtered = filtered.filter((yearLevel) =>
            statusFilter.value === "active"
                ? yearLevel.is_active
                : !yearLevel.is_active,
        );
    }

    return filtered;
});

// Helper Functions
const formatLevelType = (type) => {
    if (!type) return "-";
    const typeMap = {
        junior_high: "Junior High",
        senior_high: "Senior High",
    };
    return typeMap[type] || type;
};

const resetYearLevelForm = () => {
    yearLevelForm.value = {
        name: "",
        code: "",
        description: "",
        level_type: "",
        is_active: true,
    };
};

// Modal Functions
const openYearLevelModal = (mode, yearLevel = null) => {
    yearLevelModalMode.value = mode;
    if (mode === "edit" && yearLevel) {
        selectedYearLevel.value = yearLevel;
        yearLevelForm.value = {
            name: yearLevel.name || "",
            code: yearLevel.code || "",
            description: yearLevel.description || "",
            level_type: yearLevel.level_type || "",
            is_active: yearLevel.is_active ?? true,
        };
    } else if (mode === "add") {
        resetYearLevelForm();
        selectedYearLevel.value = null;
    }
    showYearLevelModal.value = true;
};

const viewYearLevel = (yearLevel) => {
    selectedYearLevel.value = yearLevel;
    yearLevelModalMode.value = "view";
    showYearLevelModal.value = true;
};

const closeYearLevelModal = () => {
    showYearLevelModal.value = false;
    resetYearLevelForm();
    selectedYearLevel.value = null;
};

// Form Submission
const submitYearLevelForm = () => {
    isSubmitting.value = true;

    if (yearLevelModalMode.value === "add") {
        router.post("/admin/year-levels", yearLevelForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Year level created successfully!");
                closeYearLevelModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to create year level.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else if (yearLevelModalMode.value === "edit" && selectedYearLevel.value) {
        router.put(
            `/admin/year-levels/${selectedYearLevel.value.id}`,
            yearLevelForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Year level updated successfully!");
                    closeYearLevelModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError || "Failed to update year level.");
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

// Delete Functions
const confirmDeleteYearLevel = (yearLevel) => {
    yearLevelToDelete.value = yearLevel;
    showDeleteModal.value = true;
};

const deleteYearLevel = () => {
    if (!yearLevelToDelete.value) return;
    isSubmitting.value = true;

    router.delete(`/admin/year-levels/${yearLevelToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Year level deleted successfully!");
            showDeleteModal.value = false;
            yearLevelToDelete.value = null;
        },
        onError: () => {
            toast.error("Failed to delete year level.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.year-level-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.year-level-icon-sm,
.year-level-icon-sm.junior_high,
.year-level-icon-sm.senior_high {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2f6;
    color: #003366;
}

.year-level-info-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.year-level-name-cell {
    font-weight: 600;
    color: #003366;
}

.year-level-code {
    font-size: 0.75rem;
    color: #555;
    font-family: monospace;
}

.count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    padding: 0.15rem 0.4rem;
    font-size: 0.8rem;
    font-weight: 600;
    background: #fff;
    color: #003366;
    border: 1px solid #c5c5c5;
}

.detail-avatar.year-level,
.detail-avatar.year-level.junior_high,
.detail-avatar.year-level.senior_high {
    background: #003366;
    color: #fff;
}

.delete-year-level-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.7rem 0.85rem;
    background: #f7f7f7;
    border: 1px solid #ddd;
    margin: 0.75rem 0;
}

.delete-year-level-info strong {
    color: #003366;
}

.delete-year-level-info span {
    font-size: 0.875rem;
    color: #555;
}
</style>
