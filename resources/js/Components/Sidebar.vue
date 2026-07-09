<template>
  <aside :class="['sidebar', { collapsed: isCollapsed }]">
    <div>
      <div class="sidebar-header">
        <div class="brand">
          <div class="brand-icon">
            <img
              :src="logoItca"
              alt="ITCA-FEPADE"
              class="brand-logo"
            />
          </div>

          <span v-if="!isCollapsed" class="brand-text">ITCA FEPADE</span>
        </div>

        <button class="toggle-btn" @click="$emit('toggle-sidebar')">
          <PanelLeftClose v-if="!isCollapsed" size="20" />
          <PanelLeftOpen v-else size="20" />
        </button>
      </div>

      <nav class="menu">
        <template v-for="item in filteredMenu" :key="item.page">
          <button
            type="button"
            :class="['menu-item', { active: activePage === item.page }]"
            @click="handleMenuClick(item)"
          >
            <component :is="item.icon" size="21" />
            <span v-if="!isCollapsed">{{ item.name }}</span>
            <ChevronDown
              v-if="item.children && !isCollapsed"
              size="18"
              :class="['submenu-arrow', { open: openSubmenu === item.page }]"
            />
          </button>

          <div
            v-if="item.children && openSubmenu === item.page && !isCollapsed"
            class="submenu"
          >
            <button
              v-for="child in item.children"
              :key="child.catalog || child.report"
              type="button"
              class="submenu-item"
              @click="$emit('navigate', { page: child.page, catalog: child.catalog, report: child.report })"
            >
              {{ child.name }}
            </button>
          </div>
        </template>
      </nav>
    </div>

    <div class="logout-area">
      <button type="button" class="menu-item logout-item" @click="$emit('logout')">
        <LogOut size="21" />
        <span v-if="!isCollapsed">Cerrar sesión</span>
      </button>
    </div>
  </aside>
</template>

<script>
import {
  LayoutDashboard,
  Package,
  FileText,
  Users,
  Wrench,
  PanelLeftClose,
  PanelLeftOpen,
  LogOut,
  ChevronDown
} from "lucide-vue-next"

const logoItca = "/images/icon-itca.png"

export default {
  name: "Sidebar",

  components: {
    PanelLeftClose,
    PanelLeftOpen,
    LogOut,
    ChevronDown
  },

  props: {
    isCollapsed: {
      type: Boolean,
      default: false
    },
    activePage: {
      type: String,
      default: "dashboard"
    }
  },

  emits: ["toggle-sidebar", "navigate", "logout"],

  data() {
    return {
      logoItca,
      openSubmenu: null,
      usuario: JSON.parse(localStorage.getItem("usuario")),
      menuItems: [
        { name: "Panel Principal", page: "dashboard", icon: LayoutDashboard },
        { name: "Inventario", page: "inventory", icon: Package },
        {
          name: "Módulo de Activos",
          page: "assetsModule",
          icon: Package,
          children: [
            { name: "Registrar Activos", page: "registerAsset", catalog: "registrar" },
            { name: "Activos", page: "assetsList", catalog: "activos" },
            { name: "Registrar Movimientos", page: "assetMovements", catalog: "movimientos" }
          ]
        },
        { name: "Usuarios", page: "users", icon: Users },
        {
          name: "Mantenimiento",
          page: "maintenance",
          icon: Wrench,
          children: [
            { name: "Categorías", page: "maintenance", catalog: "categorias" },
            { name: "Marcas", page: "maintenance", catalog: "marcas" },
            { name: "Modelos", page: "maintenance", catalog: "modelos" },
            { name: "Laboratorios", page: "maintenance", catalog: "laboratorios" },
            { name: "Edificios", page: "maintenance", catalog: "edificios" },
            { name: "Responsables", page: "maintenance", catalog: "responsables" }
          ]
        },
        {
          name: "Reportes",
          page: "reports",
          icon: FileText
        }
      ]
    }
  },

  computed: {
    filteredMenu() {
      if (this.usuario?.id_tipo == 1) {
        return this.menuItems
      }

      if (this.usuario?.id_tipo == 2) {
        return this.menuItems.filter(item =>
          ["dashboard", "reports"].includes(item.page)
        )
      }

      if (this.usuario?.id_tipo == 4) {
        return this.menuItems.filter(item =>
          ["dashboard", "inventory", "assetsModule", "maintenance", "reports"].includes(item.page)
        )
      }

      return this.menuItems.filter(item =>
        ["dashboard", "inventory", "reports"].includes(item.page)
      )
    }
  },

  methods: {
    handleMenuClick(item) {
      if (item.children) {
        this.openSubmenu = this.openSubmenu === item.page ? null : item.page

        if (item.page === "assetsModule") {
          this.$emit("navigate", { page: "registerAsset", catalog: "registrar" })
          return
        }

        this.$emit("navigate", { page: item.page })
        return
      }

      this.openSubmenu = null
      this.$emit("navigate", { page: item.page })
    }
  }
}
</script>
