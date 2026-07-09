<template>
  <div class="layout">
    <Sidebar
      :is-collapsed="isSidebarCollapsed"
      :active-page="activePage"
      @toggle-sidebar="toggleSidebar"
      @navigate="handleNavigation"
      @logout="$emit('logout')"
    />

    <main class="content">
      <Topbar
        @logout="$emit('logout')"
        @navigate="handleNavigation"
      />

      <div class="page-content">
        <slot />
      </div>
    </main>
  </div>
</template>

<script>
import Sidebar from "../Components/Sidebar.vue"
import Topbar from "../Components/Topbar.vue"

export default {
  name: "AppLayout",

  components: {
    Sidebar,
    Topbar
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
      isSidebarCollapsed: false,
      usuario: JSON.parse(localStorage.getItem("usuario"))
    }
  },

  methods: {
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed
    },

    // FIX: recibe payload completo { page, catalog, report } y lo emite completo hacia App.vue
    handleNavigation(payload) {
      const page = typeof payload === "string" ? payload : payload.page

      // SOLO ADMIN
      if (page === "usuarios" && this.usuario.id_tipo != 1) {
        alert("No autorizado")
        return
      }

      // ADMIN Y TECNICO
      if (page === "activos" && ![1, 2].includes(this.usuario.id_tipo)) {
        alert("No autorizado")
        return
      }

      this.$emit("navigate", payload)
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
  padding: 0;
  transition: all 0.35s ease;
  position: relative;
  overflow-y: auto;
}

.page-content {
  padding: 34px;
}
</style>
