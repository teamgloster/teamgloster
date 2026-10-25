<template>
    <span class="talkback-slot">
        <button
            type="button"
            class="talkback-toggle"
            :class="{ on: enabled, block }"
            :aria-pressed="enabled"
            :data-talkback="
                enabled
                    ? 'Talk back is on. Click to turn the reading voice off.'
                    : 'Talk back is off. Click to hear this page read aloud.'
            "
            @click="onClick"
        >
            <Volume2 :size="18" aria-hidden="true" />
            <span class="talkback-copy">
                <span class="talkback-label">Talk Back</span>
                <span class="talkback-state">{{ enabled ? "On" : "Off" }}</span>
            </span>
            <span class="talkback-switch" aria-hidden="true">
                <span class="talkback-knob"></span>
            </span>
        </button>
        <span class="talkback-live" aria-live="polite">{{ liveMessage }}</span>
    </span>
</template>

<script setup>
import { Volume2 } from "lucide-vue-next";
import { useToast } from "@/composables/useNotify";
import { useTalkBack } from "@/composables/useTalkBack";

defineProps({
    block: { type: Boolean, default: false },
});

const toast = useToast();
const { enabled, paused, liveMessage, toggle } = useTalkBack();

function onClick() {
    const turningOn = !enabled.value;
    toggle();

    if (turningOn && paused.value) {
        toast.info("Talk back will start after you stop voice input.");
    }
}
</script>

<style scoped>
.talkback-slot {
    position: relative;
    display: inline-flex;
    flex-shrink: 0;
}

.talkback-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.32rem 0.55rem 0.32rem 0.65rem;
    border: 2px solid #003366;
    border-radius: 999px;
    background: #fff;
    color: #003366;
    cursor: pointer;
    font-family: inherit;
    line-height: 1.1;
    flex-shrink: 0;
}

.talkback-toggle:hover {
    border-color: #c9a227;
}

.talkback-toggle:focus-visible {
    outline: 3px solid #c9a227;
    outline-offset: 2px;
}

.talkback-toggle.on {
    background: #003366;
    color: #fff;
}

.talkback-toggle.block {
    width: 100%;
    justify-content: space-between;
    border-radius: 8px;
    padding: 0.6rem 0.75rem;
}

.talkback-copy {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 4.4rem;
}

.talkback-label {
    font-size: 0.78rem;
    font-weight: 700;
}

.talkback-state {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    opacity: 0.8;
}

.talkback-switch {
    width: 36px;
    height: 20px;
    border-radius: 999px;
    background: #c5cdd6;
    position: relative;
    flex-shrink: 0;
}

.talkback-toggle.on .talkback-switch {
    background: #c9a227;
}

.talkback-knob {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.15s ease;
}

.talkback-toggle.on .talkback-knob {
    transform: translateX(16px);
}

.talkback-live {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
