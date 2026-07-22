<template>

    <Head title="Settings" />
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">
            <div class="content-wrapper">

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
                        <div class="avatar-wrapper" @click="triggerAvatarUpload" title="Change profile photo">
                            <img
                                v-if="avatarPreview || profile.avatar_url"
                                :src="avatarPreview || profile.avatar_url"
                                class="avatar-image"
                                alt="Profile photo"
                            />
                            <div v-else class="avatar-circle">
                                {{ userInitials }}
                            </div>

                            <div class="avatar-edit-overlay">
                                <span>📷</span>
                            </div>
                        </div>

                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            class="hidden-file-input"
                            @change="handleAvatarChange"
                        />

                        <div>
                            <h3>{{ profile.name }}</h3>
                            <span>{{ profile.email }}</span>
                            <button type="button" class="change-photo-link" @click="triggerAvatarUpload">
                                Change photo
                            </button>
                            <button
                                v-if="avatarPreview"
                                type="button"
                                class="cancel-photo-link"
                                @click="cancelAvatarChange"
                            >
                                Undo
                            </button>
                        </div>
                    </div>

                    <div class="settings-grid">

                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" v-model="profile.name" />
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" :value="profile.email" disabled />
                            <small class="field-hint">Contact your admin to change your email</small>
                        </div>

                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" :value="profile.department" disabled />
                            <small class="field-hint">Set by your workspace admin</small>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" :value="profile.role" disabled />
                            <small class="field-hint">Set by your workspace admin</small>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button class="primary-btn" @click="updateProfile" :disabled="savingProfile">
                            {{ savingProfile ? "Saving..." : "Save Changes" }}
                        </button>
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
                                            " @input="
                                            validateCurrentPassword();
                                        if (passwordTouched.newPassword) validateNewPassword();
                                            " />
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

                            <div class="form-group password-btn-alignment">
                                <button class="primary-btn" @click="updatePassword" :disabled="updatingPassword">
                                    {{ updatingPassword ? "Updating..." : "Update Security" }}
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
                                <p class="twofa-active-text">
                                    🔒 Two-Factor Authentication is currently active on your account.
                                </p>

                                <div class="form-group inline-verification-input">
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

            </div>
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
    avatar_url: null,
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
        profile.avatar_url = user.avatar_url || user.avatar || null;

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

/* ---------------- Avatar upload ---------------- */
const avatarInput = ref(null);
const avatarFile = ref(null);
const avatarPreview = ref(null);
const savingProfile = ref(false);

const triggerAvatarUpload = () => {
    avatarInput.value?.click();
};

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith("image/")) {
        toast.error("Please choose an image file");
        e.target.value = "";
        return;
    }

    const maxSizeBytes = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSizeBytes) {
        toast.error("Image must be smaller than 5MB");
        e.target.value = "";
        return;
    }

    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }

    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const cancelAvatarChange = () => {
    if (avatarPreview.value) {
        URL.revokeObjectURL(avatarPreview.value);
    }
    avatarFile.value = null;
    avatarPreview.value = null;
    if (avatarInput.value) avatarInput.value.value = "";
};

const updateProfile = async () => {
    savingProfile.value = true;

    try {
        const formData = new FormData();
        formData.append("name", profile.name);
        formData.append("email", profile.email);
        formData.append("_method", "PUT"); // Laravel method-spoofing for multipart PUT

        if (avatarFile.value) {
            formData.append("avatar", avatarFile.value);
        }

        const res = await axios.post("/user/profile", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        if (res.data?.avatar_url) {
            profile.avatar_url = res.data.avatar_url;
        }

        cancelAvatarChange();
        toast.success("Profile updated successfully");
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || "Failed to update profile");
    } finally {
        savingProfile.value = false;
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
    } else if (security.currentPassword && security.newPassword === security.currentPassword) {
        passwordErrors.newPassword = "New password must be different from your current password";
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

const updatingPassword = ref(false);

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

    updatingPassword.value = true;

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
        const serverMessage = error.response?.data?.message || "";

        if (serverMessage === "Current password is incorrect") {
            passwordErrors.currentPassword = "Current password does not match";
            passwordTouched.currentPassword = true;
        } else if (/same as|must be different|reuse/i.test(serverMessage)) {
            // Covers a backend rule like Laravel's `different:current_password`
            passwordErrors.newPassword = "New password must be different from your current password";
            passwordTouched.newPassword = true;
        } else {
            console.error(error);
            toast.error(serverMessage || "Failed to update password");
        }
    } finally {
        updatingPassword.value = false;
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

.dashboard.theme-dark {
    --dashboard-bg: #222736;
    --panel-bg: #2a2f42;
    --card-inner-bg: #262b3d;
    --card-inner-hover: #313749;
    --input-element-bg: #323a4f;
    --border-subtle: rgba(255, 255, 255, 0.07);
    --border-deep: rgba(255, 255, 255, 0.12);
    --text-main: #e4e6ef;
    --text-header: #f6f7fb;
    --text-muted: #8590a6;
    --shadow-cards: rgba(0, 0, 0, 0.28);
    --accent: #556ee6;
    --accent-soft: rgba(85, 110, 230, 0.14);
    --c-green: #34c38f;
    --c-red: #f46a6a;
    --c-amber: #f1b44c;
    --c-violet: #8b6ee8;
    --c-slate: #6c7893;
}

.dashboard.theme-light {
    --dashboard-bg: #eef0f7;
    --panel-bg: #ffffff;
    --card-inner-bg: #f7f8fb;
    --card-inner-hover: #eef0f6;
    --input-element-bg: #f2f3f8;
    --border-subtle: rgba(33, 37, 61, 0.07);
    --border-deep: rgba(33, 37, 61, 0.1);
    --text-main: #33374d;
    --text-header: #22263d;
    --text-muted: #878ea3;
    --shadow-cards: rgba(56, 65, 109, 0.07);
    --accent: #556ee6;
    --accent-soft: rgba(85, 110, 230, 0.08);
    --c-green: #34c38f;
    --c-red: #e05555;
    --c-amber: #f1b44c;
    --c-violet: #8b6ee8;
    --c-slate: #8891a5;
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
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
    padding: 28px 36px 56px;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.settings-header h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 3px 0;
    letter-spacing: -0.2px;
    color: var(--text-header);
}

.settings-header p {
    margin: 0;
    color: var(--text-muted);
    font-size: 13px;
}

.theme-btn {
    width: 38px;
    height: 38px;
    border-radius: 9px;
    border: 1px solid var(--border-subtle);
    cursor: pointer;
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.theme-btn:hover {
    background: var(--card-inner-hover);
    border-color: var(--border-deep);
}


.settings-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 22px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 5px;
    width: fit-content;
}

.settings-tabs button {
    border: none;
    padding: 9px 16px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    background: transparent;
    color: var(--text-muted);
    transition: all 0.15s ease;
}

.settings-tabs button:hover {
    color: var(--text-main);
}

.settings-tabs button.active {
    background: var(--accent);
    color: #ffffff;
}


.settings-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 26px;
    margin-bottom: 20px;
    box-shadow: 0 2px 6px var(--shadow-cards);
}

.card-header {
    margin-bottom: 22px;
    border-bottom: 1px solid var(--border-subtle);
    padding-bottom: 16px;
}

.card-header h2 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
    color: var(--text-header);
}

.card-header p {
    color: var(--text-muted);
    font-size: 13px;
    margin: 0;
}

.card-footer {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}

/* ==========================================================================
   AVATAR / PROFILE PHOTO
   ========================================================================== */
.avatar-section {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 28px;
}

.avatar-wrapper {
    position: relative;
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 50%;
    cursor: pointer;
}

.avatar-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--accent);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    font-weight: 700;
    flex-shrink: 0;
}

.avatar-image {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    border: 1px solid var(--border-subtle);
}

.avatar-edit-overlay {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.45);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    opacity: 0;
    transition: opacity 0.15s ease;
}

.avatar-wrapper:hover .avatar-edit-overlay {
    opacity: 1;
}

.hidden-file-input {
    display: none;
}

.change-photo-link {
    display: inline-block;
    margin-top: 6px;
    margin-right: 12px;
    background: none;
    border: none;
    padding: 0;
    color: var(--accent);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

.change-photo-link:hover {
    text-decoration: underline;
}

.cancel-photo-link {
    display: inline-block;
    margin-top: 6px;
    background: none;
    border: none;
    padding: 0;
    color: var(--text-muted);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}

.cancel-photo-link:hover {
    color: var(--c-red);
}

.avatar-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-header);
    margin: 0 0 3px 0;
}

.avatar-section span {
    font-size: 13px;
    color: var(--text-muted);
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
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
    margin-bottom: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
}

.form-group input,
.form-group textarea {
    padding: 10px 13px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    outline: none;
    font-size: 13.5px;
    transition: border-color 0.15s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.form-group input:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.field-hint {
    margin-top: 6px;
    font-size: 11px;
    color: var(--text-muted);
}

.form-group textarea {
    resize: none;
}

.primary-btn {
    background: var(--accent);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13.5px;
    transition: opacity 0.15s ease;
}

.primary-btn:hover {
    opacity: 0.9;
}

.primary-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.danger-btn {
    background: var(--c-red) !important;
}

.success-btn {
    background: var(--c-green) !important;
}

.setup-btn {
    background: var(--c-slate) !important;
}

.workspace-banner {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    padding: 18px 20px;
    border-radius: 10px;
    margin-bottom: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.workspace-banner h3 {
    margin: 0 0 4px 0;
    font-size: 14.5px;
    color: var(--text-header);
}

.workspace-banner p {
    margin: 0;
    font-size: 12.5px;
    color: var(--text-muted);
}

.workspace-badge {
    background: var(--c-green);
    color: white;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 20px;
}

.stat-card {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    padding: 16px 18px;
    border-radius: 10px;
}

.stat-card h4 {
    color: var(--text-muted);
    margin: 0 0 8px 0;
    font-size: 12px;
    font-weight: 600;
}

.stat-card span {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-header);
}


.password-form-block {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 24px;
}

.password-btn-alignment {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
}

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding-right: 42px;
}

.eye-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 14px;
    opacity: 0.65;
}

.eye-btn:hover {
    opacity: 1;
}

.error-text {
    color: var(--c-red);
    margin-top: 6px;
    font-size: 12px;
}

.twofa-box {
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    background: var(--card-inner-bg);
    overflow: hidden;
}

.twofa-header {
    background: var(--accent-soft);
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-subtle);
}

.twofa-header h3 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-header);
    margin: 0;
}

.twofa-content {
    padding: 20px;
}

.twofa-desc {
    color: var(--text-muted);
    font-size: 13px;
    margin-bottom: 16px;
}

.twofa-active-text {
    color: var(--c-green);
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 12px;
}

.qr-box {
    margin-top: 18px;
    padding: 18px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 380px;
}

.qr-box p {
    margin: 0;
    font-size: 13px;
    color: var(--text-muted);
}

.manual-code {
    margin-top: 4px;
}

.manual-code strong {
    color: var(--text-main);
    font-size: 13px;
}

.qr-box code {
    background: var(--card-inner-bg);
    padding: 7px 11px;
    border-radius: 6px;
    border: 1px solid var(--border-subtle);
    font-family: monospace;
    font-size: 13px;
    color: var(--c-violet);
}

.inline-verification-input {
    margin: 6px 0;
}

.notification-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notify-item {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notify-item h4 {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-main);
    margin: 0 0 3px 0;
}

.notify-item p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

.switch {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 23px;
    flex-shrink: 0;
}

.switch input {
    display: none;
}

.switch span {
    position: absolute;
    inset: 0;
    background: var(--border-deep);
    border-radius: 30px;
    cursor: pointer;
    transition: 0.2s;
}

.switch span::before {
    content: "";
    position: absolute;
    width: 17px;
    height: 17px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.2s;
}

.switch input:checked + span {
    background: var(--accent);
}

.switch input:checked + span::before {
    transform: translateX(19px);
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
        margin-top: 8px;
    }
}
</style>