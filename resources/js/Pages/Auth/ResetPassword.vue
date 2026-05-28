<template>
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>Reset Password</h1>
        <p>Create a new password for your account</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Reset Password</h2>

        <form @submit.prevent="submit">
          <input type="email" v-model="form.email" placeholder="Email" />

          <input type="password" v-model="form.password" placeholder="New Password" />

          <input type="password" v-model="form.password_confirmation" placeholder="Confirm Password" />

          <button>Reset Password</button>
        </form>

        <div class="links">
          <Link href="/login">Back to Login</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from "vue";
import { router, Link, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();
const page = usePage();

const form = reactive({
  email: page.props.email || "",
  token: page.props.token || "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  if (!form.password || !form.password_confirmation) {
    toast.error("Please fill all fields");
    return;
  }

  router.post("/reset-password", form, {
    onSuccess: () => {
      toast.success("Password reset successful");
    },
    onError: () => {
      toast.error("Invalid or expired reset link");
    },
  });
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
  background: linear-gradient(rgba(15,23,42,.85), rgba(15,23,42,.85)),
    url('https://images.unsplash.com/photo-1639322537228-f710d846310a?q=80&w=2070');
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
  opacity: 0.85;
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

button:hover {
  background: #4338ca;
}
</style>