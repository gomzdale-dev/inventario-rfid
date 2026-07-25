<template>
  <section class="register-movement-page">
    <header class="register-header">
      <h1>Registrar Movimientos</h1>
      <p>Control de entradas, salidas y traslados de activos tecnológicos</p>
    </header>

    <form class="movement-form-card" @submit.prevent="saveMovement">
      <div class="top-grid">
        <!-- LECTURA RFID -->
        <div class="field-block">
          <label class="field-label">
            Lectura RFID del activo <span>*</span>
          </label>

          <div
            :class="[
              'rfid-reader-card',
              {
                scanning: isScanning,
                detected: detectedAsset,
                error: scanError
              }
            ]"
          >
            <div class="reader-icon">
              <CheckCircle2 v-if="detectedAsset" size="30" />
              <PackageSearch v-else-if="scanError" size="30" />
              <Radio v-else size="30" :class="{ pulse: isScanning }" />
            </div>

            <div class="reader-content">
              <template v-if="detectedAsset">
                <div class="reader-status success">
                  <span class="status-dot"></span>
                  Activo detectado
                </div>

                <h3>{{ detectedAsset.nombre_activo }}</h3>

                <div class="asset-details">
                  <div class="asset-detail">
                    <Tag size="17" />
                    <div>
                      <small>Etiqueta RFID</small>
                      <strong>{{ detectedAsset.codigo_rfid }}</strong>
                    </div>
                  </div>

                  <div
                    v-if="detectedAsset.ubicacion_actual"
                    class="asset-detail"
                  >
                    <MapPin size="17" />
                    <div>
                      <small>Ubicación actual</small>
                      <strong>{{ detectedAsset.ubicacion_actual }}</strong>
                    </div>
                  </div>
                </div>
              </template>

              <template v-else-if="scanError">
                <div class="reader-status danger">
                  <span class="status-dot"></span>
                  Etiqueta no reconocida
                </div>

                <h3>No se encontró un activo asociado</h3>
                <p>{{ scanError }}</p>
              </template>

              <template v-else>
                <div class="reader-status" :class="{ active: isScanning }">
                  <span class="status-dot"></span>
                  {{ isScanning ? "Esperando lectura" : "Lector preparado" }}
                </div>

                <h3>
                  {{
                    isScanning
                      ? "Acerca una tarjeta o llavero al lector"
                      : "Inicia la lectura RFID"
                  }}
                </h3>

                <p>
                  El sistema identificará automáticamente el activo asociado a
                  la etiqueta leída.
                </p>
              </template>
            </div>

            <div class="reader-action-row">
              <button
                type="button"
                class="scan-button"
                :disabled="isPreparingScan"
                @click="startRfidScan"
              >
                <RefreshCw
                  v-if="detectedAsset || scanError"
                  size="19"
                  :class="{ rotating: isPreparingScan }"
                />
                <ScanLine
                  v-else
                  size="19"
                  :class="{ rotating: isPreparingScan }"
                />

                {{
                  isPreparingScan
                    ? "Preparando..."
                    : detectedAsset || scanError
                      ? "Leer otra etiqueta"
                      : isScanning
                        ? "Reiniciar lectura"
                        : "Iniciar lectura"
                }}
              </button>
            </div>
          </div>

          <small class="field-help">
            La etiqueta debe estar asociada previamente a un activo registrado.
          </small>
        </div>

        <!-- TIPO DE MOVIMIENTO -->
        <div class="field-block movement-type-field">
          <label class="field-label">
            Tipo de Movimiento <span>*</span>
          </label>

          <select v-model="form.tipo_movimiento" required>
            <option value="">Seleccionar movimiento...</option>
            <option
              v-for="type in filteredMovementTypes"
              :key="type.id"
              :value="type.id"
            >
              {{ type.nombre_movimiento }}
            </option>
          </select>

          <div class="type-info">
            <Wifi size="19" />
            <span>
              El movimiento se vinculará con el activo detectado por RFID.
            </span>
          </div>
        </div>
      </div>

      <div class="form-grid">
        <div class="field-block">
          <label class="field-label">Edificio <span>*</span></label>
          <select
            v-model="form.id_edificio"
            required
            @change="handleBuildingChange"
          >
            <option value="">Seleccionar edificio...</option>
            <option
              v-for="edificio in edificios"
              :key="edificio.id_edificio"
              :value="edificio.id_edificio"
            >
              {{ edificio.nombre_edificio }}
            </option>
          </select>
        </div>

        <div class="field-block">
          <label class="field-label">Salón <span>*</span></label>
          <select
            v-model="form.id_laboratorio"
            required
            :disabled="!form.id_edificio"
          >
            <option value="">
              {{
                form.id_edificio
                  ? "Seleccionar salón..."
                  : "Primero selecciona un edificio"
              }}
            </option>
            <option
              v-for="salon in availableSalones"
              :key="salon.id_laboratorio"
              :value="salon.id_laboratorio"
            >
              {{ salon.nombre_laboratorio }}
            </option>
          </select>
          <small class="field-help">
            La fecha y hora del movimiento se tomarán automáticamente al guardar.
          </small>
        </div>

        <div class="field-block full">
          <label class="field-label">Comentarios</label>
          <textarea
            v-model="form.comentarios"
            maxlength="100"
            placeholder="Comentario breve del movimiento..."
          ></textarea>
          <small class="field-help">
            {{ form.comentarios.length }}/100 caracteres.
          </small>
        </div>
      </div>

      <div class="form-actions">
        <button
          type="submit"
          class="primary-action"
          :disabled="isSaving || !detectedAsset"
        >
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
import {
  Save,
  X,
  Radio,
  ScanLine,
  CheckCircle2,
  RefreshCw,
  Tag,
  PackageSearch,
  MapPin,
  Wifi
} from "lucide-vue-next"
import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "AssetMovements",

  components: {
    Save,
    X,
    Radio,
    ScanLine,
    CheckCircle2,
    RefreshCw,
    Tag,
    PackageSearch,
    MapPin,
    Wifi
  },

  data() {
    return {
      isSaving: false,
      isScanning: false,
      isPreparingScan: false,
      scanError: "",
      scanInterval: null,
      scanBaselineId: 0,
      detectedAsset: null,
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
    },

    filteredMovementTypes() {
      return this.movementTypes.filter(type => Number(type.id) > 1)
    }
  },

  mounted() {
    this.loadCatalogs()
    this.getEdificios()
    this.getLaboratorios()
  },

  beforeUnmount() {
    this.stopRfidScan()
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
        this.movementTypes = response.data.tipos_movimiento ?? []
        this.locations = response.data.ubicaciones ?? []
      } catch (error) {
        console.error(error)
        Swal.fire({
          icon: "error",
          title: "Error",
          text:
            error.response?.data?.message ||
            "No fue posible cargar los catálogos para movimientos."
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

    normalizeAlerts(payload) {
      if (Array.isArray(payload)) return payload
      if (Array.isArray(payload?.data)) return payload.data
      if (Array.isArray(payload?.alertas)) return payload.alertas
      return []
    },

    getAlertId(alert) {
      return Number(alert?.id ?? alert?.id_alerta ?? 0)
    },

    async getLatestRfidAlert() {
      const response = await api.get("/alertas")

      const alerts = this.normalizeAlerts(response.data)
        .filter(alert => alert?.tipo === "rfid" && Boolean(alert?.codigo_rfid))
        .sort((a, b) => this.getAlertId(b) - this.getAlertId(a))

      return alerts[0] ?? null
    },

    async startRfidScan() {
      try {
        this.stopRfidScan()
        this.isPreparingScan = true
        this.scanError = ""
        this.detectedAsset = null
        this.form.id_activo = ""

        const latestAlert = await this.getLatestRfidAlert()
        this.scanBaselineId = latestAlert ? this.getAlertId(latestAlert) : 0

        this.isScanning = true
        this.scanInterval = window.setInterval(this.checkRfidReading, 1800)
      } catch (error) {
        console.error(error)
        this.scanError =
          "No fue posible preparar la lectura RFID. Verifica la conexión con el servidor."
      } finally {
        this.isPreparingScan = false
      }
    },

    async checkRfidReading() {
      if (!this.isScanning) return

      try {
        const latestAlert = await this.getLatestRfidAlert()

        if (!latestAlert) return

        const latestId = this.getAlertId(latestAlert)

        if (latestId <= this.scanBaselineId) return

        this.scanBaselineId = latestId
        await this.loadAssetByRfid(latestAlert.codigo_rfid)
      } catch (error) {
        console.error("Error consultando lectura RFID:", error)
      }
    },

    async loadAssetByRfid(codigoRfid) {
      try {
        const response = await api.get(
          `/movimientos/activo-rfid/${encodeURIComponent(codigoRfid)}`
        )

        this.detectedAsset = response.data.activo
        this.form.id_activo = response.data.activo.id_activo
        this.scanError = ""
        this.stopRfidScan()

        Swal.fire({
          icon: "success",
          title: "Activo detectado",
          text: `${this.detectedAsset.nombre_activo} fue identificado correctamente.`,
          timer: 1800,
          showConfirmButton: false
        })
      } catch (error) {
        this.detectedAsset = null
        this.form.id_activo = ""
        this.scanError =
          error.response?.data?.message ||
          `La etiqueta ${codigoRfid} no está asociada a un activo.`
        this.stopRfidScan()
      }
    },

    stopRfidScan() {
      if (this.scanInterval) {
        window.clearInterval(this.scanInterval)
        this.scanInterval = null
      }

      this.isScanning = false
    },

    handleBuildingChange() {
      this.form.id_laboratorio = ""
    },

    async saveMovement() {
      if (!this.detectedAsset || !this.form.id_activo) {
        Swal.fire({
          icon: "warning",
          title: "Activo no detectado",
          text: "Primero debes leer una etiqueta RFID asociada a un activo."
        })
        return
      }

      try {
        this.isSaving = true

        await api.post("/movimientos", {
          id_activo: this.form.id_activo,
          tipo_movimiento: this.form.tipo_movimiento,
          id_laboratorio: this.form.id_laboratorio,
          comentarios: this.form.comentarios
        })

        await Swal.fire({
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
          text:
            error.response?.data?.message ||
            "No fue posible registrar el movimiento."
        })
      } finally {
        this.isSaving = false
      }
    },

    clearForm() {
      this.stopRfidScan()
      this.form = this.getEmptyForm()
      this.detectedAsset = null
      this.scanError = ""
      this.scanBaselineId = 0
    }
  }
}
</script>

<style scoped>
.register-movement-page {
  min-height: 100%;
  padding: 30px 40px 48px;
  background:
    radial-gradient(circle at top right, rgba(180, 124, 45, 0.08), transparent 34%),
    #f7f4ef;
  color: #10203d;
}

.register-header {
  margin-bottom: 28px;
}

.section-kicker {
  display: inline-block;
  margin-bottom: 7px;
  color: #a33b2f;
  font-size: 0.8rem;
  font-weight: 900;
  letter-spacing: 0.13em;
  text-transform: uppercase;
}

.register-header h1 {
  margin: 0;
  color: #10203d;
  font-size: clamp(2.2rem, 4vw, 3.45rem);
  font-weight: 900;
  line-height: 1;
}

.register-header p {
  margin: 14px 0 0;
  color: #58677e;
  font-size: 1.05rem;
}

.movement-form-card {
  width: 100%;
  box-sizing: border-box;
  padding: 36px;
  border: 1px solid rgba(16, 32, 61, 0.08);
  border-radius: 26px;
  background: #ffffff;
  box-shadow: 0 24px 60px rgba(55, 37, 22, 0.1);
}

.top-grid,
.form-grid {
  display: grid;
  width: 100%;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 28px 32px;
  align-items: start;
}

.top-grid {
  margin-bottom: 30px;
}

.field-block {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
}

.field-block.full {
  grid-column: 1 / -1;
}

.field-label {
  display: block;
  margin-bottom: 10px;
  color: #17233d;
  font-size: 1rem;
  font-weight: 850;
}

.field-label span {
  color: #e52b2f;
}

.field-block select,
.field-block textarea {
  display: block;
  width: 100%;
  box-sizing: border-box;
  border: 2px solid #d7dce5;
  border-radius: 15px;
  background: #fff;
  color: #17233d;
  font: inherit;
  outline: none;
  transition: 0.2s ease;
}

.field-block select {
  height: 58px;
  padding: 0 16px;
}

.field-block textarea {
  min-height: 150px;
  padding: 16px;
  resize: vertical;
}

.field-block select:focus,
.field-block textarea:focus {
  border-color: #b47c2d;
  box-shadow: 0 0 0 4px rgba(180, 124, 45, 0.12);
}

.field-block select:disabled {
  cursor: not-allowed;
  background: #f3f5f8;
  color: #9da8b9;
}

.field-help {
  display: block;
  margin-top: 9px;
  color: #6d7b90;
  font-size: 0.84rem;
}

.rfid-reader-card {
  display: grid;
  width: 100%;
  min-height: 164px;
  box-sizing: border-box;
  grid-template-columns: 64px minmax(0, 1fr);
  gap: 18px;
  align-items: center;
  padding: 22px;
  border: 2px dashed #c9d0db;
  border-radius: 20px;
  background: linear-gradient(135deg, #ffffff, #fbf7f1);
  transition: 0.25s ease;
}

.rfid-reader-card.scanning {
  border-color: #b47c2d;
  box-shadow: 0 0 0 5px rgba(180, 124, 45, 0.1);
}

.rfid-reader-card.detected {
  border-style: solid;
  border-color: #209565;
  background: linear-gradient(135deg, rgba(32, 149, 101, 0.08), #ffffff);
}

.rfid-reader-card.error {
  border-style: solid;
  border-color: #d94a4a;
  background: linear-gradient(135deg, rgba(217, 74, 74, 0.07), #ffffff);
}

.reader-icon {
  display: grid;
  place-items: center;
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: #7b431f;
  color: #fff;
  box-shadow: 0 12px 24px rgba(123, 67, 31, 0.2);
}

.detected .reader-icon {
  background: #209565;
}

.error .reader-icon {
  background: #d94a4a;
}

.reader-content {
  min-width: 0;
}

.reader-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
  color: #6b778b;
  font-size: 0.78rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.reader-status.active {
  color: #a1681f;
}

.reader-status.success {
  color: #168259;
}

.reader-status.danger {
  color: #c03b3b;
}

.status-dot {
  flex: 0 0 auto;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.reader-content h3 {
  margin: 0 0 7px;
  color: #17233d;
  font-size: 1.08rem;
  line-height: 1.35;
}

.reader-content p {
  margin: 0;
  color: #6d7b90;
  font-size: 0.9rem;
  line-height: 1.5;
}

.asset-details {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 12px;
}

.asset-detail {
  display: flex;
  min-width: 175px;
  align-items: center;
  gap: 9px;
  padding: 9px 11px;
  border: 1px solid rgba(32, 149, 101, 0.18);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.82);
  color: #168259;
}

.asset-detail div {
  display: flex;
  min-width: 0;
  flex-direction: column;
}

.asset-detail small {
  color: #738095;
  font-size: 0.72rem;
}

.asset-detail strong {
  overflow: hidden;
  color: #17233d;
  font-size: 0.86rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.reader-action-row {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  width: 100%;
  padding-top: 2px;
}

.scan-button {
  display: inline-flex;
  min-height: 46px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 17px;
  border: 0;
  border-radius: 12px;
  background: #e6282d;
  color: #fff;
  font: inherit;
  font-weight: 850;
  white-space: nowrap;
  cursor: pointer;
  box-shadow: 0 10px 22px rgba(230, 40, 45, 0.23);
  transition: 0.2s ease;
}

.scan-button:hover:not(:disabled) {
  transform: translateY(-2px);
  background: #cf1d23;
}

.scan-button:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}

.movement-type-field select {
  margin-bottom: 16px;
}

.type-info {
  display: flex;
  min-height: 66px;
  box-sizing: border-box;
  align-items: center;
  gap: 11px;
  padding: 15px 16px;
  border: 1px solid rgba(180, 124, 45, 0.22);
  border-radius: 14px;
  background: rgba(180, 124, 45, 0.07);
  color: #735126;
  font-size: 0.9rem;
  line-height: 1.45;
}

.form-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  margin-top: 30px;
  padding-top: 24px;
  border-top: 1px solid #e5e8ee;
}

.primary-action,
.secondary-action {
  display: inline-flex;
  min-height: 50px;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 0 22px;
  border-radius: 13px;
  font: inherit;
  font-weight: 850;
  cursor: pointer;
  transition: 0.2s ease;
}

.primary-action {
  border: 0;
  background: #e6282d;
  color: #fff;
  box-shadow: 0 11px 24px rgba(230, 40, 45, 0.22);
}

.primary-action:hover:not(:disabled) {
  transform: translateY(-2px);
  background: #cf1d23;
}

.primary-action:disabled {
  cursor: not-allowed;
  opacity: 0.55;
}

.secondary-action {
  border: 1px solid #cdd3dd;
  background: #fff;
  color: #354157;
}

.secondary-action:hover {
  border-color: #9ea8b7;
  background: #f5f6f8;
}

.pulse {
  animation: pulse 1.25s ease-in-out infinite;
}

.rotating {
  animation: rotate 0.9s linear infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 0.55;
    transform: scale(0.92);
  }

  50% {
    opacity: 1;
    transform: scale(1.08);
  }
}

@keyframes rotate {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1100px) {
  .rfid-reader-card {
    grid-template-columns: 58px minmax(0, 1fr);
  }

  .reader-icon {
    width: 58px;
    height: 58px;
  }

  .reader-action-row {
    justify-content: stretch;
  }

  .scan-button {
    width: 100%;
  }
}

@media (max-width: 900px) {
  .register-movement-page {
    padding: 26px 22px 40px;
  }

  .top-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 620px) {
  .register-movement-page {
    padding: 22px 14px 34px;
  }

  .movement-form-card {
    padding: 22px 16px;
    border-radius: 20px;
  }

  .rfid-reader-card {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .reader-icon {
    margin: 0 auto;
  }

  .reader-action-row {
    justify-content: stretch;
  }

  .asset-details {
    justify-content: center;
  }

  .asset-detail {
    width: 100%;
    box-sizing: border-box;
    text-align: left;
  }

  .form-actions {
    flex-direction: column;
  }

  .primary-action,
  .secondary-action {
    width: 100%;
  }
}
</style>