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

            <section class="stats-grid">

                <div class="stat-card">
                    <h3>Total Projects</h3>
                    <h1>{{ projects.length }}</h1>
                </div>

                <div class="stat-card">
                    <h3>In Progress</h3>
                    <h1>
                        {{
                            projects.filter(p => p.status === 'In Progress').length
                        }}
                    </h1>
                </div>

                <div class="stat-card">
                    <h3>Completed</h3>
                    <h1>
                        {{
                            projects.filter(p => p.status === 'Completed').length
                        }}
                    </h1>
                </div>

                <div class="stat-card">
                    <h3>Planning</h3>
                    <h1>
                        {{
                            projects.filter(p => p.status === 'Planning').length
                        }}
                    </h1>
                </div>

            </section>

            <!-- Create Project -->
            <section class="project-form-card">

                <div class="section-title">
                    <div>
                        <h2>Create Project</h2>
                        <p>Create a project and assign a Team Leader.</p>
                    </div>

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

                <section class="projects-grid">

                    <div v-if="filteredProjects.length === 0" class="empty-projects">
                        <h3>No Projects Found</h3>

                        <p>
                            Create your first project and start assigning tasks.
                        </p>
                    </div>

                    <div class="project-card" v-for="project in filteredProjects" :key="project.id">

                        <div class="project-top">

                            <div>

                                <h3>
                                    {{ project.name }}
                                </h3>

                                <p>
                                    {{ project.description }}
                                </p>

                            </div>

                            <span class="badge" :class="project.statusClass">
                                {{ project.status }}
                            </span>

                        </div>

                        <div class="project-meta">

                            <div>
                                <small>Deadline</small>
                                <strong>{{ project.deadline }}</strong>
                            </div>

                            <div>
                                <small>Team Leader</small>
                                <strong>
                                    {{ project.team_leader?.first_name }}
                                </strong>
                            </div>

                        </div>

                        <div class="progress-section">

                            <div class="progress-bar">

                                <div class="progress" :style="{
                                    width:
                                        project.progress + '%'
                                }"></div>

                            </div>

                            <span>
                                {{ project.progress }}%
                            </span>

                        </div>

                        <div class="task-list">

                            <div class="task-item" v-for="task in project.tasks" :key="task.id">

                                <strong>
                                    {{ task.title }}
                                </strong>

                                <small>
                                    {{ task.member?.first_name }}
                                </small>

                            </div>

                        </div>

                        <div class="card-actions">

                            <button class="task-btn" @click="openTaskModal(project)">
                                Manage Tasks
                            </button>

                            <button class="task-btn" @click="viewTasks(project)">
                                View Tasks
                            </button>

                            <button class="edit-btn" @click="editProject(project)">
                                Edit
                            </button>

                            <button class="delete-btn" @click="
                                openDeleteModal(project.id)
                                ">
                                Delete
                            </button>

                        </div>

                    </div>

                </section>

            </section>

            <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">

                <div class="modal">

                    <h2>Edit Project</h2>

                    <input v-model="editingProject.name" placeholder="Project Name" />

                    <textarea v-model="editingProject.description" placeholder="Description"></textarea>

                    <select v-model="editingProject.status">

                        <option>Planning</option>
                        <option>In Progress</option>
                        <option>Completed</option>

                    </select>

                    <input type="date" v-model="editingProject.deadline" />

                    <div class="modal-actions">

                        <button class="cancel-btn" @click="showEditModal = false">
                            Cancel
                        </button>

                        <button class="save-btn" @click="updateProject">
                            Update
                        </button>

                    </div>

                </div>

            </div>

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

            <div v-if="showTaskModal" class="modal-overlay" @click.self="closeTaskModal">

                <div class="modal">

                    <h2>Create Task</h2>

                    <input v-model="taskForm.title" placeholder="Task title" />

                    <textarea v-model="taskForm.description" placeholder="Description"></textarea>

                    <select v-model="taskForm.member_id">

                        <option value="">
                            Assign Member
                        </option>

                        <option v-for="member in projectMembers" :key="member.id" :value="member.id">
                            {{ member.first_name }}
                            {{ member.last_name }}
                        </option>

                    </select>

                    <select v-model="taskForm.priority">
                        <option>Low</option>
                        <option>Medium</option>
                        <option>High</option>
                    </select>

                    <input type="date" v-model="taskForm.deadline" />

                    <button class="cancel-btn" @click="closeTaskModal">
                        Close
                    </button>

                    <button @click="createTask">
                        Create Task
                    </button>

                </div>

            </div>

            <div v-if="showViewTaskModal" class="modal-overlay" @click.self="closeViewTaskModal">
                <div class="task-view-modal">

                    <div class="modal-header">
                        <h2>
                            Tasks -
                            {{ selectedTaskProject?.name }}
                        </h2>

                        <button class="close-btn" @click="closeViewTaskModal">
                            ✕
                        </button>
                    </div>

                    <div v-if="
                        !selectedTaskProject?.tasks ||
                        selectedTaskProject.tasks.length === 0
                    " class="empty-task">
                        No tasks created yet.
                    </div>

                    <div v-else class="task-list-modal">

                        <div class="task-card" v-for="task in selectedTaskProject.tasks" :key="task.id">

                            <div class="task-top">

                                <h3>{{ task.title }}</h3>

                                <span class="priority" :class="task.priority.toLowerCase()">
                                    {{ task.priority }}
                                </span>

                            </div>

                            <p>
                                {{ task.description }}
                            </p>

                            <div class="task-footer">

                                <span>
                                    Assigned To:
                                    {{ task.member?.first_name }}
                                    {{ task.member?.last_name }}
                                </span>

                                <span>
                                    {{ task.status }}
                                </span>

                            </div>

                        </div>

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

const theme = useThemeStore();
const toast = useToast();

const props = defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
    teamLeaders: {
        type: Array,
        default: () => [],
    },
});

const search = ref("");

const form = reactive({
    name: "",
    status: "Planning",
    deadline: "",
    description: "",
    team_leader_id: "",
});

const projects = computed(() => props.projects || []);

const filteredProjects = computed(() => {

    return projects.value.filter(project =>
        project.name
            ?.toLowerCase()
            .includes(search.value.toLowerCase())
    );

});

const createProject = () => {

    router.post("/project", form, {

        preserveScroll: true,

        onSuccess: () => {

            toast.success("Project created successfully");

            form.name = "";
            form.status = "Planning";
            form.deadline = "";
            form.description = "";
            form.team_leader_id = "";
        },

        onError: () => {
            toast.error("Validation failed");
        },
    });
};

const showTaskModal = ref(false);
const selectedProject = ref(null);

const taskForm = reactive({
    project_id: "",
    title: "",
    description: "",
    member_id: "",
    priority: "Medium",
    deadline: "",
});

const openTaskModal = (project) => {

    selectedProject.value = project;

    taskForm.project_id = project.id;

    showTaskModal.value = true;
};

const closeTaskModal = () => {

    showTaskModal.value = false;

    selectedProject.value = null;

    taskForm.project_id = "";
    taskForm.title = "";
    taskForm.description = "";
    taskForm.member_id = "";
    taskForm.priority = "Medium";
    taskForm.deadline = "";
};

const showViewTaskModal = ref(false);
const selectedTaskProject = ref(null);

const viewTasks = (project) => {
    selectedTaskProject.value = project;
    showViewTaskModal.value = true;
};

const closeViewTaskModal = () => {
    showViewTaskModal.value = false;
    selectedTaskProject.value = null;
};

const showEditModal = ref(false);
const editingProject = ref(null);
const editProject = (project) => {

    editingProject.value = { ...project };

    showEditModal.value = true;
};

const createTask = () => {

    router.post("/task", taskForm, {

        preserveScroll: true,

        onSuccess: () => {

            toast.success("Task created successfully");

            closeTaskModal();
        },

        onError: () => {

            toast.error("Failed to create task");
        },
    });
};

const projectMembers = computed(() => {

    if (!selectedProject.value?.team_leader?.team_members) {
        return [];
    }

    return selectedProject.value.team_leader.team_members;
});

const showDeleteModal = ref(false);
const selectedProjectId = ref(null);

const openDeleteModal = (id) => {

    selectedProjectId.value = id;
    showDeleteModal.value = true;
};

const updateProject = () => {

    router.put(
        `/project/${editingProject.value.id}`,
        editingProject.value,
        {
            preserveScroll: true,

            onSuccess: () => {

                toast.success(
                    "Project updated successfully"
                );

                showEditModal.value = false;
            },
        }
    );
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
    width: 260px;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
    padding-top: 10px;
}

.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 20px;
}

.stat-card h3 {
    color: var(--subtext);
    font-size: 14px;
}

.stat-card h1 {
    margin-top: 10px;
    font-size: 32px;
}

.projects-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.project-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 20px;
}

.project-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.project-top p {
    margin-top: 8px;
    color: var(--subtext);
}

.project-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.project-meta small {
    display: block;
    color: var(--subtext);
    margin-bottom: 5px;
}

.progress-section {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.card-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 20px;
}

.task-btn {
    background: #8b5cf6;
    color: white;
    border: none;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
}

.task-list {
    margin-top: 20px;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.task-item {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px;
}

.task-item strong {
    display: block;
    margin-bottom: 5px;
}

.task-item small {
    color: var(--subtext);
}

.modal {
    width: 550px;
    background: var(--card);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid var(--border);

    display: flex;
    flex-direction: column;
    gap: 15px;
}

.modal input,
.modal textarea,
.modal select {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
}

.modal textarea {
    min-height: 120px;
}

.modal button {
    padding: 12px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
}

.empty-projects {
    grid-column: 1/-1;
    text-align: center;
    padding: 60px;
    border: 2px dashed var(--border);
    border-radius: 20px;
}

.task-view-modal {
    width: 800px;
    max-height: 80vh;
    overflow-y: auto;
    background: var(--card);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid var(--border);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.close-btn {
    border: none;
    background: transparent;
    color: var(--text);
    font-size: 20px;
    cursor: pointer;
}

.task-list-modal {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.task-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 15px;
    padding: 18px;
}

.task-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.task-card p {
    color: var(--subtext);
    margin-bottom: 15px;
}

.task-footer {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

.priority {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.priority.low {
    background: rgba(34, 197, 94, .15);
    color: #22c55e;
}

.priority.medium {
    background: rgba(251, 191, 36, .15);
    color: #fbbf24;
}

.priority.high {
    background: rgba(239, 68, 68, .15);
    color: #ef4444;
}

.empty-task {
    text-align: center;
    padding: 40px;
    color: var(--subtext);
}
</style>
