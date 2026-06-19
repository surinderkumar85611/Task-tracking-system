<template>
  <aside class="sidebar">

    <div class="logo">
      <h2>Leader Panel</h2>
    </div>

  

    <nav class="nav">

      <Link href="/dashboard" class="nav-item">📊 Dashboard</Link>
      <Link href="/projects" class="nav-item">📁 Projects</Link>
      <Link href="/team" class="nav-item">👥 Team Members</Link>
      <Link href="/settings" class="nav-item">⚙️ Settings</Link>

    </nav>

    <div class="sidebar-footer">
      <button class="logout-btn" @click="logout">
        🚪 Logout
      </button>
    </div>

  </aside>
</template>

<script setup>
import { ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

const page = usePage();

const workspaces = page.props.workspaces || [];
const selectedWorkspace = ref(page.props.currentWorkspace?.id || null);

const changeWorkspace = () => {
  if (!selectedWorkspace.value) return;

  router.post("/workspace/select", {
    workspace_id: selectedWorkspace.value
  }, {
    preserveScroll: true,
    preserveState: true
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
.sidebar {
  width: 260px;
  height: 100vh;
  background: #0f172a;
  border-right: 1px solid #1e293b;
  display: flex;
  flex-direction: column;
  padding: 20px;
  position: sticky;
  top: 0;
}

/* LOGO */
.logo {
  margin-bottom: 30px;
}

.logo h2 {
  color: white;
  font-size: 20px;
  margin: 0;
}

.logo p {
  color: #94a3b8;
  font-size: 12px;
  margin-top: 4px;
}

/* NAV */
.nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}

.nav-item {
  padding: 12px 14px;
  border-radius: 10px;
  color: #cbd5e1;
  text-decoration: none;
  transition: 0.2s;
  font-weight: 500;
}

.nav-item:hover {
  background: #1e293b;
  color: white;
}

.nav-item.active {
  background: #2563eb;
  color: white;
}

/* FOOTER */
.sidebar-footer {
  margin-top: auto;
}

.logout-btn {
  width: 100%;
  padding: 12px;
  background: #ef4444;
  border: none;
  color: white;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
}

.logout-btn:hover {
  background: #dc2626;
}
.sidebar {
    width: 15%;
    background: var(--sidebar);
    display: flex;
    flex-direction: column;
    padding: 20px;
    position: relative;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
    height: 100vh;
    border-right: 1px solid var(--border);
}

.sidebar.collapsed {
    width: 78px;
    padding: 20px 12px;
}

.logo-container {
    height: 40px;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: var(--text);
    white-space: nowrap;
}

.logo span {
    color: #06b6d4;
}

.logo-icon {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    width: 100%;
    text-align: center;
}

.logo-icon span {
    color: #06b6d4;
}

.workspace-section {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.workspace-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: #06b6d4;
    color: #111827;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
    white-space: nowrap;
    font-size: 11px;
}

.workspace-btn:hover {
    background: #0ea5e9;
}

.workspace-btn.compact {
    padding: 12px 0;
    font-size: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.workspace-select-icon-trigger {
    margin-top: 12px;
    width: 44px;
    height: 44px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.2s;
}

.workspace-select-icon-trigger:hover {
    background: var(--hover);
}

.nav-links {
    margin-top: 10px;
    flex: 1;
}

.nav-links a {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    color: var(--text);
    text-decoration: none;
    transition: background 0.3s, padding 0.3s ease;
    white-space: nowrap;
}

.sidebar.collapsed .nav-links a {
    padding: 12px 0;
    justify-content: center;
}

.nav-icon {
    font-size: 18px;
    display: inline-block;
}

.nav-text {
    margin-left: 14px;
    font-size: 15px;
}

.nav-links a:hover {
    background: var(--hover);
}

.nav-links a.active {
    background: #06b6d4;
    color: #111827;
}

.collapse-trigger-wrapper {
    margin-top: auto;
    padding-top: 15px;
    display: flex;
    justify-content: center;
    width: 100%;
}

.collapse-toggle-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: background 0.2s, transform 0.2s;
}

.collapse-toggle-btn:hover {
    background: var(--hover);
    transform: scale(1.05);
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.modal {
    width: 450px;
    background: var(--sidebar);
    padding: 25px;
    border-radius: 20px;
    border: 1px solid var(--border);
}

.modal h2 {
    margin-bottom: 20px;
    color: var(--text);
}

.modal input,
.modal textarea {
    width: 100%;
    margin-bottom: 15px;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    outline: none;
}

.modal textarea {
    min-height: 120px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.cancel-btn,
.create-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
}

.cancel-btn {
    background: #64748b;
    color: white;
}

.create-btn {
    background: #06b6d4;
    color: #111827;
}

.workspace-select {
    width: 100%;
    margin-top: 10px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    cursor: pointer;
    outline: none;
}

.workspace-select option {
    background: var(--sidebar);
    color: var(--text);
}

.sticky-modal-select {
    margin-top: 0;
    margin-bottom: 20px;
}
</style>

