<template>
    <SchoolFormShell
        :user="user"
        :viewer="viewer"
        :title="`SF10 · ${form.title}`"
        page-title="SF10"
        :back-url="backUrl"
    >
        <section v-if="show_jhs" class="sf-sheet">
                <FormLetterhead :school="school" form-code="SF10-JHS" />
                <h1 class="sf-form-title">{{ form.title }}</h1>
                <p class="sf-form-sub">
                    Junior High School · {{ form.former }} · {{ form.legal }}
                </p>
                <Sf10LearnerBlock :learner="learner" :family="family" />
                <Sf10EligibilityBlock
                    title="Eligibility for JHS (Elementary School Completer)"
                    :eligibility="eligibility"
                />
                <Sf10RecordBlock
                    v-for="(record, index) in jhs_records"
                    :key="`jhs-${index}`"
                    :record="record"
                    :school="school"
                />
                <p v-if="jhs_records.length === 0" class="sf-note">
                    No Junior High School enrollment record is stored yet.
                    Academic progress tables will appear after the learner is
                    enrolled in Grades 7–10.
                </p>
                <Sf10CertificationBlock
                    :learner="learner"
                    :school-head="school_head || school.school_head"
                    :next-grade="next_grade"
                />
            </section>

            <section
                v-if="show_shs"
                class="sf-sheet"
                :class="{ 'sf-page-break': show_jhs }"
            >
                <FormLetterhead :school="school" form-code="SF10-SHS" />
                <h1 class="sf-form-title">{{ form.title }}</h1>
                <p class="sf-form-sub">
                    Senior High School · {{ form.former }} · {{ form.legal }}
                </p>
                <Sf10LearnerBlock :learner="learner" :family="family" />
                <Sf10EligibilityBlock
                    title="Eligibility for SHS (Junior High School Completer)"
                    :eligibility="eligibility"
                />
                <Sf10RecordBlock
                    v-for="(record, index) in shs_records"
                    :key="`shs-${index}`"
                    :record="record"
                    :school="school"
                    show-semester
                />
                <p v-if="shs_records.length === 0" class="sf-note">
                    No Senior High School enrollment record is stored yet.
                    Academic progress tables will appear after the learner is
                    enrolled in Grades 11–12.
                </p>
                <Sf10CertificationBlock
                    :learner="learner"
                    :school-head="school_head || school.school_head"
                    :next-grade="next_grade"
                />
            </section>
    </SchoolFormShell>
</template>

<script setup>
import SchoolFormShell from "./SchoolFormShell.vue";
import FormLetterhead from "./FormLetterhead.vue";
import Sf10LearnerBlock from "./Sf10LearnerBlock.vue";
import Sf10EligibilityBlock from "./Sf10EligibilityBlock.vue";
import Sf10RecordBlock from "./Sf10RecordBlock.vue";
import Sf10CertificationBlock from "./Sf10CertificationBlock.vue";

const props = defineProps({
    user: { type: Object, required: true },
    form: { type: Object, required: true },
    school: { type: Object, required: true },
    learner: { type: Object, required: true },
    family: { type: Object, default: () => ({}) },
    eligibility: { type: Object, default: () => ({}) },
    show_jhs: { type: Boolean, default: false },
    show_shs: { type: Boolean, default: false },
    jhs_records: { type: Array, default: () => [] },
    shs_records: { type: Array, default: () => [] },
    school_head: { type: String, default: null },
    next_grade: { type: String, default: null },
    backUrl: { type: String, default: "/" },
    viewer: { type: String, default: "admin" },
});
</script>

<style>
@import "@/Styles/school-form.css";
</style>
