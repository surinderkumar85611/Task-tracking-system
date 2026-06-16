<template>
    <Head title="Dashboard" />
  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">
      <div class="content-wrapper">
        <header class="header">
          <div class="header-welcome">
            <h1>Leader Dashboard</h1>
            <p>Welcome back, {{ leader?.first_name }} {{ leader?.last_name }}</p>
          </div>

          <div class="header-right">
            <div class="workspace-label" v-if="currentWorkspace">
              🏢 {{ currentWorkspace.name }}
            </div>

            <input v-model="search" type="text" placeholder="Search projects..." class="search-box" />

            <button class="theme-btn" @click="theme.toggleTheme">
              {{ theme.isDark ? '☀️' : '🌙' }}
            </button>

            <!-- NOTIFICATION BELL -->
            <div class="notification-bell-container" v-click-outside="handleClickOutside">
              <button class="icon-btn"
                @click.stop="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">

                🔔

                <!-- BADGE FIX -->
                <span v-if="(props.notifications || []).length" class="bell-alert-badge-dot">
                  {{ (props.notifications || []).length }}
                </span>

              </button>

              <!-- DROPDOWN -->
              <div v-if="notificationStore?.showBellDropdown" class="notification-dropdown-panel">

                <div class="notification-dropdown-header">
                  <h3>Notifications</h3>
                </div>

                <div class="notification-dropdown-body">

                  <!-- UPDATED NOTIFICATION LIST -->
                  <div
                    v-for="notification in (props.notifications || [])"
                    :key="notification.id"
                    class="notification-alert-item"
                  >
                    <div class="alert-item-indicator">🔔</div>

                    <div class="alert-item-details">
                      <p class="alert-task-title">
                        {{ notification.title }}
                      </p>

                      <p class="alert-task-time-left">
                        {{ notification.message }}
                      </p>
                    </div>
                  </div>

                  <!-- EMPTY STATE FIX -->
                  <div v-if="!(props.notifications || []).length" class="notification-empty-state">
                    🎉 No notifications right now.
                  </div>

                </div>
              </div>
            </div>

            <!-- PROFILE -->
            <div class="profile-container">
              <img src="https://i.pravatar.cc/100" class="avatar" @click.stop="showProfileMenu = !showProfileMenu" />
              <div v-if="showProfileMenu" class="profile-dropdown">
                <button @click="logout">Logout</button>
              </div>
            </div>

          </div>
        </header>

        <!-- REST OF YOUR FILE REMAINS EXACTLY SAME -->

        <section class="stats-grid">
          <div class="stat-card total-projects-card">
            <div class="stat-card-gradient"></div>
            <div class="stat-content">
              <span class="stat-label">Total Projects</span>
              <h2 class="stat-value">{{ stats.projects }}</h2>
            </div>
          </div>

          <div class="stat-card total-tasks-card">
            <div class="stat-card-gradient"></div>
            <div class="stat-content">
              <span class="stat-label">Total Tasks</span>
              <h2 class="stat-value">{{ stats.tasks }}</h2>
            </div>
          </div>

          <div class="stat-card completed-tasks-card">
            <div class="stat-card-gradient"></div>
            <div class="stat-content">
              <span class="stat-label">Completed Tasks</span>
              <h2 class="stat-value">{{ stats.completed }}</h2>
            </div>
          </div>

          <div class="stat-card pending-tasks-card">
            <div class="stat-card-gradient"></div>
            <div class="stat-content">
              <span class="stat-label">Pending Tasks</span>
              <h2 class="stat-value">{{ stats.pending }}</h2>
            </div>
          </div>

          <div class="stat-card completion-rate-card">
            <div class="stat-card-gradient"></div>
            <div class="stat-content">
              <span class="stat-label">Completion Rate</span>
              <h2 class="stat-value">{{ completionRate }}%</h2>
            </div>
          </div>
        </section>

        <div class="top-grid">
          <div class="dashboard-card main-panel">
            <div class="card-header">
              <h2>Project Progress</h2>
            </div>

            <div class="project-grid">
              <div v-for="project in filteredProjects" :key="project.id" class="project-mini-card">
                <div class="project-card-meta">
                  <h3>{{ project.name }}</h3>
                  <span class="project-percentage">{{ project.progress || 0 }}%</span>
                </div>
                <small class="due-date">Due: {{ project.deadline }}</small>
                <div class="mini-progress">
                  <div class="mini-progress-fill" :style="{ width: (project.progress || 0) + '%' }"></div>
                </div>
              </div>
            </div>

          </div>

          <div class="dashboard-card side-panel">
            <div class="card-header">
              <h2>Team Members</h2>
            </div>

            <div class="member-list">
              <div v-for="member in (teamMembers || [])" :key="member.id" class="member-card">
                <div class="member-avatar">
                  {{ member.first_name?.charAt(0)?.toUpperCase() }}
                </div>
                <div class="member-info">
                  <h4>{{ member.first_name }} {{ member.last_name }}</h4>
                  <p>{{ member.department }}</p>
                </div>
              </div>
            </div>

          </div>
        </div>

        <section class="dashboard-card content-section">
          <div class="card-header">
            <h2>Team Workload</h2>
          </div>

          <div class="workload-grid">
            <div v-for="member in (teamWorkload || [])" :key="member.id" class="workload-card">
              <div class="member-avatar workload-avatar">
                {{ member.first_name?.charAt(0) }}
              </div>
              <div class="workload-info">
                <h4>{{ member.first_name }} {{ member.last_name }}</h4>
                <p>{{ member.taskCount }} Assigned Tasks</p>
              </div>
            </div>
          </div>
        </section>

        <section class="dashboard-card content-section">
          <div class="card-header">
            <h2>Recent Activity</h2>
          </div>

          <div class="activity-feed">
            <template v-for="project in (projects || [])" :key="project.id">
              <div v-for="task in (project.tasks || [])" :key="task.id" class="activity-item">
                <div class="activity-dot" :class="task.status?.toLowerCase().replace(' ', '-')"></div>
                <div class="activity-details">
                  <strong>{{ task.title }}</strong>
                  <p>{{ task.status }}</p>
                </div>
              </div>
            </template>
          </div>
        </section>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import Sidebar from "./Sidebar.vue";
import { useThemeStore } from "../../stores/theme.js";
import { useNotificationStore } from "@/stores/notificationStore";
import { Head } from '@inertiajs/vue3';

const theme = useThemeStore();
const notificationStore = useNotificationStore();
const page = usePage();

const props = defineProps({
  leader: Object,
  projects: Array,
  teamMembers: Array,
  stats: Object,
  statusBreakdown: Object,
  currentWorkspaceId: Number,
  notifications: Array   // ✅ ADDED
});

const workspaces = computed(() => page.props.workspaces || []);
const currentWorkspace = computed(() => workspaces.value[0] || null);

const search = ref("");
const showProfileMenu = ref(false);

const filteredProjects = computed(() => {
  if (!search.value) return props.projects;

  return props.projects.filter(project =>
    project.name?.toLowerCase().includes(search.value.toLowerCase())
  );
});

const completionRate = computed(() => {
  if (!props.stats.tasks) return 0;

  return Math.round((props.stats.completed / props.stats.tasks) * 100);
});

const teamWorkload = computed(() => {
  return props.teamMembers.map(member => {
    let tasks = 0;

    props.projects.forEach(project => {
      project.tasks?.forEach(task => {
        if (task.member_id == member.id) tasks++;
      });
    });

    return { ...member, taskCount: tasks };
  });
});

const logout = () => {
  router.post("/logout");
};

const handleClickOutside = (event) => {
  const bell = document.querySelector('.notification-bell-container');
  const profile = document.querySelector('.profile-container');

  if (bell && !bell.contains(event.target)) {
    notificationStore.showBellDropdown = false;
  }

  if (profile && !profile.contains(event.target)) {
    showProfileMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
/* Theme Context Theme Palette Declarations */
.theme-dark {
  --dashboard-bg: #0b0f19;
  --panel-bg: linear-gradient(145deg, rgba(15, 22, 36, 0.5) 0%, rgba(11, 17, 30, 0.7) 100%);
  --card-inner-bg: linear-gradient(135deg, rgba(255, 255, 255, 0.01) 0%, rgba(255, 255, 255, 0.02) 100%);
  --card-inner-hover: rgba(255, 255, 255, 0.03);
  --activity-item-bg: rgba(255, 255, 255, 0.01);
  --stat-card-bg: linear-gradient(135deg, rgba(19, 26, 42, 0.8) 0%, rgba(13, 20, 35, 0.95) 100%);
  --input-element-bg: rgba(17, 24, 39, 0.6);
  --input-element-focus: rgba(17, 24, 39, 0.8);
  --dropdown-panel-bg: #111827;
  --border-subtle: rgba(255, 255, 255, 0.04);
  --border-deep: rgba(255, 255, 255, 0.05);
  --border-divider: rgba(255, 255, 255, 0.03);
  --text-main: #f1f5f9;
  --text-header: #f8fafc;
  --text-muted: #64748b;
  --text-card-sub: #e2e8f0;
  --due-date-color: #475569;
  --header-mask: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
  --shadow-cards: rgba(0, 0, 0, 0.5);
  --shadow-stats: rgba(0, 0, 0, 0.7);
  --shadow-stats-hover: rgba(0, 0, 0, 0.9);
}

.theme-light {
  --dashboard-bg: #f8fafc;
  --panel-bg: #ffffff;
  --card-inner-bg: #f1f5f9;
  --card-inner-hover: #e2e8f0;
  --activity-item-bg: #f8fafc;
  --stat-card-bg: #ffffff;
  --input-element-bg: #f1f5f9;
  --input-element-focus: #e2e8f0;
  --dropdown-panel-bg: #ffffff;
  --border-subtle: rgba(15, 23, 42, 0.06);
  --border-deep: rgba(15, 23, 42, 0.08);
  --border-divider: rgba(15, 23, 42, 0.05);
  --text-main: #0f172a;
  --text-header: #0f172a;
  --text-muted: #64748b;
  --text-card-sub: #334155;
  --due-date-color: #64748b;
  --header-mask: linear-gradient(135deg, #0f172a 0%, #334155 100%);
  --shadow-cards: rgba(15, 23, 42, 0.04);
  --shadow-stats: rgba(15, 23, 42, 0.04);
  --shadow-stats-hover: rgba(15, 23, 42, 0.08);
}

/* Master Layout Frame context mapping */
.dashboard {
  display: flex;
  height: 100vh;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  background-color: var(--dashboard-bg);
  color: var(--text-main);
  overflow: hidden;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.main-content {
  flex: 1;
  padding: 40px;
  overflow-y: auto;
  width: 100%;
  height: 100%;
}

.content-wrapper {
  max-width: 1600px;
  margin: 0 auto;
  width: 100%;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
}

.header h1 {
  font-size: 32px;
  font-weight: 800;
  margin: 0 0 6px 0;
  letter-spacing: -0.75px;
  background: var(--header-mask);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.header p {
  color: var(--text-muted);
  font-weight: 500;
  margin: 0;
  font-size: 15px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  width: 280px;
  padding: 12px 18px;
  border-radius: 14px;
  border: 1px solid var(--border-deep);
  background: var(--input-element-bg);
  color: var(--text-main);
  outline: none;
  backdrop-filter: blur(8px);
  transition: all 0.3s ease;
}

.search-box:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
  background: var(--input-element-focus);
}

.theme-btn,
.icon-btn {
  background: var(--input-element-bg);
  border: 1px solid var(--border-deep);
  color: var(--text-main);
  width: 46px;
  height: 46px;
  border-radius: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(8px);
  transition: all 0.2s ease;
}

.theme-btn:hover,
.icon-btn:hover {
  background: var(--input-element-focus);
  border-color: var(--border-deep);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  margin-bottom: 40px;
}

@media (max-width: 1400px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  position: relative;
  background: var(--stat-card-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 20px;
  padding: 24px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 10px 30px -10px var(--shadow-stats);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
  transform: translateY(-4px);
  border-color: var(--border-deep);
  box-shadow: 0 20px 40px -15px var(--shadow-stats-hover);
}

.stat-card-gradient {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.12;
  pointer-events: none;
  transition: opacity 0.3s ease;
}

.stat-card:hover .stat-card-gradient {
  opacity: 0.22;
}

.total-projects-card .stat-card-gradient {
  background: radial-gradient(circle at top right, #3b82f6 0%, transparent 60%);
}

.total-projects-card {
  border-top: 3px solid #3b82f6;
}

.total-tasks-card .stat-card-gradient {
  background: radial-gradient(circle at top right, #8b5cf6 0%, transparent 60%);
}

.total-tasks-card {
  border-top: 3px solid #8b5cf6;
}

.completed-tasks-card .stat-card-gradient {
  background: radial-gradient(circle at top right, #10b981 0%, transparent 60%);
}

.completed-tasks-card {
  border-top: 3px solid #10b981;
}

.pending-tasks-card .stat-card-gradient {
  background: radial-gradient(circle at top right, #f59e0b 0%, transparent 60%);
}

.pending-tasks-card {
  border-top: 3px solid #f59e0b;
}

.completion-rate-card .stat-card-gradient {
  background: radial-gradient(circle at top right, #06b6d4 0%, transparent 60%);
}

.completion-rate-card {
  border-top: 3px solid #06b6d4;
}

.stat-content {
  position: relative;
  z-index: 2;
}

.stat-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 16px;
}

.stat-value {
  font-size: 38px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
  line-height: 1;
  letter-spacing: -1px;
}

.top-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 32px;
  margin-bottom: 32px;
}

@media (max-width: 1200px) {
  .top-grid {
    grid-template-columns: 1fr;
  }
}

.dashboard-card {
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 24px;
  padding: 32px;
  box-shadow: 0 20px 40px -20px var(--shadow-cards);
  backdrop-filter: blur(12px);
}

.content-section {
  margin-bottom: 32px;
}

.card-header {
  margin-bottom: 24px;
}

.card-header h2 {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  color: var(--text-header);
  letter-spacing: -0.3px;
}

.project-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.project-mini-card {
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  border-left: 4px solid #3b82f6;
  border-radius: 16px;
  padding: 20px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 20px var(--shadow-cards);
}

.project-mini-card:hover {
  background: var(--card-inner-hover);
  border-color: var(--border-deep);
  border-left-color: #60a5fa;
  transform: translateY(-2px);
}

.project-card-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.project-mini-card h3 {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: var(--text-main);
}

.project-percentage {
  font-size: 13px;
  font-weight: 700;
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.15);
  padding: 4px 8px;
  border-radius: 8px;
}

.due-date {
  color: var(--due-date-color);
  font-size: 13px;
  font-weight: 500;
}

.mini-progress {
  height: 8px;
  background: var(--border-deep);
  border-radius: 6px;
  overflow: hidden;
  margin-top: 16px;
}

.mini-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
  border-radius: 6px;
  box-shadow: 0 0 12px rgba(59, 130, 246, 0.4);
}

.member-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 16px;
}

.member-card,
.workload-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px;
  border-radius: 16px;
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  transition: all 0.2s ease;
  box-shadow: 0 4px 20px var(--shadow-cards);
}

.member-card {
  border-left: 4px solid #06b6d4;
}

.workload-card {
  border-left: 4px solid #8b5cf6;
}

.member-card:hover,
.workload-card:hover {
  background: var(--card-inner-hover);
  border-color: var(--border-deep);
  transform: translateY(-2px);
}

.workload-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.member-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
}

.workload-avatar {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  box-shadow: 0 4px 12px rgba(109, 40, 217, 0.25);
}

.member-info h4,
.workload-info h4 {
  font-size: 15px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: var(--text-main);
}

.member-info p,
.workload-info p {
  color: var(--text-muted);
  font-size: 13px;
  margin: 0;
  font-weight: 500;
}

.activity-feed {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 14px;
  background: var(--activity-item-bg);
  border: 1px solid var(--border-divider);
  border-radius: 16px;
  padding: 18px;
}

.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--text-muted);
  flex-shrink: 0;
  box-shadow: 0 0 8px currentColor;
}

.activity-dot.in-progress {
  color: #3b82f6;
  background: #3b82f6;
}

.activity-dot.completed {
  color: #10b981;
  background: #10b981;
}

.activity-dot.todo {
  color: #f59e0b;
  background: #f59e0b;
}

.activity-details strong {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 4px;
}

.activity-details p {
  margin: 0;
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
}

.profile-container {
  position: relative;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  cursor: pointer;
  border: 1px solid var(--border-deep);
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 55px;
  background: var(--dropdown-panel-bg);
  border: 1px solid var(--border-deep);
  border-radius: 12px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 10px 25px var(--shadow-cards);
}

.profile-dropdown button {
  background: transparent;
  border: none;
  color: var(--text-main);
  padding: 12px 24px;
  cursor: pointer;
  width: 100%;
  text-align: left;
  font-size: 14px;
  font-weight: 500;
}

.profile-dropdown button:hover {
  background: var(--border-divider);
}

.notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 55px;
  width: 340px;
  background: var(--dropdown-panel-bg);
  border: 1px solid var(--border-deep);
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px var(--shadow-cards);
  z-index: 50;
  overflow: hidden;
}

.notification-dropdown-header {
  padding: 18px;
  border-bottom: 1px solid var(--border-deep);
  color: var(--text-main);
}

.notification-dropdown-header h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
}

.notification-dropdown-body {
  padding: 16px;
  max-height: 320px;
  overflow-y: auto;
  color: var(--text-main);
}

.notification-alert-item {
  display: flex;
  gap: 12px;
  padding: 10px;
  border-bottom: 1px solid var(--border-divider);
}

.notification-empty-state {
  color: var(--text-muted);
  text-align: center;
  font-size: 14px;
}

.bell-alert-badge-dot {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 8px;
  border: 2px solid var(--dashboard-bg);
}

.workspace-selector select {
  background: var(--input-element-bg);
  color: var(--text-main);
  border: 1px solid var(--border-deep);
  padding: 11px 16px;
  border-radius: 14px;
  outline: none;
  cursor: pointer;
}
</style>
