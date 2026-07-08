<template>
    <Head title="Login" />
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>Baseline Task Tracker</h1>
        <p>Manage enterprise projects with confidence.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Welcome Back</h2>
        <p>Login to continue</p>

        <form @submit.prevent="login">
          <div class="field-group">
            <input type="email" v-model="form.email" placeholder="Email" @blur="validateEmail(); handleBlur('email')"
              @input="validateEmail" />

            <span v-if="errors.email && touched.email" class="error-text">
              {{ errors.email }}
            </span>
          </div>

          <div class="field-group">
            <div class="input-wrapper">
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Password"
                @blur="validatePassword(); handleBlur('password')" @input="validatePassword" />

              <span class="toggle-icon" @click="showPassword = !showPassword">
                👁️
              </span>
            </div>

            <span v-if="errors.password && touched.password" class="error-text">
              {{ errors.password }}
            </span>
          </div>

          <button>
            Login
          </button>
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
import { reactive, computed, ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { Head } from '@inertiajs/vue3';

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

  router.post("/login", form, {
    preserveState: true,
    preserveScroll: true,

    onSuccess: (page) => {
      if (page.props.flash?.two_factor_required || page.props.auth?.two_factor_pending) {
        toast.info("Two-factor authentication required.", {
          toastClassName: "custom-toast",
        });
        router.get("/two-factor-challenge");
        return;
      }

      toast("Login successful", {
        type: "success",
        toastClassName: "custom-toast",
      });
    },

    onError: (backendErrors) => {
      if (backendErrors.email) {
        toast.error("Invalid email or password", {
          toastClassName: "custom-toast",
        });
        return;
      }

      Object.values(backendErrors).forEach((error) => {
        toast.error(error, {
          toastClassName: "custom-toast",
        });
      });
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
  background:
    linear-gradient(rgba(15, 23, 42, .8), rgba(15, 23, 42, .8)),
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
  background: #4f46e5;
  color: white;
  font-weight: 600;
  cursor: pointer;
  font-size: 15px;
  transition: background 0.2s ease;
  margin-top: 8px;
}

button:hover {
  background: #4338ca;
}

.links {
  margin-top: 22px;
  display: flex;
  justify-content: space-between;
}

.links a {
  color: #818cf8;
  text-decoration: none;
  font-size: 14px;
}

.links a:hover {
  text-decoration: underline;
}

:global(.custom-toast) {
  background: #312e81 !important;
  color: white !important;
  border-radius: 12px !important;
}
</style>
