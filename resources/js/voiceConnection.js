export const LOW_INTERNET_MESSAGE =
    "Low internet connection. The voice cannot listen or speak right now. Try again when the connection is steady.";

export function isNetworkFailure(error) {
    if (typeof navigator !== "undefined" && navigator.onLine === false) {
        return true;
    }
    const message = String(error?.message || error || "");
    return /failed to fetch|networkerror|network request failed|load failed|err_network|err_internet|offline|timeout/i.test(
        message,
    );
}
