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
        <div class="field">
          <label>ID Activo <span>*</span></label>
          <input v-model="form.assetId" required placeholder="Ej: ACT-001" />
        </div>

        <div class="field">
          <label>ID Categoría <span>*</span></label>
          <select v-model="form.category" required>
            <option value="">Seleccionar categoría...</option>
            <option>CAT-001: Computadora</option>
            <option>CAT-002: Monitor</option>
            <option>CAT-003: Networking</option>
            <option>CAT-004: Microcontrolador</option>
            <option>CAT-005: Periférico</option>
            <option>CAT-006: Impresora</option>
            <option>CAT-007: Otro</option>
          </select>
        </div>

        <div class="field">
          <label>ID Ubicación Actual <span>*</span></label>
          <select v-model="form.location" required>
            <option value="">Seleccionar ubicación...</option>
            <option>UBI-001: Lab A-102</option>
            <option>UBI-002: Lab B-205</option>
            <option>UBI-003: Lab C-301</option>
            <option>UBI-004: Lab D-104</option>
            <option>UBI-005: Almacén</option>
          </select>
        </div>

        <div class="field">
          <label>ID Estado Activo <span>*</span></label>
          <select v-model="form.status" required>
            <option value="">Seleccionar estado...</option>
            <option>EST-001: Activo</option>
            <option>EST-002: En Mantenimiento</option>
            <option>EST-003: Fuera de Servicio</option>
            <option>EST-004: Disponible</option>
            <option>EST-005: Asignado</option>
          </select>
        </div>

        <div class="field full">
          <label>Etiqueta RFID <span>*</span></label>
          <div class="rfid-field">
            <input
              v-model="form.rfid"
              required
              class="mono-input"
              placeholder="Ej: RFID-1234"
            />
            <button type="button" @click="scanRfid">
              <ScanLine size="21" />
              {{ isScanning ? "Escaneando..." : "Escanear Etiqueta" }}
            </button>
          </div>
          <small>El código debe coincidir con la etiqueta RFID física adherida al dispositivo</small>
        </div>

        <div class="field">
          <label>ID Modelo <span>*</span></label>
          <input v-model="form.model" required placeholder="Ej: MOD-001" />
        </div>

        <div class="field">
          <label>ID Responsable <span>*</span></label>
          <select v-model="form.responsible" required>
            <option value="">Seleccionar responsable...</option>
            <option>RESP-001: Ing. Carlos Méndez</option>
            <option>RESP-002: Lic. María Rodríguez</option>
            <option>RESP-003: Ing. José Hernández</option>
            <option>RESP-004: Lic. Ana García</option>
            <option>RESP-005: Ing. Roberto López</option>
          </select>
        </div>

        <div class="field full">
          <label>Nombre de Activo <span>*</span></label>
          <input v-model="form.name" required placeholder="Ej: Computadora Dell OptiPlex 7090" />
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
          <label>Valor Actual <span>*</span></label>
          <span>$</span>
          <input v-model="form.currentValue" required type="number" step="0.01" placeholder="0.00" />
        </div>

        <div class="field">
          <label>Vida Útil (años) <span>*</span></label>
          <input v-model="form.usefulLife" required type="number" placeholder="Ej: 5" />
        </div>

        <div class="field money">
          <label>Depreciación Anual <span>*</span></label>
          <span>$</span>
          <input v-model="form.annualDepreciation" required type="number" step="0.01" placeholder="0.00" />
        </div>

        <div class="field full">
          <label>Descripción o Notas Adicionales</label>
          <textarea
            v-model="form.notes"
            placeholder="Información adicional sobre el equipo..."
          ></textarea>
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
      form: this.getEmptyForm()
    }
  },
  methods: {
    getEmptyForm() {
      return {
        assetId: "",
        category: "",
        location: "",
        status: "",
        rfid: "",
        model: "",
        responsible: "",
        name: "",
        serial: "",
        purchaseValue: "",
        purchaseDate: "",
        currentValue: "",
        usefulLife: "",
        annualDepreciation: "",
        notes: ""
      }
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