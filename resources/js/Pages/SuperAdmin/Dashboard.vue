<template>
    <div class="dashboard">

        <!-- Sidebar -->
        <aside class="sidebar">

            <div>

                <div class="logo">
                    <div class="logo-icon">
                        S
                    </div>

                    <div>
                        <h2>Super Admin</h2>
                        <span>Management Panel</span>
                    </div>

                </div>

                <nav class="menu">

                    <a href="#" class="active">
                        <i class="fas fa-chart-pie"></i>
                        Dashboard
                    </a>

                    <a href="#">
                        <i class="fas fa-users"></i>
                        Administrators
                    </a>

                    <a href="#">
                        <i class="fas fa-building"></i>
                        Workspaces
                    </a>

                    <a href="#">
                        <i class="fas fa-folder-open"></i>
                        Projects
                    </a>

                    <a href="#">
                        <i class="fas fa-user-friends"></i>
                        Members
                    </a>

                    <a href="#">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>

                </nav>

            </div>

            <button class="logout-btn" @click="logout">
                Logout
            </button>

        </aside>

        <!-- Main -->

        <main class="main">

            <!-- Top -->

            <header class="topbar">

                <div>

                    <h1>
                        Welcome Back 👋
                    </h1>

                    <p>
                        Manage your administrators and workspaces.
                    </p>

                </div>

                <div class="top-actions">

                    <input type="text" placeholder="Search..." />

                    <button class="circle-btn">
                        🔔
                    </button>

                    <div class="avatar">
                        SA
                    </div>

                </div>

            </header>

            <!-- Stats -->

            <section class="stats">

                <div class="card blue">

                    <div class="card-top">

                        <span class="icon">
                            👤
                        </span>

                    </div>

                    <h2>{{ stats.admins }}</h2>

                    <p>Total Administrators</p>

                </div>

                <div class="card green">

                    <div class="card-top">

                        <span class="icon">
                            🏢
                        </span>

                    </div>

                    <h2>{{ stats.workspaces }}</h2>

                    <p>Workspaces</p>

                </div>

                <div class="card orange">

                    <div class="card-top">

                        <span class="icon">
                            📂
                        </span>

                    </div>

                    <h2>{{ stats.projects }}</h2>

                    <p>Projects</p>

                </div>

                <div class="card purple">

                    <div class="card-top">

                        <span class="icon">
                            👥
                        </span>

                    </div>

                    <h2>{{ stats.members }}</h2>

                    <p>Members</p>

                </div>

            </section>

            <!-- Content -->

            <section class="content-grid">

                <!-- Users -->

                <div class="table-card">

                    <div class="table-header">

                        <div>

                            <h2>
                                Administrators
                            </h2>

                            <p>
                                Manage system users
                            </p>

                        </div>

                        <button class="create-btn" @click="showCreateModal = true">
                            + Create User
                        </button>

                    </div>

                    <table>

                        <thead>

                            <tr>

                                <th>User</th>

                                <th>Email</th>

                                <th>Joined</th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr v-for="admin in admins" :key="admin.id">

                                <td>

                                    <div class="user">

                                        <div class="user-avatar">

                                            {{ admin.name.charAt(0) }}

                                        </div>

                                        <div>

                                            <strong>

                                                {{ admin.name }}

                                            </strong>

                                            <span>
                                                Administrator
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>

                                    {{ admin.email }}

                                </td>

                                <td>

                                    {{ new Date(admin.created_at).toLocaleDateString() }}

                                </td>

                                <td>

                                    <button class="manage-btn">

                                        Manage

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- Activity -->

                <div class="activity-card">

                    <h2>
                        Quick Overview
                    </h2>

                    <div class="activity-item">

                        <span>Total Admins</span>

                        <strong>{{ stats.admins }}</strong>

                    </div>

                    <div class="activity-item">

                        <span>Workspaces</span>

                        <strong>{{ stats.workspaces }}</strong>

                    </div>

                    <div class="activity-item">

                        <span>Projects</span>

                        <strong>{{ stats.projects }}</strong>

                    </div>

                    <div class="activity-item">

                        <span>Members</span>

                        <strong>{{ stats.members }}</strong>

                    </div>

                </div>

            </section>

        </main>

        <!-- Modal -->

        <div v-if="showCreateModal" class="modal-overlay">

            <div class="modal">

                <div class="modal-header">

                    <div>

                        <h2>Create User</h2>

                        <p>Create Administrator or Team Leader</p>

                    </div>

                    <button class="close-btn" @click="showCreateModal = false">
                        ✕

                    </button>

                </div>

                <div class="form-group">

                    <label>Role</label>

                    <select v-model="form.role">

                        <option value="ADMIN">
                            Administrator
                        </option>

                        <option value="TL">
                            Team Leader
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Name</label>

                    <input v-model="form.name" placeholder="John Doe">

                </div>

                <div class="form-group">

                    <label>Email</label>

                    <input v-model="form.email" type="email">

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input v-model="form.password" type="password">

                </div>

                <div class="form-group">

                    <label>Confirm Password</label>

                    <input v-model="form.password_confirmation" type="password">

                </div>

                <div class="modal-actions">

                    <button class="cancel-btn" @click="showCreateModal = false">
                        Cancel
                    </button>

                    <button class="save-btn" @click="createAdmin">
                        Create User
                    </button>

                </div>

            </div>

        </div>

    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

defineProps({
    stats: {
        type: Object,
        required: true
    },
    admins: {
        type: Array,
        default: () => []
    }
})

const showCreateModal = ref(false)

const form = reactive({
    role: 'ADMIN',
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

function resetForm() {
    form.role = 'ADMIN'
    form.name = ''
    form.email = ''
    form.password = ''
    form.password_confirmation = ''
}

function createAdmin() {
    router.post('/super-admin/admin', form, {
        preserveScroll: true,

        onSuccess: () => {
            showCreateModal.value = false
            resetForm()
        }
    })
}

function logout() {
    router.post('/super-admin/logout')
}
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.dashboard {
    display: flex;
    min-height: 100vh;
    background: #111827;
    color: #fff;
    font-family: Inter, Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
}

/* =======================
SIDEBAR
======================= */

.sidebar {
    width: 270px;
    background: #1b2333;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 30px 24px;
    border-right: 1px solid rgba(255, 255, 255, .06);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
}

.logo {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 45px;
}

.logo-icon {
    width: 52px;
    height: 52px;
    border-radius: 15px;
    background: linear-gradient(135deg, #4f7cff, #2563eb);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
    font-weight: 700;
}

.logo h2 {
    font-size: 22px;
    margin-bottom: 4px;
}

.logo span {
    color: #94a3b8;
    font-size: 13px;
}

.menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.menu a {
    text-decoration: none;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-radius: 14px;
    transition: .3s;
    font-weight: 500;
}

.menu a:hover {
    background: #273449;
    color: #fff;
}

.menu a.active {
    background: #2563eb;
    color: #fff;
}

.logout-btn {
    border: none;
    border-radius: 14px;
    background: #ef4444;
    color: #fff;
    padding: 14px;
    cursor: pointer;
    font-weight: 600;
    transition: .3s;
}

.logout-btn:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

/* =======================
MAIN
======================= */

.main {
    margin-left: 270px;
    width: calc(100% - 270px);
    padding: 35px;
}

/* =======================
TOPBAR
======================= */

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.topbar h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.topbar p {
    color: #94a3b8;
}

.top-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.top-actions input {
    width: 260px;
    padding: 13px 18px;
    background: #1e293b;
    border: none;
    border-radius: 12px;
    color: white;
    outline: none;
}

.top-actions input::placeholder {
    color: #94a3b8;
}

.circle-btn {
    width: 45px;
    height: 45px;
    border: none;
    border-radius: 50%;
    background: #1e293b;
    color: white;
    cursor: pointer;
    transition: .3s;
}

.circle-btn:hover {
    background: #2563eb;
}

.avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #2563eb;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: 700;
}

/* =======================
STATS
======================= */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 22px;
    margin-bottom: 30px;
}

.card {
    background: #1e293b;
    border-radius: 18px;
    padding: 25px;
    position: relative;
    overflow: hidden;
    transition: .35s;
    border: 1px solid rgba(255, 255, 255, .05);
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
}

.card::after {
    content: "";
    width: 140px;
    height: 140px;
    border-radius: 50%;
    position: absolute;
    right: -50px;
    top: -50px;
    background: rgba(255, 255, 255, .05);
}

.blue {
    border-top: 4px solid #3b82f6;
}

.green {
    border-top: 4px solid #22c55e;
}

.orange {
    border-top: 4px solid #f59e0b;
}

.purple {
    border-top: 4px solid #8b5cf6;
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.icon {
    font-size: 28px;
}

.card h2 {
    font-size: 42px;
    margin: 18px 0 8px;
}

.card p {
    color: #94a3b8;
}

/* =======================
CONTENT GRID
======================= */

.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

/* =======================
TABLE
======================= */

.table-card {
    background: #1e293b;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .05);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px;
}

.table-header h2 {
    margin-bottom: 5px;
}

.table-header p {
    color: #94a3b8;
    font-size: 14px;
}

.create-btn {
    border: none;
    background: #2563eb;
    color: white;
    padding: 13px 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: .3s;
    font-weight: 600;
}

.create-btn:hover {
    background: #3b82f6;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #111827;
}

th {
    padding: 18px;
    text-align: left;
    color: #cbd5e1;
    font-weight: 600;
}

td {
    padding: 18px;
    border-top: 1px solid rgba(255, 255, 255, .05);
}

tbody tr {
    transition: .3s;
}

tbody tr:hover {
    background: #243244;
}

.user {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #2563eb;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: 700;
}

.user span {
    display: block;
    color: #94a3b8;
    font-size: 13px;
    margin-top: 4px;
}

.manage-btn {
    border: none;
    background: #0ea5e9;
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    transition: .3s;
}

.manage-btn:hover {
    background: #0284c7;
}

/* =======================
RIGHT PANEL
======================= */

.activity-card {
    background: #1e293b;
    border-radius: 18px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, .05);
}

.activity-card h2 {
    margin-bottom: 25px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid rgba(255, 255, 255, .05);
}

.activity-item:last-child {
    border: none;
}

.activity-item span {
    color: #94a3b8;
}

.activity-item strong {
    font-size: 18px;
}

/* =======================
MODAL
======================= */

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .75);
    backdrop-filter: blur(8px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fadeIn .25s ease;
}

.modal {
    width: 550px;
    max-width: 95%;
    background: #1e293b;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, .06);
    padding: 30px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, .45);
    animation: popup .3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.modal-header h2 {
    font-size: 28px;
    margin-bottom: 6px;
}

.modal-header p {
    color: #94a3b8;
    font-size: 14px;
}

.close-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 12px;
    background: #111827;
    color: white;
    cursor: pointer;
    transition: .3s;
}

.close-btn:hover {
    background: #ef4444;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #cbd5e1;
    font-weight: 600;
    font-size: 14px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid #334155;
    background: #111827;
    color: white;
    outline: none;
    transition: .3s;
    font-size: 15px;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, .15);
}

.form-group input::placeholder {
    color: #64748b;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
}

.cancel-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    background: #334155;
    color: white;
    cursor: pointer;
    transition: .3s;
}

.cancel-btn:hover {
    background: #475569;
}

.save-btn {
    padding: 12px 26px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: .3s;
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(37, 99, 235, .35);
}

/* =======================
ANIMATIONS
======================= */

@keyframes fadeIn {

    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }

}

@keyframes popup {

    from {
        opacity: 0;
        transform: translateY(30px) scale(.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

@keyframes floatCard {

    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }

}

.card {
    animation: floatCard .6s ease forwards;
}

.table-card {
    animation: floatCard .8s ease;
}

.activity-card {
    animation: floatCard 1s ease;
}

/* =======================
SCROLLBAR
======================= */

::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #111827;
}

::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 20px;
}

::-webkit-scrollbar-thumb:hover {
    background: #475569;
}

/* =======================
TRANSITIONS
======================= */

button,
input,
select,
.card,
.menu a {
    transition: all .3s ease;
}

/* =======================
RESPONSIVE
======================= */

@media(max-width:1200px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

}

@media(max-width:900px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .dashboard {
        flex-direction: column;
    }

    .main {
        margin-left: 0;
        width: 100%;
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .top-actions {
        width: 100%;
    }

    .top-actions input {
        flex: 1;
        width: 100%;
    }

}

@media(max-width:768px) {

    .stats {
        grid-template-columns: 1fr;
    }

    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .modal {
        width: 95%;
        padding: 25px;
    }

}

@media(max-width:500px) {

    .topbar h1 {
        font-size: 26px;
    }

    .logo h2 {
        font-size: 18px;
    }

    .card h2 {
        font-size: 34px;
    }

    .modal-actions {
        flex-direction: column;
    }

    .cancel-btn,
    .save-btn {
        width: 100%;
    }

}

/* =======================
OPTIONAL GLASS EFFECTS
======================= */

.table-card,
.activity-card,
.card {
    backdrop-filter: blur(10px);
}

.menu a:hover,
.create-btn:hover,
.manage-btn:hover,
.save-btn:hover {
    transform: translateY(-2px);
}

/* =======================
END
======================= */
</style>
