<template>

    <div class="hierarchy-directory-wrapper">

        <TeamNode v-for="leader in leaders" :key="leader.id" :member="leader"
            @drop-member="$emit('drop-member', $event)" @drop-leader="$emit('drop-leader', $event)" />

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

defineEmits([
    'drop-member',
    'drop-leader',
    'assign-workspace'
]);
</script>
