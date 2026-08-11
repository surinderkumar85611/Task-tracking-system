<template>

  <Head title="Dashboard" />

  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">

      <!-- TOPBAR -->
      <div class="topbar">
        <div class="topbar-greeting">
          <h2>
            Good to see you, {{ member.first_name }} <span class="wave">👋</span>
          </h2>
          <p>
            You have <strong>{{ stats.pending }}</strong> pending tasks today.
          </p>
        </div>

        <div class="topbar-icons">

          <div class="search-wrap">
            <svg class="search-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.6"/>
              <path d="M17 17L13.5 13.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <input v-model="search" type="text" placeholder="Search tasks or projects..." class="search-box" />
          </div>

          <div class="workspace-label" v-if="currentWorkspace">
            <span class="workspace-dot"></span>
            {{ currentWorkspace.name }}
          </div>

          <button class="theme-btn" @click="theme.toggleTheme" aria-label="Toggle theme">
            {{ theme.isDark ? '☀️' : '🌙' }}
          </button>

          <!-- NOTIFICATIONS -->
          <div class="notification-bell-container" v-click-outside="handleClickOutside">
            <button class="icon-btn" @click.stop="
              notificationStore.showBellDropdown =
              !notificationStore.showBellDropdown
              " aria-label="Notifications">
              🔔

              <span v-if="unreadCount + notificationStore.activeUrgentTasks.length > 0" class="bell-alert-green-dot">
                {{ unreadCount + notificationStore.activeUrgentTasks.length }}
              </span>
            </button>

            <div v-if="notificationStore.showBellDropdown" class="notification-dropdown-panel">
              <div class="notification-dropdown-header">
                <h3>Urgent Task Alerts</h3>
              </div>

              <div class="notification-dropdown-body">

                <div class="notification-scroll-area">

                  <!-- NORMAL NOTIFICATIONS -->
                  <div v-for="notification in unreadNotifications" :key="'notif-' + notification.id"
                    class="notification-alert-item" @click="markAsRead(notification.id)">
                    <div class="alert-item-indicator">🔔</div>

                    <div class="notification-content">
                      <p class="notification-title">
                        {{ notification.title }}
                      </p>

                      <p class="notification-message">
                        {{ notification.message }}
                      </p>

                      <span class="notification-time">
                        {{ formatNotificationDate(notification.created_at) }}
                      </span>
                    </div>
                  </div>

                  <!-- URGENT TASKS -->
                  <div v-for="task in notificationStore.activeUrgentTasks" :key="'urgent-' + task.id"
                    class="notification-alert-item urgent">
                    <div class="alert-item-indicator urgent-indicator">⚠️</div>

                    <div class="alert-item-details">
                      <p class="alert-task-title">{{ task.title }}</p>

                      <p class="alert-task-time-left"
                        :style="{ color: notificationStore.getLiveTaskMetrics(task).color }">
                        Only {{ notificationStore.getLiveTaskMetrics(task).string }} left!
                      </p>
                    </div>
                  </div>

                </div>

                <div v-if="unreadNotifications.length === 0 && notificationStore.activeUrgentTasks.length === 0"
                  class="notification-empty-state">
                  🎉 No notifications right now.
                </div>

              </div>
            </div>
          </div>

          <!-- PROFILE -->
          <div class="profile-container">
            <img
              v-if="member?.avatar_url"
              :src="member.avatar_url"
              class="avatar"
              @click.stop="showProfileMenu = !showProfileMenu"
            />
            <div
              v-else
              class="avatar avatar-fallback"
              @click.stop="showProfileMenu = !showProfileMenu"
            >
              {{ profileInitials }}
            </div>

            <div v-if="showProfileMenu" class="profile-dropdown">
              <button @click="logout">
                Logout
              </button>
            </div>
          </div>

        </div>
      </div>

      <div class="content-wrapper">

        <!-- STATS -->
        <section class="stats-grid">

          <!-- My Tasks -->
          <div class="stat-card assigned-tasks-card">
            <div class="stat-icon-badge">📋</div>
            <span class="stat-label">My Tasks</span>
            <h2 class="stat-value">{{ stats.tasks }}</h2>
            <small class="stat-subtitle">Total tasks assigned to you</small>
          </div>

          <div class="stat-card team-projects-card">
            <div class="stat-icon-badge">📁</div>
            <span class="stat-label">Team Projects</span>
            <h2 class="stat-value">{{ stats.teamProjects }}</h2>
            <small class="stat-subtitle">Active projects you're involved in</small>
          </div>

          <div class="stat-card completed-tasks-card">
            <div class="stat-icon-badge">✅</div>
            <span class="stat-label">Completed Tasks</span>
            <h2 class="stat-value">{{ stats.completed }}</h2>
            <small class="stat-subtitle">Successfully finished tasks</small>
          </div>

          <div class="stat-card pending-tasks-card">
            <div class="stat-icon-badge">⏳</div>
            <span class="stat-label">Pending Tasks</span>
            <h2 class="stat-value">{{ stats.pending }}</h2>
            <small class="stat-subtitle">Tasks awaiting completion</small>
          </div>

          <div class="stat-card completion-rate-card">
            <div class="stat-icon-badge">📈</div>
            <span class="stat-label">Completion Rate</span>
            <h2 class="stat-value">{{ completionRate }}%</h2>
            <small class="stat-subtitle">Overall task completion progress</small>
          </div>

        </section>

        <div class="top-row-grid">

          <div class="dashboard-card main-panel">
            <div class="card-header kanban-card-header">
              <h2>My Tasks</h2>
              <div class="view-toggle">
                <button :class="{ active: taskView === 'board' }" @click="taskView = 'board'">
                  🗂️ Board
                </button>
                <button :class="{ active: taskView === 'list' }" @click="taskView = 'list'">
                  📋 List
                </button>
              </div>
            </div>

            <div class="kanban-board" v-if="taskView === 'board'">
              <div v-for="column in kanbanColumns" :key="column.key" class="kanban-column">
                <div class="kanban-column-header">
                  <span class="kanban-column-dot" :class="column.key"></span>
                  <h3>{{ column.label }}</h3>
                  <span class="kanban-count">{{ column.tasks.length }}</span>
                </div>

                <div class="kanban-column-body">
                  <div v-for="task in column.tasks" :key="task.id" class="kanban-task-card" :class="column.key">
                    <div class="kanban-task-top">
                      <h4 v-html="highlightMatch(task.title)"></h4>
                      <span class="priority-badge" :class="priorityClass(task.priority)">
                        {{ task.priority || 'Normal' }}
                      </span>
                    </div>

                    <p class="task-row-project" v-if="task.project?.name">
                      📁 <span v-html="highlightMatch(task.project.name)"></span>
                    </p>

                    <small class="due-date" v-if="task.due_date">
                      Due: {{ task.due_date }}
                    </small>
                  </div>

                  <div v-if="!column.tasks.length" class="kanban-empty">
                    Nothing here
                  </div>
                </div>
              </div>
            </div>

            <div class="task-list" v-else-if="filteredTasks.length">
              <div v-for="task in filteredTasks" :key="task.id" class="task-row-card">
                <div class="task-status-dot" :class="statusClass(task.status)"></div>

                <div class="task-row-main">
                  <div class="task-row-top">
                    <h3 v-html="highlightMatch(task.title)"></h3>
                    <span class="priority-badge" :class="priorityClass(task.priority)">
                      {{ task.priority || 'Normal' }}
                    </span>
                  </div>

                  <p class="task-row-project" v-if="task.project?.name">
                    📁 <span v-html="highlightMatch(task.project.name)"></span>
                  </p>

                  <div class="task-row-bottom">
                    <span class="status-pill" :class="statusClass(task.status)">
                      {{ task.status }}
                    </span>
                    <small class="due-date" v-if="task.due_date">
                      Due: {{ task.due_date }}
                    </small>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="empty-state-inline">
              🎉 No tasks match your search.
            </div>
          </div>

          <div class="dashboard-card donut-card">
            <div class="card-header">
              <h2>Completion Rate</h2>
            </div>

            <div class="donut-wrap">
              <svg viewBox="0 0 120 120" class="donut-svg">
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--border-deep)" stroke-width="12" />
                <circle
                  cx="60" cy="60" r="50" fill="none"
                  stroke="var(--c-green)" stroke-width="12" stroke-linecap="round"
                  :stroke-dasharray="2 * Math.PI * 50"
                  :stroke-dashoffset="(2 * Math.PI * 50) * (1 - completionRate / 100)"
                  transform="rotate(-90 60 60)"
                />
              </svg>
              <div class="donut-center">
                <strong>{{ completionRate }}%</strong>
                <span>Completed</span>
              </div>
            </div>

            <div class="donut-footer">
              <div class="donut-stat">
                <strong>{{ stats.completed }}</strong>
                <span>Done</span>
              </div>
              <div class="donut-stat">
                <strong>{{ stats.pending }}</strong>
                <span>Pending</span>
              </div>
            </div>
          </div>

          <div class="dashboard-card today-activity-card">
            <div class="card-header">
              <h2>Today's Activity</h2>
            </div>

            <ul class="activity-stat-list">
              <li>
                <span class="ai-icon">📅</span>
                <span class="ai-label">Due Today</span>
                <span class="ai-value">{{ dueToday }}</span>
              </li>
              <li>
                <span class="ai-icon">🔥</span>
                <span class="ai-label">Overdue</span>
                <span class="ai-value">{{ overdueTasks }}</span>
              </li>
              <li>
                <span class="ai-icon">⏰</span>
                <span class="ai-label">Upcoming</span>
                <span class="ai-value">{{ upcomingTasks }}</span>
              </li>
              <li>
                <span class="ai-icon">📈</span>
                <span class="ai-label">Progress</span>
                <span class="ai-value">{{ completionRate }}%</span>
              </li>
            </ul>

            <div class="daily-goal">
              <div class="daily-goal-head">
                <span>Daily Goal</span>
                <strong>{{ completionRate }}%</strong>
              </div>
              <div class="daily-goal-bar">
                <div class="daily-goal-fill" :style="{ width: completionRate + '%' }"></div>
              </div>
            </div>
          </div>

        </div>

        <div class="mid-grid">

          <section class="dashboard-card">
            <div class="card-header">
              <h2>Team Members</h2>
            </div>

            <div class="rep-list">
              <div v-for="(teammate, index) in (teamMembers || [])" :key="teammate.id" class="rep-row">
                <span class="rep-rank">{{ index + 1 }}</span>

                <div class="rep-avatar">
                  {{ teammate.first_name?.charAt(0)?.toUpperCase() }}
                </div>

                <div class="rep-info">
                  <strong>{{ teammate.first_name }} {{ teammate.last_name }}</strong>
                  <span>{{ teammate.department || teammate.role }}</span>
                </div>

                <span class="rep-status">
                  <span class="status-dot-online"></span>
                  Active
                </span>
              </div>

              <div v-if="!(teamMembers || []).length" class="empty-state-inline">
                No team members found.
              </div>
            </div>
          </section>

          <section class="dashboard-card">
            <div class="card-header">
              <h2>Recent Activity</h2>
            </div>

            <div class="leads-table">
              <div class="leads-table-head">
                <span>Activity</span>
                <span>Project</span>
                <span>By</span>
              </div>

              <div v-for="activity in (recentActivity || [])" :key="activity.id" class="leads-row">
                <div class="lead-identity">
                  <span class="lead-dot" :class="activityDotClass(activity.type)"></span>
                  <span class="lead-title" :title="activity.message">
                    {{ activity.message }}
                  </span>
                </div>

                <span class="lead-project">
                  {{ activity.project_name || activity.task_title || '—' }}
                </span>

                <span class="status-pill" :class="{ 'is-you': activity.is_own }">
                  {{ activity.is_own ? 'You' : activity.actor_name }}
                </span>
              </div>

              <div v-if="!(recentActivity || []).length" class="empty-state-inline">
                No recent activity yet.
              </div>
            </div>
          </section>

        </div>

        <div class="mid-grid">

          <section class="dashboard-card">
            <div class="card-header">
              <h2>Team Projects</h2>
            </div>

            <div class="project-list">
              <div v-for="project in filteredProjects" :key="project.id" class="project-mini-card">
                <div class="project-card-meta">
                  <h3 v-html="highlightMatch(project.name)"></h3>
                  <span class="project-percentage">
                    {{ project.progress || 0 }}%
                  </span>
                </div>

                <small class="due-date">
                  Due: {{ project.deadline || 'No deadline' }}
                </small>

                <div class="mini-progress">
                  <div class="mini-progress-fill" :style="{ width: (project.progress || 0) + '%' }"></div>
                </div>
                <div class="project-footer">

                  <span>

                    📋 {{ project.tasks_count || 0 }} Tasks

                  </span>

                  <span>

                    👥 {{ project.members_count || 0 }} Members

                  </span>

                </div>
              </div>

              <div v-if="!filteredProjects.length" class="empty-state-inline">
                No team projects found.
              </div>
            </div>
          </section>

          <section class="dashboard-card">
            <div class="card-header">
              <h2>Upcoming Deadlines</h2>
            </div>

            <div class="timeline-list">
              <div v-for="task in upcomingDeadlineTasks" :key="task.id" class="timeline-item">
                <span class="timeline-dot" :class="statusClass(task.status)"></span>

                <div class="timeline-content">
                  <span class="timeline-date">{{ task.due_date || 'No deadline' }}</span>
                  <strong>{{ task.title }}</strong>
                  <small v-if="task.project?.name">{{ task.project.name }}</small>
                </div>
              </div>

              <div v-if="!upcomingDeadlineTasks.length" class="empty-state-inline">
                No upcoming deadlines.
              </div>
            </div>
          </section>

        </div>

        
      </div>
    </main>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import Sidebar from "./Sidebar.vue";
import { useThemeStore } from "../../stores/theme.js";
import { useNotificationStore } from "@/stores/notificationStore";
import { Head } from "@inertiajs/vue3";

const theme = useThemeStore();
const notificationStore = useNotificationStore();
const page = usePage();

const props = defineProps({
  member: Object,
  myTasks: { type: Array, default: () => [] },
  teamProjects: { type: Array, default: () => [] },
  teamMembers: Array,
  stats: Object,
  currentWorkspaceId: Number,
  notifications: Array,
  recentActivity: { type: Array, default: () => [] }
});

const search = ref("");
const showProfileMenu = ref(false);
const taskView = ref("board");
let notificationRefreshInterval = null;

const workspaces = computed(() => page.props.workspaces || []);
const currentWorkspace = computed(() => workspaces.value[0] || null);

const profileInitials = computed(() => {
  const first = props.member?.first_name?.charAt(0) || "";
  const last = props.member?.last_name?.charAt(0) || "";
  const initials = `${first}${last}`.toUpperCase();
  return initials || "U";
});

const escapeHtml = (value) => {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
};

const escapeRegExp = (value) => {
  return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
};

const highlightMatch = (text) => {
  const safeText = escapeHtml(text);
  const term = search.value.trim();

  if (!term) return safeText;

  const regex = new RegExp(`(${escapeRegExp(term)})`, "ig");
  return safeText.replace(regex, '<mark class="search-highlight">$1</mark>');
};

const filteredTasks = computed(() => {
  if (!search.value) return props.myTasks || [];
  const term = search.value.toLowerCase();
  return (props.myTasks || []).filter(task =>
    task.title?.toLowerCase().includes(term) ||
    task.project?.name?.toLowerCase().includes(term)
  );
});

const filteredProjects = computed(() => {
  if (!search.value) return props.teamProjects || [];
  const term = search.value.toLowerCase();
  return (props.teamProjects || []).filter(project =>
    project.name?.toLowerCase().includes(term)
  );
});

const completionRate = computed(() => {
  if (!props.stats?.tasks) return 0;
  return Math.round((props.stats.completed / props.stats.tasks) * 100);
});

const KANBAN_DEFS = [
  { key: "todo", label: "To Do", match: ["todo", "to-do", "to do", "backlog"] },
  { key: "in-progress", label: "In Progress", match: ["in-progress", "in progress", "inprogress", "doing"] },
  { key: "completed", label: "Completed", match: ["completed", "done", "complete"] }
];

const kanbanColumns = computed(() => {
  const tasks = filteredTasks.value;

  return KANBAN_DEFS.map(def => ({
    key: def.key,
    label: def.label,
    tasks: tasks.filter(task => {
      const status = (task.status || "").toLowerCase().trim();
      return def.match.includes(status);
    })
  }));
});

const statusClass = (status) => {
  return (status || "").toLowerCase().replace(/\s+/g, "-");
};

const priorityClass = (priority) => {
  return (priority || "normal").toLowerCase();
};

const startOfDay = (d) => {
  const copy = new Date(d);
  copy.setHours(0, 0, 0, 0);
  return copy;
};

const today = computed(() => startOfDay(new Date()));

const isCompletedTask = (task) => (task.status || "").toLowerCase() === "completed";

const parsedDueDate = (task) => {
  if (!task.due_date) return null;
  const d = new Date(task.due_date);
  if (isNaN(d.getTime())) return null;
  return startOfDay(d);
};

const dueToday = computed(() => {
  return (props.myTasks || []).filter(task => {
    if (isCompletedTask(task)) return false;
    const due = parsedDueDate(task);
    return due && due.getTime() === today.value.getTime();
  }).length;
});

const overdueTasks = computed(() => {
  return (props.myTasks || []).filter(task => {
    if (isCompletedTask(task)) return false;
    const due = parsedDueDate(task);
    return due && due.getTime() < today.value.getTime();
  }).length;
});

const upcomingTasks = computed(() => {
  return (props.myTasks || []).filter(task => {
    if (isCompletedTask(task)) return false;
    const due = parsedDueDate(task);
    return due && due.getTime() > today.value.getTime();
  }).length;
});

const upcomingDeadlineTasks = computed(() => {
  return (props.myTasks || [])
    .filter(task => !isCompletedTask(task) && parsedDueDate(task))
    .sort((a, b) => parsedDueDate(a) - parsedDueDate(b))
    .slice(0, 5);
});

const activityDotClass = (type) => {
  const key = (type || "").toLowerCase();
  if (key.includes("completed")) return "completed";
  if (key.includes("urgent") || key.includes("priority")) return "todo";
  if (key.includes("status") || key.includes("progress")) return "in-progress";
  return "";
};

watch(
  () => props.teamProjects,
  (projects) => {
    notificationStore.setProjectsSource(projects || []);
  },
  { immediate: true, deep: true }
);

const handleClickOutside = (event) => {
  const bell = document.querySelector(".notification-bell-container");
  const profile = document.querySelector(".profile-container");

  if (bell && !bell.contains(event.target)) {
    notificationStore.showBellDropdown = false;
  }

  if (profile && !profile.contains(event.target)) {
    showProfileMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);

  notificationStore.setProjectsSource(props.teamProjects || []);

  notificationRefreshInterval = setInterval(() => {
    router.reload({ only: ["notifications"] });
  }, 30000);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);

  if (notificationRefreshInterval) {
    clearInterval(notificationRefreshInterval);
  }
});

const logout = () => {
  router.post("/logout", {}, {
    replace: true,
    onSuccess: () => {
      window.location.href = "/login";
    }
  });
};

const formatNotificationDate = (date) => {
  if (!date) return "";

  const now = new Date();
  const created = new Date(date);

  const diffMinutes = Math.floor(
    (now - created) / 1000 / 60
  );

  if (diffMinutes < 1) return "Just now";
  if (diffMinutes < 60) return `${diffMinutes} min ago`;

  const diffHours = Math.floor(diffMinutes / 60);

  if (diffHours < 24) return `${diffHours} hr ago`;

  return created.toLocaleDateString();
};

const unreadNotifications = computed(() => {
  return [...(props.notifications || [])]
    .filter(n => !n.is_read)
    .sort((a, b) => {
      return new Date(b.created_at) - new Date(a.created_at);
    });
});

const unreadCount = computed(() => {
  return (props.notifications || []).filter(n => !n.is_read).length;
});

const markAsRead = (id) => {
  router.put(`/notifications/${id}/read`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ["notifications"] });
    }
  });
};

const markAllRead = () => {
  router.put("/notifications/read-all", {}, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ["notifications"] });
    }
  });
};
</script>
<style scoped>

.theme-dark {
  --dashboard-bg: #222736;
  --panel-bg: #2a2f42;
  --card-inner-bg: #262b3d;
  --card-inner-hover: #313749;
  --activity-item-bg: #262b3d;
  --stat-card-bg: #2a2f42;
  --input-element-bg: #323a4f;
  --input-element-focus: #3a4258;
  --dropdown-panel-bg: #2a2f42;
  --border-subtle: rgba(255, 255, 255, 0.07);
  --border-deep: rgba(255, 255, 255, 0.12);
  --border-divider: rgba(255, 255, 255, 0.07);
  --text-main: #e4e6ef;
  --text-header: #f6f7fb;
  --text-muted: #8590a6;
  --text-card-sub: #c1c5d6;
  --due-date-color: #74809a;
  --shadow-cards: rgba(0, 0, 0, 0.28);
  --shadow-stats: rgba(0, 0, 0, 0.22);
  --shadow-stats-hover: rgba(0, 0, 0, 0.34);
  --accent: #556ee6;
  --accent-2: #6f7fee;
  --accent-soft: rgba(85, 110, 230, 0.16);
}

.theme-light {
  --dashboard-bg: #eef0f7;
  --panel-bg: #ffffff;
  --card-inner-bg: #f7f8fb;
  --card-inner-hover: #eef0f6;
  --activity-item-bg: #f7f8fb;
  --stat-card-bg: #ffffff;
  --input-element-bg: #f2f3f8;
  --input-element-focus: #eaecf3;
  --dropdown-panel-bg: #ffffff;
  --border-subtle: rgba(33, 37, 61, 0.07);
  --border-deep: rgba(33, 37, 61, 0.1);
  --border-divider: rgba(33, 37, 61, 0.06);
  --text-main: #33374d;
  --text-header: #22263d;
  --text-muted: #878ea3;
  --text-card-sub: #495071;
  --due-date-color: #9297ac;
  --shadow-cards: rgba(56, 65, 109, 0.07);
  --shadow-stats: rgba(56, 65, 109, 0.05);
  --shadow-stats-hover: rgba(56, 65, 109, 0.1);
  --accent: #556ee6;
  --accent-2: #6f7fee;
  --accent-soft: rgba(85, 110, 230, 0.08);
}

.dashboard {
  --c-blue: #556ee6;
  --c-violet: #8b6ee8;
  --c-green: #34c38f;
  --c-amber: #f1b44c;
  --c-cyan: #50a5f1;
  --c-red: #f46a6a;
}

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
  max-width: 1520px;
  margin: 0 auto;
  width: 100%;
  padding: 24px 36px 56px;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 14px 28px;
  background: var(--panel-bg);
  border-bottom: 1px solid var(--border-subtle);
  position: sticky;
  top: 0;
  z-index: 40;
}

.topbar-icons {
  display: flex;
  align-items: center;
  gap: 10px;
}

.topbar-greeting h2 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  letter-spacing: -0.1px;
  color: var(--text-header);
}

.topbar-greeting .wave {
  display: inline-block;
}

.topbar-greeting p {
  margin-top: 3px;
  color: var(--text-muted);
  font-size: 12.5px;
  font-weight: 400;
}

.topbar-greeting p strong {
  color: var(--accent);
  font-weight: 700;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.header h1 {
  font-size: 21px;
  font-weight: 700;
  margin: 0 0 3px 0;
  letter-spacing: -0.2px;
  color: var(--text-header);
}

.header p {
  color: var(--text-muted);
  font-weight: 400;
  margin: 0;
  font-size: 13px;
}

.workspace-label {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 12.5px;
  font-weight: 500;
  color: var(--text-card-sub);
  background: var(--input-element-bg);
  border: 1px solid var(--border-subtle);
  padding: 8px 13px;
  border-radius: 8px;
}

.workspace-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--c-green);
  flex-shrink: 0;
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
  width: 230px;
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
  background: var(--panel-bg);
}

.theme-btn,
.icon-btn {
  background: var(--input-element-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-main);
  width: 36px;
  height: 36px;
  font-size: 14px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
  position: relative;
}

.theme-btn:hover,
.icon-btn:hover {
  background: var(--card-inner-hover);
  border-color: var(--border-deep);
}

.welcome-banner {
  background: linear-gradient(120deg, #3b4f9e 0%, #4a5cb0 100%);
  color: #ffffff;
  padding: 22px 26px;
  border-radius: 10px;
  margin-bottom: 22px;
  box-shadow: 0 4px 14px var(--shadow-cards);
}

.welcome-banner h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  letter-spacing: -0.1px;
}

.wave {
  display: inline-block;
}

.welcome-banner p {
  margin-top: 5px;
  opacity: 0.85;
  font-size: 13px;
  font-weight: 400;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

@media (max-width: 1400px) {
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .stats-grid { grid-template-columns: 1fr; }
}

.stat-card {
  position: relative;
  display: flex;
  flex-direction: column;
  background: var(--stat-card-bg);
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

.assigned-tasks-card::before { background: var(--c-blue); }
.team-projects-card::before { background: var(--c-violet); }
.completed-tasks-card::before { background: var(--c-green); }
.pending-tasks-card::before { background: var(--c-amber); }
.completion-rate-card::before { background: var(--c-cyan); }

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

.assigned-tasks-card .stat-icon-badge { background: rgba(85, 110, 230, 0.14); }
.team-projects-card .stat-icon-badge { background: rgba(139, 110, 232, 0.14); }
.completed-tasks-card .stat-icon-badge { background: rgba(52, 195, 143, 0.14); }
.pending-tasks-card .stat-icon-badge { background: rgba(241, 180, 76, 0.16); }
.completion-rate-card .stat-icon-badge { background: rgba(80, 165, 241, 0.14); }

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

.top-row-grid {
  display: grid;
  grid-template-columns: 1.6fr 0.7fr 0.9fr;
  gap: 20px;
  margin-bottom: 20px;
  align-items: stretch;
}

@media (max-width: 1300px) {
  .top-row-grid { grid-template-columns: 1fr 1fr; }
  .top-row-grid .main-panel { grid-column: 1 / -1; }
}
@media (max-width: 760px) {
  .top-row-grid { grid-template-columns: 1fr; }
  .top-row-grid .main-panel { grid-column: auto; }
}

.dashboard-card {
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 10px;
  padding: 24px;
  box-shadow: 0 2px 6px var(--shadow-cards);
}

.card-header {
  margin-bottom: 18px;
}

.card-header h2 {
  font-size: 15px;
  font-weight: 600;
  margin: 0;
  color: var(--text-header);
  letter-spacing: -0.1px;
}

.donut-card {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.donut-wrap {
  position: relative;
  width: 148px;
  height: 148px;
  margin: 4px auto 18px;
}

.donut-svg {
  width: 100%;
  height: 100%;
}

.donut-svg circle {
  transition: stroke-dashoffset 0.4s ease;
}

.donut-center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.donut-center strong {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-header);
  letter-spacing: -0.4px;
}

.donut-center span {
  font-size: 11.5px;
  color: var(--text-muted);
  font-weight: 500;
  margin-top: 2px;
}

.donut-footer {
  display: flex;
  width: 100%;
  gap: 12px;
  border-top: 1px solid var(--border-divider);
  padding-top: 16px;
}

.donut-stat {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.donut-stat strong {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-header);
}

.donut-stat span {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 500;
  margin-top: 2px;
}

.today-activity-card {
  background: linear-gradient(150deg, #e8834f 0%, #d8556a 100%);
  border: none;
  color: #ffffff;
  display: flex;
  flex-direction: column;
}

.today-activity-card .card-header h2 {
  color: #ffffff;
}

.activity-stat-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.activity-stat-list li {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ai-icon {
  font-size: 14px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.ai-label {
  font-size: 13px;
  font-weight: 500;
  opacity: 0.92;
  flex: 1;
}

.ai-value {
  font-size: 14px;
  font-weight: 700;
}

.daily-goal {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.22);
}

.daily-goal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12.5px;
  font-weight: 600;
  margin-bottom: 8px;
  opacity: 0.95;
}

.daily-goal-bar {
  height: 6px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.28);
  overflow: hidden;
}

.daily-goal-fill {
  height: 100%;
  border-radius: 999px;
  background: #ffffff;
}

.mid-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

@media (max-width: 1000px) {
  .mid-grid { grid-template-columns: 1fr; }
}

.kanban-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.view-toggle {
  display: flex;
  gap: 2px;
  background: var(--input-element-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 8px;
  padding: 3px;
}

.view-toggle button {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 11.5px;
  font-weight: 500;
  padding: 6px 11px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.view-toggle button.active {
  background: var(--accent);
  color: #ffffff;
}

.kanban-board {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

@media (max-width: 800px) {
  .kanban-board { grid-template-columns: 1fr; }
}

.kanban-column {
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 10px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  min-height: 120px;
}

.kanban-column-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  padding: 0 2px;
}

.kanban-column-header h3 {
  font-size: 11.5px;
  font-weight: 600;
  margin: 0;
  color: var(--text-card-sub);
  text-transform: uppercase;
  letter-spacing: 0.4px;
  flex: 1;
}

.kanban-column-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}

.kanban-column-dot.todo { background: var(--c-amber); }
.kanban-column-dot.in-progress { background: var(--c-blue); }
.kanban-column-dot.completed { background: var(--c-green); }

.kanban-count {
  font-size: 10.5px;
  font-weight: 600;
  color: var(--text-muted);
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 6px;
  padding: 2px 7px;
}

.kanban-column-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.kanban-task-card {
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 8px;
  padding: 12px;
  transition: border-color 0.15s ease;
}

.kanban-task-card:hover {
  border-color: var(--border-deep);
}

.kanban-task-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}

.kanban-task-top h4 {
  font-size: 13px;
  font-weight: 500;
  margin: 0;
  color: var(--text-main);
  line-height: 1.35;
}

.kanban-task-card .task-row-project {
  margin: 8px 0 0 0;
  font-size: 12px;
}

.kanban-task-card .due-date {
  display: block;
  margin-top: 8px;
  font-size: 11px;
}

.kanban-empty {
  text-align: center;
  padding: 18px 0;
  color: var(--text-muted);
  font-size: 12px;
  border: 1px dashed var(--border-deep);
  border-radius: 8px;
}

.task-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.task-row-card {
  display: flex;
  align-items: flex-start;
  gap: 13px;
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 9px;
  padding: 15px 16px;
  transition: border-color 0.15s ease;
}

.task-row-card:hover {
  border-color: var(--border-deep);
}

.task-status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
  background: var(--text-muted);
}

.task-status-dot.in-progress { background: var(--c-blue); }
.task-status-dot.completed { background: var(--c-green); }
.task-status-dot.todo { background: var(--c-amber); }

.status-pill.in-progress { background: rgba(85, 110, 230, 0.12); color: var(--c-blue); }
.status-pill.completed { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }
.status-pill.todo { background: rgba(241, 180, 76, 0.15); color: #b9822e; }

.task-row-main {
  flex: 1;
  min-width: 0;
}

.task-row-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.task-row-top h3 {
  font-size: 13.5px;
  font-weight: 500;
  margin: 0;
  color: var(--text-main);
}

.task-row-project {
  margin: 5px 0 0 0;
  font-size: 12px;
  color: var(--text-muted);
  font-weight: 400;
}

.task-row-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 10px;
}

.priority-badge {
  font-size: 10px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 5px;
  text-transform: capitalize;
  flex-shrink: 0;
  background: rgba(137, 147, 171, 0.14);
  color: var(--text-muted);
}

.priority-badge.high { background: rgba(244, 106, 106, 0.12); color: var(--c-red); }
.priority-badge.medium { background: rgba(241, 180, 76, 0.15); color: #b9822e; }
.priority-badge.low { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }

.status-pill {
  font-size: 10px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 5px;
  text-transform: capitalize;
  background: rgba(137, 147, 171, 0.14);
  color: var(--text-muted);
}

.empty-state-inline {
  text-align: center;
  padding: 28px 0;
  color: var(--text-muted);
  font-size: 13px;
}

.project-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.project-mini-card {
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 9px;
  padding: 16px;
  transition: border-color 0.15s ease;
}

.project-mini-card:hover {
  border-color: var(--border-deep);
}

.project-card-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.project-mini-card h3 {
  font-size: 13.5px;
  font-weight: 500;
  margin: 0;
  color: var(--text-main);
}

.project-percentage {
  font-size: 11.5px;
  font-weight: 600;
  color: var(--c-violet);
  background: rgba(139, 110, 232, 0.12);
  padding: 3px 8px;
  border-radius: 5px;
}

.due-date {
  color: var(--due-date-color);
  font-size: 12px;
  font-weight: 400;
}

.mini-progress {
  height: 5px;
  background: var(--border-deep);
  border-radius: 6px;
  overflow: hidden;
  margin-top: 12px;
}

.mini-progress-fill {
  height: 100%;
  background: var(--c-violet);
  border-radius: 6px;
}

.project-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 11px;
  color: var(--text-muted);
  font-size: 11.5px;
}

.rep-list {
  display: flex;
  flex-direction: column;
}

.rep-row {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 12px 4px;
  border-bottom: 1px solid var(--border-divider);
  transition: background 0.15s ease;
}

.rep-row:last-child {
  border-bottom: none;
}

.rep-row:hover {
  background: var(--card-inner-hover);
}

.rep-rank {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-muted);
  font-size: 10.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rep-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--c-cyan);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 12.5px;
  flex-shrink: 0;
}

.rep-row:nth-child(5n+2) .rep-avatar { background: var(--c-blue); }
.rep-row:nth-child(5n+3) .rep-avatar { background: var(--c-violet); }
.rep-row:nth-child(5n+4) .rep-avatar { background: var(--c-cyan); }
.rep-row:nth-child(5n+5) .rep-avatar { background: var(--c-amber); }
.rep-row:nth-child(5n+6) .rep-avatar { background: var(--c-green); }

.rep-info {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.rep-info strong {
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-main);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.rep-info span {
  font-size: 12px;
  color: var(--text-muted);
  margin-top: 2px;
}

.rep-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 500;
  flex-shrink: 0;
}

.status-dot-online {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--c-green);
  flex-shrink: 0;
}

.leads-table {
  display: flex;
  flex-direction: column;
}

.leads-table-head {
  display: grid;
  grid-template-columns: 1fr 140px 110px;
  align-items: center;
  padding: 0 4px 12px;
  border-bottom: 1px solid var(--border-divider);
  margin-bottom: 4px;
}

.leads-table-head span {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-muted);
}

.leads-row {
  display: grid;
  grid-template-columns: 1fr 140px 110px;
  align-items: center;
  padding: 13px 4px;
  border-bottom: 1px solid var(--border-divider);
  transition: background 0.15s ease;
}

.leads-row:last-child {
  border-bottom: none;
}

.leads-row:hover {
  background: var(--card-inner-hover);
}

.lead-identity {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.lead-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--text-muted);
  flex-shrink: 0;
}

.lead-dot.in-progress { background: var(--c-blue); }
.lead-dot.completed { background: var(--c-green); }
.lead-dot.todo { background: var(--c-amber); }

.lead-title {
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-main);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.lead-project {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 400;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-pill.is-you {
  background: var(--accent-soft);
  color: var(--accent);
}

.timeline-list {
  display: flex;
  flex-direction: column;
}

.timeline-item {
  display: flex;
  gap: 14px;
  padding: 12px 4px;
  position: relative;
}

.timeline-item::before {
  content: "";
  position: absolute;
  left: 8px;
  top: 26px;
  bottom: -4px;
  width: 1px;
  background: var(--border-divider);
}

.timeline-item:last-child::before {
  display: none;
}

.timeline-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: var(--text-muted);
  flex-shrink: 0;
  margin-top: 4px;
  box-shadow: 0 0 0 3px var(--card-inner-bg);
  z-index: 1;
}

.timeline-dot.in-progress { background: var(--c-blue); }
.timeline-dot.completed { background: var(--c-green); }
.timeline-dot.todo { background: var(--c-amber); }

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.timeline-date {
  font-size: 11px;
  font-weight: 600;
  color: var(--accent);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.timeline-content strong {
  font-size: 13.5px;
  font-weight: 500;
  color: var(--text-main);
}

.timeline-content small {
  font-size: 12px;
  color: var(--text-muted);
}

.profile-container {
  position: relative;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  border: 1px solid var(--border-subtle);
}

.avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 46px;
  background: var(--dropdown-panel-bg);
  border: 1px solid var(--border-deep);
  border-radius: 9px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 10px 24px var(--shadow-cards);
  min-width: 140px;
}

.profile-dropdown button {
  background: transparent;
  border: none;
  color: var(--text-main);
  padding: 11px 16px;
  cursor: pointer;
  width: 100%;
  text-align: left;
  font-size: 13px;
  font-weight: 400;
}

.profile-dropdown button:hover {
  background: var(--card-inner-hover);
}

.notification-bell-container {
  position: relative;
}

.notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 46px;
  width: 320px;
  background: var(--dropdown-panel-bg);
  border: 1px solid var(--border-deep);
  border-radius: 11px;
  box-shadow: 0 16px 36px -10px var(--shadow-cards);
  z-index: 50;
  overflow: hidden;
}

.notification-dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 16px;
  border-bottom: 1px solid var(--border-deep);
  color: var(--text-main);
}

.notification-dropdown-header h3 {
  margin: 0;
  font-size: 13.5px;
  font-weight: 600;
}

.notification-dropdown-body {
  padding: 10px;
  max-height: 320px;
  overflow-y: auto;
  color: var(--text-main);
}

.notification-scroll-area {
  max-height: 280px;
  overflow-y: auto;
}

.notification-alert-item {
  display: flex;
  gap: 11px;
  padding: 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s ease;
  border: 1px solid var(--border-subtle);
  margin-bottom: 6px;
}

.notification-alert-item:hover {
  background: var(--card-inner-hover);
}

.alert-item-indicator {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--accent-soft);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 12px;
}

.urgent-indicator {
  background: rgba(244, 106, 106, 0.14);
}

.notification-title {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-main);
  margin: 0;
}

.notification-message {
  font-size: 11.5px;
  color: var(--text-muted);
  margin: 3px 0;
}

.notification-time {
  font-size: 10.5px;
  color: var(--text-muted);
}

.alert-task-title {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--text-main);
  margin: 0;
}

.alert-task-time-left {
  font-size: 11.5px;
  font-weight: 600;
  margin: 3px 0 0 0;
}

.notification-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 28px 16px;
  color: var(--text-muted);
  text-align: center;
  font-size: 12.5px;
}

.icon-btn {
  position: relative;
}

.bell-alert-green-dot {
  position: absolute;
  top: -3px;
  right: -3px;
  min-width: 16px;
  height: 16px;
  border-radius: 999px;
  background: var(--c-red);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9.5px;
  font-weight: 700;
  padding: 0 4px;
  border: 2px solid var(--panel-bg);
}

/* --- Quick Links --- */
.quick-links {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

@media (max-width: 700px) {
  .quick-links { grid-template-columns: repeat(2, 1fr); }
}

.quick-links button {
  padding: 14px;
  border: 1px solid var(--border-subtle);
  border-radius: 8px;
  cursor: pointer;
  background: var(--card-inner-bg);
  color: var(--text-main);
  transition: all 0.15s ease;
  font-weight: 500;
  font-size: 13px;
}

.quick-links button:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.dashboard-divider {
  border: none;
  border-top: 1px solid var(--border-divider);
  margin: 20px 0;
}

.search-highlight {
  background: var(--accent-soft);
  color: var(--accent);
  padding: 0 2px;
  border-radius: 3px;
  font-weight: 700;
}
</style>