<template>
  <div class="layout" :class="{ 'mobile-menu-open': isMobileMenuOpen }">
    <button
      type="button"
      class="mobile-menu-button"
      :aria-label="isMobileMenuOpen ? 'Cerrar menú' : 'Abrir menú'"
      @click="toggleMobileMenu"
    >
      <X v-if="isMobileMenuOpen" size="24" />
      <Menu v-else size="24" />
    </button>

    <div
      v-if="isMobileMenuOpen"
      class="mobile-sidebar-backdrop"
      @click="closeMobileMenu"
    ></div>

    <Sidebar
      :is-collapsed="isSidebarCollapsed"
      :class="{ 'mobile-open': isMobileMenuOpen }"
      :active-page="activePage"
      @toggle-sidebar="toggleSidebar"
      @navigate="handleNavigation"
      @logout="handleLogout"
    />

    <main class="content">
      <Topbar
        @logout="handleLogout"
        @navigate="handleNavigation"
      />

      <div class="page-content">
        <slot />
      </div>
    </main>
  </div>
</template>

<script>
import { Menu, X } from "lucide-vue-next"
import Sidebar from "../Components/Sidebar.vue"
import Topbar from "../Components/Topbar.vue"

export default {
  name: "AppLayout",

  components: {
    Sidebar,
    Topbar,
    Menu,
    X
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
      isMobileMenuOpen: false,
      usuario: JSON.parse(localStorage.getItem("usuario") || "null")
    }
  },

  mounted() {
    window.addEventListener("resize", this.handleResize)
  },

  beforeUnmount() {
    window.removeEventListener("resize", this.handleResize)
  },

  methods: {
    toggleSidebar() {
      if (window.innerWidth <= 992) {
        this.toggleMobileMenu()
        return
      }

      this.isSidebarCollapsed = !this.isSidebarCollapsed
    },

    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen
      document.body.classList.toggle("menu-open", this.isMobileMenuOpen)
    },

    closeMobileMenu() {
      this.isMobileMenuOpen = false
      document.body.classList.remove("menu-open")
    },

    handleResize() {
      if (window.innerWidth > 992) {
        this.closeMobileMenu()
      }
    },

    handleLogout() {
      this.closeMobileMenu()
      this.$emit("logout")
    },

    handleNavigation(payload) {
      const page = typeof payload === "string" ? payload : payload.page
      const userType = Number(this.usuario?.id_tipo)

      if (page === "usuarios" && userType !== 1) {
        alert("No autorizado")
        return
      }

      if (page === "activos" && ![1, 2].includes(userType)) {
        alert("No autorizado")
        return
      }

      this.closeMobileMenu()
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
  min-width: 0;
  padding: 0;
  transition: all 0.35s ease;
  position: relative;
  overflow-y: auto;
}

.page-content {
  width: 100%;
  padding: 34px;
}

.mobile-menu-button,
.mobile-sidebar-backdrop {
  display: none;
}

@media (max-width: 992px) {
  .layout {
    display: block;
  }

  .content {
    width: 100%;
    min-height: 100vh;
    overflow: visible;
  }

  .page-content {
    padding: 20px;
  }

  .mobile-menu-button {
    position: fixed;
    top: 18px;
    left: 16px;
    z-index: 9100;
    width: 46px;
    height: 46px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 14px;
    background: #dc2626;
    color: #fff;
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.24);
  }

  .mobile-sidebar-backdrop {
    position: fixed;
    inset: 0;
    z-index: 8990;
    display: block;
    background: rgba(15, 23, 42, 0.56);
    backdrop-filter: blur(3px);
  }
}

@media (max-width: 576px) {
  .page-content {
    padding: 14px;
  }

  .mobile-menu-button {
    top: 13px;
    left: 12px;
    width: 42px;
    height: 42px;
    border-radius: 12px;
  }
}
</style>
