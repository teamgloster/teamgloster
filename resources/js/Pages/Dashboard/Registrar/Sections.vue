<template>
    <RegistrarLayout
        title="Sections"
        pageTitle="Sections"
        currentPage="sections"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Sections</h2>
                    <span class="gov-sy"
                        >School Year {{ currentSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total Sections</div>
                    <div class="gov-stat-value">{{ sections.length }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Active</div>
                    <div class="gov-stat-value">
                        {{ sections.filter((section) => section.is_active).length }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrolled Students</div>
                    <div class="gov-stat-value">{{ totalEnrolled }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total Capacity</div>
                    <div class="gov-stat-value">{{ totalCapacity }}</div>
                </div>
            </div>

            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search sections by name or code..."
                            class="search-input"
                        />
                    </div>
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
                </div>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Year Level</th>
                            <th>Adviser</th>
                            <th>Enrollment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="section in filteredSections"
                            :key="section.id"
                        >
                            <td>
                                <div class="section-info-cell">
                                    <span class="section-name-cell">{{
                                        section.name
                                    }}</span>
                                    <span class="section-code">{{
                                        section.code || "—"
                                    }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="year-level-badge">
                                    {{ section.year_level?.name || "—" }}
                                </span>
                            </td>
                            <td>
                                {{
                                    section.adviser
                                        ? `${section.adviser.first_name} ${section.adviser.last_name}`
                                        : "Not assigned"
                                }}
                            </td>
                            <td>
                                <span class="capacity-badge">
                                    {{ section.enrollments_count || 0 }}/{{
                                        section.capacity || 0
                                    }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        section.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        section.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="filteredSections.length === 0">
                            <td colspan="5" class="empty-table">
                                <div class="empty-message">
                                    <p>No sections found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </RegistrarLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Search } from "lucide-vue-next";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    sections: { type: Array, default: () => [] },
    yearLevels: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});

const searchQuery = ref("");
const yearLevelFilter = ref("all");

const totalEnrolled = computed(() =>
    props.sections.reduce(
        (sum, section) => sum + (section.enrollments_count || 0),
        0,
    ),
);

const totalCapacity = computed(() =>
    props.sections.reduce((sum, section) => sum + (section.capacity || 0), 0),
);

const filteredSections = computed(() => {
    return props.sections.filter((section) => {
        const matchesYear =
            yearLevelFilter.value === "all" ||
            String(section.year_level_id) === String(yearLevelFilter.value);
        const search = searchQuery.value.toLowerCase();
        const matchesSearch =
            !search ||
            section.name?.toLowerCase().includes(search) ||
            section.code?.toLowerCase().includes(search);

        return matchesYear && matchesSearch;
    });
});
</script>

<style scoped>
@import "@/Styles/admin-common.css";
</style>
