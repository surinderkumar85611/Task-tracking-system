<template>
  <div class="dashboard" :class="theme.themeClass">

    <Sidebar />

    <main class="main-content">

      <header class="header">

        <div>
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
              <button @click="logout">
                Logout
              </button>
            </div>

          </div>

        </div>

      </header>


      <section class="request-section">

        <div class="request-header">
          <h2>Team Requests</h2>

          <button class="request-btn" @click="showRequestModal = true">
            + New Request
          </button>
        </div>

        <div class="request-box">
          <p>
            Send requests to admin for adding/removing members, defining eligibility and department requirements.
          </p>
        </div>

      </section>
      <section class="leader-grid">

        <div v-for="leader in teamLeaders" :key="leader.id" class="leader-card">

          <div class="leader-header">
            <div class="avatar">
              {{ initials(leader.first_name, leader.last_name) }}
            </div>

            <div>
              <h3 v-html="highlightText(`${leader.first_name} ${leader.last_name}`)"></h3>
              <small v-html="highlightText(leader.department)"></small>
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
                      <small v-html="highlightText(member.department)"></small>
                    </div>
                  </div>

                  <div class="tasks">

                    <div v-if="getMemberTasks(member).length">

                      <div v-for="task in getMemberTasks(member)" :key="task.id" class="task-item">
                        <div>
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
                        Assigned:
                        <strong>{{ getMemberStats(member).total }}</strong>
                      </div>

                      <div class="legend-item">
                        <span class="dot progress-dot"></span>
                        In Progress:
                        <strong>{{ getMemberStats(member).inProgress }}</strong>
                      </div>

                      <div class="legend-item">
                        <span class="dot completed-dot"></span>
                        Completed:
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
      background: "rgba(255,255,255,0.1)"
    }
  }

  const completedPct =
    (stats.completed / stats.total) * 360

  const progressPct =
    (stats.inProgress / stats.total) * 360 +
    completedPct

  return {
    background: `conic-gradient(
      #22c55e 0deg ${completedPct}deg,
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
    /[-\/\\^$*+?.()|[\]{}]/g,
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
.main-content {
  padding: 25px;
}

.leader-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

.leader-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 20px;
  transition: 0.2s ease;
}

.leader-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #3b82f6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.members-section h4 {
  margin: 0 0 14px 0;
  font-size: 14px;
  opacity: 0.8;
}

.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.member-container {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.member-card {
  background: var(--bg);
  border: 1px solid var(--border);
  padding: 16px;
  border-radius: 10px;
  height: 100%;
}

.member-header {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 12px;
}

.member-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 12px;
}

.performance-card {
  background: var(--bg);
  border: 1px solid var(--border);
  padding: 16px;
  border-radius: 10px;
}

.performance-card h5 {
  margin: 0 0 12px 0;
  font-size: 13px;
  opacity: 0.9;
}

.performance-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.pie-chart {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
}

.pie-center {
  width: 46px;
  height: 46px;
  background: var(--bg, #111827);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: bold;
}

.stats-legend {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.total-dot {
  background: #f59e0b;
}

.progress-dot {
  background: #3b82f6;
}

.completed-dot {
  background: #22c55e;
}

.task-item {
  display: flex;
  justify-content: space-between;
  padding: 8px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  margin-top: 6px;
  align-items: center;
}

.task-title {
  font-size: 13px;
  font-weight: 500;
}

.task-status {
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 6px;
  margin-top: 4px;
  display: inline-block;
}

.todo {
  background: #f59e0b33;
  color: #f59e0b;
}

.in_progress {
  background: #3b82f633;
  color: #3b82f6;
}

.completed {
  background: #22c55e33;
  color: #22c55e;
}

.deadline {
  font-size: 11px;
  opacity: 0.6;
}

.no-tasks,
.empty {
  font-size: 12px;
  opacity: 0.6;
  padding: 6px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box {
  width: 260px;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--card);
  color: inherit;
}

.theme-btn {
  border: none;
  cursor: pointer;
  border-radius: 8px;
  padding: 10px;
  background: var(--card);
}

.profile-container {
  position: relative;
}

.profile-dropdown {
  position: absolute;
  top: 52px;
  right: 0;
  min-width: 140px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 8px;
  overflow: hidden;
  z-index: 999;
}

.profile-dropdown button {
  width: 100%;
  padding: 12px;
  border: none;
  background: transparent;
  color: inherit;
  cursor: pointer;
  text-align: left;
}

.profile-dropdown button:hover {
  background: rgba(255, 255, 255, 0.06);
}

:deep(.search-highlight) {
  background: #facc15;
  color: #000;
  padding: 1px 3px;
  border-radius: 3px;
  font-weight: 600;
}

.request-section {
  margin: 20px 0;
  padding: 16px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 10px;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.request-btn {
  padding: 6px 12px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.request-box {
  margin-top: 10px;
  font-size: 13px;
  opacity: 0.7;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background: var(--card);
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: flex;
  justify-content: center;
  align-items: center;
  backdrop-filter: blur(6px);
  z-index: 999;
}

.modal {
  width: 420px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.modal-header {
  padding: 16px;
  border-bottom: 1px solid var(--border);
}

.modal-header h3 {
  margin: 0;
  font-size: 16px;
}

.modal-header p {
  margin: 4px 0 0;
  font-size: 12px;
  opacity: 0.7;
}

.modal-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  opacity: 0.8;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg);
  color: inherit;
  font-size: 13px;
  outline: none;
}

.form-group textarea {
  min-height: 80px;
  resize: none;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 14px 16px;
  border-top: 1px solid var(--border);
}

.btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
}

.btn-secondary {
  background: transparent;
  border: 1px solid var(--border);
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
}
</style>