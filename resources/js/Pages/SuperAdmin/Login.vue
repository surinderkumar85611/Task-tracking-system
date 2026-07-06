<template>
    <div class="login-container">

        <div class="login-card">

            <h1>Super Admin Login</h1>

            <input v-model="form.email" type="email" placeholder="Email">

            <input v-model="form.password" type="password" placeholder="Password">

            <button @click="login">
                Login
            </button>

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
.login-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f5f7fb;
}

.login-card {
    width: 420px;
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
}

.login-card h1 {
    text-align: center;
    margin-bottom: 25px;
}

input {
    width: 100%;
    margin-bottom: 15px;
    padding: 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
}

button {
    width: 100%;
    padding: 14px;
    border: none;
    background: #2563eb;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
}

button:hover {
    background: #1d4ed8;
}
</style>
