<template>

    <Head title="Dashboard" />
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome back, manage your dashboard.</p>
                </div>
                <div class="header-right">
                    <button class="theme-btn" @click="theme.toggleTheme">
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>
                    <input type="text" placeholder="Search..." />

                    <div class="notification-bell-container"
                        v-click-outside="() => notificationStore.showBellDropdown = false">
                        <button class="icon-btn"
                            @click="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">
                            🔔
                            <span v-if="notificationStore.activeUrgentTasks.length > 0" class="bell-alert-badge-dot">
                                {{ notificationStore.activeUrgentTasks.length }}
                            </span>
                        </button>

                        <div v-if="notificationStore.showBellDropdown" class="notification-dropdown-panel">
                            <div class="notification-dropdown-header">
                                <h3>Urgent Task Alerts</h3>
                            </div>
                            <div class="notification-dropdown-body">
                                <div v-for="task in notificationStore.activeUrgentTasks" :key="task.id"
                                    class="notification-alert-item">
                                    <div class="alert-item-indicator">⚠️</div>
                                    <div class="alert-item-details">
                                        <p class="alert-task-title">{{ task.title }}</p>
                                        <p class="alert-task-time-left"
                                            :style="{ color: notificationStore.getLiveTaskMetrics(task).color }">
                                            Only {{ notificationStore.getLiveTaskMetrics(task).string }} left!
                                        </p>
                                    </div>
                                </div>

                                <div v-if="notificationStore.activeUrgentTasks.length === 0"
                                    class="notification-empty-state">
                                    🎉 No urgent deadlines right now. Everything is under control!
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-container">
                        <img src="https://i.pravatar.cc/100" class="avatar"
                            @click.stop="showProfileMenu = !showProfileMenu" />
                        <div v-if="showProfileMenu" class="profile-dropdown">
                            <button @click="logout">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stats Cards -->
            <draggable v-model="widgets" item-key="id" class="stats" @end="saveWidgetOrder">
                <template #item="{ element }">
                    <div class="card">
                        <p>{{ element.title }}</p>
                        <h2>
                            <span v-if="element.widget_type === 'total_projects'">
                                {{ props.stats.totalProjects }}
                            </span>

                            <span v-else-if="element.widget_type === 'team_members'">
                                {{ props.stats.teamMembers }}
                            </span>

                            <span v-else-if="element.widget_type === 'completed_tasks'">
                                {{ props.stats.completedTasks }}
                            </span>

                            <span v-else-if="element.widget_type === 'pending_tasks'">
                                {{ props.stats.pendingTasks }}
                            </span>
                        </h2>
                    </div>
                </template>
            </draggable>

            <!-- Main Dashboard Content -->
            <section class="dashboard-content">
                <!-- Projects -->
                <div class="projects">
                    <div class="projects-header">
                        <h2>Active Projects</h2>
                        <button>New Project</button>
                    </div>
                    <div class="project-list">
                        <div class="project-card" v-for="(board, index) in boards" :key="index">
                            <div class="project-header">
                                <h3>{{ board.title }}</h3>
                                <span>{{ board.progress }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" :style="{ width: board.progress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Right -->
                <div class="sidebar-right">
                    <div class="productivity">
                        <p>Productivity</p>
                        <h2>86%</h2>
                        <small>Team performance increased this month.</small>
                        <button>View Report</button>
                    </div>

                    <div class="team">
                        <h2>Team Members</h2>
                        <div class="team-member">
                            <img src="https://i.pravatar.cc/50?img=1" />
                            <div>
                                <p>Sarah</p>
                                <small>UI Designer</small>
                            </div>
                            <span class="status online">Online</span>
                        </div>
                        <div class="team-member">
                            <img src="https://i.pravatar.cc/50?img=2" />
                            <div>
                                <p>David</p>
                                <small>Developer</small>
                            </div>
                            <span class="status away">Away</span>
                        </div>
                        <div class="team-member">
                            <img src="https://i.pravatar.cc/50?img=3" />
                            <div>
                                <p>Emma</p>
                                <small>Manager</small>
                            </div>
                            <span class="status online">Online</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useThemeStore } from "../stores/theme";
import Sidebar from "./components/Sidebar.vue";
import { useNotificationStore } from '@/stores/notificationStore';
import draggable from 'vuedraggable';
import { Head } from '@inertiajs/vue3';

const notificationStore = useNotificationStore();

const props = defineProps({
    stats: Object,
    widgets: Array
});

const widgets = ref([]);

watch(
    () => props.widgets,
    (value) => {
        widgets.value = value ? [...value] : [];
    },
    { immediate: true }
);

const saveWidgetOrder = () => {
    router.post('/dashboard/widgets/reorder', {
        widgets: widgets.value.map((widget, index) => ({
            id: widget.id,
            position: index + 1
        }))
    });
};

const boards = [
    { title: "Website Redesign", progress: 75 },
    { title: "Mobile App", progress: 42 },
    { title: "Marketing Campaign", progress: 91 },
    { title: "CRM Dashboard", progress: 63 },
];

const theme = useThemeStore();
const showProfileMenu = ref(false);

const handleClickOutside = () => {
    showProfileMenu.value = false;
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

const logout = () => {
    router.post("/logout");
};
</script>

<style scoped>
/* Stats Cards */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    padding: 20px 40px;
}

.stats .card p {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 10px;
    transition: color 0.3s ease;
}

.theme-light .stats .card p {
    color: var(--card-text-inverse);
}

.theme-dark .stats .card p {
    color: var(--card-text-inverse);
}

/* Dashboard Content */
.dashboard-content {
    display: flex;
    padding: 20px 40px;
    gap: 20px;
    flex-wrap: wrap;
}

.projects {
    flex: 2;
    background: var(--sidebar);
    border-radius: 15px;
    padding: 20px;
}

.projects-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.projects-header button {
    background: #06b6d4;
    border: none;
    padding: 8px 15px;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s;
}

.projects-header button:hover {
    background: #0ea5e9;
}

.project-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.project-card {
    background: var(--card);
    border-radius: 12px;
    padding: 15px;
    transition: transform 0.3s;
    cursor: pointer;
}

.project-card:hover {
    transform: translateY(-5px);
}

.project-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.progress-bar {
    height: 6px;
    background: #374151;
    border-radius: 5px;
    overflow: hidden;
}

.progress {
    height: 100%;
    background: linear-gradient(90deg, #06b6d4, #3b82f6);
    border-radius: 5px;
}

/* Right Sidebar */
.sidebar-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.productivity {
    background: linear-gradient(135deg, #06b6d4, #2563eb);
    border-radius: 15px;
    padding: 25px;
    text-align: left;
}

.productivity p {
    opacity: 0.8;
}

.productivity h2 {
    font-size: 52px;
    margin: 15px 0;
}

.productivity small {
    opacity: 0.9;
    display: block;
    margin-bottom: 20px;
}

.productivity button {
    background: #fff;
    color: #111827;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

.productivity button:hover {
    transform: scale(1.05);
}

/* Team */
.team {
    background: var(--sidebar);
    border-radius: 15px;
    padding: 20px;
}

.team h2 {
    margin-bottom: 20px;
}

.team-member {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    background: var(--card);
    padding: 12px;
    border-radius: 12px;
    transition: 0.3s;
}

.team-member:hover {
    transform: translateX(5px);
    background: #273449;
}

.team-member img {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    margin-right: 12px;
}

.team-member div {
    flex: 1;
}

.team-member p {
    font-weight: 600;
}

.team-member small {
    color: #9ca3af;
}

.status {
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 20px;
}

.online {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.away {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
}

.profile-container {
    position: relative;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid var(--card);
    transition: 0.2s ease;
}

.avatar:hover {
    transform: scale(1.05);
}

.profile-dropdown {
    position: absolute;
    top: 58px;
    right: 0;
    width: 140px;
    background: var(--sidebar);
    border: 1px solid var(--card);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    z-index: 100;
}

.profile-dropdown button {
    width: 100%;
    padding: 12px 16px;
    background: transparent;
    border: none;
    color: var(--text);
    text-align: left;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s ease;
}

.profile-dropdown button:hover {
    background: var(--card);
    color: #ef4444;
}
</style>
