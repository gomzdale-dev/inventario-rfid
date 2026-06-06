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
          <span v-if="unreadNotifications > 0" class="notification-badge">
            {{ unreadNotifications }}
          </span>
        </button>

        <div v-if="showNotificationMenu" class="topbar-dropdown notification-dropdown">
          <div class="dropdown-title notification-header">
            <div>
              <h3>Centro de Alertas</h3>
              <span>Monitoreo inteligente RFID</span>
            </div>

            <button class="mark-read-btn" @click="markAllNotificationsAsRead">
              Marcar leídas
            </button>
          </div>

          <div class="notification-filters">
            <button :class="{ active: notificationFilter === 'todas' }" @click="notificationFilter = 'todas'">
              Todas
            </button>
            <button :class="{ active: notificationFilter === 'criticas' }" @click="notificationFilter = 'criticas'">
              Críticas
            </button>
            <button :class="{ active: notificationFilter === 'no_leidas' }" @click="notificationFilter = 'no_leidas'">
              No leídas
            </button>
            <button :class="{ active: notificationFilter === 'rfid' }" @click="notificationFilter = 'rfid'">
              RFID
            </button>
            <button :class="{ active: notificationFilter === 'ia' }" @click="notificationFilter = 'ia'">
              IA
            </button>
          </div>

          <div class="notifications-list">

          <div v-if="filteredNotifications.length === 0" class="empty-alerts-box">
            No hay alertas para este filtro.
          </div>

          <div
            v-for="alert in filteredNotifications"
            :key="alert.id"
            :class="['smart-notification-item', alert.priority, { unread: !alert.read }]"
            @click="markNotificationAsRead(alert.id)"
          >
            <div class="smart-alert-icon">
              {{ alert.icon }}
            </div>

            <div>
              <strong>{{ alert.title }}</strong>
              <p>{{ alert.message }}</p>
              <small>{{ formatAlertTime(alert.time) }}</small>

              <div class="notification-actions">
                <button @click.stop="openNotificationDetail(alert)">Ver</button>
                <button class="delete-alert-btn" @click.stop="deleteNotification(alert.id)">Eliminar</button>
              </div>
            </div>
          </div>
        </div>

          <button class="simulate-alert-btn" @click="simulateCriticalAlert">
            Simular alerta RFID crítica
          </button>

          <button class="simulate-alert-btn ia-alert-btn" @click="simulateIaAlert">
            Simular alerta IA
          </button>
        </div>
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

  <div v-if="toastAlert" :class="['alert-toast', toastAlert.priority]">
    <div class="toast-icon">
      {{ toastAlert.icon }}
    </div>

    <div>
      <strong>{{ toastAlert.title }}</strong>
      <p>{{ toastAlert.message }}</p>
    </div>

    <button @click="toastAlert = null">
      <X size="18" />
    </button>
  </div>

  <div v-if="selectedNotification" class="alert-detail-backdrop" @click="closeNotificationDetail">
    <div class="alert-detail-modal" @click.stop>
      <header>
        <div>
          <span :class="['alert-detail-priority', selectedNotification.priority]">
            {{ selectedNotification.priority }}
          </span>
          <h2>{{ selectedNotification.title }}</h2>
        </div>

        <button type="button" @click="closeNotificationDetail">
          <X size="22" />
        </button>
      </header>

      <p>{{ selectedNotification.message }}</p>

      <div class="alert-detail-info">
        <span>Fecha / hora:</span>
        <strong>{{ formatAlertTime(selectedNotification.time) }}</strong>
      </div>

      <div class="alert-detail-info">
        <span>Estado:</span>
        <strong>{{ selectedNotification.read ? "Leída" : "No leída" }}</strong>
      </div>

      <div v-if="selectedNotification.rfidCode" class="alert-detail-info">
        <span>Código RFID:</span>
        <strong>{{ selectedNotification.rfidCode }}</strong>
      </div>

      <div v-if="selectedNotification.origin" class="alert-detail-info">
        <span>Origen / lector:</span>
        <strong>{{ selectedNotification.origin }}</strong>
      </div>

      <div v-if="selectedNotification.riskLevel" class="alert-detail-info">
        <span>Nivel de riesgo:</span>
        <strong>{{ selectedNotification.riskLevel }}%</strong>
      </div>

      <div class="alert-detail-info">
        <span>Detectada por IA:</span>
        <strong>{{ selectedNotification.detectedByIa ? "Sí" : "No" }}</strong>
      </div>

      <button class="alert-detail-main-btn" @click="closeNotificationDetail">
        Entendido
      </button>
    </div>
  </div>

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
      selectedNotification: null,
      toastAlert: null,
      notificationFilter: "todas",
      alertInterval: null,
      lastAlertId: null,
      passwordError: "",
      passwordSuccess: "",
      passwordForm: {
        currentPassword: "",
        newPassword: "",
        confirmPassword: ""
      },
      notifications: []
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
    },

    unreadNotifications() {
      return this.notifications.filter(alert => !alert.read).length
    },

    filteredNotifications() {
      if (this.notificationFilter === "criticas") {
        return this.notifications.filter(alert => alert.priority === "critical")
      }

      if (this.notificationFilter === "no_leidas") {
        return this.notifications.filter(alert => !alert.read)
      }

      if (this.notificationFilter === "rfid") {
        return this.notifications.filter(alert => alert.type === "rfid")
      }

      if (this.notificationFilter === "ia") {
        return this.notifications.filter(alert => alert.detectedByIa)
      }

      return this.notifications
    }
  },

  mounted() {
    this.loadNotifications()

    this.alertInterval = setInterval(() => {
      this.loadNotifications(true)
    }, 5000)
  },

  beforeUnmount() {
    clearInterval(this.alertInterval)
  },

  methods: {
    closeMenus() {
      this.showHelpMenu = false
      this.showNotificationMenu = false
      this.showProfileMenu = false
      
    },
    isSecurePassword(password) {
     const regex = 
     /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_@$!%*?#&]).{8,}$/
    return regex.test(password)
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

    async loadNotifications(playSoundForNew = false) {
      try {
        const response = await api.get("/alertas")

        const mappedAlerts = response.data.map(alerta => ({
          id: alerta.id,
          title: alerta.titulo,
          message: alerta.mensaje,
          time: alerta.fecha_alerta || alerta.created_at,
          priority: alerta.prioridad,
          icon: alerta.detectada_por_ia ? "IA" : alerta.prioridad === "critical" ? "⚠" : "✓",
          read: Boolean(alerta.leida),
          sound: alerta.prioridad,
          type: alerta.tipo,
          detectedByIa: Boolean(alerta.detectada_por_ia),
          riskLevel: alerta.nivel_riesgo,
          rfidCode: alerta.codigo_rfid,
          origin: alerta.origen
        }))

        if (playSoundForNew && mappedAlerts.length > 0) {
          const newestAlert = mappedAlerts[0]

          if (this.lastAlertId && newestAlert.id !== this.lastAlertId && !newestAlert.read) {
            this.playAlertSound(newestAlert.priority)
            this.showToastAlert(newestAlert)
          }

          this.lastAlertId = newestAlert.id
        }

        if (!this.lastAlertId && mappedAlerts.length > 0) {
          this.lastAlertId = mappedAlerts[0].id
        }

        this.notifications = mappedAlerts
      } catch (error) {
        console.error("Error al cargar alertas:", error)
      }
    },

    async markNotificationAsRead(id) {
      try {
        await api.put(`/alertas/${id}/leer`)

        const alert = this.notifications.find(item => item.id === id)
        if (alert) alert.read = true
      } catch (error) {
        console.error("Error al marcar alerta:", error)
      }
    },

    async markAllNotificationsAsRead() {
      try {
        await api.put("/alertas/marcar-todas")

        this.notifications.forEach(alert => {
          alert.read = true
        })
      } catch (error) {
        console.error("Error al marcar todas:", error)
      }
    },

    openNotificationDetail(alert) {
      this.markNotificationAsRead(alert.id)
      this.selectedNotification = alert
    },

    closeNotificationDetail() {
      this.selectedNotification = null
    },

    async deleteNotification(id) {
      try {
        await api.delete(`/alertas/${id}`)

        this.notifications = this.notifications.filter(alert => alert.id !== id)
      } catch (error) {
        console.error("Error al eliminar alerta:", error)
      }
    },

    async simulateCriticalAlert() {
      try {
        const response = await api.post("/alertas/simular-rfid")
        const alerta = response.data.data

        const newAlert = {
          id: alerta.id,
          title: alerta.titulo,
          message: alerta.mensaje,
          time: "Ahora",
          priority: alerta.prioridad,
          icon: "⚠",
          read: Boolean(alerta.leida),
          sound: "critical",
          type: alerta.tipo,
          detectedByIa: Boolean(alerta.detectada_por_ia),
          riskLevel: alerta.nivel_riesgo,
          rfidCode: alerta.codigo_rfid,
          origin: alerta.origen
        }

        this.notifications.unshift(newAlert)
        this.playAlertSound("critical")
        this.showToastAlert(newAlert)
      } catch (error) {
        console.error("Error al simular alerta RFID:", error)
      }
    },

    async simulateIaAlert() {
      try {
        const response = await api.post("/alertas/simular-ia")
        const alerta = response.data.data

        const newAlert = {
          id: alerta.id,
          title: alerta.titulo,
          message: alerta.mensaje,
          time: "Ahora",
          priority: alerta.prioridad,
          icon: "IA",
          read: Boolean(alerta.leida),
          sound: "critical",
          type: alerta.tipo,
          detectedByIa: Boolean(alerta.detectada_por_ia),
          riskLevel: alerta.nivel_riesgo,
          rfidCode: alerta.codigo_rfid,
          origin: alerta.origen
        }

        this.notifications.unshift(newAlert)
        this.playAlertSound("critical")
        this.showToastAlert(newAlert)
      } catch (error) {
        console.error("Error al simular alerta IA:", error)
      }
    },

    showToastAlert(alert) {
      this.toastAlert = alert

      setTimeout(() => {
        this.toastAlert = null
      }, 5500)
    },

    playAlertSound(type) {
      const audioContext = new (window.AudioContext || window.webkitAudioContext)()

      const patterns = {
        critical: [880, 660, 880],
        warning: [660, 520],
        success: [520, 700],
        info: [440]
      }

      const tones = patterns[type] || patterns.info

      tones.forEach((frequency, index) => {
        const oscillator = audioContext.createOscillator()
        const gainNode = audioContext.createGain()

        oscillator.connect(gainNode)
        gainNode.connect(audioContext.destination)

        const startTime = audioContext.currentTime + index * 0.18

        oscillator.frequency.value = frequency
        oscillator.type = "sine"

        gainNode.gain.setValueAtTime(0.16, startTime)
        gainNode.gain.exponentialRampToValueAtTime(0.001, startTime + 0.16)

        oscillator.start(startTime)
        oscillator.stop(startTime + 0.16)
      })
    },

    formatAlertTime(value) {
      if (!value) return "Ahora"

      const date = new Date(value)
      if (Number.isNaN(date.getTime())) return value

      return date.toLocaleString("es-SV", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit"
      })
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

      if (!this.isSecurePassword(this.passwordForm.newPassword)) {
        this.passwordError =   "La contraseña debe cumplir:\n" +
                               "• Mínimo 8 caracteres\n" +
                               "• Al menos una letra mayúscula\n" +
                               "• Al menos una letra minúscula\n" +
                               "• Al menos un número\n" +
                               "• Al menos un carácter especial"
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
        this.passwordError = error.response?.data?.message || "No se pudo cambiar la contraseña"
      }
    }
  }
}
</script>
