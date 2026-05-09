<template>
  <aside :class="['sidebar', { collapsed: isCollapsed }]">
    <div>
      <div class="sidebar-header">
        <div class="brand">
          <div class="brand-icon">R</div>
          <span v-if="!isCollapsed" class="brand-text">RFID</span>
        </div>

        <button class="toggle-btn" @click="$emit('toggle-sidebar')">
          <PanelLeftClose v-if="!isCollapsed" size="20" />
          <PanelLeftOpen v-else size="20" />
        </button>
      </div>

      <nav class="menu">
        <button
          v-for="item in menuItems"
          :key="item.page"
          type="button"
          :class="['menu-item', { active: activePage === item.page }]"
          @click="$emit('navigate', item.page)"
        >
          <component :is="item.icon" size="21" />
          <span v-if="!isCollapsed">{{ item.name }}</span>
        </button>
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
  ScanLine,
  FileText,
  Users,
  Wrench,
  Settings,
  FilePlus,
  PanelLeftClose,
  PanelLeftOpen,
  LogOut
} from "lucide-vue-next"

export default {
  name: "Sidebar",
  components: {
    PanelLeftClose,
    PanelLeftOpen,
    LogOut
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
      menuItems: [
        { name: "Panel Principal", page: "dashboard", icon: LayoutDashboard },
        { name: "Inventario", page: "inventory", icon: Package },
        { name: "Registrar Activo", page: "registerAsset", icon: FilePlus },      
        { name: "Reportes", page: "reports", icon: FileText },
        { name: "Usuarios", page: "users", icon: Users },
        { name: "Mantenimiento", page: "maintenance", icon: Wrench },
        { name: "Configuración", page: "settings", icon: Settings }
        
      ]
    }
  }
}
</script>