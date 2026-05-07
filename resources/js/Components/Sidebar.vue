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
        <a v-for="item in menuItems" :key="item.name" href="#" class="menu-item">
          <component :is="item.icon" size="21" />
          <span v-if="!isCollapsed">{{ item.name }}</span>
        </a>
      </nav>
    </div>

    <div class="logout-area">
      <a href="#" class="menu-item logout-item">
        <LogOut size="21" />
        <span v-if="!isCollapsed">Cerrar sesión</span>
      </a>
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
    }
  },
  emits: ["toggle-sidebar"],
  data() {
    return {
      menuItems: [
        { name: "Dashboard", icon: LayoutDashboard },
        { name: "Inventario", icon: Package },
        { name: "Lecturas RFID", icon: ScanLine },
        { name: "Reportes", icon: FileText },
        { name: "Usuarios", icon: Users },
        { name: "Mantenimiento", icon: Wrench },
        { name: "Configuración", icon: Settings }
      ]
    }
  }
}
</script>

<style scoped>
.sidebar {
  width: 270px;
  min-height: 100vh;
  background: linear-gradient(180deg, #7a1f1f 0%, #4b1717 55%, #2a1111 100%);
  color: white;
  padding: 22px 18px;
  transition: width 0.35s ease;
  box-shadow: 8px 0 30px rgba(80, 20, 20, 0.25);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.sidebar.collapsed {
  width: 92px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 34px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #ffffff;
  color: #7a1f1f;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 22px;
  box-shadow: 0 0 18px rgba(255, 255, 255, 0.35);
}

.brand-text {
  font-size: 25px;
  font-weight: 800;
  letter-spacing: 1px;
}

.toggle-btn {
  border: none;
  background: rgba(255, 255, 255, 0.12);
  color: white;
  width: 38px;
  height: 38px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-btn:hover {
  background: rgba(211, 159, 72, 0.35);
  box-shadow: 0 0 14px rgba(211, 159, 72, 0.7);
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 13px;
}

.menu-item {
  position: relative;
  display: flex;
  align-items: center;
  gap: 14px;
  min-height: 48px;
  color: #f9f4ee;
  text-decoration: none;
  padding: 12px 14px;
  border-radius: 15px;
  font-weight: 600;
  transition: all 0.25s ease;
  white-space: nowrap;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  transform: translateX(5px);
  box-shadow:
    0 0 0 1px rgba(211, 159, 72, 0.65),
    0 0 18px rgba(211, 159, 72, 0.65);
}

.logout-area {
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.18);
}

.logout-item {
  color: #ffd7d7;
}

.logout-item:hover {
  background: rgba(255, 60, 60, 0.16);
  box-shadow:
    0 0 0 1px rgba(255, 150, 150, 0.7),
    0 0 18px rgba(255, 80, 80, 0.55);
}

.sidebar.collapsed .menu-item {
  justify-content: center;
  padding: 12px;
}

.sidebar.collapsed .menu-item:hover {
  transform: scale(1.08);
}
</style>