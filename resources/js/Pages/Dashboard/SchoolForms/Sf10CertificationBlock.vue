<template>
    <h2 class="sf-section-title">Certification</h2>
    <p class="sf-note" style="font-size: 10.5px">
        I certify that this is a true record of
        <strong>{{ dash(learner.full_name) }}</strong>
        with LRN <strong>{{ dash(learner.lrn) }}</strong
        >. This learner is eligible for transfer and admission to
        <strong>{{ dash(nextGrade) }}</strong>
        and has no property responsibility in this school.
    </p>
    <div class="sf-sign-row">
        <div class="sf-sign">
            <div class="sf-sign-line">Date</div>
            <div class="sf-sign-role">Date Issued</div>
        </div>
        <div class="sf-sign">
            <div class="sf-sign-line">{{ dash(schoolHead) }}</div>
            <div class="sf-sign-role">School Head</div>
        </div>
        <div class="sf-seal-box">Dry seal</div>
    </div>
    <p class="sf-note">
        Ratings follow DepEd Order No. 8, s. 2015. This school uses three
        grading periods (Terms 1–3). Final ratings and the general average are
        reported as whole numbers. Passing mark is 75.
    </p>
</template>

<script setup>
defineProps({
    learner: { type: Object, required: true },
    schoolHead: { type: String, default: null },
    nextGrade: { type: String, default: null },
});

const dash = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }
    return value;
};
</script>
