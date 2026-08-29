<template>
    <RegistrarLayout
        title="Students"
        pageTitle="Students"
        currentPage="students"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Students</h2>
                    <span class="gov-sy"
                        >School Year {{ currentSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Eligible to Promote</div>
                    <div class="gov-stat-value">{{ eligibleCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Incomplete Grades</div>
                    <div class="gov-stat-value">{{ incompleteCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Failed Previous Level</div>
                    <div class="gov-stat-value">{{ failedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Already Promoted</div>
                    <div class="gov-stat-value">{{ promotedCount }}</div>
                </div>
            </div>

            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="studentSearch"
                            type="text"
                            placeholder="Search students by name or LRN..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
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
                        <select v-model="promotionFilter" class="filter-select">
                            <option value="all">All Promotion Status</option>
                            <option value="eligible">Eligible to Promote</option>
                            <option value="incomplete">Incomplete Grades</option>
                            <option value="failed">Failed</option>
                            <option value="already_promoted">
                                Already Promoted
                            </option>
                            <option value="completed">Completed</option>
                            <option value="no_record">No Record</option>
                        </select>
                        <button
                            type="button"
                            class="btn-success"
                            :disabled="filteredEligibleStudents.length === 0"
                            @click="showBulkPromoteModal = true"
                        >
                            <TrendingUp :size="18" />
                            Promote Eligible
                        </button>
                    </div>
                </div>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>LRN</th>
                            <th>Year Level</th>
                            <th>Section</th>
                            <th>Promotion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="student in filteredStudents"
                            :key="student.id"
                        >
                            <td>
                                {{ student.last_name }},
                                {{ student.first_name }}
                            </td>
                            <td>
                                <span class="lrn-badge">{{ student.lrn }}</span>
                            </td>
                            <td>
                                <span
                                    v-if="
                                        student.current_enrollment?.year_level
                                            ?.name
                                    "
                                    class="year-level-badge"
                                >
                                    {{
                                        student.current_enrollment.year_level
                                            .name
                                    }}
                                </span>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <td>
                                {{
                                    student.current_enrollment?.section
                                        ?.name || "Not assigned"
                                }}
                            </td>
                            <td>
                                <div class="promotion-cell">
                                    <span
                                        class="status-badge"
                                        :class="
                                            student.promotion?.status ||
                                            'no_record'
                                        "
                                    >
                                        {{
                                            promotionLabel(
                                                student.promotion?.status,
                                            )
                                        }}
                                    </span>
                                    <small
                                        v-if="student.promotion?.gwa != null"
                                    >
                                        GWA
                                        {{
                                            Number(
                                                student.promotion.gwa,
                                            ).toFixed(2)
                                        }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon promote"
                                        :disabled="
                                            !student.promotion?.can_promote
                                        "
                                        @click="openPromoteModal(student)"
                                        :title="
                                            student.promotion?.can_promote
                                                ? 'Promote to next year level'
                                                : student.promotion?.message ||
                                                  'Student has not passed the previous year level'
                                        "
                                    >
                                        <TrendingUp :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="openYearLevelModal(student)"
                                        title="Change year level"
                                    >
                                        <ArrowUpDown :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="6" class="empty-table">
                                <div class="empty-message">
                                    <p>No students found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showPromoteModal"
                    class="modal-overlay"
                    @click.self="closePromoteModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header">
                            <h3>Promote Student</h3>
                            <button class="close-btn" @click="closePromoteModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Promote
                                <strong>
                                    {{ studentToPromote?.last_name }},
                                    {{ studentToPromote?.first_name }}
                                </strong>
                                to
                                <strong>{{
                                    studentToPromote?.promotion
                                        ?.next_year_level?.name
                                }}</strong
                                >?
                            </p>
                            <p
                                v-if="studentToPromote?.promotion?.message"
                                class="description-text"
                            >
                                {{ studentToPromote.promotion.message }}
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closePromoteModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="promoteStudent"
                            >
                                Promote Student
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showYearLevelModal"
                    class="modal-overlay"
                    @click.self="closeYearLevelModal"
                >
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3>Change Year Level</h3>
                            <button
                                class="close-btn"
                                @click="closeYearLevelModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="description-text">
                                {{
                                    studentToChange?.promotion?.message ||
                                    "Select a year level for this student."
                                }}
                            </p>
                            <div class="form-group">
                                <label>Year Level</label>
                                <select v-model="yearLevelForm.year_level_id">
                                    <option value="">Select year level</option>
                                    <option
                                        v-for="level in yearLevels"
                                        :key="level.id"
                                        :value="String(level.id)"
                                        :disabled="
                                            !isYearLevelAllowed(level.id)
                                        "
                                    >
                                        {{ level.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeYearLevelModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="
                                    isSubmitting ||
                                    !yearLevelForm.year_level_id ||
                                    isSameCurrentYearLevel
                                "
                                @click="submitYearLevelChange"
                            >
                                Save Year Level
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showBulkPromoteModal"
                    class="modal-overlay"
                    @click.self="showBulkPromoteModal = false"
                >
                    <div class="modal-container small">
                        <div class="modal-header">
                            <h3>Promote Eligible Students</h3>
                            <button
                                class="close-btn"
                                @click="showBulkPromoteModal = false"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Promote
                                <strong>{{
                                    filteredEligibleStudents.length
                                }}</strong>
                                student(s) who have passed their current year
                                level?
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="showBulkPromoteModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="promoteEligibleStudents"
                            >
                                Promote Eligible
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </RegistrarLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { ArrowUpDown, Search, TrendingUp, X } from "lucide-vue-next";
import { useToast } from "@/composables/useNotify";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

const toast = useToast();

const props = defineProps({
    user: { type: Object, required: true },
    students: { type: Array, default: () => [] },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});

const studentSearch = ref("");
const yearLevelFilter = ref("all");
const promotionFilter = ref("all");
const isSubmitting = ref(false);
const showPromoteModal = ref(false);
const studentToPromote = ref(null);
const showYearLevelModal = ref(false);
const studentToChange = ref(null);
const showBulkPromoteModal = ref(false);
const yearLevelForm = ref({ year_level_id: "" });

const filteredStudents = computed(() => {
    return props.students.filter((student) => {
        const search = studentSearch.value.toLowerCase();
        const matchesSearch =
            !search ||
            student.first_name?.toLowerCase().includes(search) ||
            student.last_name?.toLowerCase().includes(search) ||
            student.lrn?.toLowerCase().includes(search);
        const matchesYear =
            yearLevelFilter.value === "all" ||
            String(student.current_enrollment?.year_level_id) ===
                String(yearLevelFilter.value);
        const matchesPromotion =
            promotionFilter.value === "all" ||
            (student.promotion?.status || "no_record") ===
                promotionFilter.value;

        return matchesSearch && matchesYear && matchesPromotion;
    });
});

const eligibleCount = computed(
    () =>
        props.students.filter(
            (student) => student.promotion?.status === "eligible",
        ).length,
);
const incompleteCount = computed(
    () =>
        props.students.filter(
            (student) => student.promotion?.status === "incomplete",
        ).length,
);
const failedCount = computed(
    () =>
        props.students.filter(
            (student) => student.promotion?.status === "failed",
        ).length,
);
const promotedCount = computed(
    () =>
        props.students.filter(
            (student) => student.promotion?.status === "already_promoted",
        ).length,
);
const filteredEligibleStudents = computed(() =>
    filteredStudents.value.filter((student) => student.promotion?.can_promote),
);
const isSameCurrentYearLevel = computed(() => {
    const currentId = studentToChange.value?.current_enrollment?.year_level_id;
    return (
        currentId &&
        String(currentId) === String(yearLevelForm.value.year_level_id)
    );
});

const promotionLabel = (status) =>
    ({
        eligible: "Eligible",
        already_promoted: "Promoted",
        completed: "Completed",
        failed: "Failed",
        incomplete: "Incomplete",
        no_record: "No record",
    })[status] || "No record";

const isYearLevelAllowed = (yearLevelId) => {
    const allowed =
        studentToChange.value?.promotion?.allowed_year_level_ids || [];
    return allowed.map(String).includes(String(yearLevelId));
};

const openPromoteModal = (student) => {
    if (!student?.promotion?.can_promote) {
        toast.error(
            student?.promotion?.message ||
                "This student has not passed the previous year level.",
        );
        return;
    }
    studentToPromote.value = student;
    showPromoteModal.value = true;
};

const closePromoteModal = () => {
    showPromoteModal.value = false;
    studentToPromote.value = null;
};

const promoteStudent = () => {
    if (!studentToPromote.value) return;
    isSubmitting.value = true;
    router.post(
        `/registrar/students/${studentToPromote.value.id}/promote`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Student promoted successfully.");
                closePromoteModal();
            },
            onError: (errors) => toast.error(Object.values(errors)[0]),
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openYearLevelModal = (student) => {
    studentToChange.value = student;
    yearLevelForm.value.year_level_id =
        student.promotion?.can_promote && student.promotion?.next_year_level?.id
            ? String(student.promotion.next_year_level.id)
            : student.current_enrollment?.year_level_id
              ? String(student.current_enrollment.year_level_id)
              : "";
    showYearLevelModal.value = true;
};

const closeYearLevelModal = () => {
    showYearLevelModal.value = false;
    studentToChange.value = null;
    yearLevelForm.value.year_level_id = "";
};

const submitYearLevelChange = () => {
    if (!studentToChange.value || !yearLevelForm.value.year_level_id) return;
    isSubmitting.value = true;
    router.put(
        `/registrar/students/${studentToChange.value.id}/year-level`,
        yearLevelForm.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Year level updated successfully.");
                closeYearLevelModal();
            },
            onError: (errors) => toast.error(Object.values(errors)[0]),
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const promoteEligibleStudents = () => {
    isSubmitting.value = true;
    router.post(
        "/registrar/students/promote-selected",
        {
            student_ids: filteredEligibleStudents.value.map(
                (student) => student.id,
            ),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Eligible students were promoted.");
                showBulkPromoteModal.value = false;
            },
            onError: (errors) => toast.error(Object.values(errors)[0]),
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.filter-group {
    flex-wrap: wrap;
    justify-content: flex-end;
}

.text-muted {
    color: #555;
    font-style: italic;
}

.promotion-cell {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.promotion-cell small {
    color: #555;
    font-size: 0.75rem;
}

.status-badge.eligible {
    color: #1f6b3a;
    border-color: #1f6b3a;
}

.status-badge.failed {
    color: #9b1c1c;
    border-color: #9b1c1c;
}

.status-badge.incomplete,
.status-badge.no_record {
    color: #9a6700;
    border-color: #9a6700;
}

.status-badge.already_promoted,
.status-badge.completed {
    color: #003366;
    border-color: #c9a227;
}

.btn-icon:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
</style>
