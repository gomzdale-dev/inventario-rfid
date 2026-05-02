<template>
  <Login
    v-if="!isAuthenticated"
    @login-success="login"
  />

  <Dashboard v-else />
</template>

<script>
import Login from "./Pages/Login.vue"
import Dashboard from "./Pages/Dashboard.vue"

export default {
  name: "App",
  components: {
    Login,
    Dashboard
  },
  data() {
    return {
      isAuthenticated: false
    }
  },
  methods: {
    login() {
      this.isAuthenticated = true
    }
  }
}
</script>