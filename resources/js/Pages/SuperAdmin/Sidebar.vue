<template>
  <aside class="sa-sidebar">
    <div>
      <div class="sa-logo">
        <div class="sa-logo-icon">S</div>
        <div>
          <h2>Super Admin</h2>
          <span>Management Panel</span>
        </div>
      </div>

      <nav class="sa-menu">
        <Link href="/super-admin/dashboard" :class="{ active: isActive('/super-admin/dashboard') }">📊 Dashboard</Link>
        <Link href="/super-admin/dashboard" :class="{ active: false }">👤 Administrators</Link>
        <Link href="/super-admin/workspaces" :class="{ active: isActive('/super-admin/workspaces') }">🏢 Workspaces</Link>
        <Link href="/super-admin/projects" :class="{ active: isActive('/super-admin/projects') }">📁 Projects</Link>
        <Link href="/super-admin/teams" :class="{ active: isActive('/super-admin/teams') }">👥 Teams</Link>
        <Link href="/super-admin/settings" :class="{ active: isActive('/super-admin/settings') }">⚙️ Settings</Link>
      </nav>
    </div>

    <button class="sa-logout-btn" @click="logout">Logout</button>
  </aside>
</template>

<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";

const page = usePage();
const isActive = (path) => page.url.startsWith(path);

function logout() {
  router.post("/super-admin/logout");
}
</script>

<style scoped>
/* Self-contained: doesn't rely on the host page defining --accent etc.,
   so it drops into any Super Admin page unchanged. Falls back to its
   own teal if the page happens to define --accent too (dashboard does). */
.sa-sidebar {
  width: 252px;
  background: #10121a;
  border-right: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 24px 16px;
  flex-shrink: 0;
  height: 100vh;
  position: sticky;
  top: 0;
}

.sa-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding: 4px 8px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
.sa-logo-icon {
  width: 38px; height: 38px; border-radius: 9px;
  background: linear-gradient(150deg, #2be3bb, var(--accent, #1fd1ab));
  display: flex; justify-content: center; align-items: center;
  font-family: 'Lexend', 'Inter', sans-serif;
  font-size: 16px; font-weight: 700; color: #06110e; flex-shrink: 0;
}
.sa-logo h2 { font-size: 14.5px; font-weight: 600; color: #f4f5f9; margin-bottom: 1px; letter-spacing: -0.1px; font-family: 'Lexend', 'Inter', sans-serif; }
.sa-logo span { color: #6d7288; font-size: 11px; font-family: 'Inter', sans-serif; letter-spacing: 0.2px; }

.sa-menu { display: flex; flex-direction: column; gap: 2px; }
.sa-menu a {
  text-decoration: none; color: #8a8fa5; display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: 8px; font-weight: 500; font-size: 13px; transition: .15s;
  position: relative; font-family: 'Inter', sans-serif;
}
.sa-menu a:hover { background: rgba(255, 255, 255, 0.04); color: #e7e9f2; }
.sa-menu a.active { background: rgba(23, 178, 149, 0.14); color: #f4f5f9; }
.sa-menu a.active::before {
  content: ""; position: absolute; left: -16px; top: 50%; transform: translateY(-50%);
  width: 3px; height: 18px; border-radius: 0 3px 3px 0; background: var(--accent, #1fd1ab);
}

.sa-logout-btn {
  border: 1px solid rgba(214, 72, 79, 0.25); border-radius: 8px; background: transparent;
  color: #e2777c; padding: 11px; cursor: pointer; font-weight: 600; font-size: 13px; transition: .2s;
  font-family: 'Inter', sans-serif;
}
.sa-logout-btn:hover { background: rgba(214, 72, 79, .1); border-color: rgba(214, 72, 79, .4); }
</style>