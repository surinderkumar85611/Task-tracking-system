<template>
    <Head title="Register" />
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
          <div class="field-group">
            <input type="text" v-model="form.name" placeholder="Full Name" @blur="validateName(); handleBlur('name')"
              @input="validateName" />
            <p v-if="errors.name && touched.name" class="error-text">
              {{ errors.name }}
            </p>
          </div>

          <div class="field-group">
            <input type="email" v-model="form.email" placeholder="Email" @blur="validateEmail(); handleBlur('email')"
              @input="validateEmail" />
            <p v-if="errors.email && touched.email" class="error-text">
              {{ errors.email }}
            </p>
          </div>

          <div class="field-group">
            <div class="input-wrapper">
              <input :type="showPassword ? 'text' : 'password'" v-model="form.password" placeholder="Password"
                @blur="validatePassword(); handleBlur('password')" @input="validatePassword" />
              <span class="toggle-icon" @click="showPassword = !showPassword">
                👁️
              </span>
            </div>
            <p v-if="errors.password && touched.password" class="error-text">
              {{ errors.password }}
            </p>
          </div>

          <div class="field-group">
            <div class="input-wrapper">
              <input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.password_confirmation"
                placeholder="Confirm Password" @blur="validateConfirmPassword(); handleBlur('password_confirmation')"
                @input="validateConfirmPassword" />
              <span class="toggle-icon" @click="showConfirmPassword = !showConfirmPassword">
                👁️
              </span>
            </div>
            <p v-if="errors.password_confirmation && touched.password_confirmation" class="error-text">
              {{ errors.password_confirmation }}
            </p>
          </div>

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
import { reactive, computed, ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { Head } from '@inertiajs/vue3';

const toast = useToast();

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

const touched = reactive({
  name: false,
  email: false,
  password: false,
  password_confirmation: false,
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

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
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/;

  if (!form.password) {
    errors.password = "Password is required";
  } else if (form.password.length < 6) {
    errors.password = "Password must be at least 6 characters";
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
  touched[field] = true;
};

const register = () => {
  touched.name = true;
  touched.email = true;
  touched.password = true;
  touched.password_confirmation = true;

  validateName();
  validateEmail();
  validatePassword();
  validateConfirmPassword();

  // Stop if frontend validation fails
  if (hasErrors.value) {
    return;
  }

  router.post("/register", form, {
    preserveState: true,
    preserveScroll: true,

    onSuccess: () => {
      toast("Account created successfully", {
        type: "success",
        toastClassName: "custom-toast",
      });

      form.name = "";
      form.email = "";
      form.password = "";
      form.password_confirmation = "";

      errors.name = "";
      errors.email = "";
      errors.password = "";
      errors.password_confirmation = "";

      touched.name = false;
      touched.email = false;
      touched.password = false;
      touched.password_confirmation = false;

      setTimeout(() => {
        router.visit("/login");
      }, 1200);
    },

    onError: (backendErrors) => {
      if (backendErrors.email) {
        errors.email = Array.isArray(backendErrors.email)
          ? backendErrors.email[0]
          : backendErrors.email;

        touched.email = true;
      }

      if (backendErrors.name) {
        errors.name = Array.isArray(backendErrors.name)
          ? backendErrors.name[0]
          : backendErrors.name;

        touched.name = true;
      }

      if (backendErrors.password) {
        errors.password = Array.isArray(backendErrors.password)
          ? backendErrors.password[0]
          : backendErrors.password;

        touched.password = true;
      }
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

.field-group {
  margin-bottom: 14px;
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
  color: #ff0000 !important;
  font-size: 12px;
  margin-top: 4px;
  margin-left: 4px;
  font-weight: 600;
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
  justify-content: center;
}

.links a {
  color: #818cf8;
  text-decoration: none;
}
:global(.custom-toast) {
  background: #312e81 !important;
  color: white !important;
  border-radius: 12px !important;
}
</style>
