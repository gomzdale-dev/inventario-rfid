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
              v-model="email"
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
    email: "",
    password: "",
    logoPath: "/images/logo-itca.png"
  }
},
  methods: {
    handleLogin() {
      this.$emit("login-success")
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top, rgba(220, 38, 38, 0.08), transparent 34%),
    linear-gradient(135deg, #f8fafc 0%, #f3f4f6 48%, #ece7df 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 36px 20px;
  color: #071632;
}

.login-container {
  width: 100%;
  max-width: 590px;
  animation: fadeUp 0.75s ease both;
}

.institution-header {
  text-align: center;
  margin-bottom: 34px;
}

.logo-container {
  width: 160px;
  height: 160px;
  margin: 0 auto 30px;
  border-radius: 50%;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 6px solid rgba(220, 38, 38, 0.12);
  box-shadow:
    0 25px 55px rgba(127, 29, 29, 0.22),
    0 0 45px rgba(211, 159, 72, 0.18);
  animation: floatLogo 3s ease-in-out infinite;
}

.logo-img {
  width: 200px;
  height: auto;
  object-fit: contain;
}

.logo-circle span {
  font-size: 25px;
  font-weight: 900;
  letter-spacing: 1px;
}

.logo-circle small {
  font-size: 15px;
  font-weight: 700;
}

.institution-header h1 {
  margin: 0;
  font-size: 40px;
  font-weight: 900;
  color: #071632;
  letter-spacing: -1px;
}

.institution-header p {
  margin-top: 10px;
  font-size: 19px;
  color: #475569;
}

.login-card {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  border-radius: 24px;
  padding: 44px 42px;
  box-shadow:
    0 26px 70px rgba(15, 23, 42, 0.13),
    0 0 0 1px rgba(255, 255, 255, 0.7);
}

.login-card h2 {
  margin: 0 0 30px;
  font-size: 31px;
  font-weight: 900;
  color: #071632;
}

.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  margin-bottom: 10px;
  font-size: 16px;
  font-weight: 800;
  color: #17213a;
}

.input-wrapper {
  height: 62px;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 16px;
  border: 1.8px solid #d1d5db;
  border-radius: 14px;
  background: white;
  transition: all 0.25s ease;
}

.input-wrapper:focus-within {
  border-color: #dc2626;
  box-shadow:
    0 0 0 4px rgba(220, 38, 38, 0.13),
    0 0 22px rgba(220, 38, 38, 0.18);
  transform: translateY(-1px);
}

.input-icon {
  color: #94a3b8;
  flex-shrink: 0;
}

.input-wrapper input {
  width: 100%;
  border: none;
  outline: none;
  font-size: 18px;
  color: #111827;
}

.input-wrapper input::placeholder {
  color: #9ca3af;
}

.forgot-link {
  display: block;
  text-align: right;
  color: #dc2626;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  margin: 4px 0 26px;
}

.forgot-link:hover {
  color: #991b1b;
  text-decoration: underline;
}

.login-button {
  width: 100%;
  height: 62px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  color: white;
  font-size: 18px;
  font-weight: 900;
  cursor: pointer;
  box-shadow: 0 16px 28px rgba(220, 38, 38, 0.28);
  transition: all 0.25s ease;
}

.login-button:hover {
  background: linear-gradient(135deg, #b91c1c, #7f1d1d);
  transform: translateY(-2px);
  box-shadow:
    0 20px 34px rgba(185, 28, 28, 0.38),
    0 0 22px rgba(220, 38, 38, 0.38);
}

.login-button:active {
  transform: translateY(0);
}

.security-text {
  margin: 28px auto 0;
  max-width: 390px;
  text-align: center;
  color: #64748b;
  font-size: 16px;
  line-height: 1.45;
}

.info-box {
  margin-top: 20px;
  min-height: 74px;
  border-radius: 18px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-weight: 800;
  text-align: center;
  padding: 16px 22px;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(24px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulseLogo {
  0%, 100% {
    transform: scale(1);
    box-shadow:
      0 18px 34px rgba(220, 38, 38, 0.32),
      0 0 0 8px rgba(220, 38, 38, 0.08);
  }

  50% {
    transform: scale(1.04);
    box-shadow:
      0 22px 42px rgba(220, 38, 38, 0.42),
      0 0 0 12px rgba(220, 38, 38, 0.1);
  }
}

@keyframes floatLogo {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-6px);
  }
}

@media (max-width: 640px) {
  .institution-header h1 {
    font-size: 32px;
  }

  .login-card {
    padding: 34px 24px;
  }

  .login-card h2 {
    font-size: 27px;
  }
}
</style>