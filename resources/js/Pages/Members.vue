<template>
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">

            <!-- Header -->
            <header class="header">
                <div>
                    <h1>Members</h1>
                    <p>Manage team leaders and assign members.</p>
                </div>

                <div class="header-right">
                    <button class="theme-btn" @click="theme.toggleTheme">
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>

                    <input type="text" placeholder="Search member..." />

                    <button class="icon-btn">🔔</button>

                    <img src="https://i.pravatar.cc/100" class="avatar" />
                </div>
            </header>

            <!-- CREATE MEMBER -->
            <section class="create-section">

                <div class="create-card">

                    <div class="section-top">
                        <div>
                            <h2>Create Member</h2>
                            <p>Add new member to your workspace.</p>
                        </div>

                        <button @click="createMember">
                            Add Member
                        </button>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label>First Name</label>

                            <input type="text" v-model="form.firstName" placeholder="Enter first name" />

                            <small v-if="errors.firstName" class="error" style="color:red;">
                                {{ errors.firstName }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Last Name</label>

                            <input type="text" v-model="form.lastName" placeholder="Enter last name" />

                            <small v-if="errors.firstName" class="error" style="color:red;">
                                {{ errors.firstName }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>

                            <input type="email" v-model="form.email" placeholder="Enter email address" />

                            <small v-if="errors.email" class="error" style="color:red;">
                                {{ errors.email }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>

                            <input type="text" v-model="form.phone" placeholder="Enter phone number" />

                            <small v-if="errors.phone" class="error" style="color:red;">
                                {{ errors.phone }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Department</label>

                            <input type="text" v-model="form.department" placeholder="UI/UX, Development, QA..." />

                            <small v-if="errors.department" class="error" style="color:red;">
                                {{ errors.department }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Role</label>

                            <select v-model="form.role">
                                <option value="TL">Team Leader</option>
                                <option value="Member">Team Member</option>
                            </select>

                            <small v-if="errors.role" class="error" style="color:red;">
                                {{ errors.role }}
                            </small>
                        </div>

                    </div>

                </div>

            </section>

            <!-- MEMBERS AREA -->
            <section class="member-layout">

                <!-- TEAM LEADERS -->
                <div class="leaders-panel">

                    <div class="panel-title">
                        <h2>Team Leaders</h2>
                    </div>

                    <template v-if="props.teamLeaders.length">
                        <div class="leader-card" v-for="leader in props.teamLeaders" :key="leader.id" @dragover.prevent
                            @drop="dropMember(leader.id)">

                            <div class="leader-header">

                                <div class="leader-info">
                                    <img :src="leader.avatar" />

                                    <div>
                                        <h3>{{ leader.first_name }} {{ leader.last_name }}</h3>
                                        <small>Team Leader</small>
                                    </div>
                                </div>

                                <span class="count">
                                    {{ leader.team_members.length }}
                                </span>

                            </div>

                            <!-- Assigned Members -->
                            <div class="assigned-members">

                                <div class="member-item" v-for="member in leader.team_members || []" :key="member.id"
                                    draggable="true" @dragstart="dragMember(member)">

                                    <img :src="member.avatar" />

                                    <div>
                                        <p>{{ member.first_name }} {{ member.last_name }}</p>
                                        <small>Developer</small>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </template>

                    <div v-else class="empty-state">
                        No Team Leaders created yet.
                    </div>
                </div>

                <!-- TEAM MEMBERS -->
                <div class="members-panel">

                    <div class="panel-title">
                        <h2>Unassigned Members</h2>
                    </div>
                    <template v-if="unassignedMembers.length">

                        <div class="member-card" v-for="member in unassignedMembers" :key="member.id" draggable="true"
                            @dragstart="dragMember(member)">

                            <img :src="member.avatar" />

                            <div>
                                <h3>{{ member.first_name }} {{ member.last_name }}</h3>
                                <small>Team Member</small>
                            </div>

                        </div>
                    </template>

                    <div v-else class="empty-state">
                        All members are assigned to Team Leaders.
                    </div>
                </div>

            </section>

        </main>

    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import Sidebar from "./components/Sidebar.vue";
import { useThemeStore } from "../stores/theme";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();

const theme = useThemeStore();

const draggedMember = ref(null);

const form = reactive({
    name: "",
    role: "Member",
});

const props = defineProps({
    members: Array,
    teamLeaders: Array,
});

const unassignedMembers = computed(() =>
    props.members.filter(member => !member.assigned_to)
);

const errors = ref({});

const createMember = () => {

    errors.value = {};

    router.post("/member", form, {

        preserveScroll: true,

        onSuccess: () => {

            toast.success("Member created successfully");

            form.firstName = "";
            form.lastName = "";
            form.email = "";
            form.phone = "";
            form.department = "";
            form.role = "Member";
        },

        onError: (err) => {

            errors.value = err;

            toast.error("Please fix validation errors");
        },
    });
};

const dragMember = (member) => {
    draggedMember.value = member;
};

const dropMember = (leaderId) => {

    if (!draggedMember.value) return;

    router.put(
        `/members/${draggedMember.value.id}/assign`,
        {
            assigned_to: leaderId,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success("Member assigned successfully");
            },
        }
    );

    draggedMember.value = null;
};
</script>

<style scoped>
.main-content {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
}

/* HEADER */

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-right input {
    padding: 12px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
}

.avatar {
    width: 45px;
    height: 45px;
    border-radius: 12px;
}

/* CREATE */

.create-card {
    background: var(--card);
    border: 1px solid var(--border);
    padding: 25px;
    border-radius: 20px;
    margin-bottom: 30px;
}

.section-top {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
}

.section-top button {
    background: #06b6d4;
    border: none;
    padding: 12px 18px;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
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
.form-group select {
    padding: 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--bg);
    color: var(--text);
}

/* LAYOUT */

.member-layout {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 25px;
}

/* PANEL */

.leaders-panel,
.members-panel {
    background: var(--card);
    border-radius: 20px;
    border: 1px solid var(--border);
    padding: 25px;
}

.panel-title {
    margin-bottom: 25px;
}

/* LEADER */

.leader-card {
    background: var(--bg);
    border: 2px dashed var(--border);
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 20px;
    min-height: 160px;
}

.leader-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.leader-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.leader-info img {
    width: 55px;
    height: 55px;
    border-radius: 15px;
}

.count {
    background: #06b6d4;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 13px;
}

/* MEMBERS */

.member-card,
.member-item {
    display: flex;
    align-items: center;
    gap: 15px;
    background: var(--bg);
    padding: 14px;
    border-radius: 15px;
    border: 1px solid var(--border);
    margin-bottom: 15px;
    cursor: grab;
}

.member-card img,
.member-item img {
    width: 45px;
    height: 45px;
    border-radius: 12px;
}

.assigned-members {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  border: 2px dashed var(--border);
  border-radius: 16px;
  color: var(--subtext);
  background: var(--bg);
  font-size: 14px;
}

@media(max-width: 992px) {

    .member-layout {
        grid-template-columns: 1fr;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

}
</style>
