<template>
  <div class="dashboard" :class="theme.themeClass">

    <Sidebar />

    <main class="main-content">

      <!-- Header -->
      <header class="header">

        <div>
          <h1>Project Management</h1>
          <p>Create and manage company projects.</p>
        </div>

        <div class="header-right">

          <button
            class="theme-btn"
            @click="theme.toggleTheme"
          >
            {{ theme.isDark ? "☀️" : "🌙" }}
          </button>

          <input
            type="text"
            placeholder="Search project..."
            v-model="search"
          />

          <img
            src="https://i.pravatar.cc/100"
            class="avatar"
          />

        </div>

      </header>

      <!-- Create Project -->
      <section class="project-form-card">

        <div class="section-title">
          <h2>Create New Project</h2>

          <button
            class="save-btn"
            @click="createProject"
          >
            Create Project
          </button>
        </div>

        <div class="form-grid">

          <div class="form-group">
            <label>Project Name</label>

            <input
              v-model="form.name"
              type="text"
              placeholder="Enter project name"
            />
          </div>

          <div class="form-group">
            <label>Status</label>

            <select v-model="form.status">
              <option>Planning</option>
              <option>In Progress</option>
              <option>Completed</option>
            </select>
          </div>

          <div class="form-group">
            <label>Deadline</label>

            <input
              v-model="form.deadline"
              type="date"
            />
          </div>

          <div class="form-group">
            <label>Team Size</label>

            <input
              v-model="form.team"
              type="number"
              placeholder="Team members"
            />
          </div>

          <div class="form-group full-width">
            <label>Description</label>

            <textarea
              v-model="form.description"
              placeholder="Project description..."
            ></textarea>
          </div>

        </div>

      </section>

      <!-- Projects Table -->
      <section class="projects-table-card">

        <div class="table-header">
          <h2>All Projects</h2>
        </div>

        <table class="projects-table">

          <thead>
            <tr>
              <th>Project</th>
              <th>Status</th>
              <th>Deadline</th>
              <th>Team</th>
              <th>Progress</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>

            <tr
              v-for="project in filteredProjects"
              :key="project.id"
            >

              <td>
                <div class="project-info">
                  <h4>{{ project.name }}</h4>
                  <small>{{ project.description }}</small>
                </div>
              </td>

              <td>
                <span
                  class="badge"
                  :class="project.statusClass"
                >
                  {{ project.status }}
                </span>
              </td>

              <td>{{ project.deadline }}</td>

              <td>{{ project.team }} Members</td>

              <td>

                <div class="progress-wrapper">

                  <div class="progress-bar">
                    <div
                      class="progress"
                      :style="{
                        width: project.progress + '%'
                      }"
                    ></div>
                  </div>

                  <span>
                    {{ project.progress }}%
                  </span>

                </div>

              </td>

              <td>

                <div class="actions">

                  <button class="edit-btn">
                    Edit
                  </button>

                  <button
                    class="delete-btn"
                    @click="deleteProject(project.id)"
                  >
                    Delete
                  </button>

                </div>

              </td>

            </tr>

          </tbody>

        </table>

      </section>

    </main>

  </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";

import Sidebar from "./components/Sidebar.vue";

import { useThemeStore } from "../stores/theme";

const theme = useThemeStore();

const search = ref("");

const form = reactive({
  name: "",
  status: "Planning",
  deadline: "",
  description: "",
  team: "",
});

const projects = ref([
  {
    id: 1,
    name: "CRM Dashboard",
    status: "In Progress",
    statusClass: "progress-status",
    deadline: "2026-06-20",
    description: "Internal CRM system",
    progress: 72,
    team: 8,
  },

  {
    id: 2,
    name: "School ERP",
    status: "Planning",
    statusClass: "planning-status",
    deadline: "2026-07-10",
    description: "School management platform",
    progress: 25,
    team: 5,
  },
]);

const filteredProjects = computed(() => {
  return projects.value.filter((project) =>
    project.name
      .toLowerCase()
      .includes(search.value.toLowerCase())
  );
});

const createProject = () => {

  if (
    !form.name ||
    !form.deadline ||
    !form.description
  ) {
    alert("Please fill required fields");
    return;
  }

  projects.value.unshift({
    id: Date.now(),
    name: form.name,
    status: form.status,
    deadline: form.deadline,
    description: form.description,
    progress: 0,
    team: form.team,

    statusClass:
      form.status === "Completed"
        ? "completed-status"
        : form.status === "In Progress"
        ? "progress-status"
        : "planning-status",
  });

  form.name = "";
  form.status = "Planning";
  form.deadline = "";
  form.description = "";
  form.team = "";
};

const deleteProject = (id) => {
  projects.value = projects.value.filter(
    (project) => project.id !== id
  );
};
</script>

<style scoped>

.main-content {
  flex: 1;
  padding: 25px;
  overflow-y: auto;
}

/* FORM */

.project-form-card,
.projects-table-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 25px;
  margin-top: 25px;
}

.section-title,
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.save-btn {
  background: #06b6d4;
  border: none;
  padding: 12px 18px;
  border-radius: 12px;
  cursor: pointer;
  color: white;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 10px;
  color: var(--subtext);
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 14px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--bg);
  color: var(--text);
}

.form-group textarea {
  min-height: 120px;
  resize: none;
}

.full-width {
  grid-column: span 2;
}

/* TABLE */

.projects-table {
  width: 100%;
  border-collapse: collapse;
}

.projects-table th {
  text-align: left;
  padding: 15px;
  color: var(--subtext);
  border-bottom: 1px solid var(--border);
}

.projects-table td {
  padding: 18px 15px;
  border-bottom: 1px solid var(--border);
}

.project-info h4 {
  margin-bottom: 6px;
}

.project-info small {
  color: var(--subtext);
}

.badge {
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 13px;
}

.progress-status {
  background: rgba(59,130,246,0.2);
  color: #3b82f6;
}

.completed-status {
  background: rgba(34,197,94,0.2);
  color: #22c55e;
}

.planning-status {
  background: rgba(251,191,36,0.2);
  color: #fbbf24;
}

/* PROGRESS */

.progress-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.progress-bar {
  width: 120px;
  height: 8px;
  background: rgba(255,255,255,0.08);
  border-radius: 20px;
  overflow: hidden;
}

.progress {
  height: 100%;
  background: linear-gradient(
    90deg,
    #06b6d4,
    #3b82f6
  );
}

/* ACTIONS */

.actions {
  display: flex;
  gap: 10px;
}

.edit-btn,
.delete-btn {
  border: none;
  padding: 8px 14px;
  border-radius: 10px;
  cursor: pointer;
}

.edit-btn {
  background: #3b82f6;
  color: white;
}

.delete-btn {
  background: #ef4444;
  color: white;
}

</style>