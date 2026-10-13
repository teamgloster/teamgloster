<template>
    <StudentLayout
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
                    <h2>My Attendance</h2>
                    <span class="gov-sy">School Year {{ currentSchoolYear }}</span>
                </div>
            </div>

            <div class="summary">
                <span>Present {{ summary.present }}</span>
                <span>Absent {{ summary.absent }}</span>
                <span>Late {{ summary.late }}</span>
                <span>Excused {{ summary.excused }}</span>
            </div>

            <label class="date-field">
                Check a day
                <input
                    v-model="sheetDate"
                    type="date"
                    :max="today"
                    @change="reload"
                />
            </label>

            <div class="gov-panel">
                <div class="gov-panel-bar">{{ formattedDate }}</div>
                <p v-if="records.length === 0" class="empty-copy">
                    No attendance has been recorded for this day.
                </p>
                <p v-else-if="allPresent" class="day-note present">
                    You were present in every class recorded for this day.
                </p>
                <p v-else class="day-note mixed">
                    You have {{ absentCount }} absent mark{{
                        absentCount === 1 ? "" : "s"
                    }}
                    on this day.
                </p>
                <table v-if="records.length">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records" :key="record.id">
                            <td>
                                <strong>{{ record.subject_name }}</strong>
                                <small>
                                    {{ record.section_name || "No section" }}
                                    <template v-if="record.teacher_name">
                                        · {{ record.teacher_name }}
                                    </template>
                                </small>
                            </td>
                            <td>
                                <span class="status" :class="record.status">
                                    {{ statusLabel(record.status) }}
                                </span>
                            </td>
                            <td>{{ record.remarks || "—" }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="gov-panel history">
                <div class="gov-panel-bar">Recorded days this month</div>
                <table v-if="history.length">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in history" :key="`history-${record.id}`">
                            <td>
                                <button
                                    type="button"
                                    class="date-link"
                                    @click="openDate(record.date)"
                                >
                                    {{ formatDate(record.date) }}
                                </button>
                            </td>
                            <td>{{ record.subject_name }}</td>
                            <td>
                                <span class="status" :class="record.status">
                                    {{ statusLabel(record.status) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="empty-copy">
                    No attendance has been recorded this month.
                </p>
            </div>
        </div>
    </StudentLayout>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import StudentLayout from "@/Layouts/StudentLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    currentSchoolYear: { type: String, default: "" },
    date: { type: String, required: true },
    today: { type: String, required: true },
    records: { type: Array, default: () => [] },
    history: { type: Array, default: () => [] },
    summary: {
        type: Object,
        default: () => ({ present: 0, absent: 0, late: 0, excused: 0 }),
    },
});

const sheetDate = ref(props.date);

watch(
    () => props.date,
    (value) => {
        sheetDate.value = value;
    },
);

const allPresent = computed(
    () =>
        props.records.length > 0 &&
        props.records.every((record) => record.status === "present"),
);

const absentCount = computed(
    () => props.records.filter((record) => record.status === "absent").length,
);

const formattedDate = computed(() => formatDate(props.date));

const statusLabel = (status) => {
    const labels = {
        present: "Present",
        absent: "Absent",
        late: "Late",
        excused: "Excused",
    };

    return labels[status] || status;
};

const formatDate = (value) => {
    const [year, month, day] = value.split("-").map(Number);
    return new Date(year, month - 1, day).toLocaleDateString("en-PH", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
};

const reload = () => {
    router.get(
        route("student.attendance"),
        { date: sheetDate.value },
        { preserveScroll: true, preserveState: true, replace: true },
    );
};

const openDate = (date) => {
    sheetDate.value = date;
    reload();
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.summary {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.85rem;
}

.summary span {
    background: #e8eef4;
    color: #003366;
    font-weight: 700;
    padding: 0.35rem 0.6rem;
}

.date-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    max-width: 240px;
    margin-bottom: 0.85rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.date-field input {
    border: 1px solid #bdbdbd;
    padding: 0.45rem 0.55rem;
    font: inherit;
}

.gov-panel {
    background: #fff;
    border: 1px solid #c5c5c5;
    margin-bottom: 0.85rem;
}

.gov-panel-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.empty-copy,
.day-note {
    margin: 0;
    padding: 0.85rem;
}

.day-note.present {
    background: #e8f5e9;
    color: #1b5e20;
}

.day-note.mixed {
    background: #fdecea;
    color: #9b1c1c;
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

.status {
    display: inline-block;
    padding: 0.15rem 0.45rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #fff;
}

.status.present {
    background: #1b5e20;
}

.status.absent {
    background: #9b1c1c;
}

.status.late {
    background: #8a5a00;
}

.status.excused {
    background: #003366;
}

.date-link {
    border: 0;
    background: transparent;
    color: #003366;
    font-weight: 700;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
}
</style>
