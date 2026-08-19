<template>
    <AdminLayout
        title="Teachers"
        pageTitle="Teachers"
        currentPage="teachers"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Teachers</h2>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="teacherSearch"
                            type="text"
                            placeholder="Search teachers by name or email..."
                            class="search-input"
                        />
                    </div>
                    <button
                        class="btn-primary"
                        @click="openTeacherModal('add')"
                    >
                        <UserPlus :size="18" />
                        Add Teacher
                    </button>
                </div>
            </div>

            <!-- Teachers Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Gender</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="teacher in filteredTeachers"
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
                                            {{ teacher.suffix || "" }}
                                        </span>
                                        <span class="user-meta">
                                            {{
                                                formatDateFull(
                                                    teacher.date_of_birth,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ teacher.email }}</td>
                            <td>{{ teacher.phone_no || "-" }}</td>
                            <td>
                                <span
                                    class="gender-badge"
                                    :class="teacher.gender"
                                >
                                    {{
                                        teacher.gender
                                            ? teacher.gender
                                                  .charAt(0)
                                                  .toUpperCase() +
                                              teacher.gender.slice(1)
                                            : "-"
                                    }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewTeacher(teacher)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openTeacherModal('edit', teacher)
                                        "
                                        title="Edit Teacher"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon delete"
                                        @click="confirmDeleteTeacher(teacher)"
                                        title="Delete Teacher"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredTeachers.length === 0">
                            <td colspan="5" class="empty-table">
                                <div class="empty-message">
                                    <Users :size="40" />
                                    <p>No teachers found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredTeachers.length }} of
                    {{ teachers.length }} teachers
                </span>
            </div>
        </div>

        <!-- Teacher Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showTeacherModal"
                    class="modal-overlay"
                    @click.self="closeTeacherModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header teacher-header">
                            <h3>
                                <UserPlus
                                    v-if="teacherModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="teacherModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    teacherModalMode === "add"
                                        ? "Add New Teacher"
                                        : teacherModalMode === "edit"
                                          ? "Edit Teacher"
                                          : "Teacher Details"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeTeacherModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- View Mode -->
                            <div
                                v-if="teacherModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div class="detail-avatar teacher">
                                        {{ getInitials(selectedTeacher) }}
                                    </div>
                                    <div class="detail-title">
                                        <h4>
                                            {{ selectedTeacher.first_name }}
                                            {{ selectedTeacher.middle_name }}
                                            {{ selectedTeacher.last_name }}
                                            {{ selectedTeacher.suffix }}
                                        </h4>
                                        <span class="role-badge teacher"
                                            >Teacher</span
                                        >
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Email</label>
                                        <span>{{ selectedTeacher.email }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Phone</label>
                                        <span>{{
                                            selectedTeacher.phone_no ||
                                            "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Date of Birth</label>
                                        <span>{{
                                            formatDateFull(
                                                selectedTeacher.date_of_birth,
                                            ) || "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Gender</label>
                                        <span>{{
                                            selectedTeacher.gender
                                                ? selectedTeacher.gender
                                                      .charAt(0)
                                                      .toUpperCase() +
                                                  selectedTeacher.gender.slice(
                                                      1,
                                                  )
                                                : "Not provided"
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add/Edit Form -->
                            <form
                                v-else
                                @submit.prevent="submitTeacherForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >First Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="teacherForm.first_name"
                                            type="text"
                                            required
                                            placeholder="Enter first name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input
                                            v-model="teacherForm.middle_name"
                                            type="text"
                                            placeholder="Enter middle name"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Last Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="teacherForm.last_name"
                                            type="text"
                                            required
                                            placeholder="Enter last name"
                                        />
                                    </div>
                                    <div class="form-group small">
                                        <label>Suffix</label>
                                        <input
                                            v-model="teacherForm.suffix"
                                            type="text"
                                            placeholder="Jr., III"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Email
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="teacherForm.email"
                                            type="email"
                                            required
                                            placeholder="teacher@email.com"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input
                                            v-model="teacherForm.phone_no"
                                            type="tel"
                                            placeholder="09XXXXXXXXX"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input
                                            v-model="teacherForm.date_of_birth"
                                            type="date"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select v-model="teacherForm.gender">
                                            <option value="">
                                                Select gender
                                            </option>
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="form-row"
                                    v-if="teacherModalMode === 'add'"
                                >
                                    <div class="form-group">
                                        <label
                                            >Password
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="teacherForm.password"
                                            type="password"
                                            :required="
                                                teacherModalMode === 'add'
                                            "
                                            placeholder="Minimum 8 characters"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Confirm Password
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="
                                                teacherForm.password_confirmation
                                            "
                                            type="password"
                                            :required="
                                                teacherModalMode === 'add'
                                            "
                                            placeholder="Confirm password"
                                        />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeTeacherModal"
                            >
                                {{
                                    teacherModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="teacherModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitTeacherForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    teacherModalMode === "add"
                                        ? "Create Teacher"
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
                                    teacher?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{ teacherToDelete?.first_name }}
                                        {{ teacherToDelete?.last_name }}</strong
                                    >
                                    <span>{{ teacherToDelete?.email }}</span>
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
                                @click="deleteTeacher"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Teacher
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
    UserPlus,
    Eye,
    Pencil,
    Trash2,
    X,
    Loader2,
    AlertTriangle,
    GraduationCap,
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
});

// Teacher Management State
const teacherSearch = ref("");
const showTeacherModal = ref(false);
const teacherModalMode = ref("add");
const selectedTeacher = ref(null);
const showDeleteModal = ref(false);
const teacherToDelete = ref(null);
const isSubmitting = ref(false);

const teacherForm = ref({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    email: "",
    phone_no: "",
    date_of_birth: "",
    gender: "",
    password: "",
    password_confirmation: "",
});

const resetTeacherForm = () => {
    teacherForm.value = {
        first_name: "",
        middle_name: "",
        last_name: "",
        suffix: "",
        email: "",
        phone_no: "",
        date_of_birth: "",
        gender: "",
        password: "",
        password_confirmation: "",
    };
};

const getInitials = (user) => {
    if (!user) return "?";
    return (
        (user.first_name?.charAt(0) || "") + (user.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const formatDateFull = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
};

const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    const search = teacherSearch.value.toLowerCase();
    return props.teachers.filter(
        (teacher) =>
            teacher.first_name?.toLowerCase().includes(search) ||
            teacher.last_name?.toLowerCase().includes(search) ||
            teacher.email?.toLowerCase().includes(search),
    );
});

const openTeacherModal = (mode, teacher = null) => {
    teacherModalMode.value = mode;
    if (mode === "edit" && teacher) {
        selectedTeacher.value = teacher;
        teacherForm.value = {
            first_name: teacher.first_name || "",
            middle_name: teacher.middle_name || "",
            last_name: teacher.last_name || "",
            suffix: teacher.suffix || "",
            email: teacher.email || "",
            phone_no: teacher.phone_no || "",
            date_of_birth: teacher.date_of_birth
                ? teacher.date_of_birth.split("T")[0]
                : "",
            gender: teacher.gender || "",
            password: "",
            password_confirmation: "",
        };
    } else if (mode === "add") {
        resetTeacherForm();
        selectedTeacher.value = null;
    }
    showTeacherModal.value = true;
};

const viewTeacher = (teacher) => {
    selectedTeacher.value = teacher;
    teacherModalMode.value = "view";
    showTeacherModal.value = true;
};

const closeTeacherModal = () => {
    showTeacherModal.value = false;
    resetTeacherForm();
    selectedTeacher.value = null;
};

const submitTeacherForm = () => {
    isSubmitting.value = true;

    if (teacherModalMode.value === "add") {
        router.post("/admin/teachers", teacherForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Teacher created successfully!");
                closeTeacherModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else if (teacherModalMode.value === "edit" && selectedTeacher.value) {
        router.put(
            `/admin/teachers/${selectedTeacher.value.id}`,
            teacherForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Teacher updated successfully!");
                    closeTeacherModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError);
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

const confirmDeleteTeacher = (teacher) => {
    teacherToDelete.value = teacher;
    showDeleteModal.value = true;
};

const deleteTeacher = () => {
    if (!teacherToDelete.value) return;
    isSubmitting.value = true;

    router.delete(`/admin/teachers/${teacherToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Teacher deleted successfully!");
            showDeleteModal.value = false;
            teacherToDelete.value = null;
        },
        onError: () => {
            toast.error("Failed to delete teacher.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";
</style>
