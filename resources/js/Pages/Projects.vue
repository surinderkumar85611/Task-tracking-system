<template>
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">

            <header class="header">
                <div>
                    <h1>Project Workspace</h1>
                    <p>Manage initiatives, assign operational targets, and track milestones inline.</p>
                </div>

                <div class="header-right">
                    <button class="theme-btn" @click="theme.toggleTheme">
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>

                    <input type="text" placeholder="Search boards..." v-model="search" />

                    <button class="icon-btn">🔔</button>

                    <div class="profile-container">
                        <img src="https://i.pravatar.cc/100" class="avatar"
                            @click.stop="showProfileMenu = !showProfileMenu" />
                        <div v-if="showProfileMenu" class="profile-dropdown">
                            <button @click="logout">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <h3>Total Projects</h3>
                    <h1>{{ projects.length }}</h1>
                </div>

                <div class="stat-card">
                    <h3>In Progress</h3>
                    <h1>{{projects.filter(p => p.status === 'In Progress').length}}</h1>
                </div>

                <div class="stat-card">
                    <h3>Completed</h3>
                    <h1>{{projects.filter(p => p.status === 'Completed').length}}</h1>
                </div>

                <div class="stat-card">
                    <h3>Planning</h3>
                    <h1>{{projects.filter(p => p.status === 'Planning').length}}</h1>
                </div>
            </section>

            <div class="global-actions-bar">
                <button class="monday-btn-primary" @click="openCreateProjectModal">
                    <span class="icon">＋</span> New Project
                </button>

                <select v-model="selectedActiveProjectFilter" class="project-view-selector">
                    <option :value="null">📁 All Project Folders</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.name }}
                    </option>
                </select>
            </div>

            <div class="monday-board-container">
                <div v-if="filteredProjects.length === 0" class="empty-board-state">
                    <h3>No projects found matching current parameters</h3>
                    <button class="monday-btn-secondary" @click="openCreateProjectModal">Create your first
                        board</button>
                </div>

                <div v-for="project in filteredProjects" :key="project.id" class="monday-project-group">

                    <div class="monday-group-header">
                        <div class="group-title-pane">
                            <span class="collapse-arrow">▼</span>
                            <h2 :style="{ borderLeft: `6px solid ${getGroupColor(project.status)}` }">
                                {{ project.name }}
                            </h2>
                            <span class="group-status-tag">{{ project.status }}</span>
                            <p class="group-desc-inline">— {{ project.description || 'No description added' }}</p>
                        </div>

                        <div class="group-control-actions">
                            <button class="action-icon-btn" title="Edit Project Config"
                                @click="openEditProjectModal(project)">✏️ Edit</button>
                            <button class="action-icon-btn delete" title="Drop Project"
                                @click="openDeleteModal(project.id)">🗑️ Delete</button>
                        </div>
                    </div>

                    <div class="monday-table-wrapper">
                        <table class="monday-editable-table">
                            <thead>
                                <tr>
                                    <th class="col-task">Task Name</th>
                                    <th class="col-updates">Updates</th>
                                    <th class="col-member">Member</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-priority">Priority</th>
                                    <th class="col-due">Due Date</th>
                                    <th class="col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="task in project.tasks" :key="task.id" class="task-row-item">
                                    <td class="cell-task">
                                        <input type="text" v-model="task.title" placeholder="Type task description..."
                                            @change="syncTaskRow(task)" class="monday-input-cell" />
                                    </td>
                                    <td class="cell-updates" style="text-align: center; vertical-align: middle;">
                                        <button class="monday-update-icon-btn"
                                            :class="{ 'has-notes': task.notes && task.notes.length > 0 }"
                                            @click="openUpdatesSidebar(task, project)" title="Open task updates">
                                            💬
                                            <span v-if="task.notes && task.notes.length > 0"
                                                class="update-indicator-dot"></span>
                                        </button>
                                    </td>
                                    <td class="cell-member" style="position: relative; padding: 0;">
                                        <!-- Clickable Cell Area (Matches image_c2dc86.png cell hover borders) -->
                                        <div class="monday-owner-cell-trigger"
                                            @click.stop="toggleAssigneeDropdown(task.id, $event)">
                                            <div v-if="task.member_id && task.member_id.length > 0"
                                                class="avatar-overlap-stack">
                                                <div v-for="(mId, idx) in task.member_id" :key="mId"
                                                    class="monday-circle-avatar" :style="{ zIndex: 10 - idx }"
                                                    :title="getMemberFullName(project, mId)">
                                                    {{ getMemberInitials(project, mId) }}
                                                </div>
                                            </div>
                                            <div v-else class="avatar-placeholder-blank">
                                                <div class="blank-avatar-icon">👤</div>
                                            </div>
                                        </div>

                                        <!-- Premium Custom Flyout Dialog Panel -->
                                        <div v-if="activeAssigneeDropdownTaskId === task.id"
                                            class="monday-popup-modal-panel"
                                            :style="{ top: dropdownPosition.top, left: dropdownPosition.left }"
                                            v-click-outside="closeAssigneeDropdown">

                                            <!-- Active Selections Row Area -->
                                            <div class="modal-pills-row">
                                                <div v-for="mId in task.member_id" :key="mId" class="member-pill-badge">
                                                    <span class="pill-avatar-dot">{{ getMemberInitials(project, mId)
                                                    }}</span>
                                                    <span class="pill-name-text">{{ getMemberFirstNameOnly(project, mId)
                                                    }}</span>
                                                    <span class="pill-remove-btn"
                                                        @click.stop="toggleMemberAssignment(task, mId)">×</span>
                                                </div>
                                                <div v-if="!task.member_id || task.member_id.length === 0"
                                                    class="no-assignees-hint">
                                                    No owners assigned
                                                </div>
                                            </div>

                                            <!-- Search Input Bar Header -->
                                            <div class="modal-search-wrapper">
                                                <span class="search-magnifier-icon">🔍</span>
                                                <input type="text" v-model="memberSearchQuery"
                                                    placeholder="Search names, roles or teams"
                                                    class="modal-search-input" @click.stop />
                                                <span class="search-info-bubble">ℹ️</span>
                                            </div>

                                            <!-- Filtered Suggested Lists section -->
                                            <div class="modal-section-title">Suggested people</div>
                                            <div class="modal-list-scrollway">
                                                <div v-for="member in filterScopeMembers(project)" :key="member.id"
                                                    class="modal-user-row-item"
                                                    :class="{ 'is-already-selected': isMemberAssigned(task, member.id) }"
                                                    @click.stop="toggleMemberAssignment(task, member.id)">

                                                    <div class="row-user-avatar">
                                                        {{ member.first_name?.charAt(0).toUpperCase() || 'M' }}
                                                    </div>
                                                    <div class="row-user-info">
                                                        <span class="row-fullname">{{ member.first_name }} {{
                                                            member.last_name }}</span>
                                                    </div>
                                                    <div v-if="isMemberAssigned(task, member.id)"
                                                        class="row-selection-checkmark">
                                                        ✓
                                                    </div>
                                                </div>
                                                <div v-if="filterScopeMembers(project).length === 0"
                                                    class="empty-search-fallback">
                                                    No matching members found
                                                </div>
                                            </div>

                                            <!-- Sticky Interaction Footer Block -->
                                            <div class="modal-action-footer">
                                                <div class="footer-notification-info">
                                                    <span class="footer-bell-icon">🔔</span>
                                                    <span class="footer-alert-text">Assignees will be notified</span>
                                                </div>
                                                <button class="footer-mute-action-btn" @click.stop>Mute</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cell-status" :class="getStatusLabelClass(task.status)">
                                        <select v-model="task.status" @change="syncTaskRow(task)"
                                            class="monday-status-dropdown">
                                            <option value="Todo">Todo</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </td>
                                    <td class="cell-priority" :class="getPriorityLabelClass(task.priority)">
                                        <select v-model="task.priority" @change="syncTaskRow(task)"
                                            class="monday-priority-dropdown">
                                            <option value="Low">Low</option>
                                            <option value="Medium">Medium</option>
                                            <option value="High">High</option>
                                        </select>
                                    </td>
                                    <td class="cell-due">
                                        <input type="date" v-model="task.deadline" @change="syncTaskRow(task)"
                                            class="monday-date-cell" />
                                    </td>
                                    <td class="cell-action">
                                        <button class="row-remove-trigger"
                                            @click="removeTaskRow(task.id, project)">✕</button>
                                    </td>
                                </tr>

                                <tr class="append-fast-row">
                                    <td colspan="7">
                                        <div class="add-row-placeholder" @click="appendNewEmptyTask(project)">
                                            <span class="plus-sign">＋</span> Add a new task to this project...
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="showCreateProjectModal" class="modal-overlay" @click.self="showCreateProjectModal = false">
                <div class="monday-modal">
                    <div class="modal-head">
                        <h2>Create Project Board</h2>
                        <button class="close-modal-x" @click="showCreateProjectModal = false">✕</button>
                    </div>

                    <div class="modal-body-form">
                        <div class="monday-field-group">
                            <label>Project Title</label>
                            <input v-model="form.name" type="text" placeholder="e.g., Login Page Redesign" />
                        </div>

                        <div class="monday-field-group">
                            <label>Status</label>
                            <select v-model="form.status">
                                <option>Planning</option>
                                <option>In Progress</option>
                                <option>Completed</option>
                            </select>
                        </div>

                        <div class="monday-field-group">
                            <label>Target Deadline</label>
                            <input v-model="form.deadline" type="date" />
                        </div>

                        <div class="monday-field-group">
                            <label>Assign Team Leader</label>
                            <select v-model="form.team_leader_id">
                                <option value="">Select Team Leader</option>
                                <option v-for="leader in teamLeaders" :key="leader.id" :value="leader.id">
                                    {{ leader.first_name }} {{ leader.last_name }} ({{ leader.team_members?.length || 0
                                    }} Members)
                                </option>
                            </select>
                        </div>

                        <div class="monday-field-group full-row">
                            <label>Description Brief</label>
                            <textarea v-model="form.description" placeholder="Summarize goals..."></textarea>
                        </div>
                    </div>

                    <div class="monday-modal-footer">
                        <button class="btn-flat-cancel" @click="showCreateProjectModal = false">Cancel</button>
                        <button class="monday-btn-primary" @click="handleCreateProject">Save Board</button>
                    </div>
                </div>
            </div>

            <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
                <div class="monday-modal">
                    <div class="modal-head">
                        <h2>Modify Board Settings</h2>
                        <button class="close-modal-x" @click="showEditModal = false">✕</button>
                    </div>

                    <div class="modal-body-form" v-if="editingProject">
                        <div class="monday-field-group">
                            <label>Board Title</label>
                            <input v-model="editingProject.name" />
                        </div>
                        <div class="monday-field-group">
                            <label>Status Flag</label>
                            <select v-model="editingProject.status">
                                <option>Planning</option>
                                <option>In Progress</option>
                                <option>Completed</option>
                            </select>
                        </div>
                        <div class="monday-field-group">
                            <label>Target Delivery Date</label>
                            <input type="date" v-model="editingProject.deadline" />
                        </div>
                        <div class="monday-field-group full-row">
                            <label>Description Brief</label>
                            <textarea v-model="editingProject.description"></textarea>
                        </div>
                    </div>

                    <div class="monday-modal-footer">
                        <button class="btn-flat-cancel" @click="showEditModal = false">Dismiss</button>
                        <button class="monday-btn-primary" @click="updateProject">Apply Adjustments</button>
                    </div>
                </div>
            </div>

            <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
                <div class="monday-modal confirmation-variant">
                    <h3>Confirm Board Deletion</h3>
                    <p>Are you absolute in your intent to drop this project tracking sheet? This operation will
                        instantly clear any nested task matrix records permanently.</p>
                    <div class="monday-modal-footer">
                        <button class="btn-flat-cancel" @click="showDeleteModal = false">Cancel</button>
                        <button class="monday-btn-danger" @click="deleteProject">Confirm Purge</button>
                    </div>
                </div>
            </div>

            <div class="updates-sidebar-overlay" :class="{ 'open': showUpdatesSidebarPane }"
                @click="closeUpdatesSidebar">
                <div class="updates-sidebar-panel" @click.stop>
                    <div class="sidebar-panel-header">
                        <div class="panel-header-left">
                            <span class="panel-task-icon">📋</span>
                            <div>
                                <h2>{{ activeTaskForUpdates?.title || 'Task Updates' }}</h2>
                                <p class="panel-subtitle">Project: {{ activeProjectForUpdates?.name }}</p>
                            </div>
                        </div>
                        <button class="close-panel-btn" @click="closeUpdatesSidebar">✕</button>
                    </div>

                    <div class="sidebar-panel-body">
                        <div class="notes-display-box">
                            <label>📌 Updates Timeline</label>

                            <div v-if="activeTaskForUpdates?.notes && activeTaskForUpdates.notes.length > 0"
                                class="messages-thread-wrapper">

                                <div v-for="(note, index) in activeTaskForUpdates.notes" :key="index"
                                    class="chat-bubble-card">
                                    <div class="chat-bubble-meta">
                                        <span class="chat-bubble-author">
                                            <span class="monday-circle-avatar chat-variant">
                                                {{ note.sender ? note.sender.charAt(0).toUpperCase() : 'A' }}
                                            </span>
                                            <span class="chat-author-name">{{ note.sender || 'System User' }}</span>
                                        </span>
                                        <span class="chat-bubble-time">{{ formatDate(note.created_at) }}</span>
                                    </div>
                                    <div class="chat-bubble-body">
                                        {{ note.text }}
                                    </div>
                                </div>

                            </div>

                            <div v-else class="notes-empty">
                                💬 No updates logged yet. Start the conversation by writing an update below!
                            </div>
                        </div>

                        <div class="notes-editor-section">
                            <label for="task-textarea">Write a new update or modify directions:</label>
                            <textarea id="task-textarea" v-model="updatesDraftText"
                                placeholder="Share an update, flag blockers, or drop context for the team..."></textarea>
                        </div>
                    </div>

                    <div class="sidebar-panel-footer">
                        <button class="btn-flat-cancel" @click="closeUpdatesSidebar">Close</button>
                        <button class="monday-btn-primary" @click="saveTaskNotesUpdate">Update Status Box</button>
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
const page = usePage();

const props = defineProps({
    projects: { type: Array, default: () => [] },
    teamLeaders: { type: Array, default: () => [] },
});

const search = ref("");
const showProfileMenu = ref(false);
const showCreateProjectModal = ref(false);
const selectedActiveProjectFilter = ref(null);

const form = reactive({
    name: "",
    status: "Planning",
    deadline: "",
    description: "",
    team_leader_id: "",
});
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin' || true);

const projects = computed(() => props.projects || []);

const filteredProjects = computed(() => {
    return projects.value.filter(project => {
        const matchesSearch = project.name?.toLowerCase().includes(search.value.toLowerCase());
        const matchesProjectFilter = selectedActiveProjectFilter.value ? project.id === selectedActiveProjectFilter.value : true;
        return matchesSearch && matchesProjectFilter;
    });
});

const openCreateProjectModal = () => {
    showCreateProjectModal.value = true;
};

const handleCreateProject = () => {
    router.post("/project", form, {
        preserveScroll: true,
        onSuccess: () => {
            showCreateProjectModal.value = false;
            form.name = "";
            form.status = "Planning";
            form.deadline = "";
            form.description = "";
            form.team_leader_id = "";
        },
        onError: () => { toast.error("Deployment failed. Check fields."); }
    });
};

const getGroupColor = (status) => {
    switch (status) {
        case 'In Progress': return '#3b82f6';
        case 'Completed': return '#00c875';
        case 'Planning': return '#fbbf24';
        default: return '#c4c4c4';
    }
};

const getStatusLabelClass = (status) => {
    if (!status) return 'status-todo';
    return 'status-' + status.toLowerCase().replace(/\s+/g, '-');
};

const getPriorityLabelClass = (priority) => {
    if (!priority) return 'priority-low';
    return 'priority-' + priority.toLowerCase();
};

const getProjectScopeMembers = (project) => {
    if (!project || !project.team_leader) return [];
    const leader = project.team_leader;
    const assets = leader.team_members || [];

    if (isAdmin.value) {
        const managerNode = { id: leader.id, first_name: leader.first_name, last_name: " (TL)" };
        return [managerNode, ...assets];
    }
    return assets;
};

const appendNewEmptyTask = (project) => {
    const rawPayload = {
        project_id: project.id,
        title: "New Item Row",
        member_id: [],
        priority: "Medium",
        status: "Todo",
        deadline: new Date().toISOString().slice(0, 10),
    };

    router.post("/task", rawPayload, {
        preserveScroll: true,
        onSuccess: () => {
        },
        onError: () => {
            toast.error("An error occurred trying to initialize task template.");
        }
    });
};

const syncTaskRow = (task) => {
    if (!task.title || task.title.trim() === '') {
        task.title = "Untitled Task";
    }

    const updatePayload = {
        id: task.id,
        project_id: task.project_id,
        title: task.title,
        member_id: task.member_id,
        status: task.status,
        priority: task.priority,
        deadline: task.deadline
    };

    router.put(`/task/${task.id}`, updatePayload, {
        preserveScroll: true,
        onError: () => { toast.error("Sync error: Failed to save edits to server."); }
    });
};

const removeTaskRow = (taskId, project) => {
    if (confirm("Are you sure you want to delete this task row?")) {
        router.delete(`/task/${taskId}`, {
            preserveScroll: true,
            onSuccess: () => {
                project.tasks = project.tasks.filter(t => t.id !== taskId);
            },
            onError: () => { toast.error("Failed to delete task."); }
        });
    }
};

const showEditModal = ref(false);
const editingProject = ref(null);
const openEditProjectModal = (project) => {
    editingProject.value = { ...project };
    showEditModal.value = true;
};

const updateProject = () => {
    router.put(`/project/${editingProject.value.id}`, editingProject.value, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
        },
        onError: () => { toast.error("Failed to update project settings."); }
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
            showDeleteModal.value = false;
            selectedProjectId.value = null;
        },
        onError: () => { toast.error("Failed to remove project."); }
    });
};

const showUpdatesSidebarPane = ref(false);
const activeTaskForUpdates = ref(null);
const activeProjectForUpdates = ref(null);
const updatesDraftText = ref("");

const openUpdatesSidebar = (task, project) => {
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
    if (!activeTaskForUpdates.value || !updatesDraftText.value.trim()) return;

    const messageText = updatesDraftText.value.trim();

    const syncPayload = {
        id: activeTaskForUpdates.value.id,
        project_id: activeTaskForUpdates.value.project_id,
        title: activeTaskForUpdates.value.title,
        member_id: activeTaskForUpdates.value.member_id,
        status: activeTaskForUpdates.value.status,
        priority: activeTaskForUpdates.value.priority,
        deadline: activeTaskForUpdates.value.deadline,
        notes: messageText
    };

    router.put(`/task/${activeTaskForUpdates.value.id}`, syncPayload, {
        preserveScroll: true,
        onSuccess: () => {
            const currentUser = page.props.auth?.user;
            const senderName = currentUser?.first_name || currentUser?.name || 'Admin';

            if (!activeTaskForUpdates.value.notes) {
                activeTaskForUpdates.value.notes = [];
            }

            activeTaskForUpdates.value.notes.unshift({
                sender: senderName,
                text: messageText,
                created_at: new Date().toISOString()
            });

            updatesDraftText.value = "";
        },
        onError: () => { toast.error("Failed to post message update."); }
    });
};

const formatDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
    },
};

const dropdownPosition = ref({ top: '0px', left: '0px' });
const activeAssigneeDropdownTaskId = ref(null);
const memberSearchQuery = ref('');

const toggleAssigneeDropdown = (taskId, event) => {
    if (activeAssigneeDropdownTaskId.value === taskId) {
        activeAssigneeDropdownTaskId.value = null;
    } else {
        memberSearchQuery.value = '';
        activeAssigneeDropdownTaskId.value = taskId;

        if (event && event.currentTarget) {
            const rect = event.currentTarget.getBoundingClientRect();

            dropdownPosition.value = {
                top: `${rect.bottom + window.scrollY + 6}px`,
                left: `${rect.left + window.scrollX + (rect.width / 2) - 160}px`
            };
        }
    }
};

const closeAssigneeDropdown = () => {
    activeAssigneeDropdownTaskId.value = null;
};

const isMemberAssigned = (task, memberId) => {
    if (!task.member_id || !Array.isArray(task.member_id)) return false;
    return task.member_id.includes(memberId);
};

const toggleMemberAssignment = (task, memberId) => {
    if (!task.member_id || !Array.isArray(task.member_id)) {
        task.member_id = [];
    }
    const index = task.member_id.indexOf(memberId);
    if (index > -1) {
        task.member_id.splice(index, 1);
    } else {
        task.member_id.push(memberId);
    }
    syncTaskRow(task);
};

const filterScopeMembers = (project) => {
    const allMembers = getProjectScopeMembers(project) || [];
    if (!memberSearchQuery.value.trim()) return allMembers;

    const query = memberSearchQuery.value.toLowerCase();
    return allMembers.filter(m => {
        const full = `${m.first_name} ${m.last_name}`.toLowerCase();
        return full.includes(query);
    });
};

const getMemberInitials = (project, memberId) => {
    const members = getProjectScopeMembers(project) || [];
    const found = members.find(m => m.id === memberId);
    return found?.first_name ? found.first_name.charAt(0).toUpperCase() : "?";
};

const getMemberFirstNameOnly = (project, memberId) => {
    const members = getProjectScopeMembers(project) || [];
    const found = members.find(m => m.id === memberId);
    return found ? found.first_name : "User";
};

const getMemberFullName = (project, memberId) => {
    const members = getProjectScopeMembers(project) || [];
    const found = members.find(m => m.id === memberId);
    return found ? `${found.first_name} ${found.last_name || ''}` : "Team Member";
};

const logout = () => {
    router.post('/logout');
};
</script>

<style scoped>
.main-content {
    flex: 1;
    padding: 20px;
    min-height: 100vh;
    overflow-y: auto;
}

.header {
    padding: 10px 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 18px 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

.stat-card h3 {
    color: var(--subtext);
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card h1 {
    margin-top: 6px;
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
}

.global-actions-bar {
    display: flex;
    gap: 16px;
    align-items: center;
    margin-bottom: 25px;
}

.project-view-selector {
    padding: 8px 16px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 4px;
    color: var(--text);
    font-size: 14px;
    outline: none;
    cursor: pointer;
}

.monday-btn-primary {
    background: #0073ea;
    color: #ffffff;
    font-weight: 500;
    font-size: 14px;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.monday-btn-primary:hover {
    background: #0060c5;
}

.monday-btn-secondary {
    background: var(--card);
    border: 1px solid var(--border);
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    color: var(--text);
    font-weight: 500;
}

.monday-btn-secondary:hover {
    background: var(--hover);
}

.monday-btn-danger {
    background: #df2f4a;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.monday-board-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
    margin-top: 15px;
}

.monday-project-group {
    background: var(--card);
    border-radius: 8px;
    border: 1px solid var(--border);
    padding: 16px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.monday-group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
}

.group-title-pane {
    display: flex;
    align-items: center;
    gap: 12px;
}

.collapse-arrow {
    color: var(--subtext);
    font-size: 11px;
    cursor: pointer;
}

.group-title-pane h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
    padding-left: 10px;
    line-height: 1.2;
}

.group-status-tag {
    background: var(--hover);
    color: var(--subtext);
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}

.group-desc-inline {
    color: var(--subtext);
    font-size: 13px;
    font-style: italic;
}

.group-control-actions {
    display: flex;
    gap: 8px;
}

.action-icon-btn {
    background: var(--bg);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    color: var(--subtext);
}

.action-icon-btn:hover {
    background: var(--hover);
    color: var(--text);
}

.action-icon-btn.delete:hover {
    border-color: #df2f4a;
    color: #df2f4a;
    background: rgba(223, 47, 74, 0.1);
}

.monday-table-wrapper {
    overflow-x: auto;
    width: 100%;
}

.monday-editable-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    table-layout: fixed;
}

.col-task {
    width: 20%;
    text-align: center;
}

.col-member {
    width: 12%;
    text-align: center;
}

.col-status {
    width: 14%;
    text-align: center;
}

.col-priority {
    width: 12%;
    text-align: center;
}

.col-due {
    width: 12%;
    text-align: center;
}

.col-action {
    width: 5%;
    text-align: center;
}

.col-updates {
    width: 8%;
    text-align: center;
}

:deep(td.cell-member),
.cell-member {
    position: relative !important;
    overflow: visible !important;
}

:deep(tr),
tbody tr {
    contain: none !important;
}

.monday-editable-table th {
    background: var(--bg);
    color: var(--subtext);
    font-size: 13px;
    font-weight: 500;
    padding: 8px 12px;
    border: 1px solid var(--border);
}

.monday-editable-table td {
    padding: 0;
    border: 1px solid var(--border);
    vertical-align: middle;
    height: 38px;
    background: var(--card);
}

.monday-input-cell {
    width: 100%;
    height: 38px;
    border: none;
    background: transparent;
    padding: 0 12px;
    font-size: 14px;
    color: var(--text);
    outline: none;
}

.monday-input-cell:focus {
    background: var(--bg);
    box-shadow: inset 0 0 0 2px #0073ea;
}

.monday-select-cell {
    width: 100%;
    height: 38px;
    border: none;
    background: transparent;
    padding: 0 8px;
    font-size: 14px;
    color: var(--text);
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
}

.monday-select-cell option {
    background: var(--bg);
    color: var(--text);
}

.monday-date-cell {
    width: 100%;
    height: 38px;
    border: none;
    background: transparent;
    padding: 0 10px;
    font-size: 13px;
    color: var(--text);
    outline: none;
    color-scheme: dark light;

}

.monday-status-dropdown,
.monday-priority-dropdown {
    width: 100%;
    height: 38px;
    border: none;
    text-align: center;
    font-size: 13px;
    font-weight: 500;
    color: #ffffff;
    outline: none;
    cursor: pointer;
    text-align-last: center;
    appearance: none;
}

.monday-status-dropdown option,
.monday-priority-dropdown option {
    background: var(--bg);
    color: var(--text);
}

.cell-status.status-todo .monday-status-dropdown {
    background: #64748b;
    color: #ffffff;
}

.cell-status.status-in-progress .monday-status-dropdown {
    background: #fd7e14;
}

.cell-status.status-completed .monday-status-dropdown {
    background: #00c875;
}

.cell-priority.priority-low .monday-priority-dropdown {
    background: #579bfc;
}

.cell-priority.priority-medium .monday-priority-dropdown {
    background: #a25ddc;
}

.cell-priority.priority-high .monday-priority-dropdown {
    background: #e2445c;
}

.task-row-item:hover {
    background: var(--hover);
}

.row-remove-trigger {
    background: transparent;
    border: none;
    color: var(--subtext);
    cursor: pointer;
    width: 100%;
    height: 38px;
    line-height: 38px;
    text-align: center;
    font-size: 12px;
}

.row-remove-trigger:hover {
    color: #df2f4a;
    background: rgba(223, 47, 74, 0.1);
}

.add-row-placeholder {
    padding: 10px 16px;
    color: var(--subtext);
    font-size: 14px;
    cursor: pointer;
    background: var(--card);
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.add-row-placeholder:hover {
    background: var(--hover);
    color: var(--text);
}

.plus-sign {
    color: #0073ea;
    font-weight: bold;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1200;
    padding: 20px;
}

.monday-modal {
    background: var(--card);
    border-radius: 8px;
    width: 580px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid var(--border);
    transition: background-color 0.3s ease;
}

.monday-modal.confirmation-variant {
    width: 440px;
    padding: 24px;
    gap: 16px;
}

.modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
}

.modal-head h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
}

.close-modal-x {
    background: transparent;
    border: none;
    font-size: 16px;
    color: var(--subtext);
    cursor: pointer;
}

.modal-body-form {
    padding: 24px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
    background: var(--card);
}

.monday-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.monday-field-group.full-row {
    grid-column: span 2;
}

.monday-field-group label {
    font-size: 13px;
    font-weight: 500;
    color: var(--text);
}

.monday-field-group input,
.monday-field-group select,
.monday-field-group textarea {
    padding: 8px 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 4px;
    font-size: 14px;
    outline: none;
    color: var(--text);
}

.monday-field-group textarea {
    min-height: 80px;
    resize: none;
}

.monday-field-group input:focus,
.monday-field-group select:focus,
.monday-field-group textarea:focus {
    border-color: #0073ea;
}

.monday-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: var(--bg);
}

.btn-flat-cancel {
    background: transparent;
    border: none;
    color: var(--subtext);
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    padding: 8px 16px;
}

.btn-flat-cancel:hover {
    color: var(--text);
}

.empty-board-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--subtext);
}

.empty-board-state h3 {
    margin-bottom: 12px;
    font-weight: 400;
}

.profile-container {
    position: relative;
}

.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid var(--card);
    transition: 0.2s ease;
}

.avatar:hover {
    transform: scale(1.05);
}

.profile-dropdown {
    position: absolute;
    top: 58px;
    right: 0;
    width: 140px;
    background: var(--sidebar);
    border: 1px solid var(--card);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    z-index: 100;
}

.profile-dropdown button {
    width: 100%;
    padding: 12px 16px;
    background: transparent;
    border: none;
    color: var(--text);
    text-align: left;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s ease;
}

.profile-dropdown button:hover {
    background: var(--card);
    color: #ef4444;
}

.monday-update-icon-btn {
    background: transparent;
    border: none;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: background 0.2s;
}

.monday-update-icon-btn:hover {
    background: var(--hover);
}

.monday-update-icon-btn.has-notes {
    color: #0073ea;
}

.update-indicator-dot {
    position: absolute;
    top: 6px;
    right: 10px;
    width: 6px;
    height: 6px;
    background: #00c875;
    border-radius: 50%;
}

.updates-sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0);
    backdrop-filter: blur(0px);
    z-index: 1500;
    display: flex;
    justify-content: flex-end;
    pointer-events: none;
    transition: background-color 0.3s ease, backdrop-filter 0.3s ease;
}

.updates-sidebar-overlay.open {
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(2px);
    pointer-events: auto;
}

.updates-sidebar-panel {
    width: 500px;
    height: 100%;
    background: var(--card);
    border-left: 1px solid var(--border);
    box-shadow: -5px 0 25px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.updates-sidebar-overlay.open .updates-sidebar-panel {
    transform: translateX(0);
}

.sidebar-panel-header {
    padding: 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: var(--bg);
}

.panel-header-left {
    display: flex;
    gap: 14px;
}

.panel-task-icon {
    font-size: 24px;
    margin-top: 2px;
}

.sidebar-panel-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.panel-subtitle {
    font-size: 13px;
    color: var(--subtext);
    margin-top: 4px;
}

.close-panel-btn {
    background: transparent;
    border: none;
    font-size: 18px;
    color: var(--subtext);
    cursor: pointer;
    padding: 4px;
}

.close-panel-btn:hover {
    color: var(--text);
}

.sidebar-panel-body {
    padding: 24px;
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-panel-body label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    display: block;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.notes-display-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
}

.notes-content {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 12px;
    margin-top: 8px;
}

.notes-meta {
    font-size: 12px;
    color: #0073ea;
    font-weight: 500;
    margin-bottom: 6px;
}

.notes-text-body {
    font-size: 14px;
    color: var(--text);
    white-space: pre-wrap;
    line-height: 1.5;
}

.notes-editor-section textarea {
    width: 100%;
    min-height: 140px;
    padding: 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 14px;
    outline: none;
    resize: vertical;
    line-height: 1.5;
}

.notes-editor-section textarea:focus {
    border-color: #0073ea;
}

.sidebar-panel-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    background: var(--bg);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.messages-thread-wrapper {
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-height: 400px;
    overflow-y: auto;
    margin-top: 10px;
    padding-right: 4px;
}

.chat-bubble-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.chat-bubble-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 4px;
}

.chat-bubble-author {
    font-size: 13px;
    font-weight: 600;
    color: #0073ea;
    display: flex;
    align-items: center;
    gap: 6px;
}

.avatar-mini {
    font-size: 12px;
    background: var(--hover);
    border-radius: 50%;
    padding: 2px;
}

.chat-bubble-time {
    font-size: 11px;
    color: var(--subtext);
}

.chat-bubble-body {
    font-size: 13px;
    color: var(--text);
    line-height: 1.4;
    white-space: pre-wrap;
    word-break: break-word;
}

.notes-empty {
    color: var(--subtext);
    font-style: italic;
    font-size: 13px;
    text-align: center;
    padding: 30px 10px;
    background: var(--bg);
    border: 1px dashed var(--border);
    border-radius: 8px;
    margin-top: 10px;
}

.multi-assignee-trigger {
    width: 100%;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.avatar-stack-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
    padding: 2px;
}

.monday-circle-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #0073ea;
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid var(--card);
    text-transform: uppercase;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    user-select: none;
}

.monday-circle-avatar:nth-child(even) {
    background: #00c875;
}

.monday-circle-avatar:nth-child(3n) {
    background: #a25ddc;
}

.monday-circle-avatar.chat-variant {
    width: 32px;
    height: 32px;
    font-size: 13px;
    background: #fd7e14;
    border: none;
}

.avatar-placeholder-empty {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 1px dashed var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: var(--subtext);
    transition: all 0.2s;
}

.avatar-placeholder-empty:hover {
    background: var(--hover);
    border-color: #0073ea;
}

.monday-owner-cell-trigger {
    width: 100%;
    min-height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.1s ease;
    padding: 4px;
}

.monday-owner-cell-trigger:hover {
    background-color: rgba(255, 255, 255, 0.05);
    outline: 1px solid #0073ea;
}

.avatar-overlap-stack {
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible !important;
}

.avatar-overlap-stack .monday-circle-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #0073ea;
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #1e222d;
    margin-left: -6px;
    transition: transform 0.15s ease;
    text-transform: uppercase;
}

.avatar-overlap-stack .monday-circle-avatar:first-child {
    margin-left: 0;
}

.avatar-overlap-stack:hover .monday-circle-avatar {
    transform: scale(1.05);
}

.avatar-overlap-stack .monday-circle-avatar:nth-child(even) {
    background-color: #00c875;
}

.avatar-overlap-stack .monday-circle-avatar:nth-child(3n) {
    background-color: #ffcb00;
    color: #333;
}

.avatar-overlap-stack .monday-circle-avatar:nth-child(4n) {
    background-color: #a25ddc;
}

.avatar-placeholder-blank {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    color: #818594;
}

.monday-owner-cell-trigger:hover .blank-avatar-icon {
    color: #0073ea;
    transform: scale(1.1);
}

.monday-popup-modal-panel {
    position: fixed !important;
    width: 320px;
    background-color: #292c3a;
    border-radius: 8px;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5), 0 4px 12px rgba(0, 0, 0, 0.3);
    border: 1px solid #3f4254;
    z-index: 999999 !important;
    padding: 16px 14px;
    display: flex;
    flex-direction: column;
    text-align: left;
    transform: none !important;
}

.modal-pills-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
    max-height: 65px;
    overflow-y: auto;
}

.member-pill-badge {
    display: inline-flex;
    align-items: center;
    background-color: rgba(0, 115, 234, 0.2);
    border: 1px solid rgba(0, 115, 234, 0.4);
    padding: 2px 6px;
    border-radius: 4px;
    gap: 5px;
}

.pill-avatar-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background-color: #0073ea;
    font-size: 8px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pill-name-text {
    font-size: 12px;
    color: #e1e3e7;
    font-weight: 500;
}

.pill-remove-btn {
    font-size: 14px;
    color: #a1a5b7;
    cursor: pointer;
    font-weight: bold;
    padding-left: 2px;
}

.pill-remove-btn:hover {
    color: #ff4d4d;
}

.no-assignees-hint {
    font-size: 12px;
    color: #7e8299;
    font-style: italic;
    padding: 2px 4px;
}

.modal-search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 14px;
}

.search-magnifier-icon {
    position: absolute;
    left: 10px;
    font-size: 12px;
    color: #7e8299;
}

.modal-search-input {
    width: 100%;
    background-color: rgba(255, 255, 255, 0.04);
    border: 1px solid #4a4e69;
    border-radius: 6px;
    padding: 8px 32px;
    color: #ffffff;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}

.modal-search-input:focus {
    border-color: #0073ea;
    background-color: transparent;
}

.search-info-bubble {
    position: absolute;
    right: 10px;
    font-size: 12px;
    color: #7e8299;
    cursor: help;
}

.modal-section-title {
    font-size: 11px;
    font-weight: 600;
    color: #ffcb00;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 8px;
}

.modal-list-scrollway {
    max-height: 160px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 12px;
}

.modal-user-row-item {
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.15s;
    gap: 10px;
}

.modal-user-row-item:hover {
    background-color: rgba(255, 255, 255, 0.06);
}

.modal-user-row-item.is-already-selected {
    background-color: rgba(0, 113, 234, 0.08);
}

.row-user-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #565b73;
    color: white;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

.row-fullname {
    font-size: 13px;
    color: #d1d3d9;
}

.row-selection-checkmark {
    margin-left: auto;
    color: #00c875;
    font-weight: bold;
    font-size: 13px;
}

.empty-search-fallback {
    font-size: 12px;
    color: #7e8299;
    text-align: center;
    padding: 12px 0;
}

.modal-action-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #3f4254;
    padding-top: 12px;
    margin-top: auto;
}

.footer-notification-info {
    display: flex;
    align-items: center;
    gap: 6px;
}

.footer-bell-icon {
    font-size: 13px;
}

.footer-alert-text {
    font-size: 11px;
    color: #989ba0;
}

.footer-mute-action-btn {
    background: transparent;
    border: 1px solid #4a4e69;
    color: #ffffff;
    padding: 4px 12px;
    font-size: 11px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.footer-mute-action-btn:hover {
    background-color: rgba(255, 255, 255, 0.08);
    border-color: #e1e3e7;
}

.chat-author-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}
</style>
