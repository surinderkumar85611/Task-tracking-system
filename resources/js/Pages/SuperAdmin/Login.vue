<template>
    <div class="login-page">

        <div class="left-panel">

            <div class="brand">

                <div class="logo">
                    BTT
                </div>

                <h1>Team Management</h1>

                <p>
                    Manage admins, team leaders and members
                    from one secure dashboard.
                </p>

                <ul>
                    <li>✓ Secure authentication</li>
                    <li>✓ Team management</li>
                    <li>✓ Team collaboration</li>
                    <li>✓ Project tracking</li>
                </ul>

            </div>

        </div>

        <div class="right-panel">

            <div class="login-card">

                <h2>Welcome Back</h2>

                <p>
                    Sign in to continue
                </p>

                <div class="form-group">

                    <label>Email</label>

                    <input v-model="form.email" type="email" placeholder="Enter email">

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input v-model="form.password" type="password" placeholder="Enter password">

                </div>

                <button @click="login">
                    Sign In
                </button>

            </div>

        </div>

    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({
    email: '',
    password: '',
})

function login() {
    router.post('/super-admin/login', form)
}
</script>

<style scoped>
.login-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    background: #0f172a;
    color: white;
}

.left-panel {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 70px;
    background:
        radial-gradient(circle at top left, #2563eb33, transparent 35%),
        radial-gradient(circle at bottom right, #7c3aed33, transparent 35%);
}

.brand {
    max-width: 500px;
}

.logo {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    background: #2563eb;
    margin-bottom: 30px;
    box-shadow: 0 15px 35px rgba(37, 99, 235, .35);
}

.brand h1 {
    font-size: 48px;
    margin-bottom: 20px;
}

.brand p {
    color: #94a3b8;
    line-height: 1.8;
    margin-bottom: 30px;
}

.brand ul {
    list-style: none;
    padding: 0;
    color: #cbd5e1;
    display: grid;
    gap: 16px;
}

.right-panel {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
}

.login-card {
    width: 420px;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 20px;
    padding: 40px;
    box-shadow:
        0 25px 50px rgba(0, 0, 0, .3);
    animation: popup .45s ease;
}

.login-card h2 {
    font-size: 34px;
    margin-bottom: 8px;
}

.login-card p {
    color: #94a3b8;
    margin-bottom: 35px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #cbd5e1;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #334155;
    background: #0f172a;
    color: white;
    transition: .25s;
}

.form-group input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow:
        0 0 0 4px rgba(37, 99, 235, .2);
}

button {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    transition: .3s;
}

button:hover {
    transform: translateY(-2px);
    box-shadow:
        0 15px 30px rgba(37, 99, 235, .35);
}

@keyframes popup {
    from {
        opacity: 0;
        transform: translateY(40px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media(max-width:900px) {
    .login-page {
        grid-template-columns: 1fr;
    }

    .left-panel {
        display: none;
    }

    .login-card {
        width: 100%;
        max-width: 420px;
    }
}
</style>
