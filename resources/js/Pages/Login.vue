<template>
  <main class="login-page">
    <section class="login-container">
      <div class="institution-header">
        <div class="logo-container">
          <img :src="logoPath" alt="Logo ITCA FEPADE" class="logo-img" />
        </div>

        <h1>ITCA-FEPADE</h1>
        <p>Sistema de Gestión de Inventarios RFID</p>
      </div>

      <form class="login-card" @submit.prevent="handleLogin">
        <h2>Inicio de Sesión</h2>

        <div class="form-group">
          <label>Usuario o Correo Institucional</label>
          <div class="input-wrapper">
            <Mail class="input-icon" size="22" />
            <input
              v-model="correo"
              type="email"
              placeholder="ejemplo@itca.edu.sv"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label>Contraseña</label>
          <div class="input-wrapper">
            <Lock class="input-icon" size="22" />
            <input
              v-model="password"
              type="password"
              placeholder="••••••••"
              required
            />
          </div>
        </div>

        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>

        <button type="submit" class="login-button">
          Acceder al Sistema
        </button>
         <p v-if="error" class="error-message">
           {{ error }}
        </p>
        <p class="security-text">
          Sistema autorizado únicamente para personal del ITCA-FEPADE
        </p>
      </form>

      <div class="info-box">
        <Lightbulb size="20" />
        <span>
          Haz clic en "Acceder al Sistema" para ingresar al panel principal
        </span>
      </div>
    </section>
  </main>
</template>

<script>
import { Mail, Lock, Lightbulb } from "lucide-vue-next"
import api from "../services/api"

export default {
  name: "Login",
  components: {
    Mail,
    Lock,
    Lightbulb
  },
  emits: ["login-success"],
  data() {
    return {
      correo: "",
      password: "",
      error:"",
      logoPath: "/images/logo-itca.png"
    }
  },
  methods: {
    async handleLogin() {
      this.error = ""
      try {

       const response = await api.post( "/login",
        {
          correo: this.correo,
          password: this.password
        })

       localStorage.setItem( "token",response.data.token)

       localStorage.setItem("usuario",JSON.stringify(response.data.usuario))

       this.$emit('login-success')

      } catch (error) {

       this.error = "Correo o contraseña incorrectos"
       console.error(error)

      }
    }
  }
}
</script>