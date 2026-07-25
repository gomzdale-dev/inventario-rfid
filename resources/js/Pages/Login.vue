<template>
  <main class="login-page">
    <section class="login-container">
      <div class="institution-header">
        <div class="login-logo-container">
          <img
            :src="iconItca"
            alt="ITCA-FEPADE"
            class="login-logo"
          />
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
              autocomplete="username"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label>Contraseña</label>

          <div class="input-wrapper password-wrapper">
            <Lock class="input-icon" size="22" />

            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            />

            <button
              type="button"
              class="password-toggle"
              :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              @click="togglePasswordVisibility"
            >
              <EyeOff v-if="showPassword" size="22" />
              <Eye v-else size="22" />
            </button>
          </div>
        </div>

        <a href="#" class="forgot-link">
          ¿Olvidaste tu contraseña?
        </a>

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
import {
  Mail,
  Lock,
  Lightbulb,
  Eye,
  EyeOff
} from "lucide-vue-next"

import api from "../services/api"

const iconItca = "/images/icon-itca.png"

export default {
  name: "Login",

  components: {
    Mail,
    Lock,
    Lightbulb,
    Eye,
    EyeOff
  },

  emits: ["login-success"],

  data() {
    return {
      correo: "",
      password: "",
      showPassword: false,
      error: "",
      iconItca
    }
  },

  methods: {
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword
    },

    async handleLogin() {
      this.error = ""

      try {
        const response = await api.post("/login", {
          correo: this.correo,
          password: this.password
        })

        localStorage.setItem("token", response.data.token)
        localStorage.setItem(
          "usuario",
          JSON.stringify(response.data.usuario)
        )

        this.$emit("login-success")
      } catch (error) {
        this.error = "Correo o contraseña incorrectos"
        console.error(error)
      }
    }
  }
}
</script>

<style scoped>
.password-wrapper {
  position: relative;
}

.password-wrapper input {
  padding-right: 58px;
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 17px;
  display: grid;
  place-items: center;
  width: 40px;
  height: 40px;
  padding: 0;
  border: none;
  border-radius: 10px;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  transform: translateY(-50%);
  transition:
    color 0.2s ease,
    background-color 0.2s ease,
    transform 0.2s ease;
}

.password-toggle:hover {
  background: rgba(226, 38, 43, 0.08);
  color: #e2262b;
}

.password-toggle:focus-visible {
  outline: 3px solid rgba(226, 38, 43, 0.18);
  color: #e2262b;
}

.password-toggle:active {
  transform: translateY(-50%) scale(0.94);
}
</style>