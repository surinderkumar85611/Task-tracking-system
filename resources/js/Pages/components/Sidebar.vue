<template>
    <aside class="sidebar" :class="{ 'collapsed': isCollapsed }">
        <div class="logo-container">
            <div v-if="!isCollapsed" class="logo">
                BTT<span> Board</span>
            </div>
            <div v-else class="logo-icon">
                B<span>B</span>
            </div>
        </div>

        <div class="workspace-section">
            <button v-if="!isCollapsed" class="workspace-btn" @click="showCreateWorkspaceModal = true">
                ➕ Create Workspace
            </button>
            <button v-else class="workspace-btn compact" title="Create Workspace"
                @click="showCreateWorkspaceModal = true">
                ➕
            </button>

            <select v-if="!isCollapsed" class="workspace-select" v-model="selectedWorkspace" @change="changeWorkspace">
                <option disabled value="">Select Workspace</option>
                <option v-for="workspace in workspaces" :key="workspace.id" :value="workspace.id">
                    {{ workspace.name }}
                </option>
            </select>
            <div v-else class="workspace-select-icon-trigger" title="Switch Workspace"
                @click="showSelectWorkspaceModal = true">
                💼
            </div>
        </div>

        <nav class="nav-links">
            <Link href="/" :class="{ active: isActive('/') }" title="Dashboard">
                <span class="nav-icon">📊</span>
                <span v-if="!isCollapsed" class="nav-text">Dashboard</span>
            </Link>

            <Link href="/project" :class="{ active: isActive('/project') }" title="Projects">
                <span class="nav-icon">📁</span>
                <span v-if="!isCollapsed" class="nav-text">Projects</span>
            </Link>


            <Link href="/member" :class="{ active: isActive('/member') }" title="Members">
                <span class="nav-icon">👤</span>
                <span v-if="!isCollapsed" class="nav-text">Members</span>
            </Link>

            <Link href="/settings" :class="{ active: isActive('/settings') }" title="Settings">
                <span class="nav-icon">⚙️</span>
                <span v-if="!isCollapsed" class="nav-text">Settings</span>
            </Link>
        </nav>

        <div class="collapse-trigger-wrapper">
            <button class="collapse-toggle-btn" @click="toggleSidebar">
                {{ isCollapsed ? "▶" : "◀" }}
            </button>
        </div>

        <div v-if="showCreateWorkspaceModal" class="modal-overlay">
            <div class="modal">
                <h2>Create Workspace</h2>
                <input v-model="workspaceForm.name" type="text" placeholder="Workspace Name" />
                <textarea v-model="workspaceForm.description" placeholder="Description"></textarea>
                <div class="modal-actions">
                    <!-- <button class="cancel-btn" @click="showCreateWorkspaceModal = false">Cancel</button> -->
                    <button class="create-btn" @click="createWorkspace">Create</button>
                </div>
            </div>
        </div>

        <div v-if="showSelectWorkspaceModal" class="modal-overlay">
            <div class="modal">
                <h2>Select Workspace</h2>
                <select v-model="selectedWorkspace" class="workspace-select sticky-modal-select">
                    <option disabled value="">Select Workspace</option>
                    <option v-for="workspace in workspaces" :key="workspace.id" :value="workspace.id">
                        {{ workspace.name }}
                    </option>
                </select>
                <div class="modal-actions">
                    <!-- <button class="cancel-btn" @click="showSelectWorkspaceModal = false">Cancel</button> -->
                    <button class="create-btn" @click="changeWorkspace" :disabled="!selectedWorkspace">Continue</button>
                </div>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref, reactive } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();
const page = usePage();

const isCollapsed = ref(localStorage.getItem("sidebar_collapsed") === "true");
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem("sidebar_collapsed", isCollapsed.value);
};

const workspaces = page.props.workspaces || [];
const selectedWorkspace = ref(page.props.currentWorkspace || "");

const showCreateWorkspaceModal = ref(workspaces.length === 0);

const showSelectWorkspaceModal = ref(
    workspaces.length > 0 && !page.props.currentWorkspace
);

const workspaceForm = reactive({
    name: "",
    description: "",
});

const isActive = (path) => {
    return page.url === path;
};

const createWorkspace = () => {
    router.post("/workspace", workspaceForm, {
        preserveScroll: true,
        onSuccess: () => {
            showCreateWorkspaceModal.value = false;
            workspaceForm.name = "";
            workspaceForm.description = "";
        },
        onError: () => { toast.error("Creation failed. Check fields."); }
    });
};

const changeWorkspace = () => {
    if (!selectedWorkspace.value) return;

    router.post(
        "/workspace/select",
        { workspace_id: selectedWorkspace.value },
        {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                router.visit('/dashboard');
            },
        }
    );
};
</script>

<style scoped>
.sidebar {
    width: 15%;
    background: var(--sidebar);
    display: flex;
    flex-direction: column;
    padding: 20px;
    position: relative;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
    height: 100vh;
    border-right: 1px solid var(--border);
}

.sidebar.collapsed {
    width: 78px;
    padding: 20px 12px;
}

.logo-container {
    height: 40px;
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: var(--text);
    white-space: nowrap;
}

.logo span {
    color: #06b6d4;
}

.logo-icon {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    width: 100%;
    text-align: center;
}

.logo-icon span {
    color: #06b6d4;
}

.workspace-section {
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.workspace-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: #06b6d4;
    color: #111827;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
    white-space: nowrap;
    font-size: 11px;
}

.workspace-btn:hover {
    background: #0ea5e9;
}

.workspace-btn.compact {
    padding: 12px 0;
    font-size: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.workspace-select-icon-trigger {
    margin-top: 12px;
    width: 44px;
    height: 44px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.2s;
}

.workspace-select-icon-trigger:hover {
    background: var(--hover);
}

.nav-links {
    margin-top: 10px;
    flex: 1;
}

.nav-links a {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    color: var(--text);
    text-decoration: none;
    transition: background 0.3s, padding 0.3s ease;
    white-space: nowrap;
}

.sidebar.collapsed .nav-links a {
    padding: 12px 0;
    justify-content: center;
}

.nav-icon {
    font-size: 18px;
    display: inline-block;
}

.nav-text {
    margin-left: 14px;
    font-size: 15px;
}

.nav-links a:hover {
    background: var(--hover);
}

.nav-links a.active {
    background: #06b6d4;
    color: #111827;
}

.collapse-trigger-wrapper {
    margin-top: auto;
    padding-top: 15px;
    display: flex;
    justify-content: center;
    width: 100%;
}

.collapse-toggle-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: background 0.2s, transform 0.2s;
}

.collapse-toggle-btn:hover {
    background: var(--hover);
    transform: scale(1.05);
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.modal {
    width: 450px;
    background: var(--sidebar);
    padding: 25px;
    border-radius: 20px;
    border: 1px solid var(--border);
}

.modal h2 {
    margin-bottom: 20px;
    color: var(--text);
}

.modal input,
.modal textarea {
    width: 100%;
    margin-bottom: 15px;
    padding: 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    outline: none;
}

.modal textarea {
    min-height: 120px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.cancel-btn,
.create-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
}

.cancel-btn {
    background: #64748b;
    color: white;
}

.create-btn {
    background: #06b6d4;
    color: #111827;
}

.workspace-select {
    width: 100%;
    margin-top: 10px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
    cursor: pointer;
    outline: none;
}

.workspace-select option {
    background: var(--sidebar);
    color: var(--text);
}

.sticky-modal-select {
    margin-top: 0;
    margin-bottom: 20px;
}
</style>
