import { toast } from "vue-sonner";
import { speakTalkBack } from "@/composables/useTalkBack";

const TIMEOUT_MS = 2000;

function normalizeMessage(message) {
    if (message == null || message === "") {
        return "Something went wrong.";
    }

    if (typeof message === "string") {
        return message;
    }

    if (Array.isArray(message)) {
        return normalizeMessage(message[0]);
    }

    if (typeof message === "object") {
        return normalizeMessage(Object.values(message)[0]);
    }

    return String(message);
}

const options = {
    duration: TIMEOUT_MS,
};

function voiced(message, show) {
    const text = normalizeMessage(message);
    const id = show(text);
    speakTalkBack(text);
    return id;
}

export function useNotify() {
    return {
        success: (message) => voiced(message, (text) => toast.success(text, options)),
        error: (message) => voiced(message, (text) => toast.error(text, options)),
        warning: (message) => voiced(message, (text) => toast.warning(text, options)),
        info: (message) => voiced(message, (text) => toast.info(text, options)),
        dismiss: (id) => toast.dismiss(id),
        clear: () => toast.dismiss(),
    };
}

export function useToast() {
    return useNotify();
}
