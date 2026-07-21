<template>

    <Head title="My Projects" />
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">
            <div class="content-wrapper">

                <header class="header">
                    <div>
                        <h1>My Projects</h1>
                        <p>Projects you're contributing to and their task breakdown.</p>
                    </div>

                    <div class="header-right">
                        <div class="search-wrap">
                            <svg class="search-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M17 17L13.5 13.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                            <input type="text" v-model="search" placeholder="Search projects..." class="search-box" />
                        </div>

                        <button class="theme-btn" @click="theme.toggleTheme">
                            {{ theme.isDark ? "☀️" : "🌙" }}
                        </button>
                    </div>
                </header>

                <!-- STATS -->
                <section class="stats-grid">

                    <div class="stat-card projects-card">
                        <div class="stat-icon-badge">📁</div>
                        <span class="stat-label">Total Projects</span>
                        <h2 class="stat-value">{{ totalProjects }}</h2>
                        <small class="stat-subtitle">Projects you're assigned to</small>
                    </div>

                    <div class="stat-card in-progress-card">
                        <div class="stat-icon-badge">🔄</div>
                        <span class="stat-label">In Progress</span>
                        <h2 class="stat-value">{{ inProgressCount }}</h2>
                        <small class="stat-subtitle">Tasks currently active</small>
                    </div>

                    <div class="stat-card completed-card">
                        <div class="stat-icon-badge">✅</div>
                        <span class="stat-label">Completed</span>
                        <h2 class="stat-value">{{ completedCount }}</h2>
                        <small class="stat-subtitle">Tasks finished</small>
                    </div>

                    <div class="stat-card pending-card">
                        <div class="stat-icon-badge">⏳</div>
                        <span class="stat-label">Pending</span>
                        <h2 class="stat-value">{{ pendingCount }}</h2>
                        <small class="stat-subtitle">Tasks not yet started</small>
                    </div>

                </section>

                <!-- PROJECTS -->
                <div class="projects-list">

                    <div v-if="!filteredProjects.length" class="empty-board-state">
                        <h3>No projects match your search</h3>
                    </div>

                    <div v-for="project in filteredProjects" :key="project.id" class="project-card">

                        <div class="project-card-top">
                            <div class="project-title-block">
                                <h2>{{ project.name }}</h2>
                                <span class="status-pill" :class="statusClass(project.status)">
                                    {{ project.status }}
                                </span>
                            </div>

                            <div class="project-progress-block">
                                <span class="progress-percent">{{ projectProgress(project) }}%</span>
                                <span class="progress-caption">Complete</span>
                            </div>
                        </div>

                        <p class="project-desc" v-if="project.description">
                            {{ project.description }}
                        </p>

                        <div class="mini-progress">
                            <div class="mini-progress-fill" :style="{ width: projectProgress(project) + '%' }"></div>
                        </div>

                        <div class="project-meta-row">
                            <span v-if="project.team_leader">
                                👤 Led by {{ project.team_leader.first_name }} {{ project.team_leader.last_name }}
                            </span>
                            <span>📅 Due: {{ project.deadline || 'No deadline' }}</span>
                            <span>📋 {{ (project.tasks || []).length }} Tasks</span>
                            <span>✅ {{ projectCompletedCount(project) }} Completed</span>
                        </div>

                        <div class="project-tasks-table" v-if="(project.tasks || []).length">
                            <div class="tasks-table-head">
                                <span>Task</span>
                                <span>Assigned</span>
                                <span>Priority</span>
                                <span>Status</span>
                                <span>Due</span>
                            </div>

                            <div v-for="task in project.tasks" :key="task.id" class="tasks-row">
                                <span class="task-title-cell">{{ task.title }}</span>

                                <span class="assignee-cell">
                                    <span
                                        v-for="member in (task.assigned_members || [])"
                                        :key="member.id"
                                        class="mini-avatar"
                                        :title="`${member.first_name} ${member.last_name || ''}`.trim()"
                                    >
                                        {{ member.first_name?.charAt(0)?.toUpperCase() || '?' }}
                                    </span>
                                    <span v-if="!(task.assigned_members || []).length" class="unassigned-label">
                                        Unassigned
                                    </span>
                                </span>

                                <span class="priority-badge" :class="priorityClass(task.priority)">
                                    {{ task.priority || 'Normal' }}
                                </span>

                                <span class="status-pill" :class="statusClass(task.status)">
                                    {{ task.status || 'Todo' }}
                                </span>

                                <span class="due-date-cell">
                                    {{ task.due_date || task.deadline || '—' }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="empty-state-inline">
                            No tasks in this project yet.
                        </div>

                    </div>

                </div>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import Sidebar from "./Sidebar.vue";
import { useThemeStore } from "../../stores/theme.js";
import { Head } from "@inertiajs/vue3";

const theme = useThemeStore();

const props = defineProps({
    projects: { type: Array, default: () => [] },
});

const search = ref("");

const filteredProjects = computed(() => {
    if (!search.value) return props.projects || [];
    const term = search.value.toLowerCase();
    return (props.projects || []).filter(p => p.name?.toLowerCase().includes(term));
});

const allTasks = computed(() => {
    return (props.projects || []).flatMap(p => p.tasks || []);
});

const statusClass = (status) => {
    return (status || "").toLowerCase().replace(/\s+/g, "-");
};

const priorityClass = (priority) => {
    return (priority || "normal").toLowerCase();
};

const isCompleted = (task) => (task.status || "").toLowerCase() === "completed";

const isInProgress = (task) => {
    const s = (task.status || "").toLowerCase();
    return s === "in progress" || s === "in_progress" || s === "in-progress";
};

const isPending = (task) => {
    const s = (task.status || "").toLowerCase();
    return s === "todo" || s === "planning" || !s;
};

const totalProjects = computed(() => (props.projects || []).length);
const inProgressCount = computed(() => allTasks.value.filter(isInProgress).length);
const completedCount = computed(() => allTasks.value.filter(isCompleted).length);
const pendingCount = computed(() => allTasks.value.filter(isPending).length);

const projectCompletedCount = (project) => (project.tasks || []).filter(isCompleted).length;

const projectProgress = (project) => {
    const total = (project.tasks || []).length;
    if (!total) return 0;
    return Math.round((projectCompletedCount(project) / total) * 100);
};
</script>

<style scoped>
/* ==========================================================================
   THEME TOKENS — same palette as Dashboard.vue / Sidebar.vue / Settings.vue
   ========================================================================== */
.dashboard.theme-dark {
    --dashboard-bg: #222736;
    --panel-bg: #2a2f42;
    --card-inner-bg: #262b3d;
    --card-inner-hover: #313749;
    --input-element-bg: #323a4f;
    --border-subtle: rgba(255, 255, 255, 0.07);
    --border-deep: rgba(255, 255, 255, 0.12);
    --border-divider: rgba(255, 255, 255, 0.07);
    --text-main: #e4e6ef;
    --text-header: #f6f7fb;
    --text-muted: #8590a6;
    --shadow-cards: rgba(0, 0, 0, 0.28);
    --shadow-stats: rgba(0, 0, 0, 0.22);
    --shadow-stats-hover: rgba(0, 0, 0, 0.34);
    --accent: #556ee6;
    --accent-soft: rgba(85, 110, 230, 0.16);
    --c-blue: #556ee6;
    --c-violet: #8b6ee8;
    --c-green: #34c38f;
    --c-amber: #f1b44c;
    --c-cyan: #50a5f1;
    --c-red: #f46a6a;
}

.dashboard.theme-light {
    --dashboard-bg: #eef0f7;
    --panel-bg: #ffffff;
    --card-inner-bg: #f7f8fb;
    --card-inner-hover: #eef0f6;
    --input-element-bg: #f2f3f8;
    --border-subtle: rgba(33, 37, 61, 0.07);
    --border-deep: rgba(33, 37, 61, 0.1);
    --border-divider: rgba(33, 37, 61, 0.06);
    --text-main: #33374d;
    --text-header: #22263d;
    --text-muted: #878ea3;
    --shadow-cards: rgba(56, 65, 109, 0.07);
    --shadow-stats: rgba(56, 65, 109, 0.05);
    --shadow-stats-hover: rgba(56, 65, 109, 0.1);
    --accent: #556ee6;
    --accent-soft: rgba(85, 110, 230, 0.08);
    --c-blue: #556ee6;
    --c-violet: #8b6ee8;
    --c-green: #34c38f;
    --c-amber: #f1b44c;
    --c-cyan: #50a5f1;
    --c-red: #e05555;
}

/* ==========================================================================
   LAYOUT
   ========================================================================== */
.dashboard {
    display: flex;
    height: 100vh;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: var(--dashboard-bg);
    color: var(--text-main);
    overflow: hidden;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.main-content {
    flex: 1;
    overflow-y: auto;
    width: 100%;
    height: 100%;
}

.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
    padding: 28px 36px 56px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.header h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 3px 0;
    letter-spacing: -0.2px;
    color: var(--text-header);
}

.header p {
    margin: 0;
    color: var(--text-muted);
    font-size: 13px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    width: 14px;
    height: 14px;
    color: var(--text-muted);
    pointer-events: none;
}

.search-box {
    width: 220px;
    padding: 8px 13px 8px 34px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    outline: none;
    font-size: 13px;
    transition: all 0.2s ease;
}

.search-box::placeholder {
    color: var(--text-muted);
}

.search-box:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.theme-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    cursor: pointer;
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.theme-btn:hover {
    background: var(--card-inner-hover);
    border-color: var(--border-deep);
}

/* ==========================================================================
   STATS
   ========================================================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

@media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .stats-grid { grid-template-columns: 1fr; }
}

.stat-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 18px 20px 20px;
    box-shadow: 0 2px 6px var(--shadow-stats);
    transition: all 0.2s ease;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    height: 3px;
}

.projects-card::before { background: var(--c-violet); }
.in-progress-card::before { background: var(--c-blue); }
.completed-card::before { background: var(--c-green); }
.pending-card::before { background: var(--c-amber); }

.stat-card:hover {
    border-color: var(--border-deep);
    box-shadow: 0 6px 16px -4px var(--shadow-stats-hover);
    transform: translateY(-2px);
}

.stat-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
    margin-bottom: 14px;
}

.projects-card .stat-icon-badge { background: rgba(139, 110, 232, 0.14); }
.in-progress-card .stat-icon-badge { background: rgba(85, 110, 230, 0.14); }
.completed-card .stat-icon-badge { background: rgba(52, 195, 143, 0.14); }
.pending-card .stat-icon-badge { background: rgba(241, 180, 76, 0.16); }

.stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}

.stat-value {
    font-size: 25px;
    font-weight: 700;
    color: var(--text-header);
    margin: 6px 0 0 0;
    line-height: 1.2;
    letter-spacing: -0.3px;
}

.stat-subtitle {
    display: block;
    margin-top: 6px;
    color: var(--text-muted);
    font-size: 11px;
    line-height: 1.4;
}

/* ==========================================================================
   PROJECT CARDS
   ========================================================================== */
.projects-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.project-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 6px var(--shadow-cards);
}

.project-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 10px;
}

.project-title-block {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.project-title-block h2 {
    font-size: 17px;
    font-weight: 700;
    margin: 0;
    color: var(--text-header);
    letter-spacing: -0.2px;
}

.project-progress-block {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}

.progress-percent {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-header);
    line-height: 1;
}

.progress-caption {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 3px;
}

.project-desc {
    color: var(--text-muted);
    font-size: 13px;
    margin: 0 0 14px 0;
    line-height: 1.5;
}

.mini-progress {
    height: 6px;
    background: var(--border-deep);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 14px;
}

.mini-progress-fill {
    height: 100%;
    background: var(--c-violet);
    border-radius: 6px;
}

.project-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    color: var(--text-muted);
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 18px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border-divider);
}

/* ==========================================================================
   STATUS / PRIORITY PILLS
   ========================================================================== */
.status-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 5px;
    text-transform: capitalize;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    flex-shrink: 0;
    white-space: nowrap;
}

.status-pill.in-progress { background: rgba(85, 110, 230, 0.12); color: var(--c-blue); }
.status-pill.completed { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }
.status-pill.todo,
.status-pill.planning { background: rgba(241, 180, 76, 0.15); color: #b9822e; }

.priority-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 5px;
    text-transform: capitalize;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    white-space: nowrap;
}

.priority-badge.high { background: rgba(244, 106, 106, 0.12); color: var(--c-red); }
.priority-badge.medium { background: rgba(241, 180, 76, 0.15); color: #b9822e; }
.priority-badge.low { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }

/* ==========================================================================
   TASKS TABLE (per project)
   ========================================================================== */
.project-tasks-table {
    display: flex;
    flex-direction: column;
}

.tasks-table-head {
    display: grid;
    grid-template-columns: 1.6fr 1.3fr 0.9fr 0.9fr 0.9fr;
    align-items: center;
    padding: 0 4px 10px;
    border-bottom: 1px solid var(--border-divider);
    margin-bottom: 2px;
    gap: 10px;
}

.tasks-table-head span {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}

.tasks-row {
    display: grid;
    grid-template-columns: 1.6fr 1.3fr 0.9fr 0.9fr 0.9fr;
    align-items: center;
    padding: 12px 4px;
    border-bottom: 1px solid var(--border-divider);
    gap: 10px;
    transition: background 0.15s ease;
}

.tasks-row:last-child {
    border-bottom: none;
}

.tasks-row:hover {
    background: var(--card-inner-hover);
}

.task-title-cell {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.assignee-cell {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.mini-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--c-cyan);
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid var(--panel-bg);
    margin-left: -6px;
}

.mini-avatar:first-child {
    margin-left: 0;
}

.mini-avatar:nth-child(even) { background: var(--c-blue); }
.mini-avatar:nth-child(3n) { background: var(--c-violet); }
.mini-avatar:nth-child(4n) { background: var(--c-amber); }

.unassigned-label {
    font-size: 12px;
    color: var(--text-muted);
    font-style: italic;
}

.due-date-cell {
    font-size: 12.5px;
    color: var(--text-muted);
    white-space: nowrap;
}

.empty-state-inline {
    text-align: center;
    padding: 20px 0 4px;
    color: var(--text-muted);
    font-size: 13px;
}

.empty-board-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
}

.empty-board-state h3 {
    font-weight: 500;
    font-size: 15px;
    margin: 0;
}

@media (max-width: 760px) {
    .tasks-table-head { display: none; }
    .tasks-row {
        grid-template-columns: 1fr;
        gap: 6px;
        padding: 14px 4px;
    }
}
</style>