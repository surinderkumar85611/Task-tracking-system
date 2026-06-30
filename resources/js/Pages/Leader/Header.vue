<template>
  <header class="header">

    <!-- LEFT SIDE -->
    <div class="header-left">
      <slot name="left">
        <h1>{{ title }}</h1>
        <p v-if="subtitle">{{ subtitle }}</p>
      </slot>
    </div>

    <!-- RIGHT SIDE -->
    <div class="header-right">

      <!-- WORKSPACE (optional) -->
      <div v-if="workspace" class="workspace-label">
        🏢 {{ workspace }}
      </div>

      <!-- SEARCH (optional) -->
      <input
        v-if="showSearch"
        v-model="searchModel"
        type="text"
        :placeholder="searchPlaceholder"
        class="search-box"
      />

      <!-- NOTIFICATIONS (optional) -->
      <div v-if="showNotifications" class="notification-bell-container">

        <button class="icon-btn" @click="toggleNotifications">
          🔔
          <span v-if="unreadCount > 0" class="bell-dot">
            {{ unreadCount }}
          </span>
        </button>

        <div v-if="openNotifications" class="notification-dropdown-panel">
          <slot name="notifications">
            <p>No notifications</p>
          </slot>
        </div>

      </div>

      <!-- THEME -->
      <button class="theme-btn" @click="theme.toggleTheme">
        {{ theme.isDark ? "☀️" : "🌙" }}
      </button>

      <!-- PROFILE -->
      <div class="profile-container" ref="profileRef">

        <img
          src="https://i.pravatar.cc/100"
          class="avatar"
          @click.stop="showProfile = !showProfile"
        />

        <div v-if="showProfile" class="profile-dropdown">
          <button @click="logout">Logout</button>
        </div>

      </div>

    </div>

  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { useThemeStore } from "@/stores/theme";

const theme = useThemeStore();

const props = defineProps({
  title: String,
  subtitle: String,

  workspace: String,

  showSearch: Boolean,
  searchModel: String,
  searchPlaceholder: {
    type: String,
    default: "Search..."
  },

  showNotifications: Boolean,
  unreadCount: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits([
  "update:searchModel"
]);

const showProfile = ref(false);
const openNotifications = ref(false);
const profileRef = ref(null);

const toggleNotifications = () => {
  openNotifications.value = !openNotifications.value;
};

const logout = () => {
  router.post("/logout", {}, {
    replace: true,
    onSuccess: () => {
      window.location.href = "/login";
    }
  });
};

const handleClickOutside = (e) => {
  if (profileRef.value && !profileRef.value.contains(e.target)) {
    showProfile.value = false;
  }
  openNotifications.value = false;
};

onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() => document.removeEventListener("click", handleClickOutside));
</script>