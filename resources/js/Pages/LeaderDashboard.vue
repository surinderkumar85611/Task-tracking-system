<template>
  <div class="dashboard" :class="theme.themeClass">

    <Sidebar />

    <main class="main-content">

      <!-- HEADER -->
      <header class="header">

        <div>
          <h1>Leader Dashboard</h1>
          <p>
            Welcome back,
            {{ leader?.first_name }}
            {{ leader?.last_name }}
          </p>
        </div>

        <div class="header-right">

          <input
            v-model="search"
            type="text"
            placeholder="Search projects..."
            class="search-box"
          />

          <button
            class="theme-btn"
            @click="theme.toggleTheme"
          >
            {{ theme.isDark ? '☀️' : '🌙' }}
          </button>

          <div class="profile-container">

            <img
              src="https://i.pravatar.cc/100"
              class="avatar"
              @click.stop="showProfileMenu=!showProfileMenu"
            />

            <div
              v-if="showProfileMenu"
              class="profile-dropdown"
            >
              <button @click="logout">
                Logout
              </button>
            </div>

          </div>

        </div>

      </header>

      <!-- KPI CARDS -->
      <section class="stats-grid">

        <div class="stat-card">
          <span>Total Projects</span>
          <h2>{{ stats.projects }}</h2>
        </div>

        <div class="stat-card">
          <span>Total Tasks</span>
          <h2>{{ stats.tasks }}</h2>
        </div>

        <div class="stat-card success">
          <span>Completed Tasks</span>
          <h2>{{ stats.completed }}</h2>
        </div>

        <div class="stat-card warning">
          <span>Pending Tasks</span>
          <h2>{{ stats.pending }}</h2>
        </div>

        <div class="stat-card info">
          <span>Completion Rate</span>
          <h2>{{ completionRate }}%</h2>
        </div>

        <div class="stat-card danger">
          <span>Overdue Tasks</span>
          <h2>{{ overdueTasks }}</h2>
        </div>

      </section>

      <!-- QUICK ACTIONS -->
      <section class="dashboard-card">

        <div class="card-header">
          <h2>Quick Actions</h2>
        </div>

        <div class="quick-actions">


          <button class="quick-btn success">
            Assign Task
          </button>

          <button class="quick-btn warning">
            Export Report
          </button>

        </div>

      </section>

      <!-- PROJECT + TEAM GRID -->
      <section class="top-grid">

        <!-- PROJECT STATUS -->
        <div class="dashboard-card">

          <div class="card-header">
            <h2>Project Progress</h2>
          </div>

          <div
            v-for="project in filteredProjects"
            :key="project.id"
            class="project-progress-card"
          >

            <div class="project-progress-header">

              <div>
                <h3>{{ project.name }}</h3>

                <small>
                  Deadline:
                  {{ project.deadline }}
                </small>
              </div>

              <span class="project-percentage">
                {{ project.progress || 0 }}%
              </span>

            </div>

            <div class="progress-bar">
              <div
                class="progress-fill"
                :style="{
                  width: (project.progress || 0) + '%'
                }"
              ></div>
            </div>

          </div>

        </div>

        <!-- TEAM OVERVIEW -->
        <div class="dashboard-card">

          <div class="card-header">
            <h2>Team Members</h2>
          </div>

          <div
            v-for="member in teamMembers"
            :key="member.id"
            class="member-card"
          >

            <div class="member-avatar">
              {{
                member.first_name
                ?.charAt(0)
                .toUpperCase()
              }}
            </div>

            <div class="member-info">

              <h4>
                {{ member.first_name }}
                {{ member.last_name }}
              </h4>

              <p>
                {{ member.department }}
              </p>

            </div>

          </div>

        </div>

      </section>

      <!-- TEAM WORKLOAD -->
      <section class="dashboard-card">

        <div class="card-header">
          <h2>Team Workload</h2>
        </div>

        <div class="workload-grid">

          <div
            v-for="member in teamWorkload"
            :key="member.id"
            class="workload-card"
          >

            <div class="member-avatar">
              {{
                member.first_name
                  .charAt(0)
              }}
            </div>

            <div class="workload-info">

              <h4>
                {{ member.first_name }}
                {{ member.last_name }}
              </h4>

              <p>
                {{ member.taskCount }}
                Assigned Tasks
              </p>

            </div>

          </div>

        </div>

      </section>
            <!-- RECENT ACTIVITY -->
      <section class="dashboard-card">

        <div class="card-header">
          <h2>Recent Activity</h2>
        </div>

        <div class="activity-feed">

          <template
            v-for="project in projects"
            :key="project.id"
          >

            <div
              v-for="task in project.tasks"
              :key="task.id"
              class="activity-item"
            >

              <div class="activity-dot"></div>

              <div>

                <strong>
                  {{ task.title }}
                </strong>

                <p>
                  {{ task.status }}
                </p>

              </div>

            </div>

          </template>

        </div>

      </section>

      <!-- TASK BOARD -->
      <section class="dashboard-card">

        <div class="card-header">
          <h2>Project Tasks</h2>
        </div>

        <div class="table-wrapper">

          <table class="project-table">

            <thead>
              <tr>
                <th>Task</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Deadline</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>

              <template
                v-for="project in filteredProjects"
                :key="project.id"
              >

                <tr class="project-row">
                  <td colspan="5">
                    {{ project.name }}
                  </td>
                </tr>

                <tr
                  v-for="task in project.tasks"
                  :key="task.id"
                >

                  <td>
                    {{ task.title }}
                  </td>

                  <td>

                    <select
                      v-model="task.status"
                      @change="syncTask(task)"
                    >
                      <option>Todo</option>
                      <option>In Progress</option>
                      <option>Completed</option>
                    </select>

                  </td>

                  <td>

                    <span
                      class="priority-badge"
                      :class="{
                        low: task.priority === 'Low',
                        medium: task.priority === 'Medium',
                        high: task.priority === 'High'
                      }"
                    >
                      {{ task.priority }}
                    </span>

                  </td>

                  <td>
                    {{ task.due_date }}
                  </td>

                  <td>

                    <button
                      class="update-btn"
                      @click="
                        openUpdatesSidebar(
                          task,
                          project
                        )
                      "
                    >
                      Updates
                    </button>

                  </td>

                </tr>

              </template>

            </tbody>

          </table>

        </div>

      </section>

      <!-- TASK UPDATE SIDEBAR -->

      <div
        class="updates-sidebar-overlay"
        :class="{
          open:
          showUpdatesSidebarPane
        }"
        @click="closeUpdatesSidebar"
      >

        <div
          class="updates-sidebar-panel"
          @click.stop
        >

          <div
            class="sidebar-panel-header"
          >

            <div>

              <h2>
                {{
                  activeTaskForUpdates?.title
                }}
              </h2>

              <p>
                {{
                  activeProjectForUpdates?.name
                }}
              </p>

            </div>

            <button
              class="close-panel-btn"
              @click="
                closeUpdatesSidebar
              "
            >
              ✕
            </button>

          </div>

          <div
            class="sidebar-panel-body"
          >

            <div
              v-if="
              activeTaskForUpdates?.notes &&
              activeTaskForUpdates.notes.length
              "
            >

              <div
                v-for="(
                note,
                index
                ) in activeTaskForUpdates.notes"
                :key="index"
                class="chat-bubble-card"
              >

                <div
                  class="chat-bubble-meta"
                >

                  <span>
                    {{ note.sender }}
                  </span>

                  <span>
                    {{
                      formatDate(
                        note.created_at
                      )
                    }}
                  </span>

                </div>

                <div
                  class="chat-bubble-body"
                >
                  {{ note.text }}
                </div>

              </div>

            </div>

            <div
              v-else
              class="notes-empty"
            >
              No updates available
            </div>

            <textarea
              v-model="updatesDraftText"
              placeholder="Write update..."
            ></textarea>

          </div>

          <div
            class="sidebar-panel-footer"
          >

            <button
              class="btn-flat-cancel"
              @click="
                closeUpdatesSidebar
              "
            >
              Close
            </button>

            <button
              class="monday-btn-primary"
              @click="
                saveTaskNotesUpdate
              "
            >
              Save Update
            </button>

          </div>

        </div>

      </div>

    </main>

  </div>
</template>

<script setup>

import { ref } from "vue";
import { computed } from "vue";

import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

import { useToast }
from "vue-toastification";

import Sidebar
from "./components/LeaderSidebar.vue";

import {
  useThemeStore
}
from "../stores/theme";

const theme = useThemeStore();
const toast = useToast();
const page = usePage();

const props = defineProps({
  leader: Object,
  projects: Array,
  teamMembers: Array,
  stats: Object,
  statusBreakdown: Object
});

const search = ref("");
const showProfileMenu = ref(false);

const filteredProjects = computed(() => {

  if (!search.value)
    return props.projects;

  return props.projects.filter(
    project =>
      project.name
        ?.toLowerCase()
        .includes(
          search.value
            .toLowerCase()
        )
  );
});

const completionRate =
computed(() => {

  if (!props.stats.tasks)
    return 0;

  return Math.round(
    (
      props.stats.completed /
      props.stats.tasks
    ) * 100
  );
});

const overdueTasks =
computed(() => {

  let count = 0;

  props.projects.forEach(
    project => {

      project.tasks?.forEach(
        task => {

          if (
            task.status !==
            "Completed" &&
            task.due_date &&
            new Date(
              task.due_date
            ) < new Date()
          ) {
            count++;
          }

        }
      );

    }
  );

  return count;

});

const teamWorkload =
computed(() => {

  return props.teamMembers.map(
    member => {

      let tasks = 0;

      props.projects.forEach(
        project => {

          project.tasks?.forEach(
            task => {

              if (
                task.member_id ==
                member.id
              ) {
                tasks++;
              }

            }
          );

        }
      );

      return {
        ...member,
        taskCount: tasks
      };

    }
  );

});
const showUpdatesSidebarPane = ref(false);

const activeTaskForUpdates = ref(null);
const activeProjectForUpdates = ref(null);

const updatesDraftText = ref("");

const openUpdatesSidebar = (
  task,
  project
) => {

  activeTaskForUpdates.value = task;
  activeProjectForUpdates.value = project;

  updatesDraftText.value = "";

  showUpdatesSidebarPane.value = true;
};

const closeUpdatesSidebar = () => {

  showUpdatesSidebarPane.value = false;

  activeTaskForUpdates.value = null;
  activeProjectForUpdates.value = null;

  updatesDraftText.value = "";
};

const saveTaskNotesUpdate = () => {

  if (
    !activeTaskForUpdates.value ||
    !updatesDraftText.value.trim()
  ) {
    return;
  }

  router.put(
    `/task/${activeTaskForUpdates.value.id}`,
    {
      project_id:
        activeTaskForUpdates.value.project_id,

      title:
        activeTaskForUpdates.value.title,

      member_id:
        activeTaskForUpdates.value.member_id,

      status:
        activeTaskForUpdates.value.status,

      priority:
        activeTaskForUpdates.value.priority,

      deadline:
        activeTaskForUpdates.value.due_date,

      notes:
        updatesDraftText.value,
    },
    {
      preserveScroll: true,

      onSuccess: () => {

        const user =
          page.props.auth?.user;

        if (
          !activeTaskForUpdates.value.notes
        ) {
          activeTaskForUpdates.value.notes = [];
        }

        activeTaskForUpdates.value.notes.unshift({
          sender:
            user?.name || "Leader",

          text:
            updatesDraftText.value,

          created_at:
            new Date().toISOString(),
        });

        updatesDraftText.value = "";

        toast.success(
          "Update posted"
        );
      },
    }
  );
};

const syncTask = task => {

  router.put(
    `/task/${task.id}`,
    {
      project_id:
        task.project_id,

      title:
        task.title,

      member_id:
        task.member_id,

      status:
        task.status,

      priority:
        task.priority,

      deadline:
        task.due_date,
    },
    {
      preserveScroll: true,

      onSuccess: () =>
        toast.success(
          "Task Updated"
        ),

      onError: () =>
        toast.error(
          "Update Failed"
        ),
    }
  );
};

const formatDate = value => {

  if (!value) return "";

  return new Date(
    value
  ).toLocaleString();
};

const logout = () => {
  router.post("/logout");
};

</script>

<style scoped>

.dashboard {
  display: flex;
  min-height: 100vh;
  background: #0f172a;
  color: #fff;
}

.main-content {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
}

/* HEADER */

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.header h1 {
  font-size: 32px;
  margin-bottom: 6px;
}

.header p {
  color: #94a3b8;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.search-box {
  width: 280px;
  padding: 12px 16px;
  border-radius: 12px;
  border: none;
  background: #1e293b;
  color: white;
}

.theme-btn {
  width: 45px;
  height: 45px;
  border-radius: 12px;
  border: none;
  cursor: pointer;
}

/* KPI */

.stats-grid {
  display: grid;
  grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.stat-card {
  background: #1e293b;
  border-radius: 16px;
  padding: 24px;
}

.stat-card span {
  color: #94a3b8;
  font-size: 14px;
}

.stat-card h2 {
  margin-top: 10px;
  font-size: 34px;
}

.success {
  border-left: 4px solid #22c55e;
}

.warning {
  border-left: 4px solid #f59e0b;
}

.danger {
  border-left: 4px solid #ef4444;
}

.info {
  border-left: 4px solid #3b82f6;
}

/* CARDS */

.dashboard-card {
  background: #1e293b;
  border-radius: 18px;
  padding: 24px;
  margin-bottom: 25px;
}

.card-header {
  margin-bottom: 20px;
}

.card-header h2 {
  font-size: 20px;
}

/* GRID */

.top-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 25px;
  margin-bottom: 25px;
}

/* PROJECTS */

.project-progress-card {
  margin-bottom: 18px;
}

.project-progress-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.project-percentage {
  font-weight: bold;
}

.progress-bar {
  height: 10px;
  background: #334155;
  border-radius: 30px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background:
    linear-gradient(
      90deg,
      #06b6d4,
      #3b82f6
    );
}

/* TEAM */

.member-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #0f172a;
  border-radius: 12px;
  margin-bottom: 12px;
}

.member-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background:
    linear-gradient(
      135deg,
      #06b6d4,
      #2563eb
    );

  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.member-info p {
  color: #94a3b8;
  margin-top: 4px;
}

/* WORKLOAD */

.workload-grid {
  display: grid;
  grid-template-columns:
    repeat(auto-fit,minmax(250px,1fr));
  gap: 15px;
}

.workload-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 15px;
  background: #0f172a;
  border-radius: 14px;
}

/* ACTIVITY */

.activity-feed {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.activity-item {
  display: flex;
  gap: 15px;
  align-items: center;
  background: #0f172a;
  padding: 14px;
  border-radius: 12px;
}

.activity-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #22c55e;
}

/* BUTTONS */

.quick-actions {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.quick-btn {
  border: none;
  padding: 12px 20px;
  border-radius: 12px;
  cursor: pointer;
  color: white;
  font-weight: 600;
}

.primary {
  background: #2563eb;
}

.success {
  background: #22c55e;
}

.warning {
  background: #f59e0b;
}

/* TABLE */

.table-wrapper {
  overflow-x: auto;
}

.project-table {
  width: 100%;
  border-collapse: collapse;
}

.project-table th,
.project-table td {
  padding: 14px;
  text-align: left;
}

.project-table th {
  background: #0f172a;
}

.project-row td {
  background: #0f172a;
  font-weight: bold;
}

.project-table tr {
  border-bottom: 1px solid #334155;
}

select {
  background: #0f172a;
  color: white;
  border: none;
  padding: 8px;
  border-radius: 8px;
}

.priority-badge {
  padding: 6px 12px;
  border-radius: 20px;
}

.priority-badge.low {
  background: #334155;
}

.priority-badge.medium {
  background: #f59e0b;
}

.priority-badge.high {
  background: #ef4444;
}

.update-btn {
  background: #2563eb;
  border: none;
  color: white;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
}

/* PROFILE */

.profile-container {
  position: relative;
}

.avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  cursor: pointer;
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 60px;
  background: #1e293b;
  border-radius: 12px;
  overflow: hidden;
}

.profile-dropdown button {
  width: 100%;
  border: none;
  background: transparent;
  color: white;
  padding: 12px 20px;
  cursor: pointer;
}

/* SIDEBAR PANEL */

.updates-sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.5);
  opacity: 0;
  visibility: hidden;
  transition: .3s;
}

.updates-sidebar-overlay.open {
  opacity: 1;
  visibility: visible;
}

.updates-sidebar-panel {
  position: absolute;
  right: 0;
  width: 450px;
  height: 100%;
  background: #0f172a;
  display: flex;
  flex-direction: column;
}

.sidebar-panel-header {
  padding: 20px;
  border-bottom: 1px solid #334155;
  display: flex;
  justify-content: space-between;
}

.sidebar-panel-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.sidebar-panel-body textarea {
  width: 100%;
  min-height: 120px;
  background: #1e293b;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 12px;
}

.chat-bubble-card {
  background: #1e293b;
  border-radius: 12px;
  padding: 12px;
  margin-bottom: 12px;
}

.chat-bubble-meta {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #94a3b8;
  font-size: 12px;
}

.sidebar-panel-footer {
  padding: 20px;
  border-top: 1px solid #334155;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.monday-btn-primary {
  background: #2563eb;
  color: white;
  border: none;
  padding: 12px 18px;
  border-radius: 10px;
}

.btn-flat-cancel {
  background: transparent;
  border: 1px solid #334155;
  color: white;
  padding: 12px 18px;
  border-radius: 10px;
}

/* RESPONSIVE */

@media(max-width:1200px){

  .top-grid{
    grid-template-columns:1fr;
  }

}

@media(max-width:768px){

  .header{
    flex-direction:column;
    align-items:flex-start;
    gap:20px;
  }

  .header-right{
    width:100%;
    flex-wrap:wrap;
  }

  .search-box{
    width:100%;
  }

  .updates-sidebar-panel{
    width:100%;
  }

}
</style>