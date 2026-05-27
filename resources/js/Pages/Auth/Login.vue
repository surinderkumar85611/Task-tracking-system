<template>
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>EPMS</h1>
        <p>Manage enterprise projects with confidence.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Welcome Back</h2>
        <p>Login to continue</p>

        <form @submit.prevent="login">
          <input type="email" v-model="form.email" placeholder="Email" />
          <input type="password" v-model="form.password" placeholder="Password" />

          <button>Login</button>
        </form>

        <div class="links">
          <Link href="/forgot-password">Forgot Password?</Link>
          <Link href="/register">Create Account</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from "vue";
import { router, Link } from "@inertiajs/vue3";

const form = reactive({
  email: "",
  password: "",
});

const login = () => {
  router.post("/login", form);
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  font-family: Inter, sans-serif;
}

.left-panel {
  width: 55%;
  background:
    linear-gradient(rgba(15,23,42,.8), rgba(15,23,42,.8)),
    url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?q=80&w=2070');
  background-size: cover;
  color: white;
  display: flex;
  align-items: center;
  padding: 80px;
}

.content h1 {
  font-size: 64px;
  margin-bottom: 20px;
}

.content p {
  font-size: 18px;
  opacity: .85;
}

.right-panel {
  width: 45%;
  background: #020617;
  display: flex;
  justify-content: center;
  align-items: center;
}

.card {
  width: 420px;
  background: #0f172a;
  padding: 40px;
  border-radius: 20px;
  color: white;
}

.card h2 {
  font-size: 32px;
}

.card p {
  color: #94a3b8;
  margin: 12px 0 30px;
}

input {
  width: 100%;
  height: 52px;
  margin-bottom: 18px;
  border-radius: 12px;
  border: 1px solid #334155;
  background: #1e293b;
  color: white;
  padding: 0 15px;
}

button {
  width: 100%;
  height: 52px;
  border: none;
  border-radius: 12px;
  background: #4f46e5;
  color: white;
  font-weight: 600;
  cursor: pointer;
}

.links {
  margin-top: 22px;
  display: flex;
  justify-content: space-between;
}

.links a {
  color: #818cf8;
  text-decoration: none;
}
</style>
