<template>

    <div class="hierarchy-directory-wrapper">

        <TeamNode v-for="leader in leaders" :key="leader.id" :member="leader"
            @drag-member="$emit('drag-member', $event)" @drop-member="$emit('drop-member', $event)"
            @drop-leader="$emit('drop-leader', $event)" @remove-member="$emit('remove-member', $event)" />

        <div class="assign-tl-zone" @dragover.prevent @drop="$emit('assign-workspace')">
            ➕ Drop Team Leader Here
        </div>

    </div>

</template>

<script setup>
import TeamNode from './TeamNode.vue';

defineProps({
    leaders: Array,
    currentWorkspace: Number
});

const emit = defineEmits([
    'drag-member',
    'drop-member',
    'drop-leader',
    'assign-workspace',
    'remove-member'
]);
</script>
