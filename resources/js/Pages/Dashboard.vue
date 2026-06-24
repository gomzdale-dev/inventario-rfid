<template>
  <section class="dashboard">

    <h1 class="dashboard-title">Panel Principal de Control</h1>

    <!-- CARDS -->
    <div class="cards">
      <div class="card blue">
        <Package />
        <div>
          <h2>{{ totalAssets }}</h2>
          <p>Total de Activos</p>
        </div>
      </div>

      <div class="card green">
        <CheckCircle />
        <div>
          <h2>{{ assignedAssets }}</h2>
          <p>Equipos Asignados</p>
        </div>
      </div>

      <div class="card orange">
        <Wrench />
        <div>
          <h2>{{ maintenanceAssets }}</h2>
          <p>Equipos en Mantenimiento</p>
        </div>
      </div>
    </div>

    <!-- GRID -->
    <div class="dashboard-grid">

      <!-- GRAFICO -->
      <div class="chart-card">
        <h2>Historial de Movimientos por Tipo (Último Mes)</h2>
        <Bar :data="chartData" :options="chartOptions" />
      </div>

      <!-- LATERAL -->
      <div class="labs">
        <h2>Último Inventario por Laboratorio</h2>

        <div v-if="isLoading" class="lab">
          <div>
            <h3>Cargando datos...</h3>
            <p>Consultando información de la base de datos</p>
            <span>Espere un momento</span>
          </div>
        </div>

        <div v-else-if="labs.length === 0" class="lab">
          <div>
            <h3>Sin registros</h3>
            <p>No hay laboratorios con activos registrados</p>
            <span>0 activos</span>
          </div>
        </div>

        <div v-else v-for="lab in labs" :key="lab.name" class="lab">
          <div>
            <h3>{{ lab.name }}</h3>
            <p>Última revisión: {{ lab.date }}</p>
            <span>{{ lab.assets }} activos</span>
          </div>

          <CheckCircle class="check" />
        </div>

      </div>

    </div>

  </section>
</template>

<script>
import {
  Package,
  CheckCircle,
  Wrench
} from "lucide-vue-next"

import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip
} from "chart.js"

import { Bar } from "vue-chartjs"
import api from "../services/api"

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip
)

export default {
  components: {
    Package,
    CheckCircle,
    Wrench,
    Bar
  },

  data() {
    return {
      isLoading: false,
      totalAssets: 0,
      assignedAssets: 0,
      maintenanceAssets: 0,

      chartData: {
        labels: ["Altas", "Bajas", "Traslados", "Asignaciones", "Devoluciones"],
        datasets: [
          {
            data: [0, 0, 0, 0, 0],
            backgroundColor: "#DC2626",
            borderRadius: 8
          }
        ]
      },

      chartOptions: {
        responsive: true,
        plugins: {
          legend: { display: false }
        }
      },

      labs: []
    }
  },

  mounted() {
    this.loadDashboardData()
  },

  methods: {
    async loadDashboardData() {
      try {
        this.isLoading = true

        const [assetsResponse, movementsResponse] = await Promise.all([
          api.get("/activo"),
          api.get("/movimientos")
        ])

        const assets = assetsResponse.data ?? []
        const movements = movementsResponse.data ?? []

        this.totalAssets = assets.length

        this.assignedAssets = assets.filter(asset =>
          asset.id_responsable ||
          asset.responsable ||
          asset.responsable?.id
        ).length

        this.maintenanceAssets = assets.filter(asset => {
          const estado = String(
            asset.estado?.nombre_estado ??
            asset.estado_activo?.nombre_estado ??
            asset.nombre_estado ??
            asset.id_estado ??
            ""
          ).toLowerCase()

          return estado.includes("mantenimiento") || estado === "2"
        }).length

        this.buildMovementChart(movements)
        this.buildLabsSummary(assets, movements)
      } catch (error) {
        console.error("Error al cargar datos del dashboard:", error)
      } finally {
        this.isLoading = false
      }
    },

    buildMovementChart(movements) {
      const counters = {}

      movements.forEach(movement => {
        const typeName =
          movement.tipoMovimiento?.nombre_movimiento ??
          movement.tipo_movimiento?.nombre_movimiento ??
          movement.nombre_movimiento ??
          "Sin tipo"

        counters[typeName] = (counters[typeName] || 0) + 1
      })

      const labels = Object.keys(counters)

      this.chartData = {
        labels: labels.length ? labels : ["Sin movimientos"],
        datasets: [
          {
            data: labels.length ? Object.values(counters) : [0],
            backgroundColor: "#DC2626",
            borderRadius: 8
          }
        ]
      }
    },

    buildLabsSummary(assets, movements) {
      const labsMap = {}

      assets.forEach(asset => {
        const labName =
          asset.ubicacion?.laboratorio?.nombre_laboratorio ??
          asset.ubicacion?.nombre_laboratorio ??
          asset.nombre_laboratorio ??
          (asset.ubicacion?.id_laboratorio ? `Laboratorio #${asset.ubicacion.id_laboratorio}` : "Sin laboratorio")

        if (!labsMap[labName]) {
          labsMap[labName] = {
            name: labName,
            date: "Sin revisión",
            assets: 0
          }
        }

        labsMap[labName].assets++
      })

      movements.forEach(movement => {
        const labName =
          movement.ubicacion?.laboratorio?.nombre_laboratorio ??
          movement.ubicacion?.nombre_laboratorio ??
          (movement.ubicacion?.id_laboratorio ? `Laboratorio #${movement.ubicacion.id_laboratorio}` : null)

        if (!labName || !labsMap[labName]) return

        const date = movement.fecha_movimiento
        if (!date) return

        if (labsMap[labName].date === "Sin revisión") {
          labsMap[labName].date = this.formatDate(date)
        }
      })

      this.labs = Object.values(labsMap).slice(0, 5)
    },

    formatDate(date) {
      if (!date) return "Sin revisión"
      return new Date(date).toLocaleDateString("es-SV", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      })
    }
  }
}
</script>
