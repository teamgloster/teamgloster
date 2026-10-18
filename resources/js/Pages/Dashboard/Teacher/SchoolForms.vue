<template>
    <TeacherLayout
        title="School Forms"
        pageTitle="School Forms"
        currentPage="school-forms"
        :user="user"
    >
        <div class="content-section">
            <SchoolFormsHub
                viewer="teacher"
                base-path="/teacher"
                :school-year="currentSchoolYear"
                :current-term="currentTerm"
                :year-levels="yearLevels"
                :sections="sections"
                :students="students"
            />
        </div>
    </TeacherLayout>
</template>

<script setup>
import TeacherLayout from "@/Layouts/TeacherLayout.vue";
import SchoolFormsHub from "@/Pages/Dashboard/SchoolForms/SchoolFormsHub.vue";

defineProps({
    user: { type: Object, required: true },
    currentSchoolYear: { type: String, default: "" },
    currentTerm: { type: [Number, String], default: null },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});
</script>
