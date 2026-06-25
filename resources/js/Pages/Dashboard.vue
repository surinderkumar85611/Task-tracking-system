<template>

  <Head title="Dashboard" />

  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">
      <div class="content-wrapper">

        <header class="header">
          <div class="header-welcome">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, manage your dashboard.</p>
          </div>

          <div class="header-right">
            <div class="search-container">
              <input v-model="search" type="text" placeholder="Search projects..." class="search-box" />
            </div>

            <button class="theme-btn" @click="theme.toggleTheme">
              {{ theme.isDark ? "☀️" : "🌙" }}
            </button>

            <div class="notification-bell-container" v-click-outside="() => notificationStore.showBellDropdown = false">
              <button class="icon-btn"
                @click.stop="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">
                🔔
                <span v-if="
                  unreadCount > 0 ||
                  notificationStore.activeUrgentTasks.length > 0
                " class="bell-alert-badge-dot">
                  {{
                    unreadCount +
                    notificationStore.activeUrgentTasks.length
                  }}
                </span>
              </button>

              <div v-if="notificationStore.showBellDropdown" class="notification-dropdown-panel">
                <div class="notification-dropdown-header">
                  <h3>Urgent Task Alerts</h3>
                </div>

                <div class="notification-dropdown-body">

  <!-- SECTION: NORMAL NOTIFICATIONS -->
  <div class="notification-section" v-if="unreadNotifications.length">

    <h4 class="section-title">Notifications</h4>

    <div
      v-for="notification in unreadNotifications"
      :key="'notif-' + notification.id"
      class="notification-item normal"
      @click="markAsRead(notification.id)"
    >
      <div class="notif-icon">🔔</div>

      <div class="notif-content">
        <p class="notif-title">{{ notification.title }}</p>
        <p class="notif-message">{{ notification.message }}</p>
        <span class="notif-time">
          {{ formatNotificationDate(notification.created_at) }}
        </span>
      </div>
    </div>

  </div>

  <!-- SECTION: URGENT TASKS -->
  <div class="notification-section" v-if="notificationStore.activeUrgentTasks.length">

    <h4 class="section-title">Urgent Tasks</h4>

    <div
      v-for="task in notificationStore.activeUrgentTasks"
      :key="'urgent-' + task.id"
      class="notification-item urgent"
    >
      <div class="notif-icon urgent-icon">⚠️</div>

      <div class="notif-content">
        <p class="notif-title">{{ task.title }}</p>
        <p class="notif-message">
          Only {{ notificationStore.getLiveTaskMetrics(task).string }} left
        </p>
      </div>
    </div>

  </div>

  <!-- EMPTY STATE -->
  <div
    v-if="unreadNotifications.length === 0 && notificationStore.activeUrgentTasks.length === 0"
    class="notification-empty-state"
  >
    🎉 No notifications right now
  </div>

</div>
              </div>
            </div>

            <div class="profile-container">
              <img src="https://i.pravatar.cc/100" class="avatar" @click.stop="showProfileMenu = !showProfileMenu" />

              <div v-if="showProfileMenu" class="profile-dropdown">
                <button @click="logout">Logout</button>
              </div>
            </div>
          </div>
        </header>

        <draggable v-model="widgets" item-key="id" class="stats-grid" @end="saveWidgetOrder">
          <template #item="{ element }">
            <div class="stat-card" :class="element.widget_type.replace(/_/g, '-') + '-card'">
              <div class="stat-glow"></div>

              <div class="stat-inner">
                <span class="stat-label">
                  {{ element.title }}
                </span>

                <h2 class="stat-value">
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
            </div>
          </template>
        </draggable>

        <div class="dashboard-layout-split">

          <!-- Active Projects -->
          <div class="panel-container active-projects-panel">
            <div class="panel-header">
              <h2>Active Projects</h2>
              <button class="action-btn-primary">
                New Project
              </button>
            </div>

            <div class="projects-grid">
              <div class="project-card" v-for="project in boards" :key="project.id">
                <div class="project-card-header">
                  <h3>{{ project.name }}</h3>

                  <span class="project-status">
                    {{ project.status }}
                  </span>
                </div>

                <div class="project-progress">
                  <span>{{ project.progress }}%</span>
                </div>

                <div class="progress-track-line">
                  <div class="progress-track-fill" :style="{ width: project.progress + '%' }"></div>
                </div>

                <div class="project-footer">
                  <small>
                    Deadline:
                    {{ project.deadline }}
                  </small>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Sidebar -->
          <div class="sidebar-right-stack">

            <div class="productivity-card">
              <div class="prod-top-row">
                <span class="prod-title">Productivity</span>
                <h2 class="prod-percentage">86%</h2>
              </div>

              <p class="prod-desc-text">
                Team performance increased this month.
              </p>

              <button class="prod-view-btn">
                View Report
              </button>
            </div>

            <!-- Team Members -->
            <div class="panel-container team-members-panel">
              <div class="panel-header">
                <h2>Team Members</h2>
              </div>

              <div class="team-members-list">
                <div class="members-grid">

                  <div class="member-card" v-for="member in teamMembersList" :key="member.id">
                    <div class="member-avatar">
                      {{ member.first_name.charAt(0) }}{{ member.last_name.charAt(0) }}
                    </div>

                    <div class="member-profile-details">
                      <h4>
                        {{ member.first_name }}
                        {{ member.last_name }}
                      </h4>

                      <p>
                        {{ member.role }}
                      </p>
                    </div>

                    <span class="status-pill online">
                      Active
                    </span>
                  </div>

                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
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
let notificationRefreshInterval = null;
const props = defineProps({
  stats: Object,
  widgets: Array,
  projects: Array,
  members: Array,
  notifications: Array
});

const search = ref("");
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

const boards = computed(() => props.projects || []);

const teamMembersList = computed(() => props.members || []);

const theme = useThemeStore();
const showProfileMenu = ref(false);
const unreadNotifications = computed(() => {
  return [...(props.notifications || [])]
    .filter(notification => !notification.is_read)
    .sort(
      (a, b) =>
        new Date(b.created_at) -
        new Date(a.created_at)
    );
});

const unreadCount = computed(() => {
  return (props.notifications || [])
    .filter(notification => !notification.is_read)
    .length;
});


const handleClickOutside = (event) => {
  if (event.target.closest('.notification-dropdown-panel')) {
    return; 
  }
  
  showProfileMenu.value = false;
  notificationStore.showBellDropdown = false;
};
onMounted(() => {
  document.addEventListener("click", handleClickOutside);

  notificationStore.setProjectsSource(props.projects || []);

  notificationRefreshInterval = setInterval(() => {
    router.reload({
      only: ["notifications"]
    });
  }, 30000);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);

  if (notificationRefreshInterval) {
    clearInterval(notificationRefreshInterval);
  }
});
const formatNotificationDate = (date) => {
  if (!date) return "";

  const now = new Date();
  const created = new Date(date);

  const diffMinutes = Math.floor(
    (now - created) / 1000 / 60
  );

  if (diffMinutes < 1) return "Just now";

  if (diffMinutes < 60)
    return `${diffMinutes} min ago`;

  const diffHours = Math.floor(
    diffMinutes / 60
  );

  if (diffHours < 24)
    return `${diffHours} hr ago`;

  return created.toLocaleDateString();
};

const markAsRead = (id) => {
  router.put(`notifications/${id}/read`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ["notifications"] });
    },
    onError: (errors) => {
      console.log("Route error:", errors);
    }
  });
};
const logout = () => {
  router.post("/logout", {}, {
    replace: true,
    onSuccess: () => {
      window.location.href = "/login";
    }
  });
};
</script>

<style scoped>
/* --- Design Theme Configuration Mapping Variables --- */
.theme-dark {
  --dashboard-bg: #0b0f19;
  --panel-bg: rgba(15, 23, 42, 0.4);
  --card-bg: rgba(22, 30, 49, 0.5);
  --input-bg: rgba(17, 24, 39, 0.6);
  --border-color: rgba(255, 255, 255, 0.04);
  --text-main: #f1f5f9;
  --text-muted: #64748b;
  --header-title-color: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
  --shadow-profile: rgba(0, 0, 0, 0.5);
}

.theme-light {
  --dashboard-bg: #f8fafc;
  --panel-bg: #ffffff;
  --card-bg: #f1f5f9;
  --input-bg: #e2e8f0;
  --border-color: rgba(15, 23, 42, 0.06);
  --text-main: #0f172a;
  --text-muted: #64748b;
  --header-title-color: linear-gradient(135deg, #0f172a 0%, #334155 100%);
  --shadow-profile: rgba(15, 23, 42, 0.08);
}

/* --- Core Shell Structure Framework --- */
.dashboard {
  display: flex;
  height: 100vh;
  width: 100vw;
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
  height: 100%;
}

.content-wrapper {
  max-width: 1600px;
  margin: 0 auto;
  width: 100%;
}

/* --- Layout Header Sections --- */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 36px;
}

.header-welcome h1 {
  font-size: 30px;
  font-weight: 800;
  margin: 0 0 4px 0;
  letter-spacing: -0.5px;
  background: var(--header-title-color);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.header-welcome p {
  color: var(--text-muted);
  font-weight: 500;
  margin: 0;
  font-size: 14px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.search-box {
  width: 250px;
  padding: 10px 16px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  background: var(--input-bg);
  color: var(--text-main);
  outline: none;
  font-size: 13.5px;
  transition: all 0.2s ease;
}

.search-box:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.theme-btn,
.icon-btn {
  background: var(--input-bg);
  border: 1px solid var(--border-color);
  color: var(--text-main);
  width: 40px;
  height: 40px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.theme-btn:hover,
.icon-btn:hover {
  transform: translateY(-1px);
}

/* --- Premium Stats Metric Grid Blocks --- */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 36px;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  position: relative;
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: 18px;
  padding: 22px;
  overflow: hidden;
  cursor: grab;
  box-shadow: 0 10px 20px -5px var(--shadow-profile);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:active {
  cursor: grabbing;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 24px -5px var(--shadow-profile);
}

.stat-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.06;
  pointer-events: none;
}

.total-projects-card .stat-glow {
  background: radial-gradient(circle at top right, #3b82f6 0%, transparent 60%);
}

.total-projects-card {
  border-left: 4px solid #3b82f6;
}

.team-members-card .stat-glow {
  background: radial-gradient(circle at top right, #06b6d4 0%, transparent 60%);
}

.team-members-card {
  border-left: 4px solid #06b6d4;
}

.completed-tasks-card .stat-glow {
  background: radial-gradient(circle at top right, #10b981 0%, transparent 60%);
}

.completed-tasks-card {
  border-left: 4px solid #10b981;
}

.pending-tasks-card .stat-glow {
  background: radial-gradient(circle at top right, #f59e0b 0%, transparent 60%);
}

.pending-tasks-card {
  border-left: 4px solid #f59e0b;
}

.stat-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.75px;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 32px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
  letter-spacing: -0.5px;
}

/* --- Dual Column Grid System Structure --- */
.dashboard-layout-split {
  display: grid;
  grid-template-columns: 1.62fr 1fr;
  gap: 28px;
}

@media (max-width: 1150px) {
  .dashboard-layout-split {
    grid-template-columns: 1fr;
  }
}

.panel-container {
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 10px 30px -10px var(--shadow-profile);
  backdrop-filter: blur(12px);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.panel-header h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: var(--text-main);
  letter-spacing: -0.2px;
}

.action-btn-primary {
  background: #06b6d4;
  color: #ffffff;
  border: none;
  padding: 8px 16px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 12.5px;
  cursor: pointer;
  transition: background-color 0.15s, transform 0.15s;
}

.action-btn-primary:hover {
  background: #0ea5e9;
  transform: translateY(-0.5px);
}

/* --- Compact Sizing Fix for Active Projects Elements --- */
.projects-compact-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.project-strip-item {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-left: 3px solid #3b82f6;
  border-radius: 12px;
  padding: 14px 18px;
  /* Balanced padding bounds to decrease component visual density */
  transition: all 0.2s;
}

.project-strip-item:hover {
  transform: translateX(3px);
  background: var(--input-bg);
}

.project-strip-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.project-strip-item h3 {
  font-size: 14px;
  margin: 0;
  color: var(--text-main);
  font-weight: 600;
}

.project-percentage-badge {
  font-size: 11px;
  font-weight: 700;
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
  padding: 3px 8px;
  border-radius: 6px;
}

.progress-track-line {
  height: 5px;
  background: var(--input-bg);
  border-radius: 4px;
  margin-top: 10px;
  overflow: hidden;
}

.progress-track-fill {
  height: 100%;
  background: linear-gradient(90deg, #06b6d4, #3b82f6);
  border-radius: 4px;
}

/* --- Right Sidebar Column Panel Component Chain Stack --- */
.sidebar-right-stack {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

/* Productivity Highlight Card Size Reduction Fix */
.productivity-card {
  background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%);
  border-radius: 20px;
  padding: 22px 24px;
  /* Scaled down slightly to fit cleanly */
  color: #ffffff;
  box-shadow: 0 10px 25px rgba(6, 182, 212, 0.2);
}

.prod-top-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 6px;
}

.prod-title {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.85;
}

.prod-percentage {
  font-size: 36px;
  font-weight: 800;
  margin: 0;
  color: #ffffff;
  letter-spacing: -0.5px;
}

.prod-desc-text {
  margin: 0 0 16px 0;
  font-size: 13.5px;
  opacity: 0.85;
  font-weight: 500;
}

.prod-view-btn {
  background: #ffffff;
  color: #0f172a;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
  transition: transform 0.2s;
}

.prod-view-btn:hover {
  transform: translateY(-1px);
}

/* --- Team Roster Block Components Layout Alignment --- */
.team-members-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.member-strip-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  /* Scaled parameters down precisely to balance typography blocks */
  border-radius: 12px;
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  transition: all 0.2s;
}

.member-strip-card:hover {
  transform: translateX(3px);
  background: var(--input-bg);
}

.member-avatar-mask {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  object-fit: cover;
}

.member-profile-details {
  flex: 1;
}

.member-profile-details h4 {
  font-size: 13.5px;
  font-weight: 600;
  margin: 0 0 1px 0;
  color: var(--text-main);
}

.member-profile-details p {
  color: var(--text-muted);
  font-size: 11.5px;
  margin: 0;
}

.status-pill {
  font-size: 10px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 20px;
}

.status-pill.online {
  background: rgba(34, 197, 94, 0.12);
  color: #22c55e;
}

.status-pill.away {
  background: rgba(251, 191, 36, 0.12);
  color: #fbbf24;
}

/* --- Dynamic Overlay Dropdown Utilities --- */
.profile-container {
  position: relative;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  cursor: pointer;
  border: 1px solid var(--border-color);
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 50px;
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  overflow: hidden;
  z-index: 60;
  box-shadow: 0 10px 25px var(--shadow-profile);
}

.profile-dropdown button {
  background: transparent;
  border: none;
  color: var(--text-main);
  padding: 10px 20px;
  cursor: pointer;
  width: 100%;
  font-size: 13px;
  text-align: left;
}

.profile-dropdown button:hover {
  background: var(--input-bg);
  color: #ef4444;
}

.notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 50px;
  width: 320px;
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: 14px;
  box-shadow: 0 20px 40px var(--shadow-profile);
  z-index: 50;
  overflow: hidden;
}

.notification-dropdown-header {
  padding: 14px 18px;
  border-bottom: 1px solid var(--border-color);
}

.notification-dropdown-header h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
}

.notification-dropdown-body {
  padding: 12px;
  max-height: 300px;
  overflow-y: auto;
}

.notification-alert-item {
  display: flex;
  gap: 10px;
  padding: 10px 6px;
  border-bottom: 1px solid var(--border-color);
}

.bell-alert-badge-dot {
  position: absolute;
  top: -3px;
  right: -3px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 5px;
  border-radius: 7px;
  border: 2px solid var(--dashboard-bg);
}

.notification-empty-state {
  font-size: 12.5px;
  color: var(--text-muted);
  text-align: center;
  padding: 14px 0;
}

.member-initials {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  font-weight: 700;
  font-size: 14px;
}

.projects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.project-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 16px;
  transition: .3s;
}

.project-card:hover {
  transform: translateY(-3px);
}

.project-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.project-status {
  padding: 4px 10px;
  border-radius: 20px;
  background: rgba(0, 255, 150, .12);
  color: #00ff95;
  font-size: 12px;
}

.project-progress {
  margin: 15px 0;
  font-size: 22px;
  font-weight: 700;
}

.project-footer {
  margin-top: 12px;
  opacity: .7;
}

.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 15px;
}

.member-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 18px;
  text-align: center;
  transition: .3s;
}

.member-card:hover {
  transform: translateY(-3px);
}

.member-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg,
      #6d5dfc,
      #4f46e5);

  display: flex;
  align-items: center;
  justify-content: center;

  margin: 0 auto 12px;

  font-weight: 700;
  font-size: 18px;
  color: white;
}

.member-card h4 {
  margin: 0;
}

.member-card p {
  margin-top: 6px;
  opacity: .7;
}
/* --- Unified Notification Dropdown Styles --- */

.notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 50px;
  width: 320px;
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: 14px;
  box-shadow: 0 20px 40px var(--shadow-profile);
  z-index: 9999;
  overflow: hidden; /* Kept hidden here to clip children to rounded corners */
  backdrop-filter: blur(12px);
}

.notification-dropdown-header {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-dropdown-header h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
}

.notification-dropdown-body {
  padding: 8px;
  max-height: 350px;
  overflow-y: auto;
}

.notification-section {
  margin-bottom: 14px;
}

.section-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  margin: 8px 6px;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  margin-bottom: 8px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.2s ease;
}

.notification-item:hover {
  background: rgba(255, 255, 255, 0.06);
}

.notification-item.normal {
  background: rgba(59, 130, 246, 0.06);
}

.notification-item.urgent {
  background: rgba(239, 68, 68, 0.06);
}

.notif-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(59, 130, 246, 0.15);
  flex-shrink: 0;
}

.urgent-icon {
  background: rgba(239, 68, 68, 0.15);
}

.notif-content { flex: 1; }

.notif-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
  margin: 0;
}

.notif-message {
  font-size: 12px;
  color: #94a3b8;
  margin: 3px 0;
}

.notif-time {
  font-size: 11px;
  color: #64748b;
}

.notification-empty-state {
  text-align: center;
  padding: 24px;
  color: #94a3b8;
  font-size: 13px;
}
</style>