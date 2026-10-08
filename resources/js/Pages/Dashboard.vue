<template>

    <Head title="Dashboard" />

    <div class="dashboard" :class="theme.themeClass">
        <Sidebar />

        <main class="main-content">

            <!-- TOPBAR -->
            <div class="topbar">
                <div class="topbar-greeting">
                    <h2>
                        Admin Dashboard <span class="wave">👋</span>
                    </h2>
                    <p>
                        Welcome back, manage your dashboard.
                    </p>
                </div>

                <div class="topbar-icons">

                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.6" />
                            <path d="M17 17L13.5 13.5" stroke="currentColor" stroke-width="1.6"
                                stroke-linecap="round" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Search projects..." class="search-box" />
                    </div>

                    <button class="theme-btn" @click="theme.toggleTheme" aria-label="Toggle theme">
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>

                    <div class="notification-bell-container"
                        v-click-outside="() => notificationStore.showBellDropdown = false">
                        <button class="icon-btn"
                            @click.stop="notificationStore.showBellDropdown = !notificationStore.showBellDropdown"
                            aria-label="Notifications">
                            🔔
                            <span v-if="
                                unreadCount > 0 ||
                                notificationStore.activeUrgentTasks.length > 0
                            " class="bell-alert-green-dot">
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

                                <!-- EMPTY STATE -->
                                <div v-if="unreadNotifications.length === 0 && notificationStore.activeUrgentTasks.length === 0"
                                    class="notification-empty-state">
                                    🎉 No notifications right now.
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="profile-container">
                        <img src="https://i.pravatar.cc/100" class="avatar"
                            @click.stop="showProfileMenu = !showProfileMenu" />

                        <div v-if="showProfileMenu" class="profile-dropdown">
                            <button @click="logout">Logout</button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="content-wrapper">

                <draggable v-model="widgets" item-key="id" class="stats-grid" @end="saveWidgetOrder">
                    <template #item="{ element }">
                        <div class="stat-card" :class="element.widget_type.replace(/_/g, '-') + '-card'">

                            <div class="stat-icon-badge">
                                <span v-if="element.widget_type === 'total_projects'">📁</span>
                                <span v-else-if="element.widget_type === 'team_members'">👥</span>
                                <span v-else-if="element.widget_type === 'completed_tasks'">✅</span>
                                <span v-else-if="element.widget_type === 'pending_tasks'">⏳</span>
                            </div>

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

                        <!-- WORKSPACE OVERVIEW (workspace + team members | teams + TL projects) -->
                        <div class="workspace-overview">

                            <!-- LEFT: workspace name at the top, then team members with roles -->
                            <div class="overview-card workspace-card">
                                <div class="workspace-heading">
                                    <span class="overview-label">Workspace</span>
                                    <h3 class="workspace-name">{{ workspaceName }}</h3>
                                </div>

                                <div class="overview-subtitle">Team Members &amp; Roles</div>

                                <div class="overview-list">
                                    <div class="overview-row" v-for="person in workspacePeople" :key="person.key">
                                        <div class="overview-avatar">{{ person.initials }}</div>

                                        <div class="overview-info">
                                            <strong>
                                                {{ person.name }}
                                                <em v-if="person.isYou" class="you-tag">You</em>
                                            </strong>
                                            <span>{{ person.role }}</span>
                                        </div>
                                    </div>

                                    <div v-if="workspacePeople.length === 0" class="overview-empty">
                                        No team members yet.
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT: total teams + projects assigned to the TL -->
                            <div class="overview-card teams-card">
                                <span class="overview-label">Teams Overview</span>

                                <div class="overview-metrics">
                                    <div class="metric-tile">
                                        <span class="metric-value">{{ totalTeams }}</span>
                                        <span class="metric-label">Total Teams</span>
                                    </div>

                                    <div class="metric-tile">
                                        <span class="metric-value">{{ tlAssignedProjects }}</span>
                                        <span class="metric-label">Projects Assigned to TL</span>
                                    </div>
                                </div>

                                <div class="overview-subtitle">Team Lead</div>

                                <div class="overview-list">
                                    <div class="overview-row" v-for="lead in teamLeadsDetail" :key="lead.key">
                                        <div class="overview-avatar tl-avatar">{{ lead.initials }}</div>

                                        <div class="overview-info">
                                            <strong>{{ lead.name }}</strong>
                                            <span>{{ lead.projectCount }} project{{ lead.projectCount === 1 ? "" : "s" }} assigned</span>
                                        </div>
                                    </div>

                                    <div v-if="teamLeadsDetail.length === 0" class="overview-empty">
                                        No team lead assigned yet.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="sidebar-right-stack">

                        <!-- Productivity: completed projects out of all assigned projects -->
                        <div class="productivity-card">
                            <div class="prod-top-row">
                                <span class="prod-title">Productivity</span>
                                <h2 class="prod-percentage">
                                    {{ productivity.total ? productivity.rate + '%' : '—' }}
                                </h2>
                            </div>

                            <p class="prod-desc-text">
                                {{ productivityMessage }}
                            </p>

                            <div class="prod-track">
                                <div class="prod-fill" :style="{ width: productivity.rate + '%' }"></div>
                            </div>

                            <button class="prod-view-btn">
                                View Report
                            </button>
                        </div>

                        <!-- Projects assigned per month -->
                        <div class="panel-container monthly-projects-panel">
                            <div class="panel-header">
                                <h2>Projects Assigned per Month</h2>
                                <span class="monthly-total">{{ monthlyTotal }} total</span>
                            </div>

                            <div class="month-chart">
                                <div class="month-col" v-for="month in monthlyProjects" :key="month.key">
                                    <span class="month-count">{{ month.count }}</span>

                                    <div class="month-bar-track">
                                        <div class="month-bar-fill" :class="{ 'is-current': month.isCurrent }"
                                            :style="{ height: month.height + '%' }"></div>
                                    </div>

                                    <span class="month-label">{{ month.label }}</span>
                                </div>
                            </div>

                            <p v-if="monthlyTotal === 0" class="overview-empty">
                                No projects assigned in the last 6 months.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useThemeStore } from "../stores/theme";
import Sidebar from "./components/Sidebar.vue";
import { useNotificationStore } from '@/stores/notificationStore';
import draggable from 'vuedraggable';
import { Head } from '@inertiajs/vue3';

const notificationStore = useNotificationStore();
const page = usePage();
let notificationRefreshInterval = null;
const props = defineProps({
    stats: Object,
    widgets: Array,
    projects: Array,
    members: Array,
    notifications: Array,
    // NEW: optional data for the workspace / teams sections
    workspace: Object, // { id, name }
    teams: Array       // list of teams in the workspace
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

// A project counts as completed when its status is "Completed" or all of its work is done (100%)
const isProjectCompleted = (project) =>
    String(project.status || "").toLowerCase() === "completed" ||
    Number(project.progress || 0) >= 100;

// Productivity = completed projects out of all projects assigned to the team
const productivity = computed(() => {
    const total = boards.value.length;
    const completed = boards.value.filter(isProjectCompleted).length;

    return {
        total,
        completed,
        rate: total ? Math.round((completed / total) * 100) : 0,
    };
});

const productivityMessage = computed(() => {
    const { total, completed } = productivity.value;

    if (!total) return "No projects have been assigned yet.";

    return `${completed} of ${total} project${total === 1 ? "" : "s"} completed.`;
});

const teamMembersList = computed(() => props.members || []);

const theme = useThemeStore();
const showProfileMenu = ref(false);

const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

/* ---------------------------------------------------------------
   WORKSPACE OVERVIEW
   --------------------------------------------------------------- */

const authUser = computed(() => page.props.auth?.user ?? null);

// Workspaces shared from HandleInertiaRequests (owned by the logged-in admin)
const sharedWorkspaces = computed(() => page.props.workspaces || []);

const findWorkspaceName = (id) => {
    if (id == null || id === "") return null;
    return (
        sharedWorkspaces.value.find((w) => String(w.id) === String(id))?.name ||
        null
    );
};

// Workspace name shown at the top of the left card:
// 1) the workspace the team leader belongs to (member.workspace_id)
// 2) the workspace currently selected in the sidebar (session workspace_id)
const workspaceName = computed(
    () =>
        props.workspace?.name ||
        teamLeads.value.map((lead) => lead.workspace?.name || findWorkspaceName(lead.workspace_id)).find(Boolean) ||
        findWorkspaceName(page.props.currentWorkspace) ||
        teamMembersList.value.map((m) => m.workspace?.name || findWorkspaceName(m.workspace_id)).find(Boolean) ||
        "—"
);

const getInitials = (name) =>
    String(name || "")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");

const fullName = (person) =>
    [person?.first_name, person?.last_name].filter(Boolean).join(" ") ||
    person?.name ||
    "Unknown";

// Logged-in admin (with their role) first, followed by the rest of the team members and their roles
const workspacePeople = computed(() => {
    const people = [];
    const admin = authUser.value;

    if (admin) {
        const name = fullName(admin);
        people.push({
            key: "admin-" + (admin.id ?? "me"),
            name,
            initials: getInitials(name),
            role: admin.role || "Admin",
            isYou: true,
        });
    }

    teamMembersList.value
        .filter((member) => !admin || member.id !== admin.id)
        .forEach((member) => {
            const name = fullName(member);
            people.push({
                key: "member-" + member.id,
                name,
                initials: getInitials(name),
                role: member.role || "Member",
                isYou: false,
            });
        });

    return people;
});

// Total teams in the workspace
// Each team leader leads one team (members point to their TL via assigned_to)
const totalTeams = computed(
    () =>
        props.stats?.totalTeams ??
        props.teams?.length ??
        teamLeads.value.length
);

// Is this member a Team Lead?
const isTeamLead = (member) =>
    ["tl", "team lead", "team leader"].includes(
        String(member?.role || "").trim().toLowerCase()
    );

const teamLeads = computed(() => teamMembersList.value.filter(isTeamLead));

// Keys a project may use to point at its TL / assignee
const TL_KEYS = ["team_lead_id", "tl_id", "assigned_to", "assigned_to_id", "lead_id"];

const projectHasAssignmentInfo = computed(() =>
    boards.value.some((project) => TL_KEYS.some((key) => project[key] != null))
);

const projectsForLead = (lead) =>
    boards.value.filter((project) =>
        TL_KEYS.some(
            (key) => project[key] != null && String(project[key]) === String(lead.id)
        )
    ).length;

// Per-TL details (name + number of projects assigned)
const teamLeadsDetail = computed(() =>
    teamLeads.value.map((lead) => {
        const name = fullName(lead);
        return {
            key: "tl-" + lead.id,
            name,
            initials: getInitials(name),
            // If projects don't carry assignee info, all workspace projects are treated as assigned to the TL
            projectCount: projectHasAssignmentInfo.value
                ? projectsForLead(lead)
                : boards.value.length,
        };
    })
);

// Total number of projects assigned to the TL
const tlAssignedProjects = computed(() => {
    if (props.stats?.tlProjects != null) return props.stats.tlProjects;

    if (!teamLeads.value.length) return 0;

    if (!projectHasAssignmentInfo.value) return boards.value.length;

    return boards.value.filter((project) =>
        teamLeads.value.some((lead) =>
            TL_KEYS.some(
                (key) => project[key] != null && String(project[key]) === String(lead.id)
            )
        )
    ).length;
});

/* ---------------------------------------------------------------
   PROJECTS ASSIGNED PER MONTH (last 6 months)
   --------------------------------------------------------------- */
const monthKey = (date) => `${date.getFullYear()}-${date.getMonth()}`;

const monthlyProjects = computed(() => {
    const now = new Date();
    const months = [];

    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        months.push({
            key: monthKey(d),
            label: d.toLocaleString("default", { month: "short" }),
            count: 0,
            isCurrent: i === 0,
        });
    }

    boards.value.forEach((project) => {
        // Date the project was assigned / created
        const raw =
            project.assigned_at ||
            project.created_at ||
            project.start_date ||
            project.deadline;
        if (!raw) return;

        const date = new Date(raw);
        if (isNaN(date)) return;

        const bucket = months.find((m) => m.key === monthKey(date));
        if (bucket) bucket.count++;
    });

    const max = Math.max(...months.map((m) => m.count), 1);

    return months.map((m) => ({
        ...m,
        height: m.count ? Math.max((m.count / max) * 100, 8) : 0,
    }));
});

const monthlyTotal = computed(() =>
    monthlyProjects.value.reduce((sum, m) => sum + m.count, 0)
);

const unreadNotifications = computed(() => {
    return [...(props.notifications || [])]
        .filter(notification => !notification.is_read)
        .filter(notification => {
            if (!currentUserId.value) return true;
            return notification.created_by !== currentUserId.value;
        })
        .sort(
            (a, b) =>
                new Date(b.created_at) -
                new Date(a.created_at)
        );
});

const unreadCount = computed(() => {
    return unreadNotifications.value.length;
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
    router.put(`/notifications/${id}/read`, {}, {
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
.theme-dark {
    --dashboard-bg: #222736;
    --panel-bg: #111827;
    --card-inner-bg: #111827;
    --card-inner-hover: #313749;
    --activity-item-bg: #262b3d;
    --stat-card-bg: #111827;
    --input-element-bg: #323a4f;
    --input-element-focus: #3a4258;
    --dropdown-panel-bg: #111827;
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
    width: 100vw;
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
    background: var(--panel-bg);
}

.content-wrapper {
    max-width: 1600px;
    margin: 0 auto;
    width: 100%;
    padding: 24px 36px 56px;
    background: var(--panel-bg);
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
    top: 0px;
    z-index: 40;
}

.topbar-icons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.topbar-greeting h2 {
    margin: 0;
    font-size: 28px;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
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
    display: flex;
    flex-direction: column;
    background: var(--stat-card-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 18px 20px 20px;
    box-shadow: 0 2px 6px var(--shadow-stats);
    transition: all 0.2s ease;
    overflow: hidden;
    cursor: grab;
}

.stat-card:active {
    cursor: grabbing;
}

.stat-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    height: 3px;
}

.total-projects-card::before {
    background: var(--c-blue);
}

.team-members-card::before {
    background: var(--c-cyan);
}

.completed-tasks-card::before {
    background: var(--c-green);
}

.pending-tasks-card::before {
    background: var(--c-amber);
}

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

.total-projects-card .stat-icon-badge {
    background: rgba(85, 110, 230, 0.14);
}

.team-members-card .stat-icon-badge {
    background: rgba(80, 165, 241, 0.14);
}

.completed-tasks-card .stat-icon-badge {
    background: rgba(52, 195, 143, 0.14);
}

.pending-tasks-card .stat-icon-badge {
    background: rgba(241, 180, 76, 0.16);
}

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

.dashboard-layout-split {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 20px;
}

@media (max-width: 1150px) {
    .dashboard-layout-split {
        grid-template-columns: 1fr;
    }
}

.panel-container {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 24px;
    box-shadow: 0 2px 6px var(--shadow-cards);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.panel-header h2 {
    font-size: 15px;
    font-weight: 600;
    margin: 0;
    color: var(--text-header);
    letter-spacing: -0.1px;
}

.action-btn-primary {
    background: var(--accent);
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.15s, transform 0.15s;
}

.action-btn-primary:hover {
    background: var(--accent-2);
    transform: translateY(-0.5px);
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
}

.project-card {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 9px;
    padding: 16px;
    transition: border-color 0.15s ease;
}

.project-card:hover {
    border-color: var(--border-deep);
}

.project-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.project-card-header h3 {
    font-size: 13.5px;
    font-weight: 500;
    margin: 0;
    color: var(--text-main);
}

.project-status {
    padding: 3px 9px;
    border-radius: 5px;
    background: rgba(52, 195, 143, 0.12);
    color: var(--c-green);
    font-size: 10.5px;
    font-weight: 600;
    text-transform: capitalize;
}

.project-progress {
    margin: 12px 0 0;
    font-size: 19px;
    font-weight: 700;
    color: var(--text-header);
}

.progress-track-line {
    height: 5px;
    background: var(--border-deep);
    border-radius: 4px;
    margin-top: 10px;
    overflow: hidden;
}

.progress-track-fill {
    height: 100%;
    width: 0%;
    background: var(--c-violet);
    border-radius: 4px;
    transition: width 0.9s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: width;
}

.project-footer {
    margin-top: 11px;
    color: var(--text-muted);
    font-size: 11.5px;
}

/* ---------- Workspace overview (below the project cards) ---------- */
.workspace-overview {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 22px;
    padding-top: 22px;
    border-top: 1px solid var(--border-divider);
}

@media (max-width: 900px) {
    .workspace-overview {
        grid-template-columns: 1fr;
    }
}

.overview-card {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 9px;
    padding: 16px 18px;
    min-width: 0;
    transition: border-color 0.15s ease;
}

.overview-card:hover {
    border-color: var(--border-deep);
}

.overview-label {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}

.workspace-heading {
    margin-bottom: 14px;
}

.workspace-name {
    margin: 4px 0 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--text-header);
    letter-spacing: -0.2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.overview-subtitle {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-card-sub);
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-divider);
}

.overview-list {
    display: flex;
    flex-direction: column;
}

.overview-row {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 9px 0;
    border-bottom: 1px solid var(--border-divider);
}

.overview-row:last-child {
    border-bottom: none;
}

.overview-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--c-blue);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 10.5px;
    flex-shrink: 0;
}

.overview-avatar.tl-avatar {
    background: var(--c-violet);
}

.overview-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.overview-info strong {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.overview-info span {
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 2px;
}

.you-tag {
    font-style: normal;
    font-size: 9.5px;
    font-weight: 600;
    margin-left: 6px;
    padding: 2px 6px;
    border-radius: 4px;
    background: var(--accent-soft);
    color: var(--accent);
}

.overview-empty {
    padding: 14px 0 4px;
    font-size: 12px;
    color: var(--text-muted);
}

.overview-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin: 12px 0 16px;
}

.metric-tile {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px 14px;
    border-radius: 8px;
    background: var(--accent-soft);
    border: 1px solid var(--border-subtle);
}

.metric-value {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.1;
    color: var(--text-header);
    letter-spacing: -0.3px;
}

.metric-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
}

/* ---------- Projects per month chart ---------- */
.monthly-total {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
}

.month-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
    height: 190px;
    padding-top: 6px;
}

.month-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    height: 100%;
    min-width: 0;
}

.month-count {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-header);
}

.month-bar-track {
    flex: 1;
    width: 100%;
    max-width: 34px;
    display: flex;
    align-items: flex-end;
    background: var(--input-element-bg);
    border-radius: 6px;
    overflow: hidden;
}

.month-bar-fill {
    width: 100%;
    background: var(--c-violet);
    border-radius: 6px;
    transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.month-bar-fill.is-current {
    background: var(--accent);
}

.month-label {
    font-size: 11px;
    color: var(--text-muted);
}

.sidebar-right-stack {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.productivity-card {
    background: linear-gradient(150deg, #556ee6 0%, #6f7fee 100%);
    border-radius: 10px;
    padding: 20px 22px;
    color: #ffffff;
    box-shadow: 0 4px 14px var(--shadow-cards);
}

.prod-top-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 6px;
}

.prod-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
}

.prod-percentage {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
    letter-spacing: -0.4px;
}

.prod-desc-text {
    margin: 0 0 12px 0;
    font-size: 12.5px;
    opacity: 0.9;
    font-weight: 400;
}

.prod-track {
    height: 6px;
    margin: 0 0 16px 0;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.25);
    overflow: hidden;
}

.prod-fill {
    height: 100%;
    border-radius: 999px;
    background: #ffffff;
    transition: width 0.6s ease;
}

.prod-view-btn {
    background: #ffffff;
    color: var(--accent);
    border: none;
    padding: 9px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 11.5px;
    cursor: pointer;
    transition: transform 0.15s;
}

.prod-view-btn:hover {
    transform: translateY(-1px);
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
    font-size: 11.5px;
    flex-shrink: 0;
}

.rep-row:nth-child(5n+2) .rep-avatar {
    background: var(--c-blue);
}

.rep-row:nth-child(5n+3) .rep-avatar {
    background: var(--c-violet);
}

.rep-row:nth-child(5n+4) .rep-avatar {
    background: var(--c-cyan);
}

.rep-row:nth-child(5n+5) .rep-avatar {
    background: var(--c-amber);
}

.rep-row:nth-child(5n+6) .rep-avatar {
    background: var(--c-green);
}

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

.status-pill {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 5px;
    text-transform: capitalize;
    flex-shrink: 0;
}

.status-pill.online {
    background: rgba(52, 195, 143, 0.12);
    color: var(--c-green);
}

.status-pill.away {
    background: rgba(241, 180, 76, 0.15);
    color: #b9822e;
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
    z-index: 9999;
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
</style>