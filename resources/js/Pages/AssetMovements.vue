<template>
  <section class="register-asset-page">
    <header class="register-header">
      <h1>Registrar Movimientos</h1>
      <p>Control de entradas, salidas y traslados de activos tecnológicos</p>
    </header>

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
  </section>
</template>

<script>
import { Save, X } from "lucide-vue-next"
import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "AssetMovements",

  components: {
    Save,
    X
  },

  data() {
    return {
      isSaving: false,
      assets: [],
      movementTypes: [],
      locations: [],
      edificios: [],
      laboratorios: [],
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
    }
  }
}
</script>
