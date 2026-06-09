<template>
  <section class="register-asset-page">
    <header class="register-header">
      <h1>Registrar Movimientos</h1>
      <p>Control de entradas, salidas y traslados de activos tecnológicos</p>
    </header>

    <section class="rfid-info-box">
      <MoveRight size="28" />
      <div>
        <h2>Registro de Movimientos de Activos</h2>
        <p>
          Esta sección permite registrar movimientos físicos de los activos,
          asociando el equipo, tipo de movimiento y ubicación destino.
          La fecha y hora se registran automáticamente por el sistema.
        </p>
      </div>
    </section>

    <form class="asset-form-card" @submit.prevent="saveMovement">
      <div class="form-grid">
        <div class="field">
          <label>Activo <span>*</span></label>
          <select v-model="form.id_activo" required>
            <option value="">Seleccionar activo...</option>
            <option
              v-for="asset in assets"
              :key="asset.id_activo"
              :value="asset.id_activo"
            >
              {{ asset.nombre_activo }} - {{ asset.serie }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Tipo de Movimiento <span>*</span></label>
          <select v-model="form.tipo_movimiento" required>
            <option value="">Seleccionar movimiento...</option>
            <option
              v-for="type in movementTypes"
              :key="type.id"
              :value="type.id"
            >
              {{ type.nombre_movimiento }}
            </option>
          </select>
        </div>

        <div class="field full">
          <label>Ubicación Destino <span>*</span></label>
          <select v-model="form.id_ubicacion" required>
            <option value="">Seleccionar ubicación...</option>
            <option
              v-for="location in locations"
              :key="location.id_ubicacion"
              :value="location.id_ubicacion"
            >
              Ubicación #{{ location.id_ubicacion }} - Laboratorio {{ location.id_laboratorio }}
            </option>
          </select>
          <small>La fecha y hora del movimiento se tomarán automáticamente al guardar.</small>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="primary-action" :disabled="isSaving">
          <Save size="22" />
          {{ isSaving ? "Guardando..." : "Registrar Movimiento" }}
        </button>

        <button type="button" class="secondary-action" @click="clearForm">
          <X size="22" />
          Limpiar
        </button>
      </div>
    </form>

    <section class="inventory-table-card movements-table">
      <header class="movements-header">
        <div>
          <h2>Historial de Movimientos</h2>
          <p>Últimos movimientos registrados en el sistema</p>
        </div>

        <button class="outline-action" type="button" @click="loadMovements">
          <RefreshCcw size="20" />
          Actualizar
        </button>
      </header>

      <div v-if="isLoading" class="assets-state-box">
        Cargando movimientos...
      </div>

      <div v-else-if="movements.length === 0" class="assets-state-box">
        No hay movimientos registrados.
      </div>

      <table v-else>
        <thead>
          <tr>
            <th>ID</th>
            <th>Activo</th>
            <th>Tipo</th>
            <th>Ubicación</th>
            <th>Fecha</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="movement in movements" :key="movement.id_movimiento">
            <td>{{ movement.id_movimiento }}</td>
            <td>{{ movement.activo?.nombre_activo ?? "Sin activo" }}</td>
            <td>{{ movement.tipo_movimiento?.nombre_movimiento ?? movement.tipoMovimiento?.nombre_movimiento ?? "Sin tipo" }}</td>
            <td>Ubicación #{{ movement.ubicacion?.id_ubicacion ?? movement.id_ubicacion }}</td>
            <td>{{ formatDate(movement.fecha_movimiento) }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </section>
</template>

<script>
import {
  MoveRight,
  Save,
  X,
  RefreshCcw
} from "lucide-vue-next"

import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "AssetMovements",

  components: {
    MoveRight,
    Save,
    X,
    RefreshCcw
  },

  data() {
    return {
      isLoading: false,
      isSaving: false,
      assets: [],
      movementTypes: [],
      locations: [],
      movements: [],
      form: this.getEmptyForm()
    }
  },

  mounted() {
    this.loadCatalogs()
    this.loadMovements()
  },

  methods: {
    getEmptyForm() {
      return {
        id_activo: "",
        tipo_movimiento: "",
        id_ubicacion: ""
      }
    },

    async loadCatalogs() {
      try {
        const response = await api.get("/movimientos/catalogos")
        this.assets = response.data.activos ?? []
        this.movementTypes = response.data.tipos_movimiento ?? []
        this.locations = response.data.ubicaciones ?? []
      } catch (error) {
        console.error(error)
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No fue posible cargar los catálogos para movimientos."
        })
      }
    },

    async loadMovements() {
      try {
        this.isLoading = true
        const response = await api.get("/movimientos")
        this.movements = response.data
      } catch (error) {
        console.error(error)
      } finally {
        this.isLoading = false
      }
    },

    async saveMovement() {
      try {
        this.isSaving = true

        await api.post("/movimientos", {
          id_activo: this.form.id_activo,
          tipo_movimiento: this.form.tipo_movimiento,
          id_ubicacion: this.form.id_ubicacion
        })

        Swal.fire({
          icon: "success",
          title: "Movimiento registrado",
          text: "El movimiento del activo fue registrado correctamente."
        })

        this.clearForm()
        this.loadMovements()
      } catch (error) {
        console.error(error)

        Swal.fire({
          icon: "error",
          title: "Error al registrar",
          text: error.response?.data?.message || "No fue posible registrar el movimiento."
        })
      } finally {
        this.isSaving = false
      }
    },

    clearForm() {
      this.form = this.getEmptyForm()
    },

    formatDate(date) {
      if (!date) return "Sin fecha"
      return new Date(date).toLocaleString("es-SV")
    }
  }
}
</script>
