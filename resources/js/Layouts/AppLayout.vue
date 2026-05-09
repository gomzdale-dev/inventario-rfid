<template>
  <div class="layout">
    <Sidebar
      :is-collapsed="isSidebarCollapsed"
      :active-page="activePage"
      @toggle-sidebar="toggleSidebar"
      @navigate="$emit('navigate', $event)"
      @logout="$emit('logout')"
    />

    <main class="content">
      <slot />
    </main>
  </div>
</template>

<script>
import Sidebar from "../Components/Sidebar.vue"

export default {
  name: "AppLayout",
  components: {
    Sidebar
  },
  props: {
    activePage: {
      type: String,
      default: "dashboard"
    }
  },
  emits: ["navigate", "logout"],
  data() {
    return {
      isSidebarCollapsed: false
    }
  },
  methods: {
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed
    }
  }
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f7f3ee;
}

.content {
  flex: 1;
  padding: 34px;
  transition: all 0.35s ease;
}
</style>