<template>
    <aside class="sidebar">

        <div class="logo">
            BTT<span> Board</span>
        </div>

        <div class="workspace-section">
            <button class="workspace-btn" @click="showWorkspaceModal = true">
                ➕ Create Workspace
            </button>
            <select class="workspace-select" v-model="selectedWorkspace" @change="changeWorkspace">
                <option v-for="workspace in workspaces" :key="workspace.id" :value="workspace.id">
                    {{ workspace.name }}
                </option>
            </select>
        </div>

        <nav class="nav-links">

            <Link href="/" :class="{ active: isActive('/') }">
                Dashboard
            </Link>

            <Link href="/project" :class="{ active: isActive('/project') }">
                Projects
            </Link>

            <Link href="/team" :class="{ active: isActive('/team') }">
                Team
            </Link>

            <Link href="/member" :class="{ active: isActive('/member') }">
                Members
            </Link>

            <Link href="/setting" :class="{ active: isActive('/setting') }">
                Settings
            </Link>

        </nav>

        <div v-if="showWorkspaceModal" class="modal-overlay" @click.self="showWorkspaceModal = false">
            <div class="modal">

                <h2>Create Workspace</h2>

                <input v-model="workspaceForm.name" type="text" placeholder="Workspace Name" />

                <textarea v-model="workspaceForm.description" placeholder="Description"></textarea>

                <div class="modal-actions">

                    <button class="cancel-btn" @click="showWorkspaceModal = false">
                        Cancel
                    </button>

                    <button class="create-btn" @click="createWorkspace">
                        Create
                    </button>

                </div>

            </div>
        </div>
    </aside>
</template>

<script setup>
import { ref, reactive } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";

const page = usePage();

const workspaces = page.props.workspaces || [];
const currentWorkspace = page.props.currentWorkspace;

const selectedWorkspace = ref(
    Number(page.props.currentWorkspace)
);

const showWorkspaceModal = ref(false);

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

            showWorkspaceModal.value = false;

            workspaceForm.name = "";
            workspaceForm.description = "";
        },
    });
};

const changeWorkspace = () => {

    router.post("/workspace/select", {
        workspace_id: selectedWorkspace.value,
    });
};
</script>

<style scoped>
/* Sidebar */
.sidebar {
    width: 280px;
    background: var(--sidebar);
    display: flex;
    flex-direction: column;
    padding: 20px;
}

.logo {
    font-size: 24px;
    font-weight: bold;
    padding-bottom: 20px;
}

.logo span {
    color: #06b6d4;
}

.nav-links {
    margin-top: 20px;
}

.nav-links a {
    display: block;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    color: var(--text);
    text-decoration: none;
    transition: background 0.3s;
}

.nav-links a:hover {
    background: var(--hover);
}

.nav-links a.active {
    background: #06b6d4;
    color: #111827;
}

.upgrade {
    background: var(--bg);
    padding: 15px;
    border-radius: 15px;
    text-align: center;
    margin-top: 20px;
}

.upgrade button {
    margin-top: 10px;
    padding: 8px 15px;
    border: none;
    border-radius: 10px;
    background: #06b6d4;
    color: #111827;
    cursor: pointer;
    transition: 0.3s;
}

.upgrade button:hover {
    background: #0ea5e9;
}

.workspace-section {
    margin-bottom: 25px;
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
    transition: 0.3s;
}

.workspace-btn:hover {
    background: #0ea5e9;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
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
</style>
