import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const TEACHER_PAGES = [
    {
        label: "School Forms",
        href: "/teacher/school-forms",
        names: [
            "school forms",
            "school form",
            "report card",
            "permanent record",
            "papel ng eskwela",
            "mga papel",
            "sf 10",
            "sf 9",
            "sf 2",
            "sf 1",
            "sf10",
            "sf9",
            "sf2",
            "sf1",
            "form 10",
            "form 9",
            "form 2",
            "form 1",
        ],
    },
    {
        label: "Change Password",
        href: "/teacher/password",
        names: ["change my password", "change password", "bagong password", "password"],
    },
    {
        label: "Attendance",
        href: "/teacher/attendance",
        names: ["attendance", "atendance", "attendence", "pagdalo"],
    },
    {
        label: "Student Grades",
        href: "/dashboard/teacher?nav=grades",
        names: ["student grades", "mga marka", "grades", "grade", "marka"],
    },
    {
        label: "Advisory Class",
        href: "/dashboard/teacher?nav=advisory",
        names: [
            "advisory class",
            "advisor class",
            "adviser class",
            "advisery class",
            "advisory",
            "advisor",
            "adviser",
            "advisery",
        ],
    },
    {
        label: "Subjects Handled",
        href: "/dashboard/teacher?nav=subjects",
        names: [
            "subjects handled",
            "subject handled",
            "mga asignatura",
            "subjects",
            "subject",
            "asignatura",
        ],
    },
    {
        label: "Dashboard",
        href: "/dashboard/teacher?nav=dashboard",
        names: ["teacher dashboard", "home page", "unang pahina", "dashboard", "home"],
    },
];

let showTeacherSection = null;

export function registerTeacherSection(handler) {
    showTeacherSection = handler;
    return () => {
        if (showTeacherSection === handler) showTeacherSection = null;
    };
}

export function goToTeacherPage(page) {
    let nav = null;
    try {
        nav = new URL(page.href, window.location.origin).searchParams.get("nav");
    } catch (e) {
        nav = null;
    }
    if (nav && showTeacherSection) {
        showTeacherSection(nav);
    }
    const here = `${window.location.pathname}${window.location.search}`;
    if (here === page.href) return;
    router.visit(page.href, {
        preserveState: Boolean(nav && showTeacherSection),
        preserveScroll: true,
    });
}

const ADMIN_PAGES = [
    { label: "Enrollment Summary", href: "/admin/enrollment-summary", names: ["enrollment summary", "summary"] },
    { label: "Teacher Assignments", href: "/admin/teacher-assignments", names: ["teacher assignments", "teacher assignment", "assignments"] },
    { label: "Student Requirements", href: "/admin/requirements", names: ["student requirements", "requirements", "requirement"] },
    { label: "Academic Tracks", href: "/admin/strands", names: ["academic tracks", "academic track", "strands", "strand", "tracks"] },
    { label: "School Forms", href: "/admin/school-forms", names: ["school forms", "school form", "sf10", "sf9", "sf2", "sf1", "form 10", "form 9", "form 2", "form 1"] },
    { label: "Year Levels", href: "/admin/year-levels", names: ["year levels", "year level", "baitang"] },
    { label: "Admissions", href: "/admin/admissions", names: ["admissions", "admission", "pagpasok"] },
    { label: "Enrollments", href: "/admin/enrollments", names: ["enrollments", "enrollment"] },
    { label: "Settings", href: "/admin/settings", names: ["settings", "setting", "change password", "password"] },
    { label: "Sections", href: "/admin/sections", names: ["sections", "section"] },
    { label: "Subjects", href: "/admin/subjects", names: ["subjects", "subject"] },
    { label: "Students", href: "/admin/students", names: ["students", "student"] },
    { label: "Teachers", href: "/admin/teachers", names: ["teachers", "teacher"] },
    { label: "Accounts", href: "/admin/accounts", names: ["accounts", "account"] },
    { label: "Dashboard", href: "/admin", names: ["dashboard", "home page", "home", "unang pahina"] },
];

const STUDENT_PAGES = [
    { label: "Enrolled Subjects", href: "/student/subjects", names: ["enrolled subjects", "my subjects", "subjects", "subject"] },
    { label: "Student Profile", href: "/student/profile", names: ["student profile", "my profile", "profile"] },
    { label: "Change Password", href: "/student/password", names: ["change password", "change my password", "bagong password", "password"] },
    { label: "Requirements", href: "/student/requirements", names: ["requirements", "requirement", "mga kailangan"] },
    { label: "Report Card", href: "/student/sf9", names: ["report card", "sf 9", "sf9", "form 9"] },
    { label: "Permanent Record", href: "/student/sf10", names: ["permanent record", "sf 10", "sf10", "form 10"] },
    { label: "Attendance", href: "/student/attendance", names: ["attendance", "atendance", "pagdalo"] },
    { label: "Enrollment", href: "/student/enrollment", names: ["enrollment", "enrolment"] },
    { label: "Grades", href: "/student/grades", names: ["grades", "grade", "mga marka", "marka"] },
    { label: "Dashboard", href: "/student", names: ["dashboard", "home page", "home", "unang pahina"] },
];

const REGISTRAR_PAGES = [
    { label: "Enrollment Summary", href: "/registrar/enrollment-summary", names: ["enrollment summary", "summary"] },
    { label: "Grading Records", href: "/registrar/grades", names: ["grading records", "grading record", "grades", "grade"] },
    { label: "SP-10 Records", href: "/registrar/permanent-records", names: ["sp 10 records", "sp 10", "sp10", "permanent records", "permanent record"] },
    { label: "Change Password", href: "/registrar/password", names: ["change password", "change my password", "bagong password", "password"] },
    { label: "Year Levels", href: "/registrar/year-levels", names: ["year levels", "year level", "baitang"] },
    { label: "Sections", href: "/registrar/sections", names: ["sections", "section"] },
    { label: "Students", href: "/registrar/students", names: ["students", "student"] },
    { label: "Dashboard", href: "/registrar", names: ["dashboard", "home page", "home", "unang pahina"] },
];

const PORTAL_PAGES = {
    teacher: TEACHER_PAGES,
    administrator: ADMIN_PAGES,
    student: STUDENT_PAGES,
    registrar: REGISTRAR_PAGES,
};

function pageNamesFor(role) {
    const pages = PORTAL_PAGES[role] || TEACHER_PAGES;
    return pages
        .flatMap((page) => page.names.map((name) => ({ name, page })))
        .sort((a, b) => b.name.length - a.name.length);
}

function normalizeCommand(text) {
    return text
        .toLowerCase()
        .replace(/-/g, " ")
        .replace(/[^a-z0-9\s]/g, " ")
        .replace(/\s+/g, " ")
        .trim()
        .replace(/\badvis\w*/g, "advisory")
        .replace(/\bat+end\w*/g, "attendance");
}

function mentions(spoken, name) {
    const escaped = name.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    return new RegExp(`(^|\\s)${escaped}(\\s|$)`).test(spoken);
}

export function portalPageFor(role, text) {
    const spoken = normalizeCommand(text);
    if (!spoken) return null;
    const match = pageNamesFor(role).find((item) => mentions(spoken, item.name));
    if (!match) return null;
    const opening =
        /\b(open|go to|goto|take me to|bring me to|switch to|launch|buksan|i open|pumunta|puntahan|ipakita)\b/.test(
            spoken,
        ) || /\b(go home|take me home)\b/.test(spoken);
    const onlyName =
        spoken === match.name ||
        spoken === `the ${match.name}` ||
        spoken === `yung ${match.name}` ||
        spoken === `ang ${match.name}`;
    if (!opening && !onlyName) return null;
    return match.page;
}

export function teacherPageFor(text) {
    return portalPageFor("teacher", text);
}

export function commandAsksSomething(text) {
    return /\b(how|what|why|when|where|who|which|can you|paano|ano|bakit|saan|pwede)\b/i.test(
        text,
    );
}

const open = ref(false);
const messages = ref([]);
const voiceReplies = ref(true);
const language = ref("en");

try {
    voiceReplies.value = localStorage.getItem("tnhs-teacher-assistant-voice") !== "0";
    const savedLanguage = localStorage.getItem("tnhs-teacher-assistant-lang");
    if (savedLanguage === "tl" || savedLanguage === "en") language.value = savedLanguage;
} catch (e) {}

export function useTeacherAssistant() {
    function setVoiceReplies(next) {
        voiceReplies.value = Boolean(next);
        try {
            localStorage.setItem("tnhs-teacher-assistant-voice", next ? "1" : "0");
        } catch (e) {}
    }

    function setLanguage(next) {
        language.value = next === "tl" ? "tl" : "en";
        try {
            localStorage.setItem("tnhs-teacher-assistant-lang", language.value);
        } catch (e) {}
    }

    return {
        open,
        messages,
        voiceReplies,
        setVoiceReplies,
        language,
        setLanguage,
    };
}
