<template>
  <Login
    v-if="!usuario"
    @login-success="login"
  />

  <AppLayout
    v-else
    :active-page="activePage"
    @navigate="changePage"
    @logout="logout"
  >
    <Dashboard v-if="activePage === 'dashboard'" />

    <Inventory v-if="activePage === 'inventory'" />

    <AssetMovements v-if="activePage === 'assetMovements'" />

    <AssetsList v-if="activePage === 'assetsList'" />

    <RegisterAsset v-if="activePage === 'registerAsset'" />

    <Reports v-if="activePage === 'reports'" />

    <Users v-if="activePage === 'users'" />

    <Maintenance
      v-if="activePage === 'maintenance'"
      :active-catalog="activeCatalog"
    />
  </AppLayout>
</template>

<script>
import Login from "./Pages/Login.vue"
import Dashboard from "./Pages/Dashboard.vue"
import Reports from "./Pages/Reports.vue"
import AppLayout from "./Layouts/AppLayout.vue"
import Inventory from "./Pages/Inventory.vue"
import RegisterAsset from "./Pages/RegisterAsset.vue"
import AssetMovements from "./Pages/AssetMovements.vue"
import AssetsList from "./Pages/AssetsList.vue"
import Users from "./Pages/Users.vue"
import Maintenance from "./Pages/Maintenance.vue"

export default {
  name: "App",

  components: {
    Login,
    Dashboard,
    Reports,
    AppLayout,
    Inventory,
    RegisterAsset,
    AssetMovements,
    AssetsList,
    Users,
    Maintenance
  },

  data() {
    return {
      usuario: JSON.parse(localStorage.getItem("usuario")) || null,
      activePage: "dashboard",
      activeCatalog: "categorias" // ✅ FIX: declarado en data()
    }
  },

  methods: {
    login() {
      this.usuario = JSON.parse(localStorage.getItem("usuario"))
      this.activePage = "dashboard"
    },

    changePage(payload) {
      if (typeof payload === "string") {
        this.activePage = payload
        return
      }

      this.activePage = payload.page

      if (payload.catalog) {
        this.activeCatalog = payload.catalog
      }
    },

    logout() {
      localStorage.removeItem("token")
      localStorage.removeItem("usuario")
      this.usuario = null
      this.activePage = "dashboard"
    }
  }
}
</script>