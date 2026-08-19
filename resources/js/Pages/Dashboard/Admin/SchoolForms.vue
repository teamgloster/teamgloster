<template>
    <AdminLayout
        title="School Forms"
        pageTitle="School Forms"
        currentPage="school-forms"
        :user="user"
    >
        <div class="content-section">
            <SchoolFormsHub
                viewer="admin"
                base-path="/admin"
                :school-year="schoolYear"
                :current-term="currentTerm"
                :year-levels="yearLevels"
                :sections="sections"
                :students="students"
            />
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import SchoolFormsHub from "@/Pages/Dashboard/SchoolForms/SchoolFormsHub.vue";

defineProps({
    user: { type: Object, required: true },
    schoolYear: { type: String, default: "" },
    currentTerm: { type: [Number, String], default: null },
    yearLevels: { type: Array, default: () => [] },
    sections: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});
</script>
