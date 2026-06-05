<template>
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">

            <header class="header">
                <div>
                    <h1>Members</h1>
                    <p>Manage team leaders and assign members.</p>
                </div>

                <div class="header-right">
                    <button class="theme-btn" @click="theme.toggleTheme">
                        {{ theme.isDark ? "☀️" : "🌙" }}
                    </button>

                    <input type="text" placeholder="Search member..." class="header-search-bar" />

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

            <div class="members-utility-grid">

                <section class="panel-card-container">
                    <div class="panel-card-header">
                        <div class="header-icon-badge">👤</div>
                        <div>
                            <h2>Create Member</h2>
                            <p>Add new member to your workspace.</p>
                        </div>
                    </div>

                    <div class="professional-form-layout">
                        <div class="form-row-split">
                            <div class="input-group-wrapper">
                                <label>First Name</label>
                                <input type="text" v-model="form.firstName" placeholder="Enter first name"
                                    @blur="validateFirstName(); handleBlur('firstName')" @input="validateFirstName" />
                                <small v-if="errors.firstName" class="error-text">{{ errors.firstName }}</small>
                            </div>
                            <div class="input-group-wrapper">
                                <label>Last Name</label>
                                <input type="text" v-model="form.lastName" placeholder="Enter last name"
                                    @blur="validateLastName(); handleBlur('lastName')" @input="validateLastName" />
                                <small v-if="errors.lastName" class="error-text">{{ errors.lastName }}</small>
                            </div>
                        </div>

                        <div class="form-row-split">
                            <div class="input-group-wrapper">
                                <label>Email Address</label>
                                <input type="email" v-model="form.email" placeholder="Enter email address"
                                    @blur="validateEmail(); handleBlur('email')" @input="validateEmail" />
                                <small v-if="errors.email" class="error-text">{{ errors.email }}</small>
                            </div>
                            <div class="input-group-wrapper">
                                <label>Phone Number</label>
                                <input type="text" v-model="form.phone" placeholder="Enter phone number" maxlength="10"
                                    @input="form.phone = form.phone.replace(/\D/g, '').slice(0, 10); validatePhone();"
                                    @blur="validatePhone(); handleBlur('phone')" />
                                <small v-if="errors.phone" class="error-text">{{ errors.phone }}</small>
                            </div>
                        </div>

                        <div class="form-row-split">
                            <div class="input-group-wrapper">
                                <label>Department</label>
                                <input type="text" v-model="form.department" placeholder="UI/UX, Development, QA..."
                                    @blur="validateDepartment(); handleBlur('department')"
                                    @input="validateDepartment" />
                                <small v-if="errors.department" class="error-text">{{ errors.department }}</small>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Role</label>
                                <select v-model="form.role" @blur="validateRole(); handleBlur('role')"
                                    @change="validateRole">
                                    <option value="TL">Team Leader</option>
                                    <option value="Member">Team Member</option>
                                </select>
                                <small v-if="errors.role" class="error-text">{{ errors.role }}</small>
                            </div>
                        </div>

                        <button @click="createMember" class="action-primary-btn">
                            ➕ Add Member
                        </button>
                    </div>
                </section>

                <section class="panel-card-container invite-panel-height">
                    <div class="panel-card-header">
                        <div class="header-icon-badge badge-cyan">🔗</div>
                        <div>
                            <h2>Workspace Invitations</h2>
                            <p class="margin-none">Invite external team personnel to join this active workspace track.
                            </p>
                        </div>
                    </div>

                    <div class="invite-generator-body">
                        <p class="helper-description-text">
                            Generate invitation workflows that allow personnel to independently register directly into
                            your workspace track.
                        </p>

                        <button class="action-cyan-btn" @click="showInviteModal = true">
                            ✨ Invite via Link Workflow
                        </button>
                    </div>
                </section>
            </div>

            <section class="member-layout margin-top-grid">

                <div class="panel-card-container">
                    <div class="panel-card-header border-bottom-none">
                        <div class="header-icon-badge badge-purple">👥</div>
                        <div>
                            <h2>Team Leaders Hierarchy</h2>
                            <p>Drop unassigned members into cards below to build organizational alignment mappings.</p>
                        </div>
                    </div>

                    <div class="hierarchy-directory-wrapper">
                        <template v-if="props.teamLeaders.length">
                            <div class="tl-hierarchy-block-card" v-for="leader in props.teamLeaders" :key="leader.id"
                                @dragover.prevent @drop="dropMember(leader.id)">

                                <div class="tl-card-info-header">
                                    <div class="avatar-circle-initials">
                                        {{ getInitials(leader.first_name, leader.last_name) }}
                                    </div>
                                    <div class="tl-details-column">
                                        <h3>{{ leader.first_name }} {{ leader.last_name }}</h3>
                                        <span class="role-pill-tag tl-badge">Team Leader</span>
                                    </div>
                                    <div class="tl-meta-right">
                                        <span class="count-badge">{{ leader.team_members.length }} Members</span>
                                    </div>
                                </div>

                                <div class="subordinates-list-segment">
                                    <div v-if="leader.team_members && leader.team_members.length > 0"
                                        class="subordinates-flex-grid">
                                        <div v-for="member in leader.team_members || []" :key="member.id"
                                            class="member-sub-pill-row" draggable="true"
                                            @dragstart="dragMember(member)">
                                            <div class="mini-avatar-dot">
                                                {{ getInitials(member.first_name, member.last_name) }}
                                            </div>
                                            <div class="member-sub-meta">
                                                <span class="sub-name">{{ member.first_name }} {{ member.last_name
                                                    }}</span>
                                                <span class="sub-email">Developer</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="empty-subordinates-state">
                                        🍃 Drag and drop members here to assign them to this leader.
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div v-else class="empty-subordinates-state text-center-pad">
                            No Team Leaders created yet.
                        </div>
                    </div>
                </div>

                <div class="panel-card-container">
                    <div class="panel-card-header border-bottom-none">
                        <div class="header-icon-badge">🎯</div>
                        <div>
                            <h2>Unassigned Pool</h2>
                            <p>Drag individual profile badges across to assign reporting hierarchies.</p>
                        </div>
                    </div>

                    <div class="unassigned-pool-box">
                        <template v-if="unassignedMembers.length">
                            <div class="member-sub-pill-row grab-cursor" v-for="member in unassignedMembers"
                                :key="member.id" draggable="true" @dragstart="dragMember(member)">
                                <div class="avatar-circle-initials badge-cyan-bg">
                                    {{ getInitials(member.first_name, member.last_name) }}
                                </div>
                                <div class="member-sub-meta">
                                    <span class="sub-name">{{ member.first_name }} {{ member.last_name }}</span>
                                    <span class="sub-email">Team Member</span>
                                </div>
                            </div>
                        </template>

                        <div v-else class="empty-subordinates-state text-center-pad">
                            All team members have been assigned.
                        </div>
                    </div>
                </div>

            </section>

            <Transition name="modal-fade">
                <div v-if="showInviteModal" class="modal-backdrop-blur-overlay" @click.self="showInviteModal = false">
                    <div class="professional-modal-window">

                        <div class="modal-custom-header">
                            <h3>Invite Workspace Member</h3>
                            <button class="modal-close-cross-btn" @click="showInviteModal = false">✕</button>
                        </div>

                        <div class="modal-custom-body">
                            <div class="input-group-wrapper">
                                <label>Email Address</label>
                                <input v-model="inviteForm.email" type="email" placeholder="john@gmail.com" />
                            </div>

                            <div class="input-group-wrapper">
                                <label>Default Workspace Role</label>
                                <select v-model="inviteForm.role">
                                    <option value="Member">Team Member</option>
                                    <option value="TL">Team Leader</option>
                                </select>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Department Assignment</label>
                                <input v-model="inviteForm.department" placeholder="Development" />
                            </div>

                            <button class="action-cyan-btn margin-top-sm" @click="generateInvite">
                                ⚡ Generate Active Registration Link
                            </button>

                            <div v-if="generatedInviteLink" class="invite-link-copy-wrapper">
                                <input readonly :value="generatedInviteLink" class="copy-link-input-field" />
                                <button @click="copyToClipboard" class="action-copy-trigger-btn">
                                    Copy Link
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </Transition>
        </main>

    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { ref, reactive, computed, onBeforeUnmount } from "vue";
import Sidebar from "./components/Sidebar.vue";
import { useThemeStore } from "../stores/theme";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();
const theme = useThemeStore();
const page = usePage();
const draggedMember = ref(null);
const showInviteModal = ref(false);
const showProfileMenu = ref(false);

const inviteForm = reactive({
    email: "",
    role: "Member",
    department: "",
    workspace_id: null,
});
const generatedInviteLink = ref("");

const form = reactive({
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    department: "",
    role: "Member",
});

const errors = reactive({
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    department: "",
    role: "",
});

const touched = reactive({
    firstName: false,
    lastName: false,
    email: false,
    phone: false,
    department: false,
    role: false,
});

const props = defineProps({
    members: Array,
    teamLeaders: Array,
    currentWorkspace: Number,
});

const handleClickOutside = (event) => {
    if (!event.target.closest(".profile-container")) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    inviteForm.workspace_id = props.currentWorkspace;
    document.addEventListener("click", handleClickOutside);
});

const unassignedMembers = computed(() =>
    props.members.filter(member => !member.assigned_to)
);

const getInitials = (firstName, lastName) => {
    return `${firstName?.charAt(0) || ""}${lastName?.charAt(0) || ""}`.toUpperCase();
};

const validateFirstName = () => {
    if (!form.firstName) {
        errors.firstName = "First name is required";
    } else if (!/^[A-Za-z]+$/.test(form.firstName)) {
        errors.firstName = "Only letters are allowed";
    } else if (form.firstName.length < 2) {
        errors.firstName = "Minimum 2 characters required";
    } else {
        errors.firstName = "";
    }
};

const validateLastName = () => {
    if (!form.lastName) {
        errors.lastName = "Last name is required";
    } else if (!/^[A-Za-z]+$/.test(form.lastName)) {
        errors.lastName = "Only letters are allowed";
    } else if (form.lastName.length < 2) {
        errors.lastName = "Minimum 2 characters required";
    } else {
        errors.lastName = "";
    }
};

const validateEmail = () => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!form.email) {
        errors.email = "Email is required";
    } else if (!regex.test(form.email)) {
        errors.email = "Please enter a valid email";
    } else {
        errors.email = "";
    }
};

const validatePhone = () => {
    if (!form.phone) {
        errors.phone = "Phone number is required";
    } else if (!/^[0-9]+$/.test(form.phone)) {
        errors.phone = "Only numbers are allowed";
    } else if (form.phone.length !== 10) {
        errors.phone = "Please enter a valid Phone number";
    } else {
        errors.phone = "";
    }
};

const validateDepartment = () => {
    if (!form.department) {
        errors.department = "Department is required";
    } else {
        errors.department = "";
    }
};

const validateRole = () => {
    if (!form.role) {
        errors.role = "Role is required";
    } else {
        errors.role = "";
    }
};

const handleBlur = (field) => {
    touched[field] = true;
};

const hasErrors = computed(() => {
    return Object.values(errors).some(error => error);
});

const createMember = () => {
    touched.firstName = true;
    touched.lastName = true;
    touched.email = true;
    touched.phone = true;
    touched.department = true;
    touched.role = true;

    validateFirstName();
    validateLastName();
    validateEmail();
    validatePhone();
    validateDepartment();
    validateRole();

    if (hasErrors.value) {
        return;
    }

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

            Object.keys(touched).forEach(key => {
                touched[key] = false;
            });
        },
        onError: (err) => {
            Object.keys(err).forEach(key => {
                if (errors[key] !== undefined) {
                    errors[key] = err[key];
                }
            });
            toast.error("Please fix validation errors");
        },
    });
};

const generateInvite = () => {
    router.post("/invite/generate", inviteForm, {
        preserveScroll: true,
        onSuccess: (page) => {
            toast.success(page.props.flash?.success || "Invite sent");
            generatedInviteLink.value = page.props.flash?.invite_link;
        },
        onError: (errors) => {
            console.error("Invite error:", errors);
            const firstError = Object.values(errors)[0];
            toast.error(firstError || "Failed to send invite");
        }
    });
};

const copyToClipboard = () => {
    if (generatedInviteLink.value) {
        navigator.clipboard.writeText(generatedInviteLink.value);
        toast.success("Invite link copied to clipboard!");
    }
};

const dragMember = (member) => {
    draggedMember.value = member;
};

const dropMember = (leaderId) => {
    if (!draggedMember.value) return;
    router.put(
        `/members/${draggedMember.value.id}/assign`,
        { assigned_to: leaderId },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Member assigned successfully");
            },
        }
    );
    draggedMember.value = null;
};

const logout = () => {
    router.post('/logout');
};

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.main-content {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
}

/* HEADER STYLE RULES */
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

.header-search-bar {
    background-color: var(--card);
    border: 1px solid var(--border);
    padding: 10px 16px;
    border-radius: 8px;
    color: var(--text);
    outline: none;
    font-size: 13px;
    width: 220px;
    transition: border-color 0.2s;
}

.header-search-bar:focus {
    border-color: #0073ea;
}

/* Master Grid Core Structures */
.members-utility-grid {
    display: grid;
    grid-template-columns: 1.3fr 0.7fr;
    gap: 24px;
}

.member-layout {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 24px;
}

@media (max-width: 1024px) {

    .members-utility-grid,
    .member-layout {
        grid-template-columns: 1fr;
    }
}

.margin-top-grid {
    margin-top: 24px;
}

.margin-none {
    margin: 0;
}

/* Premium Card Panels layout definitions */
.panel-card-container {
    background-color: var(--sidebar);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.invite-panel-height {
    display: flex;
    flex-direction: column;
}

.panel-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 16px;
}

.panel-card-header.border-bottom-none {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 20px;
}

.header-icon-badge {
    width: 42px;
    height: 42px;
    background-color: rgba(0, 115, 234, 0.12);
    border-radius: 8px;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0073ea;
}

.header-icon-badge.badge-cyan {
    background-color: rgba(6, 182, 212, 0.12);
    color: #06b6d4;
}

.header-icon-badge.badge-purple {
    background-color: rgba(162, 93, 220, 0.12);
    color: #a25ddc;
}

.panel-card-header h2 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.panel-card-header p {
    font-size: 12px;
    color: var(--subtext);
    margin: 4px 0 0 0;
}

/* Form Controls System Settings */
.professional-form-layout {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-row-split {
    display: flex;
    gap: 16px;
}

@media(max-width: 640px) {
    .form-row-split {
        flex-direction: column;
    }
}

.input-group-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.input-group-wrapper label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text);
}

.input-group-wrapper input,
.input-group-wrapper select {
    background-color: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 14px;
    font-size: 13.5px;
    border-radius: 6px;
    outline: none;
    transition: all 0.2s ease;
}

.input-group-wrapper input:focus,
.input-group-wrapper select:focus {
    border-color: #0073ea;
    box-shadow: 0 0 0 3px rgba(0, 115, 234, 0.15);
}

.error-text {
    color: #ef4444;
    font-size: 11px;
    margin-top: 2px;
}

/* Dynamic Panel Action Buttons */
.action-primary-btn {
    background-color: #0073ea;
    color: #ffffff;
    border: none;
    padding: 11px 20px;
    font-size: 13.5px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-primary-btn:hover {
    background-color: #005ec4;
}

.action-cyan-btn {
    background-color: #06b6d4;
    color: #ffffff;
    border: none;
    padding: 12px 20px;
    font-size: 13.5px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    width: 100%;
}

.action-cyan-btn:hover {
    background-color: #0891b2;
}

.invite-generator-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
}

.helper-description-text {
    font-size: 13px;
    color: var(--subtext);
    line-height: 1.5;
    margin: 0 0 24px 0;
}

/* Tree Directory Layout System Grid Rules */
.hierarchy-directory-wrapper,
.unassigned-pool-box {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.tl-hierarchy-block-card {
    background-color: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.tl-card-info-header {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    background-color: rgba(255, 255, 255, 0.01);
    border-bottom: 1px solid var(--border);
    gap: 14px;
}

.avatar-circle-initials {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: #a25ddc;
    color: #ffffff;
    font-size: 13px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-circle-initials.badge-cyan-bg {
    background-color: #06b6d4;
}

.tl-details-column h3 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.role-pill-tag {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 1px 6px;
    border-radius: 4px;
    letter-spacing: 0.4px;
    margin-top: 3px;
    display: inline-block;
}

.role-pill-tag.tl-badge {
    background-color: rgba(162, 93, 220, 0.15);
    color: #a25ddc;
    border: 1px solid rgba(162, 93, 220, 0.3);
}

.tl-meta-right {
    margin-left: auto;
}

.count-badge {
    font-size: 11px;
    background-color: var(--card);
    border: 1px solid var(--border);
    color: var(--subtext);
    padding: 4px 10px;
    border-radius: 20px;
}

/* Inner Assigned Drag list components */
.subordinates-list-segment {
    padding: 14px;
}

.subordinates-flex-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.member-sub-pill-row {
    display: flex;
    align-items: center;
    background-color: var(--sidebar);
    border: 1px solid var(--border);
    padding: 10px 14px;
    border-radius: 6px;
    gap: 12px;
    transition: transform 0.15s ease;
}

.member-sub-pill-row:active {
    cursor: grabbing;
}

.grab-cursor {
    cursor: grab;
}

.grab-cursor:hover {
    border-color: #0073ea;
}

.mini-avatar-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #00c875;
    color: white;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

.member-sub-meta {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.sub-name {
    font-size: 13px;
    font-weight: 500;
    color: var(--text);
}

.sub-email {
    font-size: 11px;
    color: var(--subtext);
}

.empty-subordinates-state {
    font-size: 12px;
    color: var(--subtext);
    font-style: italic;
    padding: 4px;
}

.text-center-pad {
    text-align: center;
    padding: 30px 10px;
    background-color: var(--bg);
    border: 1px dashed var(--border);
    border-radius: 6px;
}

/* User Profile Dropdowns */
.avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.2s;
}

.avatar:hover {
    transform: scale(1.05);
}

.profile-container {
    position: relative;
}

.profile-dropdown {
    position: absolute;
    top: 52px;
    right: 0;
    width: 130px;
    background: var(--sidebar);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    z-index: 100;
}

.profile-dropdown button {
    width: 100%;
    padding: 10px 14px;
    background: transparent;
    border: none;
    color: var(--text);
    text-align: left;
    cursor: pointer;
    font-size: 13px;
}

.profile-dropdown button:hover {
    background: var(--bg);
    color: #ef4444;
}

/* Invitation Dialog Windows Rules CSS Framework layout */
.modal-backdrop-blur-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

.professional-modal-window {
    background-color: var(--sidebar);
    border: 1px solid var(--border);
    border-radius: 8px;
    width: 440px;
    max-width: 90%;
    box-shadow: 0 20px 48px rgba(0, 0, 0, 0.5);
}

.modal-custom-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.modal-custom-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
}

.modal-close-cross-btn {
    background: transparent;
    border: none;
    font-size: 20px;
    color: var(--subtext);
    cursor: pointer;
}

.modal-custom-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.margin-top-sm {
    margin-top: 6px;
}

.invite-link-copy-wrapper {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}

.copy-link-input-field {
    flex: 1;
    background-color: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 12px;
    font-size: 13px;
    border-radius: 6px;
    outline: none;
}

.action-copy-trigger-btn {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 0 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
}

.action-copy-trigger-btn:hover {
    background-color: var(--card);
    border-color: #0073ea;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>
