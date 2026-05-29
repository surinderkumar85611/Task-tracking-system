import { defineStore } from "pinia";

export const useThemeStore = defineStore("theme", {
  state: () => ({
    isDark: localStorage.getItem("theme") !== "light",
  }),

  getters: {
    themeClass: (state) =>
      state.isDark ? "theme-dark" : "theme-light",
  },

  actions: {
    toggleTheme() {
      this.isDark = !this.isDark;

      localStorage.setItem(
        "theme",
        this.isDark ? "dark" : "light"
      );
    },
  },
});