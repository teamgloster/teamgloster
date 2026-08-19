<template>
    <Head title="Student Login - TNHS" />
    <div class="auth-container">
        <div class="overlay"></div>
        <div class="auth-content">
            <div class="auth-card">
                <div class="card-bar">Student Login</div>
                <div class="card-content">
                    <div class="auth-left">
                        <img :src="logo" alt="TNHS Logo" class="logo" />
                        <p class="info-text">Tambo National High School</p>
                        <p class="info-text-small">Buhi, Camarines Sur</p>
                    </div>

                    <div class="auth-right">
                        <h1 class="title">Sign in</h1>
                        <form @submit.prevent="submit" class="auth-form">
                            <div class="form-group">
                                <label for="lrn"
                                    >LRN (Learner Reference Number)</label
                                >
                                <input
                                    id="lrn"
                                    v-model="form.lrn"
                                    type="text"
                                    required
                                    placeholder="Enter your 12-digit LRN"
                                    class="form-input"
                                    :class="{ 'input-error': errors.lrn }"
                                    maxlength="12"
                                    pattern="[0-9]{12}"
                                    inputmode="numeric"
                                    autocomplete="username"
                                    @input="onLrnInput"
                                    @keydown="onLrnKeydown"
                                />
                                <span v-if="errors.lrn" class="error-message">{{
                                    errors.lrn
                                }}</span>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="password-input-wrapper">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        required
                                        placeholder="Enter your password"
                                        class="form-input password-input"
                                        :class="{
                                            'input-error': errors.password,
                                        }"
                                    />
                                    <button
                                        type="button"
                                        class="password-toggle"
                                        @click="showPassword = !showPassword"
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
                                    >
                                        <EyeOff
                                            v-if="showPassword"
                                            :size="18"
                                        />
                                        <Eye v-else :size="18" />
                                    </button>
                                </div>
                                <span
                                    v-if="errors.password"
                                    class="error-message"
                                    >{{ errors.password }}</span
                                >
                            </div>

                            <div class="form-group-checkbox">
                                <label class="checkbox-label">
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="checkbox"
                                    />
                                    <span>Remember me</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="submit-btn"
                                :disabled="processing"
                            >
                                {{ processing ? "Logging in..." : "Login" }}
                            </button>
                        </form>

                        <div class="auth-footer">
                            <p>
                                Don't have an account?
                                <a href="/admission/apply" class="link"
                                    >Apply for Admission</a
                                >
                            </p>
                            <a href="/" class="link">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { router, Head, usePage } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import { Eye, EyeOff } from "lucide-vue-next";

const toast = useToast();
const page = usePage();

defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";

const form = ref({
    lrn: "",
    password: "",
    remember: false,
    role: "student",
});

const processing = ref(false);
const showPassword = ref(false);

const onLrnInput = (event) => {
    const digits = event.target.value.replace(/\D/g, "").slice(0, 12);
    form.value.lrn = digits;
    event.target.value = digits;
};

const onLrnKeydown = (event) => {
    const allowedKeys = [
        "Backspace",
        "Delete",
        "Tab",
        "Escape",
        "Enter",
        "ArrowLeft",
        "ArrowRight",
        "Home",
        "End",
    ];

    if (allowedKeys.includes(event.key) || event.ctrlKey || event.metaKey) {
        return;
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault();
    }
};

const submit = () => {
    processing.value = true;
    router.post("/login", form.value, {
        onSuccess: () => {
            toast.success("Login successful! Welcome back.");
        },
        onFinish: () => {
            processing.value = false;
        },
        onError: (errors) => {
            processing.value = false;
            if (errors.lrn) {
                toast.error(errors.lrn);
            } else {
                toast.error("Login failed. Please check your credentials.");
            }
        },
    });
};

// Check for flash messages on mount
onMounted(() => {
    const flash = page.props.flash;
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
});
</script>

<style scoped>
.auth-container {
    position: relative;
    min-height: 100vh;
    background-image: url("/images/607055602_863940879900619_6210626938722324712_n.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 40, 100, 0.4);
    backdrop-filter: blur(3px);
}

.auth-content {
    position: relative;
    z-index: 1;
    padding: 1.5rem;
    width: 100%;
    max-width: 820px;
}

.auth-card {
    background: white;
    border: 1px solid #c5c5c5;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.28);
}

.card-bar {
    background: #003366;
    color: #fff;
    padding: 0.55rem 1rem;
    font-size: 0.92rem;
    font-weight: 600;
    border-bottom: 3px solid #c9a227;
}

.card-content {
    display: flex;
    gap: 1.75rem;
    align-items: stretch;
    padding: 1.4rem 1.5rem 1.25rem;
}

.auth-left {
    flex: 0 0 230px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding-right: 1.5rem;
    border-right: 1px solid #d8d8d8;
}

.auth-right {
    flex: 1;
    min-width: 0;
}

.logo {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    border: 2px solid #003366;
    margin: 0 auto 0.75rem auto;
    object-fit: cover;
    display: block;
}

.info-text {
    font-size: 0.95rem;
    font-weight: 700;
    color: #003366;
    margin: 0 0 0.25rem 0;
    text-align: center;
}

.info-text-small {
    font-size: 0.8rem;
    color: #555;
    line-height: 1.4;
    text-align: center;
    margin: 0;
}

.title {
    color: #003366;
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
}

.auth-form {
    margin-bottom: 0.85rem;
}

.form-group {
    margin-bottom: 0.85rem;
}

.form-group label {
    display: block;
    color: #333;
    font-weight: 600;
    margin-bottom: 0.3rem;
    font-size: 0.85rem;
}

.form-input {
    width: 100%;
    padding: 0.5rem 0.6rem;
    border: 1px solid #bdbdbd;
    border-radius: 2px;
    font-size: 0.9rem;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #003366;
}

.form-input.input-error {
    border-color: #dc3545;
}

.password-input-wrapper {
    position: relative;
}

.password-input {
    padding-right: 3.4rem;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 0.45rem;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.2rem;
    border: none;
    background: transparent;
    color: #555;
    cursor: pointer;
}

.password-toggle:hover {
    color: #003366;
}

.error-message {
    display: block;
    color: #dc3545;
    font-size: 0.8rem;
    margin-top: 0.2rem;
}

.form-group-checkbox {
    margin-bottom: 0.9rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 0.85rem;
    color: #555;
}

.checkbox {
    margin-right: 0.45rem;
    cursor: pointer;
}

.submit-btn {
    width: 100%;
    background: #003366;
    color: white;
    border: none;
    padding: 0.55rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.submit-btn:hover:not(:disabled) {
    background: #00264d;
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.auth-footer {
    text-align: left;
    color: #666;
    font-size: 0.85rem;
}

.auth-footer p {
    margin: 0 0 0.4rem 0;
}

.link {
    color: #003366;
    text-decoration: underline;
    font-weight: 600;
}

.link:hover {
    color: #00264d;
}

@media (max-width: 768px) {
    .auth-content {
        padding: 1rem;
        max-width: 100%;
    }

    .card-content {
        flex-direction: column;
        gap: 1.1rem;
        padding: 1.15rem 1.1rem 1.1rem;
    }

    .auth-left {
        flex: 1;
        width: 100%;
        padding-right: 0;
        border-right: none;
        padding-bottom: 1rem;
        border-bottom: 1px solid #d8d8d8;
    }

    .auth-right {
        width: 100%;
    }

    .logo {
        width: 72px;
        height: 72px;
    }
}

@media (max-width: 480px) {
    .auth-content {
        padding: 0.75rem;
    }

    .logo {
        width: 64px;
        height: 64px;
    }
}
</style>

