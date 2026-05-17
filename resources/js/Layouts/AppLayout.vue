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
      isSidebarCollapsed: false,
      usuario: JSON.parse(localStorage.getItem("usuario"))
    }
  },
  methods: {
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed
    },

  handleNavigation(page) {

    // SOLO ADMIN
    if (
      page === "usuarios" &&
      this.usuario.id_tipo != 1
    ) {

      alert("No autorizado")
      return
    }

    // ADMIN Y TECNICO
    if (
      page === "activos" &&
      ![1,2].includes(this.usuario.id_tipo)
    ) {

      alert("No autorizado")
      return
    }

    this.$emit("navigate", page)

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