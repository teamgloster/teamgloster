<template>
    <AdminLayout
        title="Accounts"
        pageTitle="Accounts"
        currentPage="accounts"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Accounts</h2>
                </div>
            </div>

            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="accountSearch"
                            type="text"
                            placeholder="Search accounts by name or email..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-row">
                        <select v-model="roleFilter" class="role-filter">
                            <option value="all">All roles</option>
                            <option value="administrator">Administrators</option>
                            <option value="registrar">Registrars</option>
                        </select>
                        <button
                            class="btn-primary"
                            @click="openAccountModal('add')"
                        >
                            <UserPlus :size="18" />
                            Add Account
                        </button>
                    </div>
                </div>
            </div>

            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="account in filteredAccounts"
                            :key="account.id"
                        >
                            <td>
                                <div class="user-cell">
                                    <div
                                        class="user-avatar-sm"
                                        :class="account.role"
                                    >
                                        {{ getInitials(account) }}
                                    </div>
                                    <div class="user-info-cell">
                                        <span class="user-name-cell">
                                            {{ account.last_name }},
                                            {{ account.first_name }}
                                            {{
                                                account.middle_name
                                                    ? account.middle_name.charAt(
                                                          0,
                                                      ) + "."
                                                    : ""
                                            }}
                                            {{ account.suffix || "" }}
                                        </span>
                                        <span class="user-meta">
                                            {{
                                                formatDateFull(
                                                    account.date_of_birth,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ account.email }}</td>
                            <td>
                                <span
                                    class="role-badge"
                                    :class="account.role"
                                >
                                    {{ roleLabel(account.role) }}
                                </span>
                            </td>
                            <td>{{ account.phone_no || "-" }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        title="View Details"
                                        @click="viewAccount(account)"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        title="Edit Account"
                                        @click="
                                            openAccountModal('edit', account)
                                        "
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        v-if="canDeleteAccount(account)"
                                        class="btn-icon delete"
                                        title="Delete Account"
                                        @click="confirmDeleteAccount(account)"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredAccounts.length === 0">
                            <td colspan="5" class="empty-table">
                                <div class="empty-message">
                                    <Shield :size="40" />
                                    <p>No accounts found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredAccounts.length }} of
                    {{ accounts.length }} accounts
                </span>
            </div>
        </div>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showAccountModal"
                    class="modal-overlay"
                    @click.self="closeAccountModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header">
                            <h3>
                                <UserPlus
                                    v-if="accountModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="accountModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    accountModalMode === "add"
                                        ? "Add Account"
                                        : accountModalMode === "edit"
                                          ? "Edit Account"
                                          : "Account Details"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeAccountModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div
                                v-if="accountModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div class="detail-avatar">
                                        {{ getInitials(selectedAccount) }}
                                    </div>
                                    <div class="detail-title">
                                        <h4>
                                            {{ selectedAccount.first_name }}
                                            {{ selectedAccount.middle_name }}
                                            {{ selectedAccount.last_name }}
                                            {{ selectedAccount.suffix }}
                                        </h4>
                                        <span
                                            class="role-badge"
                                            :class="selectedAccount.role"
                                        >
                                            {{
                                                roleLabel(selectedAccount.role)
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Email</label>
                                        <span>{{ selectedAccount.email }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Phone</label>
                                        <span>{{
                                            selectedAccount.phone_no ||
                                            "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Date of Birth</label>
                                        <span>{{
                                            formatDateFull(
                                                selectedAccount.date_of_birth,
                                            ) || "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Gender</label>
                                        <span>{{
                                            selectedAccount.gender
                                                ? selectedAccount.gender
                                                      .charAt(0)
                                                      .toUpperCase() +
                                                  selectedAccount.gender.slice(
                                                      1,
                                                  )
                                                : "Not provided"
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <form
                                v-else
                                @submit.prevent="submitAccountForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Role
                                            <span class="required">*</span></label
                                        >
                                        <select
                                            v-model="accountForm.role"
                                            required
                                            :disabled="isOwnAccount"
                                        >
                                            <option value="registrar">
                                                Registrar
                                            </option>
                                            <option value="administrator">
                                                Administrator
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input
                                            v-model="accountForm.phone_no"
                                            type="tel"
                                            placeholder="09XXXXXXXXX"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >First Name
                                            <span class="required">*</span></label
                                        >
                                        <input
                                            v-model="accountForm.first_name"
                                            type="text"
                                            required
                                            placeholder="Enter first name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input
                                            v-model="accountForm.middle_name"
                                            type="text"
                                            placeholder="Enter middle name"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Last Name
                                            <span class="required">*</span></label
                                        >
                                        <input
                                            v-model="accountForm.last_name"
                                            type="text"
                                            required
                                            placeholder="Enter last name"
                                        />
                                    </div>
                                    <div class="form-group small">
                                        <label>Suffix</label>
                                        <input
                                            v-model="accountForm.suffix"
                                            type="text"
                                            placeholder="Jr., III"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Email
                                            <span class="required">*</span></label
                                        >
                                        <input
                                            v-model="accountForm.email"
                                            type="email"
                                            required
                                            placeholder="name@tnhs.edu.ph"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input
                                            v-model="accountForm.date_of_birth"
                                            type="date"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select v-model="accountForm.gender">
                                            <option value="">
                                                Select gender
                                            </option>
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="form-row"
                                    v-if="accountModalMode === 'add'"
                                >
                                    <div class="form-group">
                                        <label
                                            >Password
                                            <span class="required">*</span></label
                                        >
                                        <div class="password-input-wrapper">
                                            <input
                                                v-model="accountForm.password"
                                                :type="
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                required
                                                placeholder="Minimum 8 characters"
                                            />
                                            <button
                                                type="button"
                                                class="password-toggle"
                                                :aria-label="
                                                    showPassword
                                                        ? 'Hide password'
                                                        : 'Show password'
                                                "
                                                :title="
                                                    showPassword
                                                        ? 'Hide password'
                                                        : 'Show password'
                                                "
                                                @click="
                                                    showPassword = !showPassword
                                                "
                                            >
                                                <EyeOff
                                                    v-if="showPassword"
                                                    :size="16"
                                                />
                                                <Eye v-else :size="16" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Confirm Password
                                            <span class="required">*</span></label
                                        >
                                        <div class="password-input-wrapper">
                                            <input
                                                v-model="
                                                    accountForm.password_confirmation
                                                "
                                                :type="
                                                    showPasswordConfirmation
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                required
                                                placeholder="Confirm password"
                                            />
                                            <button
                                                type="button"
                                                class="password-toggle"
                                                :aria-label="
                                                    showPasswordConfirmation
                                                        ? 'Hide password'
                                                        : 'Show password'
                                                "
                                                :title="
                                                    showPasswordConfirmation
                                                        ? 'Hide password'
                                                        : 'Show password'
                                                "
                                                @click="
                                                    showPasswordConfirmation =
                                                        !showPasswordConfirmation
                                                "
                                            >
                                                <EyeOff
                                                    v-if="
                                                        showPasswordConfirmation
                                                    "
                                                    :size="16"
                                                />
                                                <Eye v-else :size="16" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeAccountModal"
                            >
                                {{
                                    accountModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="accountModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitAccountForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    accountModalMode === "add"
                                        ? "Create Account"
                                        : "Save Changes"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showDeleteModal"
                    class="modal-overlay"
                    @click.self="showDeleteModal = false"
                >
                    <div class="modal-container small">
                        <div class="modal-header danger">
                            <h3>
                                <AlertTriangle :size="22" />
                                Confirm Delete
                            </h3>
                            <button
                                class="close-btn"
                                @click="showDeleteModal = false"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="delete-warning">
                                <div class="warning-icon">
                                    <Trash2 :size="40" />
                                </div>
                                <p>
                                    Are you sure you want to delete this
                                    account?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{ accountToDelete?.first_name }}
                                        {{ accountToDelete?.last_name }}</strong
                                    >
                                    <span>{{ accountToDelete?.email }}</span>
                                    <span>{{
                                        roleLabel(accountToDelete?.role)
                                    }}</span>
                                </div>
                                <p class="warning-text">
                                    This account will no longer be able to sign
                                    in.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="showDeleteModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="isSubmitting"
                                @click="deleteAccount"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    AlertTriangle,
    Eye,
    EyeOff,
    Loader2,
    Pencil,
    Search,
    Shield,
    Trash2,
    UserPlus,
    X,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    accounts: {
        type: Array,
        default: () => [],
    },
});

const accountSearch = ref("");
const roleFilter = ref("all");
const showAccountModal = ref(false);
const accountModalMode = ref("add");
const selectedAccount = ref(null);
const showDeleteModal = ref(false);
const accountToDelete = ref(null);
const isSubmitting = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const emptyForm = () => ({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    email: "",
    phone_no: "",
    date_of_birth: "",
    gender: "",
    role: "registrar",
    password: "",
    password_confirmation: "",
});

const accountForm = ref(emptyForm());

const administratorCount = computed(
    () =>
        props.accounts.filter((account) => account.role === "administrator")
            .length,
);

const isOwnAccount = computed(
    () =>
        accountModalMode.value === "edit" &&
        selectedAccount.value?.id === props.user.id,
);

const filteredAccounts = computed(() => {
    const search = accountSearch.value.toLowerCase().trim();

    return props.accounts.filter((account) => {
        const matchesRole =
            roleFilter.value === "all" || account.role === roleFilter.value;
        const matchesSearch =
            !search ||
            account.first_name?.toLowerCase().includes(search) ||
            account.last_name?.toLowerCase().includes(search) ||
            account.email?.toLowerCase().includes(search);

        return matchesRole && matchesSearch;
    });
});

const roleLabel = (role) => {
    if (role === "administrator") return "Administrator";
    if (role === "registrar") return "Registrar";
    return role || "";
};

const getInitials = (account) => {
    if (!account) return "?";
    return (
        (account.first_name?.charAt(0) || "") +
        (account.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const formatDateFull = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
};

const canDeleteAccount = (account) => {
    if (account.id === props.user.id) return false;
    if (account.role === "administrator" && administratorCount.value <= 1) {
        return false;
    }
    return true;
};

const resetAccountForm = () => {
    accountForm.value = emptyForm();
};

const openAccountModal = (mode, account = null) => {
    accountModalMode.value = mode;
    showPassword.value = false;
    showPasswordConfirmation.value = false;

    if (mode === "edit" && account) {
        selectedAccount.value = account;
        accountForm.value = {
            first_name: account.first_name || "",
            middle_name: account.middle_name || "",
            last_name: account.last_name || "",
            suffix: account.suffix || "",
            email: account.email || "",
            phone_no: account.phone_no || "",
            date_of_birth: account.date_of_birth
                ? account.date_of_birth.split("T")[0]
                : "",
            gender: account.gender || "",
            role: account.role || "registrar",
            password: "",
            password_confirmation: "",
        };
    } else {
        resetAccountForm();
        selectedAccount.value = null;
    }

    showAccountModal.value = true;
};

const viewAccount = (account) => {
    selectedAccount.value = account;
    accountModalMode.value = "view";
    showAccountModal.value = true;
};

const closeAccountModal = () => {
    showAccountModal.value = false;
    resetAccountForm();
    selectedAccount.value = null;
    showPassword.value = false;
    showPasswordConfirmation.value = false;
};

const submitAccountForm = () => {
    if (accountModalMode.value === "add") {
        if (accountForm.value.password !== accountForm.value.password_confirmation) {
            toast.error("Password confirmation does not match.");
            return;
        }
    }

    isSubmitting.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(
                accountModalMode.value === "add"
                    ? "Account created successfully!"
                    : "Account updated successfully!",
            );
            closeAccountModal();
        },
        onError: (errors) => {
            toast.error(Object.values(errors)[0]);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    };

    if (accountModalMode.value === "add") {
        router.post("/admin/accounts", accountForm.value, options);
        return;
    }

    if (accountModalMode.value === "edit" && selectedAccount.value) {
        router.put(
            `/admin/accounts/${selectedAccount.value.id}`,
            accountForm.value,
            options,
        );
    }
};

const confirmDeleteAccount = (account) => {
    accountToDelete.value = account;
    showDeleteModal.value = true;
};

const deleteAccount = () => {
    if (!accountToDelete.value) return;

    isSubmitting.value = true;

    router.delete(`/admin/accounts/${accountToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Account deleted successfully!");
            showDeleteModal.value = false;
            accountToDelete.value = null;
        },
        onError: (errors) => {
            toast.error(Object.values(errors)[0] || "Failed to delete account.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.filter-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.role-filter {
    min-width: 11rem;
    padding: 0.5rem 0.6rem;
    border: 1px solid #bdbdbd;
    background: #fff;
    color: #222;
    font-size: 0.9rem;
}

.password-input-wrapper {
    position: relative;
}

.password-input-wrapper input {
    padding-right: 2.4rem;
    width: 100%;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 0.4rem;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.15rem;
    border: none;
    background: transparent;
    color: #555;
    cursor: pointer;
}

.password-toggle:hover {
    color: #003366;
}
</style>
