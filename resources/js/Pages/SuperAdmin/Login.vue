<template>
  <Head title="Super Admin Login" />
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>Baseline Task Tracker</h1>
        <p>Super Admin console — manage admins, team leaders and projects from one place.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Welcome Back</h2>
        <p>Sign in to the Super Admin console</p>

        <form @submit.prevent="login">
          <div class="field-group">
            <input
              type="email"
              v-model="form.email"
              placeholder="Email"
              @blur="validateEmail(); handleBlur('email')"
              @input="validateEmail"
            />

            <span v-if="errors.email && touched.email" class="error-text">
              {{ errors.email }}
            </span>
          </div>

          <div class="field-group">
            <div class="input-wrapper">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="Password"
                @blur="validatePassword(); handleBlur('password')"
                @input="validatePassword"
              />

              <span class="toggle-icon" @click="showPassword = !showPassword">
                👁️
              </span>
            </div>

            <span v-if="errors.password && touched.password" class="error-text">
              {{ errors.password }}
            </span>
          </div>

          <button :disabled="loading">
            {{ loading ? "Signing In..." : "Sign In" }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import axios from "axios";

const toast = useToast();

const form = reactive({
  email: "",
  password: "",
});

const errors = reactive({
  email: "",
  password: "",
});

const touched = reactive({
  email: false,
  password: false,
});

const showPassword = ref(false);
const loading = ref(false);

const hasErrors = computed(() => {
  return !!errors.email || !!errors.password;
});

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
  if (!form.password) {
    errors.password = "Password is required";
  } else if (form.password.length < 6) {
    errors.password = "Password must be at least 6 characters";
  } else {
    errors.password = "";
  }
};

const handleBlur = (field) => {
  touched[field] = true;
};

const login = () => {
  touched.email = true;
  touched.password = true;

  validateEmail();
  validatePassword();

  if (hasErrors.value) return;

  loading.value = true;

  axios
    .post("/super-admin/login", form)
    .then(() => {
      toast.success("Welcome back!", {
        toastClassName: "custom-toast",
      });
      setTimeout(() => {
        router.visit("/super-admin/dashboard");
      }, 1200);
    })
    .catch((error) => {
      const backendErrors = error.response?.data?.errors;

      if (backendErrors?.email) {
        toast.error(backendErrors.email[0], {
          toastClassName: "custom-toast",
        });
        return;
      }

      if (backendErrors) {
        Object.values(backendErrors).forEach((fieldErrors) => {
          toast.error(Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors, {
            toastClassName: "custom-toast",
          });
        });
        return;
      }

      toast.error("Something went wrong. Please try again.", {
        toastClassName: "custom-toast",
      });
    })
    .finally(() => {
      loading.value = false;
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
  background:
    linear-gradient(rgba(15, 23, 42, .85), rgba(15, 23, 42, .85)),
    url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?q=80&w=2070');
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
  max-width: 460px;
  line-height: 1.5;
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

.field-group {
  margin-bottom: 16px;
}

input {
  width: 100%;
  height: 52px;
  border-radius: 12px;
  border: 1px solid #334155;
  background: #1e293b;
  color: white;
  padding: 0 15px;
  font-size: 14px;
  box-sizing: border-box;
  transition: border-color 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #2563eb;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.toggle-icon {
  position: absolute;
  right: 15px;
  cursor: pointer;
  font-size: 18px;
  user-select: none;
}

.error-text {
  display: block;
  color: #ff0000 !important;
  font-size: 12px;
  margin-top: 5px;
  margin-left: 4px;
  margin-bottom: 0;
  font-weight: 600;
  line-height: 1.2;
}

button {
  width: 100%;
  height: 52px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  color: white;
  font-weight: 600;
  cursor: pointer;
  font-size: 15px;
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
  margin-top: 8px;
}

button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 30px rgba(37, 99, 235, .35);
}

button:disabled {
  opacity: .7;
  cursor: not-allowed;
}

:global(.custom-toast) {
  background: #1e3a8a !important;
  color: white !important;
  border-radius: 12px !important;
}

@media (max-width: 900px) {
  .auth-page {
    flex-direction: column;
  }

  .left-panel,
  .right-panel {
    width: 100%;
  }

  .left-panel {
    display: none;
  }

  .card {
    width: 100%;
    max-width: 420px;
  }
}
</style>