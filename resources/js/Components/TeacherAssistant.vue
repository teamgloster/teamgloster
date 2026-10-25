<template>
    <div class="assistant">
        <button
            type="button"
            class="assistant-toggle"
            :class="{ on: open }"
            :aria-expanded="open"
            data-talkback="Voice assistant. Click to open the chat."
            @click="open = !open"
        >
            <MessageCircle :size="16" />
            <span>Assistant</span>
        </button>

        <section
            v-if="open"
            class="assistant-panel"
            role="dialog"
            aria-label="Voice assistant"
        >
            <header class="assistant-head">
                <div>
                    <p class="assistant-kicker">{{ helperLabel }}</p>
                    <h2>Voice assistant</h2>
                </div>
                <div class="assistant-head-actions">
                    <button
                        type="button"
                        class="icon-btn"
                        :aria-pressed="voiceReplies"
                        :data-talkback="
                            voiceReplies
                                ? 'Voice replies are on. Click to keep answers on screen only.'
                                : 'Voice replies are off. Click to hear answers.'
                        "
                        @click="setVoiceReplies(!voiceReplies)"
                    >
                        <Volume2 v-if="voiceReplies" :size="16" />
                        <VolumeX v-else :size="16" />
                    </button>
                    <button
                        type="button"
                        class="icon-btn"
                        data-talkback="Close the assistant"
                        @click="open = false"
                    >
                        <X :size="16" />
                    </button>
                </div>
            </header>

            <div class="assistant-lang" role="group" aria-label="Reply language">
                <button
                    type="button"
                    :aria-pressed="language === 'en'"
                    data-talkback="English. Replies stay in English."
                    @click="setLanguage('en')"
                >
                    English
                </button>
                <button
                    type="button"
                    :aria-pressed="language === 'tl'"
                    data-talkback="Tagalog. Tagalog ang mga sagot."
                    @click="setLanguage('tl')"
                >
                    Tagalog
                </button>
            </div>

            <div ref="log" class="assistant-log" data-talkback-skip>
                <p v-if="!messages.length" class="assistant-empty">
                    {{ emptyHint }}
                </p>
                <div
                    v-for="(item, index) in messages"
                    :key="index"
                    class="assistant-bubble"
                    :class="item.role"
                >
                    {{ item.content }}
                </div>
                <p v-if="listening" class="assistant-status">
                    {{ language === "tl" ? "Nakikinig… pindutin ang mikropono kapag tapos na." : "Listening… click the mic when you are done." }}
                </p>
                <p v-else-if="busy" class="assistant-status">
                    {{ language === "tl" ? "Sandali lang…" : "One moment…" }}
                </p>
            </div>

            <form class="assistant-form" @submit.prevent="sendText">
                <button
                    type="button"
                    class="mic-btn"
                    :class="{ listening }"
                    :disabled="busy"
                    :data-talkback="
                        listening
                            ? 'Stop and send what I said'
                            : 'Speak a question'
                    "
                    @click="toggleMic"
                >
                    <Mic :size="18" />
                </button>
                <input
                    v-model="draft"
                    type="text"
                    maxlength="1000"
                    :placeholder="language === 'tl' ? 'Magtanong dito' : 'Type a question'"
                    :aria-label="language === 'tl' ? 'Magtanong dito' : 'Type a question'"
                    :disabled="busy || listening"
                    @keydown.esc="open = false"
                />
                <button
                    type="submit"
                    class="send-btn"
                    data-talkback="Send the question"
                    :disabled="busy || listening || !draft.trim()"
                >
                    <Send :size="16" />
                </button>
            </form>
            <p v-if="error" class="assistant-error">{{ error }}</p>
        </section>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { MessageCircle, Mic, Send, Volume2, VolumeX, X } from "lucide-vue-next";
import { useTalkBack } from "@/composables/useTalkBack";
import {
    commandAsksSomething,
    goToTeacherPage,
    portalPageFor,
    useTeacherAssistant,
} from "@/composables/useTeacherAssistant";

const props = defineProps({
    pageTitle: { type: String, default: "" },
    role: { type: String, default: "teacher" },
});

const { open, messages, voiceReplies, setVoiceReplies, language, setLanguage } = useTeacherAssistant();

const helperLabel = computed(() => {
    const labels = {
        administrator: "Admin helper",
        student: "Student helper",
        registrar: "Registrar helper",
    };
    return labels[props.role] || "Teacher helper";
});

const emptyHint = computed(() => {
    if (language.value === "tl") {
        const tagalog = {
            administrator: "Magtanong tungkol sa students, admissions, o sabihin ang page. Halimbawa: buksan ang Teachers.",
            student: "Magtanong tungkol sa grades, attendance, o sabihin ang page. Halimbawa: buksan ang Enrollment.",
            registrar: "Magtanong tungkol sa sections, students, o sabihin ang page. Halimbawa: buksan ang Year Levels.",
        };
        return tagalog[props.role] || "Magtanong tungkol sa attendance, grades, o school forms. I-type sa baba, o pindutin ang mikropono.";
    }
    const english = {
        administrator: "Ask about students or admissions, or say a page name such as Teachers or Settings.",
        student: "Ask about your grades or attendance, or say a page name such as Enrollment.",
        registrar: "Ask about sections or students, or say a page name such as Year Levels.",
    };
    return english[props.role] || "Ask how to take attendance, enter grades, or open a school form. Type below, or tap the microphone and speak.";
});
const { paused, setSuspended } = useTalkBack();
const draft = ref("");
const busy = ref(false);
const listening = ref(false);
const error = ref("");
const log = ref(null);

let recorder = null;
let stream = null;
let chunks = [];
let audio = null;
let audioUrl = "";
let talkBackHolds = 0;
let restoreTalkBack = false;

function csrfToken() {
    return (
        document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
        ""
    );
}

function holdTalkBack() {
    if (talkBackHolds === 0) restoreTalkBack = paused.value;
    talkBackHolds += 1;
    setSuspended(true);
}

function releaseTalkBack() {
    if (talkBackHolds === 0) return;
    talkBackHolds -= 1;
    if (talkBackHolds === 0 && !restoreTalkBack) {
        setSuspended(false, { quiet: true });
    }
}

function releaseTalkBackFully() {
    talkBackHolds = 0;
    if (!restoreTalkBack) setSuspended(false, { quiet: true });
}

function stopAudio() {
    if (!audio) return;
    audio.onended = null;
    audio.onerror = null;
    try {
        audio.pause();
    } catch (e) {}
    audio = null;
    if (audioUrl) {
        URL.revokeObjectURL(audioUrl);
        audioUrl = "";
    }
}

async function scrollLog() {
    await nextTick();
    if (log.value) log.value.scrollTop = log.value.scrollHeight;
}

function spokenReply(text) {
    const clean = text.replace(/\s+/g, " ").trim();
    if (clean.length <= 400) return clean;
    const cut = clean.slice(0, 400);
    const end = Math.max(cut.lastIndexOf("."), cut.lastIndexOf("!"), cut.lastIndexOf("?"));
    return (end > 80 ? cut.slice(0, end + 1) : cut).trim();
}

async function speakReply(text) {
    if (!voiceReplies.value) return;
    stopAudio();
    holdTalkBack();
    try {
        const res = await fetch("/speak", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": csrfToken(),
                "X-Requested-With": "XMLHttpRequest",
                Accept: "audio/mpeg, application/octet-stream",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                text: spokenReply(text),
                language: language.value === "tl" ? "fil" : "en",
            }),
        });
        if (!res.ok) return;
        const blob = await res.blob();
        if (blob.size < 100) return;
        audioUrl = URL.createObjectURL(blob);
        audio = new Audio(audioUrl);
        audio.onended = () => {
            stopAudio();
            releaseTalkBack();
        };
        await audio.play();
        return;
    } catch (e) {
        stopAudio();
    }
    releaseTalkBack();
}

function voicePrompt() {
    const prompts = {
        administrator: "An administrator opening a page such as students, admissions, teachers, sections, or settings.",
        student: "A student asking about enrollment, grades, attendance, requirements, or profile.",
        registrar: "A registrar opening a page such as year levels, sections, students, grades, or enrollment.",
    };
    return prompts[props.role] || "A teacher asking about classes, grades, attendance, or school forms.";
}

watch(
    () => props.role,
    (role, previous) => {
        if (previous && role !== previous) messages.value = [];
    },
);

function openingLine(label) {
    return language.value === "tl" ? `Bubuksan ang ${label}.` : `Opening ${label}.`;
}

function tidyReply(text) {
    const parts = String(text || "")
        .split(/(?<=[.!?])\s*/)
        .map((part) => part.trim())
        .filter(Boolean);
    const unique = [];
    parts.forEach((part) => {
        if (unique.at(-1) !== part) unique.push(part);
    });
    return unique.join(" ");
}

async function ask(text) {
    const question = text.trim();
    if (!question || busy.value) return;
    error.value = "";
    const destination = portalPageFor(props.role, question);
    if (destination && !commandAsksSomething(question)) {
        messages.value.push({ role: "user", content: question });
        messages.value.push({
            role: "assistant",
            content: openingLine(destination.label),
        });
        draft.value = "";
        await scrollLog();
        speakReply(openingLine(destination.label));
        goToTeacherPage(destination);
        return;
    }
    if (destination) goToTeacherPage(destination);
    messages.value.push({ role: "user", content: question });
    if (messages.value.length > 20) {
        messages.value.splice(0, messages.value.length - 20);
    }
    draft.value = "";
    busy.value = true;
    await scrollLog();

    const history = messages.value
        .slice(0, -1)
        .slice(-8)
        .map((item) => ({ role: item.role, content: item.content }));

    try {
        const res = await fetch("/assistant", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": csrfToken(),
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                message: question,
                page: props.pageTitle,
                history,
                language: language.value,
            }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            error.value = data.error || "The assistant could not answer just now.";
            return;
        }
        const reply = tidyReply(data.reply);
        messages.value.push({ role: "assistant", content: reply });
        await scrollLog();
        speakReply(reply);
    } catch (e) {
        error.value = "The assistant could not be reached. Try again.";
    } finally {
        busy.value = false;
        await scrollLog();
    }
}

function sendText() {
    ask(draft.value);
}

function pickMime() {
    if (typeof MediaRecorder === "undefined") return "";
    const types = ["audio/webm;codecs=opus", "audio/webm", "audio/mp4"];
    return types.find((type) => MediaRecorder.isTypeSupported(type)) || "";
}

async function toggleMic() {
    if (listening.value) {
        stopMic();
        return;
    }
    error.value = "";
    if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === "undefined") {
        error.value = "This browser cannot use the microphone. Type your question instead.";
        return;
    }
    try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (e) {
        error.value = "Allow the microphone, then try again.";
        return;
    }
    chunks = [];
    const mime = pickMime();
    recorder = mime ? new MediaRecorder(stream, { mimeType: mime }) : new MediaRecorder(stream);
    recorder.ondataavailable = (event) => {
        if (event.data?.size) chunks.push(event.data);
    };
    recorder.onstop = () => {
        const blob = new Blob(chunks, { type: recorder.mimeType || "audio/webm" });
        chunks = [];
        releaseMic();
        transcribe(blob);
    };
    holdTalkBack();
    listening.value = true;
    recorder.start();
}

function stopMic() {
    if (recorder && recorder.state !== "inactive") {
        recorder.stop();
        return;
    }
    releaseMic();
    listening.value = false;
    releaseTalkBack();
}

function releaseMic() {
    stream?.getTracks().forEach((track) => track.stop());
    stream = null;
    recorder = null;
    listening.value = false;
}

async function transcribe(blob) {
    if (!blob.size) {
        releaseTalkBack();
        error.value = "I didn't hear that. Try again.";
        return;
    }
    busy.value = true;
    try {
        const body = new FormData();
        const name = blob.type.includes("mp4") ? "assistant.mp4" : "assistant.webm";
        body.append("audio", blob, name);
        body.append("language", "");
        body.append("prompt", voicePrompt());
        const res = await fetch("/transcribe", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": csrfToken(),
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            error.value = data.error || "I could not hear that. Try again.";
            releaseTalkBack();
            return;
        }
        const text = (data.text || "").trim();
        if (!text) {
            error.value = "I didn't hear a question. Try again.";
            releaseTalkBack();
            return;
        }
        busy.value = false;
        releaseTalkBack();
        await ask(text);
    } catch (e) {
        error.value = "I could not hear that. Try again.";
        releaseTalkBack();
    } finally {
        busy.value = false;
    }
}

function onKeydown(event) {
    if (event.key === "Escape" && open.value) open.value = false;
}

watch(open, (isOpen) => {
    if (!isOpen) {
        stopMic();
        stopAudio();
        releaseTalkBackFully();
    }
});

onMounted(() => document.addEventListener("keydown", onKeydown));
onUnmounted(() => {
    document.removeEventListener("keydown", onKeydown);
    const wasListening = listening.value;
    if (recorder && recorder.state !== "inactive") {
        recorder.onstop = null;
        try {
            recorder.stop();
        } catch (e) {}
    }
    releaseMic();
    if (wasListening) releaseTalkBack();
});
</script>

<style scoped>
.assistant {
    position: relative;
}

.assistant-toggle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.42rem 0.85rem;
    border: none;
    border-radius: 6px;
    background: #003366;
    color: #fff;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.82rem;
    font-family: inherit;
    white-space: nowrap;
}

.assistant-toggle:hover,
.assistant-toggle.on {
    background: #c9a227;
    color: #003366;
}

.assistant-panel {
    position: fixed;
    top: 4.6rem;
    right: 1rem;
    z-index: 80;
    width: min(380px, calc(100vw - 2rem));
    max-height: min(560px, calc(100vh - 6rem));
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #d5dbe3;
    border-top: 4px solid #c9a227;
    border-radius: 12px;
    box-shadow: 0 16px 40px rgba(0, 34, 68, 0.18);
}

.assistant-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.85rem 0.9rem 0.7rem;
    border-bottom: 1px solid #e6ebf1;
}

.assistant-kicker {
    margin: 0;
    color: #8a6a12;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.assistant-head h2 {
    margin: 0.1rem 0 0;
    color: #003366;
    font-size: 1.05rem;
}

.assistant-head-actions {
    display: flex;
    gap: 0.35rem;
}

.assistant-lang {
    display: flex;
    gap: 0.35rem;
    padding: 0.55rem 0.9rem;
    border-bottom: 1px solid #e6ebf1;
    background: #f8fafc;
}

.assistant-lang button {
    flex: 1;
    border: 1px solid #d5dbe3;
    background: #fff;
    color: #003366;
    border-radius: 8px;
    padding: 0.38rem 0.5rem;
    font: inherit;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
}

.assistant-lang button[aria-pressed="true"] {
    background: #003366;
    color: #fff;
    border-color: #003366;
}

.icon-btn,
.mic-btn,
.send-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #d5dbe3;
    background: #fff;
    color: #003366;
    cursor: pointer;
}

.icon-btn {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
}

.icon-btn[aria-pressed="true"] {
    background: #003366;
    color: #fff;
    border-color: #003366;
}

.assistant-log {
    flex: 1;
    overflow: auto;
    padding: 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    min-height: 220px;
}

.assistant-empty,
.assistant-status,
.assistant-error {
    margin: 0;
    color: #4d5d73;
    font-size: 0.86rem;
    line-height: 1.45;
}

.assistant-bubble {
    max-width: 92%;
    padding: 0.6rem 0.75rem;
    border-radius: 12px;
    font-size: 0.9rem;
    line-height: 1.45;
    white-space: pre-wrap;
}

.assistant-bubble.user {
    align-self: flex-end;
    background: #003366;
    color: #fff;
    border-bottom-right-radius: 4px;
}

.assistant-bubble.assistant {
    align-self: flex-start;
    background: #f4f7fb;
    color: #1c2b3a;
    border-bottom-left-radius: 4px;
}

.assistant-status {
    color: #8a6a12;
    font-weight: 600;
}

.assistant-form {
    display: flex;
    gap: 0.4rem;
    padding: 0.75rem;
    border-top: 1px solid #e6ebf1;
}

.assistant-form input {
    flex: 1;
    min-width: 0;
    border: 1px solid #cfd6df;
    border-radius: 8px;
    padding: 0.55rem 0.7rem;
    font: inherit;
    font-size: 0.9rem;
}

.assistant-form input:focus {
    outline: 2px solid #c9a227;
    border-color: #c9a227;
}

.mic-btn,
.send-btn {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 8px;
    flex-shrink: 0;
}

.mic-btn.listening {
    background: #9b1c1c;
    border-color: #9b1c1c;
    color: #fff;
}

.send-btn {
    background: #003366;
    border-color: #003366;
    color: #fff;
}

.mic-btn:disabled,
.send-btn:disabled,
.assistant-form input:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.assistant-error {
    padding: 0 0.85rem 0.75rem;
    color: #9b1c1c;
}
</style>
