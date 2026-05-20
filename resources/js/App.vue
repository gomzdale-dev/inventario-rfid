<template>
  <Login
    v-if="!isAuthenticated"
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
    Users,
    Maintenance
  },

  data() {
    return {
      isAuthenticated: false,
      activePage: "dashboard",
      activeCatalog: "categorias"
    }
  },

  methods: {
    login() {
      this.isAuthenticated = true
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
      this.isAuthenticated = false
      this.activePage = "dashboard"
      this.activeCatalog = "categorias"
    }
  }
}
</script>