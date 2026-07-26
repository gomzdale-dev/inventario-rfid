<template>
  <section class="manual-page">
    <header class="manual-header">
      <div class="manual-badge">Documentación Audiovisual</div>
      <h1>Manual de Usuario</h1>
      <p>Video-guías detalladas para comprender el flujo de operación de cada módulo del sistema.</p>
    </header>

    <div class="manual-grid">
      <!-- 1. Inicio de Sesión -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">01</span>
          <h2>Inicio de Sesión</h2>
        </div>
        <p class="manual-desc">Acceso al sistema</p>
        <div class="video-container">
          <iframe 
            src="https://app.arcade.software/share/BDQs7mfHVosZQa6HxbFX" 
            title="Inicio de Sesión" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

      <!-- 2. Panel Principal (Dashboard) -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">02</span>
          <h2>Panel Principal (Dashboard)</h2>
        </div>
        <p class="manual-desc">Visualización de métricas globales, estado de los laboratorios y alertas tempranas del inventario.</p>
        <div class="video-container">
          <iframe 
            src="https://app.supademo.com/demo/cmrzwhbve6ep6qmblh6dpovet?utm_source=link" 
            title="Panel Principal" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

      <!-- 3. Control de Inventario -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">03</span>
          <h2>Control de Inventario</h2>
        </div>
        <p class="manual-desc">Consulta rápida de existencias, estados de los activos y rastreo de ubicaciones institucionales.</p>
        <div class="video-container">
          <iframe 
            src="https://app.arcade.software/share/fBCCx0QDvJs0dbJPuAl3" 
            title="Control de Inventario" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

      <!-- 4. Gestión de Activos y Movimientos (RFID) -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">04</span>
          <h2>Gestión de Activos y Movimientos</h2>
        </div>
        <p class="manual-desc">Cómo registrar nuevos equipos informáticos, asignar responsables y realizar transferencias o traslados entre laboratorios usando lecturas de hardware.</p>
        <div class="video-container">
          <iframe 
            src="https://app.arcade.software/share/eU23PbCYVFGqLVga5SVi" 
            title="Gestión de Activos" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

      <!-- 5. Administración de Usuarios y Roles -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">05</span>
          <h2>Administración de Usuarios y Roles</h2>
        </div>
        <p class="manual-desc">Control de accesos para Super Administradores y encargados de laboratorio. Creación de credenciales y asignación de permisos seguros.</p>
        <div class="video-container">
          <iframe 
            src="https://app.arcade.software/share/z3yMQL7JvB0aB6IMCxLi" 
            title="Administración de Usuarios" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

      <!-- 6. Mantenimiento de Catálogos Base -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">06</span>
          <h2>Mantenimiento de Catálogos Base</h2>
        </div>
        <p class="manual-desc">Gestión y parametrización de tablas maestras del sistema: marcas, modelos, categorías y codificación de áreas físicas.</p>
        <div class="video-container">
          <iframe 
            src="https://app.supademo.com/demo/cmrzujygx6e8jqmblf9cxxzid?utm_source=link" 
            title="Mantenimiento de Catálogos" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

      <!-- 7. Reportes y Auditorías Avanzadas -->
      <article class="manual-card">
        <div class="card-title-zone">
          <span class="step-number">07</span>
          <h2>Reportes y Auditorías Avanzadas</h2>
        </div>
        <p class="manual-desc">Generación de reportes de conciliación física para verificar que los activos escaneados por hardware coincidan exactamente con lo registrado en el software.</p>
        <div class="video-container">
          <iframe 
            src="https://app.arcade.software/share/bpFIcz8qP7Mao27EMQE7" 
            title="Reportes y Auditorías" 
            allowfullscreen>
          </iframe>
        </div>
      </article>

    </div>
  </section>
</template>

<script>
export default {
  name: "UserManual"
}
</script>

<style scoped>
.manual-page {
  max-width: 850px;
  margin: 0 auto;
  padding: 30px 20px;
  font-family: system-ui, -apple-system, sans-serif;
}

.manual-header {
  text-align: center;
  margin-bottom: 40px;
}

.manual-badge {
  display: inline-block;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 4px 12px;
  border-radius: 9999px;
  margin-bottom: 12px;
  border: 1px solid #bfdbfe;
}

.manual-header h1 {
  font-size: 2rem;
  color: #0f172a;
  margin: 0 0 10px 0;
  font-weight: 800;
  letter-spacing: -0.025em;
}

.manual-header p {
  font-size: 0.95rem;
  color: #64748b;
  max-width: 550px;
  margin: 0 auto;
}

/* Grilla de Manuales */
.manual-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

.manual-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 22px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
}

.card-title-zone {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.step-number {
  background: #f1f5f9;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}

.manual-card h2 {
  font-size: 1.2rem;
  color: #1e293b;
  margin: 0;
  font-weight: 700;
}

.manual-desc {
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0 0 16px 0;
}

/* Contenedor del iframe adaptable */
.video-container {
  position: relative;
  width: 100%;
  padding-bottom: 56.25%; /* Relación de aspecto 16:9 */
  height: 0;
  border-radius: 8px;
  overflow: hidden;
  background: #0f172a;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.video-container iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
</style>