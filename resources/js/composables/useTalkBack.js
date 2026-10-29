import { onMounted, onUnmounted, ref } from "vue";
import { csrfHeaders } from "@/csrf";

const STORAGE_KEY = "tnhs-talkback";
const LANG_KEY = "tnhs-talkback-lang";
const LEGACY_STORAGE_KEY = "tnhs-teacher-talkback";
const LEGACY_LANG_KEY = "tnhs-teacher-talkback-lang";

const enabled = ref(false);
const language = ref("en");
const paused = ref(false);
const liveMessage = ref("");

let mounts = 0;
let listenersOn = false;
let suspended = false;
let pendingIntro = false;
let pageSpeech = "";
let lastAnnouncedPage = "";
let lastSpoken = "";
let lastSpokenAt = 0;
let lastPointerEl = null;
let highlighted = null;
let hovered = null;
let hoverTimer = null;
let speakTimer = null;
let introTimer = null;
let pageTimer = null;
let cachedVoices = [];
let speechAbort = null;
let spokenWanted = "";
let playbackStarted = false;
let speechAudio = null;
let speechObjectUrl = null;
let speechGeneration = 0;
let speechErrorToasted = false;
let pcmReader = null;
let pcmSources = [];
let audioCtx = null;
let gestureUnlocked = false;
const audioWaiters = [];
const speechCache = new Map();
const PCM_RATE = 22050;

const SPEAKABLE =
    "button, a, input, select, textarea, summary, label, h1, h2, h3, [role='button'], tbody tr, [data-talkback]";

const SPOKEN_FIXES = [
    [/\bSP\s*-?\s*10\b/gi, "school form 10"],
    [/\bSF\s*10\b/gi, "school form 10"],
    [/\bSF\s*9\b/gi, "school form 9"],
    [/\bSF\s*2\b/gi, "school form 2"],
    [/\bSF\s*1\b/gi, "school form 1"],
    [/\bLRN\b/g, "L R N"],
];

const ENGLISH_NAME_FIXES = [
    [/\bJorge\b/gi, "George"],
    [/\bJuanito\b/gi, "Joo ah nee toe"],
    [/\bJuan\b/gi, "Joo ahn"],
    [/\bJose\b/gi, "Joe say"],
    [/\bJavier\b/gi, "Jah vee air"],
    [/\bJulio\b/gi, "Jool yo"],
    [/\bJaime\b/gi, "Jay mee"],
    [/\bJoaquin\b/gi, "Joa keen"],
    [/\bJimenez\b/gi, "Jee meh nez"],
    [/\bJuarez\b/gi, "Joo ah rez"],
];

const SHARED_GUIDES = {
    password:
        "Type your current password, then the new password two times, and press Update password.",
    "school-forms": "Choose a form to open or print it.",
};

const ROLE_GUIDES = {
    teacher: {
        dashboard:
            "This is your home page. The menu on the left opens your classes, grades, attendance, and school forms.",
        advisory:
            "This is your advisory class. Here are the students in your section.",
        subjects:
            "These are the students in this subject. Use the search box to find a name.",
        grades:
            "This is where you enter grades. Choose a class, then type a grade, or press Input grades by voice.",
        attendance:
            "Choose the class and the date, mark each student, then press Save attendance.",
    },
    administrator: {
        dashboard:
            "This is the administrator home page. The menu on the left opens students, teachers, and school records.",
        students: "This is the student list. Use the search box to find a name.",
        admissions:
            "These are the admission applications. Open one to review it.",
        enrollments: "These are the enrollment records.",
        requirements: "These are the student requirements.",
        "enrollment-summary": "This is the enrollment summary.",
        teachers: "This is the teacher list.",
        "teacher-assignments":
            "This is where teachers are assigned to classes.",
        "year-levels": "These are the year levels.",
        strands: "These are the strands.",
        sections: "These are the sections.",
        subjects: "These are the subjects.",
        accounts: "These are the staff accounts.",
        settings: "These are the school settings.",
    },
    student: {
        dashboard:
            "This is your home page. The menu on the left opens your profile, grades, attendance, and subjects.",
        profile: "This is your profile.",
        enrollment: "This is your enrollment.",
        requirements: "These are your requirements.",
        subjects: "These are your subjects.",
        grades: "These are your grades.",
        attendance: "This is your attendance.",
    },
    registrar: {
        dashboard:
            "This is the registrar home page. The menu on the left opens records, sections, and grades.",
        "year-levels": "These are the year levels.",
        sections: "These are the sections.",
        grades: "These are the student grades.",
        students: "This is the student list. Use the search box to find a name.",
        "permanent-records": "These are the permanent records.",
        "enrollment-summary": "This is the enrollment summary.",
    },
};

const TAGALOG_PHRASES = [
    ["The reading voice is not available right now. Try again in a moment.", "Hindi magamit yung boses ngayon. Subukan mo ulit mamaya."],
    ["Talk back is on. Point at a button or a box, and I will read it.", "Naka-on ang tok bak. Ituro mo yung pindutan o kahon, babasahin ko."],
    ["Talk back is on. Click to turn the reading voice off.", "Naka-on ang tok bak. Pindutin mo para patayin yung boses."],
    ["Talk back is off. Click to hear this page read aloud.", "Naka-off ang tok bak. Pindutin mo para marinig yung pahina."],
    ["This is your home page. The menu on the left opens your classes, grades, attendance, and school forms.", "Ito yung unang pahina mo. Sa kaliwa yung mga klase, marka, pagdalo, at mga papel ng eskwela."],
    ["This is your advisory class. Here are the students in your section.", "Ito yung klaseng advisory mo. Nandito yung mga estudyante sa seksyon mo."],
    ["These are the subjects you teach. Open a subject to see its students.", "Ito yung mga aralin na tinuturo mo. Buksan mo yung aralin para makita yung mga estudyante."],
    ["These are the students in this subject. Use the search box to find a name.", "Ito yung mga estudyante sa araling ito. Hanapin mo yung pangalan sa paghahanap."],
    ["This is where you enter grades. Choose a class, then type a grade, or press Input grades by voice.", "Dito mo ilalagay yung mga marka. Pumili ka ng klase, i-type mo yung marka, o pindutin mo yung Ilagay ang marka gamit ang boses."],
    ["Choose the class and the date, mark each student, then press Save attendance.", "Piliin mo yung klase at petsa, markahan mo bawat estudyante, tapos pindutin mo yung I-save ang pagdalo."],
    ["Choose a form to open or print it.", "Pumili ka ng papel para buksan o ilimbag."],
    ["Type your current password, then the new password two times, and press Update password.", "I-type mo yung password mo ngayon, tapos yung bagong password nang dalawang beses, tapos pindutin mo yung Palitan ang password."],
    ["This is a preview. Press Back to return, or Print to save a copy.", "Tingin pa lang ito. Pindutin mo yung Balik para bumalik, o ilimbag mo para may kopya."],
    ["Use the menu on the left to open another page.", "Gamitin mo yung listahan sa kaliwa para lumipat ng pahina."],
    ["Your name. Click to open the menu.", "Pangalan mo. Pindutin mo para buksan yung listahan."],
    ["Choose a term first. Then you can say the grades out loud.", "Pumili ka muna ng markahan. Tapos pwede mo nang sabihin yung mga marka."],
    ["Stop voice input. Click to turn the microphone off.", "Tigil muna yung salita sa mikropono. Pindutin mo para patayin."],
    ["Say grades out loud. Click to turn the microphone on.", "Sabihin mo yung mga marka. Pindutin mo para buksan yung mikropono."],
    ["Only names & grades are accepted", "Pangalan at marka lang ang tatanggapin"],
    ["No students match your search.", "Walang estudyanteng tumugma sa hinanap mo."],
    ["No contact info", "Walang numero o tirahan"],
    ["Open SF9 report card", "Buksan yung kard ng marka"],
    ["Open SP-10 permanent record", "Buksan yung lumang tala"],
    ["Select a term first", "Pumili ka muna ng markahan"],
    ["The system is listening", "Nakikinig na"],
    ["Wait for confirmation", "Hintayin mo muna yung sagot"],
    ["Search by name or LRN", "Hanapin mo sa pangalan o el ar en"],
    ["No classes assigned", "Wala pang klaseng hawak"],
    ["Student Information", "Tungkol sa estudyante"],
    ["Assignment Summary", "Buod ng hawak mo"],
    ["Talk back will start after you stop voice input.", "Magsisimula yung tok bak pagkatapos mong itigil yung mikropono."],
    ["Talk back is off.", "Naka-off ang tok bak."],
    ["Type here. The letters stay hidden.", "Mag-type ka dito. Nakatago yung mga letra."],
    ["Not available right now.", "Hindi pa puwede ngayon."],
    ["Say the grade", "Sabihin mo yung marka"],
    ["Say the name", "Sabihin mo yung pangalan"],
    ["Not saved yet", "Hindi pa naka-save"],
    ["No grade yet.", "Wala pang marka."],
    ["Choose from the list.", "Pumili ka sa listahan."],
    ["Choose a date.", "Pumili ka ng petsa."],
    ["Not checked.", "Hindi pa naka-tsek."],
    ["Type here.", "Mag-type ka dito."],
    ["Confirm New Password", "Ulitin yung bagong password"],
    ["Input Grades via Voice", "Ilagay ang marka gamit ang boses"],
    ["Print / Save PDF", "Ilimbag o i-save bilang PDF"],
    ["Advisory students", "Mga estudyante sa advisory"],
    ["Assigned subjects", "Mga araling hawak"],
    ["Sections handled", "Mga seksyong hawak"],
    ["Pending grades", "Mga markang hindi pa tapos"],
    ["Teacher Dashboard", "Unang pahina ng guro"],
    ["Teacher Portal", "Pahina ng guro"],
    ["Change Password", "Palitan ang password"],
    ["Current Password", "Password mo ngayon"],
    ["Student Grades", "Mga marka ng estudyante"],
    ["Subjects Handled", "Mga araling hawak"],
    ["Stop Voice Input", "Tigilan yung mikropono"],
    ["Contact Info", "Numero o tirahan"],
    ["Student Name", "Pangalan ng estudyante"],
    ["Grade Records", "Listahan ng mga marka"],
    ["Class Roster", "Listahan ng klase"],
    ["Save attendance", "I-save ang pagdalo"],
    ["Update Password", "Palitan ang password"],
    ["National High School", "Pambansang Hayskul"],
    ["High School", "Hayskul"],
    ["School Year", "Taon ng eskwela"],
    ["All Subjects", "Lahat ng aralin"],
    ["All Sections", "Lahat ng seksyon"],
    ["All Gender", "Lahat ng kasarian"],
    ["Select Term", "Pumili ng markahan"],
    ["Search students", "Hanapin ang estudyante"],
    ["School Forms", "Mga papel ng eskwela"],
    ["Advisory Class", "Klaseng advisory"],
    ["New Password", "Bagong password"],
    ["Final grade", "Huling marka"],
    ["Mark all", "Markahan lahat"],
    ["Close menu", "Isara yung listahan"],
    ["Open menu", "Buksan yung listahan"],
    ["Edit Term", "Baguhin yung markahan"],
    ["Go to", "Pumunta sa"],
    ["Term 1", "Unang markahan"],
    ["Term 2", "Ikalawang markahan"],
    ["Term 3", "Ikatlong markahan"],
    ["Currently", "Kasalukuyang"],
    ["Checked", "Naka-tsek"],
    ["Actions", "Mga aksyon"],
    ["Students", "Mga estudyante"],
    ["Student", "Estudyante"],
    ["Sections", "Mga seksyon"],
    ["Section", "Seksyon"],
    ["Subjects", "Mga asignatura"],
    ["Subject", "Asignatura"],
    ["Teachers", "Mga guro"],
    ["Teacher", "Guro"],
    ["Log out", "Labas na"],
    ["Logout", "Labas na"],
    ["This is the administrator home page. The menu on the left opens students, teachers, and school records.", "Ito yung unang pahina ng admin. Sa kaliwa yung mga estudyante, guro, at tala ng eskwela."],
    ["This is your home page. The menu on the left opens your profile, grades, attendance, and subjects.", "Ito yung unang pahina mo. Sa kaliwa yung profile mo, mga marka, pagdalo, at mga asignatura."],
    ["This is the registrar home page. The menu on the left opens records, sections, and grades.", "Ito yung unang pahina ng rehistrador. Sa kaliwa yung mga tala, seksyon, at marka."],
    ["These are the admission applications. Open one to review it.", "Ito yung mga application sa pagpasok. Buksan mo yung isa para tingnan."],
    ["This is where teachers are assigned to classes.", "Dito mo ilalagay kung sinong guro ang sa bawat klase."],
    ["This is the student list. Use the search box to find a name.", "Ito yung listahan ng estudyante. Hanapin mo yung pangalan sa paghahanap."],
    ["These are the permanent records.", "Ito yung mga lumang tala."],
    ["These are the student requirements.", "Ito yung mga kailangan ng estudyante."],
    ["These are the enrollment records.", "Ito yung mga tala ng pag-enroll."],
    ["This is the enrollment summary.", "Ito yung buod ng pag-enroll."],
    ["These are the student grades.", "Ito yung mga marka ng estudyante."],
    ["These are the staff accounts.", "Ito yung mga account ng staff."],
    ["These are the school settings.", "Ito yung mga setting ng eskwela."],
    ["These are your requirements.", "Ito yung mga kailangan mo."],
    ["This is the teacher list.", "Ito yung listahan ng guro."],
    ["These are the year levels.", "Ito yung mga baitang."],
    ["This is your attendance.", "Ito yung pagdalo mo."],
    ["This is your enrollment.", "Ito yung pag-enroll mo."],
    ["These are your subjects.", "Ito yung mga asignatura mo."],
    ["These are the subjects.", "Ito yung mga asignatura."],
    ["These are your grades.", "Ito yung mga marka mo."],
    ["These are the sections.", "Ito yung mga seksyon."],
    ["These are the strands.", "Ito yung mga strand."],
    ["This is your profile.", "Ito yung profile mo."],
].sort((a, b) => b[0].length - a[0].length);

const TAGALOG_WORDS = [
    ["Talk Back", "tok bak"],
    ["Dashboard", "unang pahina"],
    ["Attendance", "pagdalo"],
    ["Mathematics", "Matematika"],
    ["Science", "Agham"],
    ["English", "Ingles"],
    ["Students", "mga estudyante"],
    ["Student", "estudyante"],
    ["Subjects", "mga aralin"],
    ["Subject", "aralin"],
    ["Sections", "mga seksyon"],
    ["Section", "seksyon"],
    ["Teachers", "mga guro"],
    ["Teacher", "guro"],
    ["Grades", "mga marka"],
    ["Grade", "baitang"],
    ["Records", "mga tala"],
    ["Present", "pumasok"],
    ["Absent", "hindi pumasok"],
    ["Excused", "may dahilan"],
    ["Female", "babae"],
    ["Gender", "kasarian"],
    ["Guardian", "magulang"],
    ["Remarks", "komento"],
    ["Status", "lagay"],
    ["Optional", "puwedeng wala"],
    ["Classes", "mga klase"],
    ["Class", "klase"],
    ["Search", "hanap"],
    ["Saving", "sine-save"],
    ["Language", "wika"],
    ["Pending", "hindi pa tapos"],
    ["Assigned", "hawak"],
    ["Summary", "buod"],
    ["Actions", "mga aksyon"],
    ["Action", "aksyon"],
    ["Contact", "numero"],
    ["Portal", "pahina"],
    ["Roster", "listahan"],
    ["School", "eskwela"],
    ["Close", "isara"],
    ["Print", "ilimbag"],
    ["Save", "i-save"],
    ["Edit", "baguhin"],
    ["View", "tingnan"],
    ["Back", "balik"],
    ["Open", "buksan"],
    ["Male", "lalaki"],
    ["Late", "nahuli"],
    ["Date", "petsa"],
    ["Year", "taon"],
    ["List", "listahan"],
    ["Heard", "narinig"],
    ["Final", "huli"],
    ["none", "wala"],
    ["Main", "una"],
    ["Account", "account"],
    ["Permanent Records", "mga lumang tala"],
    ["Enrollment Summary", "buod ng pag-enroll"],
    ["Teacher Assignments", "mga guro sa klase"],
    ["Administrator", "admin"],
    ["Year Levels", "mga baitang"],
    ["Requirements", "mga kailangan"],
    ["Admissions", "pagpasok"],
    ["Enrollments", "pag-enroll"],
    ["Requirement", "kailangan"],
    ["Enrollment", "pag-enroll"],
    ["Registrar", "rehistrador"],
    ["Year Level", "baitang"],
    ["Settings", "mga setting"],
    ["Accounts", "mga account"],
    ["Strands", "mga strand"],
    ["Profile", "profile"],
    ["Strand", "strand"],
].sort((a, b) => b[0].length - a[0].length);

const TAGLISH_WORDS = [
    ["Talk Back", "tok bak"],
    ["LRN", "el ar en"],
    ["Saving", "nagsa-save"],
    ["Search", "i-search"],
    ["Print", "i-print"],
    ["Click", "i-click"],
    ["Save", "i-save"],
    ["Type", "i-type"],
    ["Update", "i-update"],
    ["Upload", "i-upload"],
    ["Download", "i-download"],
    ["Delete", "i-delete"],
    ["Cancel", "i-cancel"],
    ["Submit", "i-submit"],
    ["Refresh", "i-refresh"],
    ["Loading", "naglo-load"],
    ["Checked", "naka-check"],
    ["Log out", "mag-logout"],
    ["Logout", "mag-logout"],
    ["none", "wala"],
].sort((a, b) => b[0].length - a[0].length);

const TAGLISH_PHRASES = [
    ["The reading voice is not available right now. Try again in a moment.", "Hindi available yung boses ngayon. Try mo ulit mamaya."],
    ["Talk back is on. Point at a button or a box, and I will read it.", "Naka-on ang tok bak. Ituro mo yung button o box, babasahin ko."],
    ["Talk back is on. Click to turn the reading voice off.", "Naka-on ang tok bak. I-click mo para patayin yung boses."],
    ["Talk back is off. Click to hear this page read aloud.", "Naka-off ang tok bak. I-click mo para marinig yung page."],
    ["This is your home page. The menu on the left opens your classes, grades, attendance, and school forms.", "Ito yung home page mo. Yung menu sa left, andiyan yung classes, grades, attendance, at school forms."],
    ["This is your advisory class. Here are the students in your section.", "Ito yung advisory class mo. Nandito yung students sa section mo."],
    ["These are the subjects you teach. Open a subject to see its students.", "Ito yung subjects na tinuturo mo. Buksan mo yung subject para makita yung students."],
    ["These are the students in this subject. Use the search box to find a name.", "Ito yung students sa subject na ito. I-search mo yung name."],
    ["This is where you enter grades. Choose a class, then type a grade, or press Input grades by voice.", "Dito ilalagay yung grades. Pumili ka ng class, i-type mo yung grade, o pindutin mo yung Input Grades via Voice."],
    ["Choose the class and the date, mark each student, then press Save attendance.", "Piliin mo yung class at date, markahan mo bawat student, tapos i-save mo yung attendance."],
    ["Choose a form to open or print it.", "Pumili ka ng form para buksan o i-print."],
    ["Type your current password, then the new password two times, and press Update password.", "I-type mo yung current password, tapos yung new password nang dalawang beses, tapos i-update mo."],
    ["This is a preview. Press Back to return, or Print to save a copy.", "Preview lang ito. Pindutin mo yung Back para bumalik, o i-print mo para may copy."],
    ["Use the menu on the left to open another page.", "Gamitin mo yung menu sa left para lumipat ng page."],
    ["Your name. Click to open the menu.", "Pangalan mo. I-click mo para buksan yung menu."],
    ["Choose a term first. Then you can say the grades out loud.", "Pumili ka muna ng term. Tapos pwede mo nang sabihin yung grades."],
    ["Stop voice input. Click to turn the microphone off.", "Stop muna yung voice input. I-click mo para patayin yung microphone."],
    ["Say grades out loud. Click to turn the microphone on.", "Sabihin mo yung grades. I-click mo para buksan yung microphone."],
    ["Only names & grades are accepted", "Names at grades lang ang tinatanggap"],
    ["No students match your search.", "Walang student na tumugma sa search."],
    ["No contact info", "Walang contact info"],
    ["Open SF9 report card", "Buksan yung SF9 report card"],
    ["Open SP-10 permanent record", "Buksan yung SP10 permanent record"],
    ["Select a term first", "Pumili ka muna ng term"],
    ["The system is listening", "Nakikinig na yung system"],
    ["Wait for confirmation", "Hintayin mo yung confirmation"],
    ["Search by name or LRN", "I-search mo sa name o el ar en"],
    ["No classes assigned", "Walang naka-assign na class"],
    ["Not saved yet", "Hindi pa naka-save"],
    ["Save attendance", "I-save ang attendance"],
    ["Search students", "I-search ang students"],
    ["No grade yet.", "Wala pang grade."],
    ["Not available right now.", "Hindi available ngayon."],
    ["Not checked.", "Hindi naka-check."],
    ["Type here. The letters stay hidden.", "Mag-type ka dito. Nakatago yung letters."],
    ["Choose from the list.", "Pumili ka sa list."],
    ["Choose a date.", "Pumili ka ng date."],
    ["Type here.", "Mag-type ka dito."],
    ["Say the grade", "Sabihin mo yung grade"],
    ["Say the name", "Sabihin mo yung name"],
    ["Talk back will start after you stop voice input.", "Magsisimula yung tok bak pagkatapos mong i-stop yung voice input."],
    ["Talk back is off.", "Naka-off ang tok bak."],
    ["This is the administrator home page. The menu on the left opens students, teachers, and school records.", "Ito yung administrator home page. Yung menu sa left, andiyan yung students, teachers, at school records."],
    ["This is your home page. The menu on the left opens your profile, grades, attendance, and subjects.", "Ito yung home page mo. Yung menu sa left, andiyan yung profile, grades, attendance, at subjects mo."],
    ["This is the registrar home page. The menu on the left opens records, sections, and grades.", "Ito yung registrar home page. Yung menu sa left, andiyan yung records, sections, at grades."],
    ["These are the admission applications. Open one to review it.", "Ito yung admission applications. Buksan mo yung isa para i-review."],
    ["This is where teachers are assigned to classes.", "Dito mo i-assign yung teachers sa classes."],
    ["This is the student list. Use the search box to find a name.", "Ito yung student list. I-search mo yung name."],
    ["These are the permanent records.", "Ito yung permanent records."],
    ["These are the student requirements.", "Ito yung student requirements."],
    ["These are the enrollment records.", "Ito yung enrollment records."],
    ["This is the enrollment summary.", "Ito yung enrollment summary."],
    ["These are the student grades.", "Ito yung student grades."],
    ["These are the staff accounts.", "Ito yung staff accounts."],
    ["These are the school settings.", "Ito yung school settings."],
    ["These are your requirements.", "Ito yung requirements mo."],
    ["This is the teacher list.", "Ito yung teacher list."],
    ["These are the year levels.", "Ito yung year levels."],
    ["This is your attendance.", "Ito yung attendance mo."],
    ["This is your enrollment.", "Ito yung enrollment mo."],
    ["These are your subjects.", "Ito yung subjects mo."],
    ["These are the subjects.", "Ito yung subjects."],
    ["These are your grades.", "Ito yung grades mo."],
    ["These are the sections.", "Ito yung sections."],
    ["These are the strands.", "Ito yung strands."],
    ["This is your profile.", "Ito yung profile mo."],
].sort((a, b) => b[0].length - a[0].length);

function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function applyPhrases(text, phrases) {
    let spoken = text;
    for (const [english, translated] of phrases) {
        if (spoken.includes(english)) {
            spoken = spoken.split(english).join(translated);
        }
    }
    return spoken;
}

function applyWords(text, words) {
    let spoken = text;
    for (const [english, translated] of words) {
        spoken = spoken.replace(
            new RegExp(`(?<!-)\\b${escapeRegExp(english)}\\b`, "gi"),
            translated,
        );
    }
    return spoken;
}

function localize(text) {
    if (!text || language.value === "en") return text;
    if (language.value === "mix") {
        return applyWords(applyPhrases(text, TAGLISH_PHRASES), TAGLISH_WORDS);
    }
    return applyWords(applyPhrases(text, TAGALOG_PHRASES), TAGALOG_WORDS);
}

function readStored() {
    try {
        const current = localStorage.getItem(STORAGE_KEY);
        if (current === "1" || current === "0") return current === "1";
        return localStorage.getItem(LEGACY_STORAGE_KEY) === "1";
    } catch (e) {
        return false;
    }
}

function persist() {
    try {
        localStorage.setItem(STORAGE_KEY, enabled.value ? "1" : "0");
    } catch (e) {}
}

function readLanguage() {
    try {
        const stored = localStorage.getItem(LANG_KEY) || localStorage.getItem(LEGACY_LANG_KEY);
        return stored === "tl" || stored === "mix" ? stored : "en";
    } catch (e) {
        return "en";
    }
}

if (typeof window !== "undefined") {
    enabled.value = readStored();
    language.value = readLanguage();
}

function cleanText(text) {
    return (text || "").toString().replace(/\s+/g, " ").trim();
}

const TAGALOG_DIGITS = [
    "sero",
    "isa",
    "dalawa",
    "tatlo",
    "apat",
    "lima",
    "anim",
    "pito",
    "walo",
    "siyam",
];

function spellDigits(digits) {
    const words =
        language.value === "tl"
            ? TAGALOG_DIGITS
            : ["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine"];
    const parts = [...digits].map((digit) => words[Number(digit)] || digit);
    const groups = [];
    for (let i = 0; i < parts.length; i += 4) {
        groups.push(parts.slice(i, i + 4).join(" "));
    }
    return groups.join(". ");
}

function tagalogNumber(value) {
    if (!Number.isInteger(value) || value < 0 || value > 999) return "";
    const ones = TAGALOG_DIGITS;
    const under100 = (n) => {
        if (n < 10) return ones[n];
        if (n === 10) return "sampu";
        const teens = {
            11: "labing-isa",
            12: "labindalawa",
            13: "labintatlo",
            14: "labing-apat",
            15: "labinlima",
            16: "labing-anim",
            17: "labimpito",
            18: "labingwalo",
            19: "labinsiyam",
        };
        if (teens[n]) return teens[n];
        const tens = [
            "",
            "",
            "dalawampu",
            "tatlumpu",
            "apatnapu",
            "limampu",
            "animnapu",
            "pitumpu",
            "walumpu",
            "siyamnapu",
        ];
        const ten = Math.floor(n / 10);
        const one = n % 10;
        if (one === 0) return tens[ten];
        return `${tens[ten]}'t ${ones[one]}`;
    };
    if (value < 100) return under100(value);
    const hundreds = [
        "",
        "isang daan",
        "dalawang daan",
        "tatlong daan",
        "apat na daan",
        "limang daan",
        "anim na daan",
        "pitong daan",
        "walong daan",
        "siyam na daan",
    ];
    const rest = value % 100;
    const head = hundreds[Math.floor(value / 100)];
    return rest === 0 ? head : `${head} at ${under100(rest)}`;
}

function englishNumber(value) {
    if (!Number.isInteger(value) || value < 0 || value > 999) return "";
    const ones = [
        "zero",
        "one",
        "two",
        "three",
        "four",
        "five",
        "six",
        "seven",
        "eight",
        "nine",
    ];
    const teens = [
        "ten",
        "eleven",
        "twelve",
        "thirteen",
        "fourteen",
        "fifteen",
        "sixteen",
        "seventeen",
        "eighteen",
        "nineteen",
    ];
    const tens = [
        "",
        "",
        "twenty",
        "thirty",
        "forty",
        "fifty",
        "sixty",
        "seventy",
        "eighty",
        "ninety",
    ];
    const under100 = (n) => {
        if (n < 10) return ones[n];
        if (n < 20) return teens[n - 10];
        const ten = Math.floor(n / 10);
        const one = n % 10;
        return one === 0 ? tens[ten] : `${tens[ten]} ${ones[one]}`;
    };
    if (value < 100) return under100(value);
    const head = `${ones[Math.floor(value / 100)]} hundred`;
    const rest = value % 100;
    return rest === 0 ? head : `${head} ${under100(rest)}`;
}

function pronounce(text) {
    let spoken = cleanText(text);
    for (const [pattern, replacement] of SPOKEN_FIXES) {
        spoken = spoken.replace(pattern, replacement);
    }
    if (language.value === "tl" || language.value === "mix") {
        spoken = spoken.replace(/\bL\s*R\s*N\b/gi, "el ar en");
    }
    if (language.value === "tl") {
        spoken = spoken.replace(/\bschool form\b/gi, "papel ng eskwela");
        spoken = spoken.replace(/\bN\/A\b/gi, "wala");
    } else if (language.value === "en") {
        for (const [pattern, replacement] of ENGLISH_NAME_FIXES) {
            spoken = spoken.replace(pattern, replacement);
        }
    }
    spoken = spoken.replace(/\d{6,}/g, (num) => spellDigits(num));
    if (language.value === "tl" || language.value === "mix") {
        const toWords = language.value === "tl" ? tagalogNumber : englishNumber;
        spoken = spoken.replace(/\b\d{1,3}\b/g, (num) => {
            const word = toWords(Number(num));
            return word || num;
        });
    }
    return spoken;
}

function loadVoices() {
    const synth = window.speechSynthesis;
    const current = synth.getVoices();
    if (current.length) {
        cachedVoices = current;
        return Promise.resolve(current);
    }
    if (cachedVoices.length) return Promise.resolve(cachedVoices);
    return new Promise((resolve) => {
        const timer = setTimeout(() => resolve(synth.getVoices()), 400);
        synth.addEventListener(
            "voiceschanged",
            () => {
                clearTimeout(timer);
                cachedVoices = synth.getVoices();
                resolve(cachedVoices);
            },
            { once: true },
        );
    });
}

function pickVoice(voices) {
    if (language.value === "tl" || language.value === "mix") {
        const filipino = (voices || []).filter((voice) =>
            /^(fil|tl)(-|$)/i.test(voice.lang || ""),
        );
        if (filipino.length) return filipino[0];
    }
    const english = (voices || []).filter((voice) =>
        /^en(-|$)/i.test(voice.lang || ""),
    );
    const american = english.filter((voice) => /^en-US$/i.test(voice.lang || ""));
    const pool = american.length ? american : english;
    const preferred = [
        "aria",
        "jenny",
        "ana",
        "guy",
        "google us english",
        "samantha",
        "zira",
        "david",
    ];
    for (const name of preferred) {
        const match = pool.find((voice) =>
            voice.name.toLowerCase().includes(name),
        );
        if (match) return match;
    }
    return (
        pool.find((voice) => /natural|neural/i.test(voice.name)) ||
        pool[0] ||
        null
    );
}

function stopPcm() {
    if (pcmReader) {
        pcmReader.cancel().catch(() => {});
        pcmReader = null;
    }
    pcmSources.forEach((source) => {
        try {
            source.stop();
        } catch (e) {}
    });
    pcmSources = [];
}

function userActivated() {
    const activation = navigator.userActivation;
    // hasBeenActive stays true after login, but the browser will not start
    // audio from it. A click in this page is what unlocks ElevenLabs.
    return Boolean(gestureUnlocked || activation?.isActive);
}

function createAudioContext() {
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    if (!audioCtx || audioCtx.state === "closed") {
        try {
            audioCtx = new Ctx({ sampleRate: PCM_RATE });
        } catch (e) {
            audioCtx = new Ctx();
        }
    }
    return audioCtx;
}

function noteGesture() {
    gestureUnlocked = true;
    const ctx = createAudioContext();
    if (ctx?.state === "suspended") {
        ctx.resume()?.catch?.(() => {});
    }
    const waiters = audioWaiters.splice(0);
    waiters.forEach((resolve) => resolve());
}

function primeAudio() {
    if (!userActivated()) return null;
    const ctx = createAudioContext();
    if (ctx?.state === "suspended") {
        ctx.resume()?.catch?.(() => {});
    }
    return ctx;
}

async function runningContext() {
    if (!userActivated()) {
        await new Promise((resolve) => audioWaiters.push(resolve));
    }
    let ctx = primeAudio() || createAudioContext();
    if (!ctx) return null;
    if (ctx.state === "suspended") {
        try {
            await ctx.resume();
        } catch (e) {}
    }
    if (ctx.state === "running") return ctx;
    try {
        await ctx.close();
    } catch (e) {}
    audioCtx = null;
    if (!gestureUnlocked && !navigator.userActivation?.isActive) return null;
    ctx = createAudioContext();
    if (ctx?.state === "suspended") {
        try {
            await ctx.resume();
        } catch (e) {}
    }
    return ctx?.state === "running" ? ctx : null;
}

function concatBytes(left, right) {
    if (!left?.length) return right;
    if (!right?.length) return left;
    const out = new Uint8Array(left.length + right.length);
    out.set(left, 0);
    out.set(right, left.length);
    return out;
}

function rememberSpeech(text, bytes) {
    if (!text || !bytes?.length || bytes.length > 400000) return;
    if (speechCache.has(text)) speechCache.delete(text);
    speechCache.set(text, bytes);
    while (speechCache.size > 40) {
        speechCache.delete(speechCache.keys().next().value);
    }
}

function schedulePcm(ctx, bytes, generation, state) {
    if (generation !== speechGeneration || bytes.length < 2 || ctx.state !== "running") {
        return false;
    }
    const usable = bytes.length - (bytes.length % 2);
    const view = new DataView(bytes.buffer, bytes.byteOffset, usable);
    const floats = new Float32Array(usable / 2);
    for (let i = 0; i < floats.length; i++) {
        floats[i] = view.getInt16(i * 2, true) / 32768;
    }
    const buffer = ctx.createBuffer(1, floats.length, PCM_RATE);
    buffer.copyToChannel(floats, 0);
    const source = ctx.createBufferSource();
    source.buffer = buffer;
    source.connect(ctx.destination);
    const start = Math.max(state.nextTime, ctx.currentTime);
    source.start(start);
    state.nextTime = start + buffer.duration;
    pcmSources.push(source);
    source.onended = () => {
        pcmSources = pcmSources.filter((item) => item !== source);
    };
    return true;
}

async function playPcmBuffer(bytes, generation) {
    const ctx = await runningContext();
    if (!ctx || generation !== speechGeneration) return false;
    stopOutput();
    return schedulePcm(ctx, bytes, generation, {
        nextTime: ctx.currentTime + 0.02,
    });
}

async function playPcmStream(reader, generation, spoken) {
    pcmReader = reader;
    let played = false;
    try {
        const ctx = await runningContext();
        if (!ctx || generation !== speechGeneration) {
            reader.cancel().catch(() => {});
            return generation === speechGeneration ? false : null;
        }
        const state = { nextTime: ctx.currentTime + 0.01 };
        let pending = new Uint8Array(0);
        const parts = [];
        while (generation === speechGeneration) {
            const { done, value } = await reader.read();
            if (generation !== speechGeneration) break;
            if (value?.length) {
                const chunk = new Uint8Array(value);
                parts.push(chunk);
                const merged = concatBytes(pending, chunk);
                const usable = merged.length - (merged.length % 2);
                if (usable >= 2) {
                    if (
                        schedulePcm(
                            ctx,
                            merged.subarray(0, usable),
                            generation,
                            state,
                        )
                    ) {
                        played = true;
                    }
                    pending = merged.subarray(usable);
                } else {
                    pending = merged;
                }
            }
            if (done) {
                if (
                    pending.length >= 2 &&
                    schedulePcm(ctx, pending, generation, state)
                ) {
                    played = true;
                }
                if (generation === speechGeneration && parts.length) {
                    rememberSpeech(spoken, parts.reduce(concatBytes, new Uint8Array(0)));
                }
                break;
            }
        }
        return played;
    } finally {
        if (pcmReader === reader) pcmReader = null;
    }
}

function stopOutput() {
    stopPcm();
    if (speechAudio) {
        speechAudio.onended = null;
        speechAudio.onerror = null;
        try {
            speechAudio.pause();
        } catch (e) {}
        speechAudio.src = "";
        speechAudio = null;
    }
    if (speechObjectUrl) {
        URL.revokeObjectURL(speechObjectUrl);
        speechObjectUrl = null;
    }
}

function stopPlayback() {
    speechGeneration += 1;
    playbackStarted = false;
    if (speechAbort) {
        speechAbort.abort();
        speechAbort = null;
    }
    stopOutput();
    try {
        window.speechSynthesis?.cancel();
    } catch (e) {}
}

function stopSpeaking() {
    clearTimeout(hoverTimer);
    clearTimeout(speakTimer);
    hoverTimer = null;
    speakTimer = null;
    lastPointerEl = null;
    stopPlayback();
}

function speakWithBrowser(text, generation) {
    if (typeof window === "undefined" || !("speechSynthesis" in window)) {
        return Promise.resolve(false);
    }
    return (async () => {
        try {
            const synth = window.speechSynthesis;
            const voices = await loadVoices();
            if (generation !== speechGeneration) return false;
            synth.cancel();
            synth.resume();
            const utter = new SpeechSynthesisUtterance(text);
            const voice = pickVoice(voices);
            if (voice) utter.voice = voice;
            utter.lang =
                language.value === "en" ? voice?.lang || "en-US" : "fil-PH";
            utter.rate = 0.95;
            utter.pitch = 1;
            synth.speak(utter);
            return true;
        } catch (e) {
            return false;
        }
    })();
}

async function notifySpeechError() {
    if (speechErrorToasted) return;
    speechErrorToasted = true;
    try {
        const { useToast } = await import("@/composables/useNotify");
        useToast().error(
            "The reading voice is not available right now. Try again in a moment.",
        );
    } catch (e) {}
}

function speak(text, { force = false } = {}) {
    if (typeof window === "undefined") return;
    if (!force && (!enabled.value || suspended)) return;
    const spoken = pronounce(localize(text)).slice(0, 400);
    if (!spoken) return;
    const now = Date.now();
    if (!force && spoken === lastSpoken && now - lastSpokenAt < 1200) return;
    lastSpoken = spoken;
    lastSpokenAt = now;
    liveMessage.value = spoken;
    spokenWanted = spoken;
    if (!force && speechAbort && !playbackStarted) return;

    primeAudio();
    clearTimeout(speakTimer);
    // Drop the in-flight request, but keep the clip that is already playing
    // until the next one is ready. Cutting it mid-word is what stuttered.
    speechGeneration += 1;
    playbackStarted = false;
    if (speechAbort) {
        speechAbort.abort();
        speechAbort = null;
    }
    const generation = speechGeneration;
    const cached = speechCache.get(spoken);
    if (cached) {
        playPcmBuffer(cached, generation);
        return;
    }

    const controller = new AbortController();
    speechAbort = controller;
    playFromElevenLabs(spoken, generation, controller);
}

async function requestSpeech(spoken, controller, fast) {
    const send = () =>
        fetch("/speak", {
            method: "POST",
            credentials: "same-origin",
            signal: controller.signal,
            headers: {
                ...csrfHeaders(),
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/octet-stream, audio/mpeg",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                text: spoken,
                fast,
                language: language.value === "en" ? "en" : "fil",
            }),
        });
    let res = await send();
    if (res.status === 419 && !controller.signal.aborted) {
        res = await send();
    }
    return res;
}

async function playAudioElement(blob, generation) {
    if (generation !== speechGeneration || !blob || blob.size < 100) return false;
    const url = URL.createObjectURL(blob);
    const audio = new Audio(url);
    audio.preload = "auto";
    stopOutput();
    if (generation !== speechGeneration) {
        URL.revokeObjectURL(url);
        return false;
    }
    speechObjectUrl = url;
    speechAudio = audio;
    audio.onended = () => {
        if (speechObjectUrl === url) {
            URL.revokeObjectURL(url);
            speechObjectUrl = null;
        }
        if (speechAudio === audio) speechAudio = null;
    };
    const start = async () => {
        if (!userActivated()) {
            await new Promise((resolve) => audioWaiters.push(resolve));
        }
        if (generation !== speechGeneration) return false;
        await audio.play();
        return true;
    };
    try {
        return await start();
    } catch (e) {
        if (generation !== speechGeneration) return false;
        await new Promise((resolve) => audioWaiters.push(resolve));
        if (generation !== speechGeneration) return false;
        try {
            await audio.play();
            return true;
        } catch (err) {
            return false;
        }
    }
}

async function playFromElevenLabs(spoken, generation, controller) {
    try {
        const res = await requestSpeech(spoken, controller, true);
        if (generation !== speechGeneration || controller.signal.aborted) return;
        if (spokenWanted && spokenWanted !== spoken) {
            speak(spokenWanted, { force: true });
            return;
        }
        playbackStarted = true;
        if (!res.ok) {
            notifySpeechError();
            await speakWithBrowser(spoken, generation);
            return;
        }
        if (res.headers.get("X-Speech-Format") === "pcm_22050" && res.body) {
            const played = await playPcmStream(res.body.getReader(), generation, spoken);
            if (
                played === false &&
                generation === speechGeneration &&
                (!spokenWanted || spokenWanted === spoken)
            ) {
                const mp3 = await requestSpeech(spoken, controller, false);
                if (generation !== speechGeneration || controller.signal.aborted) return;
                if (mp3.ok && (await playAudioElement(await mp3.blob(), generation))) {
                    return;
                }
            } else {
                return;
            }
        } else if (await playAudioElement(await res.blob(), generation)) {
            return;
        }
        if (generation !== speechGeneration || controller.signal.aborted) return;
        if (spokenWanted && spokenWanted !== spoken) return;
        await speakWithBrowser(spoken, generation);
    } catch (e) {
        if (controller.signal.aborted || generation !== speechGeneration) return;
        notifySpeechError();
        await speakWithBrowser(spoken, generation);
    }
}

function introMessage() {
    const intro =
        "Talk back is on. Point at a button or a box, and I will read it.";
    return pageSpeech ? `${intro} ${pageSpeech}` : intro;
}

function fieldName(el) {
    const aria = cleanText(el.getAttribute("aria-label"));
    if (aria) return aria.replace(/\*/g, "");
    if (el.id) {
        const label = document.querySelector(`label[for="${CSS.escape(el.id)}"]`);
        const fromFor = cleanText(label?.innerText).replace(/\*/g, "");
        if (fromFor) return fromFor;
    }
    const parentLabel = el.closest("label");
    if (parentLabel) {
        const clone = parentLabel.cloneNode(true);
        clone.querySelectorAll("input, select, textarea").forEach((node) => node.remove());
        const text = cleanText(clone.textContent).replace(/\*/g, "");
        if (text) return text;
    }
    return cleanText(el.getAttribute("placeholder") || el.getAttribute("title"));
}

function clip(text) {
    const spoken = cleanText(text);
    if (spoken.length <= 180) return spoken;
    return `${spoken.slice(0, 177)}...`;
}

const AVATAR_SKIP =
    "[data-talkback-skip], .user-avatar, .user-avatar-sm, .avatar-initials, .detail-avatar, .avatar";

function isSkipped(el) {
    return Boolean(el?.closest?.(AVATAR_SKIP));
}

function columnHeader(el) {
    const cell = el?.closest?.("td, th");
    const row = cell?.parentElement;
    const table = cell?.closest?.("table");
    if (!cell || !row || !table) return "";
    const index = [...row.children].indexOf(cell);
    const th = table.tHead?.rows?.[0]?.cells?.[index];
    return cleanText(th?.innerText || "");
}

function headerLabel(header) {
    const names = {
        T1: "Term 1",
        T2: "Term 2",
        T3: "Term 3",
        LRN: "L R N",
        Final: "Final grade",
    };
    return names[header] || header;
}

function cellValue(text) {
    const value = cleanText(text);
    if (!value || value === "-" || value === "—") return "";
    if (value === "N/A") return "none";
    return value;
}

function describeCell(cell) {
    const clone = cell.cloneNode(true);
    clone
        .querySelectorAll(
            "button, input, select, textarea, a, [data-talkback-skip], .user-avatar, .user-avatar-sm, .avatar-initials, .detail-avatar, .avatar",
        )
        .forEach((node) => node.remove());
    const value = cellValue(clone.textContent);
    const label = headerLabel(columnHeader(cell));
    if (!value) {
        if (/^Term [123]$/.test(label) || label === "Final grade") {
            return `${label}. No grade yet.`;
        }
        return "";
    }
    return label ? `${label}. ${value}.` : clip(value);
}

function describeRow(row) {
    const cells = [...row.cells]
        .map((cell) => describeCell(cell).replace(/\.$/, ""))
        .filter(Boolean);
    if (!cells.length) return "";
    return clip(cells.join(", "));
}

function describe(el) {
    if (!el || isSkipped(el)) return "";
    const custom = cleanText(el.getAttribute("data-talkback"));
    if (custom) return custom;
    if (el.tagName === "LABEL") {
        const control = el.control || el.querySelector("input, select, textarea");
        return control ? describe(control) : clip(el.innerText);
    }
    if (el.tagName === "TR") return describeRow(el);
    if (/^H[1-3]$/.test(el.tagName)) return clip(el.innerText);
    const inCell = el.closest("td, th");
    if (
        inCell &&
        !el.closest("button, a, input, select, textarea, label")
    ) {
        if (el === inCell) return describeCell(inCell);
        const own = cellValue(el.innerText);
        if (!own) return describeCell(inCell);
        const label = headerLabel(columnHeader(inCell));
        return label ? `${label}. ${own}.` : clip(own);
    }
    if (el.tagName === "SELECT") {
        const name = fieldName(el) || "List";
        const selected = cleanText(el.options?.[el.selectedIndex]?.text || "");
        return selected ? `${name}. Currently ${selected}.` : `${name}. Choose from the list.`;
    }
    if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
        const name = fieldName(el) || "Box";
        if (el.type === "password") return `${name}. Type here. The letters stay hidden.`;
        if (el.type === "date") return `${name}. Choose a date.`;
        if (el.type === "checkbox" || el.type === "radio") {
            return `${name}. ${el.checked ? "Checked" : "Not checked"}.`;
        }
        return `${name}. Type here.`;
    }
    const aria = cleanText(el.getAttribute("aria-label"));
    const text = clip(aria || el.innerText || el.getAttribute("title") || "");
    if (!text) return "";
    if (el.disabled || el.getAttribute("aria-disabled") === "true") {
        return `${text}. Not available right now.`;
    }
    return text;
}

function isVisible(el) {
    const style = window.getComputedStyle(el);
    return style.display !== "none" && style.visibility !== "hidden";
}

function mark(el, className, previous) {
    if (previous === el) return previous;
    previous?.classList.remove(className);
    if (el) el.classList.add(className);
    return el || null;
}

function clearMarks() {
    highlighted?.classList.remove("talkback-highlight");
    hovered?.classList.remove("talkback-hover");
    highlighted = null;
    hovered = null;
}

function partFromEvent(event) {
    const target =
        event.target instanceof Element
            ? event.target
            : event.target?.parentElement;
    if (!target) return null;
    const skipped = target.closest(AVATAR_SKIP);
    if (skipped) return skipped;
    const control = target.closest(
        "button, a, input, select, textarea, summary, label, [role='button']",
    );
    if (control) return control;
    const chunk = target.closest(
        ".user-name, .user-role, .user-name-cell, .user-info-cell, .grade-cell, .lrn-badge, .gov-stat-box, .gov-link-box, .nav-item, h1, h2, h3, [data-talkback]",
    );
    if (chunk) return chunk;
    return target.closest("td, th");
}

function speakElement(el, { fromPointer = false } = {}) {
    if (!enabled.value || suspended || !el || !isVisible(el) || isSkipped(el)) {
        return;
    }
    if (fromPointer && el === lastPointerEl) return;
    const text = describe(el);
    if (!text) return;
    lastPointerEl = el;
    speak(text);
}

function onPointerOver(event) {
    if (!enabled.value || suspended) return;
    const part = partFromEvent(event);
    if (!part || !isVisible(part)) return;
    hovered = mark(part, "talkback-hover", hovered);
    if (part === lastPointerEl || isSkipped(part)) return;
    clearTimeout(hoverTimer);
    hoverTimer = setTimeout(() => speakElement(part, { fromPointer: true }), 180);
}

function onPointerOut(event) {
    const part = partFromEvent(event);
    if (!part) return;
    if (part.contains(event.relatedTarget)) return;
    if (hovered === part) {
        part.classList.remove("talkback-hover");
        hovered = null;
    }
    if (lastPointerEl === part) lastPointerEl = null;
    clearTimeout(hoverTimer);
}

function onFocusIn(event) {
    if (!enabled.value || suspended) return;
    const part = partFromEvent(event) || event.target?.closest?.(SPEAKABLE);
    if (!part || isSkipped(part)) return;
    clearTimeout(hoverTimer);
    highlighted = mark(part, "talkback-highlight", highlighted);
    speakElement(part);
}

function onClick(event) {
    if (!enabled.value || suspended || event.button !== 0) return;
    const part = partFromEvent(event);
    if (!part || !isVisible(part)) return;
    highlighted = mark(part, "talkback-highlight", highlighted);
    if (isSkipped(part)) return;
    clearTimeout(hoverTimer);
    speakElement(part);
}

function bindListeners() {
    if (listenersOn) return;
    listenersOn = true;
    document.addEventListener("pointerdown", noteGesture, true);
    document.addEventListener("keydown", noteGesture, true);
    document.addEventListener("mouseover", onPointerOver, true);
    document.addEventListener("mouseout", onPointerOut, true);
    document.addEventListener("focusin", onFocusIn, true);
    document.addEventListener("click", onClick, true);
}

function unbindListeners() {
    if (!listenersOn) return;
    listenersOn = false;
    document.removeEventListener("pointerdown", noteGesture, true);
    document.removeEventListener("keydown", noteGesture, true);
    document.removeEventListener("mouseover", onPointerOver, true);
    document.removeEventListener("mouseout", onPointerOut, true);
    document.removeEventListener("focusin", onFocusIn, true);
    document.removeEventListener("click", onClick, true);
    clearTimeout(hoverTimer);
    clearMarks();
}

function announcePageIfNeeded() {
    if (mounts < 1 || !enabled.value || suspended) return;
    if (!pageSpeech || pageSpeech === lastAnnouncedPage) return;
    lastAnnouncedPage = pageSpeech;
    speak(pageSpeech);
}

export function talkBackGuide(currentPage, pageTitle = "", role = "teacher") {
    if (
        currentPage === "school-forms" &&
        pageTitle &&
        pageTitle !== "School Forms"
    ) {
        return "This is a preview. Press Back to return, or Print to save a copy.";
    }
    const guides = ROLE_GUIDES[role] || ROLE_GUIDES.teacher;
    return (
        guides[currentPage] ||
        SHARED_GUIDES[currentPage] ||
        "Use the menu on the left to open another page."
    );
}

export function speakTalkBack(text) {
    if (mounts < 1 || !enabled.value || suspended) return;
    speak(text);
}

export function useTalkBack() {
    function setPage(title, hint) {
        const next = [title, hint]
            .map((part) => cleanText(part))
            .filter(Boolean)
            .join(". ");
        if (!next || next === pageSpeech) return;
        pageSpeech = next;
        clearTimeout(pageTimer);
        pageTimer = setTimeout(announcePageIfNeeded, 30);
    }

    function setSuspended(next, { quiet = false } = {}) {
        const turningOn = Boolean(next);
        suspended = turningOn;
        paused.value = turningOn;
        clearTimeout(introTimer);
        if (turningOn) {
            stopSpeaking();
            clearMarks();
            return;
        }
        if (quiet) {
            pendingIntro = false;
            return;
        }
        if (pendingIntro && enabled.value) {
            pendingIntro = false;
            introTimer = setTimeout(() => {
                if (suspended || !enabled.value) return;
                lastAnnouncedPage = pageSpeech;
                speak(introMessage(), { force: true });
            }, 400);
        }
    }

    function toggle() {
        enabled.value = !enabled.value;
        persist();
        if (!enabled.value) {
            pendingIntro = false;
            lastAnnouncedPage = "";
            stopSpeaking();
            clearMarks();
            if (!suspended) speak("Talk back is off.", { force: true });
            return;
        }
        if (suspended) {
            pendingIntro = true;
            return;
        }
        pendingIntro = false;
        lastAnnouncedPage = pageSpeech;
        speak(introMessage(), { force: true });
    }

    return {
        enabled,
        paused,
        liveMessage,
        setPage,
        setSuspended,
        toggle,
        speak,
        language,
        setLanguage,
    };
}

function setLanguage(next) {
    const lang = next === "tl" || next === "mix" ? next : "en";
    if (language.value === lang) return;
    language.value = lang;
    try {
        localStorage.setItem(LANG_KEY, lang);
    } catch (e) {}
    speechCache.clear();
    lastSpoken = "";
    lastAnnouncedPage = "";
    if (!enabled.value || suspended) return;
    const notes = {
        tl: "Tagalog na ang boses.",
        mix: "Taglish na ang boses. Halo ang English at Tagalog.",
        en: "The voice is now English.",
    };
    speak(notes[lang], { force: true });
}

export function useTalkBackHost() {
    onMounted(() => {
        mounts += 1;
        bindListeners();
        announcePageIfNeeded();
    });

    onUnmounted(() => {
        mounts = Math.max(0, mounts - 1);
        if (mounts === 0) {
            lastAnnouncedPage = "";
            unbindListeners();
        }
    });
}
