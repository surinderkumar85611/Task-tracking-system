<template>
    <Head title="Teams" />

    <div class="sa-page" :class="isDark ? 'theme-dark' : 'theme-light'">

        <Sidebar />

        <main class="sa-main">

            <div class="sa-topbar">
                <div class="sa-topbar-title">
                    <h2>Teams</h2>
                    <p>Everyone in the organisation, from administrators to team members</p>
                </div>

                <div class="sa-topbar-actions">
                    <input v-model="search" class="sa-input" placeholder="🔍  Search people..." />
                    <button class="sa-icon-btn" aria-label="Toggle theme" @click="isDark = !isDark">
                        {{ isDark ? '☀️' : '🌙' }}
                    </button>
                </div>
            </div>

            <div class="sa-content">

        <!-- organisation totals -->
        <section class="sa-stats">
            <div class="sa-stat violet">
                <div class="sa-stat-icon">🛡️</div>
                <span class="sa-stat-label">Administrators</span>
                <h2 class="sa-stat-value">{{ orgStats.admins }}</h2>
                <small class="sa-stat-sub">Workspace owners</small>
            </div>
            <div class="sa-stat blue">
                <div class="sa-stat-icon">🎯</div>
                <span class="sa-stat-label">Team Leaders</span>
                <h2 class="sa-stat-value">{{ orgStats.leaders }}</h2>
                <small class="sa-stat-sub">Leading a team</small>
            </div>
            <div class="sa-stat green">
                <div class="sa-stat-icon">👥</div>
                <span class="sa-stat-label">Team Members</span>
                <h2 class="sa-stat-value">{{ orgStats.members }}</h2>
                <small class="sa-stat-sub">Across the organisation</small>
            </div>
            <div class="sa-stat cyan">
                <div class="sa-stat-icon">🌐</div>
                <span class="sa-stat-label">Total People</span>
                <h2 class="sa-stat-value">{{ orgStats.people }}</h2>
                <small class="sa-stat-sub">Admins + leaders + members</small>
            </div>
        </section>

        <div class="sa-tabs">
            <button :class="{ active: tab === 'organisation' }" @click="tab = 'organisation'">🏛️ Organisation</button>
            <button :class="{ active: tab === 'analytics' }" @click="tab = 'analytics'">📊 Team analytics</button>
        </div>

        <!-- ===================== ORGANISATION ===================== -->
        <template v-if="tab === 'organisation'">

            <!-- 1. administrators -->
            <section class="org-section">
                <div class="org-title">
                    <span class="org-step">1</span>
                    <h3>Administrators</h3>
                    <span class="sa-pill violet">{{ filteredAdmins.length }}</span>
                </div>

                <div class="admin-grid">
                    <article v-for="a in filteredAdmins" :key="a.id" class="person-card">
                        <div class="sa-avatar lg" :class="av(a.id)">{{ initials(a.name) }}</div>
                        <div class="person-info">
                            <strong>{{ a.name }}</strong>
                            <span>{{ a.email }}</span>
                            <small>Joined {{ fmtDate(a.created_at) }}</small>
                        </div>
                        <span class="sa-pill violet">Administrator</span>
                        <button class="sa-icon-btn danger sm" title="Remove administrator"
                            @click="askRemove('admin', a)">🗑</button>
                    </article>
                    <div v-if="!filteredAdmins.length" class="sa-empty">No administrators found.</div>
                </div>
            </section>

            <!-- 2 + 3. leaders with their members -->
            <section class="org-section">
                <div class="org-title">
                    <span class="org-step">2</span>
                    <h3>Team leaders &amp; their teams</h3>
                    <span class="sa-pill blue">{{ filteredLeaders.length }}</span>
                    <small class="org-hint">💡 Drag a member onto another team to move them</small>
                </div>

                <div class="team-grid">
                    <article v-for="l in filteredLeaders" :key="l.id" class="team-card"
                        :class="{ over: overTarget === 'l' + l.id }" @dragover.prevent="overTarget = 'l' + l.id"
                        @dragleave="onLeave($event, 'l' + l.id)" @drop.prevent="moveToLeader(l.id)">

                        <header class="team-head">
                            <div class="sa-avatar lg" :class="av(l.id)">{{ initials(l.name) }}</div>
                            <div class="team-head-info">
                                <h4>{{ l.name }}</h4>
                                <div class="team-head-tags">
                                    <span class="sa-pill blue">Team Leader</span>
                                    <span class="sa-pill">{{ l.workspace_name }}</span>
                                </div>
                            </div>
                            <button class="sa-icon-btn danger sm" title="Remove team leader"
                                @click="askRemove('leader', l)">🗑</button>
                        </header>

                        <div class="team-metrics">
                            <div><strong>{{ l.members.length }}</strong><span>Members</span></div>
                            <div><strong>{{ l.projects.length }}</strong><span>Projects</span></div>
                            <div><strong>{{ l.completion_rate }}%</strong><span>Success</span></div>
                        </div>

                        <div class="sa-progress">
                            <div :style="{ width: l.completion_rate + '%' }"></div>
                        </div>

                        <div class="team-members-label">
                            <span>Members</span>
                            <span class="org-step small">3</span>
                        </div>

                        <ul class="member-list">
                            <li v-for="m in visibleMembers(l)" :key="m.id" class="member-row" draggable="true"
                                @dragstart="onDragStart($event, m, l.id)" @dragend="onDragEnd">
                                <div class="sa-avatar sm" :class="av(m.id)">{{ initials(m.name) }}</div>
                                <div class="member-info">
                                    <strong>{{ m.name }}</strong>
                                    <span>{{ roleLabel(m) }}</span>
                                </div>
                                <span class="sa-pill" :class="m.role === 'TL' ? 'amber' : 'green'">
                                    {{ m.role === 'TL' ? 'Sub-leader' : 'Member' }}
                                </span>
                            </li>
                            <li v-if="!visibleMembers(l).length" class="member-empty">
                                Drop members here
                            </li>
                        </ul>
                    </article>

                    <div v-if="!filteredLeaders.length" class="sa-empty full">No team leaders found.</div>
                </div>
            </section>

            <!-- unassigned members -->
            <section class="org-section">
                <div class="org-title">
                    <span class="org-step">+</span>
                    <h3>Unassigned members</h3>
                    <span class="sa-pill amber">{{ filteredUnassigned.length }}</span>
                    <small class="org-hint">Drop a member here to take them off their team</small>
                </div>

                <div class="unassigned-zone" :class="{ over: overTarget === 'unassigned' }"
                    @dragover.prevent="overTarget = 'unassigned'" @dragleave="onLeave($event, 'unassigned')"
                    @drop.prevent="moveToUnassigned">
                    <div v-for="m in filteredUnassigned" :key="m.id" class="chip" draggable="true"
                        @dragstart="onDragStart($event, m, null)" @dragend="onDragEnd">
                        <div class="sa-avatar sm" :class="av(m.id)">{{ initials(m.name) }}</div>
                        <div class="member-info">
                            <strong>{{ m.name }}</strong>
                            <span>{{ roleLabel(m) }}</span>
                        </div>
                    </div>
                    <p v-if="!filteredUnassigned.length" class="zone-empty">
                        Everyone is on a team. Drag a member here to unassign them.
                    </p>
                </div>
            </section>
        </template>

        <!-- ===================== ANALYTICS ===================== -->
        <template v-else>

            <section class="sa-stats">
                <div class="sa-stat blue">
                    <div class="sa-stat-icon">📁</div>
                    <span class="sa-stat-label">Projects assigned</span>
                    <h2 class="sa-stat-value">{{ totals.total }}</h2>
                    <small class="sa-stat-sub">To all teams</small>
                </div>
                <div class="sa-stat green">
                    <div class="sa-stat-icon">✅</div>
                    <span class="sa-stat-label">Completed</span>
                    <h2 class="sa-stat-value">{{ totals.completed }}</h2>
                    <small class="sa-stat-sub">Fully finished</small>
                </div>
                <div class="sa-stat cyan">
                    <div class="sa-stat-icon">🚀</div>
                    <span class="sa-stat-label">In progress</span>
                    <h2 class="sa-stat-value">{{ totals.in_progress }}</h2>
                    <small class="sa-stat-sub">Being worked on</small>
                </div>
                <div class="sa-stat amber">
                    <div class="sa-stat-icon">⏳</div>
                    <span class="sa-stat-label">Pending</span>
                    <h2 class="sa-stat-value">{{ totals.pending }}</h2>
                    <small class="sa-stat-sub">Not started yet</small>
                </div>
                <div class="sa-stat violet">
                    <div class="sa-stat-icon">🏆</div>
                    <span class="sa-stat-label">Overall success rate</span>
                    <h2 class="sa-stat-value">{{ totals.rate }}%</h2>
                    <small class="sa-stat-sub">Completed / assigned</small>
                </div>
            </section>

            <!-- top 3 teams -->
            <section class="sa-card top-card">
                <div class="sa-card-head">
                    <h3>🏆 Top performing teams</h3>
                    <small class="org-hint">Ranked by completion rate</small>
                </div>

                <div class="podium" v-if="topThree.length">
                    <div v-for="(t, i) in topThree" :key="t.id" class="podium-card" :class="'rank-' + (i + 1)">
                        <span class="medal">{{ ['🥇', '🥈', '🥉'][i] }}</span>
                        <div class="sa-avatar lg" :class="av(t.id)">{{ initials(t.name) }}</div>
                        <h4>{{ t.name }}</h4>
                        <small>{{ t.workspace_name }}</small>
                        <div class="podium-rate">{{ t.rate }}%</div>
                        <div class="sa-progress">
                            <div :style="{ width: t.rate + '%' }"></div>
                        </div>
                        <small>{{ t.completed }} of {{ t.total }} projects completed</small>
                    </div>
                </div>
                <div v-else class="sa-empty">No projects have been assigned to teams yet.</div>
            </section>

            <!-- monthly distribution + donut -->
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
                        <h3>Status breakdown</h3>
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

            <!-- heatmap: team x month -->
            <section class="sa-card section-gap">
                <div class="sa-card-head">
                    <h3>Project distribution by team &amp; month</h3>
                    <small class="org-hint">Number of projects assigned to each team</small>
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
                                        <span>{{ row.name }}</span>
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
                <div v-else class="sa-empty">No team data yet.</div>
            </section>

            <!-- success per team -->
            <section class="sa-card section-gap">
                <div class="sa-card-head">
                    <h3>Success rate &amp; status by team</h3>
                </div>

                <div class="rate-list" v-if="ranked.length">
                    <div v-for="t in ranked" :key="t.id" class="rate-row">
                        <div class="rate-team">
                            <div class="sa-avatar sm" :class="av(t.id)">{{ initials(t.name) }}</div>
                            <strong>{{ t.name }}</strong>
                        </div>

                        <div class="rate-bar">
                            <div class="sa-progress">
                                <div :style="{ width: t.rate + '%' }"></div>
                            </div>
                        </div>

                        <strong class="rate-value">{{ t.rate }}%</strong>

                        <div class="rate-pills">
                            <span class="sa-pill green">{{ t.completed }} done</span>
                            <span class="sa-pill blue">{{ t.in_progress }} active</span>
                            <span class="sa-pill amber">{{ t.pending }} pending</span>
                        </div>
                    </div>
                </div>
                <div v-else class="sa-empty">No team data yet.</div>
            </section>
        </template>

            </div>
        </main>

        <div v-if="confirmState.show" class="sa-modal-overlay" @click.self="closeConfirm">
            <div class="sa-modal">
                <div class="sa-modal-icon">⚠️</div>
                <h3>{{ confirmTitle }}</h3>
                <p>{{ confirmMessage }}</p>

                <div class="sa-modal-actions">
                    <button class="sa-btn ghost" @click="closeConfirm">Cancel</button>
                    <button class="sa-btn danger" :disabled="confirmState.busy" @click="doRemove">
                        {{ confirmState.busy ? 'Please wait...' : 'Yes, remove' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import Sidebar from "./Sidebar.vue";

const toast = useToast();

const props = defineProps({
    admins: { type: Array, default: () => [] },
    leaders: { type: Array, default: () => [] },
    unassigned: { type: Array, default: () => [] },
    orgStats: { type: Object, default: () => ({ admins: 0, leaders: 0, members: 0, people: 0 }) },
});

/* theme (same key on every Super Admin page) */
const isDark = ref(localStorage.getItem("sa_theme") !== "light");
watch(isDark, (v) => localStorage.setItem("sa_theme", v ? "dark" : "light"));

const tab = ref("organisation");
const search = ref("");

/* ---------- local copies so drag and drop feels instant ---------- */
const clone = (v) => JSON.parse(JSON.stringify(v || []));
const localLeaders = ref(clone(props.leaders));
const localUnassigned = ref(clone(props.unassigned));

watch(() => props.leaders, (v) => { localLeaders.value = clone(v); });
watch(() => props.unassigned, (v) => { localUnassigned.value = clone(v); });

/* ---------- helpers ---------- */
const initials = (name) =>
    (name || "?").split(" ").filter(Boolean).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
const av = (id) => "av-" + (Number(id) % 6);
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString() : "—");
const roleLabel = (m) =>
    m.role === "TL" ? `Team Leader · Level ${m.level ?? 1}` : (m.department || "Member");
const firstError = (errs) => {
    const v = Object.values(errs || {})[0];
    return Array.isArray(v) ? v[0] : v;
};

/* ---------- search ---------- */
const q = computed(() => search.value.trim().toLowerCase());
const hit = (text) => !q.value || (text || "").toLowerCase().includes(q.value);

const filteredAdmins = computed(() => props.admins.filter((a) => hit(a.name) || hit(a.email)));
const filteredUnassigned = computed(() => localUnassigned.value.filter((m) => hit(m.name)));
const filteredLeaders = computed(() =>
    localLeaders.value.filter((l) => hit(l.name) || l.members.some((m) => hit(m.name)))
);
const visibleMembers = (l) => (hit(l.name) ? l.members : l.members.filter((m) => hit(m.name)));

/* ---------- drag and drop ---------- */
const drag = ref(null);
const overTarget = ref(null);

const onDragStart = (e, member, fromLeaderId) => {
    drag.value = { member, from: fromLeaderId };
    e.dataTransfer.effectAllowed = "move";
    e.dataTransfer.setData("text/plain", String(member.id));
};

const onDragEnd = () => {
    drag.value = null;
    overTarget.value = null;
};

const onLeave = (e, key) => {
    if (!e.currentTarget.contains(e.relatedTarget) && overTarget.value === key) {
        overTarget.value = null;
    }
};

const removeLocal = (memberId) => {
    localLeaders.value.forEach((l) => {
        l.members = l.members.filter((m) => m.id !== memberId);
    });
    localUnassigned.value = localUnassigned.value.filter((m) => m.id !== memberId);
};

const moveToLeader = (targetId) => {
    const d = drag.value;
    overTarget.value = null;
    drag.value = null;

    if (!d || d.from === targetId || d.member.id === targetId) return;

    const target = localLeaders.value.find((l) => l.id === targetId);
    if (!target) return;

    removeLocal(d.member.id);
    target.members.push({ ...d.member });

    router.post(`/super-admin/teams/${targetId}/members/${d.member.id}`, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => toast.success(`${d.member.name} moved to ${target.name}'s team`),
        onError: (errs) => toast.error(firstError(errs) || "Could not move this member."),
    });
};

const moveToUnassigned = () => {
    const d = drag.value;
    overTarget.value = null;
    drag.value = null;

    if (!d || d.from === null) return;

    removeLocal(d.member.id);
    localUnassigned.value.push({ ...d.member });

    router.delete(`/super-admin/teams/${d.from}/members/${d.member.id}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => toast.success(`${d.member.name} is now unassigned`),
        onError: (errs) => toast.error(firstError(errs) || "Could not unassign this member."),
    });
};

/* ---------- remove admin / leader ---------- */
const confirmState = ref({ show: false, kind: null, item: null, busy: false });

const askRemove = (kind, item) => {
    confirmState.value = { show: true, kind, item, busy: false };
};

const closeConfirm = () => {
    confirmState.value = { show: false, kind: null, item: null, busy: false };
};

const confirmTitle = computed(() =>
    confirmState.value.kind === "admin" ? "Remove administrator?" : "Remove team leader?"
);

const confirmMessage = computed(() => {
    const { kind, item } = confirmState.value;
    if (!item) return "";
    return kind === "admin"
        ? `${item.name} will lose access to the system. This cannot be undone.`
        : `${item.name} will be removed and can no longer log in. Their ${item.members.length} team member(s) will become unassigned.`;
});

const doRemove = () => {
    const { kind, item } = confirmState.value;
    if (!item) return;

    confirmState.value.busy = true;
    const url = kind === "admin" ? `/super-admin/admin/${item.id}` : `/super-admin/leaders/${item.id}`;

    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => toast.success(kind === "admin" ? "Administrator removed." : "Team leader removed."),
        onError: (errs) => toast.error(firstError(errs) || "Could not remove this person."),
        onFinish: closeConfirm,
    });
};

/* ---------- analytics ---------- */
const statusOf = (p) => (p.progress >= 100 ? "completed" : p.progress > 0 ? "in_progress" : "pending");

const teamStats = computed(() =>
    localLeaders.value.map((l) => {
        const ps = l.projects || [];
        const completed = ps.filter((p) => statusOf(p) === "completed").length;
        const in_progress = ps.filter((p) => statusOf(p) === "in_progress").length;
        const pending = ps.filter((p) => statusOf(p) === "pending").length;
        const total = ps.length;
        return {
            id: l.id,
            name: l.name,
            workspace_name: l.workspace_name,
            projects: ps,
            total,
            completed,
            in_progress,
            pending,
            rate: total ? Math.round((completed / total) * 100) : 0,
        };
    })
);

const ranked = computed(() =>
    [...teamStats.value]
        .filter((t) => t.total > 0)
        .sort((a, b) => b.rate - a.rate || b.completed - a.completed)
);

const topThree = computed(() => ranked.value.slice(0, 3));

const totals = computed(() => {
    const t = { total: 0, completed: 0, in_progress: 0, pending: 0 };
    teamStats.value.forEach((s) => {
        t.total += s.total;
        t.completed += s.completed;
        t.in_progress += s.in_progress;
        t.pending += s.pending;
    });
    return { ...t, rate: t.total ? Math.round((t.completed / t.total) * 100) : 0 };
});

const monthBuckets = (n) => {
    const now = new Date();
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
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? null : `${d.getFullYear()}-${d.getMonth()}`;
};

const allProjects = computed(() => teamStats.value.flatMap((t) => t.projects));

const monthly = computed(() => {
    const buckets = monthBuckets(8).map((b) => ({ ...b, completed: 0, in_progress: 0, pending: 0, total: 0 }));
    allProjects.value.forEach((p) => {
        const b = buckets.find((x) => x.key === monthKey(p.created_at));
        if (!b) return;
        b[statusOf(p)]++;
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
    teamStats.value.map((t) => {
        const cells = heatMonths.value.map(
            (m) => t.projects.filter((p) => monthKey(p.created_at) === m.key).length
        );
        return { id: t.id, name: t.name, cells, total: t.total };
    })
);

const heatMax = computed(() => Math.max(1, ...heatRows.value.flatMap((r) => r.cells)));

const cellStyle = (c) => ({
    background: c
        ? `color-mix(in srgb, var(--accent) ${Math.round(18 + (c / heatMax.value) * 62)}%, transparent)`
        : "transparent",
    color: c ? "var(--text-header)" : "var(--text-muted)",
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

/* ---------- theme variables ---------- */
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

/* ---------- main area ---------- */
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

/* ---------- cards & grids ---------- */
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

/* ---------- stat cards ---------- */
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

/* ---------- buttons, inputs, pills, avatars ---------- */
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

/* ---------- tabs, progress, empty ---------- */
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

/* ---------- confirm modal ---------- */
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

.section-gap {
    margin-bottom: 18px;
}

/* ---------- organisation ---------- */
.org-section {
    margin-bottom: 30px;
}

.org-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.org-title h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-header);
}

.org-hint {
    margin-left: auto;
    font-size: 11.5px;
    color: var(--text-muted);
}

.org-step {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 700;
    color: var(--on-accent);
    background: var(--accent);
}

.org-step.small {
    width: 18px;
    height: 18px;
    font-size: 10px;
    border-radius: 5px;
}

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 14px;
}

.person-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 12px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    box-shadow: 0 1px 2px var(--shadow-cards);
    transition: all 0.18s ease;
}

.person-card:hover {
    transform: translateY(-2px);
    border-color: var(--border-deep);
    box-shadow: 0 10px 22px -8px var(--shadow-hover);
}

.person-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.person-info strong {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-header);
}

.person-info span {
    font-size: 12px;
    color: var(--text-card-sub);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.person-info small {
    margin-top: 2px;
    font-size: 11px;
    color: var(--text-muted);
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
}

.team-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 18px;
    border-radius: 14px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    box-shadow: 0 1px 2px var(--shadow-cards);
    transition: all 0.18s ease;
}

.team-card.over {
    border-color: var(--accent);
    background: linear-gradient(180deg, var(--accent-soft), var(--panel-bg) 55%);
    box-shadow: 0 0 0 2px var(--accent-soft), 0 14px 30px -10px var(--shadow-hover);
}

.team-head {
    display: flex;
    align-items: center;
    gap: 12px;
}

.team-head-info {
    flex: 1;
    min-width: 0;
}

.team-head-info h4 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-header);
    margin-bottom: 6px;
}

.team-head-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.team-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.team-metrics div {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 6px;
    border-radius: 9px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.team-metrics strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-header);
}

.team-metrics span {
    margin-top: 2px;
    font-size: 10.5px;
    color: var(--text-muted);
}

.team-members-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--text-muted);
}

.member-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 7px;
    max-height: 250px;
    overflow-y: auto;
}

.member-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 11px;
    border-radius: 9px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    cursor: grab;
    transition: all 0.15s ease;
}

.member-row:hover {
    border-color: var(--border-deep);
    background: var(--card-inner-hover);
}

.member-row:active {
    cursor: grabbing;
    opacity: 0.6;
}

.member-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.member-info strong {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-main);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.member-info span {
    font-size: 11px;
    color: var(--text-muted);
}

.member-empty {
    padding: 18px 10px;
    text-align: center;
    font-size: 12px;
    color: var(--text-muted);
    border: 1px dashed var(--border-deep);
    border-radius: 9px;
}

.unassigned-zone {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    min-height: 90px;
    padding: 16px;
    border-radius: 14px;
    border: 2px dashed var(--border-deep);
    background: var(--panel-bg);
    transition: all 0.18s ease;
}

.unassigned-zone.over {
    border-color: var(--accent);
    background: var(--accent-soft);
}

.chip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px 8px 10px;
    border-radius: 10px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    cursor: grab;
}

.chip:hover {
    border-color: var(--border-deep);
}

.zone-empty {
    margin: auto;
    font-size: 12.5px;
    color: var(--text-muted);
}

.sa-empty.full {
    grid-column: 1 / -1;
}

/* ---------- analytics ---------- */
.podium {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 16px;
}

.podium-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 22px 18px 18px;
    text-align: center;
    border-radius: 14px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    transition: transform 0.18s ease;
}

.podium-card:hover {
    transform: translateY(-3px);
}

.podium-card.rank-1 {
    border-color: var(--c-gold);
    background: linear-gradient(180deg, color-mix(in srgb, var(--c-gold) 14%, transparent), var(--card-inner-bg) 70%);
}

.podium-card.rank-2 {
    border-color: #9aa3b8;
}

.podium-card.rank-3 {
    border-color: #b07a4b;
}

.medal {
    position: absolute;
    top: 10px;
    right: 14px;
    font-size: 22px;
}

.podium-card h4 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-header);
}

.podium-card small {
    font-size: 11.5px;
    color: var(--text-muted);
}

.podium-rate {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 30px;
    font-weight: 600;
    color: var(--text-header);
}

.podium-card .sa-progress {
    width: 100%;
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

.dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.dot.completed,
.seg.completed {
    background: var(--c-green);
}

.dot.in_progress,
.seg.in_progress {
    background: var(--c-blue);
}

.dot.pending,
.seg.pending {
    background: var(--c-amber);
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
    font-weight: 500;
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

.rate-list {
    display: flex;
    flex-direction: column;
}

.rate-row {
    display: grid;
    grid-template-columns: 220px 1fr 56px auto;
    align-items: center;
    gap: 16px;
    padding: 13px 4px;
    border-bottom: 1px solid var(--border-divider);
}

.rate-row:last-child {
    border-bottom: none;
}

.rate-team {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.rate-team strong {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rate-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    color: var(--text-header);
    text-align: right;
}

.rate-pills {
    display: flex;
    gap: 6px;
}

@media (max-width: 1100px) {
    .rate-row {
        grid-template-columns: 1fr 56px;
    }

    .rate-bar,
    .rate-pills {
        grid-column: 1 / -1;
    }
}
</style>