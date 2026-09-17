<template>
    <AdminLayout
        title="Academic Tracks"
        pageTitle="Academic Tracks"
        currentPage="strands"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Academic Tracks</h2>
                </div>
                <p class="gov-lede">
                    Academic tracks created here appear as Preferred Academic
                    Track choices on the admission form for Grade 11 and Grade
                    12 applicants.
                </p>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total</div>
                    <div class="gov-stat-value">{{ totalCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Active</div>
                    <div class="gov-stat-value">{{ activeCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Inactive</div>
                    <div class="gov-stat-value">{{ inactiveCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Applicants</div>
                    <div class="gov-stat-value">{{ applicantsCount }}</div>
                </div>
            </div>

            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search academic tracks by name or code..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select v-model="statusFilter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button class="btn-primary" @click="openStrandModal('add')">
                        <Plus :size="18" />
                        Add Academic Track
                    </button>
                </div>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Academic Track</th>
                            <th>Applicants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="strand in filteredStrands" :key="strand.id">
                            <td>
                                <div class="strand-cell">
                                    <div class="strand-icon-sm">
                                        <Waypoints :size="18" />
                                    </div>
                                    <div class="strand-info-cell">
                                        <span class="strand-name-cell">{{
                                            strand.name
                                        }}</span>
                                        <span class="strand-code">{{
                                            strand.code
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="count-badge">{{
                                    strand.applicants_count || 0
                                }}</span>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        strand.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        strand.is_active ? "Active" : "Inactive"
                                    }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewStrand(strand)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openStrandModal('edit', strand)
                                        "
                                        title="Edit Academic Track"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon delete"
                                        @click="confirmDeleteStrand(strand)"
                                        title="Delete Academic Track"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredStrands.length === 0">
                            <td colspan="4" class="empty-table">
                                <div class="empty-message">
                                    <Waypoints :size="40" />
                                    <p>No academic tracks found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredStrands.length }} of
                    {{ strands.length }} academic tracks
                </span>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showStrandModal"
                    class="modal-overlay"
                    @click.self="closeStrandModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header strand-header">
                            <h3>
                                <Plus
                                    v-if="strandModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="strandModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    strandModalMode === "add"
                                        ? "Add New Academic Track"
                                        : strandModalMode === "edit"
                                          ? "Edit Academic Track"
                                          : "Academic Track Details"
                                }}
                            </h3>
                            <button class="close-btn" @click="closeStrandModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div
                                v-if="strandModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div class="detail-avatar strand">
                                        <Waypoints :size="28" />
                                    </div>
                                    <div class="detail-title">
                                        <h4>{{ selectedStrand.name }}</h4>
                                        <span class="code-badge">{{
                                            selectedStrand.code
                                        }}</span>
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Applicants</label>
                                        <span>{{
                                            selectedStrand.applicants_count || 0
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Status</label>
                                        <span
                                            class="status-badge"
                                            :class="
                                                selectedStrand.is_active
                                                    ? 'active'
                                                    : 'inactive'
                                            "
                                        >
                                            {{
                                                selectedStrand.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="detail-item full-width"
                                        v-if="selectedStrand.description"
                                    >
                                        <label>Description</label>
                                        <span>{{
                                            selectedStrand.description
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <form
                                v-else
                                @submit.prevent="submitStrandForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Name
                                            <span class="required">*</span>
                                        </label>
                                        <input
                                            v-model="strandForm.name"
                                            type="text"
                                            required
                                            placeholder="e.g., Science, Technology, Engineering, and Mathematics"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Code
                                            <span class="required">*</span>
                                        </label>
                                        <input
                                            v-model="strandForm.code"
                                            type="text"
                                            required
                                            placeholder="e.g., STEM"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select v-model="strandForm.is_active">
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
                                            v-model="strandForm.description"
                                            rows="3"
                                            placeholder="Enter academic track description (optional)"
                                        ></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeStrandModal"
                            >
                                {{
                                    strandModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="strandModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitStrandForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    strandModalMode === "add"
                                        ? "Create Academic Track"
                                        : "Save Changes"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

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
                                    academic track?
                                </p>
                                <div class="delete-strand-info">
                                    <strong>{{ strandToDelete?.name }}</strong>
                                    <span
                                        >Code: {{ strandToDelete?.code }}</span
                                    >
                                </div>
                                <p class="warning-text">
                                    Inactive academic tracks are hidden from
                                    the admission form. Delete only if no
                                    student has selected this academic track.
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
                                @click="deleteStrand"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Academic Track
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
    Waypoints,
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
    strands: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref("");
const statusFilter = ref("all");

const showStrandModal = ref(false);
const strandModalMode = ref("add");
const selectedStrand = ref(null);
const showDeleteModal = ref(false);
const strandToDelete = ref(null);
const isSubmitting = ref(false);

const strandForm = ref({
    name: "",
    code: "",
    description: "",
    is_active: true,
});

const totalCount = computed(() => props.strands.length);

const activeCount = computed(() => {
    return props.strands.filter((strand) => strand.is_active).length;
});

const inactiveCount = computed(() => {
    return props.strands.filter((strand) => !strand.is_active).length;
});

const applicantsCount = computed(() => {
    return props.strands.reduce(
        (sum, strand) => sum + (strand.applicants_count || 0),
        0,
    );
});

const filteredStrands = computed(() => {
    let filtered = props.strands;

    if (searchQuery.value) {
        const search = searchQuery.value.toLowerCase();
        filtered = filtered.filter(
            (strand) =>
                strand.name?.toLowerCase().includes(search) ||
                strand.code?.toLowerCase().includes(search),
        );
    }

    if (statusFilter.value !== "all") {
        filtered = filtered.filter((strand) =>
            statusFilter.value === "active"
                ? strand.is_active
                : !strand.is_active,
        );
    }

    return filtered;
});

const resetStrandForm = () => {
    strandForm.value = {
        name: "",
        code: "",
        description: "",
        is_active: true,
    };
};

const openStrandModal = (mode, strand = null) => {
    strandModalMode.value = mode;
    if (mode === "edit" && strand) {
        selectedStrand.value = strand;
        strandForm.value = {
            name: strand.name || "",
            code: strand.code || "",
            description: strand.description || "",
            is_active: strand.is_active ?? true,
        };
    } else if (mode === "add") {
        resetStrandForm();
        selectedStrand.value = null;
    }
    showStrandModal.value = true;
};

const viewStrand = (strand) => {
    selectedStrand.value = strand;
    strandModalMode.value = "view";
    showStrandModal.value = true;
};

const closeStrandModal = () => {
    showStrandModal.value = false;
    resetStrandForm();
    selectedStrand.value = null;
};

const submitStrandForm = () => {
    isSubmitting.value = true;

    if (strandModalMode.value === "add") {
        router.post("/admin/strands", strandForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Academic track created successfully!");
                closeStrandModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to create academic track.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else if (strandModalMode.value === "edit" && selectedStrand.value) {
        router.put(
            `/admin/strands/${selectedStrand.value.id}`,
            strandForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Academic track updated successfully!");
                    closeStrandModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(
                        firstError || "Failed to update academic track.",
                    );
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

const confirmDeleteStrand = (strand) => {
    strandToDelete.value = strand;
    showDeleteModal.value = true;
};

const deleteStrand = () => {
    if (!strandToDelete.value) return;
    isSubmitting.value = true;

    router.delete(`/admin/strands/${strandToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Academic track deleted successfully!");
            showDeleteModal.value = false;
            strandToDelete.value = null;
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            toast.error(firstError || "Failed to delete academic track.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.gov-lede {
    margin: 0.5rem 0 0;
    max-width: 42rem;
    color: #555;
    font-size: 0.9rem;
    line-height: 1.45;
}

.strand-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.strand-icon-sm {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2f6;
    color: #003366;
}

.strand-info-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.strand-name-cell {
    font-weight: 600;
    color: #003366;
}

.strand-code {
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

.detail-avatar.strand {
    background: #003366;
    color: #fff;
}

.delete-strand-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.7rem 0.85rem;
    background: #f7f7f7;
    border: 1px solid #ddd;
    margin: 0.75rem 0;
}

.delete-strand-info strong {
    color: #003366;
}

.delete-strand-info span {
    font-size: 0.875rem;
    color: #555;
}
</style>
