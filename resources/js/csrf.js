function readCookie(name) {
    const parts = document.cookie ? document.cookie.split("; ") : [];
    for (const part of parts) {
        const eq = part.indexOf("=");
        if (eq === -1 || part.slice(0, eq) !== name) continue;
        const raw = part.slice(eq + 1);
        try {
            return decodeURIComponent(raw);
        } catch (e) {
            return raw;
        }
    }
    return "";
}

function readMeta() {
    return (
        document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
        ""
    );
}

// The XSRF-TOKEN cookie is encrypted. Laravel only accepts it in the
// X-XSRF-TOKEN header, which it decrypts. Sending that value as
// X-CSRF-TOKEN makes every speak request fail before ElevenLabs is called.
export function csrfHeaders() {
    const encrypted = readCookie("XSRF-TOKEN");
    if (encrypted) {
        return { "X-XSRF-TOKEN": encrypted };
    }
    const meta = readMeta();
    return meta ? { "X-CSRF-TOKEN": meta } : {};
}
