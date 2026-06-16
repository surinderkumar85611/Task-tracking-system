<template>

<Head title="Team" />

  <div class="dashboard" :class="{ 'dark': theme.isDark, 'light': !theme.isDark }">
    <Sidebar />

    <main class="main-content">
      <header class="header">
        <div class="header-welcome">
          <h1>Team Overview</h1>
          <p>Manage team leaders, members and task assignments.</p>
        </div>

        <div class="header-right">
          <input v-model="search" type="text" placeholder="Search team..." class="search-box" />

          <button class="theme-btn" @click="theme.toggleTheme">
            {{ theme.isDark ? '☀️' : '🌙' }}
          </button>

          <div class="profile-container" ref="profileContainer">
            <img src="https://i.pravatar.cc/100" class="avatar" @click.stop="showProfileMenu = !showProfileMenu" />
            <div v-if="showProfileMenu" class="profile-dropdown">
              <button @click="logout">Logout</button>
            </div>
          </div>
        </div>
      </header>

      <section class="dashboard-card request-section">
        <div class="request-header">
          <div>
            <h2>Team Requests</h2>
            <p class="request-box">
              Send requests to admin for adding/removing members, defining eligibility and department requirements.
            </p>
          </div>
          <button class="request-btn" @click="showRequestModal = true">
            <span>+</span> New Request
          </button>
        </div>
      </section>

      <section class="leader-grid">
        <div v-for="leader in teamLeaders" :key="leader.id" class="leader-card">
          <div class="leader-header">
            <div class="leader-avatar-box">
              {{ initials(leader.first_name, leader.last_name) }}
            </div>
            <div>
              <h3 v-html="highlightText(`${leader.first_name} ${leader.last_name}`)"></h3>
              <span class="department-badge" v-html="highlightText(leader.department)"></span>
            </div>
          </div>

          <div class="members-section">
            <h4>Team Members</h4>

            <div v-if="leader.team_members?.length" class="members-grid">
              <div v-for="member in leader.team_members" :key="member.id" class="member-container">
                <div class="member-card">
                  <div class="member-header">
                    <div class="member-avatar">
                      {{ initials(member.first_name, member.last_name) }}
                    </div>
                    <div>
                      <p class="name" v-html="highlightText(`${member.first_name} ${member.last_name}`)"></p>
                      <small class="member-dept" v-html="highlightText(member.department)"></small>
                    </div>
                  </div>

                  <div class="tasks">
                    <div v-if="getMemberTasks(member).length">
                      <div v-for="task in getMemberTasks(member)" :key="task.id" class="task-item">
                        <div class="task-info">
                          <p class="task-title" v-html="highlightText(task.title)"></p>
                          <span class="task-status" :class="task.status">
                            {{ formatStatus(task.status) }}
                          </span>
                        </div>
                        <div class="deadline">
                          {{ formatDate(task.due_date || task.deadline) }}
                        </div>
                      </div>
                    </div>
                    <div v-else class="no-tasks">
                      No tasks assigned
                    </div>
                  </div>
                </div>

                <div class="performance-card">
                  <h5>Performance Analysis</h5>
                  <div class="performance-content">
                    <div class="pie-chart" :style="getPieChartStyle(getMemberStats(member))">
                      <div class="pie-center">
                        <span>
                          {{ getMemberStats(member).completed }}
                          /
                          {{ getMemberStats(member).total }}
                        </span>
                      </div>
                    </div>

                    <div class="stats-legend">
                      <div class="legend-item">
                        <span class="dot total-dot"></span>
                        <span class="legend-label">Assigned:</span>
                        <strong>{{ getMemberStats(member).total }}</strong>
                      </div>
                      <div class="legend-item">
                        <span class="dot progress-dot"></span>
                        <span class="legend-label">In Progress:</span>
                        <strong>{{ getMemberStats(member).inProgress }}</strong>
                      </div>
                      <div class="legend-item">
                        <span class="dot completed-dot"></span>
                        <span class="legend-label">Completed:</span>
                        <strong>{{ getMemberStats(member).completed }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="empty">
              No team members assigned yet
            </div>
          </div>
        </div>
      </section>

      <div v-if="showRequestModal" class="modal-overlay" @click="showRequestModal = false">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h3>Team Request</h3>
            <p>Send a request to admin for team changes</p>
          </div>

          <div class="modal-body">
            <div class="form-group">
              <label>Request Type</label>
              <select v-model="request.type">
                <option value="add">Add Member</option>
                <option value="remove">Remove Member</option>
              </select>
            </div>

            <div class="form-group">
              <label>Member Email</label>
              <input v-model="request.member_email" placeholder="Enter email..." />
            </div>

            <div class="form-group">
              <label>Department</label>
              <select v-model="request.department">
                <option value="">Select Department</option>
                <option value="Web Developer">Web Developer</option>
                <option value="PHP Developer">PHP Developer</option>
                <option value="Web Designer">Web Designer</option>
                <option value="Full Stack Developer">Full Stack Developer</option>
              </select>
            </div>

            <div class="form-group">
              <label>Eligibility Criteria</label>
              <input v-model="request.eligibility" placeholder="e.g. 2+ years Vue experience" />
            </div>

            <div class="form-group">
              <label>Reason</label>
              <textarea v-model="request.reason" placeholder="Explain your request..."></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-secondary" @click="showRequestModal = false">
              Cancel
            </button>
            <button class="btn-primary" @click="submitRequest">
              Send Request
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import Sidebar from "./Sidebar.vue"
import { useThemeStore } from "@/stores/theme"
import { ref, computed, onMounted, onBeforeUnmount } from "vue"
import { router } from "@inertiajs/vue3"
import { useToast } from "vue-toastification"
import { Head } from '@inertiajs/vue3';

const toast = useToast()
const theme = useThemeStore()
const requests = ref([])
const search = ref("")
const showProfileMenu = ref(false)

const logout = () => {
  router.post("/logout")
}
const profileContainer = ref(null)
const props = defineProps({
  teamLeaders: Array,
  members: Array,
  projects: Array
})

const getAllTasks = () => {
  return props.projects?.flatMap(p => p.tasks || []) || []
}

const normalizeMemberId = (id) => {
  if (Array.isArray(id)) return id[0]
  if (!id || id === 0) return null
  return Number(id)
}

const getMemberTasks = (member) => {
  const allTasks = getAllTasks()

  return allTasks.filter(task => {
    const taskMemberId = normalizeMemberId(task.member_id)
    return taskMemberId === Number(member.id)
  })
}

const teamLeaders = computed(() => {
  if (!search.value.trim()) {
    return props.teamLeaders || []
  }

  const term = search.value.toLowerCase()

  return (props.teamLeaders || [])
    .map(leader => ({
      ...leader,
      team_members: (leader.team_members || []).filter(member => {

        const memberName =
          `${member.first_name || ""} ${member.last_name || ""}`.toLowerCase()

        const memberDept =
          (member.department || "").toLowerCase()

        const taskMatch = getMemberTasks(member).some(task =>
          (task.title || "").toLowerCase().includes(term)
        )

        return (
          memberName.includes(term) ||
          memberDept.includes(term) ||
          taskMatch
        )
      })
    }))
    .filter(leader => {

      const leaderName =
        `${leader.first_name || ""} ${leader.last_name || ""}`.toLowerCase()

      const leaderDept =
        (leader.department || "").toLowerCase()

      return (
        leaderName.includes(term) ||
        leaderDept.includes(term) ||
        leader.team_members.length > 0
      )
    })
})

const showRequestModal = ref(false)

const request = ref({
  type: "add",
  member_email: "",
  department: "",
  eligibility: "",
  reason: ""
})
const submitRequest = () => {
  router.post("/team/request", request.value, {
    preserveScroll: true,

    onSuccess: () => {
      toast.success("Request sent successfully to admin")
      showRequestModal.value = false

      request.value = {
        type: "add",
        member_email: "",
        department: "",
        eligibility: "",
        reason: ""
      }
    },

    onError: () => {
      toast.error("Failed to send request")
    }
  })
}
const getMemberStats = (member) => {
  const tasks = getMemberTasks(member)

  return {
    total: tasks.length,
    completed: tasks.filter(t =>
      String(t.status).toLowerCase() === "completed"
    ).length,

    inProgress: tasks.filter(t =>
      ["in progress", "in_progress"].includes(
        String(t.status).toLowerCase()
      )
    ).length,

    todo: tasks.filter(t =>
      ["todo", ""].includes(
        String(t.status).toLowerCase()
      )
    ).length
  }
}

const getPieChartStyle = (stats) => {
  if (stats.total === 0) {
    return {
      background: "var(--empty-pie)"
    }
  }

  const completedPct =
    (stats.completed / stats.total) * 360

  const progressPct =
    (stats.inProgress / stats.total) * 360 +
    completedPct

  return {
    background: `conic-gradient(
      #10b981 0deg ${completedPct}deg,
      #3b82f6 ${completedPct}deg ${progressPct}deg,
      #f59e0b ${progressPct}deg 360deg
    )`
  }
}

const initials = (first, last) => {
  return (first?.[0] || "") + (last?.[0] || "")
}

const formatStatus = (status) => {
  if (!status) return "Todo"
  return String(status)
    .replace("_", " ")
    .toUpperCase()
}

const formatDate = (date) => {
  if (!date) return "-"
  return new Date(date).toLocaleDateString()
}

const highlightText = (text) => {
  if (!text) return ""

  if (!search.value.trim()) {
    return text
  }

  const escaped = search.value.replace(
    /Ref[-\/\\^$*+?.()|[\]{}]/g,
    "\\$&"
  )

  return String(text).replace(
    new RegExp(`(${escaped})`, "gi"),
    '<span class="search-highlight">$1</span>'
  )
}
const handleClickOutside = (event) => {
  if (
    profileContainer.value &&
    !profileContainer.value.contains(event.target)
  ) {
    showProfileMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener("click", handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside)
})
</script>

<style scoped>
/* ==========================================================================
   Dynamic Theme Variable Tokens
   ========================================================================== */
.dashboard {
  /* Default Dark Mode Color Definitions */
  --bg-main: #0b0f19;
  --text-main: #f1f5f9;
  --text-heading: #ffffff;
  --text-muted: #64748b;
  --card-gradient-start: rgba(15, 22, 36, 0.5);
  --card-gradient-end: rgba(11, 17, 30, 0.7);
  --nested-card: rgba(255, 255, 255, 0.02);
  --border-subtle: rgba(255, 255, 255, 0.04);
  --input-bg: rgba(17, 24, 39, 0.6);
  --dropdown-bg: #111827;
  --empty-pie: rgba(255, 255, 255, 0.05);
  --heading-gradient: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
  --shadow-color: rgba(0, 0, 0, 0.5);
}

/* Explicit Rule for when light class is applied to the main wrapper */
.dashboard.light {
  --bg-main: #f8fafc;
  --text-main: #334155;
  --text-heading: #0f172a;
  --text-muted: #64748b;
  --card-gradient-start: #ffffff;
  --card-gradient-end: #f1f5f9;
  --nested-card: #ffffff;
  --border-subtle: rgba(0, 0, 0, 0.06);
  --input-bg: #ffffff;
  --dropdown-bg: #ffffff;
  --empty-pie: rgba(0, 0, 0, 0.05);
  --heading-gradient: linear-gradient(135deg, #0f172a 0%, #334155 100%);
  --shadow-color: rgba(15, 23, 42, 0.06);
}

/* Explicit Rule for when dark class is applied to the main wrapper */
.dashboard.dark {
  --bg-main: #0b0f19;
  --text-main: #f1f5f9;
  --text-heading: #ffffff;
  --text-muted: #64748b;
  --card-gradient-start: rgba(15, 22, 36, 0.5);
  --card-gradient-end: rgba(11, 17, 30, 0.7);
  --nested-card: rgba(255, 255, 255, 0.02);
  --border-subtle: rgba(255, 255, 255, 0.04);
  --input-bg: rgba(17, 24, 39, 0.6);
  --dropdown-bg: #111827;
  --empty-pie: rgba(255, 255, 255, 0.05);
  --heading-gradient: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
  --shadow-color: rgba(0, 0, 0, 0.5);
}

/* ==========================================================================
   Structural & Component Layout
   ========================================================================== */
.dashboard {
  display: flex;
  min-height: 100vh;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  background-color: var(--bg-main);
  color: var(--text-main);
  transition: background-color 0.25s ease, color 0.25s ease;
}

.main-content {
  flex: 1;
  padding: 40px;
  overflow-y: auto;
  max-width: 1600px;
  margin: 0 auto;
  width: 100%;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
}

.header h1 {
  font-size: 32px;
  font-weight: 800;
  margin: 0 0 6px 0;
  letter-spacing: -0.75px;
  background: var(--heading-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.header p {
  color: var(--text-muted);
  font-weight: 500;
  margin: 0;
  font-size: 15px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  width: 280px;
  padding: 12px 18px;
  border-radius: 14px;
  border: 1px solid var(--border-subtle);
  background: var(--input-bg);
  color: var(--text-heading);
  outline: none;
  backdrop-filter: blur(8px);
  transition: all 0.3s ease;
}

.search-box:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}

.theme-btn {
  background: var(--input-bg);
  border: 1px solid var(--border-subtle);
  color: var(--text-heading);
  width: 46px;
  height: 46px;
  border-radius: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(8px);
  transition: all 0.2s ease;
}

.theme-btn:hover {
  background: var(--border-subtle);
}

.profile-container {
  position: relative;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  cursor: pointer;
  border: 1px solid var(--border-subtle);
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 55px;
  background: var(--dropdown-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 12px;
  overflow: hidden;
  z-index: 10;
  box-shadow: 0 10px 25px var(--shadow-color);
}

.profile-dropdown button {
  background: transparent;
  border: none;
  color: var(--text-heading);
  padding: 12px 24px;
  cursor: pointer;
  width: 100%;
  text-align: left;
  font-size: 14px;
  font-weight: 500;
}

.profile-dropdown button:hover {
  background: var(--border-subtle);
}

.dashboard-card {
  background: linear-gradient(145deg, var(--card-gradient-start) 0%, var(--card-gradient-end) 100%);
  border: 1px solid var(--border-subtle);
  border-radius: 24px;
  padding: 32px;
  box-shadow: 0 20px 40px -20px var(--shadow-color);
  backdrop-filter: blur(12px);
}

.request-section {
  margin-bottom: 32px;
  border-left: 4px solid #3b82f6;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
}

.request-header h2 {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 6px 0;
  color: var(--text-heading);
}

.request-box {
  margin: 0;
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
}

.request-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border: none;
  border-radius: 14px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  white-space: nowrap;
}

.request-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.request-btn span {
  font-size: 18px;
  line-height: 1;
}

.leader-grid {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.leader-card {
  background: linear-gradient(145deg, var(--card-gradient-start) 0%, var(--card-gradient-end) 100%);
  border: 1px solid var(--border-subtle);
  border-radius: 24px;
  padding: 32px;
  box-shadow: 0 20px 40px -20px var(--shadow-color);
}

.leader-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border-subtle);
}

.leader-avatar-box {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
}

.leader-header h3 {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 6px 0;
  color: var(--text-heading);
}

.department-badge {
  font-size: 12px;
  font-weight: 600;
  color: #6366f1;
  background: rgba(99, 102, 241, 0.12);
  padding: 4px 10px;
  border-radius: 8px;
}

.members-section h4 {
  margin: 0 0 20px 0;
  font-size: 15px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.75px;
  color: var(--text-muted);
}

.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
}

.member-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.member-card, .performance-card {
  background: var(--nested-card);
  border: 1px solid var(--border-subtle);
  border-radius: 18px;
  padding: 20px;
  box-shadow: 0 4px 20px var(--shadow-color);
}

.member-card {
  border-left: 4px solid #06b6d4;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.performance-card {
  border-left: 4px solid #8b5cf6;
}

.member-header {
  display: flex;
  gap: 14px;
  align-items: center;
  margin-bottom: 16px;
}

.member-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.25);
}

.member-header .name {
  font-size: 15px;
  font-weight: 600;
  color: var(--text-heading);
  margin: 0 0 2px 0;
}

.member-dept {
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 500;
}

.tasks {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.task-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border-radius: 12px;
  background: var(--bg-main);
  border: 1px solid var(--border-subtle);
}

.task-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.task-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-heading);
  margin: 0;
}

.task-status {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  width: fit-content;
}

.task-status.todo {
  background: rgba(245, 158, 11, 0.12);
  color: #f59e0b;
}

.task-status.in_progress, .task-status.in-progress {
  background: rgba(59, 130, 246, 0.12);
  color: #3b82f6;
}

.task-status.completed {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}

.deadline {
  font-size: 12px;
  font-weight: 500;
  color: var(--text-muted);
}

.performance-card h5 {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-heading);
}

.performance-content {
  display: flex;
  align-items: center;
  gap: 24px;
}

.pie-chart {
  width: 74px;
  height: 74px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
  box-shadow: 0 4px 14px var(--shadow-color);
}

.pie-center {
  width: 50px;
  height: 50px;
  background: var(--dropdown-bg);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: var(--text-heading);
}

.stats-legend {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.legend-item {
  display: flex;
  align-items: center;
  font-size: 13px;
}

.legend-label {
  color: var(--text-muted);
  font-weight: 500;
  margin-right: 4px;
}

.legend-item strong {
  color: var(--text-heading);
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 10px;
  flex-shrink: 0;
}

.total-dot { background: #f59e0b; box-shadow: 0 0 8px rgba(245, 158, 11, 0.4); }
.progress-dot { background: #3b82f6; box-shadow: 0 0 8px rgba(59, 130, 246, 0.4); }
.completed-dot { background: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4); }

.no-tasks, .empty {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
  text-align: center;
  padding: 12px;
  border: 1px dashed var(--border-subtle);
  border-radius: 12px;
}

.empty {
  padding: 24px;
}

:deep(.search-highlight) {
  background: rgba(250, 204, 21, 0.2);
  color: #eab308;
  padding: 1px 4px;
  border-radius: 4px;
  font-weight: 600;
}

/* ==========================================================================
   Modals & Overlays
   ========================================================================== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  display: flex;
  justify-content: center;
  align-items: center;
  backdrop-filter: blur(6px);
  z-index: 999;
}

.modal {
  width: 440px;
  background: var(--dropdown-bg);
  border: 1px solid var(--border-subtle);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px var(--shadow-color);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid var(--border-subtle);
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: var(--text-heading);
}

.modal-header p {
  margin: 6px 0 0;
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid var(--border-subtle);
  background: var(--bg-main);
  color: var(--text-heading);
  font-size: 14px;
  outline: none;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}

.form-group textarea {
  min-height: 100px;
  resize: none;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 18px 24px;
  border-top: 1px solid var(--border-subtle);
  background: var(--card-gradient-end);
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
  background: transparent;
  border: 1px solid var(--border-subtle);
  color: var(--text-muted);
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--border-subtle);
  color: var(--text-heading);
}
</style>
