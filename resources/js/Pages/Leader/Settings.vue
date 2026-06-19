<template>

    <Head title="Settings" />
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">

            <header class="settings-header">

                <div>
                    <h1>Settings</h1>
                    <p>
                        Manage security, notifications and account settings.
                    </p>
                </div>

                <button class="theme-btn" @click="theme.toggleTheme">
                    {{ theme.isDark ? "☀️" : "🌙" }}
                </button>

            </header>

            <div class="settings-tabs">

                <button :class="{ active: activeTab === 'profile' }" @click="activeTab = 'profile'">
                    👤 Profile
                </button>

                <button :class="{ active: activeTab === 'security' }" @click="activeTab = 'security'">
                    🔒 Security
                </button>

                <button :class="{ active: activeTab === 'notifications' }" @click="activeTab = 'notifications'">
                    🔔 Notifications
                </button>

            </div>

            <!-- PROFILE TAB -->
            <section v-if="activeTab === 'profile'" class="settings-card">

                <div class="card-header">
                    <div>
                        <h2>Profile Information</h2>
                        <p>Update your personal details.</p>
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
                        <input type="text" v-model="profile.name" />
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" v-model="profile.email" />
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" :value="profile.department" disabled />
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" :value="profile.role" disabled />
                    </div>

                </div>


            </section>

            <!-- WORKSPACE TAB -->
            <section v-if="activeTab === 'workspace'" class="settings-card">

                <div class="card-header">
                    <div>
                        <h2>Workspace Settings</h2>
                        <p>Configure your workspace details.</p>
                    </div>
                </div>

                <div class="workspace-banner" v-if="selectedWorkspace">
                    <div>
                        <h3>{{ selectedWorkspace.name }}</h3>
                        <p>{{ selectedWorkspace.description }}</p>
                    </div>
                    <span class="workspace-badge">
                        Active Workspace
                    </span>
                </div>

                <div class="settings-grid" v-if="selectedWorkspace">
                    <div class="form-group">
                        <label>Workspace Name</label>
                        <input type="text" v-model="selectedWorkspace.name" />
                    </div>

                    <div class="form-group">
                        <label>Workspace URL</label>
                        <input type="text" v-model="selectedWorkspace.slug" />
                    </div>
                </div>

                <div class="form-group full-width" v-if="selectedWorkspace">
                    <label>Description</label>
                    <textarea rows="5" v-model="selectedWorkspace.description"></textarea>
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
                    <button class="primary-btn" @click="updateWorkspace">
                        Update Workspace
                    </button>
                </div>

            </section>

            <!-- SECURITY TAB -->
            <section v-if="activeTab === 'security'" class="settings-card">

                <div class="card-header">
                    <div>
                        <h2>Security Settings</h2>
                        <p>Update your password and account security.</p>
                    </div>
                </div>

                <div class="password-form-block">
                    <div class="settings-grid">

                        <div class="form-group">
                            <label>Current Password</label>
                            <div class="password-wrapper">
                                <input :type="showCurrentPassword ? 'text' : 'password'"
                                    v-model="security.currentPassword" placeholder="Enter current password" @blur="
                                        validateCurrentPassword();
                                    handlePasswordBlur('currentPassword')
                                        " @input="validateCurrentPassword" />
                                <button type="button" class="eye-btn"
                                    @click="showCurrentPassword = !showCurrentPassword">
                                    {{ showCurrentPassword ? '👁️' : '👁️' }}
                                </button>
                            </div>
                            <p v-if="passwordErrors.currentPassword && passwordTouched.currentPassword"
                                class="error-text">
                                {{ passwordErrors.currentPassword }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <div class="password-wrapper">
                                <input :type="showNewPassword ? 'text' : 'password'" v-model="security.newPassword"
                                    placeholder="Enter new password" @blur="
                                        validateNewPassword();
                                    handlePasswordBlur('newPassword')
                                        " @input="validateNewPassword" />
                                <button type="button" class="eye-btn" @click="showNewPassword = !showNewPassword">
                                    {{ showNewPassword ? '👁️' : '👁️' }}
                                </button>
                            </div>
                            <p v-if="passwordErrors.newPassword && passwordTouched.newPassword" class="error-text">
                                {{ passwordErrors.newPassword }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label>Confirm Password</label>
                            <div class="password-wrapper">
                                <input :type="showConfirmPassword ? 'text' : 'password'"
                                    v-model="security.confirmPassword" placeholder="Confirm new password" @blur="
                                        validateConfirmPassword();
                                    handlePasswordBlur('confirmPassword')
                                        " @input="validateConfirmPassword" />
                                <button type="button" class="eye-btn"
                                    @click="showConfirmPassword = !showConfirmPassword">
                                    {{ showConfirmPassword ? '👁️' : '👁️' }}
                                </button>
                            </div>
                            <p v-if="passwordErrors.confirmPassword && passwordTouched.confirmPassword"
                                class="error-text">
                                {{ passwordErrors.confirmPassword }}
                            </p>
                        </div>

                        <!-- Action button placed perfectly to fill the grid space shown in image_bb4fad.png -->
                        <div class="form-group password-btn-alignment">
                            <button class="primary-btn" @click="updatePassword">
                                Update Security
                            </button>
                        </div>

                    </div>
                </div>

                <!-- TWO FACTOR AUTHENTICATION -->
                <div class="twofa-box">
                    <div class="twofa-header">
                        <h3>Two Factor Authentication (2FA)</h3>
                    </div>

                    <div class="twofa-content">
                        <div v-if="twoFA.enabled" class="twofa-enabled-container">
                            <p style="color: #4caf50; font-weight: bold; margin-bottom: 12px;">
                                🔒 Two-Factor Authentication is currently active on your account.
                            </p>

                            <div class="form-group inline-verification-input" style="margin-bottom: 15px;">
                                <label>Enter 6-digit code to confirm disabling</label>
                                <input v-model="twoFA.code" placeholder="123456" />
                            </div>

                            <button class="primary-btn danger-btn" @click="disable2FA" :disabled="twoFA.loading">
                                {{ twoFA.loading ? "Disabling..." : "Disable 2FA" }}
                            </button>
                        </div>

                        <div v-else>
                            <p class="twofa-desc">
                                Secure your account using Google Authenticator code validation protocols.
                            </p>

                            <button class="primary-btn setup-btn" @click="generate2FA" :disabled="twoFA.loading">
                                Generate QR Code
                            </button>

                            <div v-if="twoFA.qr" class="qr-box">
                                <p>Scan this QR in Google Authenticator:</p>
                                <qrcode-vue :value="twoFA.qr" :size="200" level="H" />

                                <p class="manual-code"><strong>Or enter manually:</strong></p>
                                <code>{{ twoFA.secret }}</code>

                                <div class="form-group inline-verification-input">
                                    <label>Enter 6-digit code</label>
                                    <input v-model="twoFA.code" placeholder="123456" />
                                </div>

                                <button class="primary-btn success-btn" @click="enable2FA" :disabled="twoFA.loading">
                                    Enable 2FA
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            <!-- NOTIFICATIONS TAB -->
            <section v-if="activeTab === 'notifications'" class="settings-card">

                <div class="card-header">
                    <div>
                        <h2>Notification Preferences</h2>
                        <p>Control how and when you receive updates.</p>
                    </div>
                </div>

                <div class="notification-list">

                    <div class="notify-item">
                        <div>
                            <h4>Email Notifications</h4>
                            <p>Receive updates by email.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" v-model="notifications.email">
                            <span></span>
                        </label>
                    </div>

                    <div class="notify-item">
                        <div>
                            <h4>Task Assignments</h4>
                            <p>Notify when tasks are assigned.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" v-model="notifications.tasks">
                            <span></span>
                        </label>
                    </div>

                    <div class="notify-item">
                        <div>
                            <h4>Project Updates</h4>
                            <p>Notify on project status changes.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" v-model="notifications.projects">
                            <span></span>
                        </label>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="primary-btn" @click="saveNotificationPreferences">
                        Save Preferences
                    </button>
                </div>

            </section>

        </main>

    </div>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted
} from "vue";
import axios from "axios";
import Sidebar from "./Sidebar.vue";
import { useThemeStore } from "../../stores/theme.js";
import { useToast } from "vue-toastification";
import QrcodeVue from 'qrcode.vue';
import { Head } from '@inertiajs/vue3';

const toast = useToast();
const theme = useThemeStore();

const activeTab = ref("profile");
const workspaces = ref([]);
const selectedWorkspace = ref(null);

const fetchWorkspaces = async () => {
    try {
        const res = await axios.get("/workspaces");
        workspaces.value = res.data;

        if (workspaces.value.length > 0) {
            selectedWorkspace.value = workspaces.value[0];
        }
    } catch (err) {
        console.error("Failed to fetch workspaces:", err);
    }
};

const profile = reactive({
    id: null,
    name: "",
    email: "",
    department: "",
    role: "",
});

const security = reactive({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
    twoFactor: false,
    loginAlerts: true,
});

const fetchProfile = async () => {
    try {
        const [userRes, memberRes] = await Promise.all([
            axios.get("/user/profile"),
            axios.get("/member/me"),
        ]);

        const user = userRes.data;
        const member = memberRes.data;

        profile.id = user.id;
        profile.name = user.name;
        profile.email = user.email;

        if (user.notification_preferences) {
            notifications.email = !!user.notification_preferences.email;
            notifications.tasks = !!user.notification_preferences.tasks;
            notifications.projects = !!user.notification_preferences.projects;
            notifications.reports = !!user.notification_preferences.reports;

            originalNotifications.email = !!user.notification_preferences.email;
            originalNotifications.tasks = !!user.notification_preferences.tasks;
            originalNotifications.projects = !!user.notification_preferences.projects;
            originalNotifications.reports = !!user.notification_preferences.reports;
        }

        twoFA.enabled =
            user.two_factor_enabled === 1 ||
            user.two_factor_enabled === true ||
            user.two_factor_enabled === "1";

        const data = member?.member ?? member;

        profile.role = data?.role || "N/A";
        profile.department = data?.department || "N/A";
    } catch (error) {
        console.error("fetchProfile error:", error);
    }
};

const updateProfile = async () => {
    try {
        await axios.put("/user/profile", profile);
        toast.success("Profile updated successfully");
    } catch (error) {
        console.error(error);
        toast.error("Failed to update profile");
    }
};

const validateCurrentPassword = () => {
    if (!security.currentPassword) {
        passwordErrors.currentPassword = "Current password is required";
    } else {
        passwordErrors.currentPassword = "";
    }
};

const validateNewPassword = () => {
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

    if (!security.newPassword) {
        passwordErrors.newPassword = "Password is required";
    } else if (security.newPassword.length < 8) {
        passwordErrors.newPassword = "Password must be at least 8 characters";
    } else if (!regex.test(security.newPassword)) {
        passwordErrors.newPassword = "Must include uppercase, lowercase, number & special character";
    } else {
        passwordErrors.newPassword = "";
    }
    validateConfirmPassword();
};

const validateConfirmPassword = () => {
    if (!security.confirmPassword) {
        passwordErrors.confirmPassword = "Confirm password is required";
    } else if (security.newPassword !== security.confirmPassword) {
        passwordErrors.confirmPassword = "Passwords do not match";
    } else {
        passwordErrors.confirmPassword = "";
    }
};

const handlePasswordBlur = (field) => {
    passwordTouched[field] = true;
};

const passwordErrors = reactive({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
});

const passwordTouched = reactive({
    currentPassword: false,
    newPassword: false,
    confirmPassword: false,
});

const updatePassword = async () => {
    passwordTouched.currentPassword = true;
    passwordTouched.newPassword = true;
    passwordTouched.confirmPassword = true;

    validateCurrentPassword();
    validateNewPassword();
    validateConfirmPassword();

    if (passwordErrors.currentPassword || passwordErrors.newPassword || passwordErrors.confirmPassword) {
        return;
    }

    try {
        const response = await axios.post("/user/change-password", {
            current_password: security.currentPassword,
            password: security.newPassword,
            password_confirmation: security.confirmPassword
        });

        security.currentPassword = "";
        security.newPassword = "";
        security.confirmPassword = "";

        passwordErrors.currentPassword = "";
        passwordErrors.newPassword = "";
        passwordErrors.confirmPassword = "";

        passwordTouched.currentPassword = false;
        passwordTouched.newPassword = false;
        passwordTouched.confirmPassword = false;

        toast.success(response.data.message || "Password updated successfully");
    } catch (error) {
        if (error.response?.data?.message === "Current password is incorrect") {
            passwordErrors.currentPassword = "Current password does not match";
            passwordTouched.currentPassword = true;
            return;
        }
        console.error(error);
    }
};

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const notifications = reactive({
    email: true,
    tasks: true,
    projects: true,
    reports: false,
});
const originalNotifications = reactive({
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

const twoFA = reactive({
    qr: null,
    secret: null,
    code: "",
    enabled: false,
    loading: false
});

const generate2FA = async () => {
    try {
        twoFA.loading = true;
        const res = await axios.get("/leader/2fa/generate");
        twoFA.qr = res.data.qr;
        twoFA.secret = res.data.secret;
        toast.success("QR generated. Scan it in Google Authenticator");
    } catch (err) {
        console.error(err);
        toast.error("Failed to generate QR");
    } finally {
        twoFA.loading = false;
    }
};

const enable2FA = async () => {
    if (!twoFA.code) {
        toast.error("Enter 6-digit code");
        return;
    }

    try {
        twoFA.loading = true;
        await axios.post("/leader/2fa/enable", { code: twoFA.code });
        twoFA.enabled = true;
        toast.success("2FA enabled successfully");
    } catch (err) {
        toast.error(err.response?.data?.message || "Invalid code");
    } finally {
        twoFA.loading = false;
    }
};

const disable2FA = async () => {
    if (!twoFA.code) {
        toast.error("Please enter your 6-digit verification code to disable 2FA");
        return;
    }

    try {
        twoFA.loading = true;
        await axios.post("/leader/2fa/disable", { code: twoFA.code });

        twoFA.enabled = false;
        twoFA.qr = null;
        twoFA.secret = null;
        twoFA.code = "";

        toast.success("2FA has been disabled successfully");
    } catch (err) {
        console.error(err);
        toast.error(err.response?.data?.message || "Invalid verification code. Failed to disable 2FA");
    } finally {
        twoFA.loading = false;
    }
};

const saveNotificationPreferences = async () => {
    const hasChanges = 
        notifications.email !== originalNotifications.email ||
        notifications.tasks !== originalNotifications.tasks ||
        notifications.projects !== originalNotifications.projects ||
        notifications.reports !== originalNotifications.reports;

    if (!hasChanges) {
        toast.warning("Nothing to update on saving the preferences");
        return;
    }

    try {
        await axios.put("/user/notification-preferences", {
            email: notifications.email,
            tasks: notifications.tasks,
            projects: notifications.projects,
            reports: notifications.reports
        });

        originalNotifications.email = notifications.email;
        originalNotifications.tasks = notifications.tasks;
        originalNotifications.projects = notifications.projects;
        originalNotifications.reports = notifications.reports;

        toast.success("Preferences saved successfully");
    } catch (error) {
        console.error("Failed to save preferences:", error);
        toast.error("Failed to update preferences");
    }
};

const checkForProjectAlerts = async () => {
    try {
        const res = await axios.get("/user/unread-alerts");
        if (res.data.alerts && res.data.alerts.length > 0) {
            res.data.alerts.forEach(alert => {
                toast.info(alert.message);
            });
        }
    } catch (err) {
        console.error("Alert check failed:", err);
    }
};

onMounted(() => {
    fetchProfile();
    fetchWorkspaces();
    checkForProjectAlerts();
    setInterval(checkForProjectAlerts, 30000);
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
    padding: 12px 20px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    background: var(--card);
    color: var(--text);
    transition: 0.2s ease-in-out;
}

.settings-tabs button:hover {
    background: var(--border);
    transform: translateY(-1px);
}

.settings-tabs button.active {
    background: #06b6d4;
    color: #fff;
}

.settings-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 24px;
}

.card-header {
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 16px;
}

.card-header h2 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--text);
}

.card-header p {
    color: var(--subtext);
    font-size: 14px;
}

.card-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
}

.card-footer-inline {
    margin-top: 24px;
    display: flex;
    justify-content: flex-start;
}

.avatar-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.avatar-circle {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    background: #06b6d4;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 24px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
}

.avatar-section h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
}

.avatar-section span {
    font-size: 14px;
    color: var(--subtext);
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.password-form-block {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 30px;
}

/* Aligns button inline with the Confirm Password field */
.password-btn-alignment {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
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
    font-size: 14px;
    font-weight: 500;
    color: var(--text);
}

.form-group input,
.form-group textarea {
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    outline: none;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #06b6d4;
}

.form-group input:disabled {
    background: var(--border);
    opacity: 0.6;
    cursor: not-allowed;
}

.form-group textarea {
    resize: none;
}

.primary-btn {
    background: #06b6d4;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: opacity 0.2s;
}

.primary-btn:hover {
    opacity: 0.9;
}

.danger-btn {
    background-color: #ef4444 !important;
}

.success-btn {
    background-color: #22c55e !important;
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
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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
    font-size: 14px;
}

.stat-card span {
    font-size: 28px;
    font-weight: bold;
    color: var(--text);
}

.twofa-box {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--bg);
    overflow: hidden;
}

.twofa-header {
    background: rgba(6, 182, 212, 0.05);
    padding: 16px 24px;
    border-bottom: 1px solid var(--border);
}

.twofa-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
}

.twofa-content {
    padding: 24px;
}

.twofa-desc {
    color: var(--subtext);
    font-size: 14px;
    margin-bottom: 16px;
}

.setup-btn {
    background: #475569;
}

.qr-box {
    margin-top: 20px;
    padding: 20px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-width: 400px;
}

.manual-code {
    margin-top: 8px;
}

.qr-box code {
    background: var(--bg);
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid var(--border);
    font-family: monospace;
    font-size: 14px;
    color: #ec4899;
}

.inline-verification-input {
    margin: 8px 0;
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
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notify-item h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 4px;
}

.notify-item p {
    font-size: 13px;
    color: var(--subtext);
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.switch input {
    display: none;
}

.switch span {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 30px;
    cursor: pointer;
    transition: .2s;
}

.switch span::before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: .2s;
}

.switch input:checked+span {
    background: #06b6d4;
}

.switch input:checked+span::before {
    transform: translateX(24px);
}

@media (max-width: 992px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }

    .stats-row {
        grid-template-columns: 1fr;
    }

    .password-btn-alignment {
        justify-content: flex-start;
        margin-top: 10px;
    }
}

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding-right: 46px;
}

.eye-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 16px;
    opacity: 0.7;
}

.eye-btn:hover {
    opacity: 1;
}

.error-text {
    color: #ef4444;
    margin-top: 6px;
    font-size: 13px;
}

.twofa-box {
    margin-top: 20px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--card);
}

.qr-box {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.qr-box img {
    width: 180px;
    height: 180px;
    border: 1px solid var(--border);
    border-radius: 8px;
}
</style>
