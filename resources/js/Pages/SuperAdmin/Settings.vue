<template>
    <Head title="Settings" />

    <div class="sa-page" :class="isDark ? 'theme-dark' : 'theme-light'">

        <Sidebar />

        <main class="sa-main">

            <div class="sa-topbar">
                <div class="sa-topbar-title">
                    <h2>Settings</h2>
                    <p>Manage your profile and account security</p>
                </div>

                <div class="sa-topbar-actions">
                    <button class="sa-icon-btn" aria-label="Toggle theme" @click="isDark = !isDark">
                        {{ isDark ? '☀️' : '🌙' }}
                    </button>
                </div>
            </div>

            <div class="sa-content narrow">

                <div class="sa-tabs">
                    <button :class="{ active: activeTab === 'profile' }" @click="activeTab = 'profile'">👤 Profile</button>
                    <button :class="{ active: activeTab === 'security' }" @click="activeTab = 'security'">🔒 Security</button>
                </div>

                <!-- PROFILE -->
                <section v-if="activeTab === 'profile'" class="sa-card settings-card">
                    <div class="card-title">
                        <h3>Profile information</h3>
                        <p>Update your name and email address.</p>
                    </div>

                    <div class="avatar-section">
                        <div class="avatar-circle">{{ userInitials }}</div>
                        <div>
                            <h4>{{ form.name || 'Super Admin' }}</h4>
                            <span>{{ form.email }}</span>
                            <div class="role-tag"><span class="sa-pill violet">Super Administrator</span></div>
                        </div>
                    </div>

                    <div class="settings-grid">
                        <div class="form-group">
                            <label>Full name</label>
                            <input type="text" v-model="form.name" />
                            <p v-if="errors.name" class="error-text">{{ errors.name }}</p>
                        </div>

                        <div class="form-group">
                            <label>Email address</label>
                            <input type="email" v-model="form.email" />
                            <p v-if="errors.email" class="error-text">{{ errors.email }}</p>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button class="sa-btn" :disabled="saving || !changed" @click="saveProfile">
                            {{ saving ? 'Saving...' : 'Save changes' }}
                        </button>
                    </div>
                </section>

                <!-- SECURITY -->
                <section v-if="activeTab === 'security'" class="sa-card settings-card">
                    <div class="card-title">
                        <h3>Security</h3>
                        <p>Protect your account with two factor authentication.</p>
                    </div>

                    <div class="twofa-box">
                        <div class="twofa-header">
                            <h4>Two Factor Authentication (2FA)</h4>
                            <span class="sa-pill" :class="twoFA.enabled ? 'green' : 'amber'">
                                {{ twoFA.enabled ? 'Enabled' : 'Not enabled' }}
                            </span>
                        </div>

                        <div class="twofa-content">

                            <!-- enabled -->
                            <div v-if="twoFA.enabled">
                                <p class="twofa-active">🔒 Two-factor authentication is active on your account.</p>

                                <div class="form-group code-field">
                                    <label>Enter your 6-digit code to disable 2FA</label>
                                    <input v-model="twoFA.code" placeholder="123456" maxlength="6" />
                                </div>

                                <button class="sa-btn danger" @click="disable2FA" :disabled="twoFA.loading">
                                    {{ twoFA.loading ? 'Disabling...' : 'Disable 2FA' }}
                                </button>
                            </div>

                            <!-- not enabled -->
                            <div v-else>
                                <p class="twofa-desc">
                                    Secure your account using a code from Google Authenticator each time you sign in.
                                </p>

                                <button class="sa-btn" @click="generate2FA" :disabled="twoFA.loading">
                                    {{ twoFA.loading ? 'Please wait...' : 'Generate QR code' }}
                                </button>

                                <div v-if="twoFA.qr" class="qr-box">
                                    <p>Scan this QR code in Google Authenticator:</p>
                                    <div class="qr-frame">
                                        <qrcode-vue :value="twoFA.qr" :size="190" level="H" background="#ffffff" />
                                    </div>

                                    <p class="manual"><strong>Or enter this key manually:</strong></p>
                                    <code>{{ twoFA.secret }}</code>

                                    <div class="form-group code-field">
                                        <label>Enter the 6-digit code</label>
                                        <input v-model="twoFA.code" placeholder="123456" maxlength="6" />
                                    </div>

                                    <button class="sa-btn success" @click="enable2FA" :disabled="twoFA.loading">
                                        Enable 2FA
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { router, Head } from "@inertiajs/vue3";
import axios from "axios";
import { useToast } from "vue-toastification";
import QrcodeVue from "qrcode.vue";
import Sidebar from "./Sidebar.vue";

const toast = useToast();

const props = defineProps({
    profile: {
        type: Object,
        default: () => ({ name: "", email: "", two_factor_enabled: false }),
    },
});

/* theme (same key on every Super Admin page) */
const isDark = ref(localStorage.getItem("sa_theme") !== "light");
watch(isDark, (v) => localStorage.setItem("sa_theme", v ? "dark" : "light"));

const activeTab = ref("profile");

/* ---------- profile ---------- */
const form = reactive({ name: props.profile.name, email: props.profile.email });
const original = reactive({ name: props.profile.name, email: props.profile.email });
const errors = ref({});
const saving = ref(false);

const changed = computed(() => form.name !== original.name || form.email !== original.email);

const userInitials = computed(() =>
    (form.name || "SA").split(" ").filter(Boolean).slice(0, 2).map((w) => w[0]).join("").toUpperCase()
);

const saveProfile = () => {
    saving.value = true;
    errors.value = {};

    router.put("/super-admin/profile", { name: form.name, email: form.email }, {
        preserveScroll: true,
        onSuccess: () => {
            original.name = form.name;
            original.email = form.email;
            toast.success("Profile updated successfully");
        },
        onError: (errs) => {
            const flat = {};
            Object.keys(errs || {}).forEach((k) => {
                flat[k] = Array.isArray(errs[k]) ? errs[k][0] : errs[k];
            });
            errors.value = flat;
            toast.error("Please check the highlighted fields");
        },
        onFinish: () => { saving.value = false; },
    });
};

/* ---------- two factor ---------- */
const twoFA = reactive({
    qr: null,
    secret: null,
    code: "",
    enabled: !!props.profile.two_factor_enabled,
    loading: false,
});

const generate2FA = async () => {
    try {
        twoFA.loading = true;
        const res = await axios.get("/super-admin/2fa/generate");
        twoFA.qr = res.data.qr;
        twoFA.secret = res.data.secret;
        toast.success("QR code ready. Scan it in Google Authenticator");
    } catch (err) {
        console.error(err);
        toast.error("Failed to generate the QR code");
    } finally {
        twoFA.loading = false;
    }
};

const enable2FA = async () => {
    if (!twoFA.code) {
        toast.error("Enter the 6-digit code");
        return;
    }

    try {
        twoFA.loading = true;
        await axios.post("/super-admin/2fa/enable", { code: twoFA.code });
        twoFA.enabled = true;
        twoFA.qr = null;
        twoFA.secret = null;
        twoFA.code = "";
        toast.success("2FA enabled successfully");
    } catch (err) {
        toast.error(err.response?.data?.message || "Invalid code");
    } finally {
        twoFA.loading = false;
    }
};

const disable2FA = async () => {
    if (!twoFA.code) {
        toast.error("Enter your 6-digit code to disable 2FA");
        return;
    }

    try {
        twoFA.loading = true;
        await axios.post("/super-admin/2fa/disable", { code: twoFA.code });
        twoFA.enabled = false;
        twoFA.qr = null;
        twoFA.secret = null;
        twoFA.code = "";
        toast.success("2FA has been disabled");
    } catch (err) {
        toast.error(err.response?.data?.message || "Invalid code. Failed to disable 2FA");
    } finally {
        twoFA.loading = false;
    }
};
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


.sa-content.narrow {
    max-width: 920px;
}

.settings-card {
    padding: 28px;
}

.card-title {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-divider);
}

.card-title h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-header);
    margin-bottom: 6px;
}

.card-title p {
    font-size: 13px;
    color: var(--text-muted);
}

.avatar-section {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 28px;
}

.avatar-circle {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--on-accent);
    background: linear-gradient(150deg, #2be3bb, var(--accent));
    box-shadow: 0 6px 18px var(--accent-soft);
}

.avatar-section h4 {
    font-size: 17px;
    font-weight: 600;
    color: var(--text-header);
}

.avatar-section span {
    font-size: 13px;
    color: var(--text-muted);
}

.role-tag {
    margin-top: 8px;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-card-sub);
}

.form-group input {
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid var(--border-deep);
    background: var(--card-inner-bg);
    color: var(--text-main);
    font-size: 14px;
    outline: none;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.error-text {
    margin-top: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--c-red);
}

.card-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 28px;
}

.twofa-box {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid var(--border-deep);
    background: var(--card-inner-bg);
}

.twofa-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    background: var(--accent-soft);
    border-bottom: 1px solid var(--border-deep);
}

.twofa-header h4 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-header);
}

.twofa-content {
    padding: 22px;
}

.twofa-desc {
    margin-bottom: 16px;
    font-size: 13.5px;
    color: var(--text-muted);
}

.twofa-active {
    margin-bottom: 16px;
    font-size: 14px;
    font-weight: 600;
    color: var(--c-green);
}

.code-field {
    max-width: 260px;
    margin: 14px 0;
}

.qr-box {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 420px;
    margin-top: 20px;
    padding: 20px;
    border-radius: 12px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    font-size: 13.5px;
    color: var(--text-card-sub);
}

.qr-frame {
    align-self: flex-start;
    padding: 12px;
    border-radius: 12px;
    background: #ffffff;
}

.manual {
    margin-top: 4px;
}

.qr-box code {
    align-self: flex-start;
    padding: 8px 12px;
    border-radius: 7px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    letter-spacing: 1px;
    color: var(--accent);
    word-break: break-all;
}

@media (max-width: 800px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>