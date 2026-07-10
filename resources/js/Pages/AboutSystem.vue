<template>
  <section class="help-page">
    <header class="help-page-header">
      <h1>Acerca del sistema</h1>
      <p>Información general del proyecto Sistema de Inventario RFID.</p>
    </header>

    <div class="help-grid">
      <article class="help-card about-system-card help-wide">
        <h2>Sistema de Inventario RFID</h2>
        <p>Aplicación desarrollada para apoyar la gestión, consulta, control y trazabilidad de activos tecnológicos dentro de laboratorios institucionales.</p>
      </article>

      <article class="help-card">
        <h2>Objetivo</h2>
        <p>Facilitar el registro de activos, movimientos, responsables, ubicaciones, reportes y alertas para mejorar el control administrativo del inventario.</p>
      </article>

      <article class="help-card">
        <h2>Módulos principales</h2>
        <ul>
          <li>Panel principal</li>
          <li>Inventario</li>
          <li>Módulo de activos</li>
          <li>Usuarios</li>
          <li>Mantenimiento</li>
          <li>Reportes</li>
          <li>Notificaciones inteligentes</li>
        </ul>
      </article>

      <article class="help-card">
        <h2>Tecnologías utilizadas</h2>
        <div class="help-tag-list">
          <span class="help-tag">Laravel</span>
          <span class="help-tag">Vue.js</span>
          <span class="help-tag">MySQL</span>
          <span class="help-tag">Vite</span>
          <span class="help-tag">API REST</span>
          <span class="help-tag">Isolation Forest</span>
        </div>
      </article>

      <article class="help-card">
        <h2>Equipo</h2>
        <p>Proyecto académico desarrollado para ITCA-FEPADE como parte del proceso de tesis de Ingeniería en Desarrollo de Software.</p>
      </article>
    </div>
  </section>
</template>

<script>
export default {
  name: "AboutSystem"
}
</script>
