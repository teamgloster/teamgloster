<template>
    <RegistrarLayout
        title="Students"
        pageTitle="Students"
        currentPage="students"
        :user="user"
    >
        <div class="content-section lis-page">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Learner Information
                </p>
                <div class="gov-pagehead-row">
                    <h2>Students</h2>
                    <span class="gov-sy"
                        >School Year {{ selectedSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="lis-banner" :class="enrollmentOpen ? 'open' : 'closed'">
                <strong>{{
                    enrollmentOpen ? "Enrolment is Open" : "End of School Year"
                }}</strong>
                <span>Showing enrolment for SY {{ selectedSchoolYear }}</span>
            </div>
            <p v-if="unassignedCount" class="lis-status">
                {{ unassignedCount }} learner(s) are not yet assigned to a
                section.
            </p>

            <div class="lis-toolbar">
                <input
                    v-model="sectionSearch"
                    type="text"
                    class="lis-search"
                    placeholder="Search section..."
                />
                <select
                    class="lis-search lis-year"
                    :value="selectedSchoolYear"
                    @change="changeSchoolYear"
                >
                    <option
                        v-for="year in availableSchoolYears"
                        :key="year"
                        :value="year"
                    >
                        SY {{ year }}{{ year === currentSchoolYear ? " (current)" : "" }}
                    </option>
                </select>
            </div>

            <div v-if="gradeColumns.length" class="lis-grid">
                <div
                    v-for="column in gradeColumns"
                    :key="column.id"
                    class="lis-column"
                >
                    <div class="lis-column-head">
                        {{ column.label }}
                    </div>
                    <div class="lis-column-body">
                        <div
                            v-for="section in column.sections"
                            :key="section.id"
                            class="lis-section"
                        >
                            <div class="lis-section-row">
                                <span class="lis-section-name">{{
                                    section.name
                                }}</span>
                                <span class="lis-count">{{
                                    section.enrolled_count || 0
                                }}</span>
                            </div>
                            <Link
                                class="lis-view-btn"
                                :href="`/registrar/sections/${section.id}/enrollment`"
                            >
                                View Enrolment
                            </Link>
                        </div>
                        <p
                            v-if="column.sections.length === 0"
                            class="lis-empty"
                        >
                            No sections
                        </p>
                    </div>
                </div>
            </div>
            <p v-else class="lis-empty">
                No year levels or sections are set up for this school year.
            </p>
        </div>
    </RegistrarLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    unassignedCount: { type: Number, default: 0 },
    currentSchoolYear: { type: String, default: "" },
    selectedSchoolYear: { type: String, default: "" },
    availableSchoolYears: { type: Array, default: () => [] },
    enrollmentOpen: { type: Boolean, default: true },
});

const sectionSearch = ref("");

const changeSchoolYear = (event) => {
    router.get(
        "/registrar/students",
        { sy: event.target.value },
        { preserveState: false, preserveScroll: true },
    );
};

const romanYear = (rank) =>
    ({
        7: "Year I",
        8: "Year II",
        9: "Year III",
        10: "Year IV",
        11: "SHS",
        12: "SHS",
    })[rank] || null;

const columnLabel = (level) => {
    const rank = Number(level.rank);
    const roman = romanYear(rank);
    return roman ? `${level.name} (${roman})` : level.name;
};

const gradeColumns = computed(() => {
    const search = sectionSearch.value.trim().toLowerCase();

    return [...(props.yearLevels || [])]
        .sort((a, b) => Number(a.rank || a.id) - Number(b.rank || b.id))
        .map((level) => {
            const sections = (props.sections || [])
                .filter(
                    (section) =>
                        String(section.year_level_id) === String(level.id),
                )
                .filter((section) => {
                    if (!search) return true;
                    return String(section.name || "")
                        .toLowerCase()
                        .includes(search);
                })
                .sort((a, b) =>
                    String(a.name).localeCompare(String(b.name), undefined, {
                        sensitivity: "base",
                    }),
                );

            return {
                id: level.id,
                label: columnLabel(level),
                sections,
            };
        })
        .filter((column) => !search || column.sections.length > 0);
});
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.lis-page {
    background: #f3f3f3;
    margin: -1.25rem -1.5rem -2rem;
    padding: 1.25rem 1.5rem 2rem;
}

.lis-banner {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    margin: 0 0 0.85rem;
    padding: 0.45rem 0.7rem;
    background: #d9edf7;
    border: 1px solid #b8d4e3;
    color: #245269;
    font-size: 0.84rem;
}

.lis-banner.closed {
    background: #d9edf7;
}

.lis-banner.open {
    background: #dff0d8;
    border-color: #c1e2b3;
    color: #3c763d;
}

.lis-status {
    margin: 0 0 0.75rem;
    color: #444;
    font-size: 0.88rem;
}

.lis-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.lis-search {
    width: min(320px, 100%);
    border: 1px solid #c5c5c5;
    background: #fff;
    padding: 0.4rem 0.55rem;
    font-size: 0.88rem;
}

.lis-year {
    width: auto;
    min-width: 140px;
}

.lis-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.65rem;
    align-items: start;
}

.lis-column {
    background: #fff;
    border: 1px solid #d4d4d4;
    min-width: 0;
}

.lis-column-head {
    background: #003366;
    color: #fff;
    font-size: 0.92rem;
    font-weight: 600;
    padding: 0.45rem 0.65rem;
    border-bottom: 1px solid #00264d;
}

.lis-column-body {
    padding: 0.35rem 0.45rem 0.55rem;
}

.lis-section {
    padding: 0.45rem 0.25rem 0.55rem;
    border-bottom: 1px solid #ececec;
}

.lis-section:last-child {
    border-bottom: 0;
}

.lis-section-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.lis-section-name {
    color: #1a4f9c;
    font-weight: 700;
    font-size: 0.86rem;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.lis-count {
    min-width: 1.7rem;
    background: #2b2b2b;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    text-align: center;
    padding: 0.12rem 0.35rem;
}

.lis-view-btn {
    display: inline-flex;
    margin-top: 0.35rem;
    margin-left: auto;
    background: #e8e8e8;
    color: #222;
    border: 1px solid #c8c8c8;
    text-decoration: none;
    font-size: 0.78rem;
    padding: 0.2rem 0.55rem;
}

.lis-view-btn:hover {
    background: #ddd;
}

.lis-empty {
    margin: 0.5rem 0.25rem;
    color: #777;
    font-size: 0.82rem;
}

@media (max-width: 1100px) {
    .lis-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .lis-page {
        margin: -1rem;
        padding: 1rem;
    }

    .lis-grid {
        grid-template-columns: 1fr;
    }
}
</style>
