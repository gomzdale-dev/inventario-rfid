<template>
  <section class="register-asset-page">
    <header class="register-header">
      <h1>Registro de Nuevos Activos</h1>
      <p>Incorporar nuevos equipos al sistema de inventario RFID</p>
    </header>

    <form class="asset-form-card" @submit.prevent="registerAsset">
      <div class="form-grid">
        <div class="field full">
          <label>Nombre del Activo <span>*</span></label>
          <input v-model="form.name" required placeholder="Ej: Computadora Dell OptiPlex 7090" />
        </div>

        <!-- <div class="field">
          <label>Código de Activo <span>*</span></label>
          <input v-model="form.assetCode" required placeholder="Ej: ACT-001" />
        </div>-->

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
          <input v-model="form.purchaseDate" required type="date" :max="maxDate"/>
        </div>
        
        <div class="field">
          <label>Vida Útil (años) <span>*</span></label>
          <input v-model="form.usefulLife" required type="number" placeholder="Ej: 5" />
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
          <label>Salón <span>*</span></label>
          <select v-model="form.id_laboratorio" required :disabled="!form.id_edificio">
            <option value="">
              {{ form.id_edificio ? "Seleccionar salón..." : "Primero selecciona un edificio" }}
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
            <input
              v-model="form.rfid"
              required
              readonly
              class="mono-input"
              placeholder="Presiona Escanear Etiqueta y acerca una tarjeta o llavero"
            />
            <button type="button" :disabled="isScanning" @click="scanRfid">
              <LoaderCircle v-if="isScanning" class="spin" size="21" />
              <ScanLine v-else size="21" />
              {{ isScanning ? "Esperando lectura..." : "Escanear Etiqueta" }}
            </button>
          </div>

          <small v-if="!form.rfid">
            La etiqueta se capturará directamente desde el lector RFID físico.
          </small>

          <div v-else class="rfid-selected">
            <CheckCircle2 size="18" />
            <div>
              <strong>Etiqueta detectada: {{ form.rfid }}</strong>
              <small>Este UID se vinculará con el nuevo activo al registrarlo.</small>
            </div>
          </div>
        </div>

        <div class="field">
          <label>Categoría <span>*</span></label>
          <select v-model="form.category" required>
            <option value="">Seleccionar categoría...</option>
            <option v-for="category in categorias"
                    :key="category.id_categoria"
                    :value="category.id_categoria">{{ category.nombre_categoria }}</option>
          </select>
        </div>

        <div class="field">
          <label>Modelo <span>*</span></label>
          <select v-model="form.model" required>
            <option value="">Seleccionar modelo...</option>
            <option v-for="model in modelos"
                    :key="model.id_modelo"
                    :value="model.id_modelo">{{ model.nombre_modelo }}</option>
          </select>
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
          Ahora puede ser detectado por los lectores del salón.
        </p>
      </div>
    </section>

    <div v-if="rfidModal.visible" class="rfid-modal-backdrop" @click.self="cancelRfidScan">
      <section class="rfid-modal">
        <button type="button" class="rfid-modal-close" @click="cancelRfidScan">
          <X size="21" />
        </button>

        <template v-if="rfidModal.status === 'scanning'">
          <div class="rfid-scan-rings">
            <span></span>
            <span></span>
            <span></span>
            <div class="rfid-scan-center">
              <RadioTower size="42" />
            </div>
          </div>

          <span class="rfid-modal-kicker">Lector RFID activo</span>
          <h2>Escaneando etiqueta RFID</h2>
          <p>
            Acerca una tarjeta o un llavero al lector RC522. El código se colocará
            automáticamente en el formulario.
          </p>

          <div class="rfid-countdown">
            <span>Tiempo restante</span>
            <strong>{{ scanSecondsRemaining }} s</strong>
          </div>

          <button type="button" class="rfid-secondary-btn" @click="cancelRfidScan">
            Cancelar lectura
          </button>
        </template>

        <template v-else-if="rfidModal.status === 'detected'">
          <div class="rfid-result-icon success">
            <CheckCircle2 size="46" />
          </div>
          <span class="rfid-modal-kicker success">Etiqueta detectada</span>
          <h2>{{ scannedCode }}</h2>
          <p>
            El UID fue recibido correctamente desde el lector físico y está disponible
            para asociarlo al nuevo activo.
          </p>

          <div class="rfid-modal-actions">
            <button type="button" class="rfid-secondary-btn" @click="restartRfidScan">
              <RefreshCw size="19" />
              Escanear otra
            </button>
            <button type="button" class="rfid-primary-btn" @click="useScannedTag">
              <Tag size="19" />
              Usar esta etiqueta
            </button>
          </div>
        </template>

        <template v-else-if="rfidModal.status === 'occupied'">
          <div class="rfid-result-icon danger">
            <TriangleAlert size="46" />
          </div>
          <span class="rfid-modal-kicker danger">Etiqueta no disponible</span>
          <h2>Esta etiqueta ya está asignada</h2>
          <p>
            El UID <strong>{{ scannedCode }}</strong> pertenece al activo
            <strong>{{ occupiedAssetName }}</strong>. No puede reutilizarse para registrar
            otro activo.
          </p>

          <div class="rfid-modal-actions">
            <button type="button" class="rfid-secondary-btn" @click="cancelRfidScan">
              Cerrar
            </button>
            <button type="button" class="rfid-primary-btn" @click="restartRfidScan">
              <RefreshCw size="19" />
              Escanear otra
            </button>
          </div>
        </template>

        <template v-else>
          <div class="rfid-result-icon timeout">
            <Clock3 size="46" />
          </div>
          <span class="rfid-modal-kicker timeout">Tiempo agotado</span>
          <h2>No se recibió ninguna lectura</h2>
          <p>
            No se detectó una tarjeta o llavero durante los 25 segundos de espera.
            Comprueba que el ESP32 esté encendido y conectado a la red.
          </p>

          <div class="rfid-modal-actions">
            <button type="button" class="rfid-secondary-btn" @click="cancelRfidScan">
              Cerrar
            </button>
            <button type="button" class="rfid-primary-btn" @click="restartRfidScan">
              <RefreshCw size="19" />
              Reintentar lectura
            </button>
          </div>
        </template>
      </section>
    </div>
  </section>
</template>

<script>
import {
  Package,
  Save,
  X,
  ScanLine,
  LoaderCircle,
  RadioTower,
  CheckCircle2,
  TriangleAlert,
  RefreshCw,
  Clock3,
  Tag
} from "lucide-vue-next"
import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "RegisterAsset",

  components: {
    Package,
    Save,
    X,
    ScanLine,
    LoaderCircle,
    RadioTower,
    CheckCircle2,
    TriangleAlert,
    RefreshCw,
    Clock3,
    Tag
  },
  data() {
    return {
      isScanning: false,
      showSuccess: false,
      form: this.getEmptyForm(),
      edificios: [],
      laboratorios: [],
      marcas: [],
      modelos: [],
      categorias: [],

      rfidModal: {
        visible: false,
        status: "scanning"
      },
      scanSecondsRemaining: 25,
      scanCountdownTimer: null,
      scanPollingTimer: null,
      lastAlertId: 0,
      scannedCode: "",
      occupiedAssetName: ""
    }
  },
  computed: {
    availableLaboratories() {
      return this.laboratorios.filter(
        laboratorio => laboratorio.id_edificio == this.form.id_edificio
      )
    },
    maxDate() {
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    }
  },
  mounted() {
    this.getEdificios()
    this.getLaboratorios()
    this.getMarcas()
    this.getModelos()
    this.getCategorias()
  },
  beforeUnmount() {
    this.stopRfidTimers()
  },
  methods: {
    getEmptyForm() {
      return {
        name: "",
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
        model: ""
      }
    },
    clearForm() {
      this.stopRfidTimers()
      this.form = this.getEmptyForm();
      this.showSuccess = false;
      this.isScanning = false
      this.rfidModal.visible = false
      this.scannedCode = ""
      this.occupiedAssetName = ""
    },
    async getEdificios() {
      const res = await api.get("/edificio")
      this.edificios = res.data
    },
    async getLaboratorios() {
      const res = await api.get("/laboratorio")
      this.laboratorios = res.data
    },
    async getCategorias() {
      const res = await api.get("/categoria")
      this.categorias = res.data
    },
    async getMarcas() {
      const res = await api.get("/marca")
      this.marcas = res.data
    },
    async getModelos() {
      const res = await api.get("/modelo")
      this.modelos = res.data
    },
    handleBuildingChange() {
      this.form.id_laboratorio = ""
    },

    async scanRfid() {
      this.stopRfidTimers()
      this.isScanning = true
      this.scannedCode = ""
      this.occupiedAssetName = ""
      this.scanSecondsRemaining = 25
      this.rfidModal = {
        visible: true,
        status: "scanning"
      }

      try {
        await this.captureAlertBaseline()
        this.startRfidCountdown()
        this.startRfidPolling()
      } catch (error) {
        console.error(error)
        this.stopRfidTimers()
        this.isScanning = false
        this.rfidModal.status = "timeout"
      }
    },

    async captureAlertBaseline() {
      const response = await api.get("/alertas")
      const alerts = Array.isArray(response.data) ? response.data : []

      this.lastAlertId = alerts.reduce(
        (max, alert) => Math.max(max, Number(alert.id ?? 0)),
        0
      )
    },

    startRfidCountdown() {
      this.scanCountdownTimer = window.setInterval(() => {
        this.scanSecondsRemaining--

        if (this.scanSecondsRemaining <= 0) {
          this.stopRfidTimers()
          this.isScanning = false
          this.rfidModal.status = "timeout"
        }
      }, 1000)
    },

    startRfidPolling() {
      this.scanPollingTimer = window.setInterval(() => {
        this.checkNewRfidReading()
      }, 1500)
    },

    stopRfidTimers() {
      if (this.scanCountdownTimer) {
        window.clearInterval(this.scanCountdownTimer)
        this.scanCountdownTimer = null
      }

      if (this.scanPollingTimer) {
        window.clearInterval(this.scanPollingTimer)
        this.scanPollingTimer = null
      }
    },

    async checkNewRfidReading() {
      if (!this.isScanning) return

      try {
        const response = await api.get("/alertas")
        const alerts = Array.isArray(response.data) ? response.data : []

        const newRfidAlert = alerts
          .filter(alert => Number(alert.id ?? 0) > this.lastAlertId)
          .filter(alert => String(alert.codigo_rfid ?? "").trim() !== "")
          .sort((a, b) => Number(a.id ?? 0) - Number(b.id ?? 0))[0]

        if (!newRfidAlert) return

        this.lastAlertId = Number(newRfidAlert.id ?? this.lastAlertId)
        const code = String(newRfidAlert.codigo_rfid ?? "").trim().toUpperCase()

        if (!code) return

        this.stopRfidTimers()
        this.isScanning = false
        this.scannedCode = code

        await this.validateScannedTag(code)
      } catch (error) {
        console.error("Error consultando la lectura RFID:", error)
      }
    },

    async validateScannedTag(code) {
      try {
        const response = await api.get(
          `/movimientos/activo-rfid/${encodeURIComponent(code)}`
        )

        const asset = response.data?.activo

        if (asset?.id_activo) {
          this.occupiedAssetName = asset.nombre_activo ?? "Activo registrado"
          this.rfidModal.status = "occupied"
          return
        }

        this.rfidModal.status = "detected"
      } catch (error) {
        if (error.response?.status === 404) {
          this.rfidModal.status = "detected"
          return
        }

        console.error(error)
        this.rfidModal.status = "timeout"
      }
    },

    useScannedTag() {
      if (!this.scannedCode) return

      this.form.rfid = this.scannedCode
      this.rfidModal.visible = false
      this.isScanning = false

      Swal.fire({
        icon: "success",
        title: "Etiqueta preparada",
        text: `La etiqueta ${this.scannedCode} se utilizará para registrar el nuevo activo.`,
        timer: 1900,
        showConfirmButton: false
      })
    },

    restartRfidScan() {
      this.scanRfid()
    },

    cancelRfidScan() {
      this.stopRfidTimers()
      this.isScanning = false
      this.rfidModal.visible = false
    },

    async registerAsset() {
      try {
        // 1. Validación de RFID
        if (!this.form.rfid.trim()) {
          Swal.fire({
            icon: "warning",
            title: "RFID requerido",
            text: "Debe escanear una etiqueta RFID física antes de registrar el activo."
          })
          return
        }

        // 2. Validación de Fecha Futura
        const selectedDate = new Date(this.form.purchaseDate + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0); 
        if (selectedDate > today) {
          Swal.fire({
            icon: "error",
            title: "Fecha inválida",
            text: "La fecha de compra no puede ser una fecha futura."
          })
          return
        }

        // Estructuramos los datos parseando los selectores a números enteros
        const data = {
          nombre_activo: this.form.name,
          serie: this.form.serial,
          valor_compra: parseFloat(this.form.purchaseValue),
          fecha_compra: this.form.purchaseDate,
          vida_util: parseInt(this.form.usefulLife, 10),
          id_laboratorio: parseInt(this.form.id_laboratorio, 10),
          rfid: this.form.rfid.trim(),
          id_categoria: parseInt(this.form.category, 10),
          id_modelo: parseInt(this.form.model, 10),
          id_estado: 1, 
          id_responsable: null 
        }
         // Validación preventiva general
         if (parseInt(this.form.usefulLife, 10) > 30) {
          Swal.fire({
              icon: "warning",
             title: "Vida útil inusual",
              text: "¿Estás seguro de que este activo dura más de 30 años?"
            });
            return;
         }
        await api.post("/activo", data)
        
        this.form = this.getEmptyForm()
        this.showSuccess = true
        
        Swal.fire({
          icon: "success",
          title: "Activo registrado",
          text: "El activo fue registrado correctamente "
        })

        setTimeout(() => {
          this.showSuccess = false
        }, 5000)

      } catch (error) {
        console.error(error)
        Swal.fire({
          icon: "error",
          title: "Error al registrar",
          text: error.response?.data?.message || "No fue posible registrar el activo en el sistema."
        })
      }
    }
  }
}
</script>

<style scoped>
.rfid-selected {
  margin-top: 12px;
  padding: 13px 15px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  border: 1px solid #a9dec0;
  border-radius: 13px;
  color: #137344;
  background: #effbf4;
}

.rfid-selected strong,
.rfid-selected small {
  display: block;
}

.rfid-selected small {
  margin-top: 4px;
  color: #547264;
}

.rfid-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 220;
  padding: 22px;
  display: grid;
  place-items: center;
  background: rgba(8, 18, 36, 0.7);
  backdrop-filter: blur(8px);
}

.rfid-modal {
  position: relative;
  width: min(590px, 100%);
  padding: 36px 34px 32px;
  border-radius: 26px;
  background: #fff;
  text-align: center;
  box-shadow: 0 30px 90px rgba(0, 0, 0, 0.28);
  animation: rfidModalIn 0.24s ease;
}

.rfid-modal-close {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border: none;
  border-radius: 12px;
  color: #4d596c;
  background: #f1f3f6;
  cursor: pointer;
}

.rfid-modal h2 {
  margin: 10px 0;
  color: #10213e;
  font-size: 28px;
}

.rfid-modal p {
  max-width: 490px;
  margin: 0 auto;
  color: #66758a;
  line-height: 1.65;
}

.rfid-modal-kicker {
  display: block;
  margin-top: 18px;
  color: #b51722;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.rfid-modal-kicker.success {
  color: #16824c;
}

.rfid-modal-kicker.danger,
.rfid-modal-kicker.timeout {
  color: #ad3e15;
}

.rfid-scan-rings {
  position: relative;
  width: 170px;
  height: 170px;
  margin: 4px auto 22px;
  display: grid;
  place-items: center;
}

.rfid-scan-rings > span {
  position: absolute;
  inset: 0;
  border: 2px solid rgba(190, 23, 34, 0.28);
  border-radius: 50%;
  animation: rfidWave 2.1s infinite;
}

.rfid-scan-rings > span:nth-child(2) {
  animation-delay: 0.55s;
}

.rfid-scan-rings > span:nth-child(3) {
  animation-delay: 1.1s;
}

.rfid-scan-center {
  width: 88px;
  height: 88px;
  display: grid;
  place-items: center;
  border-radius: 28px;
  color: #fff;
  background: linear-gradient(145deg, #f32b35, #b9101b);
  box-shadow: 0 18px 40px rgba(190, 23, 34, 0.3);
}

.rfid-countdown {
  width: min(270px, 100%);
  margin: 24px auto 20px;
  padding: 13px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border: 1px solid #ead9d3;
  border-radius: 14px;
  color: #6c5a52;
  background: #fff9f6;
}

.rfid-countdown strong {
  color: #b51722;
  font-size: 21px;
}

.rfid-result-icon {
  width: 92px;
  height: 92px;
  margin: 5px auto 18px;
  display: grid;
  place-items: center;
  border-radius: 50%;
}

.rfid-result-icon.success {
  color: #16824c;
  background: #e5f8ed;
}

.rfid-result-icon.danger,
.rfid-result-icon.timeout {
  color: #ad3e15;
  background: #fff0e7;
}

.rfid-modal-actions {
  margin-top: 28px;
  display: flex;
  justify-content: center;
  gap: 12px;
}

.rfid-primary-btn,
.rfid-secondary-btn {
  min-height: 48px;
  padding: 0 19px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border-radius: 13px;
  font: inherit;
  font-weight: 900;
  cursor: pointer;
}

.rfid-primary-btn {
  border: 1px solid #be1722;
  color: #fff;
  background: #be1722;
}

.rfid-secondary-btn {
  border: 1px solid #d5dae2;
  color: #354157;
  background: #fff;
}

.rfid-modal > .rfid-secondary-btn {
  margin-top: 4px;
}

.spin {
  animation: spin 0.85s linear infinite;
}

@keyframes rfidWave {
  0% {
    transform: scale(0.35);
    opacity: 0.9;
  }

  100% {
    transform: scale(1.1);
    opacity: 0;
  }
}

@keyframes rfidModalIn {
  from {
    transform: translateY(12px) scale(0.98);
    opacity: 0;
  }

  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 620px) {
  .rfid-modal {
    padding: 32px 20px 24px;
  }

  .rfid-modal-actions {
    flex-direction: column;
  }

  .rfid-primary-btn,
  .rfid-secondary-btn {
    width: 100%;
  }
}
</style>