<template>
    <Head title="2-Factor-Auth" />
  <div class="auth-page dark">
    <div class="left-panel">
      <div class="content">
        <h1>Baseline Task Tracker</h1>
        <p>Protecting your account with enterprise security.</p>
      </div>
    </div>

    <div class="right-panel">
      <div class="card">
        <h2>Security Verification</h2>
        <p>Enter the 6-digit verification code from your authenticator app.</p>

        <form @submit.prevent="submitOtp">
          <div class="field-group">
            <input
              type="text"
              v-model="form.code"
              placeholder="000000"
              maxlength="6"
              @input="cleanInput"
              @blur="validateOtp"
            />

            <span v-if="error" class="error-text">
              {{ error }}
            </span>
          </div>

          <button >
            Verify Code
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
import { reactive, ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { Head } from '@inertiajs/vue3';

const toast = useToast();
const error = ref("");

const form = reactive({
  code: "",
});

// Ensure only digits can be typed into the input field
const cleanInput = () => {
  form.code = form.code.replace(/\D/g, "");
  validateOtp();
};

const validateOtp = () => {
  if (!form.code) {
    error.value = "Verification code is required";
  } else if (form.code.length !== 6) {
    error.value = "Code must be exactly 6 digits";
  } else {
    error.value = "";
  }
};

const submitOtp = () => {
  validateOtp();
  if (error.value) return;

  router.post("/two-factor-challenge", form, {
    preserveState: true,
    preserveScroll: true,

    onSuccess: () => {
      toast.success("Identity verified successfully!", {
        toastClassName: "custom-toast",
      });
    },

    onError: (backendErrors) => {
      if (backendErrors.code) {
        error.value = backendErrors.code;
        toast.error(backendErrors.code, {
          toastClassName: "custom-toast",
        });
      } else {
        toast.error("Verification failed. Please try again.", {
          toastClassName: "custom-toast",
        });
      }
    },
  });
};
</script>

<style scoped>
/* Reuses your beautiful dark-mode theme variables and structural styling */
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
  font-size: 18px;
  letter-spacing: 4px;
  text-align: center;
  transition: border-color 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #6366f1;
}

.error-text {
  display: block;
  color: #ff0000 !important;
  font-size: 12px;
  margin-top: 5px;
  margin-left: 4px;
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
  transition: all 0.2s ease;
  margin-top: 8px;
}

button:hover:not(:disabled) {
  background: #4338ca;
}

button:disabled {
  background: #334155;
  color: #64748b;
  cursor: not-allowed;
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

:global(.custom-toast) {
  background: #312e81 !important;
  color: white !important;
  border-radius: 12px !important;
}
</style>
