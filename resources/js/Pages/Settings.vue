<template>
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">

            <header class="settings-header">

                <div>
                    <h1>Settings</h1>

                    <p>
                        Manage workspace preferences, security,
                        notifications and account settings.
                    </p>
                </div>

                <button
                    class="theme-btn"
                    @click="theme.toggleTheme"
                >
                    {{ theme.isDark ? "☀️" : "🌙" }}
                </button>

            </header>

            <div class="settings-tabs">

                <button
                    :class="{ active: activeTab === 'profile' }"
                    @click="activeTab = 'profile'"
                >
                    👤 Profile
                </button>

                <button
                    :class="{ active: activeTab === 'workspace' }"
                    @click="activeTab = 'workspace'"
                >
                    🏢 Workspace
                </button>

                <button
                    :class="{ active: activeTab === 'security' }"
                    @click="activeTab = 'security'"
                >
                    🔒 Security
                </button>

                <button
                    :class="{ active: activeTab === 'notifications' }"
                    @click="activeTab = 'notifications'"
                >
                    🔔 Notifications
                </button>

                <button
                    :class="{ active: activeTab === 'theme' }"
                    @click="activeTab = 'theme'"
                >
                    🎨 Appearance
                </button>

                <button
                    :class="{ active: activeTab === 'danger' }"
                    @click="activeTab = 'danger'"
                >
                    ⚠️ Danger Zone
                </button>

            </div>

            <section
                v-if="activeTab === 'profile'"
                class="settings-card"
            >

                <div class="card-header">

                    <div>
                        <h2>Profile Information</h2>

                        <p>
                            Update your personal details.
                        </p>
                    </div>

                </div>

                <div class="avatar-section">

                    <div class="avatar-circle">
                        {{ userInitials }}
                    </div>

                    <div>
                        <h3>{{ profile.name }}</h3>
                        <span>{{ profile.email }}</span>
                    </div>

                </div>

                <div class="settings-grid">

                    <div class="form-group">
                        <label>Full Name</label>

                        <input
                            type="text"
                            v-model="profile.name"
                        />
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>

                        <input
                            type="email"
                            v-model="profile.email"
                        />
                    </div>

                    <div class="form-group">
                        <label>Department</label>

                        <input
                            type="text"
                            v-model="profile.department"
                        />
                    </div>

                    <div class="form-group">
                        <label>Role</label>

                        <input
                            type="text"
                            v-model="profile.role"
                            readonly
                        />
                    </div>

                </div>

                <div class="card-footer">
                    <button class="primary-btn">
                        Save Changes
                    </button>
                </div>

            </section>

            <section
                v-if="activeTab === 'workspace'"
                class="settings-card"
            >

                <div class="card-header">

                    <div>
                        <h2>Workspace Settings</h2>

                        <p>
                            Configure your workspace details.
                        </p>
                    </div>

                </div>

                <div class="workspace-banner">

                    <div>
                        <h3>{{ workspace.name }}</h3>

                        <p>
                            {{ workspace.description }}
                        </p>
                    </div>

                    <span class="workspace-badge">
                        Active Workspace
                    </span>

                </div>

                <div class="settings-grid">

                    <div class="form-group">
                        <label>Workspace Name</label>

                        <input
                            type="text"
                            v-model="workspace.name"
                        />
                    </div>

                    <div class="form-group">
                        <label>Workspace URL</label>

                        <input
                            type="text"
                            v-model="workspace.slug"
                        />
                    </div>

                </div>

                <div class="form-group full-width">

                    <label>Description</label>

                    <textarea
                        rows="5"
                        v-model="workspace.description"
                    ></textarea>

                </div>

                <div class="stats-row">

                    <div class="stat-card">
                        <h4>Total Members</h4>
                        <span>{{ stats.members }}</span>
                    </div>

                    <div class="stat-card">
                        <h4>Total Projects</h4>
                        <span>{{ stats.projects }}</span>
                    </div>

                    <div class="stat-card">
                        <h4>Total Tasks</h4>
                        <span>{{ stats.tasks }}</span>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="primary-btn">
                        Update Workspace
                    </button>
                </div>

            </section>
            <section
                v-if="activeTab === 'security'"
                class="settings-card"
            >

                <div class="card-header">

                    <div>
                        <h2>Security Settings</h2>

                        <p>
                            Update your password and account security.
                        </p>
                    </div>

                </div>

                <div class="settings-grid">

                    <div class="form-group">
                        <label>Current Password</label>

                        <input
                            type="password"
                            v-model="security.currentPassword"
                            placeholder="Enter current password"
                        />
                    </div>

                    <div class="form-group">
                        <label>New Password</label>

                        <input
                            type="password"
                            v-model="security.newPassword"
                            placeholder="Enter new password"
                        />
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>

                        <input
                            type="password"
                            v-model="security.confirmPassword"
                            placeholder="Confirm new password"
                        />
                    </div>

                </div>

                <div class="security-cards">

                    <div class="mini-card">

                        <div>
                            <h4>Two Factor Authentication</h4>
                            <p>
                                Add extra protection to your account.
                            </p>
                        </div>

                        <label class="switch">
                            <input
                                type="checkbox"
                                v-model="security.twoFactor"
                            >
                            <span></span>
                        </label>

                    </div>

                    <div class="mini-card">

                        <div>
                            <h4>Login Alerts</h4>
                            <p>
                                Receive alerts for new sign-ins.
                            </p>
                        </div>

                        <label class="switch">
                            <input
                                type="checkbox"
                                v-model="security.loginAlerts"
                            >
                            <span></span>
                        </label>

                    </div>

                </div>

                <div class="card-footer">
                    <button class="primary-btn">
                        Update Security
                    </button>
                </div>

            </section>

            <section
                v-if="activeTab === 'notifications'"
                class="settings-card"
            >

                <div class="card-header">

                    <div>
                        <h2>Notification Preferences</h2>

                        <p>
                            Control how and when you receive updates.
                        </p>
                    </div>

                </div>

                <div class="notification-list">

                    <div class="notify-item">

                        <div>
                            <h4>Email Notifications</h4>
                            <p>
                                Receive updates by email.
                            </p>
                        </div>

                        <label class="switch">
                            <input
                                type="checkbox"
                                v-model="notifications.email"
                            >
                            <span></span>
                        </label>

                    </div>

                    <div class="notify-item">

                        <div>
                            <h4>Task Assignments</h4>
                            <p>
                                Notify when tasks are assigned.
                            </p>
                        </div>

                        <label class="switch">
                            <input
                                type="checkbox"
                                v-model="notifications.tasks"
                            >
                            <span></span>
                        </label>

                    </div>

                    <div class="notify-item">

                        <div>
                            <h4>Project Updates</h4>
                            <p>
                                Notify on project status changes.
                            </p>
                        </div>

                        <label class="switch">
                            <input
                                type="checkbox"
                                v-model="notifications.projects"
                            >
                            <span></span>
                        </label>

                    </div>

                    <div class="notify-item">

                        <div>
                            <h4>Weekly Reports</h4>
                            <p>
                                Receive productivity summaries.
                            </p>
                        </div>

                        <label class="switch">
                            <input
                                type="checkbox"
                                v-model="notifications.reports"
                            >
                            <span></span>
                        </label>

                    </div>

                </div>

                <div class="card-footer">
                    <button class="primary-btn">
                        Save Preferences
                    </button>
                </div>

            </section>

            <section
                v-if="activeTab === 'theme'"
                class="settings-card"
            >

                <div class="card-header">

                    <div>
                        <h2>Appearance</h2>

                        <p>
                            Personalize your workspace experience.
                        </p>
                    </div>

                </div>

                <div class="theme-grid">

                    <div
                        class="theme-option"
                        :class="{ selected: theme.isDark }"
                    >
                        <div class="theme-preview dark-preview"></div>

                        <h4>Dark Mode</h4>

                        <button
                            class="secondary-btn"
                            @click="theme.setDark()"
                        >
                            Activate
                        </button>
                    </div>

                    <div
                        class="theme-option"
                        :class="{ selected: !theme.isDark }"
                    >
                        <div class="theme-preview light-preview"></div>

                        <h4>Light Mode</h4>

                        <button
                            class="secondary-btn"
                            @click="theme.setLight()"
                        >
                            Activate
                        </button>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="primary-btn">
                        Save Appearance
                    </button>
                </div>

            </section>

            <section
                v-if="activeTab === 'danger'"
                class="danger-card"
            >

                <div class="danger-header">

                    <h2>Danger Zone</h2>

                    <p>
                        These actions are permanent and cannot be undone.
                    </p>

                </div>

                <div class="danger-actions">

                    <div class="danger-item">

                        <div>
                            <h4>Delete Workspace</h4>

                            <p>
                                Permanently remove workspace,
                                projects and tasks.
                            </p>
                        </div>

                        <button class="danger-btn">
                            Delete Workspace
                        </button>

                    </div>

                    <div class="danger-item">

                        <div>
                            <h4>Leave Workspace</h4>

                            <p>
                                Remove yourself from this workspace.
                            </p>
                        </div>

                        <button class="danger-btn">
                            Leave Workspace
                        </button>

                    </div>

                </div>

            </section>

        </main>

    </div>
</template>
<script setup>
import { ref, reactive, computed } from "vue";
import Sidebar from "./components/Sidebar.vue";
import { useThemeStore } from "../stores/theme";

const theme = useThemeStore();

const activeTab = ref("profile");

const profile = reactive({
    name: "Admin User",
    email: "admin@baseline.com",
    department: "Development",
    role: "Administrator",
});

const workspace = reactive({
    name: "Baseline Workspace",
    slug: "baseline-workspace",
    description:
        "Manage projects, teams and productivity from a single place.",
});

const security = reactive({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
    twoFactor: false,
    loginAlerts: true,
});

const notifications = reactive({
    email: true,
    tasks: true,
    projects: true,
    reports: false,
});

const stats = reactive({
    members: 18,
    projects: 7,
    tasks: 146,
});

const userInitials = computed(() => {
    return profile.name
        .split(" ")
        .map(word => word[0])
        .join("")
        .toUpperCase();
});
</script>

<style scoped>


.main-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.settings-header h1 {
    font-size: 34px;
    font-weight: 700;
    color: var(--text);
}

.settings-header p {
    margin-top: 8px;
    color: var(--subtext);
}

.theme-btn {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    background: var(--card);
    color: var(--text);
    font-size: 18px;
}


.settings-tabs {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.settings-tabs button {
    border: none;
    padding: 12px 18px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    background: var(--card);
    color: var(--text);
    transition: 0.25s;
}

.settings-tabs button:hover {
    transform: translateY(-2px);
}

.settings-tabs button.active {
    background: #06b6d4;
    color: #fff;
}


.settings-card,
.danger-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 28px;
}

.card-header {
    margin-bottom: 24px;
}

.card-header h2 {
    font-size: 24px;
    margin-bottom: 6px;
}

.card-header p {
    color: var(--subtext);
}

.card-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
}

.avatar-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.avatar-circle {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    background: #06b6d4;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
    font-weight: bold;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.full-width {
    margin-top: 20px;
}

.form-group label {
    margin-bottom: 8px;
    color: var(--subtext);
}

.form-group input,
.form-group textarea {
    padding: 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    outline: none;
}

.form-group textarea {
    resize: none;
}


.primary-btn {
    background: #06b6d4;
    color: white;
    border: none;
    padding: 13px 24px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
}

.secondary-btn {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
}

.workspace-banner {
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.workspace-badge {
    background: #22c55e;
    color: white;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 20px;
    margin-top: 24px;
}

.stat-card {
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 20px;
    border-radius: 16px;
}

.stat-card h4 {
    color: var(--subtext);
    margin-bottom: 10px;
}

.stat-card span {
    font-size: 28px;
    font-weight: bold;
}


.security-cards {
    margin-top: 25px;
    display: grid;
    gap: 15px;
}

.mini-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}


.notification-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.notify-item {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}


.switch {
    position: relative;
    display: inline-block;
    width: 56px;
    height: 30px;
}

.switch input {
    display: none;
}

.switch span {
    position: absolute;
    inset: 0;
    background: #64748b;
    border-radius: 30px;
    cursor: pointer;
    transition: .3s;
}

.switch span::before {
    content: "";
    position: absolute;
    width: 24px;
    height: 24px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: .3s;
}

.switch input:checked + span {
    background: #06b6d4;
}

.switch input:checked + span::before {
    transform: translateX(26px);
}


.theme-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 25px;
}

.theme-option {
    background: var(--bg);
    border: 2px solid var(--border);
    border-radius: 18px;
    padding: 20px;
    text-align: center;
}

.theme-option.selected {
    border-color: #06b6d4;
}

.theme-preview {
    height: 120px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.dark-preview {
    background: #0f172a;
}

.light-preview {
    background: #f8fafc;
    border: 1px solid #dbeafe;
}

.danger-header {
    margin-bottom: 24px;
}

.danger-header h2 {
    color: #ef4444;
}

.danger-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.danger-item {
    background: rgba(239,68,68,.08);
    border: 1px solid rgba(239,68,68,.2);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.danger-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 12px;
    cursor: pointer;
}


@media (max-width: 992px) {

    .settings-grid {
        grid-template-columns: 1fr;
    }

    .stats-row {
        grid-template-columns: 1fr;
    }

    .theme-grid {
        grid-template-columns: 1fr;
    }

    .danger-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>