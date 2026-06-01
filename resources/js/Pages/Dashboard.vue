<template>
  <div class="dashboard" :class="theme.themeClass">

    <Sidebar />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Header -->
      <header class="header">
        <div>
          <h1>Dashboard</h1>
          <p>Welcome back, manage your workspace.</p>
        </div>
        <div class="header-right">
          <button class="theme-btn" @click="theme.toggleTheme">
            {{ theme.isDark ? "☀️" : "🌙" }}
          </button>
          <input type="text" placeholder="Search..." />
          <button class="icon-btn">🔔</button>
          <img src="https://i.pravatar.cc/100" class="avatar" />
        </div>
      </header>

      <!-- Stats Cards -->
      <section class="stats">
        <div class="card blue">
          <p>Total Projects</p>
          <h2>48</h2>
        </div>
        <div class="card purple">
          <p>Team Members</p>
          <h2>126</h2>
        </div>
        <div class="card green">
          <p>Tasks Completed</p>
          <h2>1,204</h2>
        </div>
        <div class="card orange">
          <p>Pending Tasks</p>
          <h2>32</h2>
        </div>
      </section>

      <!-- Main Dashboard Content -->
      <section class="dashboard-content">
        <!-- Projects -->
        <div class="projects">
          <div class="projects-header">
            <h2>Active Projects</h2>
            <button>New Project</button>
          </div>
          <div class="project-list">
            <div class="project-card" v-for="(board, index) in boards" :key="index">
              <div class="project-header">
                <h3>{{ board.title }}</h3>
                <span>{{ board.progress }}%</span>
              </div>
              <div class="progress-bar">
                <div class="progress" :style="{ width: board.progress + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Right -->
        <div class="sidebar-right">
          <div class="productivity">
            <p>Productivity</p>
            <h2>86%</h2>
            <small>Team performance increased this month.</small>
            <button>View Report</button>
          </div>

          <div class="team">
            <h2>Team Members</h2>
            <div class="team-member">
              <img src="https://i.pravatar.cc/50?img=1" />
              <div>
                <p>Sarah</p>
                <small>UI Designer</small>
              </div>
              <span class="status online">Online</span>
            </div>
            <div class="team-member">
              <img src="https://i.pravatar.cc/50?img=2" />
              <div>
                <p>David</p>
                <small>Developer</small>
              </div>
              <span class="status away">Away</span>
            </div>
            <div class="team-member">
              <img src="https://i.pravatar.cc/50?img=3" />
              <div>
                <p>Emma</p>
                <small>Manager</small>
              </div>
              <span class="status online">Online</span>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useThemeStore } from "../stores/theme";
import Sidebar from "./components/Sidebar.vue";

const boards = [
  { title: "Website Redesign", progress: 75 },
  { title: "Mobile App", progress: 42 },
  { title: "Marketing Campaign", progress: 91 },
  { title: "CRM Dashboard", progress: 63 },
];

const theme = useThemeStore();
</script>

<style scoped>
/* Stats Cards */
.stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  padding: 20px 40px;
}

.stats .card p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 10px;
  transition: color 0.3s ease;
}

.theme-light .stats .card p {
  color: var(--card-text-inverse);
}

.theme-dark .stats .card p {
  color: var(--card-text-inverse);
}

/* Dashboard Content */
.dashboard-content {
  display: flex;
  padding: 20px 40px;
  gap: 20px;
  flex-wrap: wrap;
}

.projects {
  flex: 2;
  background: var(--sidebar);
  border-radius: 15px;
  padding: 20px;
}

.projects-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.projects-header button {
  background: #06b6d4;
  border: none;
  padding: 8px 15px;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.3s;
}

.projects-header button:hover {
  background: #0ea5e9;
}

.project-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.project-card {
  background: var(--card);
  border-radius: 12px;
  padding: 15px;
  transition: transform 0.3s;
  cursor: pointer;
}

.project-card:hover {
  transform: translateY(-5px);
}

.project-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.progress-bar {
  height: 6px;
  background: #374151;
  border-radius: 5px;
  overflow: hidden;
}

.progress {
  height: 100%;
  background: linear-gradient(90deg, #06b6d4, #3b82f6);
  border-radius: 5px;
}

/* Right Sidebar */
.sidebar-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.productivity {
  background: linear-gradient(135deg, #06b6d4, #2563eb);
  border-radius: 15px;
  padding: 25px;
  text-align: left;
}

.productivity p {
  opacity: 0.8;
}

.productivity h2 {
  font-size: 52px;
  margin: 15px 0;
}

.productivity small {
  opacity: 0.9;
  display: block;
  margin-bottom: 20px;
}

.productivity button {
  background: #fff;
  color: #111827;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}

.productivity button:hover {
  transform: scale(1.05);
}

/* Team */
.team {
  background: var(--sidebar);
  border-radius: 15px;
  padding: 20px;
}

.team h2 {
  margin-bottom: 20px;
}

.team-member {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 15px;
  background: var(--card);
  padding: 12px;
  border-radius: 12px;
  transition: 0.3s;
}

.team-member:hover {
  transform: translateX(5px);
  background: #273449;
}

.team-member img {
  width: 45px;
  height: 45px;
  border-radius: 12px;
  margin-right: 12px;
}

.team-member div {
  flex: 1;
}

.team-member p {
  font-weight: 600;
}

.team-member small {
  color: #9ca3af;
}

.status {
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 20px;
}

.online {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
}

.away {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
}

</style>