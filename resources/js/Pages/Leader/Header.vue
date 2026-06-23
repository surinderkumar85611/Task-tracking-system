<template>
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-50">
        <div class="flex-1 max-w-md">
            <input 
                type="text" 
                placeholder="Search projects..." 
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <div class="flex items-center gap-6">
            <button @click="$emit('open-notifications')" class="relative p-2 hover:bg-gray-100 rounded-full">
                <span>🔔</span>
                <span v-if="notificationStore.unreadCount > 0" class="absolute top-0 right-0 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">
                    {{ notificationStore.unreadCount }}
                </span>
            </button>

            <div class="relative">
                <button @click="showProfileMenu = !showProfileMenu" class="flex items-center gap-2 font-medium">
                    {{ page.props.auth?.user?.name || 'User' }}
                </button>
                
                <div v-if="showProfileMenu" v-click-outside="closeProfileMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border py-1">
                    <button @click="$emit('logout')" class="block w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useNotificationStore } from '@/Stores/notificationStore';

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue', 'open-notifications', 'logout']);

const page = usePage();
const notificationStore = useNotificationStore();
const showProfileMenu = ref(false);

const closeProfileMenu = () => { showProfileMenu.value = false; };

// Custom Click-Outside Directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event);
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
    },
};
</script>