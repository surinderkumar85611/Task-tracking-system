<template>
    <div class="dashboard">

        <header class="topbar">

            <div>
                <h1>Super Admin Dashboard</h1>
                <p>Manage platform administrators and workspaces.</p>
            </div>

            <button class="logout-btn">
                Logout
            </button>

        </header>

        <section class="stats">

            <div class="card">
                <h2>{{ stats.admins }}</h2>
                <span>Total Admins</span>
            </div>

            <div class="card">
                <h2>{{ stats.workspaces }}</h2>
                <span>Workspaces</span>
            </div>

            <div class="card">
                <h2>{{ stats.projects }}</h2>
                <span>Projects</span>
            </div>

            <div class="card">
                <h2>{{ stats.members }}</h2>
                <span>Members</span>
            </div>

        </section>

        <section class="admins">

            <div class="table-header">

                <h2>Create User</h2>

                <button class="create-btn" @click="showCreateModal = true">
                    + Create User
                </button>

            </div>

            <table>

                <thead>

                    <tr>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Joined</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <tr v-for="admin in admins" :key="admin.id">

                        <td>{{ admin.name }}</td>

                        <td>{{ admin.email }}</td>

                        <td>{{ admin.created_at }}</td>

                        <td>

                            <button class="manage-btn">
                                Manage
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </section>

        <div v-if="showCreateModal" class="modal-overlay">

            <div class="modal">

                <h2>Create User</h2>

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

                    <label>Full Name</label>

                    <input v-model="form.name" placeholder="Enter full name">

                </div>

                <div class="form-group">

                    <label>Email Address</label>

                    <input v-model="form.email" type="email" placeholder="Enter email address">

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input v-model="form.password" type="password" placeholder="Enter password">

                </div>

                <div class="form-group">

                    <label>Confirm Password</label>

                    <input v-model="form.password_confirmation" type="password" placeholder="Confirm password">

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
    stats: Object,
    admins: Array,
})

const showCreateModal = ref(false)

const form = reactive({
    role: 'ADMIN',

    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function createAdmin() {
    router.post('/super-admin/admins', form, {
        onSuccess: () => {
            showCreateModal.value = false

            form.name = ''
            form.email = ''
            form.password = ''
            form.password_confirmation = ''
        }
    })
}
</script>

<style scoped>
.dashboard {
    background: #f5f7fb;
    min-height: 100vh;
    padding: 35px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.topbar h1 {
    font-size: 34px;
    margin: 0;
}

.topbar p {
    color: #777;
}

.logout-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 10px;
    cursor: pointer;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 35px;
}

.card {
    background: white;
    border-radius: 14px;
    padding: 28px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, .05);
}

.card h2 {
    font-size: 42px;
    margin: 0;
}

.card span {
    color: #666;
}

.admins {
    background: white;
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, .05);
}

.table-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    align-items: center;
}

.create-btn {
    background: #2563eb;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f4f6f8;
    text-align: left;
    padding: 15px;
}

td {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.manage-btn {
    background: #0ea5e9;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 7px;
    cursor: pointer;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal {
    width: 480px;
    background: #fff;
    border-radius: 14px;
    padding: 30px;
}

.modal h2 {
    margin-bottom: 20px;
}

.modal input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 15px;
}

.cancel-btn {
    background: #e5e7eb;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.save-btn {
    background: #2563eb;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.modal select {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #374151;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
    outline: none;
    transition: .2s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #2563eb;
}
</style>
