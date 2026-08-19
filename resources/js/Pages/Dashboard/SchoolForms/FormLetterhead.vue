<template>
    <div class="sf-letterhead">
        <img :src="logo" alt="School seal" class="sf-seal" />
        <div class="sf-letterhead-text">
            <div class="sf-rp">Republic of the Philippines</div>
            <div class="sf-deped">Department of Education</div>
            <div v-if="school.region" class="sf-office">{{ school.region }}</div>
            <div v-if="school.division" class="sf-office">
                {{ school.division }}
            </div>
            <div v-if="school.district" class="sf-office">
                {{ school.district }}
            </div>
            <div class="sf-school-name">{{ school.school_name }}</div>
            <div v-if="school.address" class="sf-office">
                {{ school.address }}
            </div>
            <div v-if="school.school_id" class="sf-office">
                School ID: {{ school.school_id }}
            </div>
        </div>
        <div class="sf-form-badge">
            <div>{{ formCode }}</div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    school: { type: Object, required: true },
    formCode: { type: String, required: true },
});

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";
</script>
