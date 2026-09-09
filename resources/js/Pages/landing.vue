<template>
    <Head title="TNHS - Tambo National High School" />
    <div class="landing-container">
        <div class="overlay"></div>
        <div class="help-button">
            <button type="button" class="help-btn" @click="openHelp">
                Help
            </button>
        </div>
        <div
            v-if="helpOpen"
            class="help-overlay"
            @click.self="closeHelp"
        >
            <div
                class="help-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="help-title"
            >
                <div class="help-modal-top">
                    <h2 id="help-title">Help</h2>
                    <button
                        type="button"
                        class="help-modal-x"
                        aria-label="Close"
                        @click="closeHelp"
                    >
                        ×
                    </button>
                </div>
                <div class="help-modal-body">
                    <p>
                        For assistance, please contact the school administrator
                        or visit the school office.
                    </p>
                    <table class="help-table">
                        <tbody>
                            <tr>
                                <th>Email</th>
                                <td>
                                    <a href="mailto:admin@tnhs.edu.ph"
                                        >admin@tnhs.edu.ph</a
                                    >
                                </td>
                            </tr>
                            <tr>
                                <th>Office</th>
                                <td>
                                    Tambo National High School<br />
                                    Buhi, Camarines Sur
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="help-modal-actions">
                    <button
                        type="button"
                        class="help-modal-close"
                        @click="closeHelp"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="header">
                <img :src="logo" alt="TNHS Logo" class="logo" />
                <h1 class="school-name">Tambo National High School</h1>
                <p class="school-location">Buhi, Camarines Sur</p>
            </div>

            <div class="role-buttons">
                <button
                    type="button"
                    class="role-btn"
                    @click="navigateToLogin('administrator')"
                >
                    Administrator
                </button>
                <button
                    type="button"
                    class="role-btn"
                    @click="navigateToLogin('teacher')"
                >
                    Teacher
                </button>
                <button
                    type="button"
                    class="role-btn"
                    @click="navigateToLogin('registrar')"
                >
                    Registrar
                </button>
                <button
                    type="button"
                    class="role-btn"
                    @click="navigateToLogin('student')"
                >
                    Student
                </button>
                <button
                    type="button"
                    class="role-btn admission-btn"
                    @click="navigateToAdmission"
                >
                    Apply for Admission
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { router, Head, usePage } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";

const toast = useToast();
const page = usePage();
const helpOpen = ref(false);

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";

const navigateToLogin = (role) => {
    router.visit(`/login/${role}`);
};

const navigateToAdmission = () => {
    router.visit("/admission/apply");
};

const openHelp = () => {
    helpOpen.value = true;
};

const closeHelp = () => {
    helpOpen.value = false;
};

const onEscape = (event) => {
    if (event.key === "Escape" && helpOpen.value) {
        closeHelp();
    }
};

onMounted(() => {
    window.addEventListener("keydown", onEscape);

    const flash = page.props.flash;
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
});

onUnmounted(() => {
    window.removeEventListener("keydown", onEscape);
});
</script>

<style scoped>
.landing-container {
    position: relative;
    min-height: 100vh;
    background-image: url("/images/607055602_863940879900619_6210626938722324712_n.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
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

.content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem;
    max-width: 600px;
    width: 100%;
}

.header {
    margin-bottom: 3rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    margin: 0 auto 1.5rem auto;
    object-fit: cover;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    display: block;
}

.school-name {
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0;
    margin-bottom: 0.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.school-location {
    color: white;
    font-size: 1.2rem;
    margin: 0;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
}

.role-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    align-items: center;
}

.role-btn {
    width: 250px;
    background: #fff;
    color: #003366;
    border: 1px solid #c8c8c8;
    border-radius: 2px;
    padding: 0.7rem 1rem;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
}

.role-btn:hover {
    background: #eef2f6;
    border-color: #b0b0b0;
}

.admission-btn {
    margin-top: 0.55rem;
    background: #003366;
    color: #fff;
    border-color: #003366;
}

.admission-btn:hover {
    background: #00264d;
    border-color: #00264d;
}

.help-button {
    position: absolute;
    top: 1.5rem;
    right: 2rem;
    z-index: 2;
}

.help-btn {
    background: transparent;
    color: white;
    border: 2px solid white;
    padding: 0.6rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.help-btn:hover {
    background: white;
    color: #003366;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.help-overlay {
    position: fixed;
    inset: 0;
    z-index: 20;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 8rem 1rem 2rem;
}

.help-modal {
    width: 100%;
    max-width: 430px;
    background: #fff;
    border: 1px solid #c5c5c5;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
}

.help-modal-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #003366;
    color: #fff;
    padding: 0.55rem 0.85rem;
}

.help-modal-top h2 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
}

.help-modal-x {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.35rem;
    line-height: 1;
    cursor: pointer;
    padding: 0 0.15rem;
}

.help-modal-x:hover {
    color: #ddd;
}

.help-modal-body {
    padding: 1rem 1.1rem 0.4rem;
    color: #222;
    font-size: 0.9rem;
    line-height: 1.5;
}

.help-modal-body p {
    margin: 0 0 0.85rem;
}

.help-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0.35rem;
}

.help-table th,
.help-table td {
    border-top: 1px solid #e4e4e4;
    padding: 0.45rem 0;
    text-align: left;
    vertical-align: top;
    font-size: 0.88rem;
}

.help-table th {
    width: 70px;
    color: #555;
    font-weight: 600;
    padding-right: 0.75rem;
}

.help-table a {
    color: #003366;
    text-decoration: none;
    font-weight: 600;
}

.help-table a:hover {
    text-decoration: underline;
}

.help-modal-actions {
    padding: 0.65rem 1.1rem 0.95rem;
    text-align: right;
    border-top: 1px solid #e8e8e8;
}

.help-modal-close {
    background: #003366;
    color: #fff;
    border: none;
    padding: 0.4rem 1.15rem;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.help-modal-close:hover {
    background: #00264d;
}

@media (max-width: 768px) {
    .help-button {
        top: 1rem;
        right: 1rem;
    }

    .help-btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .help-overlay {
        padding-top: 5.5rem;
    }

    .school-name {
        font-size: 2rem;
    }

    .school-location {
        font-size: 1rem;
    }

    .logo {
        width: 120px;
        height: 120px;
    }

    .role-btn {
        width: 210px;
    }
}
</style>
