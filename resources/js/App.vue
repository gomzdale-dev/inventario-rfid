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
    <Reports v-if="activePage === 'reports'" />
  </AppLayout>
</template>

<script>
import Login from "./Pages/Login.vue"
import Dashboard from "./Pages/Dashboard.vue"
import Reports from "./Pages/Reports.vue"
import AppLayout from "./Layouts/AppLayout.vue"

export default {
  name: "App",
  components: {
    Login,
    Dashboard,
    Reports,
    AppLayout
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