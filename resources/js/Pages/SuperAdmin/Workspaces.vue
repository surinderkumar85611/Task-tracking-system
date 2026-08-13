<template>

  <Head title="Workspaces" />

  <div class="dashboard" :class="isDark ? 'theme-dark' : 'theme-light'">

    <SuperAdminSidebar />

    <main class="main-content">
      <div class="topbar">
        <div class="topbar-greeting">
          <h2>Workspaces</h2>
          <p>Create, edit and remove workspaces.</p>
        </div>

        <div class="topbar-icons">
          <input v-model="search" type="text" placeholder="Search workspaces..." class="search-box" />
          <button class="theme-btn" @click="isDark = !isDark">{{ isDark ? '☀️' : '🌙' }}</button>
          <button class="view-toggle-btn" @click="openCreateModal">+ New Workspace</button>
        </div>
      </div>

      <div class="content-wrapper">

        <div class="workspace-grid">
          <div v-for="ws in filteredWorkspaces" :key="ws.id" class="workspace-card">
            <div class="workspace-card-top">
              <div class="ws-icon">{{ ws.name.charAt(0).toUpperCase() }}</div>
              <div class="ws-actions">
                <button class="icon-action" @click="openEditModal(ws)" title="Edit">✏️</button>
                <button class="icon-action danger" @click="removeWorkspace(ws)" title="Delete">🗑️</button>
              </div>
            </div>

            <h3>{{ ws.name }}</h3>
            <p class="ws-desc">{{ ws.description || 'No description' }}</p>

            <div class="ws-stats">
              <div><strong>{{ ws.members_count ?? 0 }}</strong><span>Members</span></div>
              <div><strong>{{ ws.teams_count ?? 0 }}</strong><span>Teams</span></div>
              <div><strong>{{ ws.projects_count ?? 0 }}</strong><span>Projects</span></div>
            </div>
          </div>

          <div v-if="!filteredWorkspaces.length" class="empty-state-inline">No workspaces found.</div>
        </div>

      </div>
    </main>

    <!-- CREATE / EDIT MODAL -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <div>
            <h2>{{ editingWorkspace ? 'Edit Workspace' : 'New Workspace' }}</h2>
            <p>{{ editingWorkspace ? 'Update workspace details.' : 'Create a new workspace.' }}</p>
          </div>
          <button class="close-btn" @click="closeModal">✕</button>
        </div>

        <div class="form-group"><label>Name</label><input v-model="form.name" placeholder="Acme Inc."></div>
        <div class="form-group"><label>URL Slug</label><input v-model="form.slug" placeholder="acme-inc"></div>
        <div class="form-group"><label>Description</label><textarea rows="4" v-model="form.description" placeholder="What is this workspace for?"></textarea></div>

        <div class="modal-actions">
          <button class="cancel-btn" @click="closeModal">Cancel</button>
          <button class="save-btn" :disabled="!form.name.trim()" @click="saveWorkspace">
            {{ editingWorkspace ? 'Save Changes' : 'Create Workspace' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import SuperAdminSidebar from "./SuperAdminSidebar.vue";

const props = defineProps({
  workspaces: { type: Array, default: () => [] },
});

const isDark = ref(true);
const search = ref("");
const showModal = ref(false);
const editingWorkspace = ref(null);

const filteredWorkspaces = computed(() => {
  if (!search.value) return props.workspaces;
  const term = search.value.toLowerCase();
  return props.workspaces.filter(w => w.name?.toLowerCase().includes(term));
});

const form = ref({ name: "", slug: "", description: "" });

const openCreateModal = () => {
  editingWorkspace.value = null;
  form.value = { name: "", slug: "", description: "" };
  showModal.value = true;
};

const openEditModal = (ws) => {
  editingWorkspace.value = ws;
  form.value = { name: ws.name, slug: ws.slug || "", description: ws.description || "" };
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; editingWorkspace.value = null; };

const saveWorkspace = () => {
  if (!form.value.name.trim()) return;

  if (editingWorkspace.value) {
    router.put(`/super-admin/workspaces/${editingWorkspace.value.id}`, form.value, {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    router.post("/super-admin/workspaces", form.value, {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  }
};

const removeWorkspace = (ws) => {
  if (!confirm(`Delete "${ws.name}"? This cannot be undone.`)) return;
  router.delete(`/super-admin/workspaces/${ws.id}`, { preserveScroll: true });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

.theme-dark {
  --dashboard-bg: #10121c; --panel-bg: #171a26; --card-inner-bg: #1d2130; --input-element-bg: #212536;
  --border-subtle: rgba(148,163,210,0.09); --border-deep: rgba(148,163,210,0.16); --border-divider: rgba(148,163,210,0.08);
  --text-main: #d9dbe7; --text-header: #f6f7fb; --text-muted: #7d83a0; --accent: #1fd1ab; --accent-soft: rgba(31,209,171,0.16); --c-red: #d6484f;
}
.theme-light {
  --dashboard-bg: #eef1f7; --panel-bg: #ffffff; --card-inner-bg: #f5f7fb; --input-element-bg: #f0f2f8;
  --border-subtle: rgba(30,35,70,0.08); --border-deep: rgba(30,35,70,0.14); --border-divider: rgba(30,35,70,0.07);
  --text-main: #2d3142; --text-header: #12141f; --text-muted: #767c93; --accent: #0b8a75; --accent-soft: rgba(11,138,117,0.1); --c-red: #d6484f;
}

* { box-sizing: border-box; }
.dashboard { display: flex; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: var(--dashboard-bg); color: var(--text-main); }
.main-content { flex: 1; }
.content-wrapper { max-width: 1400px; margin: 0 auto; padding: 28px 40px 60px; }
.dashboard h2, .dashboard h3 { font-family: 'Lexend', 'Inter', sans-serif; }

.topbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 30px; background: var(--panel-bg); border-bottom: 1px solid var(--border-subtle); position: sticky; top: 0; z-index: 20; flex-wrap: wrap; gap: 12px; }
.topbar-greeting h2 { font-size: 16px; margin: 0; color: var(--text-header); }
.topbar-greeting p { margin-top: 4px; color: var(--text-muted); font-size: 12.5px; }
.topbar-icons { display: flex; align-items: center; gap: 10px; }
.search-box { width: 220px; padding: 9px 13px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); color: var(--text-main); font-size: 13px; }
.theme-btn { width: 36px; height: 36px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); cursor: pointer; }
.view-toggle-btn { border: none; background: var(--accent); color: #06120f; padding: 9px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 12.5px; white-space: nowrap; }

.workspace-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.workspace-card { background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 18px; }
.workspace-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.ws-icon { width: 38px; height: 38px; border-radius: 10px; background: var(--accent); color: #06120f; display: flex; align-items: center; justify-content: center; font-family: 'Lexend', sans-serif; font-weight: 700; font-size: 15px; }
.ws-actions { display: flex; gap: 6px; }
.icon-action { width: 30px; height: 30px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--card-inner-bg); cursor: pointer; font-size: 12px; }
.icon-action.danger:hover { border-color: var(--c-red); }
.workspace-card h3 { font-size: 15px; margin: 0 0 6px; color: var(--text-header); }
.ws-desc { font-size: 12.5px; color: var(--text-muted); margin: 0 0 16px; line-height: 1.5; min-height: 34px; }
.ws-stats { display: flex; gap: 10px; border-top: 1px solid var(--border-divider); padding-top: 14px; }
.ws-stats div { flex: 1; text-align: center; }
.ws-stats strong { display: block; font-family: 'IBM Plex Mono', monospace; font-size: 16px; color: var(--text-header); }
.ws-stats span { font-size: 10.5px; color: var(--text-muted); }
.empty-state-inline { text-align: center; padding: 40px 0; color: var(--text-muted); font-size: 13px; grid-column: 1/-1; }

.modal-overlay { position: fixed; inset: 0; background: rgba(6,7,12,.65); backdrop-filter: blur(6px); display: flex; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
.modal { width: 460px; max-width: 95%; background: var(--panel-bg); border-radius: 14px; border: 1px solid var(--border-deep); padding: 26px; }
.modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.modal-header h2 { font-size: 19px; color: var(--text-header); margin-bottom: 5px; }
.modal-header p { color: var(--text-muted); font-size: 12.5px; }
.close-btn { width: 32px; height: 32px; border: none; border-radius: 8px; background: var(--card-inner-bg); color: var(--text-muted); cursor: pointer; }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; margin-bottom: 7px; color: var(--text-muted); font-weight: 600; font-size: 12.5px; }
.form-group input, .form-group textarea { width: 100%; padding: 12px 14px; border-radius: 9px; border: 1px solid var(--border-deep); background: var(--card-inner-bg); color: var(--text-main); font-size: 13.5px; box-sizing: border-box; resize: none; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
.cancel-btn { padding: 11px 20px; border: 1px solid var(--border-deep); border-radius: 9px; background: transparent; color: var(--text-main); cursor: pointer; }
.save-btn { padding: 11px 22px; border: none; border-radius: 9px; background: var(--accent); color: #06120f; cursor: pointer; font-weight: 600; font-size: 13px; }
.save-btn:disabled { opacity: .5; cursor: not-allowed; }
</style>