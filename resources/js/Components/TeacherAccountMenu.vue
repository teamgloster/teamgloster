<template>
    <div class="account-menu" @click.stop>
        <button
            type="button"
            class="account-trigger"
            :aria-expanded="open"
            aria-haspopup="true"
            data-talkback="Your name. Click to open the menu."
            @click="open = !open"
        >
            <span class="user-meta">
                <span class="user-role">{{ roleLabel }}</span>
                <span class="user-name">{{ user.first_name }} {{ user.last_name }}</span>
            </span>
            <span class="user-avatar" data-talkback-skip aria-hidden="true">
                <img
                    v-if="profilePhotoUrl"
                    :src="profilePhotoUrl"
                    alt=""
                    class="avatar-img"
                />
                <span v-else>{{ userInitials }}</span>
            </span>
            <ChevronDown :size="16" class="account-chevron" :class="{ open }" />
        </button>
        <div v-if="open" class="account-dropdown" role="menu">
            <p class="account-dropdown-title">Talk Back</p>
            <TalkBackToggle block />
            <p class="account-dropdown-title language-title">Language</p>
            <div class="lang-choice" role="group" aria-label="Talk back language">
                <button
                    type="button"
                    :class="{ on: language === 'en' }"
                    :aria-pressed="language === 'en'"
                    data-talkback="English"
                    @click="setLanguage('en')"
                >
                    English
                </button>
                <button
                    type="button"
                    :class="{ on: language === 'tl' }"
                    :aria-pressed="language === 'tl'"
                    data-talkback="Tagalog"
                    @click="setLanguage('tl')"
                >
                    Tagalog
                </button>
                <button
                    type="button"
                    :class="{ on: language === 'mix' }"
                    :aria-pressed="language === 'mix'"
                    data-talkback="Taglish"
                    @click="setLanguage('mix')"
                >
                    Taglish
                </button>
            </div>
            <p class="account-hint">
                {{ languageHint }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { ChevronDown } from "lucide-vue-next";
import TalkBackToggle from "@/Components/TalkBackToggle.vue";
import { useTalkBack } from "@/composables/useTalkBack";

const props = defineProps({
    user: { type: Object, required: true },
    roleLabel: { type: String, default: "Teacher" },
});

const open = ref(false);
const { language, setLanguage } = useTalkBack();
const languageHint = computed(() => {
    if (language.value === "tl") {
        return "Piliin mo kung English, Tagalog, o Taglish ang babasahin.";
    }
    if (language.value === "mix") {
        return "Halo ang English at Tagalog. Piliin ang boses.";
    }
    return "Choose English, Tagalog, or Taglish for the reading voice.";
});

const profilePhotoUrl = computed(() => {
    if (props.user?.profile_photo_url) return props.user.profile_photo_url;
    if (props.user?.profile_photo) return `/storage/${props.user.profile_photo}`;
    return null;
});
const userInitials = computed(() => {
    const first = props.user?.first_name?.charAt(0) || "";
    const last = props.user?.last_name?.charAt(0) || "";
    return (first + last).toUpperCase();
});

function onDocumentClick(event) {
    if (!event.target.closest(".account-menu")) {
        open.value = false;
    }
}

function onKeydown(event) {
    if (event.key === "Escape") open.value = false;
}

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
    document.addEventListener("keydown", onKeydown);
});

onUnmounted(() => {
    document.removeEventListener("click", onDocumentClick);
    document.removeEventListener("keydown", onKeydown);
});
</script>

<style scoped>
.account-menu {
    position: relative;
}

.account-trigger {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.2rem 0.35rem 0.2rem 0.55rem;
    border: 2px solid transparent;
    background: transparent;
    cursor: pointer;
    font-family: inherit;
    color: inherit;
}

.account-trigger:hover,
.account-trigger[aria-expanded="true"] {
    border-color: #c9a227;
    background: #fff8e6;
}

.account-trigger:focus-visible {
    outline: 3px solid #c9a227;
    outline-offset: 2px;
}

.user-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.user-role {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #666;
}

.user-name {
    font-weight: 600;
    color: #003366;
    font-size: 0.88rem;
}

.user-avatar {
    width: 34px;
    height: 34px;
    background: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.78rem;
    overflow: hidden;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.account-chevron {
    color: #003366;
    transition: transform 0.15s ease;
}

.account-chevron.open {
    transform: rotate(180deg);
}

.account-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 320px;
    padding: 0.85rem;
    background: #fff;
    border: 2px solid #003366;
    box-shadow: 0 10px 24px rgba(0, 51, 102, 0.18);
    z-index: 80;
}

.account-dropdown-title {
    margin: 0 0 0.55rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #666;
}

.language-title {
    margin-top: 0.85rem;
}

.lang-choice {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.4rem;
}

.lang-choice button {
    padding: 0.5rem 0.4rem;
    border: 2px solid #003366;
    background: #fff;
    color: #003366;
    font-family: inherit;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
}

.lang-choice button.on {
    background: #003366;
    color: #fff;
}

.lang-choice button:focus-visible {
    outline: 3px solid #c9a227;
    outline-offset: 2px;
}

.account-hint {
    margin: 0.55rem 0 0;
    font-size: 0.78rem;
    line-height: 1.35;
    color: #003366;
}

@media (max-width: 768px) {
    .user-meta {
        display: none;
    }
}
</style>
