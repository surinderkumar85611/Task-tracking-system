<template>

    <Head title="Settings" />
    <div class="dashboard" :class="isDark ? 'theme-dark' : 'theme-light'">

        <SuperAdminSidebar />

        <main class="main-content">
            <div class="content-wrapper">

                <header class="settings-header">
                    <div>
                        <h1>Settings</h1>
                        <p>Manage your Super Admin account security and notifications.</p>
                    </div>

                    <button class="theme-btn" @click="isDark = !isDark">
                        {{ isDark ? "☀️" : "🌙" }}
                    </button>
                </header>

                <div class="settings-tabs">
                    <button :class="{ active: activeTab === 'profile' }" @click="activeTab = 'profile'">👤 Profile</button>
                    <button :class="{ active: activeTab === 'security' }" @click="activeTab = 'security'">🔒 Security</button>
                    <button :class="{ active: activeTab === 'notifications' }" @click="activeTab = 'notifications'">🔔 Notifications</button>
                </div>

                <!-- PROFILE TAB -->
                <section v-if="activeTab === 'profile'" class="settings-card">
                    <div class="card-header">
                        <div><h2>Profile Information</h2><p>Update your personal details.</p></div>
                    </div>

                    <div class="avatar-section">
                        <div class="avatar-wrapper" @click="triggerAvatarUpload" title="Change profile photo">
                            <img v-if="avatarPreview || profile.avatar_url" :src="avatarPreview || profile.avatar_url" class="avatar-image" alt="Profile photo" />
                            <div v-else class="avatar-circle">{{ userInitials }}</div>
                            <div class="avatar-edit-overlay"><span>📷</span></div>
                        </div>

                        <input ref="avatarInput" type="file" accept="image/png, image/jpeg, image/webp" class="hidden-file-input" @change="handleAvatarChange" />

                        <div>
                            <h3>{{ profile.name }}</h3>
                            <span>{{ profile.email }}</span>
                            <div class="photo-actions">
                                <button type="button" class="change-photo-link" @click="triggerAvatarUpload">Change photo</button>
                                <button v-if="avatarPreview" type="button" class="cancel-photo-link" @click="cancelAvatarChange">Undo</button>
                            </div>
                        </div>
                    </div>

                    <div class="settings-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" v-model="profile.name" />
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" :value="profile.email" disabled readonly />
                            <small class="field-hint">Super Admin email cannot be changed here</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button class="primary-btn" @click="updateProfile" :disabled="savingProfile">
                            {{ savingProfile ? "Saving..." : "Save Changes" }}
                        </button>
                    </div>
                </section>

                <!-- SECURITY TAB -->
                <section v-if="activeTab === 'security'" class="settings-card">
                    <div class="card-header">
                        <div><h2>Security Settings</h2><p>Update your password and account security.</p></div>
                    </div>

                    <div class="password-form-block">
                        <div class="settings-grid">
                            <div class="form-group">
                                <label>Current Password</label>
                                <div class="password-wrapper">
                                    <input :type="showCurrentPassword ? 'text' : 'password'" v-model="security.currentPassword"
                                        placeholder="Enter current password"
                                        @blur="validateCurrentPassword(); handlePasswordBlur('currentPassword')"
                                        @input="validateCurrentPassword(); if (passwordTouched.newPassword) validateNewPassword();" />
                                    <button type="button" class="eye-btn" @click="showCurrentPassword = !showCurrentPassword">👁️</button>
                                </div>
                                <p v-if="passwordErrors.currentPassword && passwordTouched.currentPassword" class="error-text">{{ passwordErrors.currentPassword }}</p>
                            </div>

                            <div class="form-group">
                                <label>New Password</label>
                                <div class="password-wrapper">
                                    <input :type="showNewPassword ? 'text' : 'password'" v-model="security.newPassword"
                                        placeholder="Enter new password"
                                        @blur="validateNewPassword(); handlePasswordBlur('newPassword')"
                                        @input="validateNewPassword" />
                                    <button type="button" class="eye-btn" @click="showNewPassword = !showNewPassword">👁️</button>
                                </div>
                                <p v-if="passwordErrors.newPassword && passwordTouched.newPassword" class="error-text">{{ passwordErrors.newPassword }}</p>
                            </div>

                            <div class="form-group">
                                <label>Confirm Password</label>
                                <div class="password-wrapper">
                                    <input :type="showConfirmPassword ? 'text' : 'password'" v-model="security.confirmPassword"
                                        placeholder="Confirm new password"
                                        @blur="validateConfirmPassword(); handlePasswordBlur('confirmPassword')"
                                        @input="validateConfirmPassword" />
                                    <button type="button" class="eye-btn" @click="showConfirmPassword = !showConfirmPassword">👁️</button>
                                </div>
                                <p v-if="passwordErrors.confirmPassword && passwordTouched.confirmPassword" class="error-text">{{ passwordErrors.confirmPassword }}</p>
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
                        <div class="twofa-header"><h3>Two Factor Authentication (2FA)</h3></div>

                        <div class="twofa-content">
                            <div v-if="twoFA.enabled" class="twofa-enabled-container">
                                <p class="twofa-active-text">🔒 Two-Factor Authentication is currently active on your account.</p>

                                <div class="form-group inline-verification-input">
                                    <label>Enter 6-digit code to confirm disabling</label>
                                    <input v-model="twoFA.code" placeholder="123456" />
                                </div>

                                <button class="primary-btn danger-btn" @click="disable2FA" :disabled="twoFA.loading">
                                    {{ twoFA.loading ? "Disabling..." : "Disable 2FA" }}
                                </button>
                            </div>

                            <div v-else>
                                <p class="twofa-desc">Secure your account using Google Authenticator code validation.</p>

                                <button class="primary-btn setup-btn" @click="generate2FA" :disabled="twoFA.loading">Generate QR Code</button>

                                <div v-if="twoFA.qr" class="qr-box">
                                    <p>Scan this QR in Google Authenticator:</p>
                                    <qrcode-vue :value="twoFA.qr" :size="200" level="H" />

                                    <p class="manual-code"><strong>Or enter manually:</strong></p>
                                    <code>{{ twoFA.secret }}</code>

                                    <div class="form-group inline-verification-input">
                                        <label>Enter 6-digit code</label>
                                        <input v-model="twoFA.code" placeholder="123456" />
                                    </div>

                                    <button class="primary-btn success-btn" @click="enable2FA" :disabled="twoFA.loading">Enable 2FA</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- NOTIFICATIONS TAB -->
                <section v-if="activeTab === 'notifications'" class="settings-card">
                    <div class="card-header">
                        <div><h2>Notification Preferences</h2><p>Control how and when you receive updates.</p></div>
                    </div>

                    <div class="notification-list">
                        <div class="notify-item">
                            <div><h4>Email Notifications</h4><p>Receive updates by email.</p></div>
                            <label class="switch"><input type="checkbox" v-model="notifications.email"><span></span></label>
                        </div>
                        <div class="notify-item">
                            <div><h4>New Workspace Activity</h4><p>Notify when a new workspace or team is created.</p></div>
                            <label class="switch"><input type="checkbox" v-model="notifications.workspaceActivity"><span></span></label>
                        </div>
                        <div class="notify-item">
                            <div><h4>Overdue Projects</h4><p>Notify when a project across any workspace goes overdue.</p></div>
                            <label class="switch"><input type="checkbox" v-model="notifications.overdue"><span></span></label>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button class="primary-btn" @click="saveNotificationPreferences">Save Preferences</button>
                    </div>
                </section>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";
import QrcodeVue from 'qrcode.vue';
import { Head } from '@inertiajs/vue3';
import SuperAdminSidebar from "./SuperAdminSidebar.vue";

const toast = useToast();
const isDark = ref(true);
const activeTab = ref("profile");

const profile = reactive({ id: null, name: "", email: "", avatar_url: null });

const fetchProfile = async () => {
    try {
        const res = await axios.get("/super-admin/profile");
        const user = res.data;
        profile.id = user.id;
        profile.name = user.name;
        profile.email = user.email;
        profile.avatar_url = user.avatar_url || user.avatar || null;

        twoFA.enabled = user.two_factor_enabled === 1 || user.two_factor_enabled === true || user.two_factor_enabled === "1";

        if (user.notification_preferences) {
            notifications.email = !!user.notification_preferences.email;
            notifications.workspaceActivity = !!user.notification_preferences.workspaceActivity;
            notifications.overdue = !!user.notification_preferences.overdue;
            Object.assign(originalNotifications, notifications);
        }
    } catch (error) {
        console.error("fetchProfile error:", error);
    }
};

/* ---------------- Avatar upload ---------------- */
const avatarInput = ref(null);
const avatarFile = ref(null);
const avatarPreview = ref(null);
const savingProfile = ref(false);

const triggerAvatarUpload = () => avatarInput.value?.click();

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith("image/")) {
        toast.error("Please choose an image file");
        e.target.value = "";
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        toast.error("Image must be smaller than 5MB");
        e.target.value = "";
        return;
    }

    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const cancelAvatarChange = () => {
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
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
        if (avatarFile.value) formData.append("avatar", avatarFile.value);

        const res = await axios.post("/super-admin/profile", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        if (res.data?.avatar_url) profile.avatar_url = res.data.avatar_url;
        cancelAvatarChange();
        toast.success("Profile updated successfully");
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to update profile");
    } finally {
        savingProfile.value = false;
    }
};

/* ---------------- Password ---------------- */
const security = reactive({ currentPassword: "", newPassword: "", confirmPassword: "" });
const passwordErrors = reactive({ currentPassword: "", newPassword: "", confirmPassword: "" });
const passwordTouched = reactive({ currentPassword: false, newPassword: false, confirmPassword: false });
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const updatingPassword = ref(false);

const validateCurrentPassword = () => {
    passwordErrors.currentPassword = security.currentPassword ? "" : "Current password is required";
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

const handlePasswordBlur = (field) => { passwordTouched[field] = true; };

const updatePassword = async () => {
    passwordTouched.currentPassword = true;
    passwordTouched.newPassword = true;
    passwordTouched.confirmPassword = true;

    validateCurrentPassword();
    validateNewPassword();
    validateConfirmPassword();

    if (passwordErrors.currentPassword || passwordErrors.newPassword || passwordErrors.confirmPassword) return;

    updatingPassword.value = true;
    try {
        const response = await axios.post("/super-admin/change-password", {
            current_password: security.currentPassword,
            password: security.newPassword,
            password_confirmation: security.confirmPassword,
        });

        security.currentPassword = "";
        security.newPassword = "";
        security.confirmPassword = "";
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
            passwordErrors.newPassword = "New password must be different from your current password";
            passwordTouched.newPassword = true;
        } else {
            toast.error(serverMessage || "Failed to update password");
        }
    } finally {
        updatingPassword.value = false;
    }
};

/* ---------------- 2FA ---------------- */
const twoFA = reactive({ qr: null, secret: null, code: "", enabled: false, loading: false });

const generate2FA = async () => {
    try {
        twoFA.loading = true;
        const res = await axios.get("/super-admin/2fa/generate");
        twoFA.qr = res.data.qr;
        twoFA.secret = res.data.secret;
        toast.success("QR generated. Scan it in Google Authenticator");
    } catch (err) {
        toast.error("Failed to generate QR");
    } finally {
        twoFA.loading = false;
    }
};

const enable2FA = async () => {
    if (!twoFA.code) { toast.error("Enter 6-digit code"); return; }
    try {
        twoFA.loading = true;
        await axios.post("/super-admin/2fa/enable", { code: twoFA.code });
        twoFA.enabled = true;
        toast.success("2FA enabled successfully");
    } catch (err) {
        toast.error(err.response?.data?.message || "Invalid code");
    } finally {
        twoFA.loading = false;
    }
};

const disable2FA = async () => {
    if (!twoFA.code) { toast.error("Please enter your 6-digit verification code to disable 2FA"); return; }
    try {
        twoFA.loading = true;
        await axios.post("/super-admin/2fa/disable", { code: twoFA.code });
        twoFA.enabled = false;
        twoFA.qr = null;
        twoFA.secret = null;
        twoFA.code = "";
        toast.success("2FA has been disabled successfully");
    } catch (err) {
        toast.error(err.response?.data?.message || "Invalid verification code. Failed to disable 2FA");
    } finally {
        twoFA.loading = false;
    }
};

/* ---------------- Notifications ---------------- */
const notifications = reactive({ email: true, workspaceActivity: true, overdue: true });
const originalNotifications = reactive({ email: true, workspaceActivity: true, overdue: true });

const saveNotificationPreferences = async () => {
    const hasChanges =
        notifications.email !== originalNotifications.email ||
        notifications.workspaceActivity !== originalNotifications.workspaceActivity ||
        notifications.overdue !== originalNotifications.overdue;

    if (!hasChanges) {
        toast.warning("Nothing to update on saving the preferences");
        return;
    }

    try {
        await axios.put("/super-admin/notification-preferences", { ...notifications });
        Object.assign(originalNotifications, notifications);
        toast.success("Preferences saved successfully");
    } catch (error) {
        toast.error("Failed to update preferences");
    }
};

const userInitials = computed(() =>
    profile.name.split(" ").map(w => w[0]).join("").toUpperCase()
);

onMounted(() => {
    fetchProfile();
});
</script>

<style scoped>
.dashboard.theme-dark {
    --dashboard-bg: #10121c; --panel-bg: #171a26; --card-inner-bg: #1d2130; --card-inner-hover: #262b3d;
    --input-element-bg: #212536; --border-subtle: rgba(148,163,210,0.09); --border-deep: rgba(148,163,210,0.16);
    --text-main: #d9dbe7; --text-header: #f6f7fb; --text-muted: #7d83a0; --shadow-cards: rgba(3,4,10,0.45);
    --accent: #1fd1ab; --accent-soft: rgba(31,209,171,0.16); --c-green: #0e9a7f; --c-red: #d6484f; --c-slate: #6c7893;
}
.dashboard.theme-light {
    --dashboard-bg: #eef1f7; --panel-bg: #ffffff; --card-inner-bg: #f5f7fb; --card-inner-hover: #eaedf5;
    --input-element-bg: #f0f2f8; --border-subtle: rgba(30,35,70,0.08); --border-deep: rgba(30,35,70,0.14);
    --text-main: #2d3142; --text-header: #12141f; --text-muted: #767c93; --shadow-cards: rgba(24,28,55,0.06);
    --accent: #0b8a75; --accent-soft: rgba(11,138,117,0.1); --c-green: #0e9a7f; --c-red: #d6484f; --c-slate: #8891a5;
}

.dashboard { display: flex; height: 100vh; font-family: 'Inter', system-ui, -apple-system, sans-serif; background-color: var(--dashboard-bg); color: var(--text-main); overflow: hidden; }
.main-content { flex: 1; overflow-y: auto; width: 100%; height: 100%; }
.content-wrapper { max-width: 1100px; margin: 0 auto; width: 100%; padding: 28px 36px 56px; }

.settings-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.settings-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 3px 0; color: var(--text-header); }
.settings-header p { margin: 0; color: var(--text-muted); font-size: 13px; }
.theme-btn { width: 38px; height: 38px; border-radius: 9px; border: 1px solid var(--border-subtle); cursor: pointer; background: var(--input-element-bg); color: var(--text-main); font-size: 15px; }

.settings-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px; background: var(--card-inner-bg); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 5px; width: fit-content; }
.settings-tabs button { border: none; padding: 9px 16px; border-radius: 7px; cursor: pointer; font-weight: 600; font-size: 13px; background: transparent; color: var(--text-muted); }
.settings-tabs button:hover { color: var(--text-main); }
.settings-tabs button.active { background: var(--accent); color: #06120f; }

.settings-card { background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 26px; margin-bottom: 20px; }
.card-header { margin-bottom: 22px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 16px; }
.card-header h2 { font-size: 16px; font-weight: 600; margin-bottom: 4px; color: var(--text-header); }
.card-header p { color: var(--text-muted); font-size: 13px; margin: 0; }
.card-footer { margin-top: 24px; display: flex; justify-content: flex-end; }

.avatar-section { display: flex; align-items: center; gap: 18px; margin-bottom: 28px; }
.avatar-wrapper { position: relative; width: 64px; height: 64px; flex-shrink: 0; border-radius: 50%; cursor: pointer; }
.avatar-circle { width: 64px; height: 64px; border-radius: 50%; background: var(--accent); color: #06120f; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: 700; }
.avatar-image { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; display: block; border: 1px solid var(--border-subtle); }
.avatar-edit-overlay { position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,.45); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; opacity: 0; transition: opacity .15s ease; }
.avatar-wrapper:hover .avatar-edit-overlay { opacity: 1; }
.hidden-file-input { display: none; }
.change-photo-link { background: none; border: none; padding: 0; color: var(--accent); font-size: 12.5px; font-weight: 600; cursor: pointer; }
.cancel-photo-link { background: none; border: none; padding: 0; color: var(--text-muted); font-size: 12.5px; font-weight: 600; cursor: pointer; margin-left: 16px; }
.avatar-section h3 { font-size: 16px; font-weight: 600; color: var(--text-header); margin: 0 0 3px 0; }
.avatar-section span { display: block; font-size: 13px; color: var(--text-muted); }
.photo-actions { display: flex; align-items: center; gap: 4px; margin-top: 8px; }

.settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.form-group { display: flex; flex-direction: column; }
.form-group label { margin-bottom: 7px; font-size: 12.5px; font-weight: 600; color: var(--text-muted); }
.form-group input { padding: 10px 13px; border-radius: 8px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); color: var(--text-main); outline: none; font-size: 13.5px; }
.form-group input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
.form-group input:disabled { opacity: .75; cursor: not-allowed; background: var(--card-inner-bg); color: var(--text-muted); border-style: dashed; }
.field-hint { margin-top: 6px; font-size: 11px; color: var(--text-muted); }

.primary-btn { background: var(--accent); color: #06120f; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13.5px; }
.primary-btn:hover { opacity: .9; }
.primary-btn:disabled { opacity: .5; cursor: not-allowed; }
.danger-btn { background: var(--c-red) !important; color: white !important; }
.success-btn { background: var(--c-green) !important; color: white !important; }
.setup-btn { background: var(--c-slate) !important; color: white !important; }

.password-form-block { background: var(--card-inner-bg); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 20px; margin-bottom: 24px; }
.password-btn-alignment { display: flex; align-items: flex-end; justify-content: flex-end; }
.password-wrapper { position: relative; width: 100%; }
.password-wrapper input { width: 100%; padding-right: 42px; box-sizing: border-box; }
.eye-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); border: none; background: transparent; cursor: pointer; font-size: 14px; opacity: .65; }
.error-text { color: var(--c-red); margin-top: 6px; font-size: 12px; }

.twofa-box { border: 1px solid var(--border-subtle); border-radius: 10px; background: var(--card-inner-bg); overflow: hidden; }
.twofa-header { background: var(--accent-soft); padding: 14px 20px; border-bottom: 1px solid var(--border-subtle); }
.twofa-header h3 { font-size: 14px; font-weight: 600; color: var(--text-header); margin: 0; }
.twofa-content { padding: 20px; }
.twofa-desc { color: var(--text-muted); font-size: 13px; margin-bottom: 16px; }
.twofa-active-text { color: var(--c-green); font-weight: 600; font-size: 13px; margin-bottom: 12px; }
.qr-box { margin-top: 18px; padding: 18px; background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 10px; display: flex; flex-direction: column; gap: 12px; max-width: 380px; }
.qr-box p { margin: 0; font-size: 13px; color: var(--text-muted); }
.qr-box code { background: var(--card-inner-bg); padding: 7px 11px; border-radius: 6px; border: 1px solid var(--border-subtle); font-family: monospace; font-size: 13px; color: var(--c-red); }
.inline-verification-input { margin: 6px 0; }

.notification-list { display: flex; flex-direction: column; gap: 12px; }
.notify-item { background: var(--card-inner-bg); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 16px 18px; display: flex; justify-content: space-between; align-items: center; }
.notify-item h4 { font-size: 13.5px; font-weight: 600; color: var(--text-main); margin: 0 0 3px 0; }
.notify-item p { font-size: 12px; color: var(--text-muted); margin: 0; }

.switch { position: relative; display: inline-block; width: 42px; height: 23px; flex-shrink: 0; }
.switch input { display: none; }
.switch span { position: absolute; inset: 0; background: var(--border-deep); border-radius: 30px; cursor: pointer; transition: .2s; }
.switch span::before { content: ""; position: absolute; width: 17px; height: 17px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: .2s; }
.switch input:checked + span { background: var(--accent); }
.switch input:checked + span::before { transform: translateX(19px); }

@media (max-width: 992px) {
    .settings-grid { grid-template-columns: 1fr; }
    .password-btn-alignment { justify-content: flex-start; margin-top: 8px; }
}
</style>