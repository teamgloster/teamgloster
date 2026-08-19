<template>
    <div class="sf-avoid-break">
        <h2 class="sf-section-title">
            Scholastic Record
            <span v-if="record.grade"> — {{ record.grade }}</span>
            <span v-if="showSemester && record.semester">
                ({{ record.semester }})
            </span>
        </h2>
        <div class="sf-fill-grid">
            <div class="sf-fill">
                <span class="sf-fill-label">School:</span>
                <span class="sf-fill-value">{{ dash(school.school_name) }}</span>
            </div>
            <div class="sf-fill">
                <span class="sf-fill-label">School ID:</span>
                <span class="sf-fill-value">{{ dash(school.school_id) }}</span>
            </div>
            <div class="sf-fill">
                <span class="sf-fill-label">District / Division / Region:</span>
                <span class="sf-fill-value"
                    >{{ dash(school.district) }} /
                    {{ dash(school.division) }} /
                    {{ dash(school.region) }}</span
                >
            </div>
            <div class="sf-fill">
                <span class="sf-fill-label">School Year:</span>
                <span class="sf-fill-value">{{ dash(record.school_year) }}</span>
            </div>
            <div class="sf-fill">
                <span class="sf-fill-label">Classified as Grade:</span>
                <span class="sf-fill-value">{{
                    dash(record.classified_as)
                }}</span>
            </div>
            <div class="sf-fill">
                <span class="sf-fill-label">Section / Adviser:</span>
                <span class="sf-fill-value">
                    {{ dash(record.section)
                    }}<span v-if="record.adviser"> / {{ record.adviser }}</span>
                </span>
            </div>
        </div>
        <table class="sf-table" style="margin-top: 4px">
            <thead>
                <tr>
                    <th style="width: 36%">Subject</th>
                    <th>Term 1</th>
                    <th>Term 2</th>
                    <th>Term 3</th>
                    <th>Final Rating</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <template
                    v-for="(group, gIndex) in record.groups"
                    :key="gIndex"
                >
                    <tr v-if="record.groups.length > 1">
                        <td colspan="6" class="sf-value">{{ group.label }}</td>
                    </tr>
                    <tr
                        v-for="(subject, sIndex) in group.subjects"
                        :key="`${gIndex}-${sIndex}`"
                    >
                        <td>{{ subject.name }}</td>
                        <td class="sf-center">{{ dash(subject.term_1) }}</td>
                        <td class="sf-center">{{ dash(subject.term_2) }}</td>
                        <td class="sf-center">{{ dash(subject.term_3) }}</td>
                        <td class="sf-center">{{ dash(subject.final) }}</td>
                        <td class="sf-center">{{ dash(subject.remarks) }}</td>
                    </tr>
                </template>
                <tr>
                    <td class="sf-right sf-value">General Average</td>
                    <td colspan="3" class="sf-center sf-muted">
                        {{ dash(record.general_average_descriptor) }}
                    </td>
                    <td class="sf-center sf-value">
                        {{ dash(record.general_average) }}
                    </td>
                    <td class="sf-center sf-value">
                        {{ dash(record.remarks) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({
    record: { type: Object, required: true },
    school: { type: Object, required: true },
    showSemester: { type: Boolean, default: false },
});

const dash = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }
    return value;
};
</script>
