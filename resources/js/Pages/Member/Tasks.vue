<template>

    <Head title="My Tasks" />
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">
            <div class="content-wrapper">

                <header class="header">
                    <div>
                        <h1>My Tasks</h1>
                        <p>Everything assigned to you, and who assigned it.</p>
                    </div>

                    <div class="header-right">
                        <div class="search-wrap">
                            <svg class="search-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M17 17L13.5 13.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                            <input type="text" v-model="search" placeholder="Search tasks..." class="search-box" />
                        </div>

                        <button class="theme-btn" @click="theme.toggleTheme">
                            {{ theme.isDark ? "☀️" : "🌙" }}
                        </button>
                    </div>
                </header>

                <!-- STATS -->
                <section class="stats-grid">

                    <div class="stat-card total-card">
                        <div class="stat-icon-badge">📋</div>
                        <span class="stat-label">Total Tasks</span>
                        <h2 class="stat-value">{{ totalTasks }}</h2>
                        <small class="stat-subtitle">Assigned to you overall</small>
                    </div>

                    <div class="stat-card progress-card">
                        <div class="stat-icon-badge">🔄</div>
                        <span class="stat-label">In Progress</span>
                        <h2 class="stat-value">{{ inProgressCount }}</h2>
                        <small class="stat-subtitle">Currently being worked on</small>
                    </div>

                    <div class="stat-card completed-card">
                        <div class="stat-icon-badge">✅</div>
                        <span class="stat-label">Completed</span>
                        <h2 class="stat-value">{{ completedCount }}</h2>
                        <small class="stat-subtitle">Successfully finished</small>
                    </div>

                    <div class="stat-card pending-card">
                        <div class="stat-icon-badge">⏳</div>
                        <span class="stat-label">Pending</span>
                        <h2 class="stat-value">{{ pendingCount }}</h2>
                        <small class="stat-subtitle">Not started yet</small>
                    </div>

                    <div class="stat-card overdue-card">
                        <div class="stat-icon-badge">🔥</div>
                        <span class="stat-label">Overdue</span>
                        <h2 class="stat-value">{{ overdueCount }}</h2>
                        <small class="stat-subtitle">Past their due date</small>
                    </div>

                </section>

                <!-- FILTER TABS -->
                <div class="filter-tabs">
                    <button :class="{ active: activeFilter === 'all' }" @click="activeFilter = 'all'">
                        All <span class="tab-count">{{ totalTasks }}</span>
                    </button>
                    <button :class="{ active: activeFilter === 'todo' }" @click="activeFilter = 'todo'">
                        To Do <span class="tab-count">{{ pendingCount }}</span>
                    </button>
                    <button :class="{ active: activeFilter === 'in-progress' }" @click="activeFilter = 'in-progress'">
                        In Progress <span class="tab-count">{{ inProgressCount }}</span>
                    </button>
                    <button :class="{ active: activeFilter === 'completed' }" @click="activeFilter = 'completed'">
                        Completed <span class="tab-count">{{ completedCount }}</span>
                    </button>
                </div>

                <!-- TASK TABLE -->
                <div class="dashboard-card">
                    <div class="tasks-table" v-if="filteredTasks.length">
                        <div class="tasks-table-head">
                            <span>Task</span>
                            <span>Project</span>
                            <span>Assigned By</span>
                            <span>Priority</span>
                            <span>Status</span>
                            <span>Due Date</span>
                        </div>

                        <div
                            v-for="task in filteredTasks"
                            :key="task.id"
                            class="tasks-row"
                            :class="{ 'is-overdue': isOverdue(task) }"
                        >
                            <span class="task-title-cell">{{ task.title }}</span>

                            <span class="task-project-cell">
                                {{ task.project?.name || '—' }}
                            </span>

                            <span class="assigned-by-cell" v-if="task.project?.team_leader">
                                <span class="assigner-avatar">
                                    {{ task.project.team_leader.first_name?.charAt(0)?.toUpperCase() }}
                                </span>
                                {{ task.project.team_leader.first_name }} {{ task.project.team_leader.last_name }}
                            </span>
                            <span class="assigned-by-cell muted" v-else>—</span>

                            <span class="priority-badge" :class="priorityClass(task.priority)">
                                {{ task.priority || 'Normal' }}
                            </span>

                            <span class="status-pill" :class="statusClass(task.status)">
                                {{ task.status || 'Todo' }}
                            </span>

                            <span class="due-date-cell">
                                <span v-if="isOverdue(task)" class="overdue-flag">🔥</span>
                                {{ task.due_date || 'No deadline' }}
                            </span>
                        </div>
                    </div>

                    <div v-else class="empty-state-inline">
                        🎉 No tasks match this view.
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
    tasks: { type: Array, default: () => [] },
});

const search = ref("");
const activeFilter = ref("all");

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

const isOverdue = (task) => {
    if (!task.due_date || isCompleted(task)) return false;
    const due = new Date(task.due_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return due < today;
};

const allTasks = computed(() => props.tasks || []);

const totalTasks = computed(() => allTasks.value.length);
const inProgressCount = computed(() => allTasks.value.filter(isInProgress).length);
const completedCount = computed(() => allTasks.value.filter(isCompleted).length);
const pendingCount = computed(() => allTasks.value.filter(isPending).length);
const overdueCount = computed(() => allTasks.value.filter(isOverdue).length);

const searchedTasks = computed(() => {
    if (!search.value) return allTasks.value;
    const term = search.value.toLowerCase();
    return allTasks.value.filter(task =>
        task.title?.toLowerCase().includes(term) ||
        task.project?.name?.toLowerCase().includes(term)
    );
});

const filteredTasks = computed(() => {
    if (activeFilter.value === "all") return searchedTasks.value;

    return searchedTasks.value.filter(task => {
        if (activeFilter.value === "todo") return isPending(task);
        if (activeFilter.value === "in-progress") return isInProgress(task);
        if (activeFilter.value === "completed") return isCompleted(task);
        return true;
    });
});
</script>

<style scoped>
/* ==========================================================================
   THEME TOKENS — same palette as Dashboard.vue / Sidebar.vue / Projects.vue
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
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}

@media (max-width: 1300px) {
    .stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 800px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
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

.total-card::before { background: var(--c-violet); }
.progress-card::before { background: var(--c-blue); }
.completed-card::before { background: var(--c-green); }
.pending-card::before { background: var(--c-amber); }
.overdue-card::before { background: var(--c-red); }

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

.total-card .stat-icon-badge { background: rgba(139, 110, 232, 0.14); }
.progress-card .stat-icon-badge { background: rgba(85, 110, 230, 0.14); }
.completed-card .stat-icon-badge { background: rgba(52, 195, 143, 0.14); }
.pending-card .stat-icon-badge { background: rgba(241, 180, 76, 0.16); }
.overdue-card .stat-icon-badge { background: rgba(244, 106, 106, 0.14); }

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
   FILTER TABS
   ========================================================================== */
.filter-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 5px;
    width: fit-content;
}

.filter-tabs button {
    display: flex;
    align-items: center;
    gap: 7px;
    border: none;
    padding: 9px 16px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    background: transparent;
    color: var(--text-muted);
    transition: all 0.15s ease;
}

.filter-tabs button:hover {
    color: var(--text-main);
}

.filter-tabs button.active {
    background: var(--accent);
    color: #ffffff;
}

.tab-count {
    font-size: 11px;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    padding: 1px 7px;
}

.filter-tabs button:not(.active) .tab-count {
    background: var(--input-element-bg);
    color: var(--text-muted);
}

/* ==========================================================================
   TASK TABLE
   ========================================================================== */
.dashboard-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 22px 24px;
    box-shadow: 0 2px 6px var(--shadow-cards);
}

.tasks-table {
    display: flex;
    flex-direction: column;
}

.tasks-table-head {
    display: grid;
    grid-template-columns: 1.6fr 1.1fr 1.3fr 0.8fr 0.9fr 1fr;
    align-items: center;
    padding: 0 4px 12px;
    border-bottom: 1px solid var(--border-divider);
    margin-bottom: 2px;
    gap: 12px;
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
    grid-template-columns: 1.6fr 1.1fr 1.3fr 0.8fr 0.9fr 1fr;
    align-items: center;
    padding: 14px 4px;
    border-bottom: 1px solid var(--border-divider);
    gap: 12px;
    transition: background 0.15s ease;
}

.tasks-row:last-child {
    border-bottom: none;
}

.tasks-row:hover {
    background: var(--card-inner-hover);
}

.tasks-row.is-overdue {
    background: rgba(244, 106, 106, 0.05);
}

.task-title-cell {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-project-cell {
    font-size: 12.5px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.assigned-by-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.assigned-by-cell.muted {
    color: var(--text-muted);
}

.assigner-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--c-cyan);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.priority-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 5px;
    text-transform: capitalize;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    white-space: nowrap;
    width: fit-content;
}

.priority-badge.high { background: rgba(244, 106, 106, 0.12); color: var(--c-red); }
.priority-badge.medium { background: rgba(241, 180, 76, 0.15); color: #b9822e; }
.priority-badge.low { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }

.status-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 5px;
    text-transform: capitalize;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    white-space: nowrap;
    width: fit-content;
}

.status-pill.in-progress { background: rgba(85, 110, 230, 0.12); color: var(--c-blue); }
.status-pill.completed { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }
.status-pill.todo { background: rgba(241, 180, 76, 0.15); color: #b9822e; }

.due-date-cell {
    font-size: 12.5px;
    color: var(--text-muted);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 5px;
}

.overdue-flag {
    font-size: 11px;
}

.tasks-row.is-overdue .due-date-cell {
    color: var(--c-red);
    font-weight: 600;
}

.empty-state-inline {
    text-align: center;
    padding: 40px 0;
    color: var(--text-muted);
    font-size: 13.5px;
}

@media (max-width: 900px) {
    .tasks-table-head { display: none; }
    .tasks-row {
        grid-template-columns: 1fr;
        gap: 6px;
        padding: 16px 4px;
    }
}
</style>