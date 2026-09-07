<template>

    <Head title="Super Admin Dashboard" />

    <div class="dashboard" :class="isDark ? 'theme-dark' : 'theme-light'">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div>
                <div class="logo">
                    <div class="logo-icon">S</div>
                    <div>
                        <h2>Super Admin</h2>
                        <span>Management Panel</span>
                    </div>
                </div>

                <nav class="menu">
                    <a href="#" class="active">📊 Dashboard</a>
                    <a href="#">👤 Administrators</a>
                    <a href="#">🏢 Workspaces</a>
                    <a href="#">📁 Projects</a>
                    <a href="#">👥 Teams</a>
                    <a href="#">⚙️ Settings</a>
                </nav>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </aside>

        <main class="main-content">

            <!-- TOPBAR -->
            <div class="topbar">
                <div class="topbar-greeting">
                    <h2>
                        Welcome back, Super Admin <span class="wave">👋</span>
                    </h2>
                    <p>
                        You have <strong>{{ pendingProjectsCount }}</strong> pending projects across <strong>{{
                            stats.totalTeams }}</strong> teams.
                    </p>
                </div>

                <div class="topbar-icons">

                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.6" />
                            <path d="M17 17L13.5 13.5" stroke="currentColor" stroke-width="1.6"
                                stroke-linecap="round" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Search projects, teams, admins..."
                            class="search-box" />
                    </div>

                    <!-- WORKSPACES DROPDOWN -->
                    <div class="workspace-dropdown-container" ref="workspaceRef">
                        <button class="workspace-label" @click.stop="showWorkspaceDropdown = !showWorkspaceDropdown">
                            <span class="workspace-dot"></span>
                            {{ stats.workspaces }} Workspaces
                            <svg class="workspace-caret" :class="{ open: showWorkspaceDropdown }" viewBox="0 0 12 8"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.5 1.5L6 6L10.5 1.5" stroke="currentColor" stroke-width="1.6"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div v-if="showWorkspaceDropdown" class="workspace-dropdown-panel">
                            <div class="workspace-dropdown-header">All Workspaces</div>
                            <div v-for="name in workspaceNames" :key="name" class="workspace-dropdown-item">
                                <span class="workspace-dot"></span>{{ name }}
                            </div>
                            <div v-if="!workspaceNames.length" class="empty-state-inline">No workspaces found.</div>
                        </div>
                    </div>

                    <button class="theme-btn" @click="isDark = !isDark" aria-label="Toggle theme">
                        {{ isDark ? '☀️' : '🌙' }}
                    </button>

                    <!-- NOTIFICATIONS -->
                    <div class="notification-bell-container" ref="bellRef">
                        <button class="icon-btn" @click.stop="showBellDropdown = !showBellDropdown"
                            aria-label="Notifications">
                            🔔
                            <span v-if="overdueProjects.length" class="bell-alert-green-dot">{{ overdueProjects.length
                            }}</span>
                        </button>

                        <div v-if="showBellDropdown" class="notification-dropdown-panel">
                            <div class="notification-dropdown-header">
                                <h3>Overdue Project Alerts</h3>
                            </div>

                            <div class="notification-dropdown-body">
                                <div class="notification-scroll-area">
                                    <div v-for="project in overdueProjects" :key="'overdue-' + project.id"
                                        class="notification-alert-item urgent">
                                        <div class="alert-item-indicator urgent-indicator">⚠️</div>
                                        <div class="alert-item-details">
                                            <p class="alert-task-title">{{ project.name }}</p>
                                            <p class="alert-task-time-left" style="color: var(--c-red)">
                                                Was due {{ project.deadline }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="!overdueProjects.length" class="notification-empty-state">
                                    🎉 No overdue projects right now.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PROFILE -->
                    <div class="profile-container" ref="profileRef">
                        <div class="avatar avatar-fallback" @click.stop="showProfileMenu = !showProfileMenu">SA</div>

                        <div v-if="showProfileMenu" class="profile-dropdown">
                            <button @click="logout">Logout</button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="content-wrapper">

                <!-- STATS -->
                <section class="stats-grid">

                    <div class="stat-card assigned-tasks-card">
                        <div class="stat-icon-badge">📁</div>
                        <span class="stat-label">Total Projects</span>
                        <h2 class="stat-value">{{ stats.projects }}</h2>
                        <small class="stat-subtitle">Across all workspaces</small>
                    </div>

                    <div class="stat-card team-projects-card">
                        <div class="stat-icon-badge">🏢</div>
                        <span class="stat-label">Total Teams</span>
                        <h2 class="stat-value">{{ stats.totalTeams }}</h2>
                        <small class="stat-subtitle">{{ stats.teamLeaders }} team leaders</small>
                    </div>

                <span class="rep-status">
                  <span class="status-dot-online"></span>
                  {{ team.members_count ?? 0 }} member{{ (team.members_count ?? 0) === 1 ? '' : 's' }}
                </span>
              </div>

              <div v-if="!filteredTeams.length" class="empty-state-inline">No team leaders found.</div>
            </div>
          </section>

          <!-- RECENT ACTIVITY -->
          <section class="dashboard-card">
            <div class="card-header"><h2>Recent Activity</h2></div>

            <div class="leads-table">
              <div class="leads-table-head">
                <span>Activity</span>
                <span>Project</span>
                <span>By</span>
              </div>

              <div v-for="activity in recentActivity" :key="activity.id" class="leads-row">
                <div class="lead-identity">
                  <span class="lead-dot" :class="activityDotClass(activity.type)"></span>
                  <span class="lead-title" :title="activity.message">{{ activity.message }}</span>
                </div>
                    <div class="stat-card completed-tasks-card">
                        <div class="stat-icon-badge">✅</div>
                        <span class="stat-label">Completed Projects</span>
                        <h2 class="stat-value">{{ completedProjectsCount }}</h2>
                        <small class="stat-subtitle">Fully finished projects</small>
                    </div>

                    <div class="stat-card pending-tasks-card">
                        <div class="stat-icon-badge">⏳</div>
                        <span class="stat-label">Pending Projects</span>
                        <h2 class="stat-value">{{ pendingProjectsCount }}</h2>
                        <small class="stat-subtitle">Still in progress</small>
                    </div>

                    <div class="stat-card completion-rate-card">
                        <div class="stat-icon-badge">📈</div>
                        <span class="stat-label">Completion Rate</span>
                        <h2 class="stat-value">{{ completionRateComputed }}%</h2>
                        <small class="stat-subtitle">Overall project completion</small>
                    </div>

                </section>

                <!-- PROJECTS BOARD + COMPLETION DONUT + TODAY'S OVERVIEW -->
                <div class="top-row-grid">

                    <div class="dashboard-card main-panel">
                        <div class="card-header kanban-card-header">
                            <h2>All Projects</h2>
                            <div class="view-toggle">
                                <button :class="{ active: projectView === 'board' }" @click="projectView = 'board'">🗂️
                                    Board</button>
                                <button :class="{ active: projectView === 'list' }" @click="projectView = 'list'">📋
                                    List</button>
                            </div>
                        </div>

                        <!-- BOARD VIEW -->
                        <div class="kanban-board" v-if="projectView === 'board'">
                            <div v-for="column in kanbanColumns" :key="column.key" class="kanban-column">
                                <div class="kanban-column-header">
                                    <span class="kanban-column-dot" :class="column.key"></span>
                                    <h3>{{ column.label }}</h3>
                                    <span class="kanban-count">{{ column.projects.length }}</span>
                                </div>

                                <div class="kanban-column-body"
                                    :class="{ 'is-drop-target': dragOverColumn === column.key }"
                                    @dragover.prevent="onDragOver(column.key)" @dragleave="onDragLeave(column.key)"
                                    @drop="onDrop(column.key)">
                                    <div v-for="project in column.projects" :key="project.id" class="kanban-task-card"
                                        :class="[column.key, { 'is-dragging': draggedProjectId === project.id }]"
                                        draggable="true" @dragstart="onDragStart(project)" @dragend="onDragEnd">
                                        <div class="kanban-task-top">
                                            <h4 v-html="highlightMatch(project.name)"></h4>
                                            <span class="priority-badge">{{ project.progress }}%</span>
                                        </div>

                                        <p class="task-row-project" v-if="project.team_leader_name">
                                            👤 <span v-html="highlightMatch(project.team_leader_name)"></span>
                                        </p>

                                        <small class="due-date" v-if="project.deadline">Due: {{ project.deadline
                                        }}</small>
                                    </div>

                                    <div v-if="!column.projects.length" class="kanban-empty"
                                        :class="{ 'is-drop-target': dragOverColumn === column.key }">
                                        {{ dragOverColumn === column.key ? '⬇️ Drop to move here' : '📭 Nothing here — drag a project over' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- LIST VIEW -->
                        <div class="task-list" v-else-if="filteredProjects.length">
                            <div v-for="project in filteredProjects" :key="project.id" class="task-row-card">
                                <div class="task-status-dot" :class="progressClass(project.progress)"></div>

                                <div class="task-row-main">
                                    <div class="task-row-top">
                                        <h3 v-html="highlightMatch(project.name)"></h3>
                                        <span class="priority-badge">{{ project.progress }}%</span>
                                    </div>

                                    <p class="task-row-project" v-if="project.team_leader_name">
                                        👤 <span v-html="highlightMatch(project.team_leader_name)"></span>
                                    </p>

                                    <div class="task-row-bottom">
                                        <span class="status-pill" :class="progressClass(project.progress)">
                                            {{ progressLabel(project.progress) }}
                                        </span>
                                        <small class="due-date" v-if="project.deadline">Due: {{ project.deadline
                                        }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="empty-state-inline">🎉 No projects match your search.</div>
                    </div>

              <div v-if="!recentActivity.length" class="empty-state-inline">No recent activity yet.</div>
            </div>
          </section>
                    <!-- COMPLETION DONUT -->
                    <div class="dashboard-card donut-card">
                        <div class="card-header">
                            <h2>Completion Rate</h2>
                        </div>

                        <div class="donut-wrap">
                            <svg viewBox="0 0 120 120" class="donut-svg">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--border-deep)"
                                    stroke-width="12" />
                                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--c-green)" stroke-width="12"
                                    stroke-linecap="round" :stroke-dasharray="2 * Math.PI * 50"
                                    :stroke-dashoffset="(2 * Math.PI * 50) * (1 - completionRateComputed / 100)"
                                    transform="rotate(-90 60 60)" />
                            </svg>
                            <div class="donut-center">
                                <strong>{{ completionRateComputed }}%</strong>
                                <span>Completed</span>
                            </div>
                        </div>

                        <div class="donut-footer">
                            <div class="donut-stat">
                                <strong>{{ completedProjectsCount }}</strong>
                                <span>Done</span>
                            </div>
                            <div class="donut-stat">
                                <strong>{{ inProgressCount }}</strong>
                                <span>Active</span>
                            </div>
                            <div class="donut-stat">
                                <strong>{{ pendingProjectsCount }}</strong>
                                <span>Pending</span>
                            </div>
                        </div>

                        <div class="donut-breakdown">
                            <div v-for="col in allKanbanColumns" :key="col.key" class="donut-breakdown-row">
                                <span class="kanban-column-dot" :class="col.key"></span>
                                <span class="donut-breakdown-label">{{ col.label }}</span>
                                <strong>{{ col.projects.length }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- TODAY'S OVERVIEW -->
                    <div class="dashboard-card today-activity-card">
                        <div class="card-header">
                            <h2>Today's Overview</h2>
                        </div>

                        <ul class="activity-stat-list">
                            <li>
                                <span class="ai-icon">📅</span>
                                <span class="ai-label">Due Today</span>
                                <span class="ai-value">{{ dueToday }}</span>
                            </li>
                            <li>
                                <span class="ai-icon">🔥</span>
                                <span class="ai-label">Overdue</span>
                                <span class="ai-value">{{ overdueProjects.length }}</span>
                            </li>
                            <li>
                                <span class="ai-icon">⏰</span>
                                <span class="ai-label">Upcoming</span>
                                <span class="ai-value">{{ upcomingCount }}</span>
                            </li>
                            <li>
                                <span class="ai-icon">📈</span>
                                <span class="ai-label">Progress</span>
                                <span class="ai-value">{{ completionRateComputed }}%</span>
                            </li>
                        </ul>

                        <div class="daily-goal">
                            <div class="daily-goal-head">
                                <span>Org-wide Progress</span>
                                <strong>{{ completionRateComputed }}%</strong>
                            </div>
                            <div class="daily-goal-bar">
                                <div class="daily-goal-fill" :style="{ width: completionRateComputed + '%' }"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- PROJECTS ASSIGNED PER MONTH -->
                <section class="dashboard-card monthly-projects-card">
                    <div class="card-header">
                        <h2>Projects Assigned Per Month</h2>
                    </div>

                    <div class="bar-chart">
                        <div v-for="bucket in monthlyProjects" :key="bucket.label + bucket.year" class="bar-col">
                            <span class="bar-value">{{ bucket.count }}</span>
                            <div class="bar-track">
                                <div class="bar-fill" :class="{ zero: bucket.count === 0 }"
                                    :style="{ height: (bucket.count / maxMonthlyProjectCount) * 100 + '%' }"></div>
                            </div>
                            <span class="bar-label">{{ bucket.label }}</span>
                        </div>
                    </div>
                </section>

                <!-- TEAM LEADERS + RECENT ACTIVITY -->
                <div class="mid-grid">

                    <!-- TEAM LEADERS — ranked list, click to open team detail -->
                    <section class="dashboard-card">
                        <div class="card-header">
                            <h2>Team Leaders</h2>
                        </div>

                        <div class="rep-list">
                            <div v-for="(team, index) in filteredTeams" :key="team.id" class="rep-row"
                                @click="openTeam(team.id)">
                                <span class="rep-rank">{{ index + 1 }}</span>

                                <div class="rep-avatar">{{ team.name.charAt(0).toUpperCase() }}</div>

                                <div class="rep-info">
                                    <strong>{{ team.name }}</strong>
                                    <span>
                                        {{ team.workspace_name }} ·
                                        {{ team.projects_count ?? 0 }} project{{ (team.projects_count ?? 0) === 1 ? '' :
                                            's' }} ·
                                        {{ team.completed_count ?? 0 }} completed
                                    </span>
                                </div>

                                <span class="rep-status">
                                    <span class="status-dot-online"></span>
                                    {{ team.completion_rate }}%
                                </span>
                            </div>

                            <div v-if="!filteredTeams.length" class="empty-state-inline">No team leaders found.</div>
                        </div>
                    </section>

                    <!-- RECENT ACTIVITY -->
                    <section class="dashboard-card">
                        <div class="card-header">
                            <h2>Recent Activity</h2>
                        </div>

                        <div class="leads-table">
                            <div class="leads-table-head">
                                <span>Activity</span>
                                <span>Project</span>
                                <span>By</span>
                            </div>

                            <div v-for="activity in (recentActivity || [])" :key="activity.id" class="leads-row">
                                <div class="lead-identity">
                                    <span class="lead-dot" :class="activityDotClass(activity.type)"></span>
                                    <span class="lead-title" :title="activity.message">{{ activity.message }}</span>
                                </div>

                                <span class="lead-project">{{ activity.project_name || '—' }}</span>

                                <span class="status-pill">{{ activity.actor_name }}</span>
                            </div>

                            <div v-if="!(recentActivity || []).length" class="empty-state-inline">No recent activity
                                yet.</div>
                        </div>
                    </section>

                </div>

                <!-- TEAM PROJECTS + UPCOMING DEADLINES -->
                <div class="mid-grid">

                    <section class="dashboard-card">
                        <div class="card-header">
                            <h2>Team Projects</h2>
                        </div>

                        <div class="team-projects-table" v-if="filteredProjects.length">
                            <div class="team-projects-head">
                                <span>Project</span>
                                <span>Due Date</span>
                                <span>Team Leader</span>
                                <span>Progress</span>
                            </div>

                            <div v-for="project in filteredProjects" :key="project.id" class="team-projects-row">
                                <div class="tp-name">
                                    <span class="lead-dot" :class="progressClass(project.progress)"></span>
                                    <span v-html="highlightMatch(project.name)"></span>
                                </div>

                                <span class="tp-date">{{ project.deadline || 'No deadline' }}</span>

                                <div class="tp-leader">
                                    <div class="rep-avatar small">{{ (project.team_leader_name ||
                                        '—').charAt(0).toUpperCase() }}</div>
                                    <span v-html="highlightMatch(project.team_leader_name || '—')"></span>
                                </div>

                                <div class="tp-progress">
                                    <div class="mini-progress">
                                        <div class="mini-progress-fill"
                                            :style="{ width: (project.progress || 0) + '%' }"></div>
                                    </div>
                                    <span>{{ project.progress || 0 }}%</span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="empty-state-inline">No projects found.</div>
                    </section>

                    <!-- UPCOMING DEADLINES -->
                    <section class="dashboard-card">
                        <div class="card-header">
                            <h2>Upcoming Deadlines</h2>
                        </div>

                        <div class="timeline-list">
                            <div v-for="project in upcomingDeadlineProjects" :key="project.id" class="timeline-item">
                                <span class="timeline-dot" :class="progressClass(project.progress)"></span>

                                <div class="timeline-content">
                                    <div class="timeline-top">
                                        <span class="timeline-date">{{ project.deadline || 'No deadline' }}</span>
                                        <span class="timeline-pill" :class="dueUrgencyClass(project)">{{
                                            dueLabel(project) }}</span>
                                    </div>
                                    <strong>{{ project.name }}</strong>
                                    <small v-if="project.team_leader_name">👤 {{ project.team_leader_name }}</small>
                                    <div class="timeline-progress">
                                        <div class="timeline-progress-fill"
                                            :style="{ width: (project.progress || 0) + '%' }"></div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!upcomingDeadlineProjects.length" class="empty-state-inline">No upcoming
                                deadlines.</div>
                        </div>
                    </section>

                </div>

                <!-- ADMINISTRATORS -->
                <section class="dashboard-card">
                    <div class="card-header kanban-card-header">
                        <h2>Administrators</h2>
                        <button class="view-toggle-btn" @click="showCreateModal = true">+ Create User</button>
                    </div>

                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="admin in filteredAdmins" :key="admin.id">
                                    <td>
                                        <div class="admin-user-cell">
                                            <div class="rep-avatar small">{{ admin.name.charAt(0) }}</div>
                                            <strong>{{ admin.name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ admin.email }}</td>
                                    <td>{{ new Date(admin.created_at).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="!filteredAdmins.length" class="empty-state-inline">No administrators match your
                            search.</div>
                    </div>
                </section>

            </div>
        </main>

        <!-- TEAM DETAIL MODAL -->
        <div v-if="selectedTeam" class="modal-overlay" @click.self="closeTeam">
            <div class="modal team-modal">
                <div class="modal-header">
                    <div>
                        <h2>{{ selectedTeam.name }}</h2>
                        <p>{{ selectedTeam.workspace_name }} · Team Leader</p>
                    </div>
                    <button class="close-btn" @click="closeTeam">✕</button>
                </div>

                <div class="team-modal-stats">
                    <div><strong>{{ selectedTeam.completion_rate }}%</strong><span>Completion</span></div>
                    <div><strong>{{ selectedTeam.projects_count }}</strong><span>Projects</span></div>
                    <div><strong>{{ selectedTeam.members_count }}</strong><span>Members</span></div>
                </div>

                <h4 class="modal-subhead">Team Members</h4>
                <div class="modal-member-list">
                    <div v-for="m in selectedTeam.members" :key="m.id" class="modal-member-row">
                        <div class="rep-avatar small">{{ m.name.charAt(0) }}</div>
                        <div class="modal-member-info"><strong>{{ m.name }}</strong><span>{{ m.department || m.role
                        }}</span></div>
                        <button class="remove-btn" @click="removeMember(m.id)">Remove</button>
                    </div>
                  </td>
                  <td>{{ admin.email }}</td>
                  <td>{{ new Date(admin.created_at).toLocaleDateString() }}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="!filteredAdmins.length" class="empty-state-inline">No administrators match your search.</div>
          </div>
        </section>

      </div>
    </main>

    <!-- TEAM DETAIL MODAL -->
    <div v-if="selectedTeam" class="modal-overlay" @click.self="closeTeam">
      <div class="modal team-modal">
        <div class="modal-header">
          <div>
            <h2>{{ selectedTeam.name }}</h2>
            <p>{{ selectedTeam.workspace_name }} · Team Leader</p>
          </div>
          <button class="close-btn" @click="closeTeam">✕</button>
        </div>

        <div class="team-modal-stats">
          <div><strong>{{ selectedTeam.completion_rate }}%</strong><span>Completion</span></div>
          <div><strong>{{ selectedTeam.projects_count }}</strong><span>Projects</span></div>
          <div><strong>{{ (selectedTeam.members || []).length }}</strong><span>Members</span></div>
        </div>
                    <div v-if="!selectedTeam.members.length" class="empty-state-inline">No members in this team yet.
                    </div>
                </div>

                <h4 class="modal-subhead">Add Member</h4>
                <div class="add-member-row">
                    <select v-model="memberToAdd">
                        <option value="" disabled>Select a member to add</option>
                        <option v-for="m in candidateMembers" :key="m.id" :value="m.id">{{ m.name }} ({{ m.email }})
                        </option>
                    </select>
                    <button class="save-btn small" :disabled="!memberToAdd" @click="addMember">Add</button>
                </div>

                <h4 class="modal-subhead">Team Projects</h4>
                <div class="modal-project-list">
                    <div v-for="p in selectedTeam.projects" :key="p.id" class="modal-project-row">
                        <span class="lead-dot" :class="progressClass(p.progress)"></span>
                        <span class="modal-project-name">{{ p.name }}</span>
                        <span class="status-pill" :class="progressClass(p.progress)">{{ p.progress }}%</span>
                    </div>
                    <div v-if="!selectedTeam.projects.length" class="empty-state-inline">No projects assigned to this
                        team.</div>
                </div>
            </div>
        </div>
        <div v-if="!candidateMembers.length" class="empty-state-inline">No other members available to add.</div>

        <!-- CREATE ADMIN MODAL -->
        <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <h2>Create User</h2>
                        <p>Create Administrator or Team Leader</p>
                    </div>
                    <button class="close-btn" @click="showCreateModal = false">✕</button>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select v-model="form.role">
                        <option value="ADMIN">Administrator</option>
                        <option value="TL">Team Leader</option>
                    </select>
                </div>
                <div class="form-group"><label>Name</label><input v-model="form.name" placeholder="John Doe"></div>
                <div class="form-group"><label>Email</label><input v-model="form.email" type="email"></div>
                <div class="form-group"><label>Password</label><input v-model="form.password" type="password"></div>
                <div class="form-group"><label>Confirm Password</label><input v-model="form.password_confirmation"
                        type="password"></div>

                <div class="modal-actions">
                    <button class="cancel-btn" @click="showCreateModal = false">Cancel</button>
                    <button class="save-btn" @click="createAdmin">Create User</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    stats: { type: Object, required: true },
    admins: { type: Array, default: () => [] },
    teams: { type: Array, default: () => [] },
    allProjects: { type: Array, default: () => [] },
    allMembers: { type: Array, default: () => [] },
    recentActivity: { type: Array, default: () => [] },
});

const isDark = ref(true);
const search = ref("");
const showProfileMenu = ref(false);
const showBellDropdown = ref(false);
const showWorkspaceDropdown = ref(false);
const showCreateModal = ref(false);
const projectView = ref("board");
const selectedTeamId = ref(null);
const memberToAdd = ref("");

/* ---------------- Local, mutable copy of projects ----------------
   `allProjects` is a prop and shouldn't be mutated directly. Kanban
   drag-and-drop needs to optimistically update a project's progress
   the instant it's dropped, so we keep a local working copy and sync
   it back from the prop whenever the server sends fresh data (e.g.
   after any Inertia visit/reload). */
const localProjects = ref((props.allProjects || []).map(p => ({ ...p })));

watch(
    () => props.allProjects,
    (val) => {
        localProjects.value = (val || []).map(p => ({ ...p }));
    }
);

/* ---------------- Local, mutable copy of teams (with members) ----------------
   Same rationale as localProjects: adding/removing a team member needs to
   update the member count on the "Team Leaders" list and the modal's member
   list instantly, without waiting on a full round trip. We keep a deep-ish
   local copy (each team's `members` array is cloned too) and resync it
   whenever Inertia refreshes the `teams` prop. */
const localTeams = ref((props.teams || []).map(t => ({ ...t, members: (t.members || []).map(m => ({ ...m })) })));

watch(
  () => props.teams,
  (val) => {
    localTeams.value = (val || []).map(t => ({ ...t, members: (t.members || []).map(m => ({ ...m })) }));
  }
);

/* ---------------- Local, mutable copy of all members ----------------
   Lets us optimistically flip a member's `assigned_to` the instant they're
   added to / removed from a team, so the "Add Member" dropdown (which
   excludes members already on the team) updates immediately. */
const localMembers = ref((props.allMembers || []).map(m => ({ ...m })));

watch(
  () => props.allMembers,
  (val) => {
    localMembers.value = (val || []).map(m => ({ ...m }));
  }
);

/* ---------------- Recent activity (local, optimistic) ----------------
   IMPORTANT FIX: the template below reads a bare `recentActivity`
   identifier (not `props.recentActivity`). Since `defineProps` was never
   destructured, that identifier previously didn't exist anywhere in this
   component's scope — so the "Recent Activity" panel always rendered its
   empty state, no matter what the backend sent. Declaring it here both
   fixes that bug and gives us a place to push optimistic entries (e.g.
   "Moved X from Team A to Team B") the instant a member is added/removed,
   ahead of the server's own activity-log entry arriving on reload. */
const recentActivity = ref((props.recentActivity || []).map(a => ({ ...a })));

watch(
  () => props.recentActivity,
  (val) => {
    recentActivity.value = (val || []).map(a => ({ ...a }));
  }
);

let tempActivityId = -1;
const pushLocalActivity = ({ type, message, project_name = null, actor_name = "Super Admin" }) => {
  recentActivity.value = [
    { id: tempActivityId--, type, message, project_name, actor_name },
    ...recentActivity.value,
  ];
};

/* ---------------- Search highlighting ---------------- */
const escapeHtml = (value) => String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
    const safeText = escapeHtml(text);
    const term = search.value.trim();
    if (!term) return safeText;
    const regex = new RegExp(`(${escapeRegExp(term)})`, "ig");
    return safeText.replace(regex, '<mark class="search-highlight">$1</mark>');
};

/* ---------------- Filters ---------------- */
const filteredProjects = computed(() => {
    if (!search.value) return localProjects.value;
    const term = search.value.toLowerCase();
    return localProjects.value.filter(p =>
        p.name?.toLowerCase().includes(term) ||
        p.team_leader_name?.toLowerCase().includes(term) ||
        p.workspace_name?.toLowerCase().includes(term)
    );
});

/* ---------------- Team leader project stats ----------------
   Builds { <leaderKey>: { assigned, completed } } straight from
   localProjects, keyed by team_leader_id (preferred) or team_leader_name
   (fallback), so we don't depend on the `teams` prop being populated. */
const teamLeaderStats = computed(() => {
    const map = {};
    localProjects.value.forEach(p => {
        const key = p.team_leader_id ?? p.team_leader_name;
        if (key === undefined || key === null || key === "") return;
        if (!map[key]) map[key] = { assigned: 0, completed: 0 };
        map[key].assigned++;
        if ((p.progress || 0) >= 100) map[key].completed++;
    });
    return map;
});

/* ---------------- Team leaders list ----------------
   `role: 'TL'` lives on the Member model, not on admins (admins are
   just Users, which have no role column) — so team leaders always
   come from the backend's `teams` prop (built from Member::where('role','TL')
   in SuperAdminController@index), never derived from `admins` here.
   `teamLeaderStats` is kept as a client-side cross-check/fallback for
   `projects_count`/`completed_count` in case the backend entry omits them.
   `members_count` is derived straight from each team's local `members`
   array so it stays in sync the instant someone is added/removed. */
const teamLeaders = computed(() => {
  return localTeams.value.map(t => {
    const stats = teamLeaderStats.value[t.id] ?? teamLeaderStats.value[t.name] ?? {};
    return {
      ...t,
      projects_count: t.projects_count ?? stats.assigned ?? 0,
      completed_count: t.completed_count ?? stats.completed ?? 0,
      members_count: (t.members || []).length,
    };
  });
    return (props.teams || []).map(t => {
        const stats = teamLeaderStats.value[t.id] ?? teamLeaderStats.value[t.name] ?? {};
        return {
            ...t,
            projects_count: t.projects_count ?? stats.assigned ?? 0,
            completed_count: t.completed_count ?? stats.completed ?? 0,
        };
    });
});

const filteredTeams = computed(() => {
    if (!search.value) return teamLeaders.value;
    const term = search.value.toLowerCase();
    return teamLeaders.value.filter(t => t.name?.toLowerCase().includes(term) || t.workspace_name?.toLowerCase().includes(term));
});

const filteredAdmins = computed(() => {
    if (!search.value) return props.admins || [];
    const term = search.value.toLowerCase();
    return (props.admins || []).filter(a => a.name?.toLowerCase().includes(term) || a.email?.toLowerCase().includes(term));
});

/* ---------------- Workspaces dropdown ----------------
   Derives the distinct workspace names from teams + projects so the
   "N Workspaces" pill can open into an actual list without needing a
   dedicated `workspaces` prop from the backend. */
const workspaceNames = computed(() => {
  const names = new Set();
  localTeams.value.forEach(t => { if (t.workspace_name) names.add(t.workspace_name); });
  localProjects.value.forEach(p => { if (p.workspace_name) names.add(p.workspace_name); });
  return Array.from(names).sort();
    const names = new Set();
    (props.teams || []).forEach(t => { if (t.workspace_name) names.add(t.workspace_name); });
    localProjects.value.forEach(p => { if (p.workspace_name) names.add(p.workspace_name); });
    return Array.from(names).sort();
});

/* ---------------- Progress helpers ---------------- */
const progressClass = (progress) => {
    if (progress >= 100) return "completed";
    if (progress > 0) return "in-progress";
    return "todo";
};

const progressLabel = (progress) => {
    if (progress >= 100) return "Completed";
    if (progress > 0) return "In Progress";
    return "Pending";
};

const KANBAN_DEFS = [
    { key: "todo", label: "Pending" },
    { key: "in-progress", label: "In Progress" },
    { key: "completed", label: "Completed" },
];

const kanbanColumns = computed(() => {
    const projects = filteredProjects.value;
    return KANBAN_DEFS.map(def => ({
        key: def.key,
        label: def.label,
        projects: projects.filter(p => progressClass(p.progress) === def.key),
    }));
});

/* Unfiltered version (ignores the search box) used for the Completion
   Rate card's status breakdown, so that panel always reflects the
   whole org rather than the current search. */
const allKanbanColumns = computed(() => {
    const projects = localProjects.value;
    return KANBAN_DEFS.map(def => ({
        key: def.key,
        label: def.label,
        projects: projects.filter(p => progressClass(p.progress) === def.key),
    }));
});

const inProgressCount = computed(() => allKanbanColumns.value.find(c => c.key === "in-progress")?.projects.length || 0);

/* ---------------- Live-derived headline stats ----------------
   `stats.completedProjects` / `stats.pendingProjects` / `stats.completionRate`
   come from the server and only refresh on a page reload — but kanban
   drag-and-drop updates progress instantly and optimistically. Deriving
   these numbers from `localProjects` instead (same source the kanban
   board and donut breakdown already use) keeps every panel in sync the
   moment a card is dropped, instead of drifting until the next reload. */
const completedProjectsCount = computed(() => allKanbanColumns.value.find(c => c.key === "completed")?.projects.length || 0);
const pendingProjectsCount = computed(() => allKanbanColumns.value.find(c => c.key === "todo")?.projects.length || 0);
const completionRateComputed = computed(() => {
    const total = localProjects.value.length;
    if (!total) return 0;
    return Math.round((completedProjectsCount.value / total) * 100);
});

/* ---------------- Kanban drag & drop ----------------
   Native HTML5 drag-and-drop: cards are draggable, columns are drop
   targets. Dropping a card into a different column updates its
   progress locally (optimistic) and persists the change to the
   server. Adjust the endpoint below to match your actual project
   update route if it differs. */
const draggedProject = ref(null);
const draggedProjectId = ref(null);
const dragOverColumn = ref(null);

const onDragStart = (project) => {
    draggedProject.value = project;
    draggedProjectId.value = project.id;
};

const onDragOver = (columnKey) => {
    dragOverColumn.value = columnKey;
};

const onDragLeave = (columnKey) => {
    if (dragOverColumn.value === columnKey) dragOverColumn.value = null;
};

const onDragEnd = () => {
    draggedProject.value = null;
    draggedProjectId.value = null;
    dragOverColumn.value = null;
};

const onDrop = (columnKey) => {
    dragOverColumn.value = null;
    const project = draggedProject.value;
    if (!project) return;

    // Dropped back into its own column — nothing to do.
    if (progressClass(project.progress) === columnKey) {
        draggedProject.value = null;
        draggedProjectId.value = null;
        return;
    }

    const previousProgress = project.progress;
    let newProgress;
    if (columnKey === "todo") newProgress = 0;
    else if (columnKey === "completed") newProgress = 100;
    else newProgress = (project.progress > 0 && project.progress < 100) ? project.progress : 50;

    const target = localProjects.value.find(p => p.id === project.id);
    if (target) target.progress = newProgress;

    router.patch(`/super-admin/projects/${project.id}`, { progress: newProgress }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Revert on failure so the board reflects the real server state.
            if (target) target.progress = previousProgress;
        },
    });

    draggedProject.value = null;
    draggedProjectId.value = null;
};

/* ---------------- Deadlines ---------------- */
const startOfDay = (d) => { const c = new Date(d); c.setHours(0, 0, 0, 0); return c; };
const today = computed(() => startOfDay(new Date()));

const parsedDeadline = (project) => {
    if (!project.deadline) return null;
    const d = new Date(project.deadline);
    if (isNaN(d.getTime())) return null;
    return startOfDay(d);
};

const isProjectDone = (project) => project.progress >= 100;

const dueToday = computed(() => localProjects.value.filter(p => {
    if (isProjectDone(p)) return false;
    const due = parsedDeadline(p);
    return due && due.getTime() === today.value.getTime();
}).length);

const overdueProjects = computed(() => localProjects.value.filter(p => {
    if (isProjectDone(p)) return false;
    const due = parsedDeadline(p);
    return due && due.getTime() < today.value.getTime();
}));

const upcomingCount = computed(() => localProjects.value.filter(p => {
    if (isProjectDone(p)) return false;
    const due = parsedDeadline(p);
    return due && due.getTime() > today.value.getTime();
}).length);

const upcomingDeadlineProjects = computed(() => {
    return localProjects.value
        .filter(p => !isProjectDone(p) && parsedDeadline(p))
        .sort((a, b) => parsedDeadline(a) - parsedDeadline(b))
        .slice(0, 5);
});

const daysUntil = (project) => {
    const due = parsedDeadline(project);
    if (!due) return null;
    return Math.round((due.getTime() - today.value.getTime()) / 86400000);
};

const dueLabel = (project) => {
    const days = daysUntil(project);
    if (days === null) return "";
    if (days < 0) return `${Math.abs(days)}d overdue`;
    if (days === 0) return "Due today";
    if (days === 1) return "Due tomorrow";
    return `In ${days}d`;
};

const dueUrgencyClass = (project) => {
    const days = daysUntil(project);
    if (days === null) return "";
    if (days < 0) return "urgent";
    if (days <= 2) return "soon";
    return "";
};

const activityDotClass = (type) => {
    if (type === "project") return "in-progress";
    if (type === "leader") return "completed";
    return "todo";
};

/* ---------------- Projects assigned per month (bar chart) ----------------
   Buckets localProjects into the last 9 months by created_at (falls back
   to start_date). Bucket labels include the year when it differs from
   the current year, so the chart stays unambiguous across a year
   boundary. */
const monthlyProjects = computed(() => {
    const now = new Date();
    const buckets = [];

    for (let i = 8; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        buckets.push({
            year: d.getFullYear(),
            month: d.getMonth(),
            label: d.toLocaleString("default", { month: "short" }) +
                (d.getFullYear() !== now.getFullYear() ? ` '${String(d.getFullYear()).slice(-2)}` : ""),
            count: 0,
        });
    }

    localProjects.value.forEach(project => {
        const dateSource = project.created_at || project.start_date;
        if (!dateSource) return;
        const d = new Date(dateSource);
        const bucket = buckets.find(b => b.year === d.getFullYear() && b.month === d.getMonth());
        if (bucket) bucket.count++;
    });

    return buckets;
});

const maxMonthlyProjectCount = computed(() => Math.max(1, ...monthlyProjects.value.map(b => b.count)));

/* ---------------- Team modal ---------------- */
const selectedTeam = computed(() => localTeams.value.find(t => t.id === selectedTeamId.value) || null);

/* Any member not already on this team can be added — no longer restricted
   to the same workspace, per request ("he can add any member to any team
   leader team"). Still excludes other team leaders (role "TL") from being
   dropped into someone else's team. */
const candidateMembers = computed(() => {
  if (!selectedTeam.value) return [];
  const currentMemberIds = new Set((selectedTeam.value.members || []).map(m => m.id));
  return localMembers.value.filter(m => m.role !== "TL" && !currentMemberIds.has(m.id));
    if (!selectedTeam.value) return [];
    return (props.allMembers || []).filter(m =>
        m.workspace_id === selectedTeam.value.workspace_id &&
        m.role !== "TL" &&
        m.assigned_to !== selectedTeam.value.id
    );
});

const openTeam = (id) => { selectedTeamId.value = id; memberToAdd.value = ""; };
const closeTeam = () => { selectedTeamId.value = null; memberToAdd.value = ""; };

/* Adding a member optimistically:
   1. Moves them out of whatever team they were previously on (if any).
   2. Moves them into the selected team's local `members` array.
   3. Logs a Recent Activity entry ("Moved X from A to B" / "Added X to B").
   4. Persists to the server, then reloads just the affected props so the
      optimistic state gets reconciled with whatever the backend actually
      stored (also self-heals if the request failed). */
const addMember = () => {
  if (!selectedTeam.value || !memberToAdd.value) return;

  const team = selectedTeam.value;
  const member = localMembers.value.find(m => m.id === memberToAdd.value);
  if (!member) return;

  const previousTeam = localTeams.value.find(t => (t.members || []).some(m => m.id === member.id));
  const targetTeam = localTeams.value.find(t => t.id === team.id);

  if (previousTeam && targetTeam && previousTeam.id !== targetTeam.id) {
    previousTeam.members = (previousTeam.members || []).filter(m => m.id !== member.id);
  }
  if (targetTeam) {
    targetTeam.members = [...(targetTeam.members || []), { ...member, assigned_to: team.id }];
  }
  const memberRef = localMembers.value.find(m => m.id === member.id);
  if (memberRef) memberRef.assigned_to = team.id;

  pushLocalActivity({
    type: "leader",
    message: previousTeam && previousTeam.id !== team.id
      ? `Moved ${member.name} from ${previousTeam.name} to ${team.name}`
      : `Added ${member.name} to ${team.name}`,
  });

  const addedMemberId = memberToAdd.value;
  memberToAdd.value = "";

  router.post(`/super-admin/teams/${team.id}/members/${addedMemberId}`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Reconcile optimistic state with the server's actual data (and pick
      // up the server-side activity-log entry, if the backend writes one).
      router.reload({ only: ["teams", "allMembers", "recentActivity"] });
    },
    onError: () => {
      // Request failed — reloading these props restores the true state and
      // undoes the optimistic move.
      router.reload({ only: ["teams", "allMembers", "recentActivity"] });
    },
  });
};

const removeMember = (memberId) => {
  if (!selectedTeam.value) return;
  const team = selectedTeam.value;
  const member = (team.members || []).find(m => m.id === memberId);

  const targetTeam = localTeams.value.find(t => t.id === team.id);
  if (targetTeam) {
    targetTeam.members = (targetTeam.members || []).filter(m => m.id !== memberId);
  }
  const memberRef = localMembers.value.find(m => m.id === memberId);
  if (memberRef) memberRef.assigned_to = null;

  if (member) {
    pushLocalActivity({
      type: "leader",
      message: `Removed ${member.name} from ${team.name}`,
    });
  }

  router.delete(`/super-admin/teams/${team.id}/members/${memberId}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ["teams", "allMembers", "recentActivity"] });
    },
    onError: () => {
      router.reload({ only: ["teams", "allMembers", "recentActivity"] });
    },
  });
    if (!selectedTeam.value || !memberToAdd.value) return;
    router.post(`/super-admin/teams/${selectedTeam.value.id}/members/${memberToAdd.value}`, {}, {
        preserveScroll: true,
        onSuccess: () => { memberToAdd.value = ""; },
    });
};

const removeMember = (memberId) => {
    if (!selectedTeam.value) return;
    router.delete(`/super-admin/teams/${selectedTeam.value.id}/members/${memberId}`, { preserveScroll: true });
};

/* ---------------- Create admin ---------------- */
const form = ref({ role: "ADMIN", name: "", email: "", password: "", password_confirmation: "" });

function resetForm() {
    form.value = { role: "ADMIN", name: "", email: "", password: "", password_confirmation: "" };
}

function createAdmin() {
    router.post("/super-admin/admin", form.value, {
        preserveScroll: true,
        onSuccess: () => { showCreateModal.value = false; resetForm(); },
    });
}

/* ---------------- Click-outside handling ----------------
   Plain document listener + template refs — no external directive
   plugin required. Toggle buttons use @click.stop so the click that
   opens a dropdown never reaches this listener and immediately
   closes it again. */
const bellRef = ref(null);
const profileRef = ref(null);
const workspaceRef = ref(null);

const handleDocumentClick = (event) => {
    if (bellRef.value && !bellRef.value.contains(event.target)) showBellDropdown.value = false;
    if (profileRef.value && !profileRef.value.contains(event.target)) showProfileMenu.value = false;
    if (workspaceRef.value && !workspaceRef.value.contains(event.target)) showWorkspaceDropdown.value = false;
};

onMounted(() => {
    document.addEventListener("click", handleDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener("click", handleDocumentClick);
});

function logout() {
    router.post("/super-admin/logout");
}
</script>

<style scoped>
/* ==========================================================================
   TYPE SYSTEM
   Lexend  → headings / stat values (confident, geometric, screen-native)
   Inter   → interface copy, labels, body text
   IBM Plex Mono → every hard number (stats, %, dates, counts) — the
   "console" signature: this is a super-admin control surface, so figures
   read like an audit log, not marketing copy.
   ========================================================================== */
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600;700&display=swap');

/* ==========================================================================
   THEME TOKENS
   ========================================================================== */
.theme-dark {
  --dashboard-bg: #10121c;
  --panel-bg: #171a26;
  --card-inner-bg: #1d2130;
  --card-inner-hover: #262b3d;
  --stat-card-bg: #171a26;
  --input-element-bg: #212536;
  --dropdown-panel-bg: #1a1d2a;
  --border-subtle: rgba(148, 163, 210, 0.09);
  --border-deep: rgba(148, 163, 210, 0.16);
  --border-divider: rgba(148, 163, 210, 0.08);
  --text-main: #d9dbe7;
  --text-header: #f6f7fb;
  --text-muted: #7d83a0;
  --text-card-sub: #b1b5cc;
  --due-date-color: #6c7290;
  --shadow-cards: rgba(3, 4, 10, 0.45);
  --shadow-stats: rgba(3, 4, 10, 0.35);
  --shadow-stats-hover: rgba(3, 4, 10, 0.55);
  --accent: #1fd1ab;
  --accent-soft: rgba(31, 209, 171, 0.16);

  /* Sidebar tokens (dark theme) */
  --sidebar-bg: #10121a;
  --sidebar-border: rgba(255, 255, 255, 0.06);
  --sidebar-divider: rgba(255, 255, 255, 0.06);
  --sidebar-text: #8a8fa5;
  --sidebar-text-hover: #e7e9f2;
  --sidebar-hover-bg: rgba(255, 255, 255, 0.04);
  --sidebar-logo-title: #f4f5f9;
  --sidebar-logo-sub: #6d7288;
}

.theme-light {
  --dashboard-bg: #eef1f7;
  --panel-bg: #ffffff;
  --card-inner-bg: #f5f7fb;
  --card-inner-hover: #eaedf5;
  --stat-card-bg: #ffffff;
  --input-element-bg: #f0f2f8;
  --dropdown-panel-bg: #ffffff;
  --border-subtle: rgba(30, 35, 70, 0.08);
  --border-deep: rgba(30, 35, 70, 0.14);
  --border-divider: rgba(30, 35, 70, 0.07);
  --text-main: #2d3142;
  --text-header: #12141f;
  --text-muted: #767c93;
  --text-card-sub: #454a5f;
  --due-date-color: #9298ad;
  --shadow-cards: rgba(24, 28, 55, 0.06);
  --shadow-stats: rgba(24, 28, 55, 0.05);
  --shadow-stats-hover: rgba(24, 28, 55, 0.1);
  --accent: #0b8a75;
  --accent-soft: rgba(11, 138, 117, 0.1);

  /* Sidebar tokens (light theme) — the rail now follows the toggle
     instead of always staying dark. */
  --sidebar-bg: #ffffff;
  --sidebar-border: rgba(30, 35, 70, 0.08);
  --sidebar-divider: rgba(30, 35, 70, 0.08);
  --sidebar-text: #5b607a;
  --sidebar-text-hover: #1c2033;
  --sidebar-hover-bg: rgba(30, 35, 70, 0.05);
  --sidebar-logo-title: #12141f;
  --sidebar-logo-sub: #767c93;
    --dashboard-bg: #10121c;
    --panel-bg: #171a26;
    --card-inner-bg: #1d2130;
    --card-inner-hover: #262b3d;
    --stat-card-bg: #171a26;
    --input-element-bg: #212536;
    --dropdown-panel-bg: #1a1d2a;
    --border-subtle: rgba(148, 163, 210, 0.09);
    --border-deep: rgba(148, 163, 210, 0.16);
    --border-divider: rgba(148, 163, 210, 0.08);
    --text-main: #d9dbe7;
    --text-header: #f6f7fb;
    --text-muted: #7d83a0;
    --text-card-sub: #b1b5cc;
    --due-date-color: #6c7290;
    --shadow-cards: rgba(3, 4, 10, 0.45);
    --shadow-stats: rgba(3, 4, 10, 0.35);
    --shadow-stats-hover: rgba(3, 4, 10, 0.55);
    --accent: #1fd1ab;
    --accent-soft: rgba(31, 209, 171, 0.16);
}

.theme-light {
    --dashboard-bg: #eef1f7;
    --panel-bg: #ffffff;
    --card-inner-bg: #f5f7fb;
    --card-inner-hover: #eaedf5;
    --stat-card-bg: #ffffff;
    --input-element-bg: #f0f2f8;
    --dropdown-panel-bg: #ffffff;
    --border-subtle: rgba(30, 35, 70, 0.08);
    --border-deep: rgba(30, 35, 70, 0.14);
    --border-divider: rgba(30, 35, 70, 0.07);
    --text-main: #2d3142;
    --text-header: #12141f;
    --text-muted: #767c93;
    --text-card-sub: #454a5f;
    --due-date-color: #9298ad;
    --shadow-cards: rgba(24, 28, 55, 0.06);
    --shadow-stats: rgba(24, 28, 55, 0.05);
    --shadow-stats-hover: rgba(24, 28, 55, 0.1);
    --accent: #0b8a75;
    --accent-soft: rgba(11, 138, 117, 0.1);
}

.dashboard {
    --c-blue: #3e63dd;
    --c-violet: #7c5cd6;
    --c-green: #0e9a7f;
    --c-amber: #c2792c;
    --c-cyan: #3184c2;
    --c-red: #d6484f;
}

/* ===================== BASE / LAYOUT ===================== */
* {
    box-sizing: border-box;
}

.dashboard {
    /* Break out of any parent container/max-width (common in shared app
     layouts) so the dashboard always spans the full viewport instead of
     sitting centered with dead gutters on both sides. */
    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);

    display: flex;
    height: 100vh;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: var(--dashboard-bg);
    color: var(--text-main);
    overflow: hidden;
    transition: background-color 0.2s ease, color 0.2s ease;
    -webkit-font-smoothing: antialiased;
}

.dashboard h2,
.dashboard h3,
.dashboard h4 {
    font-family: 'Lexend', 'Inter', sans-serif;
}

.main-content {
    flex: 1;
    overflow-y: auto;
    width: 100%;
    height: 100%;
}

.content-wrapper {
    max-width: 1640px;
    margin: 0 auto;
    width: 100%;
    padding: 28px 40px 60px;
}

/* ===================== SIDEBAR ===================== */
/* The rail now follows the theme toggle via --sidebar-* tokens (defined
   per-theme above) instead of being hardcoded to a fixed dark color. */
.sidebar {
  width: 252px;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--sidebar-border);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 24px 16px;
  flex-shrink: 0;
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.logo { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding: 4px 8px 20px; border-bottom: 1px solid var(--sidebar-divider); }
    width: 252px;
    background: #10121a;
    border-right: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 24px 16px;
    flex-shrink: 0;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 30px;
    padding: 4px 8px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.logo-icon {
    width: 38px;
    height: 38px;
    border-radius: 9px;
    background: linear-gradient(150deg, #2be3bb, var(--accent));
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Lexend', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #06110e;
    flex-shrink: 0;
}

.logo h2 {
    font-size: 14.5px;
    font-weight: 600;
    color: #f4f5f9;
    margin-bottom: 1px;
    letter-spacing: -0.1px;
}

.logo span {
    color: #6d7288;
    font-size: 11px;
    font-family: 'Inter', sans-serif;
    letter-spacing: 0.2px;
}

.menu {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.logo h2 { font-size: 14.5px; font-weight: 600; color: var(--sidebar-logo-title); margin-bottom: 1px; letter-spacing: -0.1px; }
.logo span { color: var(--sidebar-logo-sub); font-size: 11px; font-family: 'Inter', sans-serif; letter-spacing: 0.2px; }

.menu a {
  text-decoration: none; color: var(--sidebar-text); display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: 8px; font-weight: 500; font-size: 13px; transition: .15s;
  position: relative;
}
.menu a:hover { background: var(--sidebar-hover-bg); color: var(--sidebar-text-hover); }
.menu a.active { background: var(--accent-soft); color: var(--sidebar-text-hover); }
    text-decoration: none;
    color: #8a8fa5;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 13px;
    transition: .15s;
    position: relative;
}

.menu a:hover {
    background: rgba(255, 255, 255, 0.04);
    color: #e7e9f2;
}

.menu a.active {
    background: rgba(23, 178, 149, 0.14);
    color: #f4f5f9;
}

.menu a.active::before {
    content: "";
    position: absolute;
    left: -16px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 18px;
    border-radius: 0 3px 3px 0;
    background: var(--accent);
}

.logout-btn {
    border: 1px solid rgba(214, 72, 79, 0.25);
    border-radius: 8px;
    background: transparent;
    color: #e2777c;
    padding: 11px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: .2s;
    font-family: 'Inter', sans-serif;
}

.logout-btn:hover {
    background: rgba(214, 72, 79, .1);
    border-color: rgba(214, 72, 79, .4);
}

/* ===================== TOPBAR ===================== */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 16px 30px;
    background: var(--panel-bg);
    border-bottom: 1px solid var(--border-subtle);
    position: sticky;
    top: 0;
    z-index: 40;
}

.topbar-icons {
    display: flex;
    align-items: center;
    gap: 10px;
}

.topbar-greeting h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: -0.2px;
    color: var(--text-header);
}

.topbar-greeting .wave {
    display: inline-block;
}

.topbar-greeting p {
    margin-top: 4px;
    color: var(--text-muted);
    font-size: 12.5px;
    font-weight: 400;
}

.topbar-greeting p strong {
    color: var(--accent);
    font-weight: 700;
    font-family: 'IBM Plex Mono', monospace;
}

.workspace-dropdown-container {
    position: relative;
}

.workspace-label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-card-sub);
    background: var(--input-element-bg);
    border: 1px solid var(--border-subtle);
    padding: 8px 13px;
    border-radius: 7px;
    font-family: 'IBM Plex Mono', monospace;
    cursor: pointer;
    transition: all 0.15s ease;
}

.workspace-label:hover {
    background: var(--card-inner-hover);
    border-color: var(--border-deep);
}

.workspace-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--accent);
    flex-shrink: 0;
}

.workspace-caret {
    width: 9px;
    height: 9px;
    margin-left: 1px;
    opacity: 0.7;
    transition: transform 0.15s ease;
}

.workspace-caret.open {
    transform: rotate(180deg);
}

.workspace-dropdown-panel {
    position: absolute;
    left: 0;
    top: 46px;
    min-width: 210px;
    max-height: 280px;
    overflow-y: auto;
    background: var(--dropdown-panel-bg);
    border: 1px solid var(--border-deep);
    border-radius: 10px;
    box-shadow: 0 18px 40px -10px var(--shadow-cards);
    z-index: 50;
    padding: 8px;
}

.workspace-dropdown-header {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    padding: 6px 8px 8px;
    font-family: 'Inter', sans-serif;
}

.workspace-dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 8px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
}

.workspace-dropdown-item:hover {
    background: var(--card-inner-hover);
}

.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    width: 14px;
    height: 14px;
    color: var(--text-muted);
    pointer-events: none;
}

.search-box {
    width: 230px;
    padding: 9px 13px 9px 34px;
    border-radius: 7px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    outline: none;
    font-size: 13px;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
}

.search-box::placeholder {
    color: var(--text-muted);
}

.search-box:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
    background: var(--panel-bg);
}

.theme-btn,
.icon-btn {
    background: var(--input-element-bg);
    border: 1px solid var(--border-subtle);
    color: var(--text-main);
    width: 36px;
    height: 36px;
    font-size: 14px;
    border-radius: 7px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    position: relative;
}

.theme-btn:hover,
.icon-btn:hover {
    background: var(--card-inner-hover);
    border-color: var(--border-deep);
}

/* ===================== STATS GRID ===================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}

@media (max-width: 1400px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--stat-card-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 18px 18px 20px;
    box-shadow: 0 1px 2px var(--shadow-stats);
    transition: all 0.18s ease;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 14px;
    bottom: 14px;
    width: 3px;
    border-radius: 0 3px 3px 0;
}

.assigned-tasks-card::before {
    background: var(--c-blue);
}

.team-projects-card::before {
    background: var(--c-violet);
}

.completed-tasks-card::before {
    background: var(--c-green);
}

.pending-tasks-card::before {
    background: var(--c-amber);
}

.completion-rate-card::before {
    background: var(--c-cyan);
}

.stat-card:hover {
    border-color: var(--border-deep);
    box-shadow: 0 8px 20px -6px var(--shadow-stats-hover);
    transform: translateY(-1px);
}

.stat-icon-badge {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    margin-bottom: 16px;
    margin-left: 5px;
}

.assigned-tasks-card {
    background: linear-gradient(165deg, rgba(62, 99, 221, 0.07), var(--stat-card-bg) 55%);
}

.team-projects-card {
    background: linear-gradient(165deg, rgba(124, 92, 214, 0.07), var(--stat-card-bg) 55%);
}

.completed-tasks-card {
    background: linear-gradient(165deg, rgba(14, 154, 127, 0.08), var(--stat-card-bg) 55%);
}

.pending-tasks-card {
    background: linear-gradient(165deg, rgba(194, 121, 44, 0.09), var(--stat-card-bg) 55%);
}

.completion-rate-card {
    background: linear-gradient(165deg, rgba(49, 132, 194, 0.08), var(--stat-card-bg) 55%);
}

.assigned-tasks-card .stat-icon-badge {
    background: rgba(62, 99, 221, 0.14);
}

.team-projects-card .stat-icon-badge {
    background: rgba(124, 92, 214, 0.14);
}

.completed-tasks-card .stat-icon-badge {
    background: rgba(14, 154, 127, 0.14);
}

.pending-tasks-card .stat-icon-badge {
    background: rgba(194, 121, 44, 0.16);
}

.completion-rate-card .stat-icon-badge {
    background: rgba(49, 132, 194, 0.14);
}

.stat-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--text-muted);
    margin-left: 5px;
}

.stat-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 26px;
    font-weight: 600;
    color: var(--text-header);
    margin: 7px 0 0 5px;
    letter-spacing: -0.5px;
}

.stat-subtitle {
    display: block;
    margin-top: 6px;
    margin-left: 5px;
    color: var(--text-muted);
    font-size: 11px;
    line-height: 1.4;
}

/* ===================== TOP ROW ===================== */
.top-row-grid {
    display: grid;
    grid-template-columns: 1.6fr 0.7fr 0.9fr;
    gap: 18px;
    margin-bottom: 18px;
    align-items: stretch;
}

@media (max-width: 1300px) {
    .top-row-grid {
        grid-template-columns: 1fr 1fr;
    }

    .top-row-grid .main-panel {
        grid-column: 1 / -1;
    }
}

@media (max-width: 760px) {
    .top-row-grid {
        grid-template-columns: 1fr;
    }

    .top-row-grid .main-panel {
        grid-column: auto;
    }
}

.dashboard-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 1px 2px var(--shadow-cards);
}

.card-header {
    margin-bottom: 18px;
    display: flex;
    align-items: center;
}

.card-header h2 {
    font-size: 14.5px;
    font-weight: 600;
    margin: 0;
    color: var(--text-header);
    letter-spacing: -0.1px;
}

.donut-card {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.donut-wrap {
    position: relative;
    width: 144px;
    height: 144px;
    margin: 4px auto 18px;
}

.donut-svg {
    width: 100%;
    height: 100%;
}

.donut-svg circle {
    transition: stroke-dashoffset 0.4s ease;
}

.donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.donut-center strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 23px;
    font-weight: 600;
    color: var(--text-header);
    letter-spacing: -0.4px;
}

.donut-center span {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
    margin-top: 3px;
}

.donut-footer {
    display: flex;
    width: 100%;
    gap: 12px;
    border-top: 1px solid var(--border-divider);
    padding-top: 16px;
}

.donut-stat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.donut-stat strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 17px;
    font-weight: 600;
    color: var(--text-header);
}

.donut-stat span {
    font-size: 10.5px;
    color: var(--text-muted);
    font-weight: 500;
    margin-top: 3px;
}

.donut-breakdown {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border-divider);
}

.donut-breakdown-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-card-sub);
    font-family: 'Inter', sans-serif;
}

.donut-breakdown-label {
    flex: 1;
}

.donut-breakdown-row strong {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-header);
}

.today-activity-card {
    background: linear-gradient(160deg, #123a5c 0%, #0a1826 100%);
    border: 1px solid rgba(62, 140, 214, 0.28);
    color: #eaf2fb;
    display: flex;
    flex-direction: column;
}

.today-activity-card .card-header h2 {
    color: #f2f6fb;
}

.activity-stat-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 13px;
    flex: 1;
}

.activity-stat-list li {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-icon {
    font-size: 13px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
    opacity: 0.9;
}

.ai-label {
    font-size: 12.5px;
    font-weight: 500;
    opacity: 0.82;
    flex: 1;
    font-family: 'Inter', sans-serif;
}

.ai-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 14px;
    font-weight: 600;
    color: #7fbdf0;
}

.daily-goal {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.daily-goal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 8px;
    opacity: 0.9;
}

.daily-goal-head strong {
    font-family: 'IBM Plex Mono', monospace;
    color: #7fbdf0;
}

.daily-goal-bar {
    height: 6px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    overflow: hidden;
}

.daily-goal-fill {
    height: 100%;
    border-radius: 999px;
    background: #3f93e0;
}

/* ===================== PROJECTS ASSIGNED PER MONTH ===================== */
.monthly-projects-card {
    margin-bottom: 18px;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 8px;
    height: 176px;
    padding-top: 10px;
    background-image: repeating-linear-gradient(to top, var(--border-divider) 0, var(--border-divider) 1px, transparent 1px, transparent 25%);
}

.bar-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    height: 100%;
}

.bar-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-header);
    margin-bottom: 6px;
}

.bar-track {
    flex: 1;
    width: 22px;
    background: var(--card-inner-bg);
    border-radius: 5px;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    border: 1px solid var(--border-subtle);
}

.bar-fill {
    width: 100%;
    background: var(--accent);
    border-radius: 5px;
    transition: height 0.4s ease;
    min-height: 3px;
    opacity: 0.85;
}

.bar-fill.zero {
    background: var(--text-muted);
    opacity: 0.2;
}

.bar-label {
    margin-top: 9px;
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}

/* ===================== MID GRID ===================== */
.mid-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}

@media (max-width: 1000px) {
    .mid-grid {
        grid-template-columns: 1fr;
    }
}

.kanban-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.view-toggle {
    display: flex;
    gap: 2px;
    background: var(--input-element-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 3px;
}

.view-toggle button {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 11.5px;
    font-weight: 500;
    padding: 6px 11px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: 'Inter', sans-serif;
}

.view-toggle button.active {
    background: var(--accent);
    color: #06120f;
    font-weight: 600;
}

.view-toggle-btn {
    border: none;
    background: var(--accent);
    color: #06120f;
    padding: 9px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12.5px;
    font-family: 'Inter', sans-serif;
}

.view-toggle-btn:hover {
    opacity: .9;
}

.kanban-board {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

@media (max-width: 800px) {
    .kanban-board {
        grid-template-columns: 1fr;
    }
}

.kanban-column {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 13px;
    display: flex;
    flex-direction: column;
    min-height: 120px;
}

.kanban-column-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding: 0 2px;
}

.kanban-column-header h3 {
    font-size: 11px;
    font-weight: 600;
    margin: 0;
    color: var(--text-card-sub);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex: 1;
    font-family: 'Inter', sans-serif;
}

.kanban-column-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.kanban-column-dot.todo {
    background: var(--c-amber);
}

.kanban-column-dot.in-progress {
    background: var(--c-blue);
}

.kanban-column-dot.completed {
    background: var(--c-green);
}

.kanban-count {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 5px;
    padding: 2px 7px;
}

.kanban-column-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
    min-height: 90px;
    border-radius: 8px;
    transition: background 0.15s ease, box-shadow 0.15s ease;
}

.kanban-column-body.is-drop-target {
    background: var(--accent-soft);
    box-shadow: inset 0 0 0 2px var(--accent);
}

.kanban-task-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 12px;
    transition: border-color 0.15s ease, opacity 0.15s ease, transform 0.15s ease;
    cursor: grab;
}

.kanban-task-card:hover {
    border-color: var(--border-deep);
}

.kanban-task-card:active {
    cursor: grabbing;
}

.kanban-task-card.is-dragging {
    opacity: 0.35;
    transform: scale(0.97);
}

.kanban-task-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
}

.kanban-task-top h4 {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    margin: 0;
    color: var(--text-main);
    line-height: 1.35;
}

.kanban-task-card .task-row-project {
    margin: 8px 0 0 0;
    font-size: 12px;
}

.kanban-task-card .due-date {
    display: block;
    margin-top: 8px;
    font-size: 11px;
}

.kanban-empty {
    text-align: center;
    padding: 18px 10px;
    color: var(--text-muted);
    font-size: 12px;
    border: 1px dashed var(--border-deep);
    border-radius: 8px;
    transition: all 0.15s ease;
}

.kanban-empty.is-drop-target {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-soft);
    border-style: solid;
}

.task-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.task-row-card {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 9px;
    padding: 15px 16px;
    transition: border-color 0.15s ease;
}

.task-row-card:hover {
    border-color: var(--border-deep);
}

.task-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
    background: var(--text-muted);
}

.task-status-dot.in-progress {
    background: var(--c-blue);
}

.task-status-dot.completed {
    background: var(--c-green);
}

.task-status-dot.todo {
    background: var(--c-amber);
}

.status-pill.in-progress {
    background: rgba(62, 99, 221, 0.12);
    color: var(--c-blue);
}

.status-pill.completed {
    background: rgba(14, 154, 127, 0.12);
    color: var(--c-green);
}

.status-pill.todo {
    background: rgba(194, 121, 44, 0.14);
    color: var(--c-amber);
}

.task-row-main {
    flex: 1;
    min-width: 0;
}

.task-row-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.task-row-top h3 {
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    margin: 0;
    color: var(--text-main);
}

.task-row-project {
    margin: 5px 0 0 0;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 400;
}

.task-row-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}

.priority-badge {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 5px;
    flex-shrink: 0;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
}

.status-pill {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 5px;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    font-family: 'Inter', sans-serif;
}

.empty-state-inline {
    text-align: center;
    padding: 28px 0;
    color: var(--text-muted);
    font-size: 13px;
    width: 100%;
}

/* ===================== TEAM PROJECTS TABLE ===================== */
.team-projects-table {
    display: flex;
    flex-direction: column;
}

.team-projects-head {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1.2fr 1fr;
    align-items: center;
    padding: 0 6px 12px;
    border-bottom: 1px solid var(--border-divider);
    margin-bottom: 4px;
}

.team-projects-head span {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    font-family: 'Inter', sans-serif;
}

.team-projects-row {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1.2fr 1fr;
    align-items: center;
    gap: 8px;
    padding: 13px 6px;
    border-bottom: 1px solid var(--border-divider);
    transition: background 0.15s ease;
}

.team-projects-row:last-child {
    border-bottom: none;
}

.team-projects-row:hover {
    background: var(--card-inner-hover);
}

.tp-name {
    display: flex;
    align-items: center;
    gap: 9px;
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    min-width: 0;
}

.tp-name span:last-child {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tp-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11.5px;
    color: var(--due-date-color);
}

.tp-leader {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: var(--text-card-sub);
    min-width: 0;
}

.tp-leader span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tp-progress {
    display: flex;
    align-items: center;
    gap: 8px;
}

.tp-progress .mini-progress {
    flex: 1;
    margin-top: 0;
}

.tp-progress span {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    color: var(--accent);
    flex-shrink: 0;
}

@media (max-width: 1280px) {

    .team-projects-head,
    .team-projects-row {
        grid-template-columns: 1.6fr 1fr 1fr;
    }

    .tp-progress {
        display: none;
    }

    .team-projects-head span:last-child {
        display: none;
    }
}

.rep-list {
    display: flex;
    flex-direction: column;
}

.rep-row {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 12px 4px;
    border-bottom: 1px solid var(--border-divider);
    transition: background 0.15s ease;
    cursor: pointer;
}

.rep-row:last-child {
    border-bottom: none;
}

.rep-row:hover {
    background: var(--card-inner-hover);
}

.rep-row.is-static {
    cursor: default;
}

.rep-row.is-static:hover {
    background: transparent;
}

.rep-rank {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    color: var(--text-muted);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.rep-avatar {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--c-cyan);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Lexend', sans-serif;
    font-weight: 600;
    font-size: 12px;
    flex-shrink: 0;
}

.rep-avatar.small {
    width: 28px;
    height: 28px;
    font-size: 10.5px;
    border-radius: 7px;
}

.rep-row:nth-child(5n+2) .rep-avatar {
    background: var(--c-blue);
}

.rep-row:nth-child(5n+3) .rep-avatar {
    background: var(--c-violet);
}

.rep-row:nth-child(5n+4) .rep-avatar {
    background: var(--c-cyan);
}

.rep-row:nth-child(5n+5) .rep-avatar {
    background: var(--c-amber);
}

.rep-row:nth-child(5n+6) .rep-avatar {
    background: var(--c-green);
}

.rep-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.rep-info strong {
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rep-info span {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}

.rep-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--text-header);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.status-dot-online {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--c-green);
    flex-shrink: 0;
}

.leads-table {
    display: flex;
    flex-direction: column;
}

.leads-table-head {
    display: grid;
    grid-template-columns: 1fr 140px 110px;
    align-items: center;
    padding: 0 4px 12px;
    border-bottom: 1px solid var(--border-divider);
    margin-bottom: 4px;
}

.leads-table-head span {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    font-family: 'Inter', sans-serif;
}

.leads-row {
    display: grid;
    grid-template-columns: 1fr 140px 110px;
    align-items: center;
    padding: 13px 4px;
    border-bottom: 1px solid var(--border-divider);
    transition: background 0.15s ease;
}

.leads-row:last-child {
    border-bottom: none;
}

.leads-row:hover {
    background: var(--card-inner-hover);
}

.lead-identity {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.lead-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--text-muted);
    flex-shrink: 0;
}

.lead-dot.in-progress {
    background: var(--c-blue);
}

.lead-dot.completed {
    background: var(--c-green);
}

.lead-dot.todo {
    background: var(--c-amber);
}

.lead-title {
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.lead-project {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 400;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.timeline-list {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    display: flex;
    gap: 14px;
    padding: 13px 4px;
    position: relative;
}

.timeline-item::before {
    content: "";
    position: absolute;
    left: 8px;
    top: 26px;
    bottom: -4px;
    width: 1px;
    background: var(--border-divider);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--text-muted);
    flex-shrink: 0;
    margin-top: 4px;
    box-shadow: 0 0 0 3px var(--card-inner-bg);
    z-index: 1;
}

.timeline-dot.in-progress {
    background: var(--c-blue);
}

.timeline-dot.completed {
    background: var(--c-green);
}

.timeline-dot.todo {
    background: var(--c-amber);
}

.timeline-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    min-width: 0;
}

.timeline-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.timeline-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10.5px;
    font-weight: 600;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.timeline-pill {
    font-family: 'Inter', sans-serif;
    font-size: 10.5px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    flex-shrink: 0;
}

.timeline-pill.soon {
    background: rgba(194, 121, 44, 0.16);
    color: var(--c-amber);
}

.timeline-pill.urgent {
    background: rgba(214, 72, 79, 0.14);
    color: var(--c-red);
}

.timeline-content strong {
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
}

.timeline-content small {
    font-size: 12px;
    color: var(--text-muted);
}

.timeline-progress {
    height: 4px;
    border-radius: 999px;
    background: var(--border-deep);
    overflow: hidden;
    margin-top: 4px;
}

.timeline-progress-fill {
    height: 100%;
    border-radius: 999px;
    background: var(--accent);
}

/* ===================== ADMIN TABLE ===================== */
.admin-table-wrap {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    padding: 10px 12px;
    text-align: left;
    color: var(--text-muted);
    font-weight: 700;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid var(--border-divider);
    font-family: 'Inter', sans-serif;
}

.admin-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-divider);
    font-size: 13px;
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
}

.admin-table tr:hover td {
    background: var(--card-inner-hover);
}

.admin-user-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.admin-user-cell strong {
    font-weight: 500;
    color: var(--text-main);
}

.admin-table td:nth-child(2) {
    color: var(--text-card-sub);
}

.admin-table td:nth-child(3) {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
}

/* ===================== PROFILE / NOTIFICATIONS ===================== */
.profile-container {
    position: relative;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    cursor: pointer;
    border: 1px solid var(--border-subtle);
}

.avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--accent);
    color: #06120f;
    font-family: 'Lexend', sans-serif;
    font-size: 12.5px;
    font-weight: 700;
}

.profile-dropdown {
    position: absolute;
    right: 0;
    top: 46px;
    background: var(--dropdown-panel-bg);
    border: 1px solid var(--border-deep);
    border-radius: 9px;
    overflow: hidden;
    z-index: 10;
    box-shadow: 0 12px 28px var(--shadow-cards);
    min-width: 140px;
}

.profile-dropdown button {
    background: transparent;
    border: none;
    color: var(--text-main);
    padding: 11px 16px;
    cursor: pointer;
    width: 100%;
    text-align: left;
    font-size: 13px;
    font-weight: 400;
    font-family: 'Inter', sans-serif;
}

.profile-dropdown button:hover {
    background: var(--card-inner-hover);
}

.notification-bell-container {
    position: relative;
}

.notification-dropdown-panel {
    position: absolute;
    right: 0;
    top: 46px;
    width: 320px;
    background: var(--dropdown-panel-bg);
    border: 1px solid var(--border-deep);
    border-radius: 11px;
    box-shadow: 0 18px 40px -10px var(--shadow-cards);
    z-index: 50;
    overflow: hidden;
}

.notification-dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 16px;
    border-bottom: 1px solid var(--border-deep);
    color: var(--text-main);
}

.notification-dropdown-header h3 {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
}

.notification-dropdown-body {
    padding: 10px;
    max-height: 320px;
    overflow-y: auto;
    color: var(--text-main);
}

.notification-scroll-area {
    max-height: 280px;
    overflow-y: auto;
}

.notification-alert-item {
    display: flex;
    gap: 11px;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease;
    border: 1px solid var(--border-subtle);
    margin-bottom: 6px;
}

.notification-alert-item:hover {
    background: var(--card-inner-hover);
}

.alert-item-indicator {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--accent-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 12px;
}

.urgent-indicator {
    background: rgba(214, 72, 79, 0.14);
}

.alert-task-title {
    font-family: 'Inter', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-main);
    margin: 0;
}

.alert-task-time-left {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    margin: 3px 0 0 0;
}

.notification-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 28px 16px;
    color: var(--text-muted);
    text-align: center;
    font-size: 12.5px;
}

.icon-btn {
    position: relative;
}

.bell-alert-green-dot {
    position: absolute;
    top: -3px;
    right: -3px;
    min-width: 16px;
    height: 16px;
    border-radius: 999px;
    background: var(--c-red);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    padding: 0 4px;
    border: 2px solid var(--panel-bg);
}

/* ===================== MODALS ===================== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(6, 7, 12, .65);
    backdrop-filter: blur(6px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 20px;
}

.modal {
    width: 480px;
    max-width: 95%;
    background: var(--panel-bg);
    border-radius: 14px;
    border: 1px solid var(--border-deep);
    padding: 26px;
    box-shadow: 0 30px 80px var(--shadow-cards);
    max-height: 90vh;
    overflow-y: auto;
}

.team-modal {
    width: 560px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.modal-header h2 {
    font-size: 19px;
    color: var(--text-header);
    margin-bottom: 5px;
}

.modal-header p {
    color: var(--text-muted);
    font-size: 12.5px;
}

.close-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: var(--card-inner-bg);
    color: var(--text-muted);
    cursor: pointer;
}

.close-btn:hover {
    color: var(--c-red);
}

.team-modal-stats {
    display: flex;
    gap: 10px;
    margin-bottom: 22px;
}

.team-modal-stats div {
    flex: 1;
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}

.team-modal-stats strong {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 17px;
    color: var(--text-header);
}

.team-modal-stats span {
    font-size: 10.5px;
    color: var(--text-muted);
}

.modal-subhead {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
    margin: 16px 0 9px;
    font-weight: 700;
}

.modal-member-list,
.modal-project-list {
    display: flex;
    flex-direction: column;
    gap: 7px;
    max-height: 170px;
    overflow-y: auto;
}

.modal-member-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--card-inner-bg);
    border-radius: 9px;
    padding: 9px 11px;
}

.modal-member-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.modal-member-info strong {
    font-size: 13px;
    color: var(--text-main);
}

.modal-member-info span {
    font-size: 11px;
    color: var(--text-muted);
}

.remove-btn {
    border: none;
    background: rgba(214, 72, 79, .12);
    color: var(--c-red);
    padding: 6px 11px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
}

.remove-btn:hover {
    background: rgba(214, 72, 79, .22);
}

.add-member-row {
    display: flex;
    gap: 9px;
}

.add-member-row select {
    flex: 1;
    padding: 11px 13px;
    border-radius: 9px;
    border: 1px solid var(--border-deep);
    background: var(--card-inner-bg);
    color: var(--text-main);
    font-size: 13px;
    font-family: 'Inter', sans-serif;
}

.save-btn.small {
    padding: 9px 16px;
    font-size: 12.5px;
}

.save-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.modal-project-row {
    display: flex;
    align-items: center;
    gap: 9px;
    background: var(--card-inner-bg);
    border-radius: 9px;
    padding: 9px 11px;
}

.modal-project-name {
    flex: 1;
    font-size: 13px;
    color: var(--text-main);
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: var(--text-card-sub);
    font-weight: 600;
    font-size: 12.5px;
    font-family: 'Inter', sans-serif;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 9px;
    border: 1px solid var(--border-deep);
    background: var(--card-inner-bg);
    color: var(--text-main);
    outline: none;
    font-size: 13.5px;
    font-family: 'Inter', sans-serif;
}

.form-group input:focus,
.form-group select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

.cancel-btn {
    padding: 11px 20px;
    border: 1px solid var(--border-deep);
    border-radius: 9px;
    background: transparent;
    color: var(--text-main);
    cursor: pointer;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
}

.cancel-btn:hover {
    background: var(--card-inner-hover);
}

.save-btn {
    padding: 11px 22px;
    border: none;
    border-radius: 9px;
    background: var(--accent);
    color: #06120f;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
}

.save-btn:hover:not(:disabled) {
    opacity: .9;
}

/* ===================== SEARCH HIGHLIGHT ===================== */
.search-highlight {
    background: var(--accent-soft);
    color: var(--accent);
    padding: 0 2px;
    border-radius: 3px;
    font-weight: 700;
}

/* ===================== MINI PROGRESS (shared) ===================== */
.mini-progress {
    height: 5px;
    background: var(--border-deep);
    border-radius: 6px;
    overflow: hidden;
    margin-top: 12px;
}

.mini-progress-fill {
    height: 100%;
    background: var(--accent);
    border-radius: 6px;
}

/* ===================== FOCUS STATES (a11y) ===================== */
.dashboard a:focus-visible,
.dashboard button:focus-visible,
.dashboard input:focus-visible,
.dashboard select:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 2px;
}
</style>
