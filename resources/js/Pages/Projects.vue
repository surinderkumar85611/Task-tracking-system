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

                    <button class="theme-btn" @click="theme.toggleTheme">
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>

                    <input type="text" placeholder="Search project..." v-model="search" />

                    <img src="https://i.pravatar.cc/100" class="avatar" />

                </div>

            </header>

            <!-- Create Project -->
            <section class="project-form-card">

                <div class="section-title">
                    <h2>Create New Project</h2>

                    <button class="save-btn" @click="createProject">
                        Create Project
                    </button>
                </div>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Project Name</label>

                        <input v-model="form.name" type="text" placeholder="Enter project name" />
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

                        <input v-model="form.deadline" type="date" />
                    </div>

                    <div class="form-group">
                        <label>Assign Team Leader</label>

                        <select v-model="form.team_leader_id">
                            <option value="">
                                Select Team Leader
                            </option>

                            <option v-for="leader in teamLeaders" :key="leader.id" :value="leader.id">
                                {{ leader.first_name }}
                                {{ leader.last_name }}
                                ({{ leader.team_members.length }} Members)
                            </option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Description</label>

                        <textarea v-model="form.description" placeholder="Project description..."></textarea>
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

                        <tr v-if="filteredProjects.length === 0">
                            <td colspan="6" class="empty-state">
                                No projects exist yet.
                                <br>
                                <small>Create your first project to get started.</small>
                            </td>
                        </tr>

                        <tr v-for="project in filteredProjects" :key="project.id">

                            <td>
                                <div class="project-info">
                                    <h4>{{ project.name }}</h4>
                                    <small>{{ project.description }}</small>
                                </div>
                            </td>

                            <td>
                                <span class="badge" :class="project.statusClass">
                                    {{ project.status }}
                                </span>
                            </td>

                            <td>{{ project.deadline }}</td>

                            <td>{{ project.team }} Members</td>

                            <td>

                                <div class="progress-wrapper">

                                    <div class="progress-bar">
                                        <div class="progress" :style="{
                                            width: project.progress + '%'
                                        }"></div>
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

                                    <button class="delete-btn" @click="openDeleteModal(project.id)">
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </section>

            <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
                <div class="delete-modal">

                    <h3>Delete Project</h3>

                    <p>
                        Are you sure you want to delete this project?
                        This action cannot be undone.
                    </p>

                    <div class="modal-actions">

                        <button class="cancel-btn" @click="showDeleteModal = false">
                            Cancel
                        </button>

                        <button class="delete-confirm-btn" @click="deleteProject">
                            Delete
                        </button>

                    </div>

                </div>
            </div>
        </main>

    </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import Sidebar from "./components/Sidebar.vue";

import { useThemeStore } from "../stores/theme";

const page = usePage();
const workspaces = page.props.workspaces || [];
const currentWorkspace = page.props.currentWorkspace;

const theme = useThemeStore();

const search = ref("");

const form = reactive({
    name: "",
    status: "Planning",
    deadline: "",
    description: "",
    team_leader_id: "",
});

const toast = useToast();

const props = defineProps({
    projects: Array,
    teamLeaders: Array,
});

const projects = computed(() => props.projects);

const filteredProjects = computed(() => {
    return projects.value.filter((project) =>
        project.name
            .toLowerCase()
            .includes(search.value.toLowerCase())
    );
});

const createProject = () => {

    router.post("/project", form, {
        preserveScroll: true,

        onSuccess: () => {
            toast.success("Project created successfully");
        },

        onError: (errors) => {
            toast.error("Validation failed");
        },
    });
};

const showDeleteModal = ref(false);
const selectedProjectId = ref(null);

const openDeleteModal = (id) => {
    selectedProjectId.value = id;
    showDeleteModal.value = true;
};

const deleteProject = () => {

    router.delete(`/project/${selectedProjectId.value}`, {

        preserveScroll: true,

        onSuccess: () => {

            toast.success("Project deleted successfully");

            showDeleteModal.value = false;
            selectedProjectId.value = null;
        },
    });
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
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.completed-status {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.planning-status {
    background: rgba(251, 191, 36, 0.2);
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
    background: rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    overflow: hidden;
}

.progress {
    height: 100%;
    background: linear-gradient(90deg,
            #06b6d4,
            #3b82f6);
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

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.delete-modal {
    width: 420px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 25px;
}

.delete-modal h3 {
    margin-bottom: 10px;
}

.delete-modal p {
    color: var(--subtext);
    margin-bottom: 20px;
    line-height: 1.5;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.cancel-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: #64748b;
    color: white;
}

.delete-confirm-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: #ef4444;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--subtext);
    font-size: 15px;
}
</style>
