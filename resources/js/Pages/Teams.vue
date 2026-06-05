<template>
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">

            <!-- PAGE HEADER -->
            <header class="page-header">

                <div>
                    <h1>Teams</h1>
                    <p>
                        Organize members into teams, assign leaders,
                        monitor workload and performance.
                    </p>
                </div>

                <div class="header-actions">

                    <button
                        class="theme-btn"
                        @click="theme.toggleTheme"
                    >
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>

                    <button
                        class="create-team-btn"
                        @click="showCreateTeamModal = true"
                    >
                        + Create Team
                    </button>

                </div>

            </header>

            <!-- ANALYTICS -->
            <section class="analytics-grid">

                <div class="stat-card">
                    <h3>Total Teams</h3>
                    <span>{{ teams.length }}</span>
                </div>

                <div class="stat-card">
                    <h3>Total Members</h3>
                    <span>{{ totalMembers }}</span>
                </div>

                <div class="stat-card">
                    <h3>Active Tasks</h3>
                    <span>{{ totalTasks }}</span>
                </div>

                <div class="stat-card">
                    <h3>Completion Rate</h3>
                    <span>{{ completionRate }}%</span>
                </div>

            </section>

            <!-- CREATE TEAM MODAL -->
            <div
                v-if="showCreateTeamModal"
                class="modal-overlay"
                @click.self="showCreateTeamModal = false"
            >
                <div class="modal">

                    <div class="modal-header">
                        <h2>Create Team</h2>

                        <button
                            class="close-btn"
                            @click="showCreateTeamModal = false"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Team Name</label>

                        <input
                            v-model="teamForm.name"
                            type="text"
                            placeholder="Frontend Team"
                        />
                    </div>

                    <div class="form-group">
                        <label>Description</label>

                        <textarea
                            v-model="teamForm.description"
                            placeholder="Describe team purpose..."
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label>Team Leader</label>

                        <select v-model="teamForm.leader_id">

                            <option value="">
                                Select Team Leader
                            </option>

                            <option
                                v-for="leader in teamLeaders"
                                :key="leader.id"
                                :value="leader.id"
                            >
                                {{ leader.first_name }}
                                {{ leader.last_name }}
                            </option>

                        </select>
                    </div>

                    <button
                        class="save-btn"
                        @click="createTeam"
                    >
                        Create Team
                    </button>

                </div>
            </div>

            <!-- TEAM CARDS -->
            <section class="teams-grid">

                <div
                    v-for="team in teams"
                    :key="team.id"
                    class="team-card"
                    @click="selectTeam(team)"
                >

                    <div class="team-top">

                        <div>
                            <h3>{{ team.name }}</h3>

                            <p>
                                {{ team.description }}
                            </p>
                        </div>

                        <div class="team-badge">
                            {{ team.members?.length || 0 }}
                        </div>

                    </div>

                    <div class="team-leader">

                        <span>
                            👑 Team Leader
                        </span>

                        <strong>
                            {{ team.leader?.first_name }}
                            {{ team.leader?.last_name }}
                        </strong>

                    </div>

                    <div class="team-stats">

                        <div>
                            <small>Projects</small>

                            <strong>
                                {{ team.projects_count || 0 }}
                            </strong>
                        </div>

                        <div>
                            <small>Tasks</small>

                            <strong>
                                {{ team.tasks_count || 0 }}
                            </strong>
                        </div>

                        <div>
                            <small>Completed</small>

                            <strong>
                                {{ team.completed_tasks || 0 }}
                            </strong>
                        </div>

                    </div>

                </div>

            </section>

            <!-- TEAM DETAILS PANEL -->

            <section
                v-if="selectedTeam"
                class="team-details"
            >

                <div class="details-header">

                    <div>
                        <h2>
                            {{ selectedTeam.name }}
                        </h2>

                        <p>
                            {{ selectedTeam.description }}
                        </p>
                    </div>

                    <button class="edit-btn">
                        Edit Team
                    </button>

                </div>
                                <div class="leader-section">

                    <div class="section-card">

                        <h3>Team Leader</h3>

                        <div class="leader-profile">

                            <div class="avatar-circle">
                                {{
                                    selectedTeam.leader?.first_name?.charAt(0)
                                }}{{
                                    selectedTeam.leader?.last_name?.charAt(0)
                                }}
                            </div>

                            <div>

                                <h4>
                                    {{ selectedTeam.leader?.first_name }}
                                    {{ selectedTeam.leader?.last_name }}
                                </h4>

                                <p>
                                    {{ selectedTeam.leader?.department }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="details-grid">

                    <div class="members-section">

                        <div class="section-card">

                            <div class="section-title">
                                <h3>Team Members</h3>

                                <span>
                                    {{
                                        selectedTeam.members?.length || 0
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="selectedTeam.members?.length"
                                class="member-list"
                            >

                                <div
                                    v-for="member in selectedTeam.members"
                                    :key="member.id"
                                    class="member-row"
                                >

                                    <div class="avatar-circle small">
                                        {{ member.first_name.charAt(0) }}
                                        {{ member.last_name.charAt(0) }}
                                    </div>

                                    <div>

                                        <strong>
                                            {{ member.first_name }}
                                            {{ member.last_name }}
                                        </strong>

                                        <p>
                                            {{ member.department }}
                                        </p>

                                    </div>

                                </div>

                            </div>

                            <div
                                v-else
                                class="empty-state"
                            >
                                No members assigned.
                            </div>

                        </div>

                    </div>

                    <div class="drag-section">

                        <div class="section-card">

                            <div class="section-title">
                                <h3>Assign Members</h3>
                            </div>

                            <div class="drag-layout">

                                <div class="available-members">

                                    <h4>
                                        Available Members
                                    </h4>

                                    <div
                                        v-for="member in availableMembers"
                                        :key="member.id"
                                        class="drag-member"
                                        draggable="true"
                                        @dragstart="dragMember(member)"
                                    >

                                        <div class="avatar-circle small">
                                            {{ member.first_name.charAt(0) }}
                                            {{ member.last_name.charAt(0) }}
                                        </div>

                                        <span>
                                            {{ member.first_name }}
                                            {{ member.last_name }}
                                        </span>

                                    </div>

                                </div>

                                <div
                                    class="drop-zone"
                                    @dragover.prevent
                                    @drop="dropMember(selectedTeam.id)"
                                >

                                    <h4>
                                        Drop Here
                                    </h4>

                                    <p>
                                        Drag members into this team
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="performance-grid">

                    <div class="performance-card">

                        <h4>
                            Total Tasks
                        </h4>

                        <span>
                            {{
                                selectedTeam.tasks_count || 0
                            }}
                        </span>

                    </div>

                    <div class="performance-card">

                        <h4>
                            Completed Tasks
                        </h4>

                        <span>
                            {{
                                selectedTeam.completed_tasks || 0
                            }}
                        </span>

                    </div>

                    <div class="performance-card">

                        <h4>
                            Pending Tasks
                        </h4>

                        <span>
                            {{
                                selectedTeam.pending_tasks || 0
                            }}
                        </span>

                    </div>

                    <div class="performance-card">

                        <h4>
                            Team Efficiency
                        </h4>

                        <span>
                            {{
                                selectedTeam.efficiency || 0
                            }}%
                        </span>

                    </div>

                </div>

                <div class="workload-section">

                    <div class="section-card">

                        <div class="section-title">
                            <h3>
                                Team Workload Statistics
                            </h3>
                        </div>

                        <div class="workload-grid">

                            <div class="workload-box">

                                <small>
                                    Active Projects
                                </small>

                                <h2>
                                    {{
                                        selectedTeam.projects_count || 0
                                    }}
                                </h2>

                            </div>

                            <div class="workload-box">

                                <small>
                                    Assigned Members
                                </small>

                                <h2>
                                    {{
                                        selectedTeam.members?.length || 0
                                    }}
                                </h2>

                            </div>

                            <div class="workload-box">

                                <small>
                                    Completion Rate
                                </small>

                                <h2>
                                    {{
                                        selectedTeam.efficiency || 0
                                    }}%
                                </h2>

                            </div>

                            <div class="workload-box">

                                <small>
                                    Open Tasks
                                </small>

                                <h2>
                                    {{
                                        selectedTeam.pending_tasks || 0
                                    }}
                                </h2>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>
</template>
<script setup>
import { ref, reactive, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import Sidebar from "./components/Sidebar.vue";
import { useThemeStore } from "../stores/theme";

const toast = useToast();
const theme = useThemeStore();

const props = defineProps({
    teams: {
        type: Array,
        default: () => [],
    },

    members: {
        type: Array,
        default: () => [],
    },

    teamLeaders: {
        type: Array,
        default: () => [],
    },
});

const showCreateTeamModal = ref(false);
const selectedTeam = ref(null);
const draggedMember = ref(null);

const teamForm = reactive({
    name: "",
    description: "",
    leader_id: "",
});

const totalMembers = computed(() => {
    return props.members.length;
});

const totalTasks = computed(() => {
    return props.teams.reduce((total, team) => {
        return total + (team.tasks_count || 0);
    }, 0);
});

const completionRate = computed(() => {
    const completed = props.teams.reduce((total, team) => {
        return total + (team.completed_tasks || 0);
    }, 0);

    const tasks = props.teams.reduce((total, team) => {
        return total + (team.tasks_count || 0);
    }, 0);

    if (!tasks) return 0;

    return Math.round((completed / tasks) * 100);
});

const availableMembers = computed(() => {
    return props.members.filter(member => {
        return !member.team_id;
    });
});

const selectTeam = (team) => {
    selectedTeam.value = team;
};

const dragMember = (member) => {
    draggedMember.value = member;
};

const dropMember = (teamId) => {

    if (!draggedMember.value) {
        return;
    }

    router.put(
        `/teams/${teamId}/assign-member`,
        {
            member_id: draggedMember.value.id,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    "Member assigned successfully"
                );

                draggedMember.value = null;
            },

            onError: () => {
                toast.error(
                    "Failed to assign member"
                );
            },
        }
    );
};

const createTeam = () => {

    if (
        !teamForm.name ||
        !teamForm.description ||
        !teamForm.leader_id
    ) {
        toast.error(
            "Please complete all fields"
        );
        return;
    }

    router.post(
        "/team",
        teamForm,
        {
            preserveScroll: true,

            onSuccess: () => {

                toast.success(
                    "Team created successfully"
                );

                teamForm.name = "";
                teamForm.description = "";
                teamForm.leader_id = "";

                showCreateTeamModal.value = false;
            },

            onError: (errors) => {

                const firstError =
                    Object.values(errors)[0];

                toast.error(
                    firstError ||
                    "Failed to create team"
                );
            },
        }
    );
};

if (props.teams.length) {
    selectedTeam.value = props.teams[0];
}
</script>
<style scoped>
.dashboard {
    display: flex;
    min-height: 100vh;
    background: var(--bg);
    color: var(--text);
}

.main-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 6px;
}

.page-header p {
    color: var(--subtext);
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.theme-btn,
.create-team-btn {
    border: none;
    cursor: pointer;
    border-radius: 12px;
    font-weight: 600;
}

.theme-btn {
    width: 48px;
    height: 48px;
    background: var(--card);
    border: 1px solid var(--border);
}

.create-team-btn {
    background: #06b6d4;
    color: #0f172a;
    padding: 12px 20px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 24px;
}

.stat-card h3 {
    color: var(--subtext);
    font-size: 14px;
    margin-bottom: 12px;
}

.stat-card span {
    font-size: 32px;
    font-weight: 700;
}

.teams-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.team-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 22px;
    cursor: pointer;
    transition: .25s ease;
}

.team-card:hover {
    transform: translateY(-4px);
}

.team-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.team-top h3 {
    margin-bottom: 8px;
}

.team-top p {
    color: var(--subtext);
    font-size: 14px;
}

.team-badge {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #06b6d4;
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.team-leader {
    display: flex;
    justify-content: space-between;
    margin-bottom: 18px;
}

.team-leader span {
    color: var(--subtext);
}

.team-stats {
    display: flex;
    justify-content: space-between;
}

.team-stats small {
    display: block;
    color: var(--subtext);
    margin-bottom: 4px;
}

.team-details {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.details-header {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.edit-btn {
    border: none;
    background: #06b6d4;
    color: #0f172a;
    padding: 12px 18px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
}

.section-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
}

.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #06b6d4;
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.avatar-circle.small {
    width: 42px;
    height: 42px;
    font-size: 13px;
}

.leader-profile {
    display: flex;
    align-items: center;
    gap: 16px;
}

.member-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.member-row {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--bg);
    border-radius: 14px;
    padding: 12px;
}

.member-row p {
    color: var(--subtext);
    font-size: 13px;
}

.drag-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.available-members h4,
.drop-zone h4 {
    margin-bottom: 16px;
}

.drag-member {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--bg);
    border-radius: 14px;
    margin-bottom: 12px;
    cursor: grab;
}

.drop-zone {
    min-height: 280px;
    border: 2px dashed var(--border);
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: var(--subtext);
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.performance-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 22px;
    text-align: center;
}

.performance-card h4 {
    color: var(--subtext);
    margin-bottom: 12px;
}

.performance-card span {
    font-size: 30px;
    font-weight: 700;
}

.workload-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.workload-box {
    background: var(--bg);
    border-radius: 16px;
    padding: 20px;
}

.workload-box small {
    color: var(--subtext);
}

.workload-box h2 {
    margin-top: 10px;
    font-size: 28px;
}

.empty-state {
    text-align: center;
    color: var(--subtext);
    padding: 30px;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(8px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal {
    width: 500px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 24px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 24px;
}

.close-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text);
    font-size: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 18px;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 600;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
}

.form-group textarea {
    min-height: 120px;
    resize: none;
}

.save-btn {
    width: 100%;
    border: none;
    background: #06b6d4;
    color: #0f172a;
    padding: 14px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
}

@media (max-width: 1200px) {
    .analytics-grid,
    .performance-grid,
    .workload-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .details-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .analytics-grid,
    .performance-grid,
    .workload-grid,
    .teams-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .drag-layout {
        grid-template-columns: 1fr;
    }
}
</style>