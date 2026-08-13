<template>

  <Head title="Teams" />

  <div class="dashboard" :class="isDark ? 'theme-dark' : 'theme-light'">

    <SuperAdminSidebar />

    <main class="main-content">
      <div class="topbar">
        <div class="topbar-greeting">
          <h2>Teams &amp; People</h2>
          <p>Manage team leaders, members and administrators.</p>
        </div>

        <div class="topbar-icons">
          <input v-model="search" type="text" placeholder="Search teams, members, admins..." class="search-box" />
          <button class="theme-btn" @click="isDark = !isDark">{{ isDark ? '☀️' : '🌙' }}</button>
        </div>
      </div>

      <div class="content-wrapper">

        <section class="stats-grid">
          <div class="stat-card">
            <span class="stat-label">Total Teams</span>
            <h2 class="stat-value">{{ teams.length }}</h2>
          </div>
          <div class="stat-card">
            <span class="stat-label">Team Leaders</span>
            <h2 class="stat-value">{{ teams.length }}</h2>
          </div>
          <div class="stat-card">
            <span class="stat-label">Total Members</span>
            <h2 class="stat-value">{{ totalMembers }}</h2>
          </div>
          <div class="stat-card">
            <span class="stat-label">Administrators</span>
            <h2 class="stat-value">{{ admins.length }}</h2>
          </div>
        </section>

        <!-- TEAMS -->
        <section class="dashboard-card">
          <div class="card-header"><h2>Teams</h2></div>

          <div class="team-grid">
            <div v-for="team in filteredTeams" :key="team.id" class="team-card" @click="openTeam(team.id)">
              <div class="team-card-top">
                <div class="rep-avatar">{{ team.name.charAt(0).toUpperCase() }}</div>
                <div>
                  <strong>{{ team.name }}</strong>
                  <span>{{ team.workspace_name }}</span>
                </div>
              </div>
              <div class="team-card-stats">
                <span>👥 {{ team.members_count ?? (team.members?.length || 0) }} members</span>
                <span>📁 {{ team.projects_count ?? 0 }} projects</span>
                <span>✅ {{ team.completion_rate ?? 0 }}%</span>
              </div>
            </div>

            <div v-if="!filteredTeams.length" class="empty-state-inline">No teams found.</div>
          </div>
        </section>

        <!-- ADMINISTRATORS -->
        <section class="dashboard-card">
          <div class="card-header kanban-card-header">
            <h2>Administrators &amp; Team Leaders</h2>
            <button class="view-toggle-btn" @click="openCreateModal">+ Add Person</button>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
              <tbody>
                <tr v-for="admin in filteredAdmins" :key="admin.id">
                  <td>
                    <div class="admin-user-cell">
                      <div class="rep-avatar small">{{ admin.name.charAt(0) }}</div>
                      <strong>{{ admin.name }}</strong>
                    </div>
                  </td>
                  <td>{{ admin.email }}</td>
                  <td><span class="status-pill">{{ admin.role || 'ADMIN' }}</span></td>
                  <td>{{ new Date(admin.created_at).toLocaleDateString() }}</td>
                  <td>
                    <button class="remove-btn" @click.stop="removeAdmin(admin)">Remove</button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="!filteredAdmins.length" class="empty-state-inline">No administrators found.</div>
          </div>
        </section>

      </div>
    </main>

    <!-- TEAM DETAIL MODAL -->
    <div v-if="selectedTeam" class="modal-overlay" @click.self="closeTeam">
      <div class="modal team-modal">
        <div class="modal-header">
          <div>
            <h2>{{ selectedTeam.name }}</h2>
            <p>{{ selectedTeam.workspace_name }} · Team Leader</p>
          </div>
          <button class="close-btn" @click="closeTeam">✕</button>
        </div>

        <div class="team-modal-stats">
          <div><strong>{{ selectedTeam.completion_rate ?? 0 }}%</strong><span>Completion</span></div>
          <div><strong>{{ selectedTeam.projects_count ?? 0 }}</strong><span>Projects</span></div>
          <div><strong>{{ selectedTeam.members_count ?? (selectedTeam.members?.length || 0) }}</strong><span>Members</span></div>
        </div>

        <h4 class="modal-subhead">Team Members</h4>
        <div class="modal-member-list">
          <div v-for="m in selectedTeam.members" :key="m.id" class="modal-member-row">
            <div class="rep-avatar small">{{ m.name.charAt(0) }}</div>
            <div class="modal-member-info"><strong>{{ m.name }}</strong><span>{{ m.department || m.role }}</span></div>
            <button class="remove-btn" @click="removeMember(m.id)">Remove</button>
          </div>
          <div v-if="!selectedTeam.members?.length" class="empty-state-inline">No members in this team yet.</div>
        </div>

        <h4 class="modal-subhead">Add Member</h4>
        <div class="add-member-row">
          <select v-model="memberToAdd">
            <option value="" disabled>Select a member to add</option>
            <option v-for="m in candidateMembers" :key="m.id" :value="m.id">{{ m.name }} ({{ m.email }})</option>
          </select>
          <button class="save-btn small" :disabled="!memberToAdd" @click="addMember">Add</button>
        </div>

        <div class="modal-danger-zone">
          <div>
            <strong>Remove this team</strong>
            <span>Deletes the team and unassigns its team leader. Members are not deleted.</span>
          </div>
          <button class="remove-btn" @click="removeTeam">Delete Team</button>
        </div>
      </div>
    </div>

    <!-- CREATE PERSON MODAL -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
      <div class="modal">
        <div class="modal-header">
          <div><h2>Add Person</h2><p>Create an Administrator or Team Leader</p></div>
          <button class="close-btn" @click="showCreateModal = false">✕</button>
        </div>

        <div class="form-group">
          <label>Role</label>
          <select v-model="form.role">
            <option value="ADMIN">Administrator</option>
            <option value="TL">Team Leader</option>
          </select>
        </div>
        <div class="form-group" v-if="form.role === 'TL'">
          <label>Workspace</label>
          <select v-model="form.workspace_id">
            <option value="" disabled>Select a workspace</option>
            <option v-for="w in workspaces" :key="w.id" :value="w.id">{{ w.name }}</option>
          </select>
        </div>
        <div class="form-group"><label>Name</label><input v-model="form.name" placeholder="John Doe"></div>
        <div class="form-group"><label>Email</label><input v-model="form.email" type="email"></div>
        <div class="form-group"><label>Password</label><input v-model="form.password" type="password"></div>
        <div class="form-group"><label>Confirm Password</label><input v-model="form.password_confirmation" type="password"></div>

        <div class="modal-actions">
          <button class="cancel-btn" @click="showCreateModal = false">Cancel</button>
          <button class="save-btn" @click="createPerson">Create</button>
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
  teams: { type: Array, default: () => [] },
  admins: { type: Array, default: () => [] },
  allMembers: { type: Array, default: () => [] },
  workspaces: { type: Array, default: () => [] },
});

const isDark = ref(true);
const search = ref("");
const selectedTeamId = ref(null);
const memberToAdd = ref("");
const showCreateModal = ref(false);

const totalMembers = computed(() => (props.teams || []).reduce((sum, t) => sum + (t.members_count ?? t.members?.length ?? 0), 0));

const filteredTeams = computed(() => {
  if (!search.value) return props.teams || [];
  const term = search.value.toLowerCase();
  return (props.teams || []).filter(t => t.name?.toLowerCase().includes(term) || t.workspace_name?.toLowerCase().includes(term));
});

const filteredAdmins = computed(() => {
  if (!search.value) return props.admins || [];
  const term = search.value.toLowerCase();
  return (props.admins || []).filter(a => a.name?.toLowerCase().includes(term) || a.email?.toLowerCase().includes(term));
});

/* ---------------- Team modal ---------------- */
const selectedTeam = computed(() => (props.teams || []).find(t => t.id === selectedTeamId.value) || null);

const candidateMembers = computed(() => {
  if (!selectedTeam.value) return [];
  return (props.allMembers || []).filter(m =>
    m.workspace_id === selectedTeam.value.workspace_id &&
    m.role !== "TL" &&
    m.assigned_to !== selectedTeam.value.id
  );
});

const openTeam = (id) => { selectedTeamId.value = id; memberToAdd.value = ""; };
const closeTeam = () => { selectedTeamId.value = null; memberToAdd.value = ""; };

const addMember = () => {
  if (!selectedTeam.value || !memberToAdd.value) return;
  router.post(`/super-admin/teams/${selectedTeam.value.id}/members/${memberToAdd.value}`, {}, { preserveScroll: true });
};

const removeMember = (memberId) => {
  if (!selectedTeam.value) return;
  router.delete(`/super-admin/teams/${selectedTeam.value.id}/members/${memberId}`, { preserveScroll: true });
};

const removeTeam = () => {
  if (!selectedTeam.value) return;
  if (!confirm(`Delete "${selectedTeam.value.name}"? This cannot be undone.`)) return;
  router.delete(`/super-admin/teams/${selectedTeam.value.id}`, {
    preserveScroll: true,
    onSuccess: () => closeTeam(),
  });
};

/* ---------------- Create / remove people ---------------- */
const form = ref({ role: "ADMIN", workspace_id: "", name: "", email: "", password: "", password_confirmation: "" });

function resetForm() {
  form.value = { role: "ADMIN", workspace_id: "", name: "", email: "", password: "", password_confirmation: "" };
}

const openCreateModal = () => { resetForm(); showCreateModal.value = true; };

function createPerson() {
  router.post("/super-admin/admin", form.value, {
    preserveScroll: true,
    onSuccess: () => { showCreateModal.value = false; resetForm(); },
  });
}

function removeAdmin(admin) {
  if (!confirm(`Remove ${admin.name}? This cannot be undone.`)) return;
  router.delete(`/super-admin/admin/${admin.id}`, { preserveScroll: true });
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

.theme-dark {
  --dashboard-bg: #10121c; --panel-bg: #171a26; --card-inner-bg: #1d2130; --card-inner-hover: #262b3d;
  --input-element-bg: #212536; --border-subtle: rgba(148,163,210,0.09); --border-deep: rgba(148,163,210,0.16);
  --border-divider: rgba(148,163,210,0.08); --text-main: #d9dbe7; --text-header: #f6f7fb; --text-muted: #7d83a0;
  --shadow-cards: rgba(3,4,10,0.45); --accent: #1fd1ab; --accent-soft: rgba(31,209,171,0.16); --c-red: #d6484f;
}
.theme-light {
  --dashboard-bg: #eef1f7; --panel-bg: #ffffff; --card-inner-bg: #f5f7fb; --card-inner-hover: #eaedf5;
  --input-element-bg: #f0f2f8; --border-subtle: rgba(30,35,70,0.08); --border-deep: rgba(30,35,70,0.14);
  --border-divider: rgba(30,35,70,0.07); --text-main: #2d3142; --text-header: #12141f; --text-muted: #767c93;
  --shadow-cards: rgba(24,28,55,0.06); --accent: #0b8a75; --accent-soft: rgba(11,138,117,0.1); --c-red: #d6484f;
}

* { box-sizing: border-box; }
.dashboard { display: flex; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: var(--dashboard-bg); color: var(--text-main); }
.main-content { flex: 1; }
.content-wrapper { max-width: 1400px; margin: 0 auto; padding: 28px 40px 60px; }
.dashboard h2, .dashboard h3, .dashboard h4 { font-family: 'Lexend', 'Inter', sans-serif; }

.topbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 30px; background: var(--panel-bg); border-bottom: 1px solid var(--border-subtle); position: sticky; top: 0; z-index: 20; }
.topbar-greeting h2 { font-size: 16px; margin: 0; color: var(--text-header); }
.topbar-greeting p { margin-top: 4px; color: var(--text-muted); font-size: 12.5px; }
.topbar-icons { display: flex; align-items: center; gap: 10px; }
.search-box { width: 230px; padding: 9px 13px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); color: var(--text-main); font-size: 13px; }
.theme-btn { width: 36px; height: 36px; border-radius: 7px; border: 1px solid var(--border-subtle); background: var(--input-element-bg); cursor: pointer; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
@media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
.stat-card { background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 18px; }
.stat-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); }
.stat-value { font-family: 'IBM Plex Mono', monospace; font-size: 24px; margin-top: 6px; color: var(--text-header); }

.dashboard-card { background: var(--panel-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 22px; margin-bottom: 18px; }
.card-header { margin-bottom: 18px; }
.card-header h2 { font-size: 14.5px; color: var(--text-header); margin: 0; }
.kanban-card-header { display: flex; justify-content: space-between; align-items: center; }
.view-toggle-btn { border: none; background: var(--accent); color: #06120f; padding: 9px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 12.5px; }

.team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 14px; }
.team-card { background: var(--card-inner-bg); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 16px; cursor: pointer; transition: border-color .15s ease; }
.team-card:hover { border-color: var(--border-deep); }
.team-card-top { display: flex; align-items: center; gap: 11px; margin-bottom: 12px; }
.team-card-top strong { display: block; color: var(--text-main); font-size: 13.5px; }
.team-card-top span { display: block; color: var(--text-muted); font-size: 11.5px; margin-top: 2px; }
.team-card-stats { display: flex; flex-direction: column; gap: 4px; font-size: 11.5px; color: var(--text-muted); }
.rep-avatar { width: 34px; height: 34px; border-radius: 9px; background: var(--accent); color: #06120f; display: flex; align-items: center; justify-content: center; font-family: 'Lexend', sans-serif; font-weight: 600; font-size: 12px; flex-shrink: 0; }
.rep-avatar.small { width: 28px; height: 28px; font-size: 10.5px; border-radius: 7px; }

.admin-table-wrap { overflow-x: auto; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th { padding: 10px 12px; text-align: left; color: var(--text-muted); font-size: 10.5px; text-transform: uppercase; border-bottom: 1px solid var(--border-divider); }
.admin-table td { padding: 12px; border-bottom: 1px solid var(--border-divider); font-size: 13px; color: var(--text-main); }
.admin-user-cell { display: flex; align-items: center; gap: 11px; }
.status-pill { font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 5px; background: var(--accent-soft); color: var(--accent); }
.remove-btn { border: none; background: rgba(214,72,79,.12); color: var(--c-red); padding: 6px 11px; border-radius: 7px; cursor: pointer; font-size: 11px; font-weight: 600; }
.remove-btn:hover { background: rgba(214,72,79,.22); }
.empty-state-inline { text-align: center; padding: 28px 0; color: var(--text-muted); font-size: 13px; width: 100%; }

/* Modals (shared pattern) */
.modal-overlay { position: fixed; inset: 0; background: rgba(6,7,12,.65); backdrop-filter: blur(6px); display: flex; justify-content: center; align-items: center; z-index: 9999; padding: 20px; }
.modal { width: 480px; max-width: 95%; background: var(--panel-bg); border-radius: 14px; border: 1px solid var(--border-deep); padding: 26px; max-height: 90vh; overflow-y: auto; }
.team-modal { width: 560px; }
.modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.modal-header h2 { font-size: 19px; color: var(--text-header); margin-bottom: 5px; }
.modal-header p { color: var(--text-muted); font-size: 12.5px; }
.close-btn { width: 32px; height: 32px; border: none; border-radius: 8px; background: var(--card-inner-bg); color: var(--text-muted); cursor: pointer; }
.team-modal-stats { display: flex; gap: 10px; margin-bottom: 22px; }
.team-modal-stats div { flex: 1; background: var(--card-inner-bg); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 12px; text-align: center; }
.team-modal-stats strong { display: block; font-family: 'IBM Plex Mono', monospace; font-size: 17px; color: var(--text-header); }
.team-modal-stats span { font-size: 10.5px; color: var(--text-muted); }
.modal-subhead { font-size: 11px; text-transform: uppercase; color: var(--text-muted); margin: 16px 0 9px; font-weight: 700; }
.modal-member-list { display: flex; flex-direction: column; gap: 7px; max-height: 170px; overflow-y: auto; }
.modal-member-row { display: flex; align-items: center; gap: 10px; background: var(--card-inner-bg); border-radius: 9px; padding: 9px 11px; }
.modal-member-info { flex: 1; display: flex; flex-direction: column; }
.modal-member-info strong { font-size: 13px; color: var(--text-main); }
.modal-member-info span { font-size: 11px; color: var(--text-muted); }
.add-member-row { display: flex; gap: 9px; }
.add-member-row select { flex: 1; padding: 11px 13px; border-radius: 9px; border: 1px solid var(--border-deep); background: var(--card-inner-bg); color: var(--text-main); }
.save-btn.small { padding: 9px 16px; font-size: 12.5px; }
.save-btn:disabled { opacity: .5; cursor: not-allowed; }
.modal-danger-zone { margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border-divider); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.modal-danger-zone strong { display: block; font-size: 13px; color: var(--text-main); }
.modal-danger-zone span { font-size: 11.5px; color: var(--text-muted); }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; margin-bottom: 7px; color: var(--text-muted); font-weight: 600; font-size: 12.5px; }
.form-group input, .form-group select { width: 100%; padding: 12px 14px; border-radius: 9px; border: 1px solid var(--border-deep); background: var(--card-inner-bg); color: var(--text-main); font-size: 13.5px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
.cancel-btn { padding: 11px 20px; border: 1px solid var(--border-deep); border-radius: 9px; background: transparent; color: var(--text-main); cursor: pointer; }
.save-btn { padding: 11px 22px; border: none; border-radius: 9px; background: var(--accent); color: #06120f; cursor: pointer; font-weight: 600; font-size: 13px; }
</style>