<template>
    <component
        :is="layout"
        title="Change Password"
        pageTitle="Change Password"
        currentPage="password"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Change Password</h2>
                </div>
            </div>

            <div class="gov-panel password-panel">
                <div class="gov-panel-bar">Update your password</div>
                <form class="panel-body" @submit.prevent="savePassword">
                    <div class="form-group">
                        <label>
                            Current Password
                            <span class="required">*</span>
                        </label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                        />
                        <p v-if="errors.current_password" class="field-error">
                            {{ errors.current_password }}
                        </p>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                New Password
                                <span class="required">*</span>
                            </label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            />
                            <p class="field-hint">Use at least 8 characters.</p>
                            <p v-if="errors.password" class="field-error">
                                {{ errors.password }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label>
                                Confirm New Password
                                <span class="required">*</span>
                            </label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            />
                            <p
                                v-if="errors.password_confirmation"
                                class="field-error"
                            >
                                {{ errors.password_confirmation }}
                            </p>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn-primary"
                            :disabled="isSaving"
                        >
                            <Loader2 v-if="isSaving" :size="18" class="spin" />
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </component>
</template>

<script setup>
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Loader2 } from "lucide-vue-next";
import { useToast } from "@/composables/useNotify";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import TeacherLayout from "@/Layouts/TeacherLayout.vue";
import RegistrarLayout from "@/Layouts/RegistrarLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    role: { type: String, required: true },
});

const layouts = {
    student: StudentLayout,
    teacher: TeacherLayout,
    registrar: RegistrarLayout,
};

const layout = computed(() => layouts[props.role] || StudentLayout);
const page = usePage();
const errors = computed(() => page.props.errors || {});
const toast = useToast();
const isSaving = ref(false);
const passwordForm = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const savePassword = () => {
    isSaving.value = true;
    router.put(`/${props.role}/password`, passwordForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Password updated successfully.");
            passwordForm.value = {
                current_password: "",
                password: "",
                password_confirmation: "",
            };
        },
        onError: (formErrors) => {
            const firstError = Object.values(formErrors)[0];
            toast.error(firstError || "Failed to update password.");
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.password-panel {
    max-width: 720px;
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
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1rem 0.9rem 0.9rem;
}

.field-hint {
    margin: 0;
    font-size: 0.78rem;
    color: #555;
}

.field-error {
    margin: 0;
    font-size: 0.78rem;
    color: #9b1c1c;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin: 0;
}

.spin {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
