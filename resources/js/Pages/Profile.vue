<template>
  <div class="profile-view">
    <!-- Tarjeta Principal con Identidad Institucional -->
    <div class="profile-hero-section">
      <div class="profile-accent-bar"></div>
      
      <div class="profile-content-inner">
        <div class="profile-avatar-container">
          <div class="profile-avatar-circle">{{ userInitials }}</div>
          <span class="status-badge" title="Sesión Activa"></span>
        </div>

        <div class="profile-info-header">
          <h2>{{ userName }}</h2>
          <p class="profile-email">{{ userEmail }}</p>
          <span class="role-badge">{{ userRole }}</span>
        </div>
      </div>
    </div>

    <!-- Detalles de la Cuenta -->
    <div class="profile-details-card">
      <div class="section-title">
        <h3>Detalles de la Cuenta</h3>
        <p>Información registrada en el sistema de inventario</p>
      </div>

      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">Nombre de Usuario</span>
          <span class="info-data">{{ userName }}</span>
        </div>

        <div class="info-item">
          <span class="info-label">Correo Electrónico</span>
          <span class="info-data">{{ userEmail }}</span>
        </div>

        <div class="info-item">
          <span class="info-label">Rol Asignado</span>
          <span class="info-data highlight">{{ userRole }}</span>
        </div>

        <div class="info-item">
          <span class="info-label">Estado de la Sesión</span>
          <span class="info-data status-active">Activo</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Profile",

  data() {
    return {
      usuario: JSON.parse(localStorage.getItem("usuario")) || {}
    }
  },

  computed: {
    userName() {
      return this.usuario?.nombre_usuario || "Usuario del Sistema"
    },
    userEmail() {
      return this.usuario?.correo || this.usuario?.email || "correo@ejemplo.com"
    },
    userRole() {
      if (this.usuario?.rol_nombre) {
        return this.usuario.rol_nombre
      }
      if (this.usuario?.tipo_usuario?.nombre_tipo) {
        return this.usuario.tipo_usuario.nombre_tipo
      }
      return "Usuario"
    },
    userInitials() {
      return this.userName
        .split(" ")
        .slice(0, 2)
        .map(word => word[0])
        .join("")
        .toUpperCase()
    }
  }
}
</script>

<style scoped>
.profile-view {
  max-width: 850px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Tarjeta superior con toque institucional */
.profile-hero-section {
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(111, 63, 24, 0.1);
  text-align: center;
  position: relative;
}

/* Franja superior con el color institucional exacto (#6f3f18) */
.profile-accent-bar {
  height: 8px;
  background: #6f3f18;
  width: 100%;
}

.profile-content-inner {
  padding: 32px 32px 36px 32px;
}

.profile-avatar-container {
  position: relative;
  display: inline-block;
  margin-bottom: 16px;
}

.profile-avatar-circle {
  width: 96px;
  height: 96px;
  background: #ffffff;
  color: #ef4444; /* Letras rojas */
  border: 3px solid #6f3f18; /* Borde del círculo café institucional */
  font-size: 32px;
  font-weight: 800;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 20px rgba(111, 63, 24, 0.12);
}

.status-badge {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 18px;
  height: 18px;
  min-width: 18px;
  min-height: 18px;
  background: #22c55e;
  border: 3px solid #ffffff;
  border-radius: 50%;
  aspect-ratio: 1 / 1;
  box-sizing: border-box;
  display: inline-block;
}

.profile-info-header h2 {
  margin: 0 0 4px 0;
  font-size: 26px;
  color: #1e293b;
  font-weight: 700;
}

.profile-email {
  margin: 0 0 16px 0;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
}

.role-badge {
  background: #fef2f2;
  color: #ef4444;
  padding: 6px 18px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.3px;
  border: 1px solid #fee2e2;
  display: inline-block;
}

/* Tarjeta de Detalles */
.profile-details-card {
  background: #ffffff;
  padding: 36px;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.03);
}

.section-title h3 {
  margin: 0 0 4px 0;
  font-size: 18px;
  color: #1a1a1a;
  font-weight: 700;
}

.section-title p {
  margin: 0 0 24px 0;
  font-size: 13px;
  color: #94a3b8;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 650px) {
  .info-grid {
    grid-template-columns: 1fr;
  }
}

.info-item {
  background: #f8fafc;
  padding: 16px 20px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  border: 1px solid #f1f5f9;
}

.info-label {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.info-data {
  font-size: 15px;
  color: #1e293b;
  font-weight: 500;
  word-break: break-all;
}

.info-data.highlight {
  color: #ef4444;
  font-weight: 700;
}

.status-active {
  color: #22c55e;
  font-weight: 700;
}
</style>