<template>
  <div class="auth-page dark">
    <!-- Left Split Panel -->
    <div class="left-panel">
      <div class="content">
        <h1>Join EPMS</h1>
        <p>Create your workspace and manage teams professionally.</p>
      </div>
    </div>

    <!-- Right Form Panel -->
    <div class="right-panel">
      <div class="card">
        <h2>Create Account</h2>
        <p>Get started with your enterprise workspace</p>

        <form @submit.prevent="register">
          <input type="text" v-model="form.name" placeholder="Full Name" />
          <input type="email" v-model="form.email" placeholder="Email" />
          <input type="password" v-model="form.password" placeholder="Password" />

          <button>Create Account</button>
        </form>

        <div class="links">
          <Link href="/login">Already have an account?</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from "vue";
import { router, Link } from "@inertiajs/vue3";

const form = reactive({
  name: "",
  email: "",
  password: "",
});

const register = () => {
  router.post("/register", form);
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
    linear-gradient(rgba(15,23,42,.85), rgba(15,23,42,.85)),
    url('https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=2070');
  background-size: cover;
  background-position: center;
  color: white;
  display: flex;
  align-items: center;
  padding: 80px;
}

.content h1 {
  font-size: 64px;
  margin-bottom: 20px;
  font-weight: 700;
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
  font-size: 14px;
  transition: border-color 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #6366f1;
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
  font-size: 15px;
  transition: background 0.2s ease;
}

button:hover {
  background: #4338ca;
}

.links {
  margin-top: 22px;
  display: flex;
  justify-content: center;
}

.links a {
  color: #818cf8;
  text-decoration: none;
  font-size: 14px;
}

.links a:hover {
  text-decoration: underline;
}
</style>
