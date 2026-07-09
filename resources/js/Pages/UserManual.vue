<template>
  <section class="help-page">
    <header class="help-page-header">
      <h1>Manual de usuario</h1>
      <p>Guía rápida para utilizar el Sistema de Inventario RFID de ITCA-FEPADE.</p>
    </header>

    <div class="help-grid">
      <article class="help-card">
        <h2>Inicio de sesión</h2>
        <ol>
          <li>Ingresar el correo institucional autorizado.</li>
          <li>Escribir la contraseña del usuario.</li>
          <li>Presionar el botón Acceder al Sistema.</li>
        </ol>
      </article>

      <article class="help-card">
        <h2>Panel principal</h2>
        <p>Permite visualizar indicadores generales del sistema, como activos registrados, equipos asignados, equipos en mantenimiento y actividad reciente.</p>
      </article>

      <article class="help-card">
        <h2>Inventario</h2>
        <p>Permite consultar activos tecnológicos, realizar búsquedas, revisar códigos asociados y apoyar el proceso de inventario físico.</p>
      </article>

      <article class="help-card">
        <h2>Módulo de activos</h2>
        <ul>
          <li><strong>Registrar Activos:</strong> crea un activo nuevo en la base de datos.</li>
          <li><strong>Activos:</strong> muestra activos registrados y permite ver detalle, asignar responsable o cambiar etiqueta.</li>
          <li><strong>Registrar Movimientos:</strong> registra traslados, entradas, salidas o cambios de ubicación.</li>
        </ul>
      </article>

      <article class="help-card">
        <h2>Usuarios</h2>
        <p>Permite administrar usuarios del sistema, roles y accesos permitidos según el tipo de usuario.</p>
      </article>

      <article class="help-card">
        <h2>Mantenimiento</h2>
        <p>Permite administrar catálogos base como categorías, marcas, modelos, laboratorios, edificios y responsables.</p>
      </article>

      <article class="help-card">
        <h2>Reportes</h2>
        <p>Permite consultar reportes de inventario general, activos por categoría, ubicación, estado, lecturas RFID, activos no encontrados y diferencias entre inventarios.</p>
      </article>

      <article class="help-card">
        <h2>Notificaciones</h2>
        <p>La campana muestra alertas RFID o IA. Desde ahí se pueden filtrar, marcar como leídas, ver detalle o eliminar notificaciones.</p>
      </article>

      <article class="help-card help-wide">
        <h2>Recomendaciones de uso</h2>
        <ul>
          <li>Verificar que XAMPP y MySQL estén activos antes de iniciar el sistema.</li>
          <li>Iniciar Laravel con <strong>php artisan serve --port=8001</strong>.</li>
          <li>Iniciar Vite con <strong>npm run dev</strong>.</li>
          <li>Actualizar la página si se realizan cambios en el código fuente.</li>
        </ul>
      </article>
    </div>
  </section>
</template>

<script>
export default {
  name: "UserManual"
}
</script>
