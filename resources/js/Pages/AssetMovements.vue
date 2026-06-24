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
          asociando el equipo, tipo de movimiento, edificio, salón y comentarios.
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
            <option v-for="asset in assets" :key="asset.id_activo" :value="asset.id_activo">
              {{ asset.nombre_activo }} - {{ asset.serie ?? asset.codigo_rfid }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Tipo de Movimiento <span>*</span></label>
          <select v-model="form.tipo_movimiento" required>
            <option value="">Seleccionar movimiento...</option>
            <option v-for="type in movementTypes" :key="type.id" :value="type.id">
              {{ type.nombre_movimiento }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Edificio <span>*</span></label>
          <select v-model="form.id_edificio" required @change="handleBuildingChange">
            <option value="">Seleccionar edificio...</option>
            <option v-for="edificio in edificios" :key="edificio.id_edificio" :value="edificio.id_edificio">
              {{ edificio.nombre_edificio }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Salón <span>*</span></label>
          <select v-model="form.id_laboratorio" required :disabled="!form.id_edificio">
            <option value="">
              {{ form.id_edificio ? "Seleccionar salón..." : "Primero selecciona un edificio" }}
            </option>
            <option v-for="salon in availableSalones" :key="salon.id_laboratorio" :value="salon.id_laboratorio">
              {{ salon.nombre_laboratorio }}
            </option>
          </select>
          <small>La fecha y hora del movimiento se tomarán automáticamente al guardar.</small>
        </div>

        <div class="field full">
          <label>Comentarios</label>
          <textarea
            v-model="form.comentarios"
            maxlength="50"
            placeholder="Comentario breve del movimiento..."
          ></textarea>
          <small>Máximo 50 caracteres según la estructura actual de la base de datos.</small>
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
            <th>Salón</th>
            <th>Fecha</th>
            <th>Comentarios</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="movement in movements" :key="movement.id_movimiento">
            <td>{{ movement.id_movimiento }}</td>
            <td>{{ movement.activo?.nombre_activo ?? "Sin activo" }}</td>
            <td>{{ movement.tipo_movimiento?.nombre_movimiento ?? movement.tipoMovimiento?.nombre_movimiento ?? "Sin tipo" }}</td>
            <td>{{ movement.ubicacion?.laboratorio?.nombre_laboratorio ?? `Salón #${movement.ubicacion?.id_laboratorio ?? movement.id_ubicacion}` }}</td>
            <td>{{ formatDate(movement.fecha_movimiento) }}</td>
            <td>{{ movement.comentarios ?? "Sin comentarios" }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </section>
</template>

<script>
import { MoveRight, Save, X, RefreshCcw } from "lucide-vue-next"
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
      edificios: [],
      laboratorios: [],
      movements: [],
      form: this.getEmptyForm()
    }
  },

  computed: {
    availableSalones() {
      return this.laboratorios.filter(
        laboratorio => laboratorio.id_edificio == this.form.id_edificio
      )
    }
  },

  mounted() {
    this.loadCatalogs()
    this.getEdificios()
    this.getLaboratorios()
    this.loadMovements()
  },

  methods: {
    getEmptyForm() {
      return {
        id_activo: "",
        tipo_movimiento: "",
        id_edificio: "",
        id_laboratorio: "",
        comentarios: ""
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
          text: error.response?.data?.message || "No fue posible cargar los catálogos para movimientos."
        })
      }
    },

    async getEdificios() {
      try {
        const response = await api.get("/edificio")
        this.edificios = response.data
      } catch (error) {
        console.error(error)
      }
    },

    async getLaboratorios() {
      try {
        const response = await api.get("/laboratorio")
        this.laboratorios = response.data
      } catch (error) {
        console.error(error)
      }
    },

    handleBuildingChange() {
      this.form.id_laboratorio = ""
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
          id_laboratorio: this.form.id_laboratorio,
          comentarios: this.form.comentarios
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
