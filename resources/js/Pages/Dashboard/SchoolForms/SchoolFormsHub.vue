<template>
    <div class="sf-hub">
        <div class="gov-pagehead">
            <p class="gov-kicker">
                {{ schoolName }} — DepEd Order No. 58, s. 2017
            </p>
            <div class="gov-pagehead-row">
                <h2>School Forms</h2>
                <span class="gov-sy">School Year {{ schoolYear }}</span>
            </div>
        </div>

        <p class="hub-intro">
            Generate official DepEd school forms. SF1 and SF2 are prepared by
            section. SF9 and SF10 are prepared per learner.
        </p>

        <div class="form-cards">
            <button
                v-for="item in formOptions"
                :key="item.id"
                type="button"
                class="form-card"
                :class="{ active: activeForm === item.id }"
                @click="activeForm = item.id"
            >
                <span class="form-code">{{ item.code }}</span>
                <span class="form-name">{{ item.name }}</span>
                <span class="form-desc">{{ item.description }}</span>
            </button>
        </div>

        <div v-if="activeForm === 'sf1'" class="gov-panel">
            <div class="gov-panel-bar">SF1 — School Register</div>
            <div class="panel-body">
                <p class="panel-note">
                    List of learners enrolled in the selected grade and section
                    for the current school year.
                </p>
                <div v-if="sections.length === 0" class="empty-note">
                    {{ emptySectionMessage }}
                </div>
                <template v-else>
                    <label class="field-label">Section</label>
                    <select v-model="selectedSectionId" class="hub-select">
                        <option value="">Select section</option>
                        <option
                            v-for="section in sections"
                            :key="section.id"
                            :value="String(section.id)"
                        >
                            {{ sectionLabel(section) }}
                        </option>
                    </select>
                    <Link
                        v-if="selectedSectionId"
                        class="hub-generate"
                        :href="sf1Url"
                    >
                        Generate SF1
                    </Link>
                    <button
                        v-else
                        type="button"
                        class="hub-generate disabled"
                        disabled
                    >
                        Generate SF1
                    </button>
                </template>
            </div>
        </div>

        <div v-if="activeForm === 'sf2'" class="gov-panel">
            <div class="gov-panel-bar">
                SF2 — Daily Attendance Report of Learners
            </div>
            <div class="panel-body">
                <p class="panel-note">
                    Monthly attendance sheet for the selected section. Daily
                    marks can be written on the printed form until attendance is
                    encoded in the system.
                </p>
                <div v-if="sections.length === 0" class="empty-note">
                    {{ emptySectionMessage }}
                </div>
                <template v-else>
                    <div class="field-row">
                        <div class="field">
                            <label class="field-label">Section</label>
                            <select v-model="selectedSectionId" class="hub-select">
                                <option value="">Select section</option>
                                <option
                                    v-for="section in sections"
                                    :key="section.id"
                                    :value="String(section.id)"
                                >
                                    {{ sectionLabel(section) }}
                                </option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Month</label>
                            <select v-model.number="selectedMonth" class="hub-select">
                                <option
                                    v-for="month in months"
                                    :key="month.value"
                                    :value="month.value"
                                >
                                    {{ month.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <Link
                        v-if="selectedSectionId"
                        class="hub-generate"
                        :href="sf2Url"
                    >
                        Generate SF2
                    </Link>
                    <button
                        v-else
                        type="button"
                        class="hub-generate disabled"
                        disabled
                    >
                        Generate SF2
                    </button>
                </template>
            </div>
        </div>

        <div v-if="activeForm === 'sf9'" class="gov-panel">
            <div class="gov-panel-bar">SF9 — Learner's Progress Report Card</div>
            <div class="panel-body">
                <p class="panel-note">
                    Formerly Form 138. Filter by year level and term, then
                    generate the report card.
                </p>
                <div class="field-row three">
                    <div class="field">
                        <label class="field-label">Year Level</label>
                        <select v-model="selectedYearLevelId" class="hub-select">
                            <option value="">All year levels</option>
                            <option
                                v-for="level in yearLevels"
                                :key="level.id"
                                :value="String(level.id)"
                            >
                                {{ level.name }}
                            </option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="field-label">Term</label>
                        <select v-model="selectedTerm" class="hub-select">
                            <option value="">All terms</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="field-label">Search learner</label>
                        <input
                            v-model="studentSearch"
                            type="text"
                            class="hub-input"
                            placeholder="Search by name or LRN..."
                        />
                    </div>
                </div>
                <div class="learner-table-wrap">
                    <table class="learner-table">
                        <thead>
                            <tr>
                                <th>Learner</th>
                                <th>Year Level</th>
                                <th>LRN</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="student in filteredStudents"
                                :key="student.id"
                            >
                                <td>{{ studentName(student) }}</td>
                                <td>{{ student.year_level || "—" }}</td>
                                <td>{{ student.lrn || "—" }}</td>
                                <td class="action-cell">
                                    <Link
                                        class="hub-mini"
                                        :href="sf9Url(student.id)"
                                    >
                                        Generate SF9
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="filteredStudents.length === 0">
                                <td colspan="4" class="empty-note">
                                    {{ emptyStudentMessage }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="activeForm === 'sf10'" class="gov-panel">
            <div class="gov-panel-bar">
                SF10 — Learner's Permanent Academic Record
            </div>
            <div class="panel-body">
                <p class="panel-note">
                    Formerly Form 137. Filter by year level, then generate the
                    permanent academic record.
                </p>
                <div class="field-row">
                    <div class="field">
                        <label class="field-label">Year Level</label>
                        <select v-model="selectedYearLevelId" class="hub-select">
                            <option value="">All year levels</option>
                            <option
                                v-for="level in yearLevels"
                                :key="level.id"
                                :value="String(level.id)"
                            >
                                {{ level.name }}
                            </option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="field-label">Search learner</label>
                        <input
                            v-model="studentSearch"
                            type="text"
                            class="hub-input"
                            placeholder="Search by name or LRN..."
                        />
                    </div>
                </div>
                <div class="learner-table-wrap">
                    <table class="learner-table">
                        <thead>
                            <tr>
                                <th>Learner</th>
                                <th>Year Level</th>
                                <th>LRN</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="student in filteredStudents"
                                :key="student.id"
                            >
                                <td>{{ studentName(student) }}</td>
                                <td>{{ student.year_level || "—" }}</td>
                                <td>{{ student.lrn || "—" }}</td>
                                <td class="action-cell">
                                    <Link
                                        class="hub-mini"
                                        :href="`${basePath}/students/${student.id}/sf10`"
                                    >
                                        Generate SF10
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="filteredStudents.length === 0">
                                <td colspan="4" class="empty-note">
                                    {{ emptyStudentMessage }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    viewer: { type: String, default: "admin" },
    schoolYear: { type: String, default: "" },
    schoolName: { type: String, default: "Tambo National High School" },
    currentTerm: { type: [Number, String], default: null },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
    basePath: { type: String, default: "/admin" },
});

const formOptions = [
    {
        id: "sf1",
        code: "SF1",
        name: "School Register",
        description: "List of learners by grade and section",
    },
    {
        id: "sf2",
        code: "SF2",
        name: "Daily Attendance Report",
        description: "Monthly attendance of learners",
    },
    {
        id: "sf9",
        code: "SF9",
        name: "Progress Report Card",
        description: "Formerly Form 138",
    },
    {
        id: "sf10",
        code: "SF10",
        name: "Permanent Academic Record",
        description: "Formerly Form 137",
    },
];

const months = [
    { value: 8, label: "August" },
    { value: 9, label: "September" },
    { value: 10, label: "October" },
    { value: 11, label: "November" },
    { value: 12, label: "December" },
    { value: 1, label: "January" },
    { value: 2, label: "February" },
    { value: 3, label: "March" },
    { value: 4, label: "April" },
    { value: 5, label: "May" },
];

const currentMonth = new Date().getMonth() + 1;
const defaultMonth = months.some((month) => month.value === currentMonth)
    ? currentMonth
    : 8;

const activeForm = ref("sf1");
const selectedSectionId = ref(
    props.sections.length === 1 ? String(props.sections[0].id) : "",
);
const selectedMonth = ref(defaultMonth);
const selectedYearLevelId = ref("");
const selectedTerm = ref(
    props.currentTerm ? String(props.currentTerm) : "",
);
const studentSearch = ref("");

const emptySectionMessage = computed(() =>
    props.viewer === "teacher"
        ? "No advisory section is assigned yet. SF1 and SF2 are prepared by the class adviser."
        : "No sections are set up for this school year.",
);

const emptyStudentMessage = computed(() => {
    if (studentSearch.value || selectedYearLevelId.value) {
        return "No learners match the selected year level or search.";
    }

    return props.viewer === "teacher"
        ? "No learners in your advisory class."
        : "No learners found.";
});

const filteredStudents = computed(() => {
    const search = studentSearch.value.trim().toLowerCase();
    const yearLevelId = selectedYearLevelId.value;
    let list = props.students || [];

    if (yearLevelId) {
        list = list.filter(
            (student) => String(student.year_level_id) === yearLevelId,
        );
    }

    if (search) {
        list = list.filter((student) => {
            const name = studentName(student).toLowerCase();
            const lrn = String(student.lrn || "").toLowerCase();
            return name.includes(search) || lrn.includes(search);
        });
    }

    return list.slice(0, 40);
});

const sf1Url = computed(() =>
    selectedSectionId.value
        ? `${props.basePath}/school-forms/sf1/${selectedSectionId.value}`
        : "#",
);

const sf2Url = computed(() =>
    selectedSectionId.value
        ? `${props.basePath}/school-forms/sf2/${selectedSectionId.value}?month=${selectedMonth.value}`
        : "#",
);

const sf9Url = (studentId) => {
    const params = new URLSearchParams();
    if (selectedTerm.value) {
        params.set("term", selectedTerm.value);
    }
    const query = params.toString();
    return `${props.basePath}/students/${studentId}/sf9${query ? `?${query}` : ""}`;
};

const sectionLabel = (section) => {
    const grade = section.year_level ? `${section.year_level} — ` : "";
    const count =
        section.enrolled_count !== undefined
            ? ` (${section.enrolled_count} learners)`
            : "";
    return `${grade}${section.name}${count}`;
};

const studentName = (student) => {
    const middle = student.middle_name
        ? ` ${student.middle_name.charAt(0)}.`
        : "";
    const suffix = student.suffix ? ` ${student.suffix}` : "";
    return `${student.last_name}, ${student.first_name}${middle}${suffix}`;
};
</script>

<style scoped>
.hub-intro {
    margin: 0 0 0.85rem;
    color: #444;
    font-size: 0.88rem;
}

.form-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.5rem;
    margin-bottom: 0.85rem;
}

.form-card {
    background: #fff;
    border: 1px solid #c5c5c5;
    padding: 0.7rem 0.65rem;
    text-align: left;
    cursor: pointer;
    color: #003366;
}

.form-card.active {
    border-color: #003366;
    background: #003366;
    color: #fff;
}

.form-code {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
}

.form-name {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.82rem;
    font-weight: 700;
}

.form-desc {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.72rem;
    opacity: 0.85;
}

.gov-panel {
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

.panel-body {
    padding: 0.85rem 0.9rem 1rem;
}

.panel-note,
.empty-note {
    margin: 0 0 0.75rem;
    font-size: 0.84rem;
    color: #555;
}

.empty-note {
    text-align: center;
    padding: 0.75rem 0.5rem;
}

.field-label {
    display: block;
    margin-bottom: 0.3rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #003366;
}

.field-row {
    display: grid;
    grid-template-columns: 1.4fr 0.8fr;
    gap: 0.65rem;
    margin-bottom: 0.75rem;
}

.field-row.three {
    grid-template-columns: 1fr 0.8fr 1.4fr;
}

.hub-select,
.hub-input {
    width: 100%;
    border: 1px solid #bdbdbd;
    padding: 0.45rem 0.55rem;
    font-size: 0.88rem;
    margin-bottom: 0.75rem;
    background: #fff;
}

.field .hub-select,
.field .hub-input {
    margin-bottom: 0;
}

.hub-generate,
.hub-mini {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #003366;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.45rem 0.8rem;
    border: 1px solid #003366;
}

.hub-generate.disabled {
    opacity: 0.45;
    pointer-events: none;
}

.hub-mini {
    background: #fff;
    color: #003366;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.learner-table-wrap {
    border: 1px solid #c5c5c5;
    max-height: 420px;
    overflow: auto;
}

.learner-table {
    width: 100%;
    border-collapse: collapse;
}

.learner-table th,
.learner-table td {
    padding: 0.45rem 0.6rem;
    border-bottom: 1px solid #e0e0e0;
    font-size: 0.84rem;
    text-align: left;
}

.learner-table th {
    background: #e8eef4;
    color: #003366;
    font-weight: 700;
}

.action-cell {
    text-align: right;
    width: 1%;
    white-space: nowrap;
}

.gov-pagehead {
    margin-bottom: 0.85rem;
}

.gov-kicker {
    margin: 0 0 0.2rem;
    font-size: 0.78rem;
    color: #555;
}

.gov-pagehead-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
}

.gov-pagehead-row h2 {
    margin: 0;
    color: #003366;
    font-size: 1.25rem;
}

.gov-sy {
    font-size: 0.82rem;
    font-weight: 700;
    color: #003366;
}

@media (max-width: 900px) {
    .form-cards,
    .field-row,
    .field-row.three {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .form-cards,
    .field-row,
    .gov-pagehead-row {
        grid-template-columns: 1fr;
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
