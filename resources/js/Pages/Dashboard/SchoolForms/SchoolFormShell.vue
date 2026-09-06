<template>
    <component
        :is="layoutComponent"
        :title="title"
        :page-title="pageTitle"
        :current-page="navPage"
        :user="user"
    >
        <div class="sf-preview">
            <div class="sf-preview-bar">
                <div>
                    <div class="sf-preview-title">{{ title }}</div>
                    <p class="sf-preview-note">
                        Preview inside the portal. Use Print / Save PDF for a
                        file copy.
                    </p>
                </div>
                <div class="sf-preview-actions">
                    <button type="button" class="sf-btn ghost" @click="goBack">
                        Back
                    </button>
                    <button type="button" class="sf-btn" @click="printForm">
                        Print / Save PDF
                    </button>
                </div>
            </div>
            <div class="sf-preview-canvas">
                <slot></slot>
            </div>
        </div>
    </component>
</template>

<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import TeacherLayout from "@/Layouts/TeacherLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    viewer: { type: String, default: "admin" },
    title: { type: String, required: true },
    pageTitle: { type: String, default: "School Form Preview" },
    backUrl: { type: String, default: "/" },
});

const layoutComponent = computed(() => {
    if (props.viewer === "student") {
        return StudentLayout;
    }
    if (props.viewer === "teacher") {
        return TeacherLayout;
    }
    if (props.viewer === "registrar") {
        return RegistrarLayout;
    }
    return AdminLayout;
});

const navPage = computed(() => {
    if (props.viewer === "student") {
        return "grades";
    }
    if (props.viewer === "registrar") {
        return "students";
    }
    return "school-forms";
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }
    router.visit(props.backUrl);
};

const printForm = () => window.print();
</script>

<style>
@import "@/Styles/school-form.css";
</style>
