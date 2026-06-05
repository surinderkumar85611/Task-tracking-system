<template>
  <div class="leader-dashboard">
    <!-- Header -->
    <div class="header">
      <div>
        <h1>Leader Dashboard</h1>
        <p>Manage teams, projects and tasks</p>
      </div>

      <div class="header-actions">
        <button class="btn-primary" @click="showCreateTeam = true">
          + Create Team
        </button>

        <button class="btn-secondary" @click="showCreateProject = true">
          + Create Project
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <h3>Total Teams</h3>
        <h1>{{ teams.length }}</h1>
      </div>

      <div class="stat-card purple">
        <h3>Total Members</h3>
        <h1>{{ totalMembers }}</h1>
      </div>

      <div class="stat-card green">
        <h3>Projects</h3>
        <h1>{{ projects.length }}</h1>
      </div>

      <div class="stat-card orange">
        <h3>Tasks</h3>
        <h1>{{ tasks.length }}</h1>
      </div>
    </div>

    <div class="content-grid">
      <!-- Teams -->
      <div class="panel">
        <div class="panel-header">
          <h2>Teams</h2>
        </div>

        <div
          class="team-card"
          v-for="team in teams"
          :key="team.id"
        >
          <div>
            <h4>{{ team.name }}</h4>
            <small>{{ team.members }} Members</small>
          </div>

          <button class="small-btn">
            Open
          </button>
        </div>
      </div>

      <!-- Projects -->
      <div class="panel">
        <div class="panel-header">
          <h2>Projects</h2>
        </div>

        <div
          class="project"
          v-for="project in projects"
          :key="project.id"
        >
          <div class="project-top">
            <span>{{ project.name }}</span>
            <span>{{ project.progress }}%</span>
          </div>

          <div class="progress">
            <div
              class="progress-fill"
              :style="{ width: project.progress + '%' }"
            />
          </div>
        </div>
      </div>

      <!-- Tasks -->
      <div class="panel">
        <div class="panel-header">
          <h2>Task Board</h2>
        </div>

        <div
          class="task-card"
          v-for="task in tasks"
          :key="task.id"
        >
          <div>
            <strong>{{ task.title }}</strong>
            <p>{{ task.assignee }}</p>
          </div>

          <span class="badge">
            {{ task.status }}
          </span>
        </div>
      </div>

      <!-- Productivity -->
      <div class="productivity-card">
        <h3>Team Productivity</h3>

        <div class="score">
          86%
        </div>

        <p>Current month performance</p>

        <button class="report-btn">
          View Report
        </button>
      </div>
    </div>

    <!-- Create Team Modal -->
    <div
      v-if="showCreateTeam"
      class="modal-overlay"
    >
      <div class="modal">
        <h2>Create Team</h2>

        <input
          v-model="newTeam"
          placeholder="Team Name"
        />

        <div class="modal-actions">
          <button @click="createTeam">
            Create
          </button>

          <button @click="showCreateTeam = false">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <div
      v-if="showCreateProject"
      class="modal-overlay"
    >
      <div class="modal">
        <h2>Create Project</h2>

        <input
          v-model="newProject"
          placeholder="Project Name"
        />

        <div class="modal-actions">
          <button @click="createProject">
            Create
          </button>

          <button @click="showCreateProject = false">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";

const showCreateTeam = ref(false);
const showCreateProject = ref(false);

const newTeam = ref("");
const newProject = ref("");

const teams = ref([
  {
    id: 1,
    name: "Frontend Team",
    members: 6
  },
  {
    id: 2,
    name: "Backend Team",
    members: 4
  }
]);

const projects = ref([
  {
    id: 1,
    name: "CRM Dashboard",
    progress: 72
  },
  {
    id: 2,
    name: "Mobile App",
    progress: 41
  }
]);

const tasks = ref([
  {
    id: 1,
    title: "Login Page",
    assignee: "Sarah",
    status: "In Progress"
  },
  {
    id: 2,
    title: "API Integration",
    assignee: "David",
    status: "Pending"
  }
]);

const totalMembers = computed(() => {
  return teams.value.reduce(
    (sum, team) => sum + team.members,
    0
  );
});

function createTeam() {
  if (!newTeam.value.trim()) return;

  teams.value.push({
    id: Date.now(),
    name: newTeam.value,
    members: 0
  });

  newTeam.value = "";
  showCreateTeam.value = false;
}

function createProject() {
  if (!newProject.value.trim()) return;

  projects.value.push({
    id: Date.now(),
    name: newProject.value,
    progress: 0
  });

  newProject.value = "";
  showCreateProject.value = false;
}
</script>

<style scoped>
.leader-dashboard {
  padding: 24px;
  color: white;
  background: #071224;
  min-height: 100vh;
}

.header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 25px;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.btn-primary,
.btn-secondary,
.small-btn,
.report-btn {
  border: none;
  cursor: pointer;
  border-radius: 10px;
}

.btn-primary {
  background: #00c6ff;
  color: white;
  padding: 12px 18px;
}

.btn-secondary {
  background: #18243c;
  color: white;
  padding: 12px 18px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 15px;
  margin-bottom: 25px;
}

.stat-card {
  padding: 24px;
  border-radius: 15px;
}

.blue { background: linear-gradient(135deg,#3b82f6,#06b6d4); }
.purple { background: linear-gradient(135deg,#8b5cf6,#ec4899); }
.green { background: linear-gradient(135deg,#22c55e,#10b981); }
.orange { background: linear-gradient(135deg,#f97316,#ef4444); }

.content-grid {
  display: grid;
  grid-template-columns: 2fr 2fr;
  gap: 20px;
}

.panel,
.productivity-card {
  background: #132036;
  padding: 20px;
  border-radius: 16px;
}

.panel-header {
  margin-bottom: 15px;
}

.team-card,
.task-card {
  background: #1c2b46;
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.project {
  margin-bottom: 20px;
}

.project-top {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.progress {
  background: #2b3b59;
  height: 8px;
  border-radius: 10px;
}

.progress-fill {
  background: #00c6ff;
  height: 100%;
  border-radius: 10px;
}

.badge {
  background: #00c6ff;
  padding: 5px 10px;
  border-radius: 20px;
}

.productivity-card {
  background: linear-gradient(135deg,#00c6ff,#3b82f6);
}

.score {
  font-size: 64px;
  font-weight: bold;
}

.report-btn {
  margin-top: 20px;
  padding: 10px 20px;
  background: white;
  color: black;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal {
  background: white;
  color: black;
  padding: 25px;
  width: 400px;
  border-radius: 12px;
}

.modal input {
  width: 100%;
  padding: 12px;
  margin-top: 15px;
}

.modal-actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
}
</style>