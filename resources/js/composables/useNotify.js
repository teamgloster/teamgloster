import { toast } from "vue-sonner";

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

export function useNotify() {
    return {
        success: (message) => toast.success(normalizeMessage(message), options),
        error: (message) => toast.error(normalizeMessage(message), options),
        warning: (message) => toast.warning(normalizeMessage(message), options),
        info: (message) => toast.info(normalizeMessage(message), options),
        dismiss: (id) => toast.dismiss(id),
        clear: () => toast.dismiss(),
    };
}

export function useToast() {
    return useNotify();
}
