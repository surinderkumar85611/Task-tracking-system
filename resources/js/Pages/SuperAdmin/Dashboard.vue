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

                <div class="modal-header">

                    <div>
                        <h2>Create New User</h2>
                        <p>Create an Administrator or Team Leader account.</p>
                    </div>

                    <button class="close-btn" @click="showCreateModal = false">
                        ✕
                    </button>

                </div>

                <div class="form-group">

                    <label>User Role</label>

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

                    <input v-model="form.name" type="text" placeholder="John Doe">

                </div>

                <div class="form-group">

                    <label>Email Address</label>

                    <input v-model="form.email" type="email" placeholder="john@example.com">

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input v-model="form.password" type="password" placeholder="Create password">

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
    router.post('/super-admin/admin', form, {
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

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .65);
    backdrop-filter: blur(8px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fade .25s;
}

.modal {
    width: 520px;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 18px;
    padding: 30px;
    color: white;
    box-shadow:
        0 35px 60px rgba(0, 0, 0, .45);
    animation: popup .3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 28px;
}

.modal-header h2 {
    margin: 0;
    font-size: 28px;
}

.modal-header p {
    margin-top: 6px;
    color: #94a3b8;
    font-size: 14px;
}

.close-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: none;
    background: #0f172a;
    color: white;
    cursor: pointer;
    transition: .25s;
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
    font-size: 14px;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid #334155;
    background: #0f172a;
    color: white;
    font-size: 15px;
    transition: .25s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, .18);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
}

.cancel-btn {
    background: #334155;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    cursor: pointer;
    transition: .25s;
}

.cancel-btn:hover {
    background: #475569;
}

.save-btn {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: .25s;
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px rgba(37, 99, 235, .35);
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

@keyframes fade {

    from {
        opacity: 0;
    }

    to {
        opacity: 1;
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
