<template>
    <StudentLayout
        title="Profile"
        pageTitle="Student Profile"
        currentPage="profile"
        :user="user"
    >
        <div class="content-section">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar large">
                            <img
                                v-if="profilePhotoUrl"
                                :src="profilePhotoUrl"
                                alt="Profile"
                                class="avatar-img"
                            />
                            <span v-else
                                >{{ user.first_name?.charAt(0)
                                }}{{ user.last_name?.charAt(0) }}</span
                            >
                        </div>
                        <div class="avatar-actions">
                            <button
                                @click="triggerPhotoUpload"
                                class="avatar-btn upload-btn"
                                :disabled="isUploadingPhoto"
                                title="Upload Photo"
                            >
                                <Camera :size="16" />
                            </button>
                            <button
                                v-if="profilePhotoUrl"
                                @click="showRemovePhotoModal = true"
                                class="avatar-btn remove-btn"
                                title="Remove Photo"
                            >
                                <Trash2 :size="16" />
                            </button>
                        </div>
                        <input
                            ref="photoInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif"
                            @change="handlePhotoChange"
                            class="hidden-input"
                        />
                    </div>
                    <div class="profile-info">
                        <h2>
                            {{ user.first_name }} {{ user.middle_name }}
                            {{ user.last_name }} {{ user.suffix }}
                        </h2>
                        <p class="profile-role">Student</p>
                    </div>
                    <button
                        v-if="!isEditing"
                        @click="startEditing"
                        class="edit-profile-btn"
                    >
                        <Pencil :size="16" />
                        Edit Profile
                    </button>
                </div>

                <!-- View Mode -->
                <div v-if="!isEditing" class="profile-details">
                    <div class="detail-item">
                        <label>Email</label><span>{{ user.email }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Phone</label
                        ><span>{{ user.phone_no || "N/A" }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Date of Birth</label
                        ><span>{{ user.date_of_birth || "N/A" }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Gender</label
                        ><span>{{ user.gender || "N/A" }}</span>
                    </div>
                    <div class="detail-item">
                        <label>LRN</label><span>{{ user.lrn || "N/A" }}</span>
                    </div>
                    <div v-if="user.preferred_strand" class="detail-item">
                        <label>Academic Track</label>
                        <span>{{ user.preferred_strand }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Address</label
                        ><span
                            >{{ user.barangay }}, {{ user.municipality }},
                            {{ user.province }}</span
                        >
                    </div>
                    <div class="detail-item">
                        <label>Guardian</label
                        ><span>{{ user.guardian_full_name || "N/A" }}</span>
                    </div>
                    <div class="detail-item">
                        <label>Guardian Contact</label
                        ><span>{{ user.guardian_contact_no || "N/A" }}</span>
                    </div>
                </div>

                <!-- Edit Mode -->
                <div v-else class="profile-edit-form">
                    <form @submit.prevent="saveProfile">
                        <div class="edit-grid">
                            <div class="edit-group">
                                <label for="edit_first_name">First Name</label>
                                <input
                                    id="edit_first_name"
                                    v-model="editForm.first_name"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_middle_name"
                                    >Middle Name</label
                                >
                                <input
                                    id="edit_middle_name"
                                    v-model="editForm.middle_name"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_last_name">Last Name</label>
                                <input
                                    id="edit_last_name"
                                    v-model="editForm.last_name"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_suffix">Suffix</label>
                                <input
                                    id="edit_suffix"
                                    v-model="editForm.suffix"
                                    type="text"
                                    class="edit-input"
                                    placeholder="Jr., Sr., III"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_email">Email</label>
                                <input
                                    id="edit_email"
                                    v-model="editForm.email"
                                    type="email"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_phone_no">Phone No.</label>
                                <input
                                    id="edit_phone_no"
                                    v-model="editForm.phone_no"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_date_of_birth"
                                    >Date of Birth</label
                                >
                                <input
                                    id="edit_date_of_birth"
                                    v-model="editForm.date_of_birth"
                                    type="date"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label>Gender</label>
                                <div class="edit-radio-group">
                                    <label class="radio-option"
                                        ><input
                                            type="radio"
                                            v-model="editForm.gender"
                                            value="Male"
                                        />
                                        Male</label
                                    >
                                    <label class="radio-option"
                                        ><input
                                            type="radio"
                                            v-model="editForm.gender"
                                            value="Female"
                                        />
                                        Female</label
                                    >
                                </div>
                            </div>
                            <div class="edit-group">
                                <label for="edit_lrn">LRN</label>
                                <input
                                    id="edit_lrn"
                                    v-model="editForm.lrn"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_previous_gwa"
                                    >Previous GWA</label
                                >
                                <input
                                    id="edit_previous_gwa"
                                    v-model="editForm.previous_gwa"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_guardian_full_name"
                                    >Guardian Full Name</label
                                >
                                <input
                                    id="edit_guardian_full_name"
                                    v-model="editForm.guardian_full_name"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                            <div class="edit-group">
                                <label for="edit_guardian_contact_no"
                                    >Guardian Contact No.</label
                                >
                                <input
                                    id="edit_guardian_contact_no"
                                    v-model="editForm.guardian_contact_no"
                                    type="text"
                                    class="edit-input"
                                />
                            </div>
                        </div>
                        <div class="edit-actions">
                            <button
                                type="button"
                                @click="cancelEditing"
                                class="cancel-btn"
                            >
                                <X :size="16" /> Cancel
                            </button>
                            <button
                                type="submit"
                                class="save-btn"
                                :disabled="isSaving"
                            >
                                <Save :size="16" />
                                {{ isSaving ? "Saving..." : "Save Changes" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="showRemovePhotoModal"
            title="Remove Photo"
            message="Remove your profile photo?"
            confirm-label="Remove"
            @cancel="showRemovePhotoModal = false"
            @confirm="confirmRemovePhoto"
        />
    </StudentLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { Camera, Trash2, Pencil, X, Save } from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: { type: Object, required: true },
});

const isEditing = ref(false);
const isSaving = ref(false);
const photoInput = ref(null);
const isUploadingPhoto = ref(false);
const showRemovePhotoModal = ref(false);

const profilePhotoUrl = computed(() =>
    props.user.profile_photo ? `/storage/${props.user.profile_photo}` : null,
);

const editForm = ref({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    email: "",
    phone_no: "",
    date_of_birth: "",
    gender: "",
    lrn: "",
    previous_gwa: "",
    guardian_full_name: "",
    guardian_contact_no: "",
});

const startEditing = () => {
    editForm.value = {
        first_name: props.user.first_name || "",
        middle_name: props.user.middle_name || "",
        last_name: props.user.last_name || "",
        suffix: props.user.suffix || "",
        email: props.user.email || "",
        phone_no: props.user.phone_no || "",
        date_of_birth: props.user.date_of_birth || "",
        gender: props.user.gender || "",
        lrn: props.user.lrn || "",
        previous_gwa: props.user.previous_gwa || "",
        guardian_full_name: props.user.guardian_full_name || "",
        guardian_contact_no: props.user.guardian_contact_no || "",
    };
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
};

const triggerPhotoUpload = () => {
    photoInput.value?.click();
};

const handlePhotoChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
    if (!allowedTypes.includes(file.type)) {
        toast.error("Please select a valid image file");
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        toast.error("Image size must be less than 2MB");
        return;
    }
    uploadPhoto(file);
};

const uploadPhoto = (file) => {
    isUploadingPhoto.value = true;
    const formData = new FormData();
    formData.append("profile_photo", file);
    router.post("/profile/photo", formData, {
        forceFormData: true,
        onSuccess: () => {
            isUploadingPhoto.value = false;
            toast.success("Profile photo updated!");
        },
        onError: () => {
            isUploadingPhoto.value = false;
            toast.error("Failed to upload photo");
        },
    });
};

const confirmRemovePhoto = () => {
    router.delete("/profile/photo", {
        onSuccess: () => {
            showRemovePhotoModal.value = false;
            toast.success("Photo removed");
        },
        onError: () => toast.error("Failed to remove photo"),
    });
};

const saveProfile = () => {
    isSaving.value = true;
    router.put("/profile/update", editForm.value, {
        onSuccess: () => {
            isEditing.value = false;
            isSaving.value = false;
            toast.success("Profile updated!");
        },
        onError: () => {
            isSaving.value = false;
            toast.error("Failed to update profile");
        },
    });
};
</script>

<style scoped>
.profile-card {
    background: white;
    border: 1px solid #c5c5c5;
}

.profile-header {
    background: #003366;
    padding: 0.9rem 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white;
    position: relative;
    border-bottom: 3px solid #c9a227;
}

.edit-profile-btn {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    background: #fff;
    border: 1px solid #fff;
    color: #003366;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
}

.edit-profile-btn:hover {
    background: #e8eef4;
}

.profile-avatar {
    width: 64px;
    height: 64px;
    border-radius: 0;
    background: #002244;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 600;
    border: 1px solid #fff;
    overflow: hidden;
}

.profile-avatar.large {
    width: 72px;
    height: 72px;
    font-size: 1.35rem;
}

.profile-avatar .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.45rem;
}

.avatar-actions {
    display: flex;
    gap: 0.35rem;
}

.avatar-btn {
    width: 28px;
    height: 28px;
    border: 1px solid #fff;
    background: #fff;
    color: #003366;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.avatar-btn.remove-btn {
    background: #9b1c1c;
    border-color: #9b1c1c;
    color: white;
}

.hidden-input {
    display: none;
}

.profile-info h2 {
    margin: 0;
    font-size: 1.15rem;
}

.profile-role {
    margin: 0.2rem 0 0 0;
    font-size: 0.8rem;
    opacity: 0.9;
}

.profile-details {
    padding: 1rem 1.1rem;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.85rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.detail-item label {
    font-size: 0.78rem;
    color: #555;
    font-weight: 600;
}

.detail-item span {
    font-size: 0.92rem;
    color: #222;
}

.profile-edit-form {
    padding: 1rem 1.1rem;
}

.edit-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.85rem;
}

.edit-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.edit-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
}

.edit-input {
    padding: 0.45rem 0.55rem;
    border: 1px solid #bdbdbd;
    font-size: 0.9rem;
}

.edit-input:focus {
    outline: none;
    border-color: #003366;
}

.edit-radio-group {
    display: flex;
    gap: 1.25rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    cursor: pointer;
}

.edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 0.85rem;
    border-top: 1px solid #d8d8d8;
}

.cancel-btn,
.save-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.cancel-btn {
    background: #fff;
    border: 1px solid #bdbdbd;
    color: #333;
}

.cancel-btn:hover {
    background: #f4f4f4;
}

.save-btn {
    background: #003366;
    border: none;
    color: white;
}

.save-btn:hover:not(:disabled) {
    background: #00264d;
}

.save-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .edit-profile-btn {
        position: static;
        transform: none;
        margin-top: 0.5rem;
    }

    .profile-details,
    .edit-grid {
        grid-template-columns: 1fr;
    }
}
</style>
