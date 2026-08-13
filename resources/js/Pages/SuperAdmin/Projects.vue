<template>

  <Head title="Projects" />

  <div class="dashboard" :class="isDark ? 'theme-dark' : 'theme-light'">

    <SuperAdminSidebar />

    <main class="main-content">
      <div class="topbar">
        <div class="topbar-greeting">
          <h2>All Projects</h2>
          <p>Every project across the organisation, its status and owner.</p>
        </div>

        <div class="topbar-icons">
          <input v-model="search" type="text" placeholder="Search projects, leaders, workspaces..." class="search-box" />
          <select v-model="workspaceFilter" class="filter-select">
            <option value="">All Workspaces</option>
            <option v-for="w in workspaceNames" :key="w" :value="w">{{ w }}</option>
          </select>
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Statuses</option>
            <option value="todo">Pending</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
          <button class="theme-btn" @click="isDark = !isDark">{{ isDark ? '☀️' : '🌙' }}</button>
        </div>
      </div>

      <div class="content-wrapper">

        <section class="stats-grid">
          <div class="stat-card"><span class="stat-label">Total Projects</span><h2 class="stat-value">{{ allProjects.length }}</h2></div>
          <div class="stat-card"><span class="stat-label">Pending</span><h2 class="stat-value">{{ counts.todo }}</h2></div>
          <div class="stat-card"><span class="stat-label">In Progress</span><h2 class="stat-value">{{ counts['in-progress'] }}</h2></div>
          <div class="stat-card"><span class="stat-label">Completed</span><h2 class="stat-value">{{ counts.completed }}</h2></div>
        </section>

        <div class="charts-row">
          <!-- MONTHLY PROGRESS -->
          <section class="dashboard-card">
            <div class="card-header"><h2>Projects Assigned Per Month</h2></div>
            <div class="bar-chart">
              <div v-for="bucket in monthlyProjects" :key="bucket.label + bucket.year" class="bar-col">
                <span class="bar-value">{{ bucket.count }}</span>
                <div class="bar-track">
                  <div class="bar-fill" :class="{ zero: bucket.count === 0 }" :style="{ height: (bucket.count / maxMonthlyCount) * 100 + '%' }"></div>
                </div>
                <span class="bar-label">{{ bucket.label }}</span>
              </div>
            </div>
          </section>

          <!-- STATUS BREAKDOWN -->
          <section class="dashboard-card status-chart-card">
            <div class="card-header"><h2>Status Breakdown</h2></div>
            <div class="status-bars">
              <div v-for="row in statusRows" :key="row.key" class="status-bar-row">
                <div class="status-bar-label">
                  <span class="kanban-column-dot" :class="row.key"></span>{{ row.label }}
                </div>
                <div class="status-bar-track">
                  <div class="status-bar-fill" :class="row.key" :style="{ width: (allProjects.length ? (row.count / allProjects.length) * 100 : 0) + '%' }"></div>
                </div>
                <strong>{{ row.count }}</strong>
              </div>
            </div>
          </section>
        </div>

        <!-- PROJECT LIST -->
        <section class="dashboard-card">
          <div class="card-header"><h2>Projects</h2></div>

          <div class="projects-table" v-if="filteredProjects.length">
            <div class="projects-head">
              <span>Project</span>
              <span>Workspace</span>
              <span>Team Leader</span>
              <span>Due Date</span>
              <span>Status</span>
              <span>Progress</span>
            </div>
            <div v-for="project in filteredProjects" :key="project.id" class="projects-row">
              <div class="p-name">{{ project.name }}</div>
              <span class="p-muted">{{ project.workspace_name || '—' }}</span>
              <span class="p-muted">{{ project.team_leader_name || '—' }}</span>
              <span class="p-muted mono">{{ project.deadline || 'No deadline' }}</span>
              <span class="status-pill" :class="progressClass(project.progress)">{{ progressLabel(project.progress) }}</span>
              <div class="p-progress">
                <div class="mini-progress"><div class="mini-progress-fill" :style="{ width: (project.progress || 0) + '%' }"></div></div>
                <span>{{ project.progress || 0 }}%</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state-inline">No projects match your filters.</div>
        </section>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import SuperAdminSidebar from "./SuperAdminSidebar.vue";

const props = defineProps({
  allProjects: { type: Array, default: () => [] },
});

const isDark = ref(true);
const search = ref("");
const workspaceFilter = ref("");
const statusFilter = ref("");

const progressClass = (progress) => {
  if (progress >= 100) return "completed";
  if (progress > 0) return "in-progress";
  return "todo";
};
const progressLabel = (progress) => {
  if (progress >= 100) return "Completed";
  if (progress > 0) return "In Progress";
  return "Pending";
};

const workspaceNames = computed(() => {
  const names = new Set();
  props.allProjects.forEach(p => { if (p.workspace_name) names.add(p.workspace_name); });
  return Array.from(names).sort();
});

const filteredProjects = computed(() => {
  const term = search.value.toLowerCase();
  return props.allProjects.filter(p => {
    const matchesSearch = !term ||
      p.name?.toLowerCase().includes(term) ||
      p.team_leader_name?.toLowerCase().includes(term) ||
      p.workspace_name?.toLowerCase().includes(term);
    const matchesWorkspace = !workspaceFilter.value || p.workspace_name === workspaceFilter.value;
    const matchesStatus = !statusFilter.value || progressClass(p.progress) === statusFilter.value;
    return matchesSearch && matchesWorkspace && matchesStatus;
  });
});

const counts = computed(() => {
  const c = { todo: 0, "in-progress": 0, completed: 0 };
  props.allProjects.forEach(p => { c[progressClass(p.progress)]++; });
  return c;
});

const statusRows = computed(() => [
  { key: "todo", label: "Pending", count: counts.value.todo },
  { key: "in-progress", label: "In Progress", count: counts.value["in-progress"] },
  { key: "completed", label: "Completed", count: counts.value.completed },
]);

const monthlyProjects = computed(() => {
  const now = new Date();
  const buckets = [];
  for (let i = 8; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    buckets.push({
      year: d.getFullYear(),
      month: d.getMonth(),
      label: d.toLocaleString("default", { month: "short" }) + (d.getFullYear() !== now.getFullYear() ? ` '${String(d.getFullYear()).slice(-2)}` : ""),
      count: 0,
    });
  }
  props.allProjects.forEach(p => {
    const dateSource = p.created_at || p.start_date;
    if (!dateSource) return;
    const d = new Date(dateSource);
    const bucket = buckets.find(b => b.year === d.getFullYear() && b.month === d.getMonth());
    if (bucket) bucket.count++;
  });
  return buckets;
});

const maxMonthlyCount = computed(() => Math.max(1, ...monthlyProjects.value.map(b => b.count)));
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

.theme-dark {
  --dashboard-bg: #10121c; --panel-bg: #171a26; --card-inner-bg: #1d2130; --input-element-bg: #212536;
  --border-subtle: rgba(148,163,210,0.09); --border-deep: rgba(148,163,210,0.16); --border-divider: rgba(148,163,210,0.08);
  --text-main: #d9dbe7; --text-header: #f6f7fb; --text-muted: #7d83a0; --accent: #1fd1ab; --accent-soft: rgba(31,209,171,0.16);
  --c-blue: #3e63dd; --c-amber: #c2792c; --c-green: #0e9a7f;
}
.theme-light {
  --dashboard-bg: #eef1f7; --panel-bg: #ffffff; --card-inner-bg: #f5f7fb; --input-element-bg: #f0f2f8;
  --border-subtle: rgba(30,35,70,0.08); --border-deep: rgba(30,35,70,0.14); --border-divider: rgba(30,35,70,0.07);
  --text-main: #2d3142; --text-header: #12141f; --text-muted: #767c93; --accent: #0b8a75; --accent-soft: rgba(11,138,117,0.1);
  --c-blue: #3e63dd; --c-amber: #c2792c; --c-green: #0e9a7f;
}

* { box-sizing: border-box; }
.dashboard { display: flex; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: var(--dashboard-bg); color: var(--text-main); }
.main-content { flex: 1; }
.content-wrapper { max-width: 1500px; margin: 0 auto; padding: 28px 40px 60px; }
.dashboard h2, .dashboard h3 { font-family: 'Lexend', 'Inter', sans-serif; }

.topbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 30px; background: var(--panel-bg); border-bottom: 1px solid var(--border-subtle); position: sticky; top: 0; z-index: 20; flex-wrap: wrap; gap: 12px; }
.topbar-greeting h2 { font-size: 16px; margin: 0; color: var(--text-header); }
.topbar-greeting p { margin-top: 4px; color: var(--text-muted); font-size: 12.5px; }
.topbar-icons { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.search-box, .filter-select { padding: 9px 13px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); color: var(--text-main); font-size: 13px; }
.search-box { width: 220px; }
.theme-btn { width: 36px; height: 36px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); cursor: pointer; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
@media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
.stat-card { background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 18px; }
.stat-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }
.stat-value { font-family: 'IBM Plex Mono', monospace; font-size: 24px; margin-top: 6px; color: var(--text-header); }

.charts-row { display: grid; grid-template-columns: 1.6fr 1fr; gap: 18px; margin-bottom: 18px; }
@media (max-width: 1000px) { .charts-row { grid-template-columns: 1fr; } }

.dashboard-card { background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 22px; }
.card-header h2 { font-size: 14.5px; color: var(--text-header); margin: 0 0 18px; }

.bar-chart { display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; height: 176px; padding-top: 10px; background-image: repeating-linear-gradient(to top, var(--border-divider) 0, var(--border-divider) 1px, transparent 1px, transparent 25%); }
.bar-col { display: flex; flex-direction: column; align-items: center; flex: 1; height: 100%; }
.bar-value { font-family: 'IBM Plex Mono', monospace; font-size: 12px; font-weight: 600; color: var(--text-header); margin-bottom: 6px; }
.bar-track { flex: 1; width: 22px; background: var(--card-inner-bg); border-radius: 5px; display: flex; align-items: flex-end; overflow: hidden; border: 1px solid var(--border-subtle); }
.bar-fill { width: 100%; background: var(--accent); border-radius: 5px; min-height: 3px; opacity: .85; }
.bar-fill.zero { background: var(--text-muted); opacity: .2; }
.bar-label { margin-top: 9px; font-size: 10px; color: var(--text-muted); font-weight: 600; white-space: nowrap; }

.status-chart-card { display: flex; flex-direction: column; }
.status-bars { display: flex; flex-direction: column; gap: 18px; justify-content: center; flex: 1; }
.status-bar-row { display: grid; grid-template-columns: 110px 1fr 30px; align-items: center; gap: 10px; }
.status-bar-label { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--text-main); }
.status-bar-track { height: 10px; border-radius: 999px; background: var(--card-inner-bg); overflow: hidden; }
.status-bar-fill { height: 100%; border-radius: 999px; transition: width .3s ease; }
.status-bar-fill.todo { background: var(--c-amber); }
.status-bar-fill.in-progress { background: var(--c-blue); }
.status-bar-fill.completed { background: var(--c-green); }
.status-bar-row strong { font-family: 'IBM Plex Mono', monospace; font-size: 12.5px; color: var(--text-header); text-align: right; }
.kanban-column-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.kanban-column-dot.todo { background: var(--c-amber); }
.kanban-column-dot.in-progress { background: var(--c-blue); }
.kanban-column-dot.completed { background: var(--c-green); }

.projects-table { display: flex; flex-direction: column; }
.projects-head { display: grid; grid-template-columns: 1.8fr 1fr 1.2fr 1fr .9fr 1.1fr; padding: 0 6px 12px; border-bottom: 1px solid var(--border-divider); }
.projects-head span { font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }
.projects-row { display: grid; grid-template-columns: 1.8fr 1fr 1.2fr 1fr .9fr 1.1fr; align-items: center; gap: 8px; padding: 13px 6px; border-bottom: 1px solid var(--border-divider); }
.projects-row:hover { background: var(--card-inner-bg); }
.p-name { font-size: 13.5px; font-weight: 500; color: var(--text-main); }
.p-muted { font-size: 12.5px; color: var(--text-muted); }
.mono { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; }
.status-pill { font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 5px; width: fit-content; }
.status-pill.todo { background: rgba(194,121,44,.14); color: var(--c-amber); }
.status-pill.in-progress { background: rgba(62,99,221,.12); color: var(--c-blue); }
.status-pill.completed { background: rgba(14,154,127,.12); color: var(--c-green); }
.p-progress { display: flex; align-items: center; gap: 8px; }
.mini-progress { flex: 1; height: 5px; background: var(--border-deep); border-radius: 6px; overflow: hidden; }
.mini-progress-fill { height: 100%; background: var(--accent); border-radius: 6px; }
.p-progress span { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--accent); }
.empty-state-inline { text-align: center; padding: 28px 0; color: var(--text-muted); font-size: 13px; }
</style>