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
                                <label
                                    >School Head / Principal
                                    <span class="required">*</span></label
                                >
                                <input
                                    v-model="settingsForm.school_head"
                                    type="text"
                                    required
                                    placeholder="e.g., Juan Dela Cruz"
                                />
                                <p class="field-help">
                                    This name is printed automatically on the
                                    School Head signature line of SF1, SF2, SF9,
                                    and SP-10.
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
                                <div class="sy-dropdown">
                                    <button
                                        type="button"
                                        class="sy-dropdown-toggle"
                                        @click="yearMenuOpen = !yearMenuOpen"
                                    >
                                        <span>
                                            {{
                                                settingsForm.current_school_year
                                                    ? `SY ${settingsForm.current_school_year}`
                                                    : "Set up a school year first"
                                            }}
                                        </span>
                                        <span class="sy-caret">▾</span>
                                    </button>
                                    <div
                                        v-if="yearMenuOpen"
                                        class="sy-dropdown-menu"
                                    >
                                        <p
                                            v-if="academicYears.length === 0"
                                            class="sy-empty"
                                        >
                                            No school years yet. Add one below.
                                        </p>
                                        <div
                                            v-for="item in academicYears"
                                            :key="item.id"
                                            class="sy-option"
                                            :class="{
                                                selected:
                                                    item.year ===
                                                    settingsForm.current_school_year,
                                            }"
                                        >
                                            <button
                                                type="button"
                                                class="sy-option-label"
                                                @click="selectSchoolYear(item.year)"
                                            >
                                                SY {{ item.year }}
                                                <small v-if="item.is_current"
                                                    >(current)</small
                                                >
                                            </button>
                                            <button
                                                type="button"
                                                class="sy-remove"
                                                :disabled="!item.can_remove"
                                                :title="
                                                    item.can_remove
                                                        ? 'Remove this school year'
                                                        : 'Cannot remove the current year or a year that already has records'
                                                "
                                                @click.stop="
                                                    removeSchoolYear(item)
                                                "
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p class="field-help">
                                    Only school years you set up appear here.
                                    Choose one as current, or remove a year that
                                    is not in use.
                                </p>
                                <p
                                    v-if="errors.current_school_year"
                                    class="field-error"
                                >
                                    {{ errors.current_school_year }}
                                </p>
                            </div>
                            <div class="form-group">
                                <label>Add school year</label>
                                <div class="sy-add-row">
                                    <input
                                        v-model.number="newStartYear"
                                        type="number"
                                        min="2000"
                                        max="2100"
                                        placeholder="Start year"
                                    />
                                    <span class="sy-preview">{{
                                        newYearPreview
                                    }}</span>
                                    <button
                                        type="button"
                                        class="btn-secondary"
                                        :disabled="
                                            isSavingYear || !newYearPreview
                                        "
                                        @click="addSchoolYear"
                                    >
                                        Add
                                    </button>
                                </div>
                                <p class="field-help">
                                    Enter the starting year. The system saves it
                                    as consecutive years, e.g. 2026 becomes
                                    2026-2027.
                                </p>
                                <p
                                    v-if="errors.start_year"
                                    class="field-error"
                                >
                                    {{ errors.start_year }}
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

        <ConfirmModal
            :show="!!yearToRemove"
            title="Remove School Year"
            :message="
                yearToRemove
                    ? `Remove school year ${yearToRemove.year}? This cannot be undone.`
                    : ''
            "
            confirm-label="Remove"
            :busy="isSavingYear"
            @cancel="yearToRemove = null"
            @confirm="confirmRemoveSchoolYear"
        />
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import { Loader2 } from "lucide-vue-next";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    settings: {
        type: Object,
        required: true,
    },
    academicYears: {
        type: Array,
        default: () => [],
    },
    officialSchoolYears: {
        type: Array,
        default: () => [],
    },
});

const toast = useToast();
const page = usePage();
const errors = computed(() => page.props.errors || {});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.error) {
            toast.error(flash.error);
        }
    },
);

const isSavingSettings = ref(false);
const isSavingPassword = ref(false);
const isSavingYear = ref(false);
const yearMenuOpen = ref(false);
const newStartYear = ref("");
const yearToRemove = ref(null);

const newYearPreview = computed(() => {
    const start = Number(newStartYear.value);
    if (!Number.isInteger(start) || start < 2000 || start > 2100) {
        return "";
    }
    return `${start}-${start + 1}`;
});

const closeYearMenu = () => {
    yearMenuOpen.value = false;
};

const onDocumentClick = (event) => {
    const root = event.target.closest?.(".sy-dropdown");
    if (!root) {
        closeYearMenu();
    }
};

onMounted(() => document.addEventListener("mousedown", onDocumentClick));
onUnmounted(() => document.removeEventListener("mousedown", onDocumentClick));

const selectSchoolYear = (year) => {
    settingsForm.value.current_school_year = year;
    closeYearMenu();
};

const addSchoolYear = () => {
    if (!newYearPreview.value) return;
    isSavingYear.value = true;
    router.post(
        "/admin/settings/school-years",
        { start_year: Number(newStartYear.value) },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(`School year ${newYearPreview.value} was added.`);
                settingsForm.value.current_school_year = newYearPreview.value;
                newStartYear.value = "";
            },
            onError: (formErrors) => {
                const firstError = Object.values(formErrors)[0];
                toast.error(firstError || "Could not add that school year.");
            },
            onFinish: () => {
                isSavingYear.value = false;
            },
        },
    );
};

const removeSchoolYear = (item) => {
    if (!item.can_remove) return;
    yearToRemove.value = item;
};

const confirmRemoveSchoolYear = () => {
    const item = yearToRemove.value;
    if (!item?.can_remove) return;
    isSavingYear.value = true;
    router.delete(`/admin/settings/school-years/${item.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`School year ${item.year} was removed.`);
            if (settingsForm.value.current_school_year === item.year) {
                settingsForm.value.current_school_year =
                    props.settings.current_school_year || "";
            }
        },
        onError: () => {
            toast.error("Could not remove that school year.");
        },
        onFinish: () => {
            isSavingYear.value = false;
            yearToRemove.value = null;
        },
    });
};

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

.sy-dropdown {
    position: relative;
}

.sy-dropdown-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.45rem 0.55rem;
    border: 1px solid #bdbdbd;
    background: #fff;
    color: #1a1a1a;
    font-size: 0.9rem;
    text-align: left;
    cursor: pointer;
}

.sy-caret {
    color: #555;
}

.sy-dropdown-menu {
    position: absolute;
    z-index: 20;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #bdbdbd;
    max-height: 220px;
    overflow-y: auto;
}

.sy-empty {
    margin: 0;
    padding: 0.7rem 0.75rem;
    font-size: 0.82rem;
    color: #555;
}

.sy-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid #eee;
}

.sy-option:last-child {
    border-bottom: none;
}

.sy-option.selected {
    background: #e8eef5;
}

.sy-option-label {
    flex: 1;
    border: 0;
    background: transparent;
    text-align: left;
    padding: 0.55rem 0.7rem;
    color: #1a1a1a;
    cursor: pointer;
}

.sy-option-label small {
    color: #003366;
    margin-left: 0.35rem;
}

.sy-remove {
    margin-right: 0.45rem;
    border: 1px solid #c45c5c;
    background: #fff;
    color: #9b1c1c;
    font-size: 0.75rem;
    padding: 0.2rem 0.45rem;
    cursor: pointer;
}

.sy-remove:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.sy-add-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sy-add-row input {
    width: 8rem;
}

.sy-preview {
    font-size: 0.85rem;
    font-weight: 600;
    color: #003366;
    min-width: 5.5rem;
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
