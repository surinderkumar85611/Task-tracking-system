<template>

    <Head title="My Projects" />
    <div class="dashboard" :class="theme.themeClass">

        <Sidebar />

        <main class="main-content">
            <div class="content-wrapper">

                <header class="header">
                    <div>
                        <h1>My Projects</h1>
                        <p>Projects you're contributing to and their task breakdown.</p>
                    </div>

                    <div class="header-right">
                        <div class="search-wrap">
                            <svg class="search-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="9" cy="9" r="6.5" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M17 17L13.5 13.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                            <input type="text" v-model="search" placeholder="Search projects..." class="search-box" />
                        </div>

                        <div class="notif-wrap">
                            <button
                                class="theme-btn notif-btn"
                                :class="{ 'has-notes': tasksWithNotes.length > 0 }"
                                title="Chat and updates"
                                @click="toggleNotifDropdown"
                            >
                                💬
                                <span v-if="tasksWithNotes.length" class="notif-badge">{{ tasksWithNotes.length }}</span>
                            </button>

                            <div class="notif-dropdown" v-if="showNotifDropdown" @click.stop>
                                <div class="notif-dropdown-header">
                                    <span>💬 Chat &amp; Updates</span>
                                    <button class="notif-close" @click="showNotifDropdown = false">✕</button>
                                </div>

                                <div class="notif-list" v-if="tasksWithNotes.length">
                                    <button
                                        v-for="entry in tasksWithNotes"
                                        :key="entry.task.id"
                                        class="notif-item"
                                        @click="openFromNotif(entry)"
                                    >
                                        <span class="notif-item-icon">🆘</span>
                                        <span class="notif-item-body">
                                            <span class="notif-item-title">{{ entry.task.title }}</span>
                                            <span class="notif-item-sub">{{ entry.project.name }} · {{ entry.task.notes.length }} update{{ entry.task.notes.length === 1 ? '' : 's' }}</span>
                                        </span>
                                    </button>
                                </div>

                                <div class="notif-empty" v-else>
                                    No chat updates yet
                                </div>
                            </div>
                        </div>

                        <button class="theme-btn" @click="theme.toggleTheme">
                            {{ theme.isDark ? "☀️" : "🌙" }}
                        </button>
                    </div>
                </header>

                <section class="stats-grid">

                    <div class="stat-card projects-card">
                        <div class="stat-icon-badge">📁</div>
                        <span class="stat-label">Total Projects</span>
                        <h2 class="stat-value">{{ totalProjects }}</h2>
                        <small class="stat-subtitle">Projects you're assigned to</small>
                    </div>

                    <div class="stat-card in-progress-card">
                        <div class="stat-icon-badge">🔄</div>
                        <span class="stat-label">In Progress</span>
                        <h2 class="stat-value">{{ inProgressCount }}</h2>
                        <small class="stat-subtitle">Tasks currently active</small>
                    </div>

                    <div class="stat-card completed-card">
                        <div class="stat-icon-badge">✅</div>
                        <span class="stat-label">Completed</span>
                        <h2 class="stat-value">{{ completedCount }}</h2>
                        <small class="stat-subtitle">Tasks finished</small>
                    </div>

                    <div class="stat-card pending-card">
                        <div class="stat-icon-badge">⏳</div>
                        <span class="stat-label">Pending</span>
                        <h2 class="stat-value">{{ pendingCount }}</h2>
                        <small class="stat-subtitle">Tasks not yet started</small>
                    </div>

                </section>

                <section class="project-perf-card">
                    <div class="perf-donut-block">
                        <div class="perf-block-header">Overall Completion</div>
                        <div class="donut-wrap">
                            <svg viewBox="0 0 120 120" class="donut-svg">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--border-deep)" stroke-width="12" />
                                <circle
                                    v-for="(seg, idx) in donutSegments" :key="idx"
                                    cx="60" cy="60" r="50" fill="none"
                                    :stroke="seg.color" stroke-width="12"
                                    :stroke-dasharray="seg.dasharray"
                                    :stroke-dashoffset="seg.dashoffset"
                                    transform="rotate(-90 60 60)"
                                />
                            </svg>
                            <div class="donut-center">
                                <strong>{{ completionPercent }}%</strong>
                                <span>Done</span>
                            </div>
                        </div>
                        <div class="donut-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background: var(--c-green)"></span>
                                Completed <b>{{ completedCount }}</b>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: var(--c-blue)"></span>
                                In Progress <b>{{ inProgressCount }}</b>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background: var(--c-amber)"></span>
                                Pending <b>{{ pendingCount }}</b>
                            </div>
                        </div>
                    </div>

                    <div class="perf-divider"></div>

                    <div class="perf-bar-block">
                        <div class="perf-block-header">Projects Assigned Per Month</div>
                        <div class="bar-chart">
                            <div v-for="bucket in monthlyProjects" :key="bucket.label + bucket.year" class="bar-col">
                                <span class="bar-value">{{ bucket.count }}</span>
                                <div class="bar-track">
                                    <div
                                        class="bar-fill"
                                        :style="{ height: (bucket.count / maxMonthlyProjectCount) * 100 + '%' }"
                                    ></div>
                                </div>
                                <span class="bar-label">{{ bucket.label }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="projects-list">

                    <div v-if="!filteredProjects.length" class="empty-board-state">
                        <h3>No projects match your search</h3>
                    </div>

                    <div v-for="project in filteredProjects" :key="project.id" class="project-card">

                        <div class="project-card-top">
                            <div class="project-title-block">
                                <h2>{{ project.name }}</h2>
                                <span class="status-pill" :class="statusClass(effectiveProjectStatus(project))">
                                    {{ effectiveProjectStatus(project) }}
                                </span>
                            </div>

                            <div class="project-progress-block">
                                <span class="progress-percent">{{ projectProgress(project) }}%</span>
                                <span class="progress-caption">Complete</span>
                            </div>
                        </div>

                        <p class="project-desc" v-if="project.description">
                            {{ project.description }}
                        </p>

                        <div class="mini-progress">
                            <div class="mini-progress-fill" :style="{ width: projectProgress(project) + '%' }"></div>
                        </div>

                        <div class="project-meta-row">
                            <span v-if="project.team_leader">
                                👤 Led by {{ project.team_leader.first_name }} {{ project.team_leader.last_name }}
                            </span>
                            <span>📅 Due: {{ project.deadline || 'No deadline' }}</span>
                            <span>📋 {{ (project.tasks || []).length }} Tasks</span>
                            <span>✅ {{ projectCompletedCount(project) }} Completed</span>
                        </div>

                        <div class="project-tasks-table" v-if="(project.tasks || []).length">
                            <div class="tasks-table-head">
                                <span>Task</span>
                                <span>Assigned</span>
                                <span>Priority</span>
                                <span>Status</span>
                                <span>Due</span>
                            </div>

                            <div v-for="task in project.tasks" :key="task.id" class="tasks-row">
                                <span class="task-title-cell">{{ task.title }}</span>

                                <span class="assignee-cell">
                                    <span
                                        v-for="member in (task.assigned_members || [])"
                                        :key="member.id"
                                        class="mini-avatar"
                                        :title="`${member.first_name} ${member.last_name || ''}`.trim()"
                                    >
                                        {{ member.first_name?.charAt(0)?.toUpperCase() || '?' }}
                                    </span>
                                    <span v-if="!(task.assigned_members || []).length" class="unassigned-label">
                                        Unassigned
                                    </span>
                                </span>

                                <span class="priority-badge" :class="priorityClass(task.priority)">
                                    {{ task.priority || 'Normal' }}
                                </span>

                                <span class="status-pill" :class="statusClass(task.status)">
                                    {{ task.status || 'Todo' }}
                                </span>

                                <span class="due-date-cell">
                                    {{ task.due_date || task.deadline || '—' }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="empty-state-inline">
                            No tasks in this project yet.
                        </div>

                    </div>

                </div>

            </div>
        </main>

        <div class="updates-sidebar-overlay" :class="{ open: showUpdatesSidebarPane }" @click="closeUpdatesSidebar">
            <div class="updates-sidebar-panel" @click.stop>
                <div class="sidebar-panel-header">
                    <div class="panel-header-left">
                        <span class="panel-task-icon">🆘</span>
                        <div>
                            <h2>{{ activeTaskForUpdates?.title || 'Task Chat' }}</h2>
                            <p class="panel-subtitle">Project: {{ activeProjectForUpdates?.name || '—' }}</p>
                        </div>
                    </div>
                    <button class="close-panel-btn" @click="closeUpdatesSidebar">✕</button>
                </div>

                <div class="sidebar-panel-body">
                    <div class="notes-display-box">
                        <label>📌 Updates Timeline</label>

                        <div
                            ref="messagesContainer"
                            v-if="activeTaskForUpdates?.notes && activeTaskForUpdates.notes.length > 0"
                            class="messages-thread-wrapper"
                        >
                            <div
                                v-for="(note, index) in activeTaskForUpdates.notes"
                                :key="note.id || index"
                                class="chat-message"
                            >
                                <div class="chat-bubble-meta">
                                    <span class="chat-bubble-author">
                                        <span class="mini-avatar chat-variant">
                                            {{ note.sender ? note.sender.charAt(0).toUpperCase() : 'A' }}
                                        </span>
                                        <span class="chat-author-name">{{ note.sender || 'System User' }}</span>
                                    </span>
                                    <span class="chat-bubble-time">{{ formatDate(note.created_at) }}</span>
                                </div>

                                <div v-if="note.reply_to" class="reply-reference">
                                    <div class="reply-box">
                                        <template v-if="getReplyMessage(note.reply_to)">
                                            <div class="reply-author">{{ getReplyMessage(note.reply_to).sender }}</div>
                                            <div class="reply-text" v-html="getReplyPreview(getReplyMessage(note.reply_to).text)"></div>
                                        </template>
                                    </div>
                                </div>

                                <div class="chat-bubble-body ck-content" v-html="note.text"></div>

                                <div class="reaction-summary">
                                    <div v-for="(users, emoji) in (note.reactions || {})" :key="emoji" class="reaction-wrapper">
                                        <button v-if="Array.isArray(users) && users.length" class="reaction-chip" @click="addReaction(note, emoji)">
                                            {{ emoji }} {{ users.length }}
                                        </button>
                                        <div class="reaction-tooltip">
                                            <div class="tooltip-title">Reacted by</div>
                                            <div v-for="user in users" :key="user.user_id" class="tooltip-user">{{ user.user }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="message-hover-actions">
                                    <div class="reaction-picker">
                                        <button v-for="emoji in ['👍', '❤️', '😂', '🎉', '😮', '😢']" :key="emoji" @click="addReaction(note, emoji)">
                                            {{ emoji }}
                                        </button>
                                    </div>
                                    <button class="reply-btn" @click="startReply(note)">↩ Reply</button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="notes-empty">
                            💬 No updates logged yet. Write a message below!
                        </div>
                    </div>

                    <div v-if="replyingTo" class="replying-box">
                        Replying to: <b>{{ replyingTo.sender }}</b>
                        <button @click="cancelReply">✕</button>
                    </div>

                    <div class="notes-editor-section">
                        <label class="editor-label">Write a message:</label>
                        <ckeditor :editor="editor" v-model="updatesDraftText" :config="editorConfig" />
                    </div>
                </div>

                <div class="sidebar-panel-footer">
                    <button class="btn-flat-cancel" @click="closeUpdatesSidebar">Close</button>
                    <button class="monday-btn-primary" @click="saveTaskNotesUpdate">Send Message</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import { useToast } from "vue-toastification";
import Sidebar from "./Sidebar.vue";
import { useThemeStore } from "../../stores/theme.js";
import { Head } from "@inertiajs/vue3";
import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Heading,
    Link,
    List,
    BlockQuote,
    Image,
    ImageToolbar,
    ImageUpload,
    ImageResize,
    MediaEmbed,
    Table,
    TableToolbar,
    Alignment,
    Font,
    Indent,
    SourceEditing
} from 'ckeditor5';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';

class LaravelUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then(file => {
            const data = new FormData();
            data.append('upload', file);

            return fetch('/ckeditor/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            })
                .then(res => res.json())
                .then(res => ({ default: res.url }));
        });
    }

    abort() { }
}

function uploadPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = loader => {
        return new LaravelUploadAdapter(loader);
    };
}

const theme = useThemeStore();
const toast = useToast();
const editor = ClassicEditor;
const ckeditor = Ckeditor;

const editorConfig = {
    licenseKey: 'GPL',
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
        ]
    },
    extraPlugins: [uploadPlugin],
    plugins: [
        Essentials, Paragraph, Bold, Italic, Underline, Heading, Link, List, BlockQuote,
        Image, ImageToolbar, ImageUpload, ImageResize, MediaEmbed, Table, TableToolbar,
        Alignment, Font, Indent, SourceEditing
    ],
    toolbar: {
        items: [
            'undo', 'redo', '|', 'heading', '|', 'fontFamily', 'fontSize', '|',
            'fontColor', 'fontBackgroundColor', '|', 'bold', 'italic', 'underline', '|',
            'alignment', '|', 'link', '|', 'bulletedList', 'numberedList', '|',
            'insertImage', '|', 'insertTable', '|', 'blockQuote', '|', 'sourceEditing'
        ],
        shouldNotGroupWhenFull: false
    },
    image: {
        toolbar: ['imageTextAlternative', 'imageResize', 'imageStyle:inline', 'imageStyle:block']
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
    },
    simpleUpload: {
        uploadUrl: '/ckeditor/upload',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }
};

const props = defineProps({
    projects: { type: Array, default: () => [] },
});

const search = ref("");

const filteredProjects = computed(() => {
    if (!search.value) return props.projects || [];
    const term = search.value.toLowerCase();
    return (props.projects || []).filter(p => p.name?.toLowerCase().includes(term));
});

const allTasks = computed(() => {
    return (props.projects || []).flatMap(p => p.tasks || []);
});

const statusClass = (status) => {
    return (status || "").toLowerCase().replace(/\s+/g, "-");
};

const priorityClass = (priority) => {
    return (priority || "normal").toLowerCase();
};

const isCompleted = (task) => (task.status || "").toLowerCase() === "completed";

const isInProgress = (task) => {
    const s = (task.status || "").toLowerCase();
    return s === "in progress" || s === "in_progress" || s === "in-progress";
};

const isPending = (task) => {
    const s = (task.status || "").toLowerCase();
    return s === "todo" || s === "planning" || !s;
};

const totalProjects = computed(() => (props.projects || []).length);
const inProgressCount = computed(() => allTasks.value.filter(isInProgress).length);
const completedCount = computed(() => allTasks.value.filter(isCompleted).length);
const pendingCount = computed(() => allTasks.value.filter(isPending).length);
const totalTasks = computed(() => allTasks.value.length);

const projectCompletedCount = (project) => (project.tasks || []).filter(isCompleted).length;

const projectProgress = (project) => {
    const total = (project.tasks || []).length;
    if (!total) return 0;
    return Math.round((projectCompletedCount(project) / total) * 100);
};

const effectiveProjectStatus = (project) => {
    const total = (project.tasks || []).length;
    if (!total) return project.status || 'Planning';

    const completed = projectCompletedCount(project);
    if (completed === total) return 'Completed';
    if (completed > 0) return 'In Progress';

    const anyInProgress = (project.tasks || []).some(isInProgress);
    return anyInProgress ? 'In Progress' : 'Planning';
};

const completionPercent = computed(() => {
    if (!totalTasks.value) return 0;
    return Math.round((completedCount.value / totalTasks.value) * 100);
});

const donutSegments = computed(() => {
    const total = totalTasks.value || 1;
    const circumference = 2 * Math.PI * 50;

    const parts = [
        { value: completedCount.value, color: 'var(--c-green)' },
        { value: inProgressCount.value, color: 'var(--c-blue)' },
        { value: pendingCount.value, color: 'var(--c-amber)' },
    ];

    let cumulative = 0;
    return parts.map(part => {
        const length = (part.value / total) * circumference;
        const seg = {
            color: part.color,
            dasharray: `${length} ${circumference - length}`,
            dashoffset: -cumulative,
        };
        cumulative += length;
        return seg;
    });
});

const monthlyProjects = computed(() => {
    const now = new Date();
    const buckets = [];

    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        buckets.push({
            year: d.getFullYear(),
            month: d.getMonth(),
            label: d.toLocaleString('default', { month: 'short' }),
            count: 0,
        });
    }

    (props.projects || []).forEach(project => {
        const dateSource = project.created_at || project.start_date;
        if (!dateSource) return;
        const d = new Date(dateSource);
        const bucket = buckets.find(b => b.year === d.getFullYear() && b.month === d.getMonth());
        if (bucket) bucket.count++;
    });

    return buckets;
});

const maxMonthlyProjectCount = computed(() => Math.max(1, ...monthlyProjects.value.map(b => b.count)));

const tasksWithNotes = computed(() => {
    const entries = [];
    (props.projects || []).forEach(project => {
        (project.tasks || []).forEach(task => {
            if (task.notes && task.notes.length > 0) {
                entries.push({ task, project });
            }
        });
    });
    return entries;
});

const showNotifDropdown = ref(false);

const toggleNotifDropdown = () => {
    showNotifDropdown.value = !showNotifDropdown.value;
};

const closeNotifDropdown = (e) => {
    if (!e.target.closest('.notif-wrap')) {
        showNotifDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeNotifDropdown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeNotifDropdown);
});

const openFromNotif = (entry) => {
    showNotifDropdown.value = false;
    openUpdatesSidebar(entry.task, entry.project);
};

const showUpdatesSidebarPane = ref(false);
const activeTaskForUpdates = ref(null);
const activeProjectForUpdates = ref(null);
const updatesDraftText = ref("");
const messagesContainer = ref(null);
const replyingTo = ref(null);

const markTaskAsRead = (task) => {
    if (task.is_read) return;

    task.is_read = true;

    router.put(`/task/${task.id}`, {
        id: task.id,
        project_id: task.project_id,
        title: task.title,
        member_id: task.member_id,
        status: task.status,
        priority: task.priority,
        deadline: task.due_date,
        allocated_duration: task.allocated_duration,
        timer_started_at: task.timer_started_at,
        is_read: true,
    }, {
        preserveScroll: true,
        onError: () => {
            task.is_read = false;
            toast.error("Couldn't mark task as read.");
        },
    });
};

const openUpdatesSidebar = (task, project) => {
    activeTaskForUpdates.value = task;
    activeProjectForUpdates.value = project || null;
    updatesDraftText.value = "";
    showUpdatesSidebarPane.value = true;

    markTaskAsRead(task);
};

const closeUpdatesSidebar = () => {
    showUpdatesSidebarPane.value = false;
    activeTaskForUpdates.value = null;
    activeProjectForUpdates.value = null;
    updatesDraftText.value = "";
    replyingTo.value = null;
};

const startReply = (note) => {
    replyingTo.value = note;
};

const cancelReply = () => {
    replyingTo.value = null;
};

const addReaction = async (note, reaction) => {
    try {
        const response = await axios.post(`/tasks/${activeTaskForUpdates.value.id}/react`, {
            message_id: note.id,
            reaction: reaction,
        });
        activeTaskForUpdates.value.notes = response.data.notes;
    } catch (error) {
        toast.error(error.response?.data?.message || "Failed to add reaction");
    }
};

const saveTaskNotesUpdate = () => {
    if (!activeTaskForUpdates.value || !updatesDraftText.value.trim()) return;

    const messageText = updatesDraftText.value.trim();
    const task = activeTaskForUpdates.value;

    const payload = {
        id: task.id,
        project_id: task.project_id,
        title: task.title,
        member_id: task.member_id,
        status: task.status,
        priority: task.priority,
        deadline: task.due_date,
        notes: messageText,
        reply_to: replyingTo.value ? replyingTo.value.id : null,
    };

    router.put(`/task/${task.id}`, payload, {
        preserveScroll: true,
        onSuccess: async () => {
            if (!task.notes) task.notes = [];

            task.notes.unshift({
                id: Date.now(),
                sender: 'You',
                text: messageText,
                reply_to: replyingTo.value ? replyingTo.value.id : null,
                reactions: {},
                created_at: new Date().toISOString(),
            });

            updatesDraftText.value = "";
            replyingTo.value = null;
            await nextTick();

            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = 0;
            }
        },
        onError: () => toast.error("Failed to send message."),
    });
};

const formatDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const getReplyMessage = (replyId) => {
    if (!activeTaskForUpdates.value?.notes) return null;
    return activeTaskForUpdates.value.notes.find(note => note.id == replyId);
};

const getReplyPreview = (html) => {
    if (!html) return "";
    const div = document.createElement("div");
    div.innerHTML = html;
    const image = div.querySelector("img");
    if (image) return `🖼️ Image attachment`;
    return div.textContent || div.innerText || "";
};
</script>

<style scoped>

.dashboard.theme-dark {
    --dashboard-bg: #222736;
    --panel-bg: #2a2f42;
    --card-inner-bg: #262b3d;
    --card-inner-hover: #313749;
    --input-element-bg: #323a4f;
    --border-subtle: rgba(255, 255, 255, 0.07);
    --border-deep: rgba(255, 255, 255, 0.12);
    --border-divider: rgba(255, 255, 255, 0.07);
    --text-main: #e4e6ef;
    --text-header: #f6f7fb;
    --text-muted: #8590a6;
    --shadow-cards: rgba(0, 0, 0, 0.28);
    --shadow-stats: rgba(0, 0, 0, 0.22);
    --shadow-stats-hover: rgba(0, 0, 0, 0.34);
    --accent: #556ee6;
    --accent-soft: rgba(85, 110, 230, 0.16);
    --c-blue: #556ee6;
    --c-violet: #8b6ee8;
    --c-green: #34c38f;
    --c-amber: #f1b44c;
    --c-cyan: #50a5f1;
    --c-red: #f46a6a;
}

.dashboard.theme-light {
    --dashboard-bg: #eef0f7;
    --panel-bg: #ffffff;
    --card-inner-bg: #f7f8fb;
    --card-inner-hover: #eef0f6;
    --input-element-bg: #f2f3f8;
    --border-subtle: rgba(33, 37, 61, 0.07);
    --border-deep: rgba(33, 37, 61, 0.1);
    --border-divider: rgba(33, 37, 61, 0.06);
    --text-main: #33374d;
    --text-header: #22263d;
    --text-muted: #878ea3;
    --shadow-cards: rgba(56, 65, 109, 0.07);
    --shadow-stats: rgba(56, 65, 109, 0.05);
    --shadow-stats-hover: rgba(56, 65, 109, 0.1);
    --accent: #556ee6;
    --accent-soft: rgba(85, 110, 230, 0.08);
    --c-blue: #556ee6;
    --c-violet: #8b6ee8;
    --c-green: #34c38f;
    --c-amber: #f1b44c;
    --c-cyan: #50a5f1;
    --c-red: #e05555;
}


.dashboard {
    display: flex;
    height: 100vh;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: var(--dashboard-bg);
    color: var(--text-main);
    overflow: hidden;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.main-content {
    flex: 1;
    overflow-y: auto;
    width: 100%;
    height: 100%;
}

.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
    padding: 28px 36px 56px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.header h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 3px 0;
    letter-spacing: -0.2px;
    color: var(--text-header);
}

.header p {
    margin: 0;
    color: var(--text-muted);
    font-size: 13px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
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
    width: 220px;
    padding: 8px 13px 8px 34px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    background: var(--input-element-bg);
    color: var(--text-main);
    outline: none;
    font-size: 13px;
    transition: all 0.2s ease;
}

.search-box::placeholder {
    color: var(--text-muted);
}

.search-box:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.theme-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--border-subtle);
    cursor: pointer;
    background: var(--input-element-bg);
    color: var(--text-main);
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.theme-btn:hover {
    background: var(--card-inner-hover);
    border-color: var(--border-deep);
}

.notif-wrap {
    position: relative;
}

.notif-btn {
    position: relative;
    color: var(--text-muted);
}

.notif-btn.has-notes {
    color: var(--accent);
    border-color: var(--accent);
    background: var(--accent-soft);
}

.notif-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 17px;
    height: 17px;
    padding: 0 4px;
    border-radius: 999px;
    background: var(--c-red);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--dashboard-bg);
}

.notif-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 300px;
    max-height: 360px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    box-shadow: 0 12px 30px var(--shadow-cards);
    z-index: 200;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.notif-dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    border-bottom: 1px solid var(--border-divider);
    font-size: 13px;
    font-weight: 700;
    color: var(--text-header);
    background: var(--card-inner-bg);
}

.notif-close {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 13px;
}

.notif-close:hover {
    color: var(--text-main);
}

.notif-list {
    overflow-y: auto;
    padding: 6px;
}

.notif-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: background 0.15s ease;
}

.notif-item:hover {
    background: var(--card-inner-hover);
}

.notif-item-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.notif-item-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.notif-item-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notif-item-sub {
    font-size: 11.5px;
    color: var(--text-muted);
}

.notif-empty {
    padding: 26px 14px;
    text-align: center;
    color: var(--text-muted);
    font-size: 13px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

@media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .stats-grid { grid-template-columns: 1fr; }
}

.stat-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 18px 20px 20px;
    box-shadow: 0 2px 6px var(--shadow-stats);
    transition: all 0.2s ease;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    height: 3px;
}

.projects-card::before { background: var(--c-violet); }
.in-progress-card::before { background: var(--c-blue); }
.completed-card::before { background: var(--c-green); }
.pending-card::before { background: var(--c-amber); }

.stat-card:hover {
    border-color: var(--border-deep);
    box-shadow: 0 6px 16px -4px var(--shadow-stats-hover);
    transform: translateY(-2px);
}

.stat-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
    margin-bottom: 14px;
}

.projects-card .stat-icon-badge { background: rgba(139, 110, 232, 0.14); }
.in-progress-card .stat-icon-badge { background: rgba(85, 110, 230, 0.14); }
.completed-card .stat-icon-badge { background: rgba(52, 195, 143, 0.14); }
.pending-card .stat-icon-badge { background: rgba(241, 180, 76, 0.16); }

.stat-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}

.stat-value {
    font-size: 25px;
    font-weight: 700;
    color: var(--text-header);
    margin: 6px 0 0 0;
    line-height: 1.2;
    letter-spacing: -0.3px;
}

.stat-subtitle {
    display: block;
    margin-top: 6px;
    color: var(--text-muted);
    font-size: 11px;
    line-height: 1.4;
}

.project-perf-card {
    display: flex;
    align-items: stretch;
    gap: 28px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 22px 26px;
    box-shadow: 0 2px 6px var(--shadow-cards);
    margin-bottom: 24px;
}

@media (max-width: 800px) {
    .project-perf-card { flex-direction: column; }
}

.perf-block-header {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-header);
    margin-bottom: 16px;
}

.perf-donut-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 0 0 210px;
}

.perf-divider {
    width: 1px;
    background: var(--border-divider);
    align-self: stretch;
}

@media (max-width: 800px) {
    .perf-divider { width: 100%; height: 1px; }
}

.perf-bar-block {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.donut-wrap {
    position: relative;
    width: 130px;
    height: 130px;
    margin: 0 auto 16px;
}

.donut-svg {
    width: 100%;
    height: 100%;
}

.donut-svg circle {
    transition: stroke-dasharray 0.4s ease, stroke-dashoffset 0.4s ease;
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
    font-size: 21px;
    font-weight: 700;
    color: var(--text-header);
}

.donut-center span {
    font-size: 10.5px;
    color: var(--text-muted);
    font-weight: 500;
}

.donut-legend {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: var(--text-muted);
}

.legend-item b {
    margin-left: auto;
    color: var(--text-main);
    font-weight: 700;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
    height: 150px;
    flex: 1;
}

.bar-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    height: 100%;
}

.bar-value {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-header);
    margin-bottom: 6px;
}

.bar-track {
    flex: 1;
    width: 26px;
    background: var(--card-inner-bg);
    border-radius: 6px;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
}

.bar-fill {
    width: 100%;
    background: linear-gradient(180deg, var(--c-blue), var(--c-violet));
    border-radius: 6px;
    transition: height 0.4s ease;
    min-height: 3px;
}

.bar-label {
    margin-top: 8px;
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
}

.projects-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.project-card {
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 6px var(--shadow-cards);
}

.project-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 10px;
}

.project-title-block {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.project-title-block h2 {
    font-size: 17px;
    font-weight: 700;
    margin: 0;
    color: var(--text-header);
    letter-spacing: -0.2px;
}

.project-progress-block {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}

.progress-percent {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-header);
    line-height: 1;
}

.progress-caption {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 3px;
}

.project-desc {
    color: var(--text-muted);
    font-size: 13px;
    margin: 0 0 14px 0;
    line-height: 1.5;
}

.mini-progress {
    height: 6px;
    background: var(--border-deep);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 14px;
}

.mini-progress-fill {
    height: 100%;
    background: var(--c-violet);
    border-radius: 6px;
}

.project-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    color: var(--text-muted);
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 18px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--border-divider);
}

.status-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 5px;
    text-transform: capitalize;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    flex-shrink: 0;
    white-space: nowrap;
}

.status-pill.in-progress { background: rgba(85, 110, 230, 0.12); color: var(--c-blue); }
.status-pill.completed { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }
.status-pill.todo,
.status-pill.planning { background: rgba(241, 180, 76, 0.15); color: #b9822e; }

.priority-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 5px;
    text-transform: capitalize;
    background: rgba(137, 147, 171, 0.14);
    color: var(--text-muted);
    white-space: nowrap;
}

.priority-badge.high { background: rgba(244, 106, 106, 0.12); color: var(--c-red); }
.priority-badge.medium { background: rgba(241, 180, 76, 0.15); color: #b9822e; }
.priority-badge.low { background: rgba(52, 195, 143, 0.12); color: var(--c-green); }

.project-tasks-table {
    display: flex;
    flex-direction: column;
}

.tasks-table-head {
    display: grid;
    grid-template-columns: 1.6fr 1.3fr 0.9fr 0.9fr 0.9fr;
    align-items: center;
    padding: 0 4px 10px;
    border-bottom: 1px solid var(--border-divider);
    margin-bottom: 2px;
    gap: 10px;
}

.tasks-table-head span {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
}

.tasks-row {
    display: grid;
    grid-template-columns: 1.6fr 1.3fr 0.9fr 0.9fr 0.9fr;
    align-items: center;
    padding: 12px 4px;
    border-bottom: 1px solid var(--border-divider);
    gap: 10px;
    transition: background 0.15s ease;
}

.tasks-row:last-child {
    border-bottom: none;
}

.tasks-row:hover {
    background: var(--card-inner-hover);
}

.task-title-cell {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.assignee-cell {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.mini-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--c-cyan);
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid var(--panel-bg);
    margin-left: -6px;
}

.mini-avatar:first-child {
    margin-left: 0;
}

.mini-avatar:nth-child(even) { background: var(--c-blue); }
.mini-avatar:nth-child(3n) { background: var(--c-violet); }
.mini-avatar:nth-child(4n) { background: var(--c-amber); }

.unassigned-label {
    font-size: 12px;
    color: var(--text-muted);
    font-style: italic;
}

.due-date-cell {
    font-size: 12.5px;
    color: var(--text-muted);
    white-space: nowrap;
}

.empty-state-inline {
    text-align: center;
    padding: 20px 0 4px;
    color: var(--text-muted);
    font-size: 13px;
}

.empty-board-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
}

.empty-board-state h3 {
    font-weight: 500;
    font-size: 15px;
    margin: 0;
}

@media (max-width: 760px) {
    .tasks-table-head { display: none; }
    .tasks-row {
        grid-template-columns: 1fr;
        gap: 6px;
        padding: 14px 4px;
    }
}


.updates-sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0);
    z-index: 1500;
    display: flex;
    justify-content: flex-end;
    pointer-events: none;
    transition: background-color 0.3s ease;
}

.updates-sidebar-overlay.open {
    background: rgba(15, 23, 42, 0.45);
    pointer-events: auto;
}

.updates-sidebar-panel {
    width: 640px;
    max-width: 100%;
    height: 100%;
    background: var(--panel-bg);
    border-left: 1px solid var(--border-subtle);
    box-shadow: -8px 0 30px var(--shadow-cards);
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.updates-sidebar-overlay.open .updates-sidebar-panel {
    transform: translateX(0);
}

.sidebar-panel-header {
    padding: 22px 24px;
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: var(--card-inner-bg);
}

.panel-header-left {
    display: flex;
    gap: 14px;
}

.panel-task-icon {
    font-size: 22px;
}

.sidebar-panel-header h2 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-header);
    margin: 0;
}

.panel-subtitle {
    font-size: 12.5px;
    color: var(--text-muted);
    margin-top: 4px;
}

.close-panel-btn {
    background: transparent;
    border: none;
    font-size: 16px;
    color: var(--text-muted);
    cursor: pointer;
}

.close-panel-btn:hover {
    color: var(--text-main);
}

.sidebar-panel-body {
    padding: 22px 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 18px;
    overflow: hidden;
}

.sidebar-panel-body label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    display: block;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.notes-display-box {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}

.messages-thread-wrapper {
    flex: 1;
    overflow-y: auto;
    padding-right: 6px;
    min-height: 0;
}

.chat-message {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 12px;
    position: relative;
    transition: border-color 0.15s ease;
}

.chat-message:hover {
    border-color: var(--accent);
}

.chat-bubble-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-divider);
    padding-bottom: 8px;
    margin-bottom: 8px;
}

.chat-bubble-author {
    display: flex;
    align-items: center;
    gap: 8px;
}

.mini-avatar.chat-variant {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--accent);
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-author-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
}

.chat-bubble-time {
    font-size: 11px;
    color: var(--text-muted);
}

.chat-bubble-body {
    font-size: 13px;
    color: var(--text-main);
    line-height: 1.5;
    word-break: break-word;
}

.notes-empty {
    color: var(--text-muted);
    font-style: italic;
    font-size: 13px;
    text-align: center;
    padding: 30px 10px;
    background: var(--card-inner-bg);
    border: 1px dashed var(--border-deep);
    border-radius: 10px;
}

.reply-reference {
    margin-bottom: 8px;
}

.reply-box {
    background: var(--dashboard-bg);
    border-left: 3px solid var(--c-violet);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12.5px;
}

.reply-author {
    font-weight: 600;
    color: var(--c-violet);
    margin-bottom: 3px;
}

.reply-text {
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.reaction-summary {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.reaction-wrapper {
    position: relative;
}

.reaction-chip {
    border: 1px solid var(--border-subtle);
    background: var(--panel-bg);
    border-radius: 14px;
    padding: 3px 10px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.15s ease;
}

.reaction-chip:hover {
    border-color: var(--accent);
}

.reaction-tooltip {
    position: absolute;
    bottom: 130%;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: 0.15s ease;
    z-index: 999;
}

.reaction-wrapper:hover .reaction-tooltip {
    opacity: 1;
    visibility: visible;
}

.tooltip-title {
    font-weight: 700;
    margin-bottom: 3px;
}

.message-hover-actions {
    position: absolute;
    top: 10px;
    right: 12px;
    display: none;
    align-items: center;
    gap: 6px;
    z-index: 20;
}

.chat-message:hover .message-hover-actions {
    display: flex;
}

.reaction-picker {
    display: flex;
    gap: 2px;
    background: var(--panel-bg);
    border: 1px solid var(--border-subtle);
    border-radius: 20px;
    padding: 3px 6px;
    box-shadow: 0 4px 12px var(--shadow-cards);
}

.reaction-picker button {
    background: transparent;
    border: none;
    font-size: 15px;
    cursor: pointer;
    padding: 3px;
}

.reaction-picker button:hover {
    transform: scale(1.2);
}

.reply-btn {
    background: var(--panel-bg);
    color: var(--text-main);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 4px 12px var(--shadow-cards);
}

.reply-btn:hover {
    background: var(--card-inner-hover);
}

.replying-box {
    background: var(--card-inner-bg);
    border: 1px solid var(--border-subtle);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12.5px;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 6px;
}

.replying-box button {
    margin-left: auto;
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
}

.notes-editor-section {
    flex-shrink: 0;
}

.editor-label {
    display: block;
}

.sidebar-panel-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border-subtle);
    background: var(--card-inner-bg);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-flat-cancel {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    padding: 9px 16px;
}

.btn-flat-cancel:hover {
    color: var(--text-main);
}

.monday-btn-primary {
    background: var(--accent);
    color: #ffffff;
    font-weight: 600;
    font-size: 13px;
    border: none;
    padding: 9px 18px;
    border-radius: 8px;
    cursor: pointer;
}

.monday-btn-primary:hover {
    opacity: 0.9;
}

:deep(.ck-editor__editable_inline) {
    min-height: 140px;
    max-height: 300px;
    color: #1e1e2d !important;
    background-color: #ffffff !important;
    text-align: left !important;
}

:deep(.ck.ck-toolbar) {
    background: #f3f6f9 !important;
    border-color: #e4e6ef !important;
}

:deep(.ck-body-wrapper) {
    z-index: 999999 !important;
}

:deep(.ck-balloon-panel) {
    z-index: 999999 !important;
    position: fixed !important;
}
</style>