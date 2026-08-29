<template>
    <div class="content-section">
        <div class="gov-pagehead">
            <p class="gov-kicker">
                Tambo National High School — Buhi, Camarines Sur
            </p>
            <div class="gov-pagehead-row">
                <h2>Grading Records</h2>
                <span class="gov-sy">School Year {{ currentSchoolYear }}</span>
            </div>
        </div>

        <div class="gov-stat-row">
            <div class="gov-stat-box">
                <div class="gov-stat-label">Total Records</div>
                <div class="gov-stat-value">{{ records.length }}</div>
            </div>
            <div class="gov-stat-box">
                <div class="gov-stat-label">Passed</div>
                <div class="gov-stat-value">
                    {{ records.filter((row) => row.remarks === "Passed").length }}
                </div>
            </div>
            <div class="gov-stat-box">
                <div class="gov-stat-label">Failed</div>
                <div class="gov-stat-value">
                    {{ records.filter((row) => row.remarks === "Failed").length }}
                </div>
            </div>
            <div class="gov-stat-box">
                <div class="gov-stat-label">Incomplete</div>
                <div class="gov-stat-value">
                    {{
                        records.filter((row) => row.remarks === "Incomplete")
                            .length
                    }}
                </div>
            </div>
        </div>

        <div class="section-header">
            <div class="header-actions">
                <div class="search-box">
                    <Search :size="18" class="search-icon" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by student name or LRN..."
                        class="search-input"
                    />
                </div>
                <div class="filter-group">
                    <select v-model="yearLevelFilter" class="filter-select">
                        <option value="all">All Year Levels</option>
                        <option
                            v-for="level in yearLevels"
                            :key="level.id"
                            :value="String(level.id)"
                        >
                            {{ level.name }}
                        </option>
                    </select>
                    <select v-model="sectionFilter" class="filter-select">
                        <option value="all">All Sections</option>
                        <option
                            v-for="section in availableSections"
                            :key="section.id"
                            :value="String(section.id)"
                        >
                            {{ section.name }}
                        </option>
                    </select>
                    <select v-model="subjectFilter" class="filter-select">
                        <option value="all">All Subjects</option>
                        <option
                            v-for="subject in availableSubjects"
                            :key="subject.id"
                            :value="String(subject.id)"
                        >
                            {{ subject.name }}
                        </option>
                    </select>
                    <select v-model="remarksFilter" class="filter-select">
                        <option value="all">All Remarks</option>
                        <option value="Passed">Passed</option>
                        <option value="Failed">Failed</option>
                        <option value="Incomplete">Incomplete</option>
                    </select>
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
                        <th>Subject</th>
                        <th>Term 1</th>
                        <th>Term 2</th>
                        <th>Term 3</th>
                        <th>Final</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in filteredRecords" :key="row.id">
                        <td>{{ row.student_name }}</td>
                        <td>
                            <span class="lrn-badge">{{ row.lrn || "—" }}</span>
                        </td>
                        <td>
                            <span class="year-level-badge">{{
                                row.year_level || "—"
                            }}</span>
                        </td>
                        <td>{{ row.section || "Not assigned" }}</td>
                        <td>{{ row.subject }}</td>
                        <td>{{ formatGrade(row.term_1) }}</td>
                        <td>{{ formatGrade(row.term_2) }}</td>
                        <td>{{ formatGrade(row.term_3) }}</td>
                        <td>{{ formatGrade(row.final_grade) }}</td>
                        <td>
                            <span
                                class="status-badge"
                                :class="remarksClass(row.remarks)"
                            >
                                {{ row.remarks }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="filteredRecords.length === 0">
                        <td colspan="10" class="empty-table">
                            <div class="empty-message">
                                <p>No grading records found</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span class="record-count">
                Showing {{ filteredRecords.length }} of
                {{ records.length }} records
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { Search } from "lucide-vue-next";

const props = defineProps({
    records: { type: Array, default: () => [] },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
    initialYearLevelId: { type: [String, Number], default: null },
    initialSectionId: { type: [String, Number], default: null },
});

const searchQuery = ref("");
const yearLevelFilter = ref(
    props.initialYearLevelId ? String(props.initialYearLevelId) : "all",
);
const sectionFilter = ref(
    props.initialSectionId ? String(props.initialSectionId) : "all",
);
const subjectFilter = ref("all");
const remarksFilter = ref("all");

const availableSections = computed(() => {
    if (yearLevelFilter.value === "all") {
        return props.sections;
    }

    return props.sections.filter(
        (section) =>
            String(section.year_level_id) === String(yearLevelFilter.value),
    );
});

const availableSubjects = computed(() => {
    if (yearLevelFilter.value === "all") {
        return props.subjects;
    }

    return props.subjects.filter(
        (subject) =>
            String(subject.year_level_id) === String(yearLevelFilter.value),
    );
});

watch(yearLevelFilter, () => {
    if (
        sectionFilter.value !== "all" &&
        !availableSections.value.some(
            (section) => String(section.id) === String(sectionFilter.value),
        )
    ) {
        sectionFilter.value = "all";
    }
    subjectFilter.value = "all";
});

const filteredRecords = computed(() => {
    return props.records.filter((row) => {
        const search = searchQuery.value.toLowerCase();
        const matchesSearch =
            !search ||
            row.student_name?.toLowerCase().includes(search) ||
            row.lrn?.toLowerCase().includes(search);
        const matchesYear =
            yearLevelFilter.value === "all" ||
            String(row.year_level_id) === String(yearLevelFilter.value);
        const matchesSection =
            sectionFilter.value === "all" ||
            String(row.section_id) === String(sectionFilter.value);
        const matchesSubject =
            subjectFilter.value === "all" ||
            String(row.subject_id) === String(subjectFilter.value);
        const matchesRemarks =
            remarksFilter.value === "all" ||
            row.remarks === remarksFilter.value;

        return (
            matchesSearch &&
            matchesYear &&
            matchesSection &&
            matchesSubject &&
            matchesRemarks
        );
    });
});

const formatGrade = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    return Number(value).toFixed(2);
};

const remarksClass = (remarks) => {
    if (remarks === "Passed") return "enrolled";
    if (remarks === "Failed") return "rejected";
    return "pending";
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.filter-group {
    flex-wrap: wrap;
    justify-content: flex-end;
}
</style>
