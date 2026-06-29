<template>
  <Head title="Member Dashboard" />

  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">
      <div class="content-wrapper">

        <!-- HEADER -->
        <header class="header">
          <div class="header-welcome">
            <h1>Member Dashboard</h1>
            <p>
              Welcome back, {{ member?.first_name }} {{ member?.last_name }}
            </p>

            <p v-if="teamLeader">
              Team Leader: {{ teamLeader.first_name }} {{ teamLeader.last_name }}
            </p>
          </div>

          <div class="header-right">

            <input v-model="search" type="text" placeholder="Search tasks/projects..." class="search-box" />

            <button class="theme-btn" @click="theme.toggleTheme">
              {{ theme.isDark ? '☀️' : '🌙' }}
            </button>

            <!-- NOTIFICATIONS -->
            <div class="notification-bell-container" v-click-outside="handleClickOutside">

              <button class="icon-btn"
                @click.stop="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">
                🔔
                <span v-if="unreadCount > 0" class="bell-alert-badge-dot">
                  {{ unreadCount }}
                </span>
              </button>

              <div v-if="notificationStore.showBellDropdown" class="notification-dropdown-panel">

                <div class="notification-dropdown-header">
                  <h3>Notifications</h3>
                  <button @click="markAllRead">Mark all</button>
                </div>

                <div class="notification-dropdown-body">

                  <div
                    v-for="notification in unreadNotifications"
                    :key="notification.id"
                    class="notification-item"
                    @click="markAsRead(notification.id)"
                  >
                    <p class="notif-title">{{ notification.title }}</p>
                    <p class="notif-msg">{{ notification.message }}</p>
                  </div>

                  <div v-if="unreadNotifications.length === 0" class="notification-empty-state">
                    No notifications
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

        <!-- STATS -->
        <section class="stats-grid">

          <div class="stat-card">
            <h3>Assigned Projects</h3>
            <h2>{{ stats.projects }}</h2>
          </div>

          <div class="stat-card">
            <h3>Assigned Tasks</h3>
            <h2>{{ stats.tasks }}</h2>
          </div>

          <div class="stat-card">
            <h3>Completed Tasks</h3>
            <h2>{{ stats.completed }}</h2>
          </div>

          <div class="stat-card">
            <h3>Progress</h3>
            <h2>{{ completionRate }}%</h2>
          </div>

        </section>

        <!-- MAIN CONTENT -->
        <div class="top-grid">

          <!-- TASKS -->
          <div class="dashboard-card main-panel">

            <div class="card-header">
              <h2>My Tasks</h2>
            </div>

            <div v-for="task in filteredTasks" :key="task.id" class="task-card">

              <div>
                <h3>{{ task.title }}</h3>
                <p>{{ task.project_name }}</p>
                <small>Due: {{ task.deadline }}</small>
              </div>

              <div class="task-meta">
                <span>{{ task.status }}</span>
                <button @click="toggleTask(task)">
                  {{ task.status === 'Completed' ? 'Reopen' : 'Complete' }}
                </button>
              </div>

            </div>

          </div>

          <!-- SIDE PANEL -->
          <div class="dashboard-card side-panel">

            <div class="card-header">
              <h2>Team Info</h2>
            </div>

            <div class="team-block">
              <p><strong>Team:</strong> {{ team?.name }}</p>
              <p v-if="teamLeader">
                <strong>Leader:</strong> {{ teamLeader.first_name }} {{ teamLeader.last_name }}
              </p>
            </div>

            <hr />

            <div class="card-header">
              <h2>Progress Overview</h2>
            </div>

            <div class="progress-block">
              <p>Task Completion</p>
              <div class="progress-bar">
                <div class="fill" :style="{ width: completionRate + '%' }"></div>
              </div>

              <p>Project Completion</p>
              <div class="progress-bar">
                <div class="fill" :style="{ width: projectCompletionRate + '%' }"></div>
              </div>
            </div>

          </div>

        </div>

        <!-- PROJECTS -->
        <section class="dashboard-card">

          <div class="card-header">
            <h2>My Projects</h2>
          </div>

          <div class="project-grid">

            <div v-for="project in filteredProjects" :key="project.id" class="project-card">

              <h3>{{ project.name }}</h3>

              <p>
                {{ project.completed_tasks }} / {{ project.total_tasks }} tasks
              </p>

              <div class="mini-progress">
                <div class="fill"
                  :style="{ width: projectProgress(project) + '%' }">
                </div>
              </div>

            </div>

          </div>

        </section>

      </div>
    </main>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { router, usePage, Head } from "@inertiajs/vue3";

import Sidebar from "../Leader/Sidebar.vue";
import { useThemeStore } from "../../stores/theme.js";
import { useNotificationStore } from "@/stores/notificationStore";

const theme = useThemeStore();
const notificationStore = useNotificationStore();
const page = usePage();

const props = defineProps({
  member: Object,
  tasks: Array,
  projects: Array,
  team: Object,
  teamLeader: Object,
  stats: Object,
  notifications: Array
});

const search = ref("");
const showProfileMenu = ref(false);

const localTasks = ref([...props.tasks || []]);

/* -----------------------
   COMPUTED DATA
------------------------ */

const filteredTasks = computed(() => {
  return localTasks.value.filter(t =>
    t.title?.toLowerCase().includes(search.value.toLowerCase()) ||
    t.project_name?.toLowerCase().includes(search.value.toLowerCase())
  );
});

const filteredProjects = computed(() => {
  return (props.projects || []).filter(p =>
    p.name?.toLowerCase().includes(search.value.toLowerCase())
  );
});

const stats = computed(() => props.stats || {});

const completionRate = computed(() => {
  if (!localTasks.value.length) return 0;
  const done = localTasks.value.filter(t => t.status === "Completed").length;
  return Math.round((done / localTasks.value.length) * 100);
});

const projectCompletionRate = computed(() => {
  const projects = props.projects || [];
  if (!projects.length) return 0;

  const total = projects.reduce((sum, p) => sum + (p.total_tasks || 0), 0);
  const done = projects.reduce((sum, p) => sum + (p.completed_tasks || 0), 0);

  return total ? Math.round((done / total) * 100) : 0;
});

const unreadNotifications = computed(() =>
  (props.notifications || []).filter(n => !n.is_read)
);

const unreadCount = computed(() => unreadNotifications.value.length);

/* -----------------------
   ACTIONS
------------------------ */

const toggleTask = (task) => {
  const t = localTasks.value.find(x => x.id === task.id);
  if (!t) return;

  t.status = t.status === "Completed" ? "In Progress" : "Completed";

  router.put(`/tasks/${task.id}/toggle`, {}, {
    preserveScroll: true
  });
};

const projectProgress = (project) => {
  if (!project.total_tasks) return 0;
  return Math.round((project.completed_tasks / project.total_tasks) * 100);
};

const markAsRead = (id) => {
  router.put(`/notifications/${id}/read`, {}, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ["notifications"] })
  });
};

const markAllRead = () => {
  router.put(`/notifications/read-all`, {}, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ["notifications"] })
  });
};

const logout = () => {
  router.post("/logout", {}, {
    onSuccess: () => window.location.href = "/login"
  });
};

const handleClickOutside = (e) => {
  const bell = document.querySelector(".notification-bell-container");
  const profile = document.querySelector(".profile-container");

  if (bell && !bell.contains(e.target)) {
    notificationStore.showBellDropdown = false;
  }

  if (profile && !profile.contains(e.target)) {
    showProfileMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  notificationStore.setProjectsSource(props.projects || []);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
/* =========================
   BASE LAYOUT
========================= */

.dashboard {
  display: flex;
  height: 100vh;
  background: var(--dashboard-bg, #0b0f19);
  color: var(--text-main, #cbd5e1);
  font-family: -apple-system, BlinkMacSystemFont, Inter, sans-serif;
  overflow: hidden;
}

.main-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}

.content-wrapper {
  max-width: 1400px;
  margin: 0 auto;
}

/* =========================
   HEADER
========================= */

.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
}

.header h1 {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-header, #fff);
}

.header p {
  font-size: 12px;
  color: var(--text-muted, #94a3b8);
}

/* =========================
   SEARCH + BUTTONS
========================= */

.search-box {
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.03);
  color: white;
  outline: none;
  font-size: 12px;
}

.search-box:focus {
  border-color: #6366f1;
}

.theme-btn {
  margin-left: 10px;
  padding: 8px 10px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.03);
  cursor: pointer;
}

/* =========================
   STATS GRID
========================= */

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}

.stat-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 16px;
  transition: 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(99,102,241,0.4);
}

.stat-card h3 {
  font-size: 11px;
  color: #94a3b8;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-card h2 {
  font-size: 22px;
  margin: 6px 0 0 0;
  color: #fff;
}

/* =========================
   GRID LAYOUT
========================= */

.top-grid {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 18px;
}

/* =========================
   CARD BASE
========================= */

.dashboard-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 18px;
  backdrop-filter: blur(10px);
}

.card-header h2 {
  font-size: 13px;
  text-transform: uppercase;
  color: #e2e8f0;
  margin-bottom: 12px;
}

/* =========================
   TASK CARDS
========================= */

.task-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-radius: 12px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.05);
  margin-bottom: 10px;
  transition: 0.2s ease;
}

.task-card:hover {
  background: rgba(255,255,255,0.05);
}

.task-card h3 {
  font-size: 13px;
  margin: 0;
  color: #fff;
}

.task-card p {
  font-size: 11px;
  color: #94a3b8;
  margin: 4px 0;
}

.task-card small {
  font-size: 10px;
  color: #64748b;
}

.task-meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-end;
}

.task-meta span {
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 8px;
  background: rgba(99,102,241,0.15);
  color: #818cf8;
}

/* =========================
   BUTTON
========================= */

.task-meta button {
  font-size: 10px;
  padding: 6px 10px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  background: #6366f1;
  color: white;
}

/* =========================
   TEAM + PROGRESS PANEL
========================= */

.side-panel p {
  font-size: 12px;
  margin: 6px 0;
  color: #cbd5e1;
}

/* Progress bars */
.progress-bar {
  height: 6px;
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 12px;
}

.progress-bar .fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #10b981);
}

/* =========================
   PROJECT GRID
========================= */

.project-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.project-card {
  padding: 14px;
  border-radius: 14px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.05);
}

.project-card h3 {
  font-size: 13px;
  margin: 0;
  color: #fff;
}

.project-card p {
  font-size: 11px;
  color: #94a3b8;
  margin: 6px 0;
}

.mini-progress {
  height: 4px;
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
  overflow: hidden;
  margin-top: 10px;
}

.mini-progress .fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #3b82f6);
}

/* =========================
   NOTIFICATIONS
========================= */

.notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 45px;
  width: 260px;
  background: #0b0f19;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  z-index: 100;
}

.notification-dropdown-body {
  max-height: 240px;
  overflow-y: auto;
  padding: 8px;
}

.notification-item {
  padding: 10px;
  border-radius: 10px;
  cursor: pointer;
}

.notification-item:hover {
  background: rgba(255,255,255,0.05);
}

.notif-title {
  font-size: 12px;
  font-weight: 600;
  color: #fff;
}

.notif-msg {
  font-size: 11px;
  color: #94a3b8;
}

/* =========================
   PROFILE DROPDOWN
========================= */

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 45px;
  background: #0b0f19;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px;
  width: 140px;
  padding: 6px;
}

.profile-dropdown button {
  width: 100%;
  padding: 8px;
  background: transparent;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 11px;
}
</style>