<template>
    <RegistrarLayout
        title="Year Levels"
        pageTitle="Year Levels"
        currentPage="year-levels"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Year Levels</h2>
                    <span class="gov-sy"
                        >School Year {{ currentSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Total</div>
                    <div class="gov-stat-value">{{ yearLevels.length }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Junior High</div>
                    <div class="gov-stat-value">
                        {{
                            yearLevels.filter(
                                (level) => level.level_type === "junior_high",
                            ).length
                        }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Senior High</div>
                    <div class="gov-stat-value">
                        {{
                            yearLevels.filter(
                                (level) => level.level_type === "senior_high",
                            ).length
                        }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Active</div>
                    <div class="gov-stat-value">
                        {{
                            yearLevels.filter((level) => level.is_active)
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
                            placeholder="Search year levels by name or code..."
                            class="search-input"
                        />
                    </div>
                    <select v-model="typeFilter" class="filter-select">
                        <option value="all">All Types</option>
                        <option value="junior_high">Junior High</option>
                        <option value="senior_high">Senior High</option>
                    </select>
                </div>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Year Level</th>
                            <th>Type</th>
                            <th>Sections</th>
                            <th>Subjects</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="yearLevel in filteredYearLevels"
                            :key="yearLevel.id"
                        >
                            <td>
                                <div class="year-level-cell">
                                    <div class="year-level-info-cell">
                                        <span class="year-level-name-cell">{{
                                            yearLevel.name
                                        }}</span>
                                        <span class="year-level-code">{{
                                            yearLevel.code
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="type-badge"
                                    :class="yearLevel.level_type"
                                >
                                    {{
                                        yearLevel.level_type === "senior_high"
                                            ? "Senior High"
                                            : "Junior High"
                                    }}
                                </span>
                            </td>
                            <td>{{ yearLevel.sections_count || 0 }}</td>
                            <td>{{ yearLevel.subjects_count || 0 }}</td>
                            <td>{{ yearLevel.enrollments_count || 0 }}</td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        yearLevel.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        yearLevel.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="filteredYearLevels.length === 0">
                            <td colspan="6" class="empty-table">
                                <div class="empty-message">
                                    <p>No year levels found</p>
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
    yearLevels: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});

const searchQuery = ref("");
const typeFilter = ref("all");

const filteredYearLevels = computed(() => {
    return props.yearLevels.filter((level) => {
        const matchesType =
            typeFilter.value === "all" || level.level_type === typeFilter.value;
        const search = searchQuery.value.toLowerCase();
        const matchesSearch =
            !search ||
            level.name?.toLowerCase().includes(search) ||
            level.code?.toLowerCase().includes(search);

        return matchesType && matchesSearch;
    });
});
</script>

<style scoped>
@import "@/Styles/admin-common.css";
</style>
