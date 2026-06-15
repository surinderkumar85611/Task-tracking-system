<template>
    <Head title="Reset-Password" />
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
          <div class="field-group">
            <p class="locked-text">
              Email cannot be changed
            </p>

            <input
              type="email"
              v-model="form.email"
              placeholder="Email"
              disabled
              class="disabled-input"
            />
          </div>

          <div class="field-group">
            <div class="input-wrapper">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="New Password"
                @blur="validatePassword(); handleBlur('password')"
                @input="validatePassword"
              />

              <span
                class="toggle-icon"
                @click="showPassword = !showPassword"
              >
                👁️
              </span>
            </div>

            <p
              v-if="errors.password && touched.password"
              class="error-text"
            >
              {{ errors.password }}
            </p>
          </div>

          <div class="field-group">
            <div class="input-wrapper">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="form.password_confirmation"
                placeholder="Confirm Password"
                @blur="
                  validateConfirmPassword();
                  handleBlur('password_confirmation');
                "
                @input="validateConfirmPassword"
              />

              <span
                class="toggle-icon"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                👁️
              </span>
            </div>

            <p
              v-if="
                errors.password_confirmation &&
                touched.password_confirmation
              "
              class="error-text"
            >
              {{ errors.password_confirmation }}
            </p>
          </div>

          <button>
            Reset Password
          </button>
        </form>

        <div class="links">
          <Link href="/login">Back to Login</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, onMounted } from "vue";
import { router, Link, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { Head } from '@inertiajs/vue3';

const toast = useToast();
const page = usePage();

const form = reactive({
  email: page.props.email || "",
  token: page.props.token || "",
  password: "",
  password_confirmation: "",
});

const errors = reactive({
  password: "",
  password_confirmation: "",
});

const touched = reactive({
  password: false,
  password_confirmation: false,
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

onMounted(() => {
  if (!form.token || !form.email) {
    router.visit("/login");
  }
});

const hasErrors = computed(() => {
  return (
    !!errors.password ||
    !!errors.password_confirmation
  );
});

const validatePassword = () => {
  const regex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/;

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

const submit = () => {
  touched.password = true;
  touched.password_confirmation = true;

  validatePassword();
  validateConfirmPassword();


  router.post("/reset-password", form, {
    preserveState: true,
    preserveScroll: true,

    onSuccess: () => {
      toast("Password reset successful", {
        type: "success",
        toastClassName: "custom-toast",
      });

      setTimeout(() => {
        router.visit("/login");
      }, 1200);
    },

    onError: () => {
      toast.error("Invalid or expired reset link", {
        toastClassName: "custom-toast",
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

.field-group {
  margin-bottom: 18px;
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

.disabled-input {
  opacity: 0.7;
  cursor: not-allowed;
}

.locked-text {
  color: #94a3b8;
  font-size: 12px;
  margin-bottom: 6px;
  margin-left: 2px;
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
  margin-top: 6px;
  margin-left: 2px;
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
