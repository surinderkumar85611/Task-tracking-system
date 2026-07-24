<template>
    <aside class="sidebar" :class="theme.themeClass">

        <div class="logo">
            <div class="logo-mark">M</div>
            <h2>Member Panel</h2>
        </div>

        <div class="nav-section-label">Menu</div>

        <nav class="nav">

            <Link href="/member/dashboard" class="nav-item" :class="{ active: isActive('/member/dashboard') }">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </Link>

            <Link href="/member/tasks" class="nav-item" :class="{ active: isActive('/member/tasks') }">
                <span class="nav-icon">📋</span>
                <span class="nav-label">My Tasks</span>
            </Link>

            <Link href="/member/projects" class="nav-item" :class="{ active: isActive('/member/projects') }">
                <span class="nav-icon">📁</span>
                <span class="nav-label">My Projects</span>
            </Link>

            <Link href="/member/settings" class="nav-item" :class="{ active: isActive('/member/settings') }">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Settings</span>
            </Link>

        </nav>

        <div class="sidebar-footer">
            <button class="logout-btn" @click="logout">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </button>
        </div>

    </aside>
</template>

<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { useThemeStore } from "../../stores/theme.js";

const page = usePage();
const theme = useThemeStore();

const currentUrl = computed(() => page.url || "");

const isActive = (path) => {
    return currentUrl.value === path || currentUrl.value.startsWith(path + "/");
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
/* ==========================================================================
   THEME TOKENS — mirrors Dashboard.vue's palette so both stay in sync
   ========================================================================== */
.sidebar.theme-dark {
    --sidebar-bg: #1e2230;
    --sidebar-border: rgba(255, 255, 255, 0.06);
    --sidebar-hover-bg: rgba(255, 255, 255, 0.05);
    --sidebar-text: #a8afc4;
    --sidebar-text-strong: #f6f7fb;
    --sidebar-muted: #626a83;
    --sidebar-active-bg: rgba(85, 110, 230, 0.14);
    --sidebar-active-text: #7c90f0;
    --sidebar-accent: #556ee6;
    --sidebar-danger: #f46a6a;
    --sidebar-danger-border: rgba(244, 106, 106, 0.25);
    --sidebar-danger-border-hover: rgba(244, 106, 106, 0.4);
    --sidebar-danger-bg-hover: rgba(244, 106, 106, 0.1);
}

.sidebar.theme-light {
    --sidebar-bg: #ffffff;
    --sidebar-border: rgba(33, 37, 61, 0.08);
    --sidebar-hover-bg: rgba(33, 37, 61, 0.05);
    --sidebar-text: #5c637a;
    --sidebar-text-strong: #22263d;
    --sidebar-muted: #9298ac;
    --sidebar-active-bg: rgba(85, 110, 230, 0.1);
    --sidebar-active-text: #4a5cd6;
    --sidebar-accent: #556ee6;
    --sidebar-danger: #e05555;
    --sidebar-danger-border: rgba(224, 85, 85, 0.25);
    --sidebar-danger-border-hover: rgba(224, 85, 85, 0.4);
    --sidebar-danger-bg-hover: rgba(224, 85, 85, 0.08);
}

.sidebar {
    width: 250px;
    height: 100vh;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--sidebar-border);
    display: flex;
    flex-direction: column;
    padding: 22px 16px;
    position: sticky;
    top: 0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    flex-shrink: 0;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.logo {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 4px 8px 24px 8px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--sidebar-border);
}

.logo-mark {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--sidebar-accent);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
}

.logo h2 {
    color: var(--sidebar-text-strong);
    font-size: 15.5px;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.1px;
}

.nav-section-label {
    color: var(--sidebar-muted);
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 0 10px;
    margin-bottom: 10px;
}

.nav {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    color: var(--sidebar-text);
    text-decoration: none;
    transition: all 0.15s ease;
    font-weight: 500;
    font-size: 13.5px;
}

.nav-icon {
    font-size: 15px;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

.nav-item:hover {
    background: var(--sidebar-hover-bg);
    color: var(--sidebar-text-strong);
}

.nav-item.active {
    background: var(--sidebar-active-bg);
    color: var(--sidebar-active-text);
    font-weight: 600;
    position: relative;
}

.nav-item.active::before {
    content: "";
    position: absolute;
    left: -16px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 18px;
    background: var(--sidebar-accent);
    border-radius: 0 3px 3px 0;
}

.sidebar-footer {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid var(--sidebar-border);
}

.logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: transparent;
    border: 1px solid var(--sidebar-danger-border);
    border-radius: 8px;
    color: var(--sidebar-danger);
    cursor: pointer;
    font-weight: 600;
    font-size: 13.5px;
    transition: all 0.15s ease;
}

.logout-btn:hover {
    background: var(--sidebar-danger-bg-hover);
    border-color: var(--sidebar-danger-border-hover);
}
</style>