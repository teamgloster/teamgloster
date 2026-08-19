<template>
    <div class="sf-fill-grid">
        <div class="sf-fill">
            <span class="sf-fill-label">LRN:</span>
            <span class="sf-fill-value">{{ dash(learner.lrn) }}</span>
        </div>
        <div class="sf-fill">
            <span class="sf-fill-label">Sex:</span>
            <span class="sf-fill-value">{{ dash(learner.sex) }}</span>
        </div>
        <div class="sf-fill wide">
            <span class="sf-fill-label">Learner's Name (Last, First, Middle):</span>
            <span class="sf-fill-value">{{ dash(learner.full_name) }}</span>
        </div>
        <div class="sf-fill">
            <span class="sf-fill-label">Date of Birth:</span>
            <span class="sf-fill-value">{{ dash(learner.birth_date) }}</span>
        </div>
        <div class="sf-fill">
            <span class="sf-fill-label">Age (as of August 1):</span>
            <span class="sf-fill-value">{{ dash(learner.age) }}</span>
        </div>
        <div class="sf-fill">
            <span class="sf-fill-label">Name of Father:</span>
            <span class="sf-fill-value">{{ dash(family.father) }}</span>
        </div>
        <div class="sf-fill">
            <span class="sf-fill-label">Name of Mother:</span>
            <span class="sf-fill-value">{{ dash(family.mother) }}</span>
        </div>
        <div class="sf-fill wide">
            <span class="sf-fill-label">Address:</span>
            <span class="sf-fill-value">{{ dash(learner.address) }}</span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    learner: { type: Object, required: true },
    family: { type: Object, default: () => ({}) },
});

const dash = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }
    return value;
};
</script>
