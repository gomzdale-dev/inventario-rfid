<template>
  <section class="register-asset-page">
    <header class="register-header">
      <h1>Registro de Nuevos Activos</h1>
      <p>Incorporar nuevos equipos al sistema de inventario RFID</p>
    </header>

    <section class="rfid-info-box">
      <Package size="28" />
      <div>
        <h2>Vinculación Automática con Etiqueta RFID</h2>
        <p>
          Una vez completado el registro, el activo será vinculado automáticamente
          con la etiqueta RFID correspondiente, permitiendo su detección por los
          lectores instalados en el laboratorio.
        </p>
      </div>
    </section>

    <form class="asset-form-card" @submit.prevent="registerAsset">
      <div class="form-grid">
        <div class="field full">
          <label>Nombre del Activo <span>*</span></label>
          <input v-model="form.name" required placeholder="Ej: Computadora Dell OptiPlex 7090" />
        </div>

        <div class="field">
          <label>Código de Activo <span>*</span></label>
          <input v-model="form.assetCode" required placeholder="Ej: ACT-001" />
        </div>

        <div class="field">
          <label>Serie <span>*</span></label>
          <input v-model="form.serial" required placeholder="Ej: SN123456789" />
        </div>

        <div class="field money">
          <label>Valor de Compra <span>*</span></label>
          <span>$</span>
          <input v-model="form.purchaseValue" required type="number" step="0.01" placeholder="0.00" />
        </div>

        <div class="field">
          <label>Fecha de Compra <span>*</span></label>
          <input v-model="form.purchaseDate" required type="date" />
        </div>

        <div class="field money">
          <label>Valor Actual</label>
          <span>$</span>
          <input v-model="form.currentValue" type="number" step="0.01" placeholder="0.00" />
        </div>

        <div class="field">
          <label>Vida Útil (años) <span>*</span></label>
          <input v-model="form.usefulLife" required type="number" placeholder="Ej: 5" />
        </div>

        <div class="field money">
          <label>Depreciación Anual</label>
          <span>$</span>
          <input v-model="form.annualDepreciation" type="number" step="0.01" placeholder="0.00" />
        </div>

        <div class="field">
          <label>Edificio <span>*</span></label>
          <select v-model="form.id_edificio" required @change="handleBuildingChange">
            <option value="">Seleccionar edificio...</option>
            <option
             v-for="edificio in edificios" 
             :key="edificio.id_edificio" 
             :value="edificio.id_edificio">
              {{ edificio.nombre_edificio }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Laboratorio <span>*</span></label>
          <select v-model="form.id_laboratorio" required :disabled="!form.id_edificio">
            <option value="">
              {{ form.id_edificio ? "Seleccionar laboratorio..." : "Primero selecciona un edificio" }}
            </option>
            <option v-for="laboratorio in availableLaboratories" 
            :key="laboratorio.id_laboratorio" 
            :value="laboratorio.id_laboratorio">
              {{ laboratorio.nombre_laboratorio }}
            </option>
          </select>
        </div>

        <div class="field full">
          <label>Etiqueta RFID <span>*</span></label>
          <div class="rfid-field">
            <input v-model="form.rfid" required class="mono-input" placeholder="Ej: RFID-1234" />
            <button type="button" @click="scanRfid">
              <ScanLine size="21" />
              {{ isScanning ? "Escaneando..." : "Escanear Etiqueta" }}
            </button>
          </div>
          <small>El código debe coincidir con la etiqueta RFID física adherida al dispositivo</small>
        </div>

        <div class="field">
          <label>Categoría <span>*</span></label>
          <select v-model="form.category" required>
            <option value="">Seleccionar categoría...</option>
            <option>Computadora</option>
            <option>Monitor</option>
            <option>Networking</option>
            <option>Microcontrolador</option>
            <option>Periférico</option>
            <option>Impresora</option>
            <option>Otro</option>
          </select>
        </div>

        <div class="field">
          <label>Modelo <span>*</span></label>
          <select v-model="form.model" required>
            <option value="">Seleccionar modelo...</option>
            <option>Dell OptiPlex 7090</option>
            <option>HP EliteBook 840</option>
            <option>Epson PowerLite</option>
            <option>Cisco Catalyst</option>
            <option>Arduino UNO</option>
          </select>
        </div>

        <div class="field">
          <label>Estado del Activo <span>*</span></label>
          <select v-model="form.status" required>
            <option value="">Seleccionar estado...</option>
            <option>Activo</option>
            <option>En mantenimiento</option>
            <option>Fuera de servicio</option>
            <option>Disponible</option>
            <option>Asignado</option>
          </select>
        </div>

        <div class="field">
          <label>Responsable <span>*</span></label>
          <select v-model="form.responsible" required>
            <option value="">Seleccionar responsable...</option>
            <option>Ing. Carlos Méndez</option>
            <option>Lic. María Rodríguez</option>
            <option>Ing. José Hernández</option>
            <option>Lic. Ana García</option>
            <option>Ing. Roberto López</option>
          </select>
        </div>

        <div class="field full">
          <label>Descripción o Notas Adicionales</label>
          <textarea v-model="form.notes" placeholder="Información adicional sobre el equipo..."></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="primary-action">
          <Save size="22" />
          Registrar Activo
        </button>

        <button type="button" class="secondary-action" @click="clearForm">
          <X size="22" />
          Limpiar Formulario
        </button>
      </div>
    </form>

    <section v-if="showSuccess" class="success-box">
      <div class="success-icon">
        <Package size="28" />
      </div>
      <div>
        <h2>Registro Exitoso</h2>
        <p>
          El activo ha sido registrado correctamente y vinculado con la etiqueta RFID.
          Ahora puede ser detectado por los lectores del laboratorio.
        </p>
      </div>
    </section>
  </section>
</template>

<script>
import { Package, Save, X, ScanLine } from "lucide-vue-next"
import api from "../services/api"
import Swal from 'sweetalert2'
export default {
  name: "RegisterAsset",

  components: {
    Package,
    Save,
    X,
    ScanLine
  },
  data() {
    return {
      isScanning: false,
      showSuccess: false,
      form: this.getEmptyForm(),
      edificios :[],
      laboratorios: [],
      
    }
  },
  computed: {
    availableLaboratories() {
    return this.laboratorios.filter(
      laboratorio => laboratorio.id_edificio == this.form.id_edificio
    )
  }
  },
  mounted(){
   this.getEdificios()
   this.getLaboratorios()
  },
  methods: {
    getEmptyForm() {
      return {
        name: "",
        assetCode: "",
        serial: "",
        purchaseValue: "",
        purchaseDate: "",
        currentValue: "",
        usefulLife: "",
        annualDepreciation: "",
        id_edificio: "",
        id_laboratorio: "",
        rfid: "",
        category: "",
        model: "",
        status: "",
        responsible: "",
        notes: ""
      }
    },
    async getEdificios(){
      const res = await api.get("/edificio")
      this.edificios = res.data
    },
    async getLaboratorios(){
      const res = await api.get("/laboratorio")
      this.laboratorios = res.data
    },
    handleBuildingChange() {
      this.form.id_laboratorio = ""
    },

    scanRfid() {
      this.isScanning = true

      setTimeout(() => {
        this.form.rfid = "RFID-" + Math.floor(1000 + Math.random() * 9000)
        this.isScanning = false
      }, 1200)
    },

    registerAsset() {
      this.showSuccess = true
      this.form = this.getEmptyForm()

      setTimeout(() => {
        this.showSuccess = false
      }, 5000)
    },

    clearForm() {
      this.form = this.getEmptyForm()
      this.showSuccess = false
    }
  }
}
</script>