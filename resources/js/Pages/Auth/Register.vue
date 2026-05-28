<template>
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>Baseline Task Tracker</h1>
        <p>Create your workspace and manage teams professionally.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Create Account</h2>
        <p>Get started with your enterprise workspace</p>

        <form @submit.prevent="register">
          <input type="text" v-model="form.name" placeholder="Full Name" @input="validateName"
            @focus="activeField = 'name'" @blur="handleBlur('name')" />
          <p v-if="errors.name && activeField === 'name'" class="error-text">
            {{ errors.name }}
          </p>

          <input type="email" v-model="form.email" placeholder="Email" @input="validateEmail"
            @focus="activeField = 'email'" @blur="handleBlur('email')" />
          <p v-if="errors.email && activeField === 'email'" class="error-text">
            {{ errors.email }}
          </p>

          <div class="input-wrapper">
            <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Password"
              @input="validatePassword" @focus="activeField = 'password'" @blur="handleBlur('password')" />
            <span class="toggle-icon" @click="showPassword = !showPassword">
              {{ showPassword ? '👁️' : '👁️' }}
            </span>
          </div>

          <p v-if="errors.password && activeField === 'password'" class="error-text">
            {{ errors.password }}
          </p>

          <input :type="showPassword ? 'text' : 'password'" v-model="form.password_confirmation"
            placeholder="Confirm Password" @input="validateConfirmPassword" @focus="activeField = 'confirm'"
            @blur="handleBlur('confirm')" />

          <p v-if="errors.password_confirmation && activeField === 'confirm'" class="error-text">
            {{ errors.password_confirmation }}
          </p>

          <button :disabled="hasErrors || isFormEmpty">
            Create Account
          </button>
        </form>

        <div class="links">
          <Link href="/login">Already have an account?</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref } from "vue";
import { router, Link } from "@inertiajs/vue3";

const form = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const errors = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const showPassword = ref(false);
const activeField = ref("");

const isFormEmpty = computed(() => {
  return (
    !form.name || !form.email || !form.password || !form.password_confirmation
  );
});

const hasErrors = computed(() => {
  return (
    !!errors.name ||
    !!errors.email ||
    !!errors.password ||
    !!errors.password_confirmation
  );
});

const validateName = () => {
  const regex = /^[A-Za-z\s]+$/;

  if (!form.name) {
    errors.name = "Name is required";
  } else if (form.name.length < 4) {
    errors.name = "Name must be at least 4 characters";
  } else if (!regex.test(form.name)) {
    errors.name = "Only letters allowed";
  } else {
    errors.name = "";
  }
};

const validateEmail = () => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!form.email) {
    errors.email = "Email is required";
  } else if (!regex.test(form.email)) {
    errors.email = "Please enter a valid email address";
  } else {
    errors.email = "";
  }
};

const validatePassword = () => {
  const regex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/;

  if (!form.password) {
    errors.password = "Password is required";
  } else if (!regex.test(form.password)) {
    errors.password =
      "Must include uppercase, lowercase, number & special char";
  } else {
    errors.password = "";
  }

  validateConfirmPassword();
};

const validateConfirmPassword = () => {
  if (!form.password_confirmation || !form.password) {
    errors.password_confirmation = "";
    return;
  }

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = "Passwords do not match";
  } else {
    errors.password_confirmation = "";
  }
};

const handleBlur = (field) => {
  activeField.value = "";
  errors[field] = "";
};

const register = () => {
  if (!hasErrors.value) {
    router.post("/register", form);
  }
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
  background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)),
    url("https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=2070");
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

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.toggle-icon {
  position: absolute;
  right: 15px;
  color: #6366f1;
  font-size: 12px;
  cursor: pointer;
  font-weight: 600;
  user-select: none;
}

.error-text {
  color: #ff4d4d;
  font-size: 12px;
  margin: -12px 0 10px 5px;
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

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #3730a3;
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
