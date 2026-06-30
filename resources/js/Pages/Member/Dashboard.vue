<template>
  <Head title="Member Dashboard" />

  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">

      <!-- TOPBAR -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>{{ greeting }}, {{ member?.first_name }} 👋</h1>
          <p>Here's what's on your plate today</p>
        </div>

        <div class="topbar-right">
          <div class="search-wrap">
            <i class="ti ti-search search-icon" aria-hidden="true"></i>
            <input v-model="search" type="text" placeholder="Search tasks, projects…" class="search-box" />
          </div>

          <button class="theme-btn" @click="theme.toggleTheme" :title="theme.isDark ? 'Light mode' : 'Dark mode'">
            <i :class="theme.isDark ? 'ti ti-sun' : 'ti ti-moon'" aria-hidden="true"></i>
          </button>

          <!-- NOTIFICATIONS -->
          <div class="icon-wrap" v-click-outside="closeNotifications">
            <button class="icon-btn" @click.stop="toggleNotifications" aria-label="Notifications">
              <i class="ti ti-bell" aria-hidden="true"></i>
              <span v-if="unreadCount > 0" class="badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
            </button>

            <transition name="dropdown">
              <div v-if="showNotifications" class="dropdown-panel notif-panel">
                <div class="panel-head">
                  <span>Notifications</span>
                  <button v-if="unreadCount > 0" class="text-btn" @click="markAllRead">Mark all read</button>
                </div>
                <div class="panel-body">
                  <div
                    v-for="n in sortedNotifications"
                    :key="n.id"
                    class="notif-item"
                    :class="{ unread: !n.is_read }"
                    @click="markAsRead(n.id)"
                  >
                    <div class="notif-dot" :class="{ active: !n.is_read }"></div>
                    <div>
                      <p class="notif-title">{{ n.title }}</p>
                      <p class="notif-msg">{{ n.message }}</p>
                    </div>
                  </div>
                  <div v-if="!sortedNotifications.length" class="empty-state">
                    <i class="ti ti-bell-off" aria-hidden="true"></i>
                    <p>No notifications</p>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <!-- PROFILE -->
          <div class="icon-wrap" v-click-outside="closeProfile">
            <button class="avatar-btn" @click.stop="showProfileMenu = !showProfileMenu" aria-label="Profile menu">
              <span>{{ initials }}</span>
            </button>

            <transition name="dropdown">
              <div v-if="showProfileMenu" class="dropdown-panel profile-panel">
                <div class="profile-info">
                  <div class="profile-avatar">{{ initials }}</div>
                  <div>
                    <p class="profile-name">{{ member?.first_name }} {{ member?.last_name }}</p>
                    <p class="profile-role">Team Member</p>
                  </div>
                </div>
                <div class="panel-divider"></div>
                <button class="menu-item">
                  <i class="ti ti-user" aria-hidden="true"></i> Profile
                </button>
                <button class="menu-item">
                  <i class="ti ti-settings" aria-hidden="true"></i> Settings
                </button>
                <div class="panel-divider"></div>
                <button class="menu-item danger" @click="logout">
                  <i class="ti ti-logout" aria-hidden="true"></i> Logout
                </button>
              </div>
            </transition>
          </div>

        </div>
      </header>

      <div class="content-body">

        <!-- STATS -->
        <section class="stats-grid" aria-label="Summary statistics">
          <div class="stat-card">
            <div class="stat-icon purple"><i class="ti ti-folders" aria-hidden="true"></i></div>
            <div>
              <p class="stat-label">Assigned projects</p>
              <h2 class="stat-value">{{ stats?.projects ?? 0 }}</h2>
              <p class="stat-trend up">↑ active this sprint</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon blue"><i class="ti ti-checklist" aria-hidden="true"></i></div>
            <div>
              <p class="stat-label">Total tasks</p>
              <h2 class="stat-value">{{ stats?.tasks ?? 0 }}</h2>
              <p class="stat-trend muted">across all projects</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="ti ti-circle-check" aria-hidden="true"></i></div>
            <div>
              <p class="stat-label">Completed</p>
              <h2 class="stat-value">{{ stats?.completed ?? 0 }}</h2>
              <p class="stat-trend up">↑ {{ stats?.completed ?? 0 }} tasks done</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon amber"><i class="ti ti-chart-pie" aria-hidden="true"></i></div>
            <div>
              <p class="stat-label">Completion rate</p>
              <h2 class="stat-value">{{ completionRate }}%</h2>
              <div class="mini-bar-wrap">
                <div class="mini-bar">
                  <div class="mini-fill" :style="{ width: completionRate + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- MAIN GRID -->
        <div class="main-grid">

          <!-- TASKS PANEL -->
          <div class="card tasks-card">
            <div class="card-head">
              <h2>My tasks</h2>
              <div class="filter-tabs">
                <button
                  v-for="f in taskFilters"
                  :key="f.value"
                  class="filter-tab"
                  :class="{ active: taskFilter === f.value }"
                  @click="taskFilter = f.value"
                >{{ f.label }}</button>
              </div>
            </div>

            <div v-if="paginatedTasks.length" class="task-list" role="list">
              <div
                v-for="task in paginatedTasks"
                :key="task.id"
                class="task-item"
                role="listitem"
              >
                <button
                  class="task-check"
                  :class="{ done: task.status === 'Completed' }"
                  @click="toggleTask(task)"
                  :aria-label="task.status === 'Completed' ? 'Reopen task' : 'Complete task'"
                >
                  <i v-if="task.status === 'Completed'" class="ti ti-check" aria-hidden="true"></i>
                </button>
                <div class="task-info">
                  <p class="task-title" :class="{ done: task.status === 'Completed' }">{{ task.title }}</p>
                  <p class="task-sub">{{ task.project_name }}</p>
                </div>
                <div class="task-meta">
                  <span class="status-pill" :class="statusClass(task.status)">{{ task.status }}</span>
                  <span class="task-due" :class="{ overdue: isOverdue(task.deadline) }">
                    <i class="ti ti-calendar" aria-hidden="true"></i> {{ formatDate(task.deadline) }}
                  </span>
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <i class="ti ti-playlist-x" aria-hidden="true"></i>
              <p>No tasks match your filter</p>
            </div>

            <!-- PAGINATION -->
            <div v-if="filteredTasks.length > pageSize" class="pagination">
              <button class="page-btn" :disabled="page === 1" @click="page--">
                <i class="ti ti-chevron-left" aria-hidden="true"></i>
              </button>
              <span>{{ page }} / {{ totalPages }}</span>
              <button class="page-btn" :disabled="page === totalPages" @click="page++">
                <i class="ti ti-chevron-right" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <!-- SIDE PANELS -->
          <div class="side-col">

            <!-- TEAM -->
            <div class="card">
              <div class="card-head"><h2>Team</h2></div>
              <div class="team-list">
                <div v-if="teamLeader" class="team-member">
                  <div class="member-avatar leader">{{ nameInitials(teamLeader) }}</div>
                  <div class="member-info">
                    <p class="member-name">{{ teamLeader.first_name }} {{ teamLeader.last_name }}</p>
                    <p class="member-role">Team Leader</p>
                  </div>
                  <span class="role-badge leader-badge">Leader</span>
                </div>
                <div
                  v-for="m in team?.members?.filter(m => m.id !== teamLeader?.id)"
                  :key="m.id"
                  class="team-member"
                >
                  <div class="member-avatar">{{ nameInitials(m) }}</div>
                  <div class="member-info">
                    <p class="member-name">{{ m.first_name }} {{ m.last_name }}</p>
                    <p class="member-role">Member</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- PROGRESS -->
            <div class="card">
              <div class="card-head"><h2>Progress overview</h2></div>
              <div class="progress-list">
                <div class="progress-row">
                  <div class="prog-label">
                    <span>Task completion</span>
                    <span class="prog-value">{{ completionRate }}%</span>
                  </div>
                  <div class="prog-bar">
                    <div class="prog-fill purple" :style="{ width: completionRate + '%' }"></div>
                  </div>
                </div>
                <div class="progress-row">
                  <div class="prog-label">
                    <span>Project completion</span>
                    <span class="prog-value">{{ projectCompletionRate }}%</span>
                  </div>
                  <div class="prog-bar">
                    <div class="prog-fill blue" :style="{ width: projectCompletionRate + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- PROJECTS -->
        <section class="card projects-card">
          <div class="card-head">
            <h2>My projects</h2>
            <span class="count-badge">{{ filteredProjects.length }}</span>
          </div>
          <div class="project-grid">
            <div
              v-for="project in filteredProjects"
              :key="project.id"
              class="project-card"
            >
              <div class="proj-top">
                <h3 class="proj-name">{{ project.name }}</h3>
                <span class="proj-status-pill" :class="projectStatus(project).cls">
                  {{ projectStatus(project).label }}
                </span>
              </div>
              <p class="proj-meta">{{ project.completed_tasks }} / {{ project.total_tasks }} tasks</p>
              <div class="proj-bar">
                <div class="proj-fill" :style="{ width: projectProgress(project) + '%' }"></div>
              </div>
              <div class="proj-foot">
                <span class="proj-pct">{{ projectProgress(project) }}%</span>
                <span class="proj-due">
                  <i class="ti ti-clock" aria-hidden="true"></i>
                  {{ project.deadline ? formatDate(project.deadline) : 'No deadline' }}
                </span>
              </div>
            </div>
          </div>
          <div v-if="!filteredProjects.length" class="empty-state">
            <i class="ti ti-folders-off" aria-hidden="true"></i>
            <p>No projects found</p>
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

const props = defineProps({
  member:        Object,
  tasks:         Array,
  projects:      Array,
  team:          Object,
  teamLeader:    Object,
  stats:         Object,
  notifications: Array,
});

/* ─── LOCAL STATE ─── */
const search          = ref("");
const taskFilter      = ref("all");
const showNotifications = ref(false);
const showProfileMenu   = ref(false);
const page            = ref(1);
const pageSize        = 5;

const localTasks = ref([...(props.tasks ?? [])]);

const taskFilters = [
  { label: "All",        value: "all"        },
  { label: "To do",      value: "To Do"      },
  { label: "In progress",value: "In Progress" },
  { label: "Completed",  value: "Completed"  },
];

/* ─── COMPUTED ─── */
const greeting = computed(() => {
  const h = new Date().getHours();
  if (h < 12) return "Good morning";
  if (h < 18) return "Good afternoon";
  return "Good evening";
});

const initials = computed(() =>
  [props.member?.first_name, props.member?.last_name]
    .filter(Boolean)
    .map(n => n[0].toUpperCase())
    .join("") || "?"
);

const filteredTasks = computed(() => {
  const q = search.value.toLowerCase();
  return localTasks.value.filter(t => {
    const matchSearch =
      t.title?.toLowerCase().includes(q) ||
      t.project_name?.toLowerCase().includes(q);
    const matchFilter =
      taskFilter.value === "all" || t.status === taskFilter.value;
    return matchSearch && matchFilter;
  });
});

const totalPages   = computed(() => Math.max(1, Math.ceil(filteredTasks.value.length / pageSize)));
const paginatedTasks = computed(() => {
  const start = (page.value - 1) * pageSize;
  return filteredTasks.value.slice(start, start + pageSize);
});

const filteredProjects = computed(() => {
  const q = search.value.toLowerCase();
  return (props.projects ?? []).filter(p =>
    p.name?.toLowerCase().includes(q)
  );
});

const completionRate = computed(() => {
  if (!localTasks.value.length) return 0;
  const done = localTasks.value.filter(t => t.status === "Completed").length;
  return Math.round((done / localTasks.value.length) * 100);
});

const projectCompletionRate = computed(() => {
  const projects = props.projects ?? [];
  if (!projects.length) return 0;
  const total = projects.reduce((s, p) => s + (p.total_tasks ?? 0), 0);
  const done  = projects.reduce((s, p) => s + (p.completed_tasks ?? 0), 0);
  return total ? Math.round((done / total) * 100) : 0;
});

const sortedNotifications = computed(() =>
  [...(props.notifications ?? [])].sort((a, b) => a.is_read - b.is_read)
);
const unreadCount = computed(() =>
  (props.notifications ?? []).filter(n => !n.is_read).length
);

/* ─── HELPERS ─── */
const nameInitials = (person) =>
  [person?.first_name, person?.last_name]
    .filter(Boolean)
    .map(n => n[0].toUpperCase())
    .join("") || "?";

const formatDate = (d) => {
  if (!d) return "—";
  return new Date(d).toLocaleDateString("en-GB", { day: "numeric", month: "short" });
};

const isOverdue = (d) => d && new Date(d) < new Date();

const statusClass = (status) => ({
  "todo":     status === "To Do",
  "progress": status === "In Progress",
  "review":   status === "In Review",
  "done":     status === "Completed",
});

const projectProgress = (p) =>
  p.total_tasks ? Math.round((p.completed_tasks / p.total_tasks) * 100) : 0;

const projectStatus = (p) => {
  const pct = projectProgress(p);
  if (pct === 100)  return { label: "Complete",    cls: "s-done"    };
  if (pct === 0)    return { label: "Not started", cls: "s-idle"    };
  if (isOverdue(p.deadline)) return { label: "Behind", cls: "s-behind" };
  return { label: "Active", cls: "s-active" };
};

/* ─── ACTIONS ─── */
const toggleTask = (task) => {
  const t = localTasks.value.find(x => x.id === task.id);
  if (!t) return;
  t.status = t.status === "Completed" ? "In Progress" : "Completed";
  router.put(`/tasks/${task.id}/toggle`, {}, { preserveScroll: true });
};

const markAsRead = (id) => {
  router.put(`/notifications/${id}/read`, {}, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ["notifications"] }),
  });
};

const markAllRead = () => {
  router.put("/notifications/read-all", {}, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ["notifications"] }),
  });
};

const logout = () => {
  router.post("/logout", {}, {
    onSuccess: () => (window.location.href = "/login"),
  });
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) showProfileMenu.value = false;
};

const closeNotifications = () => { showNotifications.value = false; };
const closeProfile        = () => { showProfileMenu.value = false; };

/* ─── LIFECYCLE ─── */
onMounted(() => {
  notificationStore.setProjectsSource(props.projects ?? []);
});
</script>

<style scoped>
/* ── RESET ── */
*, *::before, *::after { box-sizing: border-box; }

/* ── SHELL ── */
.dashboard {
  display: flex;
  height: 100vh;
  background: var(--dashboard-bg, #0d1117);
  color: var(--text-main, #c9d1d9);
  font-family: -apple-system, BlinkMacSystemFont, "Inter", sans-serif;
  font-size: 13px;
  overflow: hidden;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ── TOPBAR ── */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 28px;
  border-bottom: 1px solid var(--border, rgba(255,255,255,.08));
  background: var(--dashboard-bg, #0d1117);
  position: sticky;
  top: 0;
  z-index: 50;
  flex-shrink: 0;
}

.topbar-left h1 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-header, #f0f6fc);
  margin: 0;
}

.topbar-left p {
  font-size: 11px;
  color: var(--text-muted, #6e7681);
  margin: 2px 0 0;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ── SEARCH ── */
.search-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 10px;
  color: var(--text-muted, #6e7681);
  font-size: 14px;
  pointer-events: none;
}

.search-box {
  padding: 8px 12px 8px 32px;
  border-radius: 10px;
  border: 1px solid var(--border, rgba(255,255,255,.08));
  background: var(--card-bg, rgba(255,255,255,.04));
  color: var(--text-header, #f0f6fc);
  font-size: 12px;
  outline: none;
  width: 220px;
  transition: border-color .15s;
}

.search-box:focus { border-color: #7c3aed; }
.search-box::placeholder { color: var(--text-muted, #6e7681); }

/* ── ICON BUTTONS ── */
.theme-btn,
.icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid var(--border, rgba(255,255,255,.08));
  background: var(--card-bg, rgba(255,255,255,.04));
  color: var(--text-muted, #6e7681);
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: background .15s, color .15s;
}

.theme-btn:hover,
.icon-btn:hover { background: rgba(255,255,255,.08); color: var(--text-header, #f0f6fc); }

.badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #7c3aed;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  border-radius: 8px;
  min-width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3px;
}

.avatar-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c3aed, #a78bfa);
  border: none;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── DROPDOWNS ── */
.icon-wrap { position: relative; }

.dropdown-panel {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: var(--panel-bg, #161b22);
  border: 1px solid var(--border, rgba(255,255,255,.1));
  border-radius: 12px;
  z-index: 200;
  overflow: hidden;
}

.notif-panel { width: 300px; }
.profile-panel { width: 200px; padding: 6px; }

.dropdown-enter-active,
.dropdown-leave-active { transition: opacity .15s, transform .15s; }
.dropdown-enter-from,
.dropdown-leave-to { opacity: 0; transform: translateY(-6px); }

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px 10px;
  border-bottom: 1px solid var(--border, rgba(255,255,255,.06));
  font-size: 12px;
  font-weight: 600;
  color: var(--text-header, #f0f6fc);
}

.text-btn {
  font-size: 11px;
  color: #7c3aed;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.panel-body { max-height: 320px; overflow-y: auto; }

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 14px;
  cursor: pointer;
  transition: background .12s;
}

.notif-item:hover { background: rgba(255,255,255,.04); }
.notif-item.unread { background: rgba(124,58,237,.06); }

.notif-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,.1);
  flex-shrink: 0;
  margin-top: 4px;
}
.notif-dot.active { background: #7c3aed; }

.notif-title {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-header, #f0f6fc);
  margin: 0 0 2px;
}

.notif-msg { font-size: 11px; color: var(--text-muted, #6e7681); margin: 0; }

.profile-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 8px 8px;
}

.profile-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #7c3aed, #a78bfa);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}

.profile-name { font-size: 12px; font-weight: 600; color: var(--text-header, #f0f6fc); margin: 0; }
.profile-role { font-size: 11px; color: var(--text-muted, #6e7681); margin: 0; }

.panel-divider { height: 1px; background: var(--border, rgba(255,255,255,.08)); margin: 4px 0; }

.menu-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--text-main, #c9d1d9);
  font-size: 12px;
  cursor: pointer;
  text-align: left;
  transition: background .12s;
}
.menu-item:hover { background: rgba(255,255,255,.05); }
.menu-item.danger { color: #f85149; }
.menu-item.danger:hover { background: rgba(248,81,73,.08); }

/* ── BODY ── */
.content-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px 28px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── STATS ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}

.stat-card {
  background: var(--card-bg, rgba(255,255,255,.03));
  border: 1px solid var(--border, rgba(255,255,255,.06));
  border-radius: 14px;
  padding: 16px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  transition: border-color .15s, transform .15s;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(124,58,237,.35);
}

.stat-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.stat-icon.purple { background: rgba(124,58,237,.15); color: #a78bfa; }
.stat-icon.blue   { background: rgba(59,130,246,.15); color: #60a5fa; }
.stat-icon.green  { background: rgba(63,185,80,.15);  color: #3fb950; }
.stat-icon.amber  { background: rgba(255,166,87,.15); color: #ffa657; }

.stat-label {
  font-size: 10px;
  color: var(--text-muted, #6e7681);
  text-transform: uppercase;
  letter-spacing: .06em;
  margin: 0;
}

.stat-value {
  font-size: 26px;
  font-weight: 800;
  color: var(--text-header, #f0f6fc);
  letter-spacing: -0.5px;
  margin: 3px 0;
  line-height: 1;
}

.stat-trend { font-size: 10px; margin: 0; }
.stat-trend.up   { color: #3fb950; }
.stat-trend.muted { color: var(--text-muted, #6e7681); }

.mini-bar-wrap { margin-top: 6px; }
.mini-bar {
  height: 4px;
  background: rgba(255,255,255,.06);
  border-radius: 6px;
  overflow: hidden;
  width: 100%;
}
.mini-fill {
  height: 100%;
  background: linear-gradient(90deg, #7c3aed, #a78bfa);
  border-radius: 6px;
}

/* ── MAIN GRID ── */
.main-grid {
  display: grid;
  grid-template-columns: 1.7fr 1fr;
  gap: 16px;
  align-items: start;
}

/* ── CARD BASE ── */
.card {
  background: var(--card-bg, rgba(255,255,255,.03));
  border: 1px solid var(--border, rgba(255,255,255,.06));
  border-radius: 16px;
  padding: 18px;
}

.card-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.card-head h2 {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--text-muted, #8b949e);
  margin: 0;
}

.count-badge {
  font-size: 10px;
  padding: 2px 7px;
  border-radius: 8px;
  background: rgba(124,58,237,.15);
  color: #a78bfa;
  font-weight: 700;
}

/* ── FILTER TABS ── */
.filter-tabs {
  display: flex;
  gap: 4px;
  margin-left: auto;
  flex-wrap: wrap;
}

.filter-tab {
  font-size: 10px;
  padding: 4px 10px;
  border-radius: 8px;
  border: 1px solid var(--border, rgba(255,255,255,.08));
  background: transparent;
  color: var(--text-muted, #6e7681);
  cursor: pointer;
  transition: .12s;
}
.filter-tab.active {
  background: rgba(124,58,237,.15);
  color: #a78bfa;
  border-color: rgba(124,58,237,.3);
}
.filter-tab:hover:not(.active) { background: rgba(255,255,255,.04); }

/* ── TASK LIST ── */
.task-list { display: flex; flex-direction: column; gap: 8px; }

.task-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 12px;
  border-radius: 12px;
  background: rgba(255,255,255,.02);
  border: 1px solid var(--border, rgba(255,255,255,.05));
  transition: background .12s, border-color .12s;
}
.task-item:hover { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.09); }

.task-check {
  width: 18px;
  height: 18px;
  border-radius: 6px;
  border: 1.5px solid var(--border-strong, rgba(255,255,255,.15));
  background: transparent;
  color: #fff;
  font-size: 11px;
  cursor: pointer;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .12s, border-color .12s;
}
.task-check.done { background: #7c3aed; border-color: #7c3aed; }

.task-info { flex: 1; min-width: 0; }
.task-title {
  font-size: 12px;
  color: var(--text-header, #f0f6fc);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0;
}
.task-title.done { text-decoration: line-through; color: var(--text-muted, #484f58); }
.task-sub { font-size: 10px; color: var(--text-muted, #6e7681); margin: 2px 0 0; }

.task-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  flex-shrink: 0;
}

.task-due {
  font-size: 10px;
  color: var(--text-muted, #6e7681);
  display: flex;
  align-items: center;
  gap: 3px;
}
.task-due.overdue { color: #f85149; }

/* ── STATUS PILLS ── */
.status-pill {
  font-size: 9px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 7px;
}
.status-pill.todo     { background: rgba(63,185,80,.1);  color: #3fb950; }
.status-pill.progress { background: rgba(124,58,237,.1); color: #a78bfa; }
.status-pill.review   { background: rgba(255,166,87,.1); color: #ffa657; }
.status-pill.done     { background: rgba(255,255,255,.06); color: var(--text-muted, #484f58); }

/* ── PAGINATION ── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 12px;
  font-size: 12px;
  color: var(--text-muted, #6e7681);
}
.page-btn {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1px solid var(--border, rgba(255,255,255,.08));
  background: transparent;
  color: var(--text-muted, #6e7681);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}
.page-btn:disabled { opacity: .35; cursor: default; }
.page-btn:not(:disabled):hover { background: rgba(255,255,255,.06); }

/* ── SIDE COL ── */
.side-col { display: flex; flex-direction: column; gap: 14px; }

/* ── TEAM ── */
.team-list { display: flex; flex-direction: column; gap: 10px; }
.team-member { display: flex; align-items: center; gap: 10px; }

.member-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1d4ed8, #60a5fa);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}
.member-avatar.leader { background: linear-gradient(135deg, #7c3aed, #a78bfa); }

.member-info { flex: 1; min-width: 0; }
.member-name { font-size: 12px; color: var(--text-header, #f0f6fc); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.member-role { font-size: 10px; color: var(--text-muted, #6e7681); margin: 0; }

.role-badge {
  font-size: 9px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 6px;
  flex-shrink: 0;
}
.leader-badge { background: rgba(124,58,237,.15); color: #a78bfa; }

/* ── PROGRESS ── */
.progress-list { display: flex; flex-direction: column; gap: 12px; }
.progress-row {}
.prog-label {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: var(--text-main, #8b949e);
  margin-bottom: 5px;
}
.prog-value { font-weight: 600; }
.prog-bar {
  height: 5px;
  background: rgba(255,255,255,.05);
  border-radius: 6px;
  overflow: hidden;
}
.prog-fill {
  height: 100%;
  border-radius: 6px;
  transition: width .4s ease;
}
.prog-fill.purple { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.prog-fill.blue   { background: linear-gradient(90deg, #1d4ed8, #60a5fa); }

/* ── PROJECTS ── */
.projects-card { margin-top: 0; }
.project-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
}

.project-card {
  padding: 14px;
  border-radius: 14px;
  background: rgba(255,255,255,.02);
  border: 1px solid var(--border, rgba(255,255,255,.05));
  transition: border-color .15s, background .15s;
  cursor: pointer;
}
.project-card:hover {
  background: rgba(255,255,255,.04);
  border-color: rgba(124,58,237,.4);
}

.proj-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 6px;
  margin-bottom: 4px;
}
.proj-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-header, #f0f6fc);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.proj-meta {
  font-size: 10px;
  color: var(--text-muted, #6e7681);
  margin: 0 0 10px;
}

.proj-status-pill {
  font-size: 9px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 6px;
  white-space: nowrap;
  flex-shrink: 0;
}
.s-active  { background: rgba(63,185,80,.1);   color: #3fb950; }
.s-done    { background: rgba(60,215,140,.1);   color: #3fb950; }
.s-behind  { background: rgba(255,166,87,.1);   color: #ffa657; }
.s-idle    { background: rgba(255,255,255,.06); color: var(--text-muted, #6e7681); }

.proj-bar {
  height: 4px;
  background: rgba(255,255,255,.05);
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 8px;
}
.proj-fill {
  height: 100%;
  background: linear-gradient(90deg, #7c3aed, #60a5fa);
  border-radius: 6px;
  transition: width .4s ease;
}

.proj-foot {
  display: flex;
  justify-content: space-between;
  font-size: 10px;
}
.proj-pct { color: #a78bfa; font-weight: 700; }
.proj-due { color: var(--text-muted, #6e7681); display: flex; align-items: center; gap: 3px; }

/* ── EMPTY STATE ── */
.empty-state {
  padding: 32px 0;
  text-align: center;
  color: var(--text-muted, #6e7681);
  font-size: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.empty-state i { font-size: 28px; opacity: .5; }
</style>