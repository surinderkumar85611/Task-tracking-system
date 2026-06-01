<template>
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>Baseline Task Tracking</h1>
        <p>Restoring access to your enterprise projects safely.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Forgot Password?</h2>
        <p>Enter your email and we'll send you a reset link.</p>

        <form @submit.prevent="sendReset">
          <div class="field-group">
            <input
              type="email"
              v-model="form.email"
              placeholder="Email Address"
              @blur="validateEmail(); handleBlur('email')"
              @input="validateEmail"
            />

            <p
              v-if="errors.email && touched.email"
              class="error-text"
            >
              {{ errors.email }}
            </p>
          </div>

          <button>
            Send Reset Link
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
import { reactive, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const toast = useToast();

const form = reactive({
  email: "",
});

const errors = reactive({
  email: "",
});

const touched = reactive({
  email: false,
});

const hasErrors = computed(() => {
  return !!errors.email;
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

const handleBlur = (field) => {
  touched[field] = true;
};

const sendReset = () => {
  touched.email = true;

  validateEmail();

  if (hasErrors.value) {
    toast.error("Please fix validation errors", {
      toastClassName: "custom-toast",
    });
    return;
  }

  router.post("/forgot-password", form, {
    preserveState: true,
    preserveScroll: true,

    onSuccess: () => {
      toast("Reset password link sent successfully", {
        type: "success",
        toastClassName: "custom-toast",
      });

      form.email = "";
      errors.email = "";
      touched.email = false;
    },

    onError: (backendErrors) => {
      if (backendErrors.email) {
        toast.error(backendErrors.email, {
          toastClassName: "custom-toast",
        });
        return;
      }

      toast.error("Something went wrong", {
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
  background:
    linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)),
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

.card h2 {
  font-size: 32px;
}

.card > p {
  color: #94a3b8;
  margin: 12px 0 30px;
  line-height: 1.5;
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
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #6366f1;
}

.error-text {
  color: #ff3b3b !important;
  font-size: 12px;
  margin-top: 4px;
  margin-left: 2px;
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
  margin-top: 6px;
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

.field-group .error-text {
  color: #ff3b3b !important;
}

:global(.custom-toast) {
  background: #312e81 !important;
  color: white !important;
  border-radius: 12px !important;
}
</style>