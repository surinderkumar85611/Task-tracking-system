<template>
  <div class="dashboard" :class="theme.themeClass">

    <Sidebar />

    <main class="main-content">

      <!-- HEADER -->
      <header class="header">
        <div>
          <h1>Team Overview</h1>
          <p>Manage team leaders, members and task assignments.</p>
        </div>
      </header>

      <!-- LEADER CARDS -->
      <section class="leader-grid">

        <div
          v-for="leader in teamLeaders"
          :key="leader.id"
          class="leader-card"
        >

          <!-- LEADER HEADER -->
          <div class="leader-header">
            <div class="avatar">
              {{ initials(leader.first_name, leader.last_name) }}
            </div>

            <div>
              <h3>{{ leader.first_name }} {{ leader.last_name }}</h3>
              <small>{{ leader.department }}</small>
            </div>
          </div>

          <!-- MEMBERS -->
          <div class="members-section">

            <h4>Team Members</h4>

            <div v-if="leader.team_members?.length">

              <div
                v-for="member in leader.team_members"
                :key="member.id"
                class="member-card"
              >

                <!-- MEMBER HEADER -->
                <div class="member-header">
                  <div class="member-avatar">
                    {{ initials(member.first_name, member.last_name) }}
                  </div>

                  <div>
                    <p class="name">
                      {{ member.first_name }} {{ member.last_name }}
                    </p>
                    <small>{{ member.department }}</small>
                  </div>
                </div>

                <!-- TASKS -->
                <div class="tasks">

                  <div
                    v-if="member.assignedTasks?.length"
                    v-for="task in member.assignedTasks"
                    :key="task.id"
                    class="task-item"
                  >

                    <div>
                      <p class="task-title">{{ task.title }}</p>

                      <span class="task-status" :class="task.status">
                        {{ formatStatus(task.status) }}
                      </span>
                    </div>

                    <div class="deadline">
                      {{ formatDate(task.deadline) }}
                    </div>

                  </div>

                  <div v-else class="no-tasks">
                    No tasks assigned
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

    </main>
  </div>
</template>

<script setup>
import Sidebar from "./Sidebar.vue"
import { useThemeStore } from "@/stores/theme"

const theme = useThemeStore()

const props = defineProps({
  teamLeaders: Array,
  members: Array
})

const teamLeaders = props.teamLeaders || []

const initials = (first, last) => {
  return (first?.[0] || "") + (last?.[0] || "")
}

const formatStatus = (status) => {
  if (!status) return "Todo"
  return status.replace("_", " ").toUpperCase()
}

const formatDate = (date) => {
  if (!date) return "-"
  return new Date(date).toLocaleDateString()
}
</script>

<style scoped>

.main-content {
  padding: 25px;
}

/* GRID */
.leader-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 20px;
}

/* LEADER CARD */
.leader-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 16px;
  transition: 0.2s ease;
}

.leader-card:hover {
  transform: translateY(-3px);
}

/* HEADER */
.leader-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
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

/* MEMBERS */
.members-section h4 {
  margin: 10px 0;
  font-size: 14px;
  opacity: 0.8;
}

.member-card {
  background: var(--bg);
  border: 1px solid var(--border);
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 10px;
}

/* MEMBER HEADER */
.member-header {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 8px;
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

/* TASKS */
.task-item {
  display: flex;
  justify-content: space-between;
  padding: 8px;
  border-radius: 6px;
  background: rgba(255,255,255,0.04);
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

/* STATUS COLORS */
.todo { background: #f59e0b33; color: #f59e0b; }
.in_progress { background: #3b82f633; color: #3b82f6; }
.completed { background: #22c55e33; color: #22c55e; }

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

</style>