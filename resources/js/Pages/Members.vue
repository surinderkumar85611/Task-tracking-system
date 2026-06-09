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

                    <div class="notification-bell-container" v-click-outside="() => notificationStore.showBellDropdown = false">
    <button class="icon-btn" @click="notificationStore.showBellDropdown = !notificationStore.showBellDropdown">
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
            <div v-for="task in notificationStore.activeUrgentTasks" :key="task.id" class="notification-alert-item">
                <div class="alert-item-indicator">⚠️</div>
                <div class="alert-item-details">
                    <p class="alert-task-title">{{ task.title }}</p>
                    <p class="alert-task-time-left" :style="{ color: notificationStore.getLiveTaskMetrics(task).color }">
                        Only {{ notificationStore.getLiveTaskMetrics(task).string }} left!
                    </p>
                </div>
            </div>

            <div v-if="notificationStore.activeUrgentTasks.length === 0" class="notification-empty-state">
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
                                <input 
                                    type="email" 
                                    v-model="form.email" 
                                    placeholder="Enter email address"
                                    @blur="validateEmail(); handleBlur('email')" 
                                    @input="validateEmail" 
                                />
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
                    <input class="copy-link-input-field" :value="generatedInviteLink" readonly />
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
                                    <div class="avatar-circle-initials" @click="openEditModal(leader)"
                                        style="cursor:pointer">
                                        {{ getInitials(leader.first_name, leader.last_name) }}
                                    </div>

                                   
                                    <div class="tl-details-column">
                                        <h3>{{ leader.role === 'TL' ? 'Team Leader' : 'Team Member' }}</h3>
                                        
                                        <span class="role-pill-tag tl-badge">
                                            {{ leader.department }}
                                        </span>
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
                                            <div class="mini-avatar-dot" @click.stop="openEditModal(member)"
                                                style="cursor:pointer">
                                                {{ getInitials(member.first_name, member.last_name) }}
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
import { useNotificationStore } from "../stores/notificationStore";

const notificationStore = useNotificationStore();

const toast = useToast();
const theme = useThemeStore();
const page = usePage();
const draggedMember = ref(null);
const showInviteModal = ref(false);
const showProfileMenu = ref(false);
const showEditModal = ref(false);
const selectedMember = ref(null);

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
});
const editForm = reactive({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    department: "",
    role: "",
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
const logout = () => {
    router.post('/logout');
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
</script>

<style scoped>
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

    width: fit-content;
    min-width: 120px;
    max-width: 180px;

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
</style>
