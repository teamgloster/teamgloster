<template>
    <RegistrarLayout
        title="Grading Records"
        pageTitle="Grading Records"
        currentPage="grades"
        :user="user"
    >
        <GradingRecordsTable
            :records="records"
            :year-levels="yearLevels"
            :sections="sections"
            :subjects="subjects"
            :current-school-year="currentSchoolYear"
        />
    </RegistrarLayout>
</template>

<script setup>
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";
import GradingRecordsTable from "@/Components/GradingRecordsTable.vue";

defineProps({
    user: { type: Object, required: true },
    records: { type: Array, default: () => [] },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});
</script>
