<template>
  <aside class="sidebar">

    <!-- LOGO -->
    <div class="logo">
      <h2>Leader Panel</h2>
      <p>Workspace Control</p>
    </div>

    <!-- NAV LINKS -->
    <nav class="nav">

      <Link href="/leader-dashboard" class="nav-item">
        📊 Dashboard
      </Link>

      <Link href="/project" class="nav-item">
        📁 Projects
      </Link>

      <Link href="/member" class="nav-item">
        👥 Team Members
      </Link>

      <Link href="/teams" class="nav-item">
        🧩 Teams
      </Link>

      <Link href="/task" class="nav-item">
        ✅ Task Board
      </Link>

      <Link href="/settings" class="nav-item">
        ⚙️ Settings
      </Link>

    </nav>

    <!-- FOOTER -->
    <div class="sidebar-footer">

      <button class="logout-btn" @click="logout">
        🚪 Logout
      </button>

    </div>

  </aside>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";

const logout = () => {
  router.post("/logout");
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
</style>