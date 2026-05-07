<template>
  <AppLayout>
    <section>
      <h1>Dashboard</h1>
      <p>Bienvenido al sistema de inventario RFID.</p>
    </section>
  </AppLayout>
</template>

<script>
import AppLayout from "../Layouts/AppLayout.vue"

export default {
  name: "Dashboard",
  components: {
    AppLayout
  }
}
</script>

<style scoped>
h1 {
  font-size: 32px;
  margin-bottom: 8px;
  color: #111827;
}

p {
  color: #4b5563;
}
</style>