import { defineStore } from 'pinia';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

export const useNotificationStore = defineStore('notification', () => {
    const currentTickTime = ref(Date.now());
    const showBellDropdown = ref(false);
    const workspaceProjects = ref([]);
    let timerIntervalId = null;
    let removeInertiaListener = null;

    const setProjectsSource = (data) => {
        if (data) {
            workspaceProjects.value = data;
        }
    };

    onMounted(() => {
        if (!timerIntervalId) {
            timerIntervalId = setInterval(() => {
                currentTickTime.value = Date.now();
            }, 1000);
        }

        const page = usePage();
        if (page.props.projects) {
            setProjectsSource(page.props.projects);
        }

        removeInertiaListener = router.on('success', (event) => {
            if (event.detail.page.props.projects) {
                setProjectsSource(event.detail.page.props.projects);
            }
        });
    });

    onBeforeUnmount(() => {
        if (timerIntervalId) clearInterval(timerIntervalId);
        if (removeInertiaListener) removeInertiaListener();
    });

    const activeUrgentTasks = computed(() => {
        const urgentList = [];
        if (!workspaceProjects.value || !Array.isArray(workspaceProjects.value)) return urgentList;
        workspaceProjects.value.forEach(project => {
            if (project.tasks) {
                project.tasks.forEach(task => {
                    if (task.allocated_duration && task.timer_started_at) {
                        const startTimestamp = new Date(task.timer_started_at).getTime();
                        const totalDurationMs = task.allocated_duration * 60 * 1000;
                        const endTimestamp = startTimestamp + totalDurationMs;
                        const remainingMs = endTimestamp - currentTickTime.value;
                        if (remainingMs > 0 && remainingMs <= 15 * 60 * 1000) {
                            urgentList.push(task);
                        }
                    }
                });
            }
        });
        return urgentList;
    });

    const getLiveTaskMetrics = (task) => {
        if (!task.allocated_duration || !task.timer_started_at) return { string: "00:00", color: "#7e8299" };
        const startTimestamp = new Date(task.timer_started_at).getTime();
        const remainingMs = (startTimestamp + (task.allocated_duration * 60 * 1000)) - currentTickTime.value;
        if (remainingMs <= 0) return { string: "Done", color: "#ef4444" };
        const totalSeconds = Math.floor(remainingMs / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        let displayString = "";
        if (hours > 0) displayString += `${hours}h `;
        displayString += `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        return { string: displayString, color: "#ef4444" };
    };

    return {
        showBellDropdown,
        workspaceProjects,
        activeUrgentTasks,
        setProjectsSource,
        getLiveTaskMetrics
    };
});
