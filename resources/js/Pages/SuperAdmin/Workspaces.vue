<template>
    <Head title="Workspaces" />

    <div class="sa-page" :class="isDark ? 'theme-dark' : 'theme-light'">

        <Sidebar />

        <main class="sa-main">

            <div class="sa-topbar">
                <div class="sa-topbar-title">
                    <h2>Workspaces</h2>
                    <p>Every workspace with its administrator, team leaders and project progress</p>
                </div>

                <div class="sa-topbar-actions">
                    <input v-model="search" class="sa-input" placeholder="🔍  Search workspaces..." />
                    <button class="sa-btn" @click="openAdd">＋ Add workspace</button>
                    <button class="sa-icon-btn" aria-label="Toggle theme" @click="isDark = !isDark">
                        {{ isDark ? '☀️' : '🌙' }}
                    </button>
                </div>
            </div>

            <div class="sa-content">

                <!-- totals -->
                <section class="sa-stats">
                    <div class="sa-stat cyan">
                        <div class="sa-stat-icon">🏢</div>
                        <span class="sa-stat-label">Workspaces</span>
                        <h2 class="sa-stat-value">{{ stats.workspaces }}</h2>
                        <small class="sa-stat-sub">In the organisation</small>
                    </div>
                    <div class="sa-stat blue">
                        <div class="sa-stat-icon">🎯</div>
                        <span class="sa-stat-label">Team leaders</span>
                        <h2 class="sa-stat-value">{{ stats.leaders }}</h2>
                        <small class="sa-stat-sub">Across all workspaces</small>
                    </div>
                    <div class="sa-stat green">
                        <div class="sa-stat-icon">👥</div>
                        <span class="sa-stat-label">Team members</span>
                        <h2 class="sa-stat-value">{{ stats.members }}</h2>
                        <small class="sa-stat-sub">Working in teams</small>
                    </div>
                    <div class="sa-stat violet">
                        <div class="sa-stat-icon">📁</div>
                        <span class="sa-stat-label">Projects</span>
                        <h2 class="sa-stat-value">{{ stats.projects }}</h2>
                        <small class="sa-stat-sub">Assigned in total</small>
                    </div>
                </section>

                <!-- workspace cards -->
                <div class="ws-grid">
                    <article v-for="w in filtered" :key="w.id" class="ws-card">

                        <header class="ws-card-head">
                            <div class="ws-icon">🏢</div>
                            <div class="ws-card-title">
                                <h3>{{ w.name }}</h3>
                                <p>{{ w.description || 'No description' }}</p>
                            </div>
                            <div class="ws-card-actions">
                                <button class="sa-icon-btn sm" title="Edit workspace" @click="openRename(w)">✏️</button>
                                <button class="sa-icon-btn danger sm" title="Delete workspace" @click="openDelete(w)">🗑</button>
                            </div>
                        </header>

                        <!-- administrator -->
                        <div class="ws-admin" :class="{ none: !w.admin }">
                            <template v-if="w.admin">
                                <div class="sa-avatar" :class="av(w.admin.id)">{{ initials(w.admin.name) }}</div>
                                <div class="ws-admin-info">
                                    <strong>{{ w.admin.name }}</strong>
                                    <span>{{ w.admin.email }}</span>
                                </div>
                                <span class="sa-pill violet">🛡️ Administrator</span>
                            </template>
                            <span v-else class="sa-pill">No administrator</span>
                        </div>

                        <!-- project numbers -->
                        <div class="ws-metrics">
                            <div>
                                <strong>{{ w.projects_total }}</strong>
                                <span>Assigned</span>
                            </div>
                            <div class="good">
                                <strong>{{ w.completed }}</strong>
                                <span>Completed</span>
                            </div>
                            <div class="info">
                                <strong>{{ w.in_progress }}</strong>
                                <span>In progress</span>
                            </div>
                            <div class="wait">
                                <strong>{{ w.pending }}</strong>
                                <span>Pending</span>
                            </div>
                        </div>

                        <div class="ws-rate">
                            <div class="ws-rate-head">
                                <span>Completion rate</span>
                                <div>
                                    <span v-if="w.overdue" class="sa-pill red">{{ w.overdue }} overdue</span>
                                    <strong>{{ w.completion_rate }}%</strong>
                                </div>
                            </div>

                            <div class="status-bar">
                                <div class="s-done" :style="{ width: pct(w, w.completed) + '%' }"></div>
                                <div class="s-active" :style="{ width: pct(w, w.in_progress) + '%' }"></div>
                                <div class="s-wait" :style="{ width: pct(w, w.pending) + '%' }"></div>
                            </div>
                        </div>

                        <!-- team leaders -->
                        <div class="ws-leaders">
                            <div class="ws-leaders-title">
                                <span>Team leaders</span>
                                <span class="sa-pill blue">{{ w.leaders.length }}</span>
                            </div>

                            <ul v-if="w.leaders.length" class="leader-list">
                                <li v-for="l in w.leaders" :key="l.id" class="leader-row">
                                    <div class="sa-avatar sm" :class="av(l.id)">{{ initials(l.name) }}</div>
                                    <div class="leader-info">
                                        <strong>{{ l.name }}</strong>
                                        <span>🎯 TL{{ l.level ?? 1 }} · {{ l.members_count }} members</span>
                                    </div>
                                    <span class="sa-pill cyan">{{ l.projects_count }} projects</span>
                                </li>
                            </ul>
                            <div v-else class="leader-empty">No team leaders in this workspace yet.</div>
                        </div>

                        <footer class="ws-card-foot">
                            <span>👥 {{ w.members_count }} members</span>
                            <span>Created {{ fmt(w.created_at) }}</span>
                        </footer>
                    </article>

                    <div v-if="!filtered.length" class="sa-empty full">
                        {{ workspaces.length ? 'No workspaces match your search.' : 'No workspaces yet. Click “Add workspace” to create the first one.' }}
                    </div>
                </div>
            </div>
        </main>

        <!-- add / rename -->
        <div v-if="formModal.show" class="sa-modal-overlay" @click.self="closeForm">
            <div class="sa-modal form">
                <h3>{{ formModal.mode === 'add' ? 'Add workspace' : 'Edit workspace' }}</h3>
                <p class="modal-sub">
                    {{ formModal.mode === 'add'
                        ? 'Create a workspace and choose the administrator who will own it.'
                        : 'Change the name, the description or the administrator of this workspace.' }}
                </p>

                <div class="form-group">
                    <label>Workspace name</label>
                    <input v-model="form.name" type="text" placeholder="e.g. Marketing Team" maxlength="100" />
                    <p v-if="errors.name" class="error-text">{{ errors.name }}</p>
                </div>

                <div class="form-group">
                    <label>Administrator (owner)</label>

                    <select v-model="form.owner">
                        <option value="" disabled>Select an administrator</option>

                        <optgroup v-if="admins.length" label="Administrators">
                            <option v-for="a in admins" :key="'a' + a.id" :value="'admin:' + a.id">
                                {{ a.name }} ({{ a.email }})
                            </option>
                        </optgroup>

                        <optgroup v-if="candidates.length" label="Team members (will be promoted to administrator)">
                            <option v-for="m in candidates" :key="'m' + m.id" :value="'member:' + m.id"
                                :disabled="!m.has_login">
                                {{ m.name }} · {{ m.workspace_name }}{{ m.has_login ? '' : ' — no login account' }}
                            </option>
                        </optgroup>
                    </select>

                    <p v-if="selectedMember" class="warn-text">
                        ⚠ {{ selectedMember.name }} will become an administrator and will be removed from their team.
                    </p>
                    <p v-if="!admins.length && !candidates.length" class="hint-text">
                        There are no administrators or team members to choose from yet.
                    </p>
                    <p v-if="ownerError" class="error-text">{{ ownerError }}</p>
                </div>

                <div class="form-group">
                    <label>Description <small>(optional)</small></label>
                    <textarea v-model="form.description" rows="3" maxlength="500"
                        placeholder="What is this workspace for?"></textarea>
                    <p v-if="errors.description" class="error-text">{{ errors.description }}</p>
                </div>

                <div class="sa-modal-actions end">
                    <button class="sa-btn ghost" @click="closeForm">Cancel</button>
                    <button class="sa-btn" :disabled="saving" @click="submitForm">
                        {{ saving ? 'Saving...' : (formModal.mode === 'add' ? 'Create workspace' : 'Save changes') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- delete -->
        <div v-if="deleteModal.show" class="sa-modal-overlay" @click.self="closeDelete">
            <div class="sa-modal form">
                <div class="sa-modal-icon">⚠️</div>
                <h3 class="center">Delete “{{ deleteModal.workspace.name }}”?</h3>

                <ul class="delete-list">
                    <li>🗑 <strong>{{ deleteModal.workspace.projects_total }}</strong> project(s) and all of their tasks will be deleted.</li>
                    <li>
                        👥 <strong>{{ deleteModal.workspace.leaders.length }}</strong> team leader(s) and
                        <strong>{{ deleteModal.workspace.members_count }}</strong> member(s) will stay in the system, but
                        will be removed from this workspace and from their teams.
                    </li>
                    <li>This cannot be undone.</li>
                </ul>

                <div class="form-group">
                    <label>
                        Type <strong class="danger-text">{{ deleteModal.workspace.name }}</strong> to confirm
                    </label>
                    <input v-model="deleteModal.typed" type="text" placeholder="Enter the exact workspace name"
                        @keyup.enter="canDelete && confirmDelete()" />
                </div>

                <div class="sa-modal-actions end">
                    <button class="sa-btn ghost" @click="closeDelete">Cancel</button>
                    <button class="sa-btn danger" :disabled="!canDelete || deleteModal.busy" @click="confirmDelete">
                        {{ deleteModal.busy ? 'Deleting...' : 'Delete workspace' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import Sidebar from "./Sidebar.vue";

const toast = useToast();

const props = defineProps({
    workspaces: { type: Array, default: () => [] },
    admins: { type: Array, default: () => [] },
    candidates: { type: Array, default: () => [] },
    stats: {
        type: Object,
        default: () => ({ workspaces: 0, leaders: 0, members: 0, projects: 0 }),
    },
});

/* theme (same key on every Super Admin page) */
const isDark = ref(localStorage.getItem("sa_theme") !== "light");
watch(isDark, (v) => localStorage.setItem("sa_theme", v ? "dark" : "light"));

/* ---------- helpers ---------- */
const initials = (name) =>
    (name || "?").split(" ").filter(Boolean).slice(0, 2).map((w) => w[0]).join("").toUpperCase();
const av = (id) => "av-" + (Number(id) % 6);
const fmt = (d) =>
    d ? new Date(d + "T00:00:00").toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" }) : "—";
const pct = (w, n) => (w.projects_total ? (n / w.projects_total) * 100 : 0);

const firstError = (errs) => {
    const v = Object.values(errs || {})[0];
    return Array.isArray(v) ? v[0] : v;
};
const flatErrors = (errs) => {
    const flat = {};
    Object.keys(errs || {}).forEach((k) => {
        flat[k] = Array.isArray(errs[k]) ? errs[k][0] : errs[k];
    });
    return flat;
};

/* ---------- search ---------- */
const search = ref("");
const q = computed(() => search.value.trim().toLowerCase());
const hit = (text) => !q.value || (text || "").toLowerCase().includes(q.value);

const filtered = computed(() =>
    props.workspaces.filter(
        (w) =>
            hit(w.name) ||
            hit(w.description) ||
            (w.admin && (hit(w.admin.name) || hit(w.admin.email))) ||
            w.leaders.some((l) => hit(l.name))
    )
);

/* ---------- add / rename ---------- */
const formModal = reactive({ show: false, mode: "add", id: null });
const form = reactive({ name: "", description: "", owner: "" });
const errors = ref({});
const saving = ref(false);

// a team member picked as the new administrator
const selectedMember = computed(() => {
    if (!String(form.owner).startsWith("member:")) return null;
    const id = Number(String(form.owner).split(":")[1]);
    return props.candidates.find((m) => m.id === id) || null;
});

const ownerError = computed(
    () => errors.value.owner || errors.value.owner_id || errors.value.promote_member_id || ""
);

const openAdd = () => {
    form.name = "";
    form.description = "";
    form.owner = "";
    errors.value = {};
    Object.assign(formModal, { show: true, mode: "add", id: null });
};

const openRename = (w) => {
    form.name = w.name;
    form.description = w.description || "";
    form.owner = w.admin ? `admin:${w.admin.id}` : "";
    errors.value = {};
    Object.assign(formModal, { show: true, mode: "rename", id: w.id });
};

const closeForm = () => {
    formModal.show = false;
    errors.value = {};
};

const ownerPayload = () => {
    const [type, id] = String(form.owner).split(":");
    if (type === "member") return { promote_member_id: Number(id) };
    if (type === "admin") return { owner_id: Number(id) };
    return {};
};

const submitForm = () => {
    errors.value = {};

    if (form.name.trim().length < 3) {
        errors.value = { name: "The name must be at least 3 characters." };
        return;
    }
    if (formModal.mode === "add" && !form.owner) {
        errors.value = { owner: "Please pick an administrator." };
        return;
    }

    saving.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(formModal.mode === "add" ? "Workspace created." : "Workspace updated.");
            closeForm();
        },
        onError: (errs) => {
            errors.value = flatErrors(errs);
            toast.error(firstError(errs) || "Could not save the workspace.");
        },
        onFinish: () => { saving.value = false; },
    };

    const payload = {
        ...ownerPayload(),
        name: form.name.trim(),
        description: form.description,
    };

    if (formModal.mode === "add") {
        router.post("/super-admin/workspaces", payload, options);
    } else {
        router.put(`/super-admin/workspaces/${formModal.id}`, payload, options);
    }
};

/* ---------- delete ---------- */
const deleteModal = reactive({ show: false, workspace: null, typed: "", busy: false });

const canDelete = computed(
    () => !!deleteModal.workspace && deleteModal.typed.trim() === deleteModal.workspace.name
);

const openDelete = (w) => {
    Object.assign(deleteModal, { show: true, workspace: w, typed: "", busy: false });
};

const closeDelete = () => {
    deleteModal.show = false;
    deleteModal.workspace = null;
    deleteModal.typed = "";
};

const confirmDelete = () => {
    if (!canDelete.value) return;

    deleteModal.busy = true;

    router.delete(`/super-admin/workspaces/${deleteModal.workspace.id}`, {
        data: { confirm_name: deleteModal.typed.trim() },
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Workspace deleted.");
            closeDelete();
        },
        onError: (errs) => toast.error(firstError(errs) || "Could not delete the workspace."),
        onFinish: () => { deleteModal.busy = false; },
    });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

/* ---------- theme variables ---------- */
.sa-page.theme-dark {
    --dashboard-bg: #10121c;
    --panel-bg: #171a26;
    --card-inner-bg: #1d2130;
    --card-inner-hover: #262b3d;
    --input-element-bg: #212536;
    --border-subtle: rgba(148, 163, 210, 0.09);
    --border-deep: rgba(148, 163, 210, 0.16);
    --border-divider: rgba(148, 163, 210, 0.08);
    --text-main: #d9dbe7;
    --text-header: #f6f7fb;
    --text-muted: #7d83a0;
    --text-card-sub: #b1b5cc;
    --shadow-cards: rgba(3, 4, 10, 0.45);
    --shadow-hover: rgba(3, 4, 10, 0.55);
    --accent: #1fd1ab;
    --accent-soft: rgba(31, 209, 171, 0.16);
    --on-accent: #06110e;
}

.sa-page.theme-light {
    --dashboard-bg: #eef1f7;
    --panel-bg: #ffffff;
    --card-inner-bg: #f5f7fb;
    --card-inner-hover: #eaedf5;
    --input-element-bg: #f0f2f8;
    --border-subtle: rgba(30, 35, 70, 0.08);
    --border-deep: rgba(30, 35, 70, 0.14);
    --border-divider: rgba(30, 35, 70, 0.07);
    --text-main: #2d3142;
    --text-header: #12141f;
    --text-muted: #767c93;
    --text-card-sub: #454a5f;
    --shadow-cards: rgba(24, 28, 55, 0.06);
    --shadow-hover: rgba(24, 28, 55, 0.12);
    --accent: #0b8a75;
    --accent-soft: rgba(11, 138, 117, 0.1);
    --on-accent: #ffffff;
}

.sa-page {
    --c-blue: #3e63dd;
    --c-violet: #7c5cd6;
    --c-green: #0e9a7f;
    --c-amber: #c2792c;
    --c-cyan: #3184c2;
    --c-red: #d6484f;
    --c-gold: #d9a441;

    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    display: flex;
    height: 100vh;
    overflow: hidden;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: var(--dashboard-bg);
    color: var(--text-main);
    -webkit-font-smoothing: antialiased;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.sa-page *,
.sa-page *::before,
.sa-page *::after {
    box-sizing: border-box;
}

.sa-page h1,
.sa-page h2,
.sa-page h3,
.sa-page h4,
.sa-page p {
    margin: 0;
}

.sa-page h2,
.sa-page h3,
.sa-page h4 {
    font-family: 'Lexend', 'Inter', sans-serif;
}

/* ---------- main area ---------- */
.sa-main {
    flex: 1;
    min-width: 0;
    overflow-y: auto;
}

.sa-topbar {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 30px;
    background: var(--panel-bg);
    border-bottom: 1px solid var(--border-subtle);
}

.sa-topbar-title h2 {
    font-size: 17px;
    font-weight: 600;
    letter-spacing: -0.2px;
    color: var(--text-header);
}

.sa-topbar-title p {
    margin-top: 4px;
    font-size: 12.5px;
    color: var(--text-muted);
}

.sa-topbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sa-content {
    max-width: 1640px;
    margin: 0 auto;
    padding: 28px 40px 60px;
}

/* ---------- cards & grids ---------- */
.sa-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 1px 2px var(--shadow-cards);
}

.sa-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.sa-card-head h3 {
    font-size: 14.5px;
    font-weight: 600;
    color: var(--text-header);
}

.sa-grid-2 {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}

@media (max-width: 1200px) {
    .sa-grid-2 {
        grid-template-columns: 1fr;
    }
}

/* ---------- stat cards ---------- */
.sa-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}

.sa-stat {
    --tone: var(--c-blue);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding: 18px 18px 20px;
    border-radius: 12px;
    border: 1px solid var(--border-subtle);
    background: linear-gradient(165deg, color-mix(in srgb, var(--tone) 9%, transparent), var(--panel-bg) 60%);
    box-shadow: 0 1px 2px var(--shadow-cards);
    transition: all 0.18s ease;
}

.sa-stat:hover {
    transform: translateY(-2px);
    border-color: var(--border-deep);
    box-shadow: 0 10px 22px -8px var(--shadow-hover);
}

.sa-stat::before {
    content: "";
    position: absolute;
    left: 0;
    top: 14px;
    bottom: 14px;
    width: 3px;
    border-radius: 0 3px 3px 0;
    background: var(--tone);
}

.sa-stat.blue { --tone: var(--c-blue); }
.sa-stat.violet { --tone: var(--c-violet); }
.sa-stat.green { --tone: var(--c-green); }
.sa-stat.amber { --tone: var(--c-amber); }
.sa-stat.cyan { --tone: var(--c-cyan); }
.sa-stat.red { --tone: var(--c-red); }

.sa-stat-icon {
    width: 34px;
    height: 34px;
    margin: 0 0 14px 5px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    background: color-mix(in srgb, var(--tone) 16%, transparent);
}

.sa-stat-label {
    margin-left: 5px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--text-muted);
}

.sa-stat-value {
    margin: 7px 0 0 5px !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 28px;
    font-weight: 600;
    letter-spacing: -0.5px;
    color: var(--text-header);
}

.sa-stat-sub {
    margin: 6px 0 0 5px;
    font-size: 11px;
    color: var(--text-muted);
}

/* ---------- buttons, inputs, pills, avatars ---------- */
.sa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 18px;
    border: none;
    border-radius: 9px;
    background: var(--accent);
    color: var(--on-accent);
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: opacity 0.15s ease;
}

.sa-btn:hover:not(:disabled) { opacity: 0.9; }
.sa-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.sa-btn.ghost { background: transparent; color: var(--text-main); border: 1px solid var(--border-deep); }
.sa-btn.ghost:hover:not(:disabled) { background: var(--card-inner-hover); }
.sa-btn.danger { background: var(--c-red); color: #fff; }
.sa-btn.success { background: var(--c-green); color: #fff; }

.sa-icon-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.sa-icon-btn:hover { background: var(--card-inner-hover); border-color: var(--border-deep); }
.sa-icon-btn.danger:hover { background: rgba(214, 72, 79, 0.14); border-color: rgba(214, 72, 79, 0.4); }
.sa-icon-btn.sm { width: 30px; height: 30px; font-size: 12px; }

.sa-input {
    width: 250px;
    padding: 9px 13px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 13px;
    outline: none;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s ease;
}

.sa-input::placeholder { color: var(--text-muted); }
.sa-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); background: var(--panel-bg); }

.sa-pill {
    --tone: var(--text-muted);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 600;
    white-space: nowrap;
    color: var(--tone);
    background: color-mix(in srgb, var(--tone) 14%, transparent);
}

.sa-pill.green { --tone: var(--c-green); }
.sa-pill.blue { --tone: var(--c-blue); }
.sa-pill.amber { --tone: var(--c-amber); }
.sa-pill.red { --tone: var(--c-red); }
.sa-pill.violet { --tone: var(--c-violet); }
.sa-pill.cyan { --tone: var(--c-cyan); }

.sa-avatar {
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    font-family: 'Lexend', sans-serif;
    font-weight: 600;
    font-size: 12px;
    color: #fff;
    background: var(--c-cyan);
    user-select: none;
}

.sa-avatar.sm { width: 28px; height: 28px; font-size: 10.5px; border-radius: 7px; }
.sa-avatar.lg { width: 44px; height: 44px; font-size: 15px; border-radius: 11px; }
.sa-avatar.av-0 { background: var(--c-blue); }
.sa-avatar.av-1 { background: var(--c-violet); }
.sa-avatar.av-2 { background: var(--c-green); }
.sa-avatar.av-3 { background: var(--c-amber); }
.sa-avatar.av-4 { background: var(--c-cyan); }
.sa-avatar.av-5 { background: var(--c-red); }

/* ---------- tabs, progress, empty ---------- */
.sa-tabs {
    display: inline-flex;
    gap: 3px;
    padding: 4px;
    margin-bottom: 22px;
    border-radius: 11px;
    background: var(--input-element-bg);
    border: 1px solid var(--border-subtle);
}

.sa-tabs button {
    padding: 9px 18px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--text-muted);
    font-weight: 500;
    font-size: 13px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: all 0.15s ease;
}

.sa-tabs button.active { background: var(--accent); color: var(--on-accent); font-weight: 600; }

.sa-progress {
    height: 6px;
    border-radius: 999px;
    overflow: hidden;
    background: var(--border-deep);
}

.sa-progress > div {
    height: 100%;
    border-radius: 999px;
    background: var(--accent);
    transition: width 0.4s ease;
}

.sa-empty {
    padding: 26px 12px;
    text-align: center;
    font-size: 13px;
    color: var(--text-muted);
    border: 1px dashed var(--border-deep);
    border-radius: 10px;
}

/* ---------- confirm modal ---------- */
.sa-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(6, 7, 12, 0.65);
    backdrop-filter: blur(6px);
}

.sa-modal {
    width: 420px;
    max-width: 95%;
    padding: 28px;
    text-align: center;
    border-radius: 14px;
    background: var(--panel-bg);
    border: 1px solid var(--border-deep);
    box-shadow: 0 30px 80px var(--shadow-cards);
}

.sa-modal-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 22px;
    background: rgba(214, 72, 79, 0.14);
}

.sa-modal h3 { font-size: 17px; color: var(--text-header); margin-bottom: 8px; }
.sa-modal p { font-size: 13px; line-height: 1.55; color: var(--text-muted); }
.sa-modal-actions { display: flex; justify-content: center; gap: 12px; margin-top: 22px; }


/* ---------- workspace cards ---------- */
.ws-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(430px, 1fr));
    gap: 18px;
}

.ws-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 20px;
    border-radius: 16px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    box-shadow: 0 1px 2px var(--shadow-cards);
    transition: all 0.18s ease;
}

.ws-card:hover {
    transform: translateY(-2px);
    border-color: var(--border-deep);
    box-shadow: 0 14px 30px -12px var(--shadow-hover);
}

.ws-card-head {
    display: flex;
    align-items: flex-start;
    gap: 13px;
}

.ws-icon {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 21px;
    background: var(--accent-soft);
    border: 1px solid color-mix(in srgb, var(--accent) 35%, transparent);
}

.ws-card-title {
    flex: 1;
    min-width: 0;
}

.ws-card-title h3 {
    font-size: 17px;
    font-weight: 600;
    color: var(--text-header);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ws-card-title p {
    margin-top: 3px;
    font-size: 12px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ws-card-actions {
    display: flex;
    gap: 6px;
}

.ws-admin {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.ws-admin.none {
    justify-content: center;
}

.ws-admin-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.ws-admin-info strong {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-header);
}

.ws-admin-info span {
    font-size: 11.5px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ws-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.ws-metrics div {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 11px 6px;
    border-radius: 10px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.ws-metrics strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 20px;
    font-weight: 600;
    color: var(--text-header);
}

.ws-metrics span {
    margin-top: 3px;
    font-size: 10.5px;
    color: var(--text-muted);
}

.ws-metrics .good strong { color: var(--c-green); }
.ws-metrics .info strong { color: var(--c-blue); }
.ws-metrics .wait strong { color: var(--c-amber); }

.ws-rate-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 12px;
    color: var(--text-muted);
}

.ws-rate-head div {
    display: flex;
    align-items: center;
    gap: 8px;
}

.ws-rate-head strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 14px;
    color: var(--text-header);
}

.status-bar {
    display: flex;
    height: 8px;
    border-radius: 999px;
    overflow: hidden;
    background: var(--border-deep);
}

.status-bar div {
    height: 100%;
    transition: width 0.4s ease;
}

.s-done { background: var(--c-green); }
.s-active { background: var(--c-blue); }
.s-wait { background: var(--c-amber); }

.ws-leaders-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--text-muted);
}

.leader-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 7px;
    max-height: 220px;
    overflow-y: auto;
}

.leader-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 11px;
    border-radius: 10px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
}

.leader-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.leader-info strong {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-main);
}

.leader-info span {
    font-size: 11px;
    color: var(--text-muted);
}

.leader-empty {
    padding: 16px 10px;
    text-align: center;
    font-size: 12.5px;
    color: var(--text-muted);
    border: 1px dashed var(--border-deep);
    border-radius: 10px;
}

.ws-card-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid var(--border-divider);
    font-size: 11.5px;
    color: var(--text-muted);
}

.sa-empty.full {
    grid-column: 1 / -1;
}

/* ---------- modals ---------- */
.sa-modal.form {
    width: 500px;
    text-align: left;
}

.sa-modal.form .sa-modal-icon {
    margin-left: auto;
    margin-right: auto;
}

.sa-modal.form h3.center {
    text-align: center;
}

.modal-sub {
    margin-bottom: 20px;
}

.sa-modal-actions.end {
    justify-content: flex-end;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.form-group label {
    margin-bottom: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-card-sub);
}

.form-group label small {
    font-weight: 400;
    color: var(--text-muted);
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 11px 14px;
    border-radius: 10px;
    border: 1px solid var(--border-deep);
    background: var(--card-inner-bg);
    color: var(--text-main);
    font-size: 13.5px;
    outline: none;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group textarea {
    resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.form-group input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.error-text {
    margin-top: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--c-red);
}

.hint-text {
    margin-top: 6px;
    font-size: 12px;
    color: var(--text-muted);
}

.danger-text {
    color: var(--c-red);
    user-select: none;
}

.delete-list {
    list-style: none;
    margin: 0 0 18px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 9px;
    border-radius: 10px;
    background: rgba(214, 72, 79, 0.08);
    border: 1px solid rgba(214, 72, 79, 0.28);
    font-size: 13px;
    line-height: 1.5;
    color: var(--text-card-sub);
}

@media (max-width: 560px) {
    .ws-grid {
        grid-template-columns: 1fr;
    }
}

.warn-text {
    margin-top: 8px;
    padding: 9px 12px;
    border-radius: 9px;
    font-size: 12.5px;
    line-height: 1.45;
    color: var(--c-amber);
    background: color-mix(in srgb, var(--c-amber) 12%, transparent);
    border: 1px solid color-mix(in srgb, var(--c-amber) 35%, transparent);
}
</style>