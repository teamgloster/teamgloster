<template>
    <TeacherLayout
        title="Subject Students"
        pageTitle="Subjects Handled"
        currentPage="subjects"
        :user="user"
    >
        <div class="content-section">
            <div class="page-banner">
                <div>
                    <h2>{{ subject.name }}</h2>
                    <span>
                        {{ subject.code || "No code" }}
                        <template v-if="subject.year_level?.name">
                            · {{ subject.year_level.name }}
                        </template>
                        · {{ filteredStudents.length }} student{{
                            filteredStudents.length !== 1 ? "s" : ""
                        }}
                    </span>
                </div>
                <Link
                    href="/dashboard/teacher?nav=subjects"
                    class="btn-secondary"
                >
                    Back to Subjects
                </Link>
            </div>

            <div class="toolbar">
                <div class="search-box">
                    <Search :size="16" class="search-icon" />
                    <input
                        v-model="search"
                        type="text"
                        class="search-input"
                        placeholder="Search students by name or LRN..."
                    />
                </div>
            </div>

            <div class="student-list" v-if="filteredStudents.length">
                <div
                    v-for="student in filteredStudents"
                    :key="student.id"
                    class="student-row"
                >
                    <div class="avatar">
                        <img
                            v-if="student.profile_photo"
                            :src="`/storage/${student.profile_photo}`"
                            alt=""
                        />
                        <span v-else>{{ initials(student) }}</span>
                    </div>
                    <div class="student-copy">
                        <strong>
                            {{ student.last_name }}, {{ student.first_name }}
                            {{
                                student.middle_name
                                    ? student.middle_name.charAt(0) + "."
                                    : ""
                            }}
                        </strong>
                        <small>
                            LRN {{ student.lrn || "N/A" }} ·
                            {{ student.section_name || "No section" }}
                        </small>
                    </div>
                    <button
                        type="button"
                        class="btn-icon"
                        title="View details"
                        @click="openDetails(student)"
                    >
                        <Eye :size="16" />
                    </button>
                </div>
            </div>
            <p v-else class="empty">
                {{
                    search
                        ? "No students match your search."
                        : "No students are enrolled in this subject."
                }}
            </p>
        </div>

        <Teleport to="body">
            <div
                v-if="selectedStudent"
                class="modal-overlay"
                @click.self="selectedStudent = null"
            >
                <div class="modal-card">
                    <div class="modal-head">
                        <h3>Student Information</h3>
                        <button
                            type="button"
                            class="close-btn"
                            @click="selectedStudent = null"
                        >
                            <X :size="18" />
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="detail-name">
                            {{ selectedStudent.last_name }},
                            {{ selectedStudent.first_name }}
                            {{ selectedStudent.middle_name }}
                        </p>
                        <div class="detail-grid">
                            <div>
                                <label>LRN</label>
                                <span>{{ selectedStudent.lrn || "—" }}</span>
                            </div>
                            <div>
                                <label>Section</label>
                                <span>{{
                                    selectedStudent.section_name || "—"
                                }}</span>
                            </div>
                            <div>
                                <label>Email</label>
                                <span>{{ selectedStudent.email || "—" }}</span>
                            </div>
                            <div>
                                <label>Contact</label>
                                <span>{{
                                    selectedStudent.phone_no || "—"
                                }}</span>
                            </div>
                            <div>
                                <label>Gender</label>
                                <span>{{
                                    selectedStudent.gender || "—"
                                }}</span>
                            </div>
                            <div>
                                <label>Guardian</label>
                                <span>{{
                                    selectedStudent.guardian_full_name || "—"
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </TeacherLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { Eye, Search, X } from "lucide-vue-next";
import TeacherLayout from "@/Layouts/TeacherLayout.vue";

const props = defineProps({
    user: { type: Object, required: true },
    subject: { type: Object, required: true },
    students: { type: Array, default: () => [] },
    currentSchoolYear: { type: String, default: "" },
});

const search = ref("");
const selectedStudent = ref(null);

const filteredStudents = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) {
        return props.students;
    }

    return props.students.filter((student) => {
        return (
            student.first_name?.toLowerCase().includes(term) ||
            student.last_name?.toLowerCase().includes(term) ||
            student.lrn?.toLowerCase().includes(term)
        );
    });
});

const initials = (student) =>
    (
        (student.first_name?.charAt(0) || "") +
        (student.last_name?.charAt(0) || "")
    ).toUpperCase();

const openDetails = (student) => {
    selectedStudent.value = student;
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.page-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    background: #003366;
    color: #fff;
    padding: 0.55rem 0.85rem;
    margin-bottom: 1rem;
    border-bottom: 3px solid #c9a227;
}

.page-banner h2 {
    margin: 0;
    font-size: 1rem;
    line-height: 1.25;
}

.page-banner span {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.85);
}

.page-banner .btn-secondary {
    background: #fff;
    color: #003366;
    text-decoration: none;
}

.toolbar {
    margin-bottom: 0.85rem;
}

.search-box {
    position: relative;
    max-width: 360px;
}

.search-icon {
    position: absolute;
    left: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.student-list {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.student-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.85rem;
    border-bottom: 1px solid #ececec;
}

.student-row:last-child {
    border-bottom: 0;
}

.avatar {
    width: 36px;
    height: 36px;
    background: #003366;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-copy {
    flex: 1;
    min-width: 0;
}

.student-copy strong {
    display: block;
    color: #222;
}

.student-copy small {
    color: #666;
}

.btn-icon {
    border: 1px solid #c5c5c5;
    background: #fff;
    color: #003366;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.empty {
    margin: 0;
    padding: 1rem;
    background: #fff;
    border: 1px solid #c5c5c5;
    color: #555;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 80;
    padding: 1rem;
}

.modal-card {
    width: min(560px, 100%);
    background: #fff;
    border: 1px solid #c5c5c5;
    border-radius: 0;
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #003366;
    color: #fff;
    padding: 0.55rem 0.85rem;
    border-bottom: 3px solid #c9a227;
}

.modal-head h3 {
    margin: 0;
    font-size: 1rem;
}

.close-btn {
    background: transparent;
    border: 0;
    color: #fff;
    cursor: pointer;
}

.modal-body {
    padding: 1rem;
}

.detail-name {
    margin: 0 0 0.85rem;
    font-weight: 700;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.detail-grid label {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    color: #666;
}

.detail-grid span {
    color: #222;
}
</style>
