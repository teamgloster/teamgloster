<template>
    <StudentLayout
        title="Requirements"
        pageTitle="Requirements"
        currentPage="requirements"
        :user="user"
    >
        <div class="content-section">
            <div class="requirements-container">
                <div class="requirements-header">
                    <h2>Student Requirements</h2>
                    <p>
                        Please submit all required documents for enrollment
                        verification
                    </p>
                </div>

                <div class="requirements-grid">
                    <div
                        v-for="requirement in localRequirements"
                        :key="requirement.id"
                        class="requirement-card"
                    >
                        <div class="requirement-header">
                            <div class="requirement-info">
                                <h3>{{ requirement.name }}</h3>
                                <p class="requirement-description">
                                    {{ requirement.description }}
                                </p>
                            </div>
                            <span
                                v-if="requirement.submitted"
                                class="status-badge submitted"
                            >
                                <Check :size="14" /> Submitted
                            </span>
                            <span v-else class="status-badge pending"
                                >Pending</span
                            >
                        </div>

                        <div class="requirement-content">
                            <div
                                v-if="requirement.submitted"
                                class="submitted-file"
                            >
                                <div class="file-info">
                                    <FileText :size="20" />
                                    <div>
                                        <p class="file-name">
                                            {{ requirement.file_name }}
                                        </p>
                                        <p class="file-date">
                                            Submitted on:
                                            {{ requirement.submitted_date }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="removeRequirement(requirement.id)"
                                    class="remove-btn"
                                    title="Remove file"
                                >
                                    <Trash2 :size="16" />
                                </button>
                            </div>

                            <div v-else class="upload-area">
                                <div
                                    class="upload-drop-zone"
                                    @click="
                                        triggerRequirementUpload(requirement.id)
                                    "
                                    @dragover.prevent="
                                        requirement.dragOver = true
                                    "
                                    @dragleave.prevent="
                                        requirement.dragOver = false
                                    "
                                    @drop.prevent="
                                        handleRequirementDrop(
                                            $event,
                                            requirement.id,
                                        )
                                    "
                                    :class="{
                                        'drag-over': requirement.dragOver,
                                    }"
                                >
                                    <Upload :size="32" />
                                    <p>Drag and drop your file here</p>
                                    <p class="upload-hint">
                                        or click to select
                                    </p>
                                    <p class="file-type-hint">
                                        {{ getFileTypeHint(requirement.id) }}
                                    </p>
                                </div>
                                <input
                                    :ref="
                                        (el) => {
                                            requirementInputRefs[
                                                requirement.id
                                            ] = el;
                                        }
                                    "
                                    type="file"
                                    class="hidden-input"
                                    :accept="getAcceptTypes(requirement.id)"
                                    @change="
                                        handleRequirementChange(
                                            $event,
                                            requirement.id,
                                        )
                                    "
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="requirements-info">
                    <h4><AlertCircle :size="18" /> Important Notes:</h4>
                    <ul>
                        <li>All documents must be clear and legible</li>
                        <li>Accepted formats: PDF, JPG, PNG (Max 5MB each)</li>
                        <li>
                            Form 137 must be the original copy from previous
                            school
                        </li>
                        <li>2x2 picture must be recent (within 6 months)</li>
                        <li>Birth Certificate must be from PSA/NEC</li>
                        <li>
                            Good Moral Certificate must be from previous school
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showDeleteModal"
                    class="modal-overlay"
                    @click.self="closeDeleteModal"
                >
                    <div class="modal-container">
                        <div class="modal-icon">
                            <AlertCircle :size="48" />
                        </div>
                        <h3>Remove Document</h3>
                        <p>
                            Are you sure you want to remove
                            <strong>{{
                                getRequirementName(deleteTargetId)
                            }}</strong
                            >?
                        </p>
                        <p class="modal-warning">
                            You will need to upload it again for enrollment
                            verification.
                        </p>
                        <div class="modal-actions">
                            <button
                                class="btn-cancel"
                                @click="closeDeleteModal"
                            >
                                Cancel
                            </button>
                            <button
                                class="btn-delete"
                                @click="confirmDelete"
                                :disabled="isDeleting"
                            >
                                <span v-if="isDeleting">Removing...</span>
                                <template v-else>
                                    <Trash2 :size="16" />
                                    <span>Remove</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </StudentLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import { FileText, Upload, Trash2, Check, AlertCircle } from "lucide-vue-next";

const props = defineProps({
    user: { type: Object, required: true },
    requirements: { type: Array, default: () => [] },
});

const toast = useToast();

// Local state for requirements with dragOver property
const localRequirements = ref(
    props.requirements.map((r) => ({ ...r, dragOver: false })),
);

const requirementInputRefs = reactive({});

// Delete modal state
const showDeleteModal = ref(false);
const deleteTargetId = ref(null);
const isDeleting = ref(false);

const getRequirementName = (id) => {
    const names = {
        1: "Form 137",
        2: "2x2 ID Picture",
        3: "Birth Certificate",
        4: "Good Moral Certificate",
    };
    return names[id] || "this document";
};

const openDeleteModal = (requirementId) => {
    deleteTargetId.value = requirementId;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteTargetId.value = null;
};

// Get accept types per requirement
const getAcceptTypes = (requirementId) => {
    if (requirementId === 2) return ".jpg,.jpeg,.png"; // 2x2 Picture - images only
    return ".pdf"; // Form 137, Birth Certificate, Good Moral - PDF only
};

// Get file type hint text
const getFileTypeHint = (requirementId) => {
    if (requirementId === 2) return "Accepts: JPG, PNG";
    return "Accepts: PDF only";
};

const triggerRequirementUpload = (requirementId) => {
    const input = requirementInputRefs[requirementId];
    if (input) input.click();
};

const handleRequirementChange = (event, requirementId) => {
    const file = event.target.files[0];
    if (file) {
        uploadRequirement(file, requirementId);
    }
};

const handleRequirementDrop = (event, requirementId) => {
    const file = event.dataTransfer.files[0];
    const req = localRequirements.value.find((r) => r.id === requirementId);
    if (req) req.dragOver = false;
    if (file) {
        uploadRequirement(file, requirementId);
    }
};

const uploadRequirement = (file, requirementId) => {
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        toast.error("File size must not exceed 5MB");
        return;
    }

    // Define allowed types per requirement
    const requirementConfig = {
        1: {
            // Form 137
            types: ["application/pdf"],
            name: "Form 137",
            hint: "PDF files only",
        },
        2: {
            // 2x2 Picture
            types: ["image/jpeg", "image/png"],
            name: "2x2 ID Picture",
            hint: "JPG or PNG images only",
        },
        3: {
            // Birth Certificate
            types: ["application/pdf"],
            name: "Birth Certificate",
            hint: "PDF files only",
        },
        4: {
            // Good Moral Certificate
            types: ["application/pdf"],
            name: "Good Moral Certificate",
            hint: "PDF files only",
        },
    };

    const config = requirementConfig[requirementId];
    if (config && !config.types.includes(file.type)) {
        toast.error(`Invalid file type for ${config.name}. ${config.hint}.`);
        return;
    }

    const formData = new FormData();
    formData.append("file", file);
    formData.append("requirement_id", requirementId);

    router.post("/requirements/upload", formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Update local state
            const req = localRequirements.value.find(
                (r) => r.id === requirementId,
            );
            if (req) {
                req.submitted = true;
                req.file_name = file.name;
                req.submitted_date = new Date().toLocaleDateString();
            }
            toast.success("Document uploaded successfully!");
        },
        onError: (errors) => {
            console.error("Upload failed:", errors);
            toast.error(
                errors.file || "Failed to upload file. Please try again.",
            );
        },
    });
};

const removeRequirement = (requirementId) => {
    openDeleteModal(requirementId);
};

const confirmDelete = () => {
    if (!deleteTargetId.value) return;
    isDeleting.value = true;

    router.post(
        "/requirements/remove",
        { requirement_id: deleteTargetId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                const req = localRequirements.value.find(
                    (r) => r.id === deleteTargetId.value,
                );
                if (req) {
                    req.submitted = false;
                    req.file_name = null;
                    req.submitted_date = null;
                }
                toast.success("Document removed successfully!");
                isDeleting.value = false;
                closeDeleteModal();
            },
            onError: (errors) => {
                console.error("Delete failed:", errors);
                toast.error("Failed to remove file. Please try again.");
                isDeleting.value = false;
            },
        },
    );
};
</script>

<style scoped>
.requirements-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.requirements-header {
    margin-bottom: 0.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #cfcfcf;
}

.requirements-header h2 {
    margin: 0 0 0.2rem 0;
    color: #003366;
    font-size: 1.2rem;
}

.requirements-header p {
    margin: 0;
    color: #555;
    font-size: 0.85rem;
}

.requirements-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
}

.requirement-card {
    background: white;
    border: 1px solid #c5c5c5;
    padding: 0;
}

.requirement-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    border-bottom: 3px solid #c9a227;
}

.requirement-info h3 {
    margin: 0;
    color: #fff;
    font-size: 0.9rem;
}

.requirement-description {
    margin: 0.15rem 0 0;
    color: #d8e4f0;
    font-size: 0.78rem;
}

.status-badge {
    padding: 0.15rem 0.45rem;
    border: 1px solid #fff;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    background: transparent;
    color: #fff;
}

.status-badge.submitted,
.status-badge.pending {
    background: transparent;
    color: #fff;
}

.requirement-content {
    padding: 0.85rem 0.9rem;
}

.submitted-file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    padding: 0;
    border: none;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: #003366;
}

.file-name {
    margin: 0;
    font-weight: 600;
    color: #222;
    font-size: 0.88rem;
}

.file-date {
    margin: 0;
    font-size: 0.78rem;
    color: #555;
}

.remove-btn {
    background: #fff;
    border: 1px solid #9b1c1c;
    color: #9b1c1c;
    width: auto;
    height: auto;
    padding: 0.25rem 0.4rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-btn:hover {
    background: #9b1c1c;
    color: white;
}

.upload-area {
    margin-top: 0.25rem;
}

.upload-drop-zone {
    border: 1px dashed #bdbdbd;
    padding: 1rem 0.85rem;
    text-align: left;
    cursor: pointer;
    background: #fff;
}

.upload-drop-zone:hover,
.upload-drop-zone.drag-over {
    border-color: #003366;
    background: #f4f7fb;
}

.upload-drop-zone svg {
    display: none;
}

.upload-drop-zone p {
    margin: 0;
    color: #555;
}

.upload-hint {
    font-size: 0.8rem;
    color: #555 !important;
}

.file-type-hint {
    font-size: 0.75rem;
    color: #003366 !important;
    background: #e8eef4;
    padding: 0.15rem 0.4rem;
    margin-top: 0.45rem !important;
    display: inline-block;
}

.hidden-input {
    display: none;
}

.requirements-info {
    background: #fff;
    border: 1px solid #c5c5c5;
    padding: 0.85rem 1rem;
}

.requirements-info h4 {
    margin: 0 0 0.5rem 0;
    color: #003366;
}

.requirements-info ul {
    margin: 0;
    padding-left: 1.2rem;
    color: #333;
}

.requirements-info li {
    margin-bottom: 0.3rem;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    z-index: 1000;
    padding: 4rem 1rem 1rem;
}

.modal-container {
    background: white;
    border: 1px solid #c5c5c5;
    padding: 0;
    max-width: 450px;
    width: 100%;
    text-align: left;
}

.modal-icon {
    display: none;
}

.modal-container h3 {
    margin: 0;
    color: #fff;
    background: #9b1c1c;
    font-size: 0.95rem;
    padding: 0.55rem 0.85rem;
    border-bottom: 3px solid #c9a227;
}

.modal-container p {
    margin: 0;
    color: #333;
    padding: 0.85rem 1rem 0;
}

.modal-warning {
    font-size: 0.8rem;
    color: #555 !important;
    padding-bottom: 0.85rem !important;
}

.modal-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    padding: 0.7rem 1rem;
    border-top: 1px solid #e0e0e0;
}

.btn-cancel {
    padding: 0.5rem 0.9rem;
    border: 1px solid #bdbdbd;
    background: white;
    color: #333;
    cursor: pointer;
    font-weight: 600;
}

.btn-cancel:hover {
    background: #f4f4f4;
}

.btn-delete {
    padding: 0.5rem 0.9rem;
    border: none;
    background: #9b1c1c;
    color: white;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-delete:hover:not(:disabled) {
    background: #7f1d1d;
}

.btn-delete:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.15s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
