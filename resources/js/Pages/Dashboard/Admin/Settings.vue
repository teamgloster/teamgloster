<template>
    <AdminLayout
        title="Settings"
        pageTitle="Settings"
        currentPage="settings"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    {{ settingsForm.school_name }} —
                    {{ settingsForm.municipality }},
                    {{ settingsForm.province }}
                </p>
                <div class="gov-pagehead-row">
                    <h2>Settings</h2>
                    <span class="gov-sy"
                        >School Year {{ settingsForm.current_school_year }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">School Year</div>
                    <div class="gov-stat-value gov-stat-text">
                        {{ settings.current_school_year }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Current Term</div>
                    <div class="gov-stat-value">
                        {{ settings.current_term }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrollment</div>
                    <div class="gov-stat-value gov-stat-text">
                        {{ settings.enrollment_open ? "Open" : "Closed" }}
                    </div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Division</div>
                    <div class="gov-stat-value gov-stat-text">
                        {{ settings.division || "—" }}
                    </div>
                </div>
            </div>

            <form @submit.prevent="saveSettings" class="settings-form">
                <div class="gov-panel">
                    <div class="gov-panel-bar">School Information</div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label
                                    >School Name
                                    <span class="required">*</span></label
                                >
                                <input
                                    v-model="settingsForm.school_name"
                                    type="text"
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label>DepEd School ID</label>
                                <input
                                    v-model="settingsForm.school_id"
                                    type="text"
                                    placeholder="e.g., 301234"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Region</label>
                                <input
                                    v-model="settingsForm.region"
                                    type="text"
                                    placeholder="e.g., Region V (Bicol)"
                                />
                            </div>
                            <div class="form-group">
                                <label>Division</label>
                                <input
                                    v-model="settingsForm.division"
                                    type="text"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>District</label>
                                <input
                                    v-model="settingsForm.district"
                                    type="text"
                                />
                            </div>
                            <div class="form-group">
                                <label>School Head</label>
                                <input
                                    v-model="settingsForm.school_head"
                                    type="text"
                                    placeholder="Name of Principal / School Head"
                                />
                                <p class="field-help">
                                    Printed on SF9 and SF10 with Region,
                                    Division, District, and School ID.
                                </p>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Address</label>
                                <input
                                    v-model="settingsForm.address"
                                    type="text"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Municipality</label>
                                <input
                                    v-model="settingsForm.municipality"
                                    type="text"
                                />
                            </div>
                            <div class="form-group">
                                <label>Province</label>
                                <input
                                    v-model="settingsForm.province"
                                    type="text"
                                />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input
                                    v-model="settingsForm.contact_number"
                                    type="text"
                                />
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input
                                    v-model="settingsForm.email"
                                    type="email"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gov-two-col">
                    <div class="gov-panel">
                        <div class="gov-panel-bar">Academic Period</div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label
                                    >Current School Year
                                    <span class="required">*</span></label
                                >
                                <input
                                    v-model="settingsForm.current_school_year"
                                    type="text"
                                    required
                                    placeholder="2026-2027"
                                />
                                <p class="field-help">
                                    Use consecutive years, e.g. 2026-2027. This
                                    is the school year used for enrollments,
                                    sections, and grades.
                                </p>
                                <p
                                    v-if="errors.current_school_year"
                                    class="field-error"
                                >
                                    {{ errors.current_school_year }}
                                </p>
                            </div>
                            <div class="form-group">
                                <label
                                    >Current Term
                                    <span class="required">*</span></label
                                >
                                <select
                                    v-model.number="settingsForm.current_term"
                                    required
                                >
                                    <option :value="1">Term 1</option>
                                    <option :value="2">Term 2</option>
                                    <option :value="3">Term 3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="gov-panel">
                        <div class="gov-panel-bar">Enrollment</div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label>Enrollment Status</label>
                                <select
                                    v-model="settingsForm.enrollment_open"
                                >
                                    <option :value="true">Open</option>
                                    <option :value="false">Closed</option>
                                </select>
                                <p class="field-help">
                                    When closed, students cannot submit a new
                                    enrollment application.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button
                        type="submit"
                        class="btn-primary"
                        :disabled="isSavingSettings"
                    >
                        <Loader2
                            v-if="isSavingSettings"
                            :size="18"
                            class="spin"
                        />
                        Save Settings
                    </button>
                </div>
            </form>

            <div class="gov-panel password-panel">
                <div class="gov-panel-bar">Change Administrator Password</div>
                <form @submit.prevent="savePassword" class="panel-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label
                                >Current Password
                                <span class="required">*</span></label
                            >
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                autocomplete="current-password"
                            />
                            <p
                                v-if="errors.current_password"
                                class="field-error"
                            >
                                {{ errors.current_password }}
                            </p>
                        </div>
                        <div class="form-group">
                            <label
                                >New Password
                                <span class="required">*</span></label
                            >
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            />
                            <p v-if="errors.password" class="field-error">
                                {{ errors.password }}
                            </p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label
                                >Confirm New Password
                                <span class="required">*</span></label
                            >
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>
                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn-primary"
                            :disabled="isSavingPassword"
                        >
                            <Loader2
                                v-if="isSavingPassword"
                                :size="18"
                                class="spin"
                            />
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import { Loader2 } from "lucide-vue-next";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
});

const toast = useToast();
const page = usePage();
const errors = computed(() => page.props.errors || {});

const isSavingSettings = ref(false);
const isSavingPassword = ref(false);

const settingsForm = ref({
    school_name: props.settings.school_name || "",
    school_id: props.settings.school_id || "",
    region: props.settings.region || "",
    division: props.settings.division || "",
    district: props.settings.district || "",
    school_head: props.settings.school_head || "",
    address: props.settings.address || "",
    municipality: props.settings.municipality || "",
    province: props.settings.province || "",
    contact_number: props.settings.contact_number || "",
    email: props.settings.email || "",
    current_school_year: props.settings.current_school_year || "",
    current_term: props.settings.current_term || 1,
    enrollment_open: props.settings.enrollment_open ?? true,
});

const passwordForm = ref({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const saveSettings = () => {
    isSavingSettings.value = true;
    router.put("/admin/settings", settingsForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Settings saved successfully.");
        },
        onError: (formErrors) => {
            const firstError = Object.values(formErrors)[0];
            toast.error(firstError || "Failed to save settings.");
        },
        onFinish: () => {
            isSavingSettings.value = false;
        },
    });
};

const savePassword = () => {
    isSavingPassword.value = true;
    router.put("/admin/settings/password", passwordForm.value, {
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
            isSavingPassword.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.gov-pagehead {
    margin-bottom: 1.35rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #cfcfcf;
}

.gov-kicker {
    margin: 0 0 0.25rem;
    font-size: 0.78rem;
    color: #555;
}

.gov-pagehead-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
}

.gov-pagehead h2 {
    margin: 0;
    color: #003366;
    font-size: 1.2rem;
}

.gov-sy {
    font-size: 0.85rem;
    color: #333;
    font-weight: 600;
}

.gov-stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.gov-stat-box {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.gov-stat-label {
    background: #003366;
    color: #fff;
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.gov-stat-value {
    padding: 0.75rem 0.6rem 0.85rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #003366;
    text-align: center;
}

.gov-stat-text {
    font-size: 0.95rem;
    line-height: 1.3;
}

.gov-panel {
    background: #fff;
    border: 1px solid #c5c5c5;
    margin-bottom: 0.85rem;
}

.gov-panel-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.panel-body {
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.gov-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
    margin-bottom: 0.85rem;
}

.gov-two-col .gov-panel {
    margin-bottom: 0;
}

.field-help {
    margin: 0.25rem 0 0;
    font-size: 0.78rem;
    color: #555;
}

.field-error {
    margin: 0.25rem 0 0;
    font-size: 0.78rem;
    color: #9b1c1c;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin: 0.25rem 0 1.25rem;
}

.password-panel {
    margin-top: 0.5rem;
}

.password-panel .form-actions {
    margin-bottom: 0;
}

@media (max-width: 900px) {
    .gov-stat-row,
    .gov-two-col {
        grid-template-columns: 1fr;
    }

    .gov-pagehead-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
