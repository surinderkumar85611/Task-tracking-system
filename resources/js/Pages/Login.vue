<template>
  <div class="auth-page">
    <div class="background-orb"></div>
    <div class="background-orb second"></div>
    
    <section class="wrapper" :class="{ active: isLogin }">

      <!-- SIGNUP -->
      <div class="form signup">
        <header @click="isLogin = false">Signup</header>

        <form @submit.prevent="handleSignup">
          <input v-model="signup.name" type="text" placeholder="Full name" required />
          <input v-model="signup.email" type="email" placeholder="Email address" required />
          <input v-model="signup.password" type="password" placeholder="Password" required />

          <div class="checkbox">
            <input type="checkbox" v-model="signup.terms" id="signupCheck" />
            <label for="signupCheck">I accept all terms & conditions</label>
          </div>

          <input type="submit" value="Signup" />
        </form>
      </div>

      <!-- LOGIN -->
      <div class="form login">
        <header @click="isLogin = true">Login</header>

        <form @submit.prevent="handleLogin">
          <input v-model="login.email" type="email" placeholder="Email address" required />
          <input v-model="login.password" type="password" placeholder="Password" required />

          <div class="forgot-pass">
            <a href="#">Forgot password?</a>
          </div>

          <input type="submit" value="Login" />
        </form>
      </div>

    </section>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "AuthToggle",

  data() {
    return {
      isLogin: false,

      login: {
        email: "",
        password: "",
      },

      signup: {
        name: "",
        email: "",
        password: "",
        terms: false,
      },
    };
  },

  methods: {
    async handleLogin() {
      try {
        await axios.post("/loginsave", this.login);
        this.$router.push("/dashboard");
      } catch (err) {
        console.log(err.response?.data);
      }
    },

    async handleSignup() {
      try {
        await axios.post("/signsave", this.signup);
        this.isLogin = true; // switch to login after signup
      } catch (err) {
        console.log(err.response?.data);
      }
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

.auth-page {
  box-sizing: border-box;
  font-family: 'Outfit', sans-serif;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100%;
  background: radial-gradient(circle at center, #100d23 0%, #06050b 100%);
  position: relative;
  overflow: hidden;
}

.background-orb {
  position: absolute;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(124, 77, 255, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
  top: -100px;
  left: -100px;
  z-index: 1;
  filter: blur(40px);
  animation: float 8s ease-in-out infinite alternate;
}

.background-orb.second {
  background: radial-gradient(circle, rgba(213, 0, 249, 0.12) 0%, rgba(0, 0, 0, 0) 70%);
  bottom: -150px;
  right: -100px;
  top: auto;
  left: auto;
  animation: float 10s ease-in-out infinite alternate-reverse;
}

@keyframes float {
  0% { transform: translateY(0) scale(1); }
  100% { transform: translateY(30px) scale(1.1); }
}

.wrapper {
  position: relative;
  max-width: 400px;
  width: 100%;
  height: 560px;
  background: rgba(22, 19, 39, 0.65);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  overflow: hidden;
  z-index: 2;
}

.form {
  position: absolute;
  width: 100%;
  padding: 40px;
  box-sizing: border-box;
  transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* SIGNUP form state styling */
.form.signup {
  top: 15px;
  opacity: 1;
  z-index: 5;
  pointer-events: auto;
}

/* LOGIN form state styling */
.form.login {
  top: 480px; /* Shifted down - only header visible */
  opacity: 0.9;
  z-index: 2;
}

.form.login header {
  font-size: 20px;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 0;
  font-weight: 500;
}

.form.login header:hover {
  color: #7c4dff;
  text-shadow: 0 0 10px rgba(124, 77, 255, 0.4);
}

.form.signup header {
  font-size: 32px;
  font-weight: 700;
  color: #fff;
  text-align: center;
  margin-bottom: 30px;
  cursor: default;
}

/* ACTIVE STATE (When Login is Active) */
.wrapper.active .form.signup {
  top: -380px; /* Shifted up - only header visible */
  z-index: 2;
}

.wrapper.active .form.signup header {
  font-size: 20px;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 360px; /* Bring the header down into view */
  font-weight: 500;
}

.wrapper.active .form.signup header:hover {
  color: #7c4dff;
  text-shadow: 0 0 10px rgba(124, 77, 255, 0.4);
}

.wrapper.active .form.signup form {
  opacity: 0;
  pointer-events: none;
}

.wrapper.active .form.login {
  top: 15px; /* Slides up into focus */
  opacity: 1;
  z-index: 5;
  pointer-events: auto;
}

.wrapper.active .form.login header {
  font-size: 32px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 30px;
  cursor: default;
}

.wrapper.active .form.login header:hover {
  color: #fff;
  text-shadow: none;
}

/* Inputs, Labels, and Action Elements */
form {
  display: flex;
  flex-direction: column;
  transition: opacity 0.4s ease;
}

input[type="text"],
input[type="email"],
input[type="password"] {
  width: 100%;
  height: 50px;
  outline: none;
  padding: 0 16px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: #fff;
  font-size: 15px;
  margin-bottom: 20px;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
  border-color: #7c4dff;
  background: rgba(255, 255, 255, 0.06);
  box-shadow: 0 0 12px rgba(124, 77, 255, 0.25);
}

input::placeholder {
  color: rgba(255, 255, 255, 0.35);
}

.checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 22px;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.7);
  user-select: none;
}

.checkbox input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #7c4dff;
  cursor: pointer;
}

.checkbox label {
  cursor: pointer;
}

.forgot-pass {
  margin-bottom: 22px;
}

.forgot-pass a {
  color: #7c4dff;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s ease;
}

.forgot-pass a:hover {
  color: #d500f9;
  text-decoration: underline;
}

input[type="submit"] {
  width: 100%;
  height: 50px;
  border: none;
  outline: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #7c4dff 0%, #d500f9 100%);
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(124, 77, 255, 0.35);
}

input[type="submit"]:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(124, 77, 255, 0.5);
}

input[type="submit"]:active {
  transform: translateY(0);
}
</style>