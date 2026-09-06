<template>
    <component
        :is="layoutComponent"
        title="SP-10 Records"
        pageTitle="SP-10 Records"
        currentPage="permanent-records"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Learner's Permanent Academic
                    Record
                </p>
                <div class="gov-pagehead-row">
                    <h2>SP-10 / SF10 Finder</h2>
                    <span class="gov-sy">School Year {{ schoolYear }}</span>
                </div>
            </div>

            <p class="intro">
                Search any Tambo NHS learner and open the generated SP-10. Upload
                an old Form 137 / SF10 file when the record came from another
                school or an earlier year. Grades entered once by the teacher
                appear on both SF9 and SP-10.
            </p>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Learners</div>
                    <div class="gov-stat-value">{{ filteredStudents.length }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">With uploaded files</div>
                    <div class="gov-stat-value">{{ withFilesCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">With encoded grades</div>
                    <div class="gov-stat-value">{{ withGradesCount }}</div>
                </div>
            </div>

            <div class="toolbar">
                <div class="search-box">
                    <Search :size="18" class="search-icon" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="search-input"
                        placeholder="Search by name or LRN..."
                    />
                </div>
                <select v-model="yearLevelFilter" class="filter-select">
                    <option value="">All year levels</option>
                    <option
                        v-for="level in yearLevels"
                        :key="level.id"
                        :value="String(level.id)"
                    >
                        {{ level.name }}
                    </option>
                </select>
                <select v-model="fileFilter" class="filter-select">
                    <option value="">All records</option>
                    <option value="1">Has uploaded file</option>
                    <option value="0">No uploaded file</option>
                </select>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Learner</th>
                            <th>LRN</th>
                            <th>Year Level</th>
                            <th>Section</th>
                            <th>Grades</th>
                            <th>Uploaded files</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in paginatedStudents" :key="student.id">
                            <td>{{ studentName(student) }}</td>
                            <td>{{ student.lrn || "—" }}</td>
                            <td>{{ student.year_level || "—" }}</td>
                            <td>{{ student.section || "—" }}</td>
                            <td>
                                <span
                                    class="pill"
                                    :class="student.has_grades ? 'ready' : 'pending'"
                                >
                                    {{ student.has_grades ? "Encoded" : "None yet" }}
                                </span>
                            </td>
                            <td>{{ student.uploaded_count }}</td>
                            <td class="actions">
                                <Link class="hub-mini" :href="sf9Url(student.id)">
                                    SF9
                                </Link>
                                <Link class="hub-mini" :href="sf10Url(student.id)">
                                    SP-10
                                </Link>
                                <button
                                    type="button"
                                    class="hub-mini"
                                    @click="openUpload(student)"
                                >
                                    Upload
                                </button>
                                <button
                                    v-if="student.uploaded_count > 0"
                                    type="button"
                                    class="hub-mini"
                                    @click="openFiles(student)"
                                >
                                    Files
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="7" class="empty-note">
                                No learners match the current search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="totalPages > 1" class="table-footer">
                <span>
                    Showing {{ pageStart }}–{{ pageEnd }} of
                    {{ filteredStudents.length }}
                </span>
                <div class="pagination">
                    <button
                        type="button"
                        class="page-btn"
                        :disabled="currentPage === 1"
                        @click="currentPage--"
                    >
                        Prev
                    </button>
                    <button
                        type="button"
                        class="page-btn"
                        :disabled="currentPage === totalPages"
                        @click="currentPage++"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="uploadStudent"
                class="modal-overlay"
                @click.self="uploadStudent = null"
            >
                <div class="modal-container">
                    <div class="modal-header">
                        <h3>Upload old SP-10</h3>
                        <button type="button" class="close-btn" @click="uploadStudent = null">
                            ×
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="modal-note">
                            Attach the previous Form 137 / SF10 for
                            <strong>{{ studentName(uploadStudent) }}</strong>.
                            PDF or image, up to 10 MB.
                        </p>
                        <label class="field-label">School year (optional)</label>
                        <select v-model="uploadForm.school_year" class="hub-select">
                            <option value="">Not specified / legacy</option>
                            <option
                                v-for="year in schoolYears"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                        <label class="field-label">File</label>
                        <input
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*"
                            @change="onFileChange"
                        />
                        <label class="field-label">Notes</label>
                        <textarea
                            v-model="uploadForm.notes"
                            rows="3"
                            placeholder="e.g. Transferee record from previous school"
                        ></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" @click="uploadStudent = null">
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn-primary"
                            :disabled="!uploadForm.file || uploading"
                            @click="submitUpload"
                        >
                            {{ uploading ? "Uploading..." : "Upload file" }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div
                v-if="filesStudent"
                class="modal-overlay"
                @click.self="filesStudent = null"
            >
                <div class="modal-container">
                    <div class="modal-header">
                        <h3>Uploaded SP-10 files</h3>
                        <button type="button" class="close-btn" @click="filesStudent = null">
                            ×
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="modal-note">
                            {{ studentName(filesStudent) }}
                        </p>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>School year</th>
                                    <th>Uploaded</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="file in filesStudent.files"
                                    :key="file.id"
                                >
                                    <td>
                                        <div>{{ file.original_filename }}</div>
                                        <div class="file-meta">
                                            {{ file.notes || "No notes" }}
                                        </div>
                                    </td>
                                    <td>{{ file.school_year || "Legacy" }}</td>
                                    <td>
                                        {{ file.created_at }}
                                        <div class="file-meta">
                                            {{ file.uploaded_by || "—" }}
                                        </div>
                                    </td>
                                    <td class="actions">
                                        <a
                                            class="hub-mini"
                                            :href="downloadUrl(file.id)"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Download
                                        </a>
                                        <button
                                            type="button"
                                            class="hub-mini danger"
                                            @click="removeFile(file)"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmModal
            :show="!!fileToRemove"
            title="Remove File"
            message="Remove this uploaded SP-10 file? This cannot be undone."
            confirm-label="Remove"
            @cancel="fileToRemove = null"
            @confirm="confirmRemoveFile"
        />
    </component>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { Search } from "lucide-vue-next";
import { useToast } from "@/composables/useNotify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

const props = defineProps({
    user: { type: Object, required: true },
    viewer: { type: String, default: "admin" },
    schoolYear: { type: String, default: "" },
    yearLevels: { type: Array, default: () => [] },
    schoolYears: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const layoutComponent = computed(() =>
    props.viewer === "registrar" ? RegistrarLayout : AdminLayout,
);
const basePath = computed(() =>
    props.viewer === "registrar" ? "/registrar" : "/admin",
);

const searchQuery = ref(props.filters?.search || "");
const yearLevelFilter = ref(props.filters?.year_level_id || "");
const fileFilter = ref(props.filters?.has_file || "");
const currentPage = ref(1);
const pageSize = 15;
const uploadStudent = ref(null);
const filesStudent = ref(null);
const uploading = ref(false);
const fileToRemove = ref(null);
const uploadForm = ref({
    file: null,
    school_year: "",
    notes: "",
});

const studentName = (student) => {
    if (!student) return "";
    const middle = student.middle_name ? ` ${student.middle_name.charAt(0)}.` : "";
    const suffix = student.suffix ? ` ${student.suffix}` : "";
    return `${student.last_name}, ${student.first_name}${middle}${suffix}`;
};

const filteredStudents = computed(() => {
    const search = searchQuery.value.trim().toLowerCase();
    return (props.students || []).filter((student) => {
        if (
            yearLevelFilter.value &&
            String(student.year_level_id) !== String(yearLevelFilter.value)
        ) {
            return false;
        }
        if (fileFilter.value === "1" && student.uploaded_count <= 0) {
            return false;
        }
        if (fileFilter.value === "0" && student.uploaded_count > 0) {
            return false;
        }
        if (!search) {
            return true;
        }
        const name = studentName(student).toLowerCase();
        const lrn = String(student.lrn || "").toLowerCase();
        return name.includes(search) || lrn.includes(search);
    });
});

const withFilesCount = computed(
    () => filteredStudents.value.filter((student) => student.uploaded_count > 0).length,
);
const withGradesCount = computed(
    () => filteredStudents.value.filter((student) => student.has_grades).length,
);

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredStudents.value.length / pageSize)),
);
const pageStart = computed(() =>
    filteredStudents.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize + 1,
);
const pageEnd = computed(() =>
    Math.min(filteredStudents.value.length, currentPage.value * pageSize),
);
const paginatedStudents = computed(() =>
    filteredStudents.value.slice(pageStart.value - 1, pageEnd.value),
);

watch([searchQuery, yearLevelFilter, fileFilter], () => {
    currentPage.value = 1;
});

const sf9Url = (studentId) => `${basePath.value}/students/${studentId}/sf9`;
const sf10Url = (studentId) => `${basePath.value}/students/${studentId}/sf10`;
const downloadUrl = (recordId) =>
    `${basePath.value}/permanent-records/${recordId}/download`;

const openUpload = (student) => {
    uploadStudent.value = student;
    uploadForm.value = { file: null, school_year: "", notes: "" };
};

const openFiles = (student) => {
    filesStudent.value = student;
};

const onFileChange = (event) => {
    uploadForm.value.file = event.target.files?.[0] || null;
};

const submitUpload = () => {
    if (!uploadStudent.value || !uploadForm.value.file) {
        return;
    }

    uploading.value = true;
    const data = new FormData();
    data.append("file", uploadForm.value.file);
    if (uploadForm.value.school_year) {
        data.append("school_year", uploadForm.value.school_year);
    }
    if (uploadForm.value.notes) {
        data.append("notes", uploadForm.value.notes);
    }

    router.post(
        `${basePath.value}/students/${uploadStudent.value.id}/permanent-records`,
        data,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: (page) => {
                toast.success(page.props.flash?.success || "File uploaded.");
                uploadStudent.value = null;
            },
            onError: (errors) => {
                toast.error(Object.values(errors)[0] || "Upload failed.");
            },
            onFinish: () => {
                uploading.value = false;
            },
        },
    );
};

const removeFile = (file) => {
    fileToRemove.value = file;
};

const confirmRemoveFile = () => {
    const file = fileToRemove.value;
    if (!file) return;

    router.delete(`${basePath.value}/permanent-records/${file.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            toast.success(page.props.flash?.success || "File removed.");
            filesStudent.value = null;
            fileToRemove.value = null;
        },
        onError: () => toast.error("Could not remove the file."),
        onFinish: () => {
            fileToRemove.value = null;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.intro {
    margin: 0 0 0.85rem;
    color: #444;
    font-size: 0.88rem;
}

.toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    margin: 0.85rem 0;
}

.search-box {
    position: relative;
    flex: 1 1 240px;
}

.search-icon {
    position: absolute;
    left: 0.55rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.filter-select,
.hub-select,
textarea,
input[type="file"] {
    border: 1px solid #bdbdbd;
    padding: 0.45rem 0.55rem;
    font-size: 0.88rem;
    background: #fff;
}

.actions {
    white-space: nowrap;
    text-align: right;
}

.hub-mini,
.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #003366;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border: 1px solid #003366;
    margin-left: 0.25rem;
    cursor: pointer;
}

.hub-mini.danger {
    color: #9b1c1c;
    border-color: #9b1c1c;
}

.btn-primary {
    background: #003366;
    color: #fff;
}

.pill {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
}

.pill.ready {
    background: #e8eef4;
    color: #003366;
}

.pill.pending {
    background: #f4f4f4;
    color: #666;
}

.empty-note {
    text-align: center;
    color: #666;
    padding: 1rem;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 0.75rem;
}

.page-btn {
    border: 1px solid #003366;
    background: #fff;
    color: #003366;
    padding: 0.3rem 0.65rem;
    font-weight: 700;
    cursor: pointer;
}

.page-btn:disabled {
    opacity: 0.45;
}

.modal-overlay {
    z-index: 80;
}

.modal-container {
    width: min(720px, 94vw);
}

.modal-note,
.file-meta,
.field-label {
    color: #555;
    font-size: 0.84rem;
}

.field-label {
    display: block;
    margin: 0.7rem 0 0.3rem;
    font-weight: 700;
    color: #003366;
}

.hub-select,
textarea,
input[type="file"] {
    width: 100%;
}

.close-btn {
    color: #fff;
}
</style>
