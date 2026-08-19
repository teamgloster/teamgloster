<template>
    <h2 class="sf-section-title">{{ title }}</h2>
    <div class="sf-fill-grid">
        <div class="sf-fill">
            <span class="sf-fill-label">Name of School:</span>
            <span class="sf-fill-value">{{
                dash(eligibility.previous_school)
            }}</span>
        </div>
        <div class="sf-fill">
            <span class="sf-fill-label">General Average:</span>
            <span class="sf-fill-value">{{
                dash(eligibility.previous_gwa)
            }}</span>
        </div>
        <div class="sf-fill wide">
            <span class="sf-fill-label">Year Level / Completer Record on File:</span>
            <span class="sf-fill-value">{{
                dash(eligibility.year_level_applying)
            }}</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    eligibility: { type: Object, default: () => ({}) },
});

const dash = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }
    return value;
};
</script>
