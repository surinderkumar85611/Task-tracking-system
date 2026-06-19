<template>
  <Head title="System Workspace Core" />
  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">
      <div class="content-wrapper">
        
        <header class="header">
          <div class="header-welcome">
            <span class="live-pulse-badge">
              <span class="pulse-dot"></span> Node Core Active
            </span>
            <h1>Workspace Index</h1>
            <p>Authentication node verified: {{ member?.first_name }} {{ member?.last_name }}</p>
          </div>

          <div class="header-right">
            <div class="workspace-chip" v-if="currentWorkspace">
              <div class="chip-indicator"></div>
              <span class="chip-text">{{ currentWorkspace.name }}</span>
            </div>

            <div class="search-wrapper">
              <span class="search-icon">//</span>
              <input v-model="search" type="text" placeholder="Search system tokens..." class="search-box" />
            </div>

            <button class="action-control-btn" @click="theme.toggleTheme">
              <span class="theme-icon-fallback">{{ theme.isDark ? '⚡' : '🌌' }}</span>
            </button>

            <div class="notification-bell-container" v-click-outside="handleClickOutside">
              <button class="action-control-btn" @click.stop="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">
                <span class="bell-icon-inner">⚿</span>
                <span v-if="unreadNotifications.length" class="notification-badge">
                  {{ unreadNotifications.length }}
                </span>
              </button>

              <div v-if="notificationStore?.showBellDropdown" class="notification-dropdown-panel">
                <div class="notification-dropdown-header">
                  <h3>Leader Broadcast Stack</h3>
                  <button @click="markAllRead" class="mark-all-btn">Flush All</button>
                </div>
                <div class="notification-dropdown-body">
                  <div v-for="notification in unreadNotifications" :key="notification.id" class="notification-alert-item" @click="markAsRead(notification.id)">
                    <div class="alert-item-indicator">◆</div>
                    <div class="alert-item-details">
                      <p class="alert-task-title">{{ notification.title }}</p>
                      <p class="alert-task-time-left">{{ notification.message }}</p>
                    </div>
                  </div>
                  <div v-if="unreadNotifications.length === 0" class="notification-empty-state">
                    Zero Interrupt Latency
                  </div>
                </div>
              </div>
            </div>

            <div class="profile-container">
              <div class="avatar-frame" @click.stop="showProfileMenu = !showProfileMenu">
                <img src="https://i.pravatar.cc/150?img=33" class="avatar-img" />
                <div class="status-indicator online"></div>
              </div>
              <div v-if="showProfileMenu" class="profile-dropdown">
                <button @click="logout" class="logout-btn">
                  <span>Terminate Session</span>
                  <span>🡪</span>
                </button>
              </div>
            </div>
          </div>
        </header>

        <section class="stats-grid">
          <div class="stat-card" v-for="(stat, key) in dynamicStats" :key="key" :class="stat.class">
            <div class="stat-content">
              <span class="stat-label">{{ stat.label }}</span>
              <div class="stat-value-group">
                <h2 class="stat-value">{{ stat.value }}</h2>
                <span class="stat-trend" :class="stat.trendUp ? 'up' : 'neutral'">
                  {{ stat.trendText }}
                </span>
              </div>
            </div>
            <div class="mini-sparkline">
              <div class="sparkline-bar" v-for="n in 8" :key="n" :style="{ height: Math.floor(Math.random() * 60) + 40 + '%' }"></div>
            </div>
          </div>
        </section>

        <div class="top-grid">
          
          <div class="dashboard-card main-panel">
            <div class="panel-header-interactive">
              <div>
                <h2>Assigned Lifecycle Queues</h2>
                <p class="sub-text">Personal runtime vectors mapped to team workflows</p>
              </div>
              
              <div class="filter-tabs-row">
                <button 
                  v-for="tab in ['All', 'In Progress', 'Todo', 'Completed']" 
                  :key="tab"
                  @click="activeStatusFilter = tab"
                  class="filter-tab"
                  :class="{ active: activeStatusFilter === tab }"
                >
                  {{ tab }}
                </button>
              </div>
            </div>

            <div class="task-vertical-list">
              <TransitionGroup name="task-fade">
                <div v-for="task in finalProcessedTasks" :key="task.id" class="task-strip-card" :class="{ completed: task.status === 'Completed' }">
                  <div class="task-interactive-checkbox" @click="toggleTaskLocal(task)">
                    <div class="checkbox-box" :class="task.status.toLowerCase().replace(' ', '-')">
                      <div class="checkbox-inner-dot" v-if="task.status === 'Completed'"></div>
                    </div>
                  </div>

                  <div class="task-strip-meta">
                    <div class="meta-upper-tags">
                      <span class="task-priority-badge" :class="task.priority.toLowerCase()">● {{ task.priority }}</span>
                      <span class="task-project-tag">{{ task.project_name }}</span>
                    </div>
                    <h3>{{ task.title }}</h3>
                  </div>

                  <div class="task-strip-action">
                    <span class="task-due-indicator" :class="{ overdue: isOverdue(task.deadline) }">
                      {{ task.deadline }}
                    </span>
                    <span class="status-pill-badge" :class="task.status.toLowerCase().replace(' ', '-')">
                      {{ task.status }}
                    </span>
                  </div>
                </div>
              </TransitionGroup>

              <div v-if="finalProcessedTasks.length === 0" class="empty-state-message">
                Void Sequence. Array matches zero criteria fields.
              </div>
            </div>
          </div>

          <div class="dashboard-card side-panel">
            <div class="card-header">
              <h2>Throughput Vector</h2>
              <p class="sub-text">Computed delta velocity distributions</p>
            </div>
            
            <div class="progress-matrix-wrapper">
              <div class="matrix-bar-item" v-for="bar in matrixBars" :key="bar.label">
                <div class="matrix-label-row">
                  <span class="matrix-name">{{ bar.label }}</span>
                  <span class="matrix-pct" :class="bar.class">{{ bar.value }}%</span>
                </div>
                <div class="matrix-track">
                  <div class="matrix-fill" :class="bar.class" :style="{ width: bar.value + '%' }"></div>
                </div>
              </div>

              <div class="insight-micro-card">
                <div class="insight-icon">⚡</div>
                <div class="insight-text">
                  <h4>Telemetry System Diagnostics</h4>
                  <p>Efficiency matrix running at <strong>{{ performanceData?.productivity_score }}%</strong> threshold. Target output vectors map nominal execution load levels.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <section class="dashboard-card content-section">
          <div class="card-header">
            <h2>Ecosystem Clusters</h2>
            <p class="sub-text">Core high-level data models mapped to your workspace allocations</p>
          </div>
          <div class="project-grid">
            <div v-for="project in projects" :key="project.id" class="project-mini-card">
              <div class="project-card-meta">
                <h3>{{ project.name }}</h3>
                <span class="project-percentage">{{ Math.round((project.completed_tasks / (project.total_tasks || 1)) * 100) }}%</span>
              </div>
              
              <div class="mini-progress-track">
                <div class="mini-progress-fill" :style="{ width: Math.round((project.completed_tasks / (project.total_tasks || 1)) * 100) + '%' }"></div>
              </div>

              <div class="project-card-footer">
                <small class="due-date">Target Space: {{ project.deadline }}</small>
                <span class="task-count-tag">{{ project.completed_tasks }} / {{ project.total_tasks }} Nodes</span>
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
  projects: Array,
  tasks: Array,
  stats: Object,
  performanceData: Object,
  notifications: Array
});

const activeStatusFilter = ref("All");
const search = ref("");
const showProfileMenu = ref(false);
const localizedTasks = ref([...props.tasks]);

const workspaces = computed(() => page.props.workspaces || []);
const currentWorkspace = computed(() => workspaces.value[0] || null);

const dynamicStats = computed(() => ({
  projects: { label: "Cluster Contexts", value: props.stats?.total_projects || 0, trendText: "Nominal", trendUp: false, class: "blue-deck" },
  tasks: { label: "Allocated Registry", value: localizedTasks.value.length, trendText: "Stack Total", trendUp: true, class: "purple-deck" },
  completed: { label: "Terminated Units", value: localizedTasks.value.filter(t => t.status === 'Completed').length, trendText: "Δ Sync", trendUp: true, class: "green-deck" },
  pending: { label: "Unresolved Queues", value: localizedTasks.value.filter(t => t.status !== 'Completed').length, trendText: "Live Stack", trendUp: false, class: "amber-deck" },
  efficiency: { label: "Telemetry Index", value: `${props.performanceData?.productivity_score || 0}%`, trendText: "Optimal Level", trendUp: true, class: "cyan-deck" }
}));

const matrixBars = computed(() => {
  const total = localizedTasks.value.length || 1;
  const completed = localizedTasks.value.filter(t => t.status === 'Completed').length;
  const progress = localizedTasks.value.filter(t => t.status === 'In Progress').length;
  const todo = localizedTasks.value.filter(t => t.status === 'Todo').length;

  return [
    { label: "Resolved Pipelines", value: Math.round((completed / total) * 100), class: "completed" },
    { label: "Active Evaluation Contexts", value: Math.round((progress / total) * 100), class: "progress" },
    { label: "Staged Registry Buffers", value: Math.round((todo / total) * 100), class: "todo" }
  ];
});

const finalProcessedTasks = computed(() => {
  return localizedTasks.value.filter(task => {
    const matchesSearch = task.title?.toLowerCase().includes(search.value.toLowerCase()) ||
                          task.project_name?.toLowerCase().includes(search.value.toLowerCase());
    
    if (activeStatusFilter.value === "All") return matchesSearch;
    return matchesSearch && task.status === activeStatusFilter.value;
  });
});

const unreadNotifications = computed(() => (props.notifications || []).filter(n => !n.is_read));

const toggleTaskLocal = (task) => {
  const target = localizedTasks.value.find(t => t.id === task.id);
  if (target) {
    target.status = target.status === "Completed" ? "In Progress" : "Completed";
  }
};

const isOverdue = (dateStr) => {
  if (!dateStr) return false;
  return dateStr.toLowerCase().includes('june 19');
};

const logout = () => {
    router.post("/logout", {}, {
        replace: true,
        onSuccess: () => {
            window.location.href = "/login";
        }
    });
};
const markAsRead = (id) => router.put(`/notifications/${id}/read`, {}, { preserveScroll: true });
const markAllRead = () => router.put('/notifications/read-all', {}, { preserveScroll: true });

const handleClickOutside = (event) => {
  const bell = document.querySelector('.notification-bell-container');
  const profile = document.querySelector('.profile-container');
  if (bell && !bell.contains(event.target)) notificationStore.showBellDropdown = false;
  if (profile && !profile.contains(event.target)) showProfileMenu.value = false;
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() => document.removeEventListener("click", handleClickOutside));
</script>

<style scoped>
.theme-dark {
  --dashboard-bg: #030712;
  --panel-bg: rgba(17, 24, 39, 0.4);
  --card-inner-bg: rgba(31, 41, 55, 0.3);
  --card-inner-hover: rgba(55, 65, 81, 0.5);
  --input-element-bg: #0b0f19;
  --dropdown-panel-bg: #0b0f19;
  --border-subtle: rgba(255, 255, 255, 0.04);
  --border-deep: rgba(255, 255, 255, 0.08);
  --text-main: #9ca3af;
  --text-header: #f9fafb;
  --text-muted: #4b5563;
  --accent-neon: #6366f1;
  --shadow-lux: 0 40px 80px -15px rgba(0, 0, 0, 0.7);
  
  --stat-blue: rgba(59, 130, 246, 0.15);
  --stat-blue-text: #60a5fa;
  --stat-purple: rgba(139, 92, 246, 0.15);
  --stat-purple-text: #a78bfa;
  --stat-green: rgba(16, 185, 129, 0.15);
  --stat-green-text: #34d399;
  --stat-amber: rgba(245, 158, 11, 0.15);
  --stat-amber-text: #fbbf24;
  --stat-cyan: rgba(6, 182, 212, 0.15);
  --stat-cyan-text: #22d3ee;
}

.theme-light {
  --dashboard-bg: #f9fafb;
  --panel-bg: rgba(255, 255, 255, 0.75);
  --card-inner-bg: rgba(243, 244, 246, 0.8);
  --card-inner-hover: #e5e7eb;
  --input-element-bg: #ffffff;
  --dropdown-panel-bg: #ffffff;
  --border-subtle: rgba(0, 0, 0, 0.04);
  --border-deep: rgba(0, 0, 0, 0.08);
  --text-main: #374151;
  --text-header: #111827;
  --text-muted: #9ca3af;
  --accent-neon: #4f46e5;
  --shadow-lux: 0 30px 60px -20px rgba(0, 0, 0, 0.03);
  
  --stat-blue: rgba(219, 234, 254, 0.7);
  --stat-blue-text: #1d4ed8;
  --stat-purple: rgba(237, 233, 254, 0.7);
  --stat-purple-text: #6d28d9;
  --stat-green: rgba(209, 250, 229, 0.7);
  --stat-green-text: #047857;
  --stat-amber: rgba(254, 243, 199, 0.7);
  --stat-amber-text: #b45309;
  --stat-cyan: rgba(207, 250, 254, 0.7);
  --stat-cyan-text: #0369a1;
}

.dashboard {
  display: flex;
  height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Mona Sans', 'Inter', sans-serif;
  background-color: var(--dashboard-bg);
  color: var(--text-main);
  overflow: hidden;
  letter-spacing: -0.01em;
}

.main-content {
  flex: 1;
  padding: 48px;
  overflow-y: auto;
}

.content-wrapper {
  max-width: 1440px;
  margin: 0 auto;
  width: 100%;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 48px;
}

.live-pulse-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 3px 8px;
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-header);
  border-radius: 6px;
  margin-bottom: 12px;
}

.pulse-dot {
  width: 4px;
  height: 4px;
  background: #10b981;
  border-radius: 50%;
  box-shadow: 0 0 8px #10b981;
}

.header h1 {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.03em;
  margin: 0;
  color: var(--text-header);
}

.header p {
  color: var(--text-muted);
  font-size: 13px;
  margin: 2px 0 0 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.workspace-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-header);
}

.chip-indicator {
  width: 6px;
  height: 6px;
  background: #10b981;
  border-radius: 50%;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
}

.search-box {
  width: 220px;
  padding: 10px 14px 10px 36px;
  border-radius: 12px;
  border: 1px solid var(--border-subtle);
  background: var(--input-element-bg);
  color: var(--text-header);
  font-size: 12px;
  outline: none;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.search-box:focus {
  width: 280px;
  border-color: var(--text-muted);
}

.action-control-btn {
  background: var(--input-element-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-header);
  width: 38px;
  height: 38px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  position: relative;
  transition: background-color 0.2s;
}

.action-control-btn:hover {
  background: var(--card-inner-hover);
}

.notification-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 6px;
  height: 6px;
  background: #ef4444;
  border-radius: 50%;
  text-indent: -9999px;
  box-shadow: 0 0 6px #ef4444;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
  margin-bottom: 48px;
}

.stat-card {
  position: relative;
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 16px;
  padding: 20px;
  box-shadow: var(--shadow-lux);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100px;
  transition: transform 0.2s, border-color 0.2s;
}

.stat-card:hover {
  border-color: var(--border-deep);
  transform: translateY(-2px);
}

.stat-label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

.stat-value-group {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  z-index: 2;
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.03em;
}

.blue-deck { background: linear-gradient(180deg, var(--panel-bg) 0%, var(--stat-blue) 100%); }
.blue-deck .stat-value { color: var(--stat-blue-text); }
.purple-deck { background: linear-gradient(180deg, var(--panel-bg) 0%, var(--stat-purple) 100%); }
.purple-deck .stat-value { color: var(--stat-purple-text); }
.green-deck { background: linear-gradient(180deg, var(--panel-bg) 0%, var(--stat-green) 100%); }
.green-deck .stat-value { color: var(--stat-green-text); }
.amber-deck { background: linear-gradient(180deg, var(--panel-bg) 0%, var(--stat-amber) 100%); }
.amber-deck .stat-value { color: var(--stat-amber-text); }
.cyan-deck { background: linear-gradient(180deg, var(--panel-bg) 0%, var(--stat-cyan) 100%); }
.cyan-deck .stat-value { color: var(--stat-cyan-text); }

.stat-trend {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted);
}
.stat-trend.up { color: #10b981; }

.mini-sparkline {
  position: absolute;
  bottom: 0;
  left: 20px;
  right: 20px;
  height: 24px;
  display: flex;
  align-items: flex-end;
  gap: 3px;
  opacity: 0.08;
  pointer-events: none;
}

.sparkline-bar {
  flex: 1;
  background: var(--text-header);
  border-top-left-radius: 2px;
  border-top-right-radius: 2px;
}

.top-grid {
  display: grid;
  grid-template-columns: 1.62fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.panel-header-interactive {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
}

.panel-header-interactive h2 {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
  color: var(--text-header);
}

.filter-tabs-row {
  display: flex;
  background: var(--input-element-bg);
  border: 1px solid var(--border-subtle);
  padding: 3px;
  border-radius: 10px;
}

.filter-tab {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 11px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.filter-tab.active {
  background: var(--card-inner-bg);
  color: var(--text-header);
}

.task-vertical-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.task-strip-card {
  display: flex;
  align-items: center;
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 12px;
  padding: 12px 18px;
  gap: 16px;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.task-strip-card:hover {
  background: var(--card-inner-hover);
  border-color: var(--border-deep);
}

.task-strip-card.completed {
  opacity: 0.35;
}

.task-interactive-checkbox {
  cursor: pointer;
  display: flex;
  align-items: center;
}

.checkbox-box {
  width: 14px;
  height: 14px;
  border-radius: 4px;
  border: 1px solid var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, border-color 0.2s;
}

.checkbox-box.completed {
  border-color: #10b981;
  background: #10b981;
}

.checkbox-inner-dot {
  width: 4px;
  height: 4px;
  background: #ffffff;
  border-radius: 50%;
}

.task-strip-meta {
  flex: 1;
}

.meta-upper-tags {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 2px;
}

.task-strip-meta h3 {
  font-size: 13px;
  font-weight: 600;
  margin: 0;
  color: var(--text-header);
  letter-spacing: -0.01em;
}

.task-priority-badge {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.task-priority-badge.high { color: #ef4444; }
.task-priority-badge.medium { color: #f59e0b; }
.task-priority-badge.low { color: #3b82f6; }

.task-project-tag {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-muted);
  background: var(--input-element-bg);
  padding: 1px 6px;
  border-radius: 4px;
  border: 1px solid var(--border-subtle);
}

.task-strip-action {
  display: flex;
  align-items: center;
  gap: 24px;
}

.task-due-indicator {
  font-size: 11px;
  color: var(--text-muted);
  font-variant-numeric: tabular-nums;
}
.task-due-indicator.overdue {
  color: #ef4444;
  font-weight: 600;
}

.status-pill-badge {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 2px 8px;
  border-radius: 6px;
}
.status-pill-badge.in-progress { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
.status-pill-badge.todo { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
.status-pill-badge.completed { color: #10b981; background: rgba(16, 185, 129, 0.1); }

.progress-matrix-wrapper {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.matrix-label-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
}

.matrix-name { font-size: 12px; font-weight: 600; color: var(--text-main); }
.matrix-pct { font-size: 11px; font-weight: 700; font-variant-numeric: tabular-nums; }
.matrix-pct.completed { color: #10b981; }
.matrix-pct.progress { color: #3b82f6; }
.matrix-pct.todo { color: #f59e0b; }

.matrix-track {
  height: 4px;
  background: var(--card-inner-bg);
  border-radius: 2px;
  overflow: hidden;
}

.matrix-fill {
  height: 100%;
  transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.matrix-fill.completed { background: #10b981; }
.matrix-fill.progress { background: #3b82f6; }
.matrix-fill.todo { background: #f59e0b; }

.insight-micro-card {
  margin-top: 24px;
  background: var(--input-element-bg);
  border: 1px solid var(--border-subtle);
  padding: 16px;
  border-radius: 12px;
  border-left: 3px solid var(--accent-neon);
}

.insight-icon { font-size: 14px; margin-bottom: 6px; color: var(--accent-neon); }
.insight-text h4 { margin: 0; font-size: 12px; font-weight: 700; color: var(--text-header); }
.insight-text p { margin: 4px 0 0 0; font-size: 11px; color: var(--text-main); line-height: 1.5; }

.project-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 14px;
}

.project-mini-card {
  background: var(--card-inner-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 14px;
  padding: 16px;
  transition: border-color 0.2s;
}

.project-mini-card:hover {
  border-color: var(--border-deep);
}

.project-card-meta {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.project-mini-card h3 {
  font-size: 13px;
  font-weight: 700;
  margin: 0;
  color: var(--text-header);
}

.project-percentage {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-header);
  font-variant-numeric: tabular-nums;
}

.mini-progress-track {
  height: 3px;
  background: var(--border-subtle);
  margin: 12px 0;
  border-radius: 2px;
  overflow: hidden;
}

.mini-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-neon) 0%, #10b981 100%);
}

.project-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.due-date, .task-count-tag {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-muted);
}

.avatar-frame {
  position: relative;
  cursor: pointer;
}

.avatar-img {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid var(--border-subtle);
}

.status-indicator {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  border: 2px solid var(--dashboard-bg);
}
.status-indicator.online { background: #10b981; }

.task-fade-enter-active,
.task-fade-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.task-fade-enter-from,
.task-fade-leave-to {
  opacity: 0;
  transform: translateX(-4px);
}

.dashboard-card {
  background: var(--panel-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 20px;
  padding: 24px;
  box-shadow: var(--shadow-lux);
  backdrop-filter: blur(20px);
}

.card-header h2 {
  font-size: 14px;
  font-weight: 700;
  margin: 0;
  color: var(--text-header);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.profile-dropdown, .notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 48px;
  background: var(--dropdown-panel-bg);
  border: 1px solid var(--border-deep);
  border-radius: 12px;
  box-shadow: var(--shadow-lux);
  z-index: 100;
}

.profile-dropdown { width: 160px; padding: 4px; }
.logout-btn {
  background: transparent;
  border: none;
  color: #ef4444;
  padding: 10px 14px;
  cursor: pointer;
  width: 100%;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  justify-content: space-between;
  border-radius: 8px;
}
.logout-btn:hover { background: rgba(239, 44, 44, 0.05); }

.notification-dropdown-panel { width: 280px; }
.notification-dropdown-header { display: flex; justify-content: space-between; padding: 14px; border-bottom: 1px solid var(--border-subtle); }
.notification-dropdown-header h3 { margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
.mark-all-btn { background: none; border: none; color: var(--text-header); cursor: pointer; font-size: 10px; font-weight: 700; }
.notification-dropdown-body { padding: 8px; max-height: 240px; overflow-y: auto; }
.notification-alert-item { display: flex; gap: 10px; padding: 10px; cursor: pointer; border-radius: 8px; }
.notification-alert-item:hover { background: var(--card-inner-bg); }
.alert-task-title { font-size: 12px; font-weight: 600; margin: 0; color: var(--text-header); }
.alert-task-time-left { font-size: 10px; color: var(--text-muted); margin: 2px 0 0 0; }
.notification-empty-state { text-align: center; color: var(--text-muted); padding: 24px; font-size: 11px; font-weight: 600; }
.empty-state-message { text-align: center; color: var(--text-muted); padding: 36px; font-size: 12px; font-weight: 500; border: 1px dashed var(--border-subtle); border-radius: 12px; }
</style>