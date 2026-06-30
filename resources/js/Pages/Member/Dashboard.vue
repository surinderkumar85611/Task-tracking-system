<template>
  <Head title="Member Dashboard" />

  <div class="dashboard" :class="theme.themeClass">
    <Sidebar />

    <main class="main-content">
      <div class="content-wrapper">

        <header class="header">

          <div class="header-welcome">
            <h1>Member Dashboard</h1>
            <p>
              Welcome back,
              {{ member?.first_name }} {{ member?.last_name }}
            </p>
          </div>

          <div class="header-right">

            <div class="workspace-label" v-if="currentWorkspace">
              🏢 {{ currentWorkspace.name }}
            </div>

            <input
              v-model="search"
              type="text"
              placeholder="Search projects or tasks..."
              class="search-box"
            />

            <button class="theme-btn" @click="theme.toggleTheme">
              {{ theme.isDark ? '☀️' : '🌙' }}
            </button>

            <div class="notification-bell-container" v-click-outside="handleClickOutside">

              <button
                class="icon-btn"
                @click.stop="notificationStore.showBellDropdown = !notificationStore.showBellDropdown"
              >
                🔔

                <span
                  v-if="unreadCount > 0 || notificationStore.activeUrgentTasks.length > 0"
                  class="bell-alert-green-dot"
                >
                  {{ unreadCount + notificationStore.activeUrgentTasks.length }}
                </span>

              </button>

              <div v-if="notificationStore.showBellDropdown" class="notification-dropdown-panel">

                <div class="notification-dropdown-header">
                  <h3>Notifications</h3>
                </div>

                <div class="notification-dropdown-body">

                  <div v-if="unreadNotifications.length" class="notification-section">

                    <h4 class="section-title">Updates</h4>

                    <div
                      v-for="notification in unreadNotifications"
                      :key="'notif-' + notification.id"
                      class="notification-item normal"
                      @click="markAsRead(notification.id)"
                    >
                      <div class="notif-icon">🔔</div>

                      <div class="notif-content">
                        <p class="notif-title">{{ notification.title }}</p>
                        <p class="notif-message">{{ notification.message }}</p>
                        <span class="notif-time">
                          {{ formatNotificationDate(notification.created_at) }}
                        </span>
                      </div>
                    </div>

                  </div>

                  <div v-if="notificationStore.activeUrgentTasks.length" class="notification-section">

                    <h4 class="section-title">Urgent Tasks</h4>

                    <div
                      v-for="task in notificationStore.activeUrgentTasks"
                      :key="'urgent-' + task.id"
                      class="notification-item urgent"
                    >
                      <div class="notif-icon urgent-icon">⚠️</div>

                      <div class="notif-content">
                        <p class="notif-title">{{ task.title }}</p>
                        <p class="notif-message">
                          Only {{ notificationStore.getLiveTaskMetrics(task).string }} left
                        </p>
                      </div>
                    </div>

                  </div>

                  <div
                    v-if="!unreadNotifications.length && !notificationStore.activeUrgentTasks.length"
                    class="notification-empty-state"
                  >
                    🎉 No notifications right now
                  </div>

                </div>
              </div>
            </div>

            <div class="profile-container">

              <img
                src="https://i.pravatar.cc/100"
                class="avatar"
                @click.stop="showProfileMenu = !showProfileMenu"
              />

              <div v-if="showProfileMenu" class="profile-dropdown">
                <button @click="logout">Logout</button>
              </div>

            </div>

          </div>
        </header>

        <section class="stats-grid">

          <div class="stat-card projects-card">
            <span class="stat-label">Assigned Projects</span>
            <h2 class="stat-value">{{ stats.assignedProjects }}</h2>
          </div>

          <div class="stat-card tasks-card">
            <span class="stat-label">Assigned Tasks</span>
            <h2 class="stat-value">{{ stats.assignedTasks }}</h2>
          </div>

          <div class="stat-card completed-card">
            <span class="stat-label">Completed Tasks</span>
            <h2 class="stat-value">{{ stats.completedTasks }}</h2>
          </div>

          <div class="stat-card pending-card">
            <span class="stat-label">Pending Tasks</span>
            <h2 class="stat-value">{{ stats.pendingTasks }}</h2>
          </div>

          <div class="stat-card progress-card">
            <span class="stat-label">Completion Rate</span>
            <h2 class="stat-value">{{ completionRate }}%</h2>
          </div>

          <div class="stat-card team-card">
            <span class="stat-label">Team Members</span>
            <h2 class="stat-value">{{ teamMembers.length }}</h2>
          </div>

        </section>

        <div class="dashboard-grid">

          <section class="panel large-panel">

            <div class="panel-header">
              <h2>My Projects</h2>
            </div>

            <div class="project-grid">

              <div
                v-for="project in filteredProjects"
                :key="project.id"
                class="project-card"
              >
                <h3>{{ project.name }}</h3>
                <p>Due: {{ project.deadline }}</p>

                <div class="progress-bar">
                  <div
                    class="progress-fill"
                    :style="{ width: (project.progress || 0) + '%' }"
                  ></div>
                </div>

                <span>{{ project.progress || 0 }}%</span>
              </div>

            </div>

          </section>

          <section class="panel small-panel">

            <div class="panel-header">
              <h2>My Team</h2>
            </div>

            <div class="team-list">

              <div
                v-for="member in teamMembers"
                :key="member.id"
                class="team-card"
              >
                <div class="avatar-circle">
                  {{ member.first_name?.charAt(0) }}{{ member.last_name?.charAt(0) }}
                </div>

                <div>
                  <h4>{{ member.first_name }} {{ member.last_name }}</h4>
                  <p>{{ member.role }}</p>
                </div>

              </div>

            </div>

          </section>

        </div>

        <div class="dashboard-grid-secondary">

          <section class="panel medium-panel">

            <div class="panel-header">
              <h2>My Tasks</h2>
            </div>

            <div class="task-list">

              <div
                v-for="task in filteredTasks"
                :key="task.id"
                class="task-card"
              >
                <h4>{{ task.title }}</h4>
                <p>{{ task.project_name }}</p>
                <p>{{ task.due_date }}</p>
                <span>{{ task.status }}</span>
              </div>

            </div>

          </section>

          <section class="panel medium-panel">

            <div class="panel-header">
              <h2>My Progress</h2>
            </div>

            <div class="progress-overview">
              <div class="progress-circle">
                {{ completionRate }}%
              </div>

              <div>
                <p>Completed: {{ stats.completedTasks }}</p>
                <p>Pending: {{ stats.pendingTasks }}</p>
                <p>Total: {{ stats.assignedTasks }}</p>
              </div>
            </div>

          </section>

        </div>

        <div class="dashboard-grid-secondary">

          <section class="panel large-panel">

            <div class="panel-header">
              <h2>Project History</h2>
            </div>

            <div class="history-grid">

              <div
                v-for="project in projectHistory"
                :key="project.id"
                class="history-card"
              >
                <h4>{{ project.name }}</h4>
                <p>{{ project.status }}</p>
                <span>{{ project.completed_at }}</span>
              </div>

            </div>

          </section>

          <section class="panel large-panel">

            <div class="panel-header">
              <h2>Task History</h2>
            </div>

            <div class="history-list">

              <div
                v-for="task in taskHistory"
                :key="task.id"
                class="history-item"
              >
                <div class="history-dot"></div>

                <div>
                  <h4>{{ task.title }}</h4>
                  <p>{{ task.status }}</p>
                  <span>{{ task.updated_at }}</span>
                </div>

              </div>

            </div>

          </section>

        </div>

        <section class="panel full-panel">

          <div class="panel-header">
            <h2>Recent Activity</h2>
          </div>

          <div class="activity-list">

            <div
              v-for="activity in activities"
              :key="activity.id"
              class="activity-item"
            >
              <div class="activity-dot"></div>

              <div>
                <h4>{{ activity.title }}</h4>
                <p>{{ activity.message }}</p>
                <span>{{ formatNotificationDate(activity.created_at) }}</span>
              </div>

            </div>

          </div>

        </section>

      </div>
    </main>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue"
import { router } from "@inertiajs/vue3"
import { useThemeStore } from "../../stores/theme.js"
import { useNotificationStore } from "@/stores/notificationStore"

const props = defineProps({
  member: Object,
  stats: Object,
  projects: Array,
  tasks: Array,
  teamMembers: Array,
  projectHistory: Array,
  taskHistory: Array,
  notifications: Array,
  activities: Array,
  currentWorkspace: Object
})

const theme = useThemeStore()
const notificationStore = useNotificationStore()

const search = ref("")
const showProfileMenu = ref(false)

const filteredProjects = computed(() =>
  (props.projects || []).filter(p =>
    (p?.name || "").toLowerCase().includes(search.value.toLowerCase())
  )
)

const filteredTasks = computed(() =>
  (props.tasks || []).filter(t =>
    (t?.title || "").toLowerCase().includes(search.value.toLowerCase())
  )
)

const unreadNotifications = computed(() =>
  (props.notifications || [])
    .filter(n => !n.is_read)
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
)

const unreadCount = computed(() => unreadNotifications.value.length)

const completionRate = computed(() => {
  const total = props.stats?.assignedTasks || 0
  const completed = props.stats?.completedTasks || 0
  return total ? Math.round((completed / total) * 100) : 0
})

const handleClickOutside = (event) => {
  if (!event?.target) return

  const panel = event.target.closest?.(".notification-dropdown-panel")
  const bell = event.target.closest?.(".notification-bell-container")

  if (!panel && !bell) {
    showProfileMenu.value = false
    if (notificationStore) notificationStore.showBellDropdown = false
  }
}

const markAsRead = (id) => {
  router.put(`/notifications/${id}/read`, {}, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ["notifications"] })
  })
}

const logout = () => {
  router.post("/logout", {}, {
    onSuccess: () => (window.location.href = "/login")
  })
}

const formatNotificationDate = (date) => {
  if (!date) return ""
  const now = new Date()
  const created = new Date(date)
  const diff = Math.floor((now - created) / 60000)

  if (diff < 1) return "Just now"
  if (diff < 60) return `${diff} min ago`

  const hours = Math.floor(diff / 60)
  if (hours < 24) return `${hours} hr ago`

  return created.toLocaleDateString()
}

let interval = null

onMounted(() => {
  document.addEventListener("click", handleClickOutside)

  interval = setInterval(() => {
    router.reload({ only: ["notifications"] })
  }, 30000)
})

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside)
  if (interval) clearInterval(interval)
})
</script>
<style scoped>
.dashboard {
  display: flex;
  height: 100vh;
  width: 100vw;
  background: #0b1220;
  color: #e5e7eb;
  font-family: Inter, system-ui, sans-serif;
  overflow: hidden;
}

.main-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}

.content-wrapper {
  max-width: 1500px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-welcome h1 {
  font-size: 28px;
  font-weight: 800;
  margin: 0;
}

.header-welcome p {
  font-size: 13px;
  opacity: 0.7;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.04);
  color: #fff;
  outline: none;
  width: 240px;
}

.theme-btn,
.icon-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.04);
  color: #fff;
  cursor: pointer;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}

.stat-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 16px;
}

.stat-label {
  font-size: 11px;
  opacity: 0.6;
  text-transform: uppercase;
}

.stat-value {
  font-size: 22px;
  font-weight: 800;
  margin-top: 6px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.dashboard-grid-secondary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.panel {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 16px;
}

.panel-header h2 {
  font-size: 15px;
  margin-bottom: 12px;
}

.project-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.project-card {
  padding: 14px;
  border-radius: 12px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
}

.progress-bar {
  height: 6px;
  background: rgba(255,255,255,0.06);
  border-radius: 20px;
  overflow: hidden;
  margin-top: 10px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #22c55e);
}

.team-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: 10px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
  margin-bottom: 10px;
}

.avatar-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.task-card {
  padding: 12px;
  border-radius: 12px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
  margin-bottom: 10px;
}

.task-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.task-status {
  font-size: 10px;
  padding: 4px 8px;
  border-radius: 999px;
  background: rgba(34,197,94,0.15);
  color: #22c55e;
}

.task-meta {
  font-size: 12px;
  opacity: 0.7;
}

.task-priority {
  font-size: 10px;
  margin-top: 6px;
  display: inline-block;
  padding: 4px 8px;
  border-radius: 999px;
}

.task-priority.high {
  background: rgba(239,68,68,0.15);
  color: #ef4444;
}

.task-priority.medium {
  background: rgba(245,158,11,0.15);
  color: #f59e0b;
}

.task-priority.low {
  background: rgba(59,130,246,0.15);
  color: #3b82f6;
}

.history-grid,
.history-list,
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.history-card,
.history-item,
.activity-item {
  padding: 12px;
  border-radius: 12px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
}

.progress-overview {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.progress-circle {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  border: 6px solid rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}

.notification-dropdown-panel {
  position: absolute;
  right: 0;
  top: 50px;
  width: 320px;
  background: rgba(20,24,35,0.95);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  backdrop-filter: blur(12px);
  z-index: 1000;
}

.notification-dropdown-body {
  max-height: 320px;
  overflow-y: auto;
}

.notification-item {
  padding: 10px;
  border-radius: 10px;
  margin-bottom: 8px;
  background: rgba(255,255,255,0.03);
  cursor: pointer;
}

.notification-item:hover {
  background: rgba(255,255,255,0.06);
}

.bell-alert-green-dot {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #22c55e;
  color: #000;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 999px;
}
</style>