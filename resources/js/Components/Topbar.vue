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
      <input type="text" placeholder="Buscar en el sistema..." v-model="searchText" />
    </div>

    <div class="topbar-actions">
      <div class="topbar-menu-wrapper">
        <button class="topbar-icon-btn" @click="toggleHelpMenu">
          <CircleHelp size="22" />
        </button>

        <div v-if="showHelpMenu" class="topbar-dropdown help-dropdown">
          <button class="dropdown-item"><BookOpen size="18" /> Manual de usuario</button>
          <button class="dropdown-item"><Headphones size="18" /> Soporte técnico</button>
          <button class="dropdown-item"><Info size="18" /> Acerca del sistema</button>
        </div>
      </div>

      <div class="topbar-menu-wrapper">
        <button class="topbar-icon-btn notification-btn" @click="toggleNotificationMenu">
          <Bell size="22" />
          <span class="notification-badge">3</span>
        </button>
      </div>

      <div class="topbar-menu-wrapper">
        <button class="profile-button" @click="toggleProfileMenu">
          <div class="profile-avatar">{{ userInitials }}</div>

          <div class="profile-info">
            <strong>{{ userName }}</strong>
            <span>{{ userRole }}</span>
          </div>

          <ChevronDown size="18" />
        </button>

        <div v-if="showProfileMenu" class="topbar-dropdown profile-dropdown">
          <button class="dropdown-item">
            <UserCog size="18" />
            Mi perfil
          </button>

          <button class="dropdown-item" @click="openPasswordModal">
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

  <div v-if="showPasswordModal" class="password-modal-backdrop" @click="closePasswordModal">
    <form class="password-modal" @submit.prevent="changePassword" @click.stop>
      <header>
        <h2>Cambiar contraseña</h2>
        <button type="button" @click="closePasswordModal">
          <X size="22" />
        </button>
      </header>

      <div class="password-field">
        <label>Contraseña actual *</label>
        <input v-model="passwordForm.currentPassword" type="password" required />
      </div>

      <div class="password-field">
        <label>Nueva contraseña *</label>
        <input v-model="passwordForm.newPassword" type="password" required />
      </div>

      <div class="password-field">
        <label>Confirmar nueva contraseña *</label>
        <input v-model="passwordForm.confirmPassword" type="password" required />
      </div>

      <p v-if="passwordError" class="password-error">{{ passwordError }}</p>
      <p v-if="passwordSuccess" class="password-success">{{ passwordSuccess }}</p>

      <div class="password-actions">
        <button type="button" class="cancel-password-btn" @click="closePasswordModal">
          Cancelar
        </button>

        <button type="submit" class="save-password-btn">
          Guardar contraseña
        </button>
      </div>
    </form>
  </div>
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
  ChevronDown,
  X
} from "lucide-vue-next"

import api from "../services/api"

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
    ChevronDown,
    X
  },

  emits: ["logout"],

  data() {
    const usuario = JSON.parse(localStorage.getItem("usuario"))

    return {
      searchText: "",
      usuario,
      showHelpMenu: false,
      showNotificationMenu: false,
      showProfileMenu: false,
      showPasswordModal: false,
      passwordError: "",
      passwordSuccess: "",
      passwordForm: {
        currentPassword: "",
        newPassword: "",
        confirmPassword: ""
      }
    }
  },

  computed: {
    userName() {
      return this.usuario?.nombre_usuario || "Usuario"
    },

    userRole() {
      const roles = {
        1: "Administrador",
        2: "Auditor",
        3: "Contabilidad",
        4: "Bodeguero"
      }

      return roles[this.usuario?.id_tipo] || "Usuario"
    },

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
    },

    openPasswordModal() {
      this.closeMenus()
      this.passwordError = ""
      this.passwordSuccess = ""
      this.passwordForm = {
        currentPassword: "",
        newPassword: "",
        confirmPassword: ""
      }
      this.showPasswordModal = true
    },

    closePasswordModal() {
      this.showPasswordModal = false
    },

    async changePassword() {
      this.passwordError = ""
      this.passwordSuccess = ""

      if (this.passwordForm.newPassword !== this.passwordForm.confirmPassword) {
        this.passwordError = "Las contraseñas no coinciden"
        return
      }

      if (this.passwordForm.newPassword.length < 8) {
        this.passwordError = "La nueva contraseña debe tener mínimo 8 caracteres"
        return
      }

      try {
        await api.put("/change-password", {
          current_password: this.passwordForm.currentPassword,
          new_password: this.passwordForm.newPassword,
          new_password_confirmation: this.passwordForm.confirmPassword
        })

        this.passwordSuccess = "Contraseña actualizada correctamente"

        setTimeout(() => {
          this.closePasswordModal()
        }, 1200)
      } catch (error) {
        this.passwordError =
          error.response?.data?.message || "No se pudo cambiar la contraseña"
      }
    }
  }
}
</script>