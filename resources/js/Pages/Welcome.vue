<template>
  <div class="welcome-container">
    <!-- Top Header Navigation -->
    <header v-if="canLogin" class="header-nav">
      <nav class="auth-nav">
        <a 
          v-if="auth.user" 
          href="/dashboard" 
          class="nav-btn border-btn"
        >
          Dashboard
        </a>
        <template v-else>
          <a 
            href="/login" 
            class="nav-btn plain-btn"
          >
            Log in
          </a>
          <a 
            v-if="canRegister" 
            href="/login" 
            class="nav-btn border-btn"
          >
            Register
          </a>
        </template>
      </nav>
    </header>

    <!-- Main Content Section -->
    <div class="main-wrapper">
      <main class="content-card">
        
        <!-- Left Side: Information & Resources -->
        <div class="info-column">
          <h1 class="title">Let's get started</h1>
          <p class="subtitle">
            Laravel has an incredibly rich ecosystem. <br />
            We suggest starting with the following.
          </p>

          <ul class="resources-list">
            <!-- Documentation -->
            <li class="resource-item first-item">
              <span class="bullet-wrapper">
                <span class="bullet-circle">
                  <span class="bullet-dot"></span>
                </span>
              </span>
              <span class="resource-text">
                Read the
                <a href="https://laravel.com/docs" target="_blank" class="resource-link">
                  <span>Documentation</span>
                  <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-icon">
                    <path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square" />
                  </svg>
                </a>
              </span>
            </li>

            <!-- Laracasts -->
            <li class="resource-item second-item">
              <span class="bullet-wrapper">
                <span class="bullet-circle">
                  <span class="bullet-dot"></span>
                </span>
              </span>
              <span class="resource-text">
                Watch video tutorials at
                <a href="https://laracasts.com" target="_blank" class="resource-link">
                  <span>Laracasts</span>
                  <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-icon">
                    <path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square" />
                  </svg>
                </a>
              </span>
            </li>
          </ul>

          <div class="cta-wrapper">
            <a href="https://cloud.laravel.com" target="_blank" class="deploy-btn">
              Deploy now
            </a>
          </div>
        </div>

        <!-- Right Side: Red Logo Graphic Block -->
        <div class="logo-column">
          <!-- Laravel Vector Wordmark / Graphic -->
          <div class="laravel-graphic">
            <svg class="laravel-logo" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L2 22h20L12 2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M12 6l-6 12h12L12 6z" fill="currentColor"/>
            </svg>
            <div class="brand-text">Laravel</div>
          </div>
          
          <!-- Background Abstract Vector Grid / Graphic -->
          <div class="abstract-grid">
            <div class="grid-line"></div>
            <div class="grid-line"></div>
            <div class="grid-line"></div>
            <div class="grid-line"></div>
          </div>
        </div>

      </main>
    </div>

    <!-- Page Footer with Version Numbers -->
    <footer class="footer-info">
      Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
    </footer>
  </div>
</template>

<script>
export default {
  name: "Welcome",
  props: {
    canLogin: {
      type: Boolean,
      default: true,
    },
    canRegister: {
      type: Boolean,
      default: true,
    },
    laravelVersion: {
      type: String,
      default: "11.x",
    },
    phpVersion: {
      type: String,
      default: "8.x",
    },
    auth: {
      type: Object,
      default: () => ({ user: null }),
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap');

/* Main resets and standard default Laravel styles */
.welcome-container {
  box-sizing: border-box;
  font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
  margin: 0;
  padding: 24px;
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  background-color: #fdfdfc;
  color: #1b1b18;
  transition: background-color 0.3s, color 0.3s;
}

/* System Dark Mode automatically handled in standard CSS */
@media (prefers-color-scheme: dark) {
  .welcome-container {
    background-color: #0a0a0a;
    color: #ededec;
  }
}

/* Header Navigation Styling */
.header-nav {
  width: 100%;
  max-width: 896px;
  display: flex;
  justify-content: flex-end;
  margin-bottom: 24px;
}

.auth-nav {
  display: flex;
  align-items: center;
  gap: 16px;
}

.nav-btn {
  font-size: 14px;
  line-height: 1.5;
  text-decoration: none;
  font-weight: 500;
  display: inline-block;
  padding: 6px 20px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.nav-btn.plain-btn {
  color: #1b1b18;
}

.nav-btn.plain-btn:hover {
  border-color: #19140035;
}

.nav-btn.border-btn {
  border: 1px solid #19140035;
  color: #1b1b18;
}

.nav-btn.border-btn:hover {
  border-color: #1915014a;
}

@media (prefers-color-scheme: dark) {
  .nav-btn.plain-btn {
    color: #ededec;
  }
  .nav-btn.plain-btn:hover {
    border-color: #3e3e3a;
  }
  .nav-btn.border-btn {
    border-color: #3e3e3a;
    color: #ededec;
  }
  .nav-btn.border-btn:hover {
    border-color: #62605b;
  }
}

/* Content Area Wrapper */
.main-wrapper {
  width: 100%;
  max-width: 896px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
}

/* Main Split Content Card */
.content-card {
  width: 100%;
  display: flex;
  flex-direction: column-reverse;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: inset 0px 0px 0px 1px rgba(26, 26, 0, 0.16);
  overflow: hidden;
}

@media (min-width: 1024px) {
  .content-card {
    flex-direction: row;
  }
}

@media (prefers-color-scheme: dark) {
  .content-card {
    background-color: #161615;
    box-shadow: inset 0px 0px 0px 1px rgba(255, 250, 237, 0.18);
  }
}

/* Left Column: Info & Resources */
.info-column {
  flex: 1;
  padding: 24px;
  box-sizing: border-box;
}

@media (min-width: 1024px) {
  .info-column {
    padding: 80px;
  }
}

.title {
  font-size: 13px;
  font-weight: 500;
  margin: 0 0 4px 0;
}

.subtitle {
  font-size: 13px;
  color: #706f6c;
  line-height: 1.5;
  margin: 0 0 16px 0;
}

@media (prefers-color-scheme: dark) {
  .subtitle {
    color: #a1a09a;
  }
}

/* Custom bullet resources list */
.resources-list {
  list-style: none;
  padding: 0;
  margin: 0 0 24px 0;
  display: flex;
  flex-direction: column;
}

.resource-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 0;
  position: relative;
}

.bullet-wrapper {
  position: relative;
  padding: 4px 0;
  background-color: #ffffff;
  z-index: 2;
}

@media (prefers-color-scheme: dark) {
  .bullet-wrapper {
    background-color: #161615;
  }
}

.bullet-circle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background-color: #fdfdfc;
  border: 1px solid #e3e3e0;
  box-shadow: 0px 0px 1px 0px rgba(0, 0, 0, 0.03), 0px 1px 2px 0px rgba(0, 0, 0, 0.06);
}

.bullet-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #dbdbd7;
}

@media (prefers-color-scheme: dark) {
  .bullet-circle {
    background-color: #161615;
    border-color: #3e3e3a;
  }
  .bullet-dot {
    background-color: #3e3e3a;
  }
}

/* The vertical dashed tree lines between bullets */
.resource-item::before {
  content: "";
  position: absolute;
  left: 6px;
  width: 1px;
  border-left: 1px solid #e3e3e0;
  z-index: 1;
}

.resource-item.first-item::before {
  top: 50%;
  bottom: 0;
}

.resource-item.second-item::before {
  top: 0;
  bottom: 50%;
}

@media (prefers-color-scheme: dark) {
  .resource-item::before {
    border-left-color: #3e3e3a;
  }
}

.resource-text {
  font-size: 13px;
  line-height: 1.5;
}

.resource-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-weight: 500;
  text-decoration: underline;
  text-underline-offset: 4px;
  color: #f53003;
  margin-left: 4px;
  transition: color 0.2s;
}

.resource-link:hover {
  color: #c52702;
}

@media (prefers-color-scheme: dark) {
  .resource-link {
    color: #ff4433;
  }
  .resource-link:hover {
    color: #ff6657;
  }
}

.arrow-icon {
  width: 10px;
  height: 10px;
  flex-shrink: 0;
}

/* Deploy now Button styling */
.cta-wrapper {
  display: flex;
}

.deploy-btn {
  display: inline-block;
  background-color: #1b1b18;
  color: #ffffff;
  padding: 6px 20px;
  border-radius: 4px;
  border: 1px solid #1b1b18;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  text-align: center;
  transition: background-color 0.2s, border-color 0.2s;
  cursor: pointer;
}

.deploy-btn:hover {
  background-color: #000000;
  border-color: #000000;
}

@media (prefers-color-scheme: dark) {
  .deploy-btn {
    background-color: #eeeeec;
    color: #1c1c1a;
    border-color: #eeeeec;
  }
  .deploy-btn:hover {
    background-color: #ffffff;
    border-color: #ffffff;
  }
}

/* Right Column: Red Logo Graphic */
.logo-column {
  flex-shrink: 0;
  width: 100%;
  aspect-ratio: 335 / 376;
  background-color: #fff2f2;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

@media (min-width: 1024px) {
  .logo-column {
    width: 438px;
    height: auto;
    aspect-ratio: auto;
  }
}

@media (prefers-color-scheme: dark) {
  .logo-column {
    background-color: #1d0002;
  }
}

.laravel-graphic {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  z-index: 2;
  color: #f53003;
  transition: transform 0.3s ease;
}

.laravel-logo {
  width: 96px;
  height: 96px;
  filter: drop-shadow(0px 8px 16px rgba(245, 48, 3, 0.2));
}

.brand-text {
  font-size: 28px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

@media (prefers-color-scheme: dark) {
  .laravel-graphic {
    color: #ff4433;
  }
  .laravel-logo {
    filter: drop-shadow(0px 8px 16px rgba(255, 68, 51, 0.3));
  }
}

/* Abstract design details */
.abstract-grid {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  padding: 40px;
  opacity: 0.15;
  pointer-events: none;
  z-index: 1;
}

.grid-line {
  height: 1px;
  background: linear-gradient(90deg, transparent, currentColor, transparent);
  color: #f53003;
}

@media (prefers-color-scheme: dark) {
  .grid-line {
    color: #ff4433;
  }
}

/* Footer Section for version info */
.footer-info {
  margin-top: 24px;
  font-size: 12px;
  color: #706f6c;
}

@media (prefers-color-scheme: dark) {
  .footer-info {
    color: #a1a09a;
  }
}
</style>
