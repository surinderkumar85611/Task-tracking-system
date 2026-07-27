<template>

    <Head title="Member" />
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

                    <div class="notification-bell-container"
                        v-click-outside="() => notificationStore.showBellDropdown = false">
                        <button class="icon-btn"
                            @click="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">
                            🔔
                            <span v-if="notificationStore.activeUrgentTasks.length > 0" class="bell-alert-badge-dot">
                                {{ notificationStore.activeUrgentTasks.length }}
                            </span>
                        </button>

                        <div v-if="notificationStore.showBellDropdown" class="notification-dropdown-panel">
                            <div class="notification-dropdown-header">
                                <h3>Urgent Task Alerts</h3>
                            </div>
                            <div class="notification-dropdown-body">
                                <div v-for="task in notificationStore.activeUrgentTasks" :key="task.id"
                                    class="notification-alert-item">
                                    <div class="alert-item-indicator">⚠️</div>
                                    <div class="alert-item-details">
                                        <p class="alert-task-title">{{ task.title }}</p>
                                        <p class="alert-task-time-left"
                                            :style="{ color: notificationStore.getLiveTaskMetrics(task).color }">
                                            Only {{ notificationStore.getLiveTaskMetrics(task).string }} left!
                                        </p>
                                    </div>
                                </div>

                                <div v-if="notificationStore.activeUrgentTasks.length === 0"
                                    class="notification-empty-state">
                                    🎉 No urgent deadlines right now. Everything is under control!
                                </div>
                            </div>
                        </div>
                    </div>

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
                                <select v-if="form.role === 'TL'" v-model="form.level">
                                    <option value="3">Senior TL</option>
                                    <option value="2">TL Level 2</option>
                                    <option value="1">TL Level 1</option>
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
                        <Transition name="modal-fade">
                            <div v-if="showInviteModal" class="modal-backdrop-blur-overlay"
                                @click.self="showInviteModal = false">

                                <div class="professional-modal-window">

                                    <div class="modal-custom-header">
                                        <h3>Invite Workspace Member</h3>
                                        <button class="modal-close-cross-btn"
                                            @click="showInviteModal = false">✕</button>
                                    </div>

                                    <div class="modal-custom-body">

                                        <div class="input-group-wrapper">
                                            <label>Email Address</label>
                                            <input v-model="inviteForm.email" type="email"
                                                placeholder="john@gmail.com" />
                                            <small v-if="inviteErrors.email" class="error-text">
                                                {{ inviteErrors.email }}
                                            </small>
                                        </div>

                                        <div class="input-group-wrapper">
                                            <label>Default Role</label>
                                            <select v-model="inviteForm.role">
                                                <option value="Member">Team Member</option>
                                                <option value="TL">Team Leader</option>
                                            </select>
                                            <small v-if="inviteErrors.role" class="error-text">
                                                {{ inviteErrors.role }}
                                            </small>
                                        </div>

                                        <div class="input-group-wrapper">
                                            <label>Department</label>
                                            <input v-model="inviteForm.department" placeholder="Development" />
                                            <small v-if="inviteErrors.department" class="error-text">
                                                {{ inviteErrors.department }}
                                            </small>
                                        </div>

                                        <button class="action-primary-btn" @click="generateInvite">
                                            ✨ Generate Invite Link
                                        </button>

                                        <div v-if="generatedInviteLink" class="invite-link-copy-wrapper">
                                            <input class="copy-link-input-field" :value="generatedInviteLink"
                                                readonly />
                                            <button class="action-copy-trigger-btn" @click="copyToClipboard">
                                                Copy
                                            </button>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </Transition>
                    </div>
                </section>
            </div>

            <section v-if="emptyMembers.length" class="panel-card orphan-panel">

                <div class="panel-header">
                    <h2>Members Without Workspace</h2>
                    <span class="member-count">
                        {{ emptyMembers.length }}
                    </span>
                </div>

                <div class="orphan-grid">

                    <div v-for="member in emptyMembers" :key="member.id" class="orphan-card" draggable="true"
                        @dragstart="dragMember(member)">
                        <div class="orphan-avatar">
                            {{ getInitials(member.first_name, member.last_name) }}
                        </div>

                        <div class="orphan-info">
                            <h4>
                                {{ member.first_name }}
                                {{ member.last_name }}
                            </h4>

                            <span class="orphan-role" :class="member.role === 'TL' ? 'leader-role' : 'member-role'">
                                {{ member.role }}
                            </span>
                        </div>

                    </div>

                </div>

            </section>

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
                        <TeamHierarchy :leaders="teamLeaders" :currentWorkspace="currentWorkspace"
                            @drag-member="dragMember" @drop-member="dropMember" @drop-leader="dropLeader"
                            @assign-workspace="dropTeamLeaderToWorkspace" @remove-member="removeMember" />
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
                                <div class="avatar-circle-initials badge-cyan-bg" @click.stop="openEditModal(member)"
                                    style="cursor:pointer">
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
                <div v-if="showEditModal" class="modal-backdrop-blur-overlay" @click.self="showEditModal = false">
                    <div class="professional-modal-window">

                        <div class="modal-custom-header">
                            <h3>Edit Member</h3>

                            <button class="modal-close-cross-btn" @click="showEditModal = false">
                                ✕
                            </button>
                        </div>

                        <div class="modal-custom-body">

                            <div class="input-group-wrapper">
                                <label>First Name</label>
                                <input v-model="editMember.first_name" type="text" />
                            </div>

                            <div class="input-group-wrapper">
                                <label>Last Name</label>
                                <input v-model="editMember.last_name" type="text" />
                            </div>

                            <div class="input-group-wrapper">
                                <label>Email</label>
                                <input v-model="editMember.email" type="email" />
                            </div>

                            <div class="input-group-wrapper">
                                <label>Phone</label>
                                <input v-model="editMember.phone" type="text" />
                            </div>

                            <div class="input-group-wrapper">
                                <label>Department</label>
                                <input v-model="editMember.department" type="text" />
                            </div>

                            <button class="action-primary-btn" @click="updateMember">
                                Save Changes
                            </button>

                        </div>

                    </div>
                </div>
            </Transition>
            <Transition name="modal-fade">
                <div v-if="showEditModal" class="modal-backdrop-blur-overlay" @click.self="showEditModal = false">
                    <div class="professional-modal-window">

                        <div class="modal-custom-header">
                            <h3>Edit Member</h3>
                            <button class="modal-close-cross-btn" @click="showEditModal = false">
                                ✕
                            </button>
                        </div>

                        <div class="modal-custom-body">

                            <div class="input-group-wrapper">
                                <label>First Name</label>
                                <input v-model="editForm.first_name" type="text" />
                                <small v-if="editErrors.first_name" class="error-text">
                                    {{ editErrors.first_name }}
                                </small>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Last Name</label>
                                <input v-model="editForm.last_name" type="text" />
                                <small v-if="editErrors.last_name" class="error-text">
                                    {{ editErrors.last_name }}
                                </small>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Email Address</label>
                                <div class="readonly-field">
                                    {{ selectedMember?.email }}
                                </div>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Phone Number</label>
                                <input v-model="editForm.phone" type="text" maxlength="10"
                                    @input="editForm.phone = editForm.phone.replace(/\D/g, '').slice(0, 10)" />

                                <small v-if="editErrors.phone" class="error-text">
                                    {{ editErrors.phone }}
                                </small>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Department</label>
                                <input v-model="editForm.department" type="text" />

                                <small v-if="editErrors.department" class="error-text">
                                    {{ editErrors.department }}
                                </small>
                            </div>

                            <div class="input-group-wrapper">
                                <label>Role</label>
                                <div class="readonly-field">
                                    {{
                                        selectedMember?.role === 'TL'
                                            ? 'Team Leader'
                                            : 'Team Member'
                                    }}
                                </div>
                            </div>

                            <button class="action-primary-btn" @click="updateMember">
                                Save Changes
                            </button>

                        </div>

                    </div>
                </div>
            </Transition>

            <div v-if="showDeleteModal" class="modal-overlay">
                <div class="delete-modal">

                    <h3>Delete Member</h3>

                    <p>
                        Are you sure you want to delete
                        <strong>
                            {{ memberToDelete?.first_name }}
                            {{ memberToDelete?.last_name }}
                        </strong>?
                    </p>

                    <p class="delete-warning">
                        This action cannot be undone.
                    </p>

                    <div class="modal-actions">
                        <button class="btn-secondary" @click="cancelDeleteMember">
                            Cancel
                        </button>

                        <button class="btn-danger" @click="confirmDeleteMember">
                            Delete
                        </button>
                    </div>

                </div>
            </div>
        </main>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onBeforeUnmount, onMounted, watch } from "vue";
import Sidebar from "./components/Sidebar.vue";
import { useThemeStore } from "../stores/theme";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { useNotificationStore } from "../stores/notificationStore";
import { Head } from '@inertiajs/vue3';
import TeamHierarchy from "./TeamHierarchy.vue";

const notificationStore = useNotificationStore();

const toast = useToast();
const theme = useThemeStore();
const page = usePage();
const showInviteModal = ref(false);
const showProfileMenu = ref(false);
const showEditModal = ref(false);
const selectedMember = ref(null);
const draggedItem = ref(null);

const editMember = reactive({
    id: null,
    first_name: "",
    last_name: "",

    phone: "",
    department: "",
});

const openEditMember = (member) => {
    editMember.id = member.id;
    editMember.first_name = member.first_name;
    editMember.last_name = member.last_name;

    editMember.phone = member.phone || "";
    editMember.department = member.department || "";

    showEditModal.value = true;
};

const inviteForm = reactive({
    email: "",
    role: "Member",
    department: "",
    workspace_id: null,
});
const inviteErrors = reactive({
    email: "",
    role: "",
    department: "",
});

const validateInviteForm = () => {
    let valid = true;

    inviteErrors.email = "";
    inviteErrors.role = "";
    inviteErrors.department = "";

    if (!inviteForm.email) {
        inviteErrors.email = "Email is required";
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(inviteForm.email)) {
        inviteErrors.email = "Invalid email format";
        valid = false;
    }

    if (!inviteForm.role) {
        inviteErrors.role = "Role is required";
        valid = false;
    }

    if (!inviteForm.department) {
        inviteErrors.department = "Department is required";
        valid = false;
    }

    return valid;
};
const generatedInviteLink = ref("");

const form = reactive({
    firstName: "",
    lastName: "",
    email: "",
    phone: "",
    department: "",
    role: "Member",
    level: null
});
const editForm = reactive({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    department: "",
    role: "",
    level: ""
});
const editErrors = reactive({
    first_name: "",
    last_name: "",
    phone: "",
    department: "",
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
    emptyMembers: Array,
    workspaces: Array,
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
    props.members.filter(
        member =>
            member.role === 'Member' &&
            !member.assigned_to
    )
);

const emptyLeaders = computed(() =>
    props.members.filter(
        member =>
            !member.workspace_id &&
            member.role === 'TL'
    )
);

const emptyTeamMembers = computed(() =>
    props.members.filter(
        member =>
            !member.workspace_id &&
            member.role === 'TM'
    )
);

const getInitials = (firstName, lastName) => {
    return `${firstName?.charAt(0) || ""}${lastName?.charAt(0) || ""}`.toUpperCase();
};
const originalMemberData = ref({});
const openEditModal = (member) => {
    selectedMember.value = member;

    editForm.first_name = member.first_name;
    editForm.last_name = member.last_name;
    editForm.email = member.email;
    editForm.phone = member.phone || "";
    editForm.department = member.department || "";

    originalMemberData.value = {
        first_name: member.first_name,
        last_name: member.last_name,
        phone: member.phone || "",
        department: member.department || "",
    };

    showEditModal.value = true;
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
        },
    });
};

const generateInvite = () => {
    if (!validateInviteForm()) {
        toast.error("Please fix invite form errors");
        return;
    }

    router.post("/invite/generate", inviteForm, {
        preserveScroll: true,
        onSuccess: (page) => {
            toast.success(page.props.flash?.success || "Invite created");

            generatedInviteLink.value =
                page.props.flash?.invite_link ||
                page.props.flash?.data?.invite_link ||
                "";

            inviteForm.email = "";
            inviteForm.role = "Member";
            inviteForm.department = "";
            showInviteModal.value = false;
        },
        onError: (errors) => {
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
    draggedItem.value = member;
};

const dropMember = (leaderId) => {
    if (!draggedItem.value) return;
    router.put(
        `/member/${draggedItem.value.id}/assign`,
        {
            assigned_to: leaderId
        }
    );
    draggedItem.value = null;
};

const dropLeader = (leaderId) => {

    if (!draggedItem.value) return;

    router.put(
        `/member/${draggedItem.value.id}/assign`,
        {
            assigned_to: leaderId
        }
    );

    draggedItem.value = null;
};

const dropTeamLeaderToWorkspace = () => {

    if (!draggedItem.value) return;

    if (draggedItem.value.role !== 'TL') {
        toast.error(
            'Only Team Leaders can be assigned to a workspace'
        );
        return;
    }

    router.put(
        `/member/${draggedItem.value.id}/assign-workspace`,
        {
            workspace_id: props.currentWorkspace
        },
        {
            onSuccess: () => {

                const index = props.emptyMembers.findIndex(
                    m => m.id === draggedItem.value.id
                );

                if (index !== -1) {
                    props.emptyMembers.splice(index, 1);
                }
            }
        }
    );
};

const validateEditForm = () => {

    editErrors.first_name = "";
    editErrors.last_name = "";
    editErrors.phone = "";
    editErrors.department = "";

    let valid = true;

    if (!editForm.first_name.trim()) {
        editErrors.first_name = "First name is required";
        valid = false;
    } else if (!/^[A-Za-z]+$/.test(editForm.first_name)) {
        editErrors.first_name = "Only letters are allowed";
        valid = false;
    }

    if (!editForm.last_name.trim()) {
        editErrors.last_name = "Last name is required";
        valid = false;
    } else if (!/^[A-Za-z]+$/.test(editForm.last_name)) {
        editErrors.last_name = "Only letters are allowed";
        valid = false;
    }

    if (!editForm.phone.trim()) {
        editErrors.phone = "Phone number is required";
        valid = false;
    } else if (!/^[0-9]{10}$/.test(editForm.phone)) {
        editErrors.phone = "Phone number must be 10 digits";
        valid = false;
    }

    if (!editForm.department.trim()) {
        editErrors.department = "Department is required";
        valid = false;
    }

    return valid;
};

const updateMember = () => {

    const isValid = validateEditForm();

    if (!isValid) {
        return;
    }

    const noChanges =
        editForm.first_name === originalMemberData.value.first_name &&
        editForm.last_name === originalMemberData.value.last_name &&
        editForm.phone === originalMemberData.value.phone &&
        editForm.department === originalMemberData.value.department;

    if (noChanges) {
        toast.info("Nothing to update");
        showEditModal.value = false;
        return;
    }

    router.put(
        `/member/${selectedMember.value.id}`,
        {
            first_name: editForm.first_name,
            last_name: editForm.last_name,
            email: selectedMember.value.email,
            phone: editForm.phone,
            department: editForm.department,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Member updated successfully");
                showEditModal.value = false;
            },
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    if (editErrors[key] !== undefined) {
                        editErrors[key] = errors[key];
                    }
                });
            }
        }
    );
};

const showDeleteModal = ref(false);
const memberToDelete = ref(null);

const removeMember = (member) => {
    memberToDelete.value = member;
    showDeleteModal.value = true;
};

const confirmDeleteMember = () => {

    if (!memberToDelete.value) return;

    router.delete(
        `/member/${memberToDelete.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Member deleted successfully");
                showDeleteModal.value = false;
                memberToDelete.value = null;
            },
            onError: () => {
                toast.error("Failed to delete member");
            }
        }
    );
};

const cancelDeleteMember = () => {
    showDeleteModal.value = false;
    memberToDelete.value = null;
};

const logout = () => {
    router.post("/logout", {}, {
        replace: true,
        onSuccess: () => {
            window.location.href = "/login";
        }
    });
};

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

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

watch(() => form.role, (role) => {
    if (role !== 'TL') {
        form.level = null;
    }
});
</script>

<style>
.main-content {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
}

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

.subordinates-list-segment {
    padding: 14px;
}

.subordinates-flex-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.member-sub-pill-row {
    display: flex;
    align-items: center;
    background-color: var(--sidebar);
    border: 1px solid var(--border);
    padding: 10px 14px;
    border-radius: 8px;
    gap: 10px;
    flex: 0 0 auto;
    transition: transform 0.15s ease;
    cursor: grab;
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

.clickable-avatar {
    cursor: pointer;
    transition: all 0.2s ease;
}

.clickable-avatar:hover {
    transform: scale(1.1);
}

.readonly-field {
    background-color: var(--bg);
    border: 1px solid var(--border);
    color: var(--subtext);
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
}

:deep(.Toastify__toast-container) {
    z-index: 9999 !important;
}

:deep(.Toastify__toast-container) {
    z-index: 100000 !important;
}

.member-sub-pill-row {
    position: relative;
}

.member-rich-tooltip-box {
    position: absolute;
    left: calc(100% + 12px);
    top: -25px;
    transform: translateY(-10%);
    width: 260px;
    background: #1e1e2d;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px;
    box-shadow: 0 12px 36px rgba(0, 0, 0, .65);
    opacity: 0;
    pointer-events: none;
    z-index: 999999;
    transition: .15s ease;
}

.member-rich-tooltip-box::after {
    content: "";
    position: absolute;
    top: 50%;
    right: 100%;
    transform: translateY(-50%);
    border-width: 6px;
    border-style: solid;
    border-color: transparent #1e1e2d transparent transparent;
}

.member-sub-pill-row:hover .member-rich-tooltip-box {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(0);
}

.tooltip-header-row {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 8px;
    width: 100%;
}

.tooltip-avatar-badge {
    background-color: #3b82f6;
    color: #ffffff;
    font-size: 11px;
    font-weight: bold;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tooltip-title-block {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
}

.tooltip-title-block h4 {
    margin: 0;
    font-size: 13px;
    color: var(--text);
    font-weight: 600;
    text-align: left;
    white-space: nowrap;
}

.tooltip-email-label {
    margin: 2px 0 0 0;
    font-size: 11px;
    color: var(--subtext);
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 170px;
}

.tooltip-details-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
    text-align: left;
    width: 100%;
}

.tooltip-meta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    width: 100%;
}

.meta-label-title {
    color: var(--subtext);
    font-weight: 500;
}

.meta-value-text {
    color: var(--text);
    font-weight: 600;
}

.role-tag-pill {
    background: rgba(59, 130, 246, 0.15);
    color: #3b82f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.assign-tl-zone {
    margin-top: 15px;
    border: 2px dashed #6366f1;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

.assign-tl-zone:hover {
    background: rgba(99, 102, 241, .08);
}

.orphan-panel {
    margin-top: 20px;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.member-count {
    background: rgba(59, 130, 246, .15);
    color: #60a5fa;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.orphan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
}

.orphan-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, .08);
    background: rgba(255, 255, 255, .03);
    cursor: grab;
    transition: all .25s ease;
}

.orphan-card:hover {
    transform: translateY(-3px);
    border-color: #3b82f6;
    background: rgba(59, 130, 246, .08);
}

.orphan-avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: linear-gradient(135deg,
            #06b6d4,
            #3b82f6);

    display: flex;
    align-items: center;
    justify-content: center;

    color: white;
    font-weight: 700;
}

.orphan-info h4 {
    margin: 0;
    font-size: 14px;
    color: white;
}

.orphan-role {
    display: inline-block;
    margin-top: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

.leader-role {
    background: rgba(168, 85, 247, .15);
    color: #c084fc;
}

.member-role {
    background: rgba(16, 185, 129, .15);
    color: #34d399;
}

.nested-leader-card {
    width: 100%;
    margin-top: 12px;
    padding: 12px;
    border-radius: 12px;
    background: rgba(255, 255, 255, .03);
    border-left: 3px solid #8b5cf6;
}

.nested-leader-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nested-members {
    margin-left: 40px;
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.independent-zone {
    margin-top: 15px;
    padding: 16px;
    text-align: center;

    border: 2px dashed #ef4444;
    border-radius: 12px;

    color: #ef4444;
    font-weight: 600;

    transition: .2s;
}

.independent-zone:hover {
    background: rgba(239, 68, 68, .08);
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.delete-modal {
    width: 420px;
    max-width: 95%;
    background: #1f2937;
    color: white;
    padding: 24px;
    border-radius: 12px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.btn-secondary {
    background: #374151;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-danger {
    background: #dc2626;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-danger:hover {
    background: #b91c1c;
}
</style>
