<template>
  <header ref="topbarRef" class="topbar">
    <div class="topbar-left">
      <div class="topbar-title">
        <h2>Sistema de Inventario RFID</h2>
        <p>ITCA-FEPADE</p>
      </div>
    </div>

    <div class="topbar-search-wrapper" @click.stop>
      <div class="topbar-search">
        <Search size="20" />
        <input
          type="text"
          placeholder="Buscar en el sistema..."
          v-model="searchText"
          @focus="showSearchResults = true"
          @keydown.enter.prevent="selectFirstSearchResult"
          @keydown.esc.prevent="clearSearch"
        />
      </div>

      <div v-if="searchText.trim() && showSearchResults" class="global-search-dropdown">
        <button
          v-for="result in filteredSearchResults"
          :key="result.key"
          type="button"
          class="global-search-item"
          @click="selectSearchResult(result)"
        >
          <div class="global-search-icon">
            {{ result.icon }}
          </div>

          <div class="global-search-info">
            <strong>{{ result.label }}</strong>
            <span>{{ result.description }}</span>
          </div>
        </button>

        <div v-if="filteredSearchResults.length === 0" class="global-search-empty">
          No hay coincidencias para "{{ searchText }}".
        </div>
      </div>
    </div>

    <div class="topbar-actions">
      <div class="topbar-menu-wrapper">
        <button class="topbar-icon-btn" @click="toggleHelpMenu">
          <CircleHelp size="22" />
        </button>

        <div v-if="showHelpMenu" class="topbar-dropdown help-dropdown">
          <button class="dropdown-item" @click="openHelpPage('userManual')">
            <BookOpen size="18" />
            Manual de usuario
          </button>

          <button class="dropdown-item" @click="openHelpPage('aboutSystem')">
            <Info size="18" />
            Acerca del sistema
          </button>
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
              <h3>Centro de Notificaciones</h3>
              <span>Registro inteligente RFID</span>
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
            No hay notificaciones para este filtro.
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
  Info,
    Bell,
    UserCog,
    KeyRound,
    LogOut,
    ChevronDown,
    X
  },

  emits: ["logout", "navigate"],

  data() {
    const usuario = JSON.parse(localStorage.getItem("usuario"))

    return {
      searchText: "",
      usuario,
      showHelpMenu: false,
      showNotificationMenu: false,
      showProfileMenu: false,
      showPasswordModal: false,
      showSearchResults: false,
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
  // 1. Intenta obtener el nuevo accesor plano
  if (this.usuario?.rol_nombre) {
    return this.usuario.rol_nombre;
  }
  // 2. Fallback por si el localStorage tiene el formato antiguo con la relación anidada
  if (this.usuario?.tipo_usuario?.nombre_tipo) {
    return this.usuario.tipo_usuario.nombre_tipo;
  }
  // 3. Fallback en caso de que sea una propiedad directa vieja (ej. id_tipo)
  return "Usuario del Sistema";
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
    },

    searchOptions() {
      return [
        {
          key: "dashboard",
          icon: "🏠",
          label: "Panel Principal",
          description: "Dashboard general del sistema.",
          payload: { page: "dashboard" },
          keywords: "inicio home dashboard panel principal control resumen graficas estadísticas"
        },
        {
          key: "dashboard-total-activos",
          icon: "📦",
          label: "Total de Activos",
          description: "Tarjeta del dashboard con el total de activos registrados.",
          payload: { page: "dashboard" },
          keywords: "total activos cantidad equipos registrados dashboard panel principal"
        },
        {
          key: "dashboard-asignados",
          icon: "✅",
          label: "Equipos Asignados",
          description: "Tarjeta del dashboard con activos asignados.",
          payload: { page: "dashboard" },
          keywords: "equipos asignados responsables asignacion dashboard activos"
        },
        {
          key: "dashboard-mantenimiento",
          icon: "🔧",
          label: "Equipos en Mantenimiento",
          description: "Tarjeta del dashboard con activos en mantenimiento.",
          payload: { page: "dashboard" },
          keywords: "mantenimiento equipos reparar revision estado activos dashboard"
        },
        {
          key: "dashboard-movimientos",
          icon: "📊",
          label: "Historial de Movimientos por Tipo",
          description: "Gráfico del último mes en el panel principal.",
          payload: { page: "dashboard" },
          keywords: "historial movimientos gráfico grafica tipo mes altas bajas traslados devoluciones"
        },
        {
          key: "dashboard-ultimo-inventario",
          icon: "🧪",
          label: "Último Inventario por Laboratorio",
          description: "Listado lateral de inventarios recientes por salón o laboratorio.",
          payload: { page: "dashboard" },
          keywords: "ultimo inventario laboratorio salon revision activos ubicacion"
        },
        {
          key: "inventory",
          icon: "📡",
          label: "Inventario",
          description: "Gestión de inventario y lectura de activos.",
          payload: { page: "inventory" },
          keywords: "inventario rfid escaneo scanner lector leer activos codigo busqueda filtros exportar"
        },
        {
          key: "inventory-rfid",
          icon: "📶",
          label: "Realizar Inventario con RFID",
          description: "Botón para iniciar una jornada de inventario.",
          payload: { page: "inventory" },
          keywords: "realizar inventario rfid escanear lectura iniciar jornada scanner lector"
        },
        {
          key: "inventory-codigo",
          icon: "#",
          label: "Código de Activo",
          description: "Columna de código en la tabla de inventario.",
          payload: { page: "inventory" },
          keywords: "codigo código id activo identificador tabla inventario"
        },
        {
          key: "inventory-nombre",
          icon: "📝",
          label: "Nombre / Descripción",
          description: "Columna de nombre o descripción del activo.",
          payload: { page: "inventory" },
          keywords: "nombre descripcion descripción activo equipo computadora laptop monitor"
        },
        {
          key: "inventory-ubicacion",
          icon: "📍",
          label: "Ubicación",
          description: "Columna de edificio, salón o laboratorio del activo.",
          payload: { page: "inventory" },
          keywords: "ubicacion ubicación edificio salon salón laboratorio bodega oficina departamento"
        },
        {
          key: "inventory-rfid-columna",
          icon: "🏷️",
          label: "RFID",
          description: "Columna de código RFID asociado al activo.",
          payload: { page: "inventory" },
          keywords: "rfid etiqueta tag codigo código lectura escaner"
        },
        {
          key: "register-asset",
          icon: "➕",
          label: "Registrar Activos",
          description: "Formulario para crear nuevos activos.",
          payload: { page: "registerAsset", catalog: "registrar" },
          keywords: "registrar activo crear nuevo agregar alta serie valor compra categoria modelo responsable etiqueta"
        },
        {
          key: "assets-list",
          icon: "📋",
          label: "Activos",
          description: "Consulta general de activos registrados.",
          payload: { page: "assetsList", catalog: "activos" },
          keywords: "activos listado consulta general asignar responsable cambiar etiqueta rfid editar ver"
        },
        {
          key: "asset-movements",
          icon: "↔️",
          label: "Registrar Movimientos",
          description: "Registro de entradas, salidas y traslados.",
          payload: { page: "assetMovements", catalog: "movimientos" },
          keywords: "registrar movimientos movimiento entrada salida traslado activo ubicacion comentario historial"
        },
        {
          key: "users",
          icon: "👥",
          label: "Usuarios",
          description: "Gestión de usuarios y roles.",
          payload: { page: "users" },
          keywords: "usuarios roles permisos administrador auditor contabilidad bodeguero crear editar eliminar activos inactivos"
        },
        {
          key: "maintenance",
          icon: "🛠️",
          label: "Mantenimiento",
          description: "Catálogos principales del sistema.",
          payload: { page: "maintenance", catalog: "categorias" },
          keywords: "mantenimiento catalogos catálogos categorias marcas modelos laboratorios edificios responsables"
        },
        {
          key: "maintenance-categorias",
          icon: "🗂️",
          label: "Categorías",
          description: "Catálogo de categorías de activos.",
          payload: { page: "maintenance", catalog: "categorias" },
          keywords: "categorias categorías catalogo activo tipo equipo mobiliario herramientas laboratorio vehiculos"
        },
        {
          key: "maintenance-marcas",
          icon: "🏷️",
          label: "Marcas",
          description: "Catálogo de marcas de activos.",
          payload: { page: "maintenance", catalog: "marcas" },
          keywords: "marcas marca dell hp lenovo epson steren catalogo"
        },
        {
          key: "maintenance-modelos",
          icon: "💻",
          label: "Modelos",
          description: "Catálogo de modelos de activos.",
          payload: { page: "maintenance", catalog: "modelos" },
          keywords: "modelos modelo laptop computadora monitor proyector catalogo"
        },
        {
          key: "maintenance-laboratorios",
          icon: "🧪",
          label: "Laboratorios",
          description: "Catálogo de laboratorios o salones.",
          payload: { page: "maintenance", catalog: "laboratorios" },
          keywords: "laboratorios laboratorio salon salón aula area ubicación ubicacion"
        },
        {
          key: "maintenance-edificios",
          icon: "🏢",
          label: "Edificios",
          description: "Catálogo de edificios.",
          payload: { page: "maintenance", catalog: "edificios" },
          keywords: "edificios edificio instalaciones campus ubicacion ubicación"
        },
        {
          key: "maintenance-responsables",
          icon: "🙋",
          label: "Responsables",
          description: "Catálogo de responsables de activos.",
          payload: { page: "maintenance", catalog: "responsables" },
          keywords: "responsables responsable encargado empleado codigo código asignar activo"
        },
        {
          key: "reports",
          icon: "📄",
          label: "Reportes",
          description: "Catálogo y generación de reportes.",
          payload: { page: "reports", report: "inventario_general" },
          keywords: "reportes reporte generar descargar pdf excel historial inventario analiticos"
        },
        {
          key: "report-inventario-general",
          icon: "📦",
          label: "Inventario General de Activos",
          description: "Reporte de todos los activos registrados.",
          payload: { page: "reports", report: "inventario_general" },
          keywords: "inventario general activos codigo nombre categoria ubicacion estado etiqueta rfid"
        },
        {
          key: "report-categoria",
          icon: "🗂️",
          label: "Activos por Categoría",
          description: "Reporte agrupado por categoría.",
          payload: { page: "reports", report: "activos_categoria" },
          keywords: "activos categoria categorías equipo informatico mobiliario herramientas laboratorio vehiculos"
        },
        {
          key: "report-ubicacion",
          icon: "📍",
          label: "Activos por Ubicación",
          description: "Reporte agrupado por edificio o laboratorio.",
          payload: { page: "reports", report: "activos_ubicacion" },
          keywords: "activos ubicacion ubicación edificio departamento oficina bodega laboratorio salon"
        },
        {
          key: "report-estado",
          icon: "✅",
          label: "Activos por Estado",
          description: "Reporte agrupado por estado del activo.",
          payload: { page: "reports", report: "activos_estado" },
          keywords: "activos estado activo mantenimiento prestado baja extraviado control administrativo"
        },
        {
          key: "report-rfid-encontrados",
          icon: "📡",
          label: "Activos Encontrados Durante Inventario RFID",
          description: "Activos detectados durante una jornada de inventario.",
          payload: { page: "reports", report: "rfid_encontrados" },
          keywords: "rfid encontrados detectados jornada inventario lectura fecha hora ubicacion"
        },
        {
          key: "report-no-encontrados",
          icon: "⚠️",
          label: "Activos No Encontrados",
          description: "Comparación entre activos registrados y detectados.",
          payload: { page: "reports", report: "activos_no_encontrados" },
          keywords: "activos no encontrados faltantes extraviados comparacion registrados detectados ultima ubicacion"
        },
        {
          key: "report-historial-rfid",
          icon: "🕘",
          label: "Historial de Lecturas RFID",
          description: "Trazabilidad de lecturas RFID.",
          payload: { page: "reports", report: "historial_rfid" },
          keywords: "historial lecturas rfid trazabilidad activo usuario fecha hora ubicacion"
        },
        {
          key: "report-diferencias",
          icon: "🔄",
          label: "Diferencias entre Inventarios",
          description: "Comparación entre inventarios realizados.",
          payload: { page: "reports", report: "diferencias_inventarios" },
          keywords: "diferencias inventarios comparar fechas encontrados faltantes cambios jornadas"
        },
        {
          key: "notifications",
          icon: "🔔",
          label: "Centro de Notificaciones",
          description: "Alertas RFID e IA del sistema.",
          payload: { page: "dashboard" },
          keywords: "notificaciones alertas campana rfid ia criticas no leidas leer eliminar"
        },
        {
          key: "manual",
          icon: "📘",
          label: "Manual de usuario",
          description: "Guía interna de uso del sistema.",
          payload: { page: "userManual" },
          keywords: "manual usuario ayuda guía guia instrucciones soporte documentación"
        },
        {
          key: "about",
          icon: "ℹ️",
          label: "Acerca del sistema",
          description: "Información general del proyecto.",
          payload: { page: "aboutSystem" },
          keywords: "acerca sistema información version tesis itca fepade proyecto"
        }
      ]
    },

    filteredSearchResults() {
      const query = this.normalizeSearchText(this.searchText)

      if (!query) {
        return []
      }

      return this.searchOptions
        .filter(item => {
          const searchableText = this.normalizeSearchText(
            `${item.label} ${item.description} ${item.keywords}`
          )

          return searchableText.includes(query)
        })
        .slice(0, 12)
    }
  },

  mounted() {
    this.loadNotifications()
    document.addEventListener("click", this.handleOutsideClick)

    this.alertInterval = setInterval(() => {
      this.loadNotifications(true)
    }, 5000)
  },

  beforeUnmount() {
    clearInterval(this.alertInterval)
    document.removeEventListener("click", this.handleOutsideClick)
  },

  methods: {
    handleOutsideClick(event) {
      const clickedInsideTopbar = this.$refs.topbarRef?.contains(event.target)
      const clickedInsideSearchDropdown = event.target.closest(".global-search-dropdown")
      const clickedInsideDetailModal = event.target.closest(".alert-detail-modal")
      const clickedInsidePasswordModal = event.target.closest(".password-modal")
      const clickedInsideToast = event.target.closest(".alert-toast")

      if (
        !clickedInsideTopbar &&
        !clickedInsideSearchDropdown &&
        !clickedInsideDetailModal &&
        !clickedInsidePasswordModal &&
        !clickedInsideToast
      ) {
        this.closeMenus()
      }
    },

    closeMenus() {
      this.showHelpMenu = false
      this.showNotificationMenu = false
      this.showProfileMenu = false
      this.showSearchResults = false

    },

    normalizeSearchText(value) {
      return String(value ?? "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
    },

    selectSearchResult(result) {
      this.searchText = ""
      this.showSearchResults = false
      this.closeMenus()
      this.$emit("navigate", result.payload)
    },

    selectFirstSearchResult() {
      if (this.filteredSearchResults.length > 0) {
        this.selectSearchResult(this.filteredSearchResults[0])
      }
    },

    clearSearch() {
      this.searchText = ""
      this.showSearchResults = false
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

    openHelpPage(page) {
      this.closeMenus()
      this.$emit("navigate", { page })
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

<style scoped>
.topbar-search-wrapper {
  position: relative;
  flex: 1;
  max-width: 620px;
}

.global-search-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  left: 0;
  right: 0;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 22px 45px rgba(15, 23, 42, 0.18);
  padding: 14px;
  z-index: 80;
  max-height: 430px;
  overflow-y: auto;
}

.global-search-item {
  width: 100%;
  border: none;
  background: #f8fafc;
  border-radius: 16px;
  padding: 13px 14px;
  display: flex;
  align-items: center;
  gap: 13px;
  cursor: pointer;
  text-align: left;
  margin-bottom: 9px;
  transition: transform 0.2s ease, background 0.2s ease;
}

.global-search-item:hover {
  background: #fff1f1;
  transform: translateX(3px);
}

.global-search-icon {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: #fee2e2;
  font-weight: 800;
  flex-shrink: 0;
}

.global-search-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.global-search-info strong {
  color: #0f172a;
  font-size: 15px;
}

.global-search-info span {
  color: #64748b;
  font-size: 13px;
}

.global-search-empty {
  padding: 22px;
  text-align: center;
  color: #64748b;
  font-weight: 700;
}
</style>
