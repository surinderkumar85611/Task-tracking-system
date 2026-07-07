<template>
    <div class="dashboard">

        <div class="layout">

            <aside class="sidebar">

                <h1>Super Admin Dashboard</h1>

                <p>
                    Manage platform administrators and workspaces.
                </p>

                <button class="logout-btn" @click="logout">
                    Logout
                </button>

            </aside>

            <main class="content">

                <section class="stats">

                    <div class="card" v-for="(item, index) in [
                        { value: stats.admins, label: 'Total Admins' },
                        { value: stats.workspaces, label: 'Workspaces' },
                        { value: stats.projects, label: 'Projects' },
                        { value: stats.members, label: 'Members' }
                    ]" :key="index" :style="{ animationDelay: `${index * 0.12}s` }">
                        <h2>{{ item.value }}</h2>
                        <span>{{ item.label }}</span>
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

                </section>

            </main>
        </div>

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

function logout() {
    router.post('/super-admin/logout')
}
</script>

<style scoped>
.dashboard {
    display: block;
    min-height: 100vh;
    background: #0f172a;
    color: white;
    padding: 40px;
}

.layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 35px;
    align-items: start;
}

.sidebar {
    position: sticky;
    top: 40px;

    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 20px;

    padding: 35px;

    height: fit-content;
}

.sidebar h1 {
    font-size: 38px;
    margin-bottom: 18px;
}

.sidebar p {
    color: #94a3b8;
    line-height: 1.6;
    margin-bottom: 30px;
}

.logout-btn {
    padding: 12px 22px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
    box-shadow: 0 10px 25px rgba(239, 68, 68, .3);
}

.logout-btn:hover {
    transform: translateY(-3px);
}

.content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.card {

    background: #1e293b;

    border: 1px solid #334155;

    border-radius: 18px;

    padding: 28px;

    transition: .35s;

    position: relative;

    overflow: hidden;
}

.card:hover {

    transform: translateY(-8px);

    border-color: #3b82f6;

    box-shadow: 0 20px 40px rgba(59, 130, 246, .25);
}

.card h2 {

    color: #60a5fa;

    font-size: 50px;

    margin: 0;
}

.card span {

    color: #94a3b8;
}

.card::before {

    content: '';

    position: absolute;

    width: 150px;
    height: 150px;

    background: rgba(37, 99, 235, .08);

    right: -40px;
    top: -40px;

    border-radius: 50%;
}

.admins {
    background: #1e293b;
    border-radius: 18px;
    border: 1px solid #334155;
    overflow: hidden;
}

.table-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 25px;
}

table {

    width: 100%;

    border-collapse: collapse;
}

thead {

    background: #111827;
}

th {

    color: #cbd5e1;

    padding: 18px;
}

td {

    padding: 18px;

    color: #e2e8f0;

    border-top: 1px solid #334155;
}

tbody tr {
    text-align: center;
    transition: .3s;
}

tbody tr:hover {

    background: #273449;
}

.create-btn {

    background: #2563eb;

    color: white;

    border: none;

    padding: 12px 22px;

    border-radius: 10px;

    cursor: pointer;

    transition: .3s;
}

.create-btn:hover {

    background: #3b82f6;
}

.manage-btn {

    background: #0ea5e9;

    color: white;

    border: none;

    padding: 10px 18px;

    border-radius: 8px;

    cursor: pointer;
}

.manage-btn:hover {

    background: #0284c7;

    transform: scale(1.05);
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

.modal-overlay {

    position: fixed;

    inset: 0;

    background: rgba(15, 23, 42, .45);

    backdrop-filter: blur(6px);

    display: flex;

    justify-content: center;

    align-items: center;

    animation: fade .25s;

    z-index: 999;
}

.modal {

    width: 520px;

    background: white;

    border-radius: 22px;

    padding: 35px;

    box-shadow:
        0 30px 60px rgba(0, 0, 0, .25);

    animation: popup .35s ease;
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

    padding: 14px;

    border: 1px solid #d1d5db;

    border-radius: 12px;

    transition: .25s;

    font-size: 15px;

    background: #f9fafb;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #2563eb;
    background: white;
    box-shadow:
        0 0 0 4px rgba(37, 99, 235, .15);
}

@keyframes popup {
    from {
        opacity: 0;
        transform: translateY(40px) scale(.9);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes fade {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes fadeUp {
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
    opacity: 0;
    animation: cardIn .6s forwards;
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media(max-width:1100px) {
    .layout {
        grid-template-columns: 1fr;
    }

    .sidebar {
        position: relative;
        top: 0;
    }

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width:700px) {
    .stats {
        grid-template-columns: 1fr;
    }
}
</style>
