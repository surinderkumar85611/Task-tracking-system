<template>
    <Head title="Projects" />

    <div class="sa-page" :class="isDark ? 'theme-dark' : 'theme-light'">

        <Sidebar />

        <main class="sa-main">

            <div class="sa-topbar">
                <div class="sa-topbar-title">
                    <h2>Projects</h2>
                    <p>Administrators, their workspaces, team leaders and the projects assigned to them</p>
                </div>

                <div class="sa-topbar-actions">
                    <input v-model="search" class="sa-input" placeholder="🔍  Search admin, workspace, leader, project..." />
                    <button class="sa-icon-btn" aria-label="Toggle theme" @click="isDark = !isDark">
                        {{ isDark ? '☀️' : '🌙' }}
                    </button>
                </div>
            </div>

            <div class="sa-content">

                <!-- totals -->
                <section class="sa-stats">
                    <div class="sa-stat violet">
                        <div class="sa-stat-icon">🛡️</div>
                        <span class="sa-stat-label">Administrators</span>
                        <h2 class="sa-stat-value">{{ stats.admins }}</h2>
                        <small class="sa-stat-sub">Each with their own workspaces</small>
                    </div>
                    <div class="sa-stat blue">
                        <div class="sa-stat-icon">🎯</div>
                        <span class="sa-stat-label">Team leaders</span>
                        <h2 class="sa-stat-value">{{ stats.leaders }}</h2>
                        <small class="sa-stat-sub">Running projects</small>
                    </div>
                    <div class="sa-stat green">
                        <div class="sa-stat-icon">📂</div>
                        <span class="sa-stat-label">Ongoing projects</span>
                        <h2 class="sa-stat-value">{{ stats.ongoing }}</h2>
                        <small class="sa-stat-sub">Not completed yet</small>
                    </div>
                    <div class="sa-stat red">
                        <div class="sa-stat-icon">🚨</div>
                        <span class="sa-stat-label">Teams needing projects</span>
                        <h2 class="sa-stat-value">{{ stats.needs_projects }}</h2>
                        <small class="sa-stat-sub">No ongoing project right now</small>
                    </div>
                </section>

                <!-- teams that need work -->
                <section class="sa-card urgent-card" :class="{ clear: !needsFiltered.length }">
                    <div class="sa-card-head">
                        <h3>🚨 Teams that need projects urgently</h3>
                        <span class="sa-pill" :class="needsFiltered.length ? 'red' : 'green'">
                            {{ needsFiltered.length ? needsFiltered.length + ' team(s) waiting' : 'All teams busy' }}
                        </span>
                    </div>

                    <div v-if="needsFiltered.length" class="needs-grid">
                        <div v-for="n in needsFiltered" :key="n.id" class="needs-card">
                            <div class="sa-avatar" :class="av(n.id)">{{ initials(n.name) }}</div>
                            <div class="needs-info">
                                <strong>{{ n.name }}</strong>
                                <span class="ws-tag small">🏢 {{ n.workspace_name }}</span>
                                <small>
                                    {{ n.admin_name ? 'Admin: ' + n.admin_name : 'No administrator' }}
                                    · {{ n.members_count }} members · {{ n.completed_count }} completed before
                                </small>
                            </div>
                            <span class="sa-pill red">No ongoing project</span>
                        </div>
                    </div>

                    <p v-else class="all-good">🎉 Every team has at least one ongoing project.</p>
                </section>

                <div class="sa-grid-2">
                    <section class="sa-card">
                        <div class="sa-card-head">
                            <h3>Projects assigned per month</h3>
                            <div class="legend">
                                <span><i class="dot completed"></i>Completed</span>
                                <span><i class="dot in_progress"></i>In progress</span>
                                <span><i class="dot pending"></i>Pending</span>
                            </div>
                        </div>

                        <div class="month-chart">
                            <div v-for="m in monthly" :key="m.key" class="month-col">
                                <span class="month-total">{{ m.total }}</span>
                                <div class="month-bar" :title="m.total + ' projects'">
                                    <div class="seg completed" :style="{ height: segHeight(m.completed) }"></div>
                                    <div class="seg in_progress" :style="{ height: segHeight(m.in_progress) }"></div>
                                    <div class="seg pending" :style="{ height: segHeight(m.pending) }"></div>
                                </div>
                                <span class="month-label">{{ m.label }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="sa-card donut-card">
                        <div class="sa-card-head">
                            <h3>Overall project status</h3>
                        </div>

                        <div class="donut-wrap">
                            <svg viewBox="0 0 140 140" class="donut-svg">
                                <circle cx="70" cy="70" r="54" fill="none" stroke="var(--border-deep)" stroke-width="14" />
                                <circle v-for="s in donutSegments" :key="s.key" cx="70" cy="70" r="54" fill="none"
                                    stroke-width="14" :stroke-dasharray="s.dash" :stroke-dashoffset="s.offset"
                                    :style="{ stroke: s.color }" transform="rotate(-90 70 70)" />
                            </svg>
                            <div class="donut-center">
                                <strong>{{ totals.rate }}%</strong>
                                <span>success</span>
                            </div>
                        </div>

                        <div class="donut-legend">
                            <div v-for="s in donutSegments" :key="s.key" class="donut-row">
                                <i class="dot" :style="{ background: s.color }"></i>
                                <span>{{ s.label }}</span>
                                <strong>{{ s.value }}</strong>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="sa-card section-gap">
                    <div class="sa-card-head">
                        <h3>Project distribution by team &amp; month</h3>
                        <small class="list-hint">Number of projects assigned to each team</small>
                    </div>

                    <div class="heat-wrap" v-if="heatRows.length">
                        <table class="heat">
                            <thead>
                                <tr>
                                    <th>Team leader</th>
                                    <th v-for="m in heatMonths" :key="m.key">{{ m.label }}</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in heatRows" :key="row.id">
                                    <td>
                                        <div class="heat-team">
                                            <div class="sa-avatar sm" :class="av(row.id)">{{ initials(row.name) }}</div>
                                            <div class="heat-name">
                                                <span>{{ row.name }}</span>
                                                <small>{{ row.workspace_name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td v-for="(c, i) in row.cells" :key="i">
                                        <span class="heat-cell" :style="cellStyle(c)">{{ c || '·' }}</span>
                                    </td>
                                    <td><strong>{{ row.total }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="sa-empty">No team leaders yet.</div>
                </section>

                <!-- administrators -->
                <div class="list-title">
                    <h3>Administrators</h3>
                    <span class="sa-pill violet">{{ shownGroups.length }}</span>
                    <small class="list-hint">Click an administrator to see their team leaders and projects</small>

                    <div class="list-actions">
                        <button class="sa-btn ghost sm" @click="expandAll">Expand all</button>
                        <button class="sa-btn ghost sm" @click="collapseAll">Collapse all</button>
                    </div>
                </div>

                <div class="admin-list">
                    <article v-for="g in shownGroups" :key="g.id" class="admin-item" :class="{ open: isOpen(g.id) }">

                        <div class="admin-head" role="button" tabindex="0" @click="toggle(g.id)"
                            @keydown.enter="toggle(g.id)">
                            <div class="sa-avatar lg" :class="av(g.id)">{{ initials(g.name) }}</div>

                            <div class="admin-id">
                                <strong>{{ g.name }}</strong>
                                <span>{{ g.email }}</span>
                            </div>

                            <div class="admin-ws">
                                <span v-for="w in g.workspaces" :key="w.id" class="ws-tag">🏢 {{ w.name }}</span>
                                <span v-if="!g.workspaces.length" class="ws-tag muted">No workspace</span>
                                <span v-if="!g.isUnlinked" class="sa-pill violet role-pill">🛡️ Administrator</span>
                            </div>

                            <div class="admin-metrics">
                                <div><strong>{{ g.leaders_count }}</strong><span>Leaders</span></div>
                                <div><strong>{{ g.ongoing_count }}</strong><span>Ongoing</span></div>
                                <div :class="{ warn: g.needs_projects_count }">
                                    <strong>{{ g.needs_projects_count }}</strong><span>Need work</span>
                                </div>
                            </div>

                            <span class="chev" :class="{ open: isOpen(g.id) }">▾</span>
                        </div>

                        <div v-if="isOpen(g.id)" class="admin-body">

                            <div v-if="!g.leaders.length" class="sa-empty">
                                No team leaders found in this workspace yet.
                            </div>

                            <div v-for="l in g.leaders" :key="l.id" class="leader-card">
                                <header class="leader-head">
                                    <div class="sa-avatar" :class="av(l.id)">{{ initials(l.name) }}</div>

                                    <div class="leader-id">
                                        <strong>{{ l.name }}</strong>
                                        <div class="tag-row">
                                            <span class="ws-tag small">🏢 {{ l.workspace_name }}</span>
                                            <span class="sa-pill amber role-pill">🎯 Team Leader · TL{{ l.level ?? 1 }}</span>
                                        </div>
                                    </div>

                                    <div class="leader-pills">
                                        <span class="sa-pill cyan">{{ l.members_count }} members</span>
                                        <span class="sa-pill blue">{{ l.ongoing_count }} ongoing</span>
                                        <span class="sa-pill green">{{ l.completed_count }} completed</span>
                                        <span v-if="l.overdue_count" class="sa-pill red">{{ l.overdue_count }} overdue</span>
                                        <span v-if="l.needs_projects" class="sa-pill amber">Needs projects</span>
                                    </div>
                                </header>

                                <div v-if="l.projects.length" class="proj-table">
                                    <div class="proj-row proj-head">
                                        <span>Project</span>
                                        <span>Assigned on</span>
                                        <span>Deadline</span>
                                        <span>Progress</span>
                                    </div>

                                    <div v-for="p in l.projects" :key="p.id" class="proj-row">
                                        <div class="proj-name">
                                            <i class="dot" :class="statusKey(p)"></i>
                                            <strong>{{ p.name }}</strong>
                                        </div>

                                        <span class="proj-date">{{ fmtDate(p.assigned_at) }}</span>

                                        <div class="proj-deadline">
                                            <span class="proj-date">{{ fmtDate(p.deadline) }}</span>
                                            <span class="sa-pill" :class="deadlineTone(p)">{{ deadlineNote(p) }}</span>
                                        </div>

                                        <div class="proj-progress">
                                            <div class="sa-progress" :class="{ late: isOverdue(p) }">
                                                <div :style="{ width: p.progress + '%' }"></div>
                                            </div>
                                            <span>{{ p.progress }}%</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="sa-empty">No projects have been assigned to this team yet.</div>
                            </div>
                        </div>
                    </article>

                    <div v-if="!shownGroups.length" class="sa-empty">
                        No administrators, workspaces, leaders or projects match your search.
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import Sidebar from "./Sidebar.vue";
import { useSuperAdminTheme } from "@/composables/useSuperAdminTheme";
const props = defineProps({
    admins: { type: Array, default: () => [] },
    unlinked: { type: Array, default: () => [] },
    needsProjects: { type: Array, default: () => [] },
    stats: {
        type: Object,
        default: () => ({ admins: 0, leaders: 0, ongoing: 0, needs_projects: 0 }),
    },
});

const { isDark } = useSuperAdminTheme();

const search = ref("");

const initials = (name) =>
    (name || "?").split(" ").filter(Boolean).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
const av = (id) => (typeof id === "number" ? "av-" + (id % 6) : "av-5");

const pad = (n) => String(n).padStart(2, "0");
const now = new Date();
const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
const todayMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate());

const fmtDate = (d) =>
    d
        ? new Date(d + "T00:00:00").toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" })
        : "—";

const statusKey = (p) => (p.progress >= 100 ? "completed" : p.progress > 0 ? "in_progress" : "pending");
const isOverdue = (p) => p.progress < 100 && !!p.deadline && p.deadline < todayStr;

const daysLeft = (p) =>
    p.deadline ? Math.round((new Date(p.deadline + "T00:00:00") - todayMidnight) / 86400000) : null;

const deadlineNote = (p) => {
    if (!p.deadline) return "No deadline";
    if (p.progress >= 100) return "Finished";
    const d = daysLeft(p);
    if (d < 0) return `${Math.abs(d)}d overdue`;
    if (d === 0) return "Due today";
    if (d === 1) return "Tomorrow";
    return `in ${d}d`;
};

const deadlineTone = (p) => {
    if (p.progress >= 100) return "green";
    if (isOverdue(p)) return "red";
    if (p.deadline && daysLeft(p) <= 3) return "amber";
    return "blue";
};

const q = computed(() => search.value.trim().toLowerCase());
const hit = (text) => !q.value || (text || "").toLowerCase().includes(q.value);

const needsFiltered = computed(() =>
    props.needsProjects.filter((n) => hit(n.name) || hit(n.workspace_name) || hit(n.admin_name))
);

/* administrators + one extra group for leaders that have no administrator */
const groups = computed(() => {
    const base = props.admins.map((a) => ({ ...a }));

    if (props.unlinked.length) {
        base.push({
            id: "unlinked",
            isUnlinked: true,
            name: "No administrator",
            email: "Leaders whose workspace has no owner",
            workspaces: [],
            leaders: props.unlinked,
            leaders_count: props.unlinked.length,
            ongoing_count: props.unlinked.reduce((s, l) => s + l.ongoing_count, 0),
            needs_projects_count: props.unlinked.filter((l) => l.needs_projects).length,
        });
    }

    return base;
});

const groupMatches = (g) => hit(g.name) || hit(g.email) || g.workspaces.some((w) => hit(w.name));

const shownGroups = computed(() =>
    groups.value
        .map((g) => {
            if (!q.value || groupMatches(g)) return g;
            return {
                ...g,
                leaders: g.leaders.filter(
                    (l) => hit(l.name) || hit(l.workspace_name) || l.projects.some((p) => hit(p.name))
                ),
            };
        })
        .filter((g) => !q.value || groupMatches(g) || g.leaders.length)
);

const allLeaders = computed(() => [
    ...props.admins.flatMap((a) => a.leaders),
    ...props.unlinked,
]);

const allProjects = computed(() => allLeaders.value.flatMap((l) => l.projects));

const totals = computed(() => {
    const t = { total: allProjects.value.length, completed: 0, in_progress: 0, pending: 0 };
    allProjects.value.forEach((p) => { t[statusKey(p)]++; });
    return { ...t, rate: t.total ? Math.round((t.completed / t.total) * 100) : 0 };
});

const monthBuckets = (n) => {
    const out = [];
    for (let i = n - 1; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        out.push({
            key: `${d.getFullYear()}-${d.getMonth()}`,
            label: d.toLocaleString("default", { month: "short" }),
        });
    }
    return out;
};

const monthKey = (dateStr) => {
    if (!dateStr) return null;
    const d = new Date(dateStr + "T00:00:00");
    return isNaN(d.getTime()) ? null : `${d.getFullYear()}-${d.getMonth()}`;
};

const monthly = computed(() => {
    const buckets = monthBuckets(8).map((b) => ({ ...b, completed: 0, in_progress: 0, pending: 0, total: 0 }));
    allProjects.value.forEach((p) => {
        const b = buckets.find((x) => x.key === monthKey(p.assigned_at));
        if (!b) return;
        b[statusKey(p)]++;
        b.total++;
    });
    return buckets;
});

const maxMonthly = computed(() => Math.max(1, ...monthly.value.map((m) => m.total)));
const segHeight = (count) => (count / maxMonthly.value) * 100 + "%";

const donutSegments = computed(() => {
    const C = 2 * Math.PI * 54;
    const total = totals.value.total || 1;
    const defs = [
        { key: "completed", label: "Completed", value: totals.value.completed, color: "var(--c-green)" },
        { key: "in_progress", label: "In progress", value: totals.value.in_progress, color: "var(--c-blue)" },
        { key: "pending", label: "Pending", value: totals.value.pending, color: "var(--c-amber)" },
    ];
    let used = 0;
    return defs.map((d) => {
        const len = (d.value / total) * C;
        const seg = { ...d, dash: `${len} ${C - len}`, offset: -used };
        used += len;
        return seg;
    });
});

const heatMonths = computed(() => monthBuckets(6));

const heatRows = computed(() =>
    [...allLeaders.value]
        .sort((a, b) => a.name.localeCompare(b.name))
        .map((l) => ({
            id: l.id,
            name: l.name,
            workspace_name: l.workspace_name,
            cells: heatMonths.value.map(
                (m) => l.projects.filter((p) => monthKey(p.assigned_at) === m.key).length
            ),
            total: l.projects.length,
        }))
);

const heatMax = computed(() => Math.max(1, ...heatRows.value.flatMap((r) => r.cells)));

const cellStyle = (c) => ({
    background: c
        ? `color-mix(in srgb, var(--accent) ${Math.round(18 + (c / heatMax.value) * 62)}%, transparent)`
        : "transparent",
    color: c ? "var(--text-header)" : "var(--text-muted)",
});

const expanded = ref([]);

// while searching, every matching administrator is shown open
const isOpen = (id) => !!q.value || expanded.value.includes(id);

const toggle = (id) => {
    expanded.value = expanded.value.includes(id)
        ? expanded.value.filter((x) => x !== id)
        : [...expanded.value, id];
};

const expandAll = () => { expanded.value = groups.value.map((g) => g.id); };
const collapseAll = () => { expanded.value = []; };
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

.sa-page.theme-dark {
    --dashboard-bg: #10121c;
    --panel-bg: #171a26;
    --card-inner-bg: #1d2130;
    --card-inner-hover: #262b3d;
    --input-element-bg: #212536;
    --border-subtle: rgba(148, 163, 210, 0.09);
    --border-deep: rgba(148, 163, 210, 0.16);
    --border-divider: rgba(148, 163, 210, 0.08);
    --text-main: #d9dbe7;
    --text-header: #f6f7fb;
    --text-muted: #7d83a0;
    --text-card-sub: #b1b5cc;
    --shadow-cards: rgba(3, 4, 10, 0.45);
    --shadow-hover: rgba(3, 4, 10, 0.55);
    --accent: #1fd1ab;
    --accent-soft: rgba(31, 209, 171, 0.16);
    --on-accent: #06110e;
}

.sa-page.theme-light {
    --dashboard-bg: #eef1f7;
    --panel-bg: #ffffff;
    --card-inner-bg: #f5f7fb;
    --card-inner-hover: #eaedf5;
    --input-element-bg: #f0f2f8;
    --border-subtle: rgba(30, 35, 70, 0.08);
    --border-deep: rgba(30, 35, 70, 0.14);
    --border-divider: rgba(30, 35, 70, 0.07);
    --text-main: #2d3142;
    --text-header: #12141f;
    --text-muted: #767c93;
    --text-card-sub: #454a5f;
    --shadow-cards: rgba(24, 28, 55, 0.06);
    --shadow-hover: rgba(24, 28, 55, 0.12);
    --accent: #0b8a75;
    --accent-soft: rgba(11, 138, 117, 0.1);
    --on-accent: #ffffff;
}

.sa-page {
    --c-blue: #3e63dd;
    --c-violet: #7c5cd6;
    --c-green: #0e9a7f;
    --c-amber: #c2792c;
    --c-cyan: #3184c2;
    --c-red: #d6484f;
    --c-gold: #d9a441;

    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    display: flex;
    height: 100vh;
    overflow: hidden;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: var(--dashboard-bg);
    color: var(--text-main);
    -webkit-font-smoothing: antialiased;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.sa-page *,
.sa-page *::before,
.sa-page *::after {
    box-sizing: border-box;
}

.sa-page h1,
.sa-page h2,
.sa-page h3,
.sa-page h4,
.sa-page p {
    margin: 0;
}

.sa-page h2,
.sa-page h3,
.sa-page h4 {
    font-family: 'Lexend', 'Inter', sans-serif;
}

.sa-main {
    flex: 1;
    min-width: 0;
    overflow-y: auto;
}

.sa-topbar {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 30px;
    background: var(--panel-bg);
    border-bottom: 1px solid var(--border-subtle);
}

.sa-topbar-title h2 {
    font-size: 17px;
    font-weight: 600;
    letter-spacing: -0.2px;
    color: var(--text-header);
}

.sa-topbar-title p {
    margin-top: 4px;
    font-size: 12.5px;
    color: var(--text-muted);
}

.sa-topbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sa-content {
    max-width: 1640px;
    margin: 0 auto;
    padding: 28px 40px 60px;
}

.sa-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 1px 2px var(--shadow-cards);
}

.sa-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.sa-card-head h3 {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-header);
}

.sa-grid-2 {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}

@media (max-width: 1200px) {
    .sa-grid-2 {
        grid-template-columns: 1fr;
    }
}

.sa-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.sa-stat {
    --tone: var(--c-blue);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding: 18px 18px 20px;
    border-radius: 12px;
    border: 1px solid var(--border-subtle);
    background: linear-gradient(165deg, color-mix(in srgb, var(--tone) 9%, transparent), var(--panel-bg) 60%);
    box-shadow: 0 1px 2px var(--shadow-cards);
    transition: all 0.18s ease;
}

.sa-stat:hover {
    transform: translateY(-2px);
    border-color: var(--border-deep);
    box-shadow: 0 10px 22px -8px var(--shadow-hover);
}

.sa-stat::before {
    content: "";
    position: absolute;
    left: 0;
    top: 14px;
    bottom: 14px;
    width: 3px;
    border-radius: 0 3px 3px 0;
    background: var(--tone);
}

.sa-stat.blue { --tone: var(--c-blue); }
.sa-stat.violet { --tone: var(--c-violet); }
.sa-stat.green { --tone: var(--c-green); }
.sa-stat.amber { --tone: var(--c-amber); }
.sa-stat.cyan { --tone: var(--c-cyan); }
.sa-stat.red { --tone: var(--c-red); }

.sa-stat-icon {
    width: 34px;
    height: 34px;
    margin: 0 0 14px 5px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    background: color-mix(in srgb, var(--tone) 16%, transparent);
}

.sa-stat-label {
    margin-left: 5px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--text-muted);
}

.sa-stat-value {
    margin: 7px 0 0 5px !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 28px;
    font-weight: 600;
    letter-spacing: -0.5px;
    color: var(--text-header);
}

.sa-stat-sub {
    margin: 6px 0 0 5px;
    font-size: 11px;
    color: var(--text-muted);
}

.sa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 18px;
    border: none;
    border-radius: 9px;
    background: var(--accent);
    color: var(--on-accent);
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: opacity 0.15s ease;
}

.sa-btn:hover:not(:disabled) { opacity: 0.9; }
.sa-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.sa-btn.ghost { background: transparent; color: var(--text-main); border: 1px solid var(--border-deep); }
.sa-btn.ghost:hover:not(:disabled) { background: var(--card-inner-hover); }
.sa-btn.danger { background: var(--c-red); color: #fff; }
.sa-btn.success { background: var(--c-green); color: #fff; }

.sa-icon-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.sa-icon-btn:hover { background: var(--card-inner-hover); border-color: var(--border-deep); }
.sa-icon-btn.danger:hover { background: rgba(214, 72, 79, 0.14); border-color: rgba(214, 72, 79, 0.4); }
.sa-icon-btn.sm { width: 30px; height: 30px; font-size: 12px; }

.sa-input {
    width: 250px;
    padding: 9px 13px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 13px;
    outline: none;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s ease;
}

.sa-input::placeholder { color: var(--text-muted); }
.sa-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); background: var(--panel-bg); }

.sa-pill {
    --tone: var(--text-muted);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 600;
    white-space: nowrap;
    color: var(--tone);
    background: color-mix(in srgb, var(--tone) 14%, transparent);
}

.sa-pill.green { --tone: var(--c-green); }
.sa-pill.blue { --tone: var(--c-blue); }
.sa-pill.amber { --tone: var(--c-amber); }
.sa-pill.red { --tone: var(--c-red); }
.sa-pill.violet { --tone: var(--c-violet); }
.sa-pill.cyan { --tone: var(--c-cyan); }

.sa-avatar {
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    font-family: 'Lexend', sans-serif;
    font-weight: 600;
    font-size: 12px;
    color: #fff;
    background: var(--c-cyan);
    user-select: none;
}

.sa-avatar.sm { width: 28px; height: 28px; font-size: 10.5px; border-radius: 7px; }
.sa-avatar.lg { width: 44px; height: 44px; font-size: 15px; border-radius: 11px; }
.sa-avatar.av-0 { background: var(--c-blue); }
.sa-avatar.av-1 { background: var(--c-violet); }
.sa-avatar.av-2 { background: var(--c-green); }
.sa-avatar.av-3 { background: var(--c-amber); }
.sa-avatar.av-4 { background: var(--c-cyan); }
.sa-avatar.av-5 { background: var(--c-red); }

.sa-tabs {
    display: inline-flex;
    gap: 3px;
    padding: 4px;
    margin-bottom: 22px;
    border-radius: 11px;
    background: var(--input-element-bg);
    border: 1px solid var(--border-subtle);
}

.sa-tabs button {
    padding: 9px 18px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--text-muted);
    font-weight: 500;
    font-size: 13px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: all 0.15s ease;
}

.sa-tabs button.active { background: var(--accent); color: var(--on-accent); font-weight: 600; }

.sa-progress {
    height: 6px;
    border-radius: 999px;
    overflow: hidden;
    background: var(--border-deep);
}

.sa-progress > div {
    height: 100%;
    border-radius: 999px;
    background: var(--accent);
    transition: width 0.4s ease;
}

.sa-empty {
    padding: 26px 12px;
    text-align: center;
    font-size: 13px;
    color: var(--text-muted);
    border: 1px dashed var(--border-deep);
    border-radius: 10px;
}

.sa-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(6, 7, 12, 0.65);
    backdrop-filter: blur(6px);
}

.sa-modal {
    width: 420px;
    max-width: 95%;
    padding: 28px;
    text-align: center;
    border-radius: 14px;
    background: var(--panel-bg);
    border: 1px solid var(--border-deep);
    box-shadow: 0 30px 80px var(--shadow-cards);
}

.sa-modal-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 22px;
    background: rgba(214, 72, 79, 0.14);
}

.sa-modal h3 { font-size: 17px; color: var(--text-header); margin-bottom: 8px; }
.sa-modal p { font-size: 13px; line-height: 1.55; color: var(--text-muted); }
.sa-modal-actions { display: flex; justify-content: center; gap: 12px; margin-top: 22px; }


.sa-btn.sm {
    padding: 7px 13px;
    font-size: 12px;
}

.ws-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 11px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
    color: var(--accent);
    background: var(--accent-soft);
    border: 1px solid color-mix(in srgb, var(--accent) 35%, transparent);
}

.ws-tag.small {
    padding: 2px 9px;
    font-size: 10.5px;
}

.ws-tag.muted {
    color: var(--text-muted);
    background: var(--input-element-bg);
    border-color: var(--border-subtle);
    font-weight: 500;
}

.urgent-card {
    margin-bottom: 26px;
    border-color: rgba(214, 72, 79, 0.35);
    background: linear-gradient(180deg, rgba(214, 72, 79, 0.07), var(--panel-bg) 60%);
}

.urgent-card.clear {
    border-color: var(--border-subtle);
    background: var(--panel-bg);
}

.needs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 12px;
}

.needs-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-radius: 12px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.needs-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
}

.needs-info strong {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-header);
}

.needs-info small {
    font-size: 11.5px;
    color: var(--text-muted);
}

.all-good {
    padding: 8px 2px;
    font-size: 13.5px;
    color: var(--text-card-sub);
}

.list-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.list-title h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-header);
}

.list-hint {
    font-size: 11.5px;
    color: var(--text-muted);
}

.list-actions {
    margin-left: auto;
    display: flex;
    gap: 8px;
}

.admin-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.admin-item {
    border-radius: 14px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    box-shadow: 0 1px 2px var(--shadow-cards);
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.admin-item.open {
    border-color: var(--border-deep);
    box-shadow: 0 12px 28px -12px var(--shadow-hover);
}

.admin-head {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 18px;
    cursor: pointer;
    border-radius: 14px;
    transition: background 0.15s ease;
}

.admin-head:hover {
    background: var(--card-inner-bg);
}

.admin-id {
    min-width: 200px;
    max-width: 260px;
    display: flex;
    flex-direction: column;
}

.admin-id strong {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-header);
}

.admin-id span {
    font-size: 12px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-ws {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.admin-metrics {
    display: flex;
    gap: 10px;
}

.admin-metrics div {
    min-width: 66px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 10px;
    border-radius: 9px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.admin-metrics strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-header);
}

.admin-metrics span {
    margin-top: 2px;
    font-size: 10.5px;
    color: var(--text-muted);
}

.admin-metrics div.warn {
    background: rgba(214, 72, 79, 0.1);
    border-color: rgba(214, 72, 79, 0.35);
}

.admin-metrics div.warn strong {
    color: var(--c-red);
}

.chev {
    font-size: 18px;
    color: var(--text-muted);
    transition: transform 0.2s ease;
}

.chev.open {
    transform: rotate(180deg);
    color: var(--accent);
}

.admin-body {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 4px 18px 20px;
    border-top: 1px solid var(--border-divider);
    margin-top: 2px;
    padding-top: 18px;
}

.leader-card {
    padding: 16px;
    border-radius: 12px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.leader-head {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.leader-id {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
    min-width: 170px;
}

.leader-id strong {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-header);
}

.leader-pills {
    margin-left: auto;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.proj-table {
    display: flex;
    flex-direction: column;
    border-radius: 10px;
    overflow: hidden;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
}

.proj-row {
    display: grid;
    grid-template-columns: 1.7fr 1fr 1.5fr 1.3fr;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-divider);
}

.proj-row:last-child {
    border-bottom: none;
}

.proj-row:not(.proj-head):hover {
    background: var(--card-inner-hover);
}

.proj-head {
    padding: 10px 16px;
    background: var(--input-element-bg);
}

.proj-head span {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--text-muted);
}

.proj-name {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
}

.proj-name strong {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dot {
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--c-amber);
}

.dot.in_progress {
    background: var(--c-blue);
}

.dot.completed {
    background: var(--c-green);
}

.proj-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    color: var(--text-card-sub);
}

.proj-deadline {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.proj-progress {
    display: flex;
    align-items: center;
    gap: 10px;
}

.proj-progress .sa-progress {
    flex: 1;
}

.proj-progress span {
    width: 38px;
    text-align: right;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-header);
}

.sa-progress.late > div {
    background: var(--c-red);
}

.tag-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 7px;
}

.role-pill {
    padding: 4px 11px;
    font-size: 11px;
}

.section-gap {
    margin-bottom: 26px;
}

.sa-grid-2 {
    margin-bottom: 18px;
}

.legend {
    display: flex;
    gap: 14px;
    font-size: 11.5px;
    color: var(--text-muted);
}

.legend span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.month-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
    height: 230px;
    padding-top: 6px;
}

.month-col {
    flex: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.month-total {
    margin-bottom: 6px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-header);
}

.month-bar {
    flex: 1;
    width: 100%;
    max-width: 34px;
    display: flex;
    flex-direction: column-reverse;
    border-radius: 7px;
    overflow: hidden;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.seg {
    width: 100%;
    transition: height 0.4s ease;
}

.seg.completed { background: var(--c-green); }
.seg.in_progress { background: var(--c-blue); }
.seg.pending { background: var(--c-amber); }

.month-label {
    margin-top: 9px;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--text-muted);
}

.donut-card {
    display: flex;
    flex-direction: column;
}

.donut-wrap {
    position: relative;
    width: 170px;
    height: 170px;
    margin: 4px auto 18px;
}

.donut-svg {
    width: 100%;
    height: 100%;
}

.donut-svg circle {
    transition: stroke-dasharray 0.4s ease;
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
    font-family: 'IBM Plex Mono', monospace;
    font-size: 26px;
    font-weight: 600;
    color: var(--text-header);
}

.donut-center span {
    font-size: 11px;
    color: var(--text-muted);
}

.donut-legend {
    display: flex;
    flex-direction: column;
    gap: 9px;
    padding-top: 16px;
    border-top: 1px solid var(--border-divider);
}

.donut-row {
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 12.5px;
    color: var(--text-card-sub);
}

.donut-row span {
    flex: 1;
}

.donut-row strong {
    font-family: 'IBM Plex Mono', monospace;
    color: var(--text-header);
}

.heat-wrap {
    overflow-x: auto;
}

.heat {
    width: 100%;
    border-collapse: separate;
    border-spacing: 5px;
}

.heat th {
    padding: 4px 6px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    text-align: center;
    color: var(--text-muted);
}

.heat th:first-child,
.heat td:first-child {
    text-align: left;
}

.heat td {
    text-align: center;
    font-size: 13px;
    color: var(--text-main);
}

.heat-team {
    display: flex;
    align-items: center;
    gap: 9px;
}

.heat-name {
    display: flex;
    flex-direction: column;
    text-align: left;
}

.heat-name span {
    font-weight: 500;
}

.heat-name small {
    font-size: 10.5px;
    color: var(--text-muted);
}

.heat-cell {
    display: block;
    padding: 9px 0;
    border-radius: 8px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid var(--border-subtle);
}

@media (max-width: 1250px) {
    .admin-head {
        flex-wrap: wrap;
    }

    .admin-ws {
        flex-basis: 100%;
        order: 5;
    }
}

@media (max-width: 1000px) {
    .proj-row {
        grid-template-columns: 1fr 1fr;
    }

    .proj-head {
        display: none;
    }
}
</style>