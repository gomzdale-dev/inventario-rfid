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
    <Reports v-if="activePage === 'reports'" />
  </AppLayout>
</template>

<script>
import Login from "./Pages/Login.vue"
import Dashboard from "./Pages/Dashboard.vue"
import Reports from "./Pages/Reports.vue"
import AppLayout from "./Layouts/AppLayout.vue"
import Inventory from "./Pages/Inventory.vue"

export default {
  name: "App",
  components: {
    Login,
    Dashboard,
    Reports,
    AppLayout,
    Inventory
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