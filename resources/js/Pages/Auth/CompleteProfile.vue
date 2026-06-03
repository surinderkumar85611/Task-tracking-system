<template>
    <div class="auth-page dark">
        <div class="left-panel">
            <div class="content">
                <h1>Baseline Task Tracker</h1>
                <p>
                    Complete your profile and join your workspace.
                </p>
            </div>
        </div>

        <div class="right-panel">
            <div class="card">
                <h2>Complete Profile</h2>

                <p>
                    You have been invited to join a workspace.
                    Complete your profile to continue.
                </p>

                <form @submit.prevent="completeProfile">

                    <!-- Name -->
                    <div class="field-group">
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Full Name"
                            @blur="validateName(); handleBlur('name')"
                            @input="validateName"
                        />

                        <p
                            v-if="errors.name && touched.name"
                            class="error-text"
                        >
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="field-group">
                        <label class="field-label">
                            Email Address
                        </label>

                        <input
                            type="email"
                            v-model="form.email"
                            readonly
                        />
                    </div>

                    <!-- Department -->
                    <div class="field-group">
                        <label class="field-label">
                            Department
                        </label>

                        <input
                            type="text"
                            v-model="form.department"
                            readonly
                        />
                    </div>

                    <!-- Role -->
                    <div class="field-group">
                        <label class="field-label">
                            Role
                        </label>

                        <input
                            type="text"
                            v-model="form.role"
                            readonly
                        />
                    </div>

                    <!-- Password -->
                    <div class="field-group">
                        <div class="input-wrapper">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                placeholder="Password"
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

                    <!-- Confirm Password -->
                    <div class="field-group">
                        <div class="input-wrapper">
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                placeholder="Confirm Password"
                                @blur="
                                    validateConfirmPassword();
                                    handleBlur('password_confirmation')
                                "
                                @input="validateConfirmPassword"
                            />

                            <span
                                class="toggle-icon"
                                @click="
                                    showConfirmPassword =
                                        !showConfirmPassword
                                "
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
                        Complete Profile
                    </button>

                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

const page = usePage();
const toast = useToast();

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const form = reactive({
    name: "",
    email: page.props.email,
    department: page.props.department,
    role: page.props.role,
    workspace_id: page.props.workspace_id,

    password: "",
    password_confirmation: "",
});

const errors = reactive({
    name: "",
    password: "",
    password_confirmation: "",
});

const touched = reactive({
    name: false,
    password: false,
    password_confirmation: false,
});

const hasErrors = computed(() => {
    return (
        !!errors.name ||
        !!errors.password ||
        !!errors.password_confirmation
    );
});

const handleBlur = (field) => {
    touched[field] = true;
};

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

const validatePassword = () => {
    const regex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/;

    if (!form.password) {
        errors.password = "Password is required";
    } else if (form.password.length < 8) {
        errors.password = "Password must be at least 8 characters";
    } else if (!regex.test(form.password)) {
        errors.password =
            "Must include uppercase, lowercase, number & special char";
    } else {
        errors.password = "";
    }

    validateConfirmPassword();
};

const validateConfirmPassword = () => {
    if (
        form.password_confirmation &&
        form.password !== form.password_confirmation
    ) {
        errors.password_confirmation =
            "Passwords do not match";
    } else {
        errors.password_confirmation = "";
    }
};

const completeProfile = () => {

    touched.name = true;
    touched.password = true;
    touched.password_confirmation = true;

    validateName();
    validatePassword();
    validateConfirmPassword();

    if (hasErrors.value) {
        return;
    }

    router.post("/complete-profile", form, {
        preserveScroll: true,

        onSuccess: () => {
            toast.success(
                "Profile completed successfully"
            );

            setTimeout(() => {
                router.visit("/login");
            }, 1000);
        },

        onError: () => {
            toast.error(
                "Please fix validation errors"
            );
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
        linear-gradient(
            rgba(15, 23, 42, 0.85),
            rgba(15, 23, 42, 0.85)
        ),
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
    margin-bottom: 16px;
}

.field-label {
    display: block;
    margin-bottom: 8px;
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
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
}

input:focus {
    outline: none;
    border-color: #6366f1;
}

input[readonly] {
    background: #172033;
    color: white;
    font-weight: 500;
    cursor: default;
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
    margin-top: 10px;
}

button:hover {
    opacity: 0.95;
}
</style>