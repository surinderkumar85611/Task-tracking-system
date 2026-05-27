<template>
  <div class="landing-page">
    <!-- HERO -->
    <section class="hero">
      <div class="overlay"></div>

      <div class="hero-content">
        <span class="badge">Enterprise Project Management System</span>

        <h1>
          Building A Professional <br />
          Enterprise Workflow Platform
        </h1>

        <p>
          Currently developing an enterprise-level task and project
          management system using Laravel + Vue + Inertia.
          This platform is focused on scalable architecture,
          professional workflows, team collaboration, realtime features,
          and enterprise productivity.
        </p>

        <div class="hero-buttons">
          <Link href="/login" class="primary-btn">
            Login
          </Link>

          <Link href="/register" class="secondary-btn">
            Get Started
          </Link>
        </div>
      </div>
    </section>

    <!-- CURRENT WORK -->
    <section class="section">
      <div class="section-header">
        <h2>What We Are Working On</h2>
        <p>Current development modules and architecture planning.</p>
      </div>

      <div class="grid">
        <div class="card">
          <div class="icon">📁</div>
          <h3>Project Management</h3>
          <p>
            Enterprise project structure with milestones,
            statuses, deadlines, and tracking.
          </p>
        </div>

        <div class="card">
          <div class="icon">✅</div>
          <h3>Task System</h3>
          <p>
            Professional task workflow with priorities,
            assignments, activity logs, and progress tracking.
          </p>
        </div>

        <div class="card">
          <div class="icon">👥</div>
          <h3>Team Collaboration</h3>
          <p>
            Team management, permissions, role access,
            realtime updates, and collaboration tools.
          </p>
        </div>

        <div class="card">
          <div class="icon">📊</div>
          <h3>Analytics Dashboard</h3>
          <p>
            Productivity analytics, reports, charts,
            project insights, and team performance.
          </p>
        </div>
      </div>
    </section>

    <!-- TECH STACK -->
    <section class="stack-section">
      <div class="section-header light">
        <h2>Current Tech Stack</h2>
        <p>Modern enterprise technologies being used in development.</p>
      </div>

      <div class="stack-grid">
        <div class="stack-card">Laravel</div>
        <div class="stack-card">Vue.js</div>
        <div class="stack-card">Inertia.js</div>
        <div class="stack-card">MySQL</div>
        <div class="stack-card">REST API</div>
        <div class="stack-card">Vite</div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.landing-page {
  font-family: Inter, sans-serif;
  background: #f8fafc;
  color: #0f172a;
}

/* HERO */
.hero {
  min-height: 100vh;
  position: relative;
  display: flex;
  align-items: center;
  padding: 80px;
  background:
    linear-gradient(rgba(15,23,42,.82), rgba(15,23,42,.82)),
    url('https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=2070');
  background-size: cover;
  background-position: center;
}

.hero-content {
  max-width: 760px;
  color: white;
  position: relative;
  z-index: 2;
}

.badge {
  display: inline-block;
  padding: 10px 18px;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 50px;
  margin-bottom: 26px;
  font-size: 14px;
}

.hero h1 {
  font-size: 72px;
  line-height: 1.15;
  margin-bottom: 26px;
}

.hero p {
  font-size: 18px;
  line-height: 1.9;
  color: rgba(255,255,255,.85);
  margin-bottom: 36px;
}

.hero-buttons {
  display: flex;
  gap: 18px;
}

.primary-btn,
.secondary-btn {
  height: 52px;
  padding: 0 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  transition: .3s ease;
}

.primary-btn {
  background: #4f46e5;
  color: white;
}

.primary-btn:hover {
  background: #4338ca;
}

.secondary-btn {
  border: 1px solid rgba(255,255,255,.2);
  color: white;
  background: rgba(255,255,255,.05);
}

.secondary-btn:hover {
  background: rgba(255,255,255,.1);
}

/* SECTION */
.section {
  padding: 100px 80px;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;
}

.section-header h2 {
  font-size: 42px;
  margin-bottom: 14px;
}

.section-header p {
  color: #64748b;
  font-size: 17px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 28px;
}

.card {
  background: white;
  padding: 34px;
  border-radius: 22px;
  box-shadow: 0 10px 30px rgba(0,0,0,.06);
  transition: .3s ease;
}

.card:hover {
  transform: translateY(-6px);
}

.icon {
  width: 62px;
  height: 62px;
  border-radius: 16px;
  background: #eef2ff;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 28px;
  margin-bottom: 22px;
}

.card h3 {
  font-size: 22px;
  margin-bottom: 14px;
}

.card p {
  line-height: 1.8;
  color: #64748b;
}

/* STACK */
.stack-section {
  background: #0f172a;
  padding: 100px 80px;
}

.light h2 {
  color: white;
}

.light p {
  color: rgba(255,255,255,.7);
}

.stack-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 24px;
  margin-top: 50px;
}

.stack-card {
  height: 90px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  font-weight: 600;
}

@media (max-width: 900px) {
  .hero,
  .section,
  .stack-section {
    padding: 60px 24px;
  }

  .hero h1 {
    font-size: 48px;
  }

  .hero-buttons {
    flex-direction: column;
  }
}
</style>
