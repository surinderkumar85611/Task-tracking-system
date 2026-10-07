<template>

    <div class="tl-hierarchy-block-card" @dragover.prevent @drop="handleDrop"
        @click.stop="$emit('edit-member', member)">

        <div class="tl-card-info-header">



            <div class="avatar-circle-initials">
                {{ getInitials(member.first_name, member.last_name) }}
            </div>

            <div class="tl-details-column">

                <h3>
                    {{ member.first_name }}
                    {{ member.last_name }}

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
            <!-- DRAG HANDLE ONLY -->
            <div class="drag-handle" draggable="true" title="Drag to move" @dragstart="startDrag($event, member)"
                @click.stop>
                ⠿
            </div>
        </div>


        <div class="subordinates-list-segment">

            <div v-if="member.team_members?.length" class="subordinates-flex-grid">

                <template v-for="child in member.team_members" :key="child.id">

                    <!-- MEMBER -->
                    <div v-if="child.role === 'Member'" class="member-sub-pill-row"
                        @click.stop="$emit('edit-member', child)">

                        <button class="member-delete-btn" @mousedown.stop @click.stop="$emit('remove-member', child)">
                            ×
                        </button>

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
                        <!-- DRAG HANDLE -->
                        <div class="member-drag-handle" draggable="true" title="Drag member"
                            @dragstart="startDrag($event, child)" @click.stop>
                            ⠿
                        </div>
                    </div>


                    <!-- TEAM LEADER -->
                    <TeamNode v-else :member="child" @drag-member="$emit('drag-member', $event)"
                        @drop-member="$emit('drop-member', $event)" @drop-leader="$emit('drop-leader', $event)"
                        @remove-member="$emit('remove-member', $event)" @edit-member="$emit('edit-member', $event)" />

                </template>


                <!-- DROP SLOT -->
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

const props = defineProps({
    member: {
        type: Object,
        required: true
    }
});

const emit = defineEmits([
    'drag-member',
    'drop-member',
    'drop-leader',
    'remove-member',
    'edit-member'
]);


const handleDrop = (event) => {

    const type = event.dataTransfer.getData('type');

    if (type === 'leader') {

        emit(
            'drop-leader',
            props.member.id
        );

    } else {

        emit(
            'drop-member',
            props.member.id
        );

    }
};


const startDrag = (event, item) => {

    event.stopPropagation();

    event.dataTransfer.effectAllowed = 'move';

    event.dataTransfer.setData(
        'type',
        item.role === 'TL'
            ? 'leader'
            : 'member'
    );

    emit('drag-member', item);
};


const getInitials = (first, last) => {

    return `${first?.[0] || ''}${last?.[0] || ''}`
        .toUpperCase();

};


const getTotalTeamCount = (node) => {

    if (!node.team_members?.length) {
        return 0;
    }

    return node.team_members.reduce(
        (total, child) => {

            return total +
                1 +
                getTotalTeamCount(child);

        },
        0
    );

};

</script>


<style scoped>
.tl-hierarchy-block-card {
    position: relative;
    width: 100%;
    cursor: pointer;
}


/* =========================
   DRAG HANDLE
========================= */

.drag-handle {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 7px;

    color: #94a3b8;
    background: rgba(148, 163, 184, 0.08);

    cursor: grab;

    font-size: 20px;

    user-select: none;

    flex-shrink: 0;
}

.drag-handle:hover {
    color: #818cf8;
    background: rgba(99, 102, 241, 0.15);
}

.drag-handle:active {
    cursor: grabbing;
}


/* =========================
   MEMBER DRAG HANDLE
========================= */

.member-drag-handle {
    width: 24px;
    height: 24px;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #94a3b8;

    cursor: grab;

    font-size: 16px;

    flex-shrink: 0;
}

.member-drag-handle:hover {
    color: #818cf8;
}

.member-drag-handle:active {
    cursor: grabbing;
}


/* =========================
   MEMBER CARD
========================= */

.member-sub-pill-row {
    position: relative;

    cursor: pointer;

    transition: .2s;
}

.member-sub-pill-row:hover {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.06);
}


/* =========================
   DELETE
========================= */

.member-delete-btn {
    position: absolute;

    top: -6px;
    right: -6px;

    width: 20px;
    height: 20px;

    border: none;
    border-radius: 50%;

    background: #ef4444;
    color: white;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 14px;
    font-weight: bold;

    opacity: 0;

    transition: .2s;

    z-index: 5;
}

.member-sub-pill-row:hover .member-delete-btn {
    opacity: 1;
}


/* =========================
   DROP SLOT
========================= */

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
</style>
