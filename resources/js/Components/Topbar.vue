<template>
  <header class="topbar">
    <div class="topbar-left">
      <div class="topbar-title">
        <h2>Sistema de Inventario RFID</h2>
        <p>ITCA-FEPADE</p>
      </div>
    </div>

    <div class="topbar-search">
      <Search size="20" />
      <input
        type="text"
        placeholder="Buscar en el sistema..."
        v-model="searchText"
      />
    </div>

    <div class="topbar-actions">
      <div class="topbar-menu-wrapper">
        <button class="topbar-icon-btn" @click="toggleHelpMenu">
          <CircleHelp size="22" />
        </button>

        <div v-if="showHelpMenu" class="topbar-dropdown help-dropdown">
          <button class="dropdown-item">
            <BookOpen size="18" />
            Manual de usuario
          </button>

          <button class="dropdown-item">
            <Headphones size="18" />
            Soporte técnico
          </button>

          <button class="dropdown-item">
            <Info size="18" />
            Acerca del sistema
          </button>
        </div>
      </div>

      <div class="topbar-menu-wrapper">
        <button class="topbar-icon-btn notification-btn" @click="toggleNotificationMenu">
          <Bell size="22" />
          <span class="notification-badge">3</span>
        </button>

        <div v-if="showNotificationMenu" class="topbar-dropdown notification-dropdown">
          <div class="dropdown-title">
            <h3>Notificaciones</h3>
            <span>Actividad reciente</span>
          </div>

          <div class="notification-item">
            <span class="notification-dot green"></span>
            <div>
              <strong>Inventario completado</strong>
              <p>Laboratorio A-102 actualizado correctamente.</p>
            </div>
          </div>

          <div class="notification-item">
            <span class="notification-dot yellow"></span>
            <div>
              <strong>Inventario pendiente</strong>
              <p>Laboratorio B-205 requiere revisión semanal.</p>
            </div>
          </div>

          <div class="notification-item">
            <span class="notification-dot red"></span>
            <div>
              <strong>Registro actualizado</strong>
              <p>Se modificó información de un activo fijo.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="topbar-menu-wrapper">
        <button class="profile-button" @click="toggleProfileMenu">
          <div class="profile-avatar">
            {{ userInitials }}
          </div>

          <div class="profile-info">
            <strong>{{ userName }}</strong>
            <span>Administrador</span>
          </div>

          <ChevronDown size="18" />
        </button>

        <div v-if="showProfileMenu" class="topbar-dropdown profile-dropdown">
          <button class="dropdown-item">
            <UserCog size="18" />
            Mi perfil
          </button>

          <button class="dropdown-item">
            <KeyRound size="18" />
            Cambiar contraseña
          </button>

          <button class="dropdown-item logout-item" @click="$emit('logout')">
            <LogOut size="18" />
            Cerrar sesión
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import {
  Search,
  CircleHelp,
  BookOpen,
  Headphones,
  Info,
  Bell,
  UserCog,
  KeyRound,
  LogOut,
  ChevronDown
} from "lucide-vue-next"

export default {
  name: "Topbar",

  components: {
    Search,
    CircleHelp,
    BookOpen,
    Headphones,
    Info,
    Bell,
    UserCog,
    KeyRound,
    LogOut,
    ChevronDown
  },

  emits: ["logout"],

  data() {
    return {
      searchText: "",
      userName: "Eduardo Gomez",
      showHelpMenu: false,
      showNotificationMenu: false,
      showProfileMenu: false
    }
  },

  computed: {
    userInitials() {
      return this.userName
        .split(" ")
        .slice(0, 2)
        .map(word => word[0])
        .join("")
        .toUpperCase()
    }
  },

  methods: {
    closeMenus() {
      this.showHelpMenu = false
      this.showNotificationMenu = false
      this.showProfileMenu = false
    },

    toggleHelpMenu() {
      const wasOpen = this.showHelpMenu
      this.closeMenus()
      this.showHelpMenu = !wasOpen
    },

    toggleNotificationMenu() {
      const wasOpen = this.showNotificationMenu
      this.closeMenus()
      this.showNotificationMenu = !wasOpen
    },

    toggleProfileMenu() {
      const wasOpen = this.showProfileMenu
      this.closeMenus()
      this.showProfileMenu = !wasOpen
    }
  }
}
</script>
