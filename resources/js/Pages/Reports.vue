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
          <button 
            type="button"
            :class="['type-card', { selected: form.tipo_reporte === 'historial' }]"
            @click="form.tipo_reporte = 'historial'"
          >
            📊 Historial de Movimientos
          </button>
          <button 
            type="button"
            :class="['type-card', { selected: form.tipo_reporte === 'inventario' }]"
            @click="form.tipo_reporte = 'inventario'"
          >
            📦 Inventario Actualizado
          </button>
          <button 
            type="button"
            :class="['type-card', { selected: form.tipo_reporte === 'mantenimiento' }]"
            @click="form.tipo_reporte = 'mantenimiento'"
          >
            🔧 Equipos en Mantenimiento
          </button>
        </div>

        <div class="date-grid">
          <div>
            <label>Fecha Inicio</label>
            <input type="date" v-model="form.fecha_inicio" />
          </div>
          <div>
            <label>Fecha Fin</label>
            <input type="date" v-model="form.fecha_fin" />
          </div>
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

        <label>Filtros Adicionales (opcional)</label>
        <div class="filter-grid">
          <select v-model="form.id_ubicacion">
            <option :value="null">Todas las ubicaciones</option>
            <option v-for="ubi in catalogos.ubicaciones" :key="ubi.id_ubicacion" :value="ubi.id_ubicacion">
              {{ ubi.laboratorio?.nombre_laboratorio }} ({{ ubi.laboratorio?.edificio?.nombre_edificio }})
            </option>
          </select>
          
          <select v-model="form.id_estado" :disabled="form.tipo_reporte === 'mantenimiento'">
            <option :value="null">Todos los estados</option>
            <option v-for="estado in catalogos.estados" :key="estado.id_estado" :value="estado.id_estado">
              {{ estado.nombre_estado }}
            </option>
          </select>
        </div>

        <button class="download-btn" @click="procesarReporte" :disabled="cargando">
          <Download size="22" />
          {{ cargando ? 'Generando...' : 'Generar y Descargar Reporte' }}
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
import { FileText, Download } from "lucide-vue-next";
import api from "../services/api"
import Swal from 'sweetalert2'
export default {
  name: "Reports",
  components: {
    FileText,
    Download
  },
  data() {
    return {
      cargando: false,
      form: {
        tipo_reporte: 'historial',
        fecha_inicio: '',
        fecha_fin: '',
        formato: 'pdf',
        id_ubicacion: null,
        id_estado: null
      },
      catalogos: {
        ubicaciones: [],
        estados: [] // Puedes cargar estos mediante una petición inicial a tu endpoint de catálogos
      },
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
    };
  },
  mounted() {
    this.cargarFiltros();
  },
  methods: {
  async cargarFiltros() {
     try {
       const response = await api.get('/movimientos/catalogos'); 
       this.catalogos.ubicaciones = response.data.ubicaciones || [];
       this.catalogos.estados = response.data.estados || [];
     } catch (error) {
        console.error("Error al cargar listados para filtros:", error);
     }
  },
  async validarFormulario() {
    
     if (this.form.fecha_inicio && this.form.fecha_fin) {
      const inicio = new Date(this.form.fecha_inicio);
      const fin = new Date(this.form.fecha_fin);
      const hoy = new Date().toISOString().split('T')[0];
      const { fecha_inicio, fecha_fin, tipo_reporte } = this.form;
      const esInvalido = 
    (fecha_inicio > hoy) || (fecha_fin > hoy) || 
    (fecha_inicio > fecha_fin) ||
    (tipo_reporte === 'historial' && (!fecha_inicio || !fecha_fin));

    if (esInvalido) {
    await Swal.fire({
      icon: 'error',
      title: 'Datos inválidos',
      text: 'Verifica que las fechas no sean futuras, que el inicio no sea posterior al fin, y que el rango esté completo.',
      confirmButtonColor: '#d33'
    });
    return false;
    }

    return true;
   }
  },
    async procesarReporte() {
    if (!(await this.validarFormulario())) return;

    this.cargando = true;
    try {
      // Usar loading de SweetAlert2 mientras se procesa
      Swal.fire({
        title: 'Generando reporte...',
        text: 'Por favor, espera un momento.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
      });

      if (this.form.formato === 'excel') {
        await this.descargarExcelNativo();
      } else {
        this.abrirVisorImpresionPdf();
      }
      
      Swal.close(); // Cierra el loading al finalizar
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error de servidor',
        text: 'No se pudo generar el reporte. Inténtalo de nuevo.'
      });
    } finally {
      this.cargando = false;
    }
  },
  async descargarExcelNativo() {
  this.cargando = true;
  try {
    const response = await api.post('/reportes/exportar', this.form, {
      responseType: 'blob' 
    });
    
    // Creamos el Blob con el tipo correcto para que Excel lo reconozca
    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
    
    const url = window.URL.createObjectURL(blob);
    
    // CORRECCIÓN AQUÍ: Se completó la declaración del elemento 'a'
    const link = document.createElement('a');
    
    const timestamp = new Date().toISOString().slice(0,10);
    link.href = url;
    link.setAttribute('download', `reporte_${this.form.tipo_reporte}_${timestamp}.csv`);
    
    document.body.appendChild(link);
    link.click();
    
    // Limpieza
    link.remove();
    window.URL.revokeObjectURL(url);
    
  } catch (error) {
    console.error("Error descargando el reporte Excel:", error);
    alert("Hubo un error al procesar la descarga del archivo.");
  } finally {
    this.cargando = false;
  }
},
    abrirVisorImpresionPdf() {
   
      const baseUrl = 'http://127.0.0.1:8000/api/reportes/exportar'; 
      const token = localStorage.getItem('token') || '';
      const params = new URLSearchParams({
        tipo_reporte: this.form.tipo_reporte,
        formato: 'pdf',
        fecha_inicio: this.form.fecha_inicio || '',
        fecha_fin: this.form.fecha_fin || '',
        id_ubicacion: this.form.id_ubicacion || '',
        id_estado: this.form.id_estado || '',
        token: token
      });

      // Abre una pestaña nueva. El backend de Laravel interpretará esto y renderizará la vista de impresión
      window.open(`${baseUrl}?${params.toString()}`, '_blank');
    }
  }
};
</script>