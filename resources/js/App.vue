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

export default {
  name: "App",
  components: {
    Login,
    Dashboard,
    Reports,
    AppLayout,
    Inventory,
    RegisterAsset,
    Users
  },
  data() {
    return {
      isAuthenticated: false,
      activePage: "dashboard"
    }
  },
  methods: {
    login() {
      this.isAuthenticated = true
      this.activePage = "dashboard"
    },
    changePage(page) {
      this.activePage = page
    },
    logout() {
      this.isAuthenticated = false
      this.activePage = "dashboard"
    }
  }
}
</script>