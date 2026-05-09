<template>
  <section class="dashboard">

    <h1 class="dashboard-title">Panel Principal de Control</h1>

    <!-- CARDS -->
    <div class="cards">
      <div class="card blue">
        <Package />
        <div>
          <h2>248</h2>
          <p>Total de Activos</p>
        </div>
      </div>

      <div class="card green">
        <CheckCircle />
        <div>
          <h2>187</h2>
          <p>Equipos Asignados</p>
        </div>
      </div>

      <div class="card orange">
        <Wrench />
        <div>
          <h2>8</h2>
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

        <div v-for="lab in labs" :key="lab.name" class="lab">
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

      chartData: {
        labels: ["Altas", "Bajas", "Traslados", "Asignaciones", "Devoluciones"],
        datasets: [
          {
            data: [15, 8, 23, 32, 18],
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

      labs: [
        { name: "Laboratorio A-102", date: "12 Abr 2026", assets: 45 },
        { name: "Laboratorio B-205", date: "10 Abr 2026", assets: 38 },
        { name: "Laboratorio C-301", date: "08 Abr 2026", assets: 52 }
      ]

    }
  }
}
</script>