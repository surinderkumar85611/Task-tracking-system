<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const isActive = (path) => {
  return page.url === path;
};
</script>

<template>
  <aside class="sidebar">

    <div class="logo">
      BTT<span> Board</span>
    </div>

    <nav class="nav-links">

      <Link
        href="/"
        :class="{ active: isActive('/') }"
      >
        Dashboard
      </Link>

      <Link
        href="/project"
        :class="{ active: isActive('/project') }"
      >
        Projects
      </Link>

      <Link
        href="/team"
        :class="{ active: isActive('/team') }"
      >
        Team
      </Link>

      <Link
        href="/member"
        :class="{ active: isActive('/member') }"
      >
        Members
      </Link>

      <Link
        href="/setting"
        :class="{ active: isActive('/setting') }"
      >
        Settings
      </Link>

    </nav>

    <div class="upgrade">
      <p>Upgrade Plan</p>
      <small>Unlock premium analytics & AI tools.</small>
      <button>Upgrade</button>
    </div>

  </aside>
</template>

<style scoped>
/* Sidebar */
.sidebar {
  width: 280px;
  background: var(--sidebar);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px;
}

.logo {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 40px;
}

.logo span {
  color: #06b6d4;
}

.nav-links a {
  display: block;
  padding: 12px 16px;
  border-radius: 12px;
  margin-bottom: 8px;
  color: var(--text);
  text-decoration: none;
  transition: background 0.3s;
}

.nav-links a:hover {
  background: var(--hover);
}

.nav-links a.active {
  background: #06b6d4;
  color: #111827;
}

.upgrade {
  background: var(--bg);
  padding: 15px;
  border-radius: 15px;
  text-align: center;
  margin-top: 20px;
}

.upgrade button {
  margin-top: 10px;
  padding: 8px 15px;
  border: none;
  border-radius: 10px;
  background: #06b6d4;
  color: #111827;
  cursor: pointer;
  transition: 0.3s;
}

.upgrade button:hover {
  background: #0ea5e9;
}
</style>