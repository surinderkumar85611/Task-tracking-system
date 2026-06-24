<template>

    <div class="tl-hierarchy-block-card" :draggable="isDraggable" :class="{ 'draggable-card': isDraggable }"
        @dragstart="startDrag($event, member)" @dragover.prevent @drop="handleDrop">

        <div class="tl-card-info-header">

            <div class="avatar-circle-initials">
                {{ getInitials(member.first_name, member.last_name) }}
            </div>

            <div class="tl-details-column">

                <h3>
                    {{ member.first_name }}
                    {{ member.last_name }}
                    &nbsp;
                    <span class="role-pill-tag tl-badge">
                        {{
                            member.role === 'TL'
                                ? `TL${member.level}`
                                : 'Member'
                        }}
                    </span>
                </h3>

                <span class="role-pill-tag tl-badge">
                    {{ member.department || member.role }}
                </span>

            </div>

            <div class="tl-meta-right">
                <span class="count-badge">
                    {{ getTotalTeamCount(member) }}
                    Members
                </span>
            </div>
        </div>

        <div class="subordinates-list-segment">

            <div v-if="member.team_members?.length" class="subordinates-flex-grid">
                <template v-for="child in member.team_members" :key="child.id">

                    <!-- MEMBER -->
                    <div v-if="child.role === 'Member'" class="member-sub-pill-row" draggable="true"
                        @dragstart="startDrag($event, child)">
                        <div class="mini-avatar-dot">
                            {{ getInitials(child.first_name, child.last_name) }}
                        </div>

                        <div class="member-sub-meta">
                            <span class="sub-name">
                                {{ child.first_name }}
                                {{ child.last_name }}
                            </span>

                            <span class="sub-email">
                                {{ child.department }}
                            </span>
                        </div>
                    </div>

                    <!-- TL -->
                    <TeamNode v-else :member="child" @drag-member="$emit('drag-member', $event)"
                        @drop-member="$emit('drop-member', $event)" @drop-leader="$emit('drop-leader', $event)" />

                </template>

                <div v-if="member.role === 'TL'" class="empty-member-slot" @dragover.prevent @drop="handleDrop">
                    +
                </div>
            </div>

            <div v-else class="empty-subordinates-state">
                🍃 Drag and drop members here
            </div>

        </div>

    </div>

</template>

<script setup>
import { computed } from 'vue';
import TeamNode from "./TeamNode.vue";

const props = defineProps({
    member: Object
});

const emit = defineEmits([
    'drag-member',
    'drop-member',
    'drop-leader'
]);

const isDraggable = computed(() => {
    if (props.member.role === 'Member') {
        return true;
    }

    return props.member.level != 3;
});

const handleDrop = (e) => {
    const type = e.dataTransfer.getData('type');

    if (type === 'leader') {
        emit('drop-leader', props.member.id);
    } else {
        emit('drop-member', props.member.id);
    }
};

const startDrag = (event, item) => {

    event.stopPropagation();

    event.dataTransfer.setData(
        'type',
        item.role === 'TL'
            ? 'leader'
            : 'member'
    );

    emit('drag-member', item);
};

const getInitials = (first, last) => {
    return `${first?.[0] || ''}${last?.[0] || ''}`.toUpperCase();
};

const getTotalTeamCount = (node) => {

    if (!node.team_members?.length) {
        return 0;
    }

    return node.team_members.reduce(
        (total, child) => {
            return total + 1 + getTotalTeamCount(child);
        },
        0
    );
};
</script>

<style scoped>
.empty-member-slot {
    width: 70px;
    height: 70px;

    border: 2px dashed #6366f1;
    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 28px;
    font-weight: bold;

    color: #6366f1;
    cursor: pointer;
}

.draggable-card {
    cursor: grab;
    width: 100%;
}

.draggable-card:active {
    cursor: grabbing;
}
</style>
