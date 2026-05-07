<template>
  <section class="reports-page">
    <header class="reports-header">
      <h1>Historial y Reportes de Inventario</h1>
      <p>Consulta de historial y generación de reportes analíticos</p>
    </header>

    <div class="reports-grid">
      <div class="report-card main-report">
        <h2><FileText size="24" /> Generar Nuevo Reporte</h2>

        <label>Tipo de Reporte</label>
        <div class="report-types">
          <button class="type-card selected">📊 Historial de Movimientos</button>
          <button class="type-card">📦 Inventario Actualizado</button>
          <button class="type-card">🔧 Equipos en Mantenimiento</button>
        </div>

        <div class="date-grid">
          <div>
            <label>Fecha Inicio</label>
            <input type="text" value="01/03/2026" />
          </div>
          <div>
            <label>Fecha Fin</label>
            <input type="text" value="18/03/2026" />
          </div>
        </div>

        <label>Formato de Exportación</label>
        <div class="format-grid">
          <button class="format active">PDF</button>
          <button class="format">Excel</button>
        </div>

        <label>Filtros Adicionales (opcional)</label>
        <div class="filter-grid">
          <select><option>Todas las ubicaciones</option></select>
          <select><option>Todos los estados</option></select>
        </div>

        <button class="download-btn">
          <Download size="22" />
          Generar y Descargar Reporte
        </button>
      </div>

      <aside class="report-card recent-reports">
        <h2>Reportes Recientes</h2>

        <div v-for="report in reports" :key="report.title" class="recent-item">
          <FileText class="recent-icon" size="24" />
          <div>
            <h3>{{ report.title }}</h3>
            <p>{{ report.date }}</p>
          </div>
          <span :class="['badge', report.type.toLowerCase()]">{{ report.type }}</span>
          <small>{{ report.size }}</small>
        </div>

        <button class="outline-btn">Ver Todos los Reportes</button>
      </aside>
    </div>

    <div class="bottom-grid">
      <section class="report-card frequent-assets">
        <h2>📈 Activos con Mayor Frecuencia de Uso</h2>

        <div v-for="asset in assets" :key="asset.name" class="asset-row">
          <span class="rank">{{ asset.rank }}</span>
          <div>
            <h3>{{ asset.name }}</h3>
            <p>{{ asset.info }}</p>
          </div>
          <strong>{{ asset.moves }}</strong>
          <small>movimientos</small>
        </div>
      </section>

      <section class="advanced-box">
        <h2>Análisis Avanzado</h2>
        <p>
          Los reportes incluyen análisis estadístico y visualizaciones que facilitan
          la toma de decisiones sobre gestión de recursos y planificación de mantenimiento.
        </p>
      </section>
    </div>
  </section>
</template>

<script>
import { FileText, Download } from "lucide-vue-next"

export default {
  name: "Reports",
  components: {
    FileText,
    Download
  },
  data() {
    return {
      reports: [
        { title: "Inventario General - Marzo 2026", date: "2026-03-18", type: "PDF", size: "2.4 MB" },
        { title: "Movimientos Semanales", date: "2026-03-15", type: "Excel", size: "156 KB" },
        { title: "Alertas del Mes", date: "2026-03-10", type: "PDF", size: "892 KB" },
        { title: "Auditoría Q1 2026", date: "2026-03-01", type: "PDF", size: "3.1 MB" }
      ],
      assets: [
        { rank: 1, name: "Computadora Dell OptiPlex 7090", info: "Lab A-102 • RFID-2847", moves: 145 },
        { rank: 2, name: "Arduino Mega 2560", info: "Lab C-301 • RFID-8834", moves: 132 },
        { rank: 3, name: "Laptop HP EliteBook 840", info: "Lab A-102 • RFID-4521", moves: 118 }
      ]
    }
  }
}
</script>