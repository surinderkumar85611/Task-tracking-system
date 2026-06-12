<template>
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

            <section v-if="activeTab === 'profile'" class="settings-card">

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

                <!-- <div class="card-footer">
                    <button class="primary-btn" @click="updateProfile">
                        Save Changes
                    </button>
                </div> -->

            </section>

            <section v-if="activeTab === 'workspace'" class="settings-card">

                <div class="card-header">
                    <div>
                        <h2>Workspace Settings</h2>
                        <p>
                            Configure your workspace details.
                        </p>
                    </div>
                </div>

                <!-- WORKSPACE DISPLAY -->
                <div class="workspace-banner" v-if="selectedWorkspace">

                    <div>
                        <h3>{{ selectedWorkspace.name }}</h3>

                        <p>
                            {{ selectedWorkspace.description }}
                        </p>
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
            <section v-if="activeTab === 'security'" class="settings-card">

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

                        <div class="password-wrapper">
                            <input :type="showCurrentPassword ? 'text' : 'password'" v-model="security.currentPassword"
                                placeholder="Enter current password" @blur="
                                    validateCurrentPassword();
                                handlePasswordBlur('currentPassword')
                                    " @input="validateCurrentPassword" />

                            <button type="button" class="eye-btn" @click="showCurrentPassword = !showCurrentPassword">
                                {{ showCurrentPassword ? '👁️' : '👁️' }}
                            </button>

                        </div>

                        <p v-if="
                            passwordErrors.currentPassword &&
                            passwordTouched.currentPassword
                        " class="error-text">
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

                        <p v-if="
                            passwordErrors.newPassword &&
                            passwordTouched.newPassword
                        " class="error-text">
                            {{ passwordErrors.newPassword }}
                        </p>

                    </div>

                    <div class="form-group">

                        <label>Confirm Password</label>

                        <div class="password-wrapper">

                            <input :type="showConfirmPassword ? 'text' : 'password'" v-model="security.confirmPassword"
                                placeholder="Confirm new password" @blur="
                                    validateConfirmPassword();
                                handlePasswordBlur('confirmPassword')
                                    " @input="validateConfirmPassword" />
                            <button type="button" class="eye-btn" @click="showConfirmPassword = !showConfirmPassword">
                                {{ showConfirmPassword ? '👁️' : '👁️' }}
                            </button>

                        </div>

                        <p v-if="
                            passwordErrors.confirmPassword &&
                            passwordTouched.confirmPassword
                        " class="error-text">
                            {{ passwordErrors.confirmPassword }}
                        </p>
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
                            <input type="checkbox" v-model="security.twoFactor">
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
                            <input type="checkbox" v-model="security.loginAlerts">
                            <span></span>
                        </label>

                    </div>
                    
                    <!-- TWO FACTOR AUTHENTICATION -->
<div class="twofa-box">

    <h3>Two Factor Authentication (2FA)</h3>

    <p>
        Secure your account using Google Authenticator.
    </p>

    <!-- STEP 1 BUTTON -->
    <button class="primary-btn" @click="generate2FA">
        Generate QR Code
    </button>

    <!-- SHOW QR -->
    <div v-if="twoFA.qr" class="qr-box">

        <p>Scan this QR in Google Authenticator:</p>

       <qrcode-vue :value="twoFA.qr" :size="200" level="H" />
        <p><strong>Or enter manually:</strong></p>
        <code>{{ twoFA.secret }}</code>

        <div class="form-group">
            <label>Enter 6-digit code</label>

            <input v-model="twoFA.code" placeholder="123456" />
        </div>

        <button class="primary-btn" @click="enable2FA">
            Enable 2FA
        </button>

    </div>

</div>
                </div>

                <div class="card-footer">
                    <button class="primary-btn" @click="updatePassword">
                        Update Security
                    </button>
                </div>

            </section>

            <section v-if="activeTab === 'notifications'" class="settings-card">

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
                            <input type="checkbox" v-model="notifications.email">
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
                            <input type="checkbox" v-model="notifications.tasks">
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
                            <input type="checkbox" v-model="notifications.projects">
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
                            <input type="checkbox" v-model="notifications.reports">
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

            

        </main>

    </div>
</template>
<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted,
    watch
} from "vue";
import axios from "axios";
import Sidebar from "./Sidebar.vue";
import {
    useThemeStore
}
    from "../../stores/theme.js";

import { useToast } from "vue-toastification";
import QrcodeVue from 'qrcode.vue';

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

        const data = member?.member ?? member;

        profile.role = data?.role || "N/A";
        profile.department = data?.department || "N/A";
    } catch (error) {
        console.error("fetchProfile error:", error);
    }
};
const updateProfile = async () => {

    try {

        await axios.put(
            "/user/profile",
            profile
        );

        alert("Profile updated");

    } catch (error) {

        console.error(error);

    }

};

const validateCurrentPassword = () => {
    if (!security.currentPassword) {
        passwordErrors.currentPassword =
            "Current password is required";
    } else {
        passwordErrors.currentPassword = "";
    }
};

const validateNewPassword = () => {

    const regex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

    if (!security.newPassword) {

        passwordErrors.newPassword =
            "Password is required";

    } else if (security.newPassword.length < 8) {

        passwordErrors.newPassword =
            "Password must be at least 8 characters";

    } else if (!regex.test(security.newPassword)) {

        passwordErrors.newPassword =
            "Must include uppercase, lowercase, number & special character";

    } else {

        passwordErrors.newPassword = "";
    }

    validateConfirmPassword();
};

const validateConfirmPassword = () => {

    if (!security.confirmPassword) {

        passwordErrors.confirmPassword =
            "Confirm password is required";

    } else if (
        security.newPassword !==
        security.confirmPassword
    ) {

        passwordErrors.confirmPassword =
            "Passwords do not match";

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

    if (
        passwordErrors.currentPassword ||
        passwordErrors.newPassword ||
        passwordErrors.confirmPassword
    ) {
        return;
    }

    try {

        const response = await axios.post(
            "/user/change-password",
            {
                current_password:
                    security.currentPassword,

                password:
                    security.newPassword,

                password_confirmation:
                    security.confirmPassword
            }
        );

        security.currentPassword = "";
        security.newPassword = "";
        security.confirmPassword = "";

        passwordErrors.currentPassword = "";
        passwordErrors.newPassword = "";
        passwordErrors.confirmPassword = "";

        passwordTouched.currentPassword = false;
        passwordTouched.newPassword = false;
        passwordTouched.confirmPassword = false;

        toast.success(
            response.data.message ||
            "Password updated successfully"
        );

    } catch (error) {

        if (
            error.response?.data?.message ===
            "Current password is incorrect"
        ) {

            passwordErrors.currentPassword =
                "Current password does not match";

            passwordTouched.currentPassword =
                true;

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

onMounted(() => {
    fetchProfile();
    fetchWorkspaces();
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

        console.log("2FA Response:", res.data);

        twoFA.qr = res.data.qr;
        twoFA.secret = res.data.secret;

        console.log("QR URL:", twoFA.qr);

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

        await axios.post("/leader/2fa/enable", {
            code: twoFA.code
        });

        twoFA.enabled = true;

        toast.success("2FA enabled successfully");

    } catch (err) {
        toast.error(err.response?.data?.message || "Invalid code");
    } finally {
        twoFA.loading = false;
    }
};
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

.switch input:checked+span {
    background: #06b6d4;
}

.switch input:checked+span::before {
    transform: translateX(26px);
}


.theme-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
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

    
}

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    width: 100%;
    padding-right: 50px;
}

.eye-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 16px;
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