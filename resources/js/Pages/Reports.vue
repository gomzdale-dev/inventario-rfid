<template>
  <section class="reports-page">
    <header class="reports-header">
      <h1>Historial y Reportes de Inventario</h1>
      <p>Consulta y generación de reportes conectados a la base de datos</p>
    </header>

    <div class="reports-layout">
      <aside class="report-card reports-menu-card">
        <h2><FileText size="24" /> Catálogo de Reportes</h2>

        <button
          v-for="report in reportTypes"
          :key="report.value"
          type="button"
          :class="['report-menu-item', { active: form.tipo_reporte === report.value }]"
          @click="selectReport(report.value)"
        >
          <span class="report-icon">{{ report.icon }}</span>
          <span>
            <strong>{{ report.title }}</strong>
            <small>{{ report.utility }}</small>
          </span>
        </button>
      </aside>

      <section class="report-card main-report">
        <h2><FileText size="24" /> {{ selectedReport.title }}</h2>
        <p class="report-description">{{ selectedReport.description }}</p>

        <div class="report-fields-box">
          <p><strong>Utilidad:</strong> {{ selectedReport.utility }}</p>
        </div>

        <div class="date-grid">
          <div>
             <label>Fecha Inicio</label>
                <input type="date" v-model="form.fecha_inicio" :max="fechaMaxima" @change="loadPreview" />
          </div>
       <div>
         <label>Fecha Fin</label>
          <input type="date" v-model="form.fecha_fin" :max="fechaMaxima" @change="loadPreview" />
       </div>
       </div>
        <label>Filtros Adicionales (opcional)</label>
        <div class="filter-grid">
          <select v-model="form.id_ubicacion" @change="loadPreview">
            <option :value="null">Todas las ubicaciones</option>
            <option
              v-for="ubi in catalogos.ubicaciones"
              :key="ubi.id_ubicacion"
              :value="ubi.id_ubicacion"
            >
              {{ nombreUbicacion(ubi) }}
            </option>
          </select>

          <select v-model="form.id_estado" @change="loadPreview">
            <option :value="null">Todos los estados</option>
            <option
              v-for="estado in catalogos.estados"
              :key="estado.id_estado"
              :value="estado.id_estado"
            >
              {{ estado.nombre_estado }}
            </option>
          </select>
        </div>
        <label>Formato de Exportación</label>
        <div class="format-grid">
          <button
            type="button"
            :class="['format', { active: form.formato === 'pdf' }]"
            @click="form.formato = 'pdf'"
          >
            PDF
          </button>
          <button
            type="button"
            :class="['format', { active: form.formato === 'excel' }]"
            @click="form.formato = 'excel'"
          >
            Excel
          </button>
        </div>

        <button class="download-btn" @click="procesarReporte" :disabled="cargando">
          <Download size="22" />
          {{ cargando ? "Generando..." : "Generar y Descargar Reporte" }}
        </button>
      </section>
    </div>

    <section class="report-card preview-card">
      <header class="preview-header">
        <div>
          <h2>Vista previa del reporte</h2>
          <p>{{ previewRows.length }} registros encontrados</p>
        </div>

        <button
          class="outline-btn"
          type="button"
          @click="loadPreview"
          :disabled="cargandoPreview"
        >
          <RefreshCcw size="20" />
          {{ cargandoPreview ? "Actualizando..." : "Actualizar" }}
        </button>
      </header>

      <div v-if="cargandoPreview" class="empty-preview">
        Cargando información real desde la base de datos...
      </div>

      <div v-else-if="previewRows.length === 0" class="empty-preview">
        No hay registros para este reporte o filtro.
      </div>

      <div v-else class="preview-table-wrapper">
        <table>
          <thead>
            <tr>
              <th v-for="column in previewColumns" :key="column.key">
                {{ column.label }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(row, index) in previewRows" :key="index">
              <td v-for="column in previewColumns" :key="column.key">
                {{ row[column.key] ?? "N/A" }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</template>

<script>
import { FileText, Download, RefreshCcw } from "lucide-vue-next"
import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "Reports",

  components: {
    FileText,
    Download,
    RefreshCcw
  },

  props: {
    activeReport: {
      type: String,
      default: "inventario_general"
    }
  },

  data() {
    return {
      cargando: false,
      cargandoPreview: false,
      form: {
        tipo_reporte: this.activeReport || "inventario_general",
        fecha_inicio: "",
        fecha_fin: "",
        formato: "pdf",
        id_ubicacion: null,
        id_estado: null
      },
      catalogos: {
        ubicaciones: [],
        estados: []
      },
      previewColumns: [],
      previewRows: [],
      reportTypes: [
        {
          value: "inventario_general",
          icon: "📦",
          title: "Inventario General de Activos",
          description: "Listado completo de los activos registrados en el sistema.",
          fields: ["Código del activo", "Nombre o descripción", "Categoría", "Ubicación", "Estado", "Etiqueta RFID asociada"],
          utility: "Conocer todos los activos registrados."
        },        
        {
          value: "activos_ubicacion",
          icon: "📍",
          title: "Activos por Ubicación",
          description: "Muestra los activos por edificio y laboratorio.",
          fields: ["Edificio", "Laboratorio", "Código", "Activo", "RFID", "Estado"],
          utility: "Verificar qué activos se encuentran en cada área."
        },
        {
          value: "activos_estado",
          icon: "✅",
          title: "Activos por Estado",
          description: "Clasifica los activos según su estado administrativo.",
          fields: ["Estado", "Código", "Activo", "Categoría", "Ubicación", "RFID"],
          utility: "Control administrativo."
        },
        {
          value: "activos_no_encontrados",
          icon: "⚠️",
          title: "Activos No Encontrados",
          description: "Compara activos registrados contra activos detectados en inventario.",
          fields: ["Código", "Descripción", "Última ubicación conocida"],
          utility: "Uno de los reportes más importantes."
        },
        {
          value: "diferencias_inventarios",
          icon: "🔁",
          title: "Diferencias entre Inventarios",
          description: "Compara dos inventarios registrados en fechas distintas.",
          fields: ["Activo", "Inventario anterior", "Inventario reciente", "Resultado"],
          utility: "Identificar activos encontrados, faltantes o cambios entre jornadas."
        },
        {
          value: "historial",
          icon: "📊",
          title: "Historial de Movimientos",
          description: "Reporte operativo de altas, traslados, mantenimiento y bajas.",
          fields: ["Fecha", "Tipo de movimiento", "Activo", "RFID", "Ubicación", "Usuario"],
          utility: "Revisar el movimiento histórico de los activos."
        },
        {
          value: "mantenimiento",
          icon: "🔧",
          title: "Equipos en Mantenimiento",
          description: "Filtra los activos marcados como mantenimiento.",
          fields: ["Código", "Activo", "RFID", "Ubicación", "Responsable", "Estado"],
          utility: "Controlar los equipos que requieren seguimiento técnico."
        }
      ]
    }
  },

  computed: {
    selectedReport() {
      return this.reportTypes.find(report => report.value === this.form.tipo_reporte) || this.reportTypes[0]
    },
    fechaMaxima() {
    const tzoffset = (new Date()).getTimezoneOffset() * 60000;
    return (new Date(Date.now() - tzoffset)).toISOString().split('T')[0];
  }
  },

  watch: {
    activeReport(newValue) {
      if (newValue && newValue !== this.form.tipo_reporte) {
        this.selectReport(newValue)
      }
    }
  },

  mounted() {
    this.cargarFiltros()
    this.loadPreview()
  },

  methods: {
    selectReport(type) {
      this.form.tipo_reporte = type
      this.loadPreview()
    },

    nombreUbicacion(ubicacion) {
      const laboratorio = ubicacion.laboratorio?.nombre_laboratorio || `Ubicación #${ubicacion.id_ubicacion}`
      const edificio = ubicacion.laboratorio?.edificio?.nombre_edificio
      return edificio ? `${laboratorio} (${edificio})` : laboratorio
    },

    async cargarFiltros() {
      try {
        const response = await api.get("/reportes/catalogos")
        this.catalogos.ubicaciones = response.data.ubicaciones || []
        this.catalogos.estados = response.data.estados || []
      } catch (error) {
        console.error("Error al cargar filtros de reportes:", error)
      }
    },

    buildPayload(extra = {}) {
      return {
        tipo_reporte: this.form.tipo_reporte,
        fecha_inicio: this.form.fecha_inicio || "",
        fecha_fin: this.form.fecha_fin || "",
        id_ubicacion: this.form.id_ubicacion || "",
        id_estado: this.form.id_estado || "",
        ...extra
      }
    },

    async loadPreview() {
  const { fecha_inicio, fecha_fin } = this.form
  const hoyLocal = this.fechaMaxima

  // Si no se han puesto ambas fechas, salimos sin hacer nada (evita errores al limpiar campos)
  if (!fecha_inicio || !fecha_fin) return

  // CORRECCIÓN: Si intentan meter una fecha futura en la vista previa, avisamos y limpiamos la tabla
  if (fecha_inicio > hoyLocal || fecha_fin > hoyLocal) {
    this.previewColumns = []
    this.previewRows = []
    Swal.fire({
      icon: "error",
      title: "Fechas inválidas",
      text: "No puedes consultar una vista previa con fechas futuras.",
      confirmButtonColor: "#DC2626"
    })
    return 
  }

  try {
    this.cargandoPreview = true
    const response = await api.get("/reportes/vista", {
      params: this.buildPayload()
    })

    this.previewColumns = response.data.columns || []
    this.previewRows = response.data.data || []

    if (this.previewRows.length === 0) {
      Swal.fire({
        icon: "info",
        title: "Sin resultados",
        text: "No hay registros para los filtros seleccionados.",
        confirmButtonColor: "#DC2626", 
        timer: 3000 
      })
    }
  } catch (error) {
    console.error("Error al cargar vista previa:", error)
    this.previewColumns = []
    this.previewRows = []
    Swal.fire({
      icon: "error",
      title: "Error de conexión",
      text: "Hubo un problema al conectar con el servidor.",
      confirmButtonColor: "#DC2626"
    })
  } finally {
    this.cargandoPreview = false
  }
    },

    async validarFormulario() {
  const { fecha_inicio, fecha_fin } = this.form
  const hoyLocal = this.fechaMaxima

  if (!fecha_inicio || !fecha_fin) {
    await Swal.fire({
      icon: "warning",
      title: "Campos obligatorios",
      text: "Por favor, seleccione una Fecha de Inicio y una Fecha Fin para continuar.",
      confirmButtonColor: "#DC2626"
    })
    return false 
  }

  if (fecha_inicio > hoyLocal || fecha_fin > hoyLocal) {
    await Swal.fire({
      icon: "error",
      title: "Fechas inválidas",
      text: "Las fechas no pueden ser futuras.",
      confirmButtonColor: "#DC2626"
    })
    return false
  }

  if (fecha_inicio > fecha_fin) {
    await Swal.fire({
      icon: "error",
      title: "Rango inválido",
      text: "La fecha de inicio no puede ser posterior a la fecha final.",
      confirmButtonColor: "#DC2626"
    })
    return false
  }

  return true 
  },

    async procesarReporte() {
  // 1. Validar que las fechas sean correctas
  if (!(await this.validarFormulario())) return

  // 2. Si la vista previa actual está vacía, detener la descarga
  if (this.previewRows.length === 0) {
    await Swal.fire({
      icon: "warning",
      title: "Reporte vacío",
      text: "No se puede generar el archivo porque no existen registros con los filtros seleccionados.",
      confirmButtonColor: "#DC2626"
    })
    return 
  }

  this.cargando = true

  try {
    Swal.fire({
      title: "Generando reporte...",
      text: "Por favor, espera un momento.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    })

    if (this.form.formato === "excel") {
      await this.descargarExcelNativo()
    } else {
      this.abrirVisorImpresionPdf()
    }

    Swal.close()
  } catch (error) {
    console.error(error)
    Swal.fire({
      icon: "error",
      title: "Error de servidor",
      text: "No se pudo generar el reporte. Inténtalo de nuevo."
    })
  } finally {
    this.cargando = false
  }
},

    async descargarExcelNativo() {
      const response = await api.post("/reportes/exportar", {
        ...this.buildPayload(),
        formato: "excel"
      }, {
        responseType: "blob"
      })

      const blob = new Blob([response.data], { type: "text/csv;charset=utf-8;" })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement("a")
      const timestamp = new Date().toISOString().slice(0, 10)

      link.href = url
      link.setAttribute("download", `reporte_${this.form.tipo_reporte}_${timestamp}.csv`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    },

    abrirVisorImpresionPdf() {
      const token = localStorage.getItem("token") || ""
      const params = new URLSearchParams({
        ...this.buildPayload({ formato: "pdf" }),
        token
      })

      window.open(`/api/reportes/exportar?${params.toString()}`, "_blank")
    }
  }
}
</script>

<style scoped>
.reports-info-card,
.report-card {
  background: #fff;
  border-radius: 24px;
  padding: 28px;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.reports-info-card {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 24px;
  margin-bottom: 28px;
  align-items: center;
}

.reports-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 28px;
  margin-bottom: 28px;
}

.reports-menu-card h2,
.main-report h2,
.preview-card h2 {
  display: flex;
  align-items: center;
  gap: 12px;
}

.report-menu-item {
  width: 100%;
  display: flex;
  gap: 14px;
  text-align: left;
  padding: 16px;
  border: 1px solid #e5e7eb;
  background: #f8fafc;
  border-radius: 16px;
  margin-top: 12px;
  cursor: pointer;
}

.report-menu-item.active {
  border-color: #DC2626;
  background: #fff1f2;
}

.report-menu-item small {
  display: block;
  color: #64748b;
  margin-top: 4px;
}

.report-icon {
  font-size: 24px;
}

.report-description {
  color: #64748b;
  margin-bottom: 20px;
}

.report-fields-box {
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  padding: 18px;
  margin-bottom: 24px;
}

.fields-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 12px 0;
}

.fields-list span {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: 8px 12px;
}

.advanced-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 18px;
  padding: 20px;
}

.advanced-box.compact p {
  margin-bottom: 0;
}

.date-grid,
.filter-grid,
.format-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin-bottom: 22px;
}

input,
select {
  width: 100%;
  padding: 16px;
  border: 1px solid #cbd5e1;
  border-radius: 14px;
  font-size: 16px;
}

.format,
.download-btn,
.outline-btn {
  border-radius: 14px;
  padding: 16px;
  font-weight: 800;
  cursor: pointer;
}

.format {
  background: #fff;
  border: 1px solid #cbd5e1;
}

.format.active {
  background: #fff1f2;
  border-color: #DC2626;
}

.download-btn {
  width: 100%;
  background: #DC2626;
  color: #fff;
  border: none;
  display: flex;
  justify-content: center;
  gap: 12px;
  align-items: center;
}


.outline-btn {
  background: #fff;
  border: 1px solid #cbd5e1;
  display: flex;
  align-items: center;
  gap: 10px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
}

.preview-table-wrapper {
  width: 100%;
  overflow-x: auto;
}

.preview-table-wrapper table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 18px;
}

.preview-table-wrapper th,
.preview-table-wrapper td {
  padding: 14px;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
}

.preview-table-wrapper th {
  background: #f8fafc;
  color: #0f172a;
}

.empty-preview {
  margin-top: 18px;
  padding: 24px;
  background: #f8fafc;
  border-radius: 16px;
  color: #64748b;
}

@media (max-width: 1100px) {
  .reports-info-card,
  .reports-layout {
    grid-template-columns: 1fr;
  }
}
</style>