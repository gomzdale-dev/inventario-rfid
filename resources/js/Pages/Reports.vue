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

        <!-- Filtro de Fechas (Se muestra en Inventario General e Historial) -->
        <template v-if="form.tipo_reporte === 'inventario_general' || form.tipo_reporte === 'historial_por_movimiento'">
          <label>Rango de Fechas</label>
          <div class="date-grid">
            <div>
               <label>Fecha Inicio</label>
                <input type="date" v-model="form.fecha_inicio" :max="fechaMaxima" />
            </div>
            <div>
               <label>Fecha Fin</label>
                <input type="date" v-model="form.fecha_fin" :max="fechaMaxima" />
            </div>
    
          </div>
        </template>
        <!-- Filtros de Edificio y Laboratorio/Salón para Inventario General -->
        <template v-if="form.tipo_reporte === 'inventario_general'">
          <label>Filtrar por Ubicación</label>

          <div class="location-filter-grid">
            <div>
              <label>Edificio</label>
              <select v-model="form.id_edificio" @change="handleBuildingChange">
                <option value="">Todos los edificios</option>
                <option
                  v-for="edificio in catalogos.edificios"
                  :key="edificio.id_edificio"
                  :value="edificio.id_edificio"
                >
                  {{ edificio.nombre_edificio }}
                </option>
              </select>
            </div>

            <div>
              <label>Laboratorio / Salón</label>
              <select v-model="form.id_laboratorio" :disabled="!form.id_edificio">
                <option value="">
                  {{
                    form.id_edificio
                      ? "Todos los laboratorios / salones"
                      : "Primero seleccione un edificio"
                  }}
                </option>
                <option
                  v-for="lab in filteredLaboratorios"
                  :key="lab.id_laboratorio"
                  :value="lab.id_laboratorio"
                >
                  {{ lab.nombre_laboratorio }}
                </option>
              </select>
            </div>
          </div>
        </template>

        <!-- Filtros Adicionales Dinámicos -->
        <template v-if="form.tipo_reporte !== 'inventario_general'">
          <label>{{ form.tipo_reporte === 'historial_por_movimiento' ? 'Filtros Adicionales' : 'Filtro' }}</label>
          <div class="filter-grid-single">
            <!-- Select de Ubicación (Solo para Activos por Ubicación) -->
            <select v-if="form.tipo_reporte === 'activos_por_ubicacion'" v-model="form.id_ubicacion">
              <option value="" disabled selected>Seleccione una ubicación ...</option>
              <option
                v-for="ubi in catalogos.ubicaciones"
                :key="ubi.id_ubicacion"
                :value="ubi.id_ubicacion"
              >
                {{ nombreUbicacion(ubi) }}
              </option>
            </select>

            <!-- Select de Estado (Solo para Activos por Estado) -->
            <select v-if="form.tipo_reporte === 'activos_por_estado'" v-model="form.id_estado">
              <option value="" disabled selected>Seleccione un estado ...</option>
              <option
                v-for="estado in catalogos.estados"
                :key="estado.id_estado"
                :value="estado.id_estado"
              >
                {{ estado.nombre_estado }}
              </option>
            </select>

            <!-- Select de Tipo de Movimiento (Cargado desde la Base de Datos) -->
            <select v-if="form.tipo_reporte === 'historial_por_movimiento'" v-model="form.tipo_movimiento">
              <option value="" disabled selected>Seleccione un tipo de movimiento ...</option>
              <option
                v-for="mov in catalogos.movimientos"
                :key="mov.id"
                :value="mov.id"
              >
                {{ mov.nombre }}
              </option>
            </select>
          </div>
        </template>

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
  </section>
</template>

<script>
import { FileText, Download } from "lucide-vue-next"
import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "Reports",

  components: {
    FileText,
    Download
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
      form: {
        tipo_reporte: this.activeReport || "inventario_general",
        fecha_inicio: "",
        fecha_fin: "",
        formato: "pdf",
        id_ubicacion: "",
        id_estado: "",
        tipo_movimiento: "",
        id_edificio: "",
        id_laboratorio: ""
      },
      catalogos: {
        ubicaciones: [],
        estados: [],
        movimientos: [],
        edificios: [],
        laboratorios: []
      },
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
          value: "activos_por_ubicacion",
          icon: "📍",
          title: "Activos por Ubicación",
          description: "Muestra los activos por edificio y laboratorio.",
          fields: ["Edificio", "Laboratorio", "Código", "Activo", "RFID", "Estado"],
          utility: "Verificar qué activos se encuentran en cada área."
        },
        {
          value: "activos_por_estado",
          icon: "✅",
          title: "Activos por Estado",
          description: "Clasifica los activos según su estado administrativo.",
          fields: ["Estado", "Código", "Activo", "Categoría", "Ubicación", "RFID"],
          utility: "Control administrativo."
        },
        {
          value: "historial_por_movimiento",
          icon: "📊",
          title: "Historial de Movimientos",
          description: "Reporte operativo de altas, traslados, mantenimiento y bajas.",
          fields: ["Fecha", "Tipo de movimiento", "Activo", "RFID", "Ubicación", "Usuario"],
          utility: "Revisar el movimiento histórico de los activos."
        }
      ]
    }
  },

  computed: {
    selectedReport() {
      return this.reportTypes.find(report => report.value === this.form.tipo_reporte) || this.reportTypes[0]
    },
    fechaMaxima() {
      const tzoffset = (new Date()).getTimezoneOffset() * 60000
      return (new Date(Date.now() - tzoffset)).toISOString().split("T")[0]
    },

    filteredLaboratorios() {
      if (!this.form.id_edificio) {
        return []
      }

      return this.catalogos.laboratorios.filter(
        laboratorio => String(laboratorio.id_edificio) === String(this.form.id_edificio)
      )
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
  },

  methods: {
    selectReport(type) {
      this.form.tipo_reporte = type
      this.form.fecha_inicio = ""
      this.form.fecha_fin = ""
      this.form.id_ubicacion = ""
      this.form.id_estado = ""
      this.form.tipo_movimiento = ""
      this.form.id_edificio = ""
      this.form.id_laboratorio = ""
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
        this.catalogos.movimientos = response.data.movimientos || []
        this.catalogos.edificios = response.data.edificios || []
        this.catalogos.laboratorios = response.data.laboratorios || []
      } catch (error) {
        console.error("Error al cargar filtros de reportes:", error)
      }
    },

    handleBuildingChange() {
      this.form.id_laboratorio = ""
    },

    buildPayload(extra = {}) {
      return {
        tipo_reporte: this.form.tipo_reporte,
        fecha_inicio: this.form.fecha_inicio || "",
        fecha_fin: this.form.fecha_fin || "",
        id_ubicacion: this.form.id_ubicacion || "",
        id_estado: this.form.id_estado || "",
        tipo_movimiento: this.form.tipo_movimiento || "",
        id_edificio: this.form.id_edificio || "",
        id_laboratorio: this.form.id_laboratorio || "",
        ...extra
      }
    },

    async validarFormulario() {
      const {
        tipo_reporte,
        fecha_inicio,
        fecha_fin,
        id_ubicacion,
        id_estado,
        tipo_movimiento,
        id_edificio,
        id_laboratorio
      } = this.form
      const hoyLocal = this.fechaMaxima

      if (tipo_reporte === 'inventario_general' || tipo_reporte === 'historial_por_movimiento') {
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
      }

      if (tipo_reporte === 'activos_por_ubicacion' && !id_ubicacion) {
        await Swal.fire({
          icon: "warning",
          title: "Ubicación requerida",
          text: "Debe seleccionar una ubicación obligatoriamente para generar este reporte.",
          confirmButtonColor: "#DC2626"
        })
        return false
      }

      if (tipo_reporte === 'activos_por_estado' && !id_estado) {
        await Swal.fire({
          icon: "warning",
          title: "Estado requerido",
          text: "Debe seleccionar un estado obligatoriamente para generar este reporte.",
          confirmButtonColor: "#DC2626"
        })
        return false
      }

      if (tipo_reporte === 'historial_por_movimiento' && !tipo_movimiento) {
        await Swal.fire({
          icon: "warning",
          title: "Movimiento requerido",
          text: "Debe seleccionar un tipo de movimiento obligatoriamente para generar este reporte.",
          confirmButtonColor: "#DC2626"
        })
        return false
      }

      return true 
    },

    async procesarReporte() {
      if (!(await this.validarFormulario())) return

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
.main-report h2 {
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

.date-grid,
.filter-grid-single,
.location-filter-grid,
.format-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin-bottom: 22px;
}

.filter-grid-single {
  grid-template-columns: 1fr;
}

.location-filter-grid {
  grid-template-columns: 1fr 1fr;
}

.location-filter-grid > div > label {
  display: block;
  margin-bottom: 10px;
}

select:disabled {
  cursor: not-allowed;
  background: #f1f5f9;
  color: #94a3b8;
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
.download-btn {
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

@media (max-width: 1100px) {
  .reports-info-card,
  .reports-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .date-grid,
  .location-filter-grid,
  .format-grid {
    grid-template-columns: 1fr;
  }
}
</style>