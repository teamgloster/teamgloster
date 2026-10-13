<template>
    <TeacherLayout
        title="Attendance"
        pageTitle="Attendance"
        currentPage="attendance"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Attendance</h2>
                    <span class="gov-sy">School Year {{ currentSchoolYear }}</span>
                </div>
            </div>

            <div v-if="classes.length === 0" class="gov-panel">
                <div class="gov-panel-bar">No classes assigned</div>
                <p class="empty-copy">
                    You can take attendance once a subject class or advisory
                    section is assigned to you.
                </p>
            </div>

            <template v-else>
                <div class="toolbar">
                    <label>
                        Class
                        <select v-model="classKey" @change="reload">
                            <option
                                v-for="item in classes"
                                :key="item.key"
                                :value="item.key"
                            >
                                {{ item.label }} ({{ item.student_count }})
                            </option>
                        </select>
                    </label>
                    <label>
                        Date
                        <input
                            v-model="sheetDate"
                            type="date"
                            :max="today"
                            @change="reload"
                        />
                    </label>
                </div>

                <div class="summary">
                    <span>Present {{ countStatus("present") }}</span>
                    <span>Absent {{ countStatus("absent") }}</span>
                    <span>Late {{ countStatus("late") }}</span>
                    <span>Excused {{ countStatus("excused") }}</span>
                </div>

                <p v-if="hasUnsavedStudents" class="note">
                    Students without a saved mark start as Present. Review the
                    list, then save.
                </p>

                <div class="mark-all">
                    <span>Mark all</span>
                    <button type="button" @click="markAll('present')">
                        Present
                    </button>
                    <button type="button" @click="markAll('absent')">
                        Absent
                    </button>
                    <button type="button" @click="markAll('late')">Late</button>
                    <button type="button" @click="markAll('excused')">
                        Excused
                    </button>
                </div>

                <div class="search-box">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search by name or LRN"
                    />
                </div>

                <div class="table-wrap" v-if="filteredRows.length">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in filteredRows" :key="row.id">
                                <td>
                                    <strong>
                                        {{ row.last_name }}, {{ row.first_name }}
                                        {{
                                            row.middle_name
                                                ? row.middle_name.charAt(0) + "."
                                                : ""
                                        }}
                                    </strong>
                                    <small>LRN {{ row.lrn || "N/A" }}</small>
                                    <small v-if="!row.recorded" class="unsaved">
                                        Not saved yet
                                    </small>
                                </td>
                                <td>
                                    <div class="status-choices">
                                        <button
                                            v-for="choice in statuses"
                                            :key="choice.value"
                                            type="button"
                                            :class="{
                                                active: row.status === choice.value,
                                                [choice.value]: true,
                                            }"
                                            @click="row.status = choice.value"
                                        >
                                            {{ choice.label }}
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <input
                                        v-model="row.remarks"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Optional"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="empty-copy">
                    {{
                        search
                            ? "No students match your search."
                            : "No enrolled students are in this class."
                    }}
                </p>

                <div class="actions" v-if="rows.length">
                    <button
                        type="button"
                        class="save-btn"
                        :disabled="isSaving"
                        @click="save"
                    >
                        {{ isSaving ? "Saving..." : "Save attendance" }}
                    </button>
                </div>
            </template>
        </div>
    </TeacherLayout>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import TeacherLayout from "@/Layouts/TeacherLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    currentSchoolYear: { type: String, default: "" },
    classes: { type: Array, default: () => [] },
    selectedClass: { type: String, default: "" },
    date: { type: String, required: true },
    today: { type: String, required: true },
    students: { type: Array, default: () => [] },
});

const statuses = [
    { value: "present", label: "Present" },
    { value: "absent", label: "Absent" },
    { value: "late", label: "Late" },
    { value: "excused", label: "Excused" },
];

const toast = useToast();
const classKey = ref(props.selectedClass);
const sheetDate = ref(props.date);
const search = ref("");
const isSaving = ref(false);
const rows = ref([]);

const syncRows = () => {
    rows.value = props.students.map((student) => ({
        id: student.id,
        first_name: student.first_name,
        middle_name: student.middle_name,
        last_name: student.last_name,
        lrn: student.lrn,
        recorded: Boolean(student.status),
        status: student.status || "present",
        remarks: student.remarks || "",
    }));
};

watch(() => props.students, syncRows, { immediate: true });
watch(
    () => [props.selectedClass, props.date],
    () => {
        classKey.value = props.selectedClass;
        sheetDate.value = props.date;
        search.value = "";
    },
);

const filteredRows = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return rows.value;

    return rows.value.filter((row) => {
        const name = `${row.first_name} ${row.middle_name || ""} ${row.last_name}`.toLowerCase();
        return name.includes(query) || (row.lrn || "").toLowerCase().includes(query);
    });
});

const hasUnsavedStudents = computed(() => rows.value.some((row) => !row.recorded));

const countStatus = (status) =>
    rows.value.filter((row) => row.status === status).length;

const markAll = (status) => {
    rows.value = rows.value.map((row) => ({ ...row, status }));
};

const reload = () => {
    router.get(
        route("teacher.attendance"),
        { class: classKey.value, date: sheetDate.value },
        { preserveScroll: true, preserveState: true, replace: true },
    );
};

const save = () => {
    isSaving.value = true;
    router.put(
        route("teacher.attendance.update"),
        {
            class_key: props.selectedClass,
            date: props.date,
            records: rows.value.map((row) => ({
                student_id: row.id,
                status: row.status,
                remarks: row.remarks,
            })),
        },
        {
            preserveScroll: true,
            onSuccess: () => toast.success("Attendance saved."),
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Could not save attendance.");
            },
            onFinish: () => {
                isSaving.value = false;
            },
        },
    );
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.gov-panel,
.table-wrap {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.gov-panel-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.empty-copy,
.note {
    margin: 0;
    padding: 0.85rem;
    color: #333;
}

.note {
    background: #fff8e6;
    border: 1px solid #c9a227;
    margin-bottom: 0.75rem;
}

.toolbar,
.summary,
.mark-all,
.search-box,
.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: end;
    margin-bottom: 0.75rem;
}

.toolbar label {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    min-width: 220px;
}

.toolbar select,
.toolbar input,
.search-box input,
td input {
    border: 1px solid #bdbdbd;
    padding: 0.45rem 0.55rem;
    font: inherit;
}

.search-box {
    display: block;
}

.search-box input {
    width: min(100%, 360px);
}

.summary span {
    background: #e8eef4;
    color: #003366;
    font-weight: 700;
    padding: 0.35rem 0.6rem;
}

.mark-all button,
.status-choices button,
.save-btn {
    border: 1px solid #003366;
    background: #fff;
    color: #003366;
    font-weight: 700;
    padding: 0.35rem 0.6rem;
    cursor: pointer;
}

.status-choices {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.status-choices button.active.present {
    background: #1b5e20;
    border-color: #1b5e20;
    color: #fff;
}

.status-choices button.active.absent {
    background: #9b1c1c;
    border-color: #9b1c1c;
    color: #fff;
}

.status-choices button.active.late {
    background: #8a5a00;
    border-color: #8a5a00;
    color: #fff;
}

.status-choices button.active.excused {
    background: #003366;
    color: #fff;
}

.save-btn {
    background: #003366;
    color: #fff;
    padding: 0.55rem 0.9rem;
}

.save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 0.65rem 0.75rem;
    border-top: 1px solid #e0e0e0;
    text-align: left;
    vertical-align: top;
}

th {
    background: #e8eef4;
    color: #003366;
    border-top: none;
}

td strong,
td small {
    display: block;
}

td small {
    color: #555;
}

.unsaved {
    color: #8a5a00;
}
</style>
