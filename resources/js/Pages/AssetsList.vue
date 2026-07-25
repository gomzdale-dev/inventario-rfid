<template>
  <section class="inventory-page">
    <header class="inventory-header">
      <div>
        <h1>Activos</h1>
        <p>Consulta general de activos registrados en el sistema RFID</p>
      </div>
    </header>

    <section class="inventory-toolbar assets-toolbar-fix assets-scan-toolbar">
      <div class="search-box">
        <Search size="24" />
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre, serie, RFID, responsable o ubicación..."
        />
      </div>

      <button
        class="scan-rfid-action"
        type="button"
        :disabled="isPreparingScan"
        @click="startRfidScan"
      >
        <ScanLine size="22" />
        {{ isPreparingScan ? "Preparando..." : "Escanear Etiqueta" }}
      </button>
    </section>

    <section class="inventory-table-card assets-table-fix">
      <div v-if="isLoading" class="assets-state-box">
        Cargando activos registrados...
      </div>

      <div v-else-if="filteredAssets.length === 0" class="assets-state-box">
        No se encontraron activos registrados para los filtros seleccionados.
      </div>

      <div v-else class="assets-table-scroll">
        <table>
          <thead>
            <tr>
              <th>Activo</th>
              <th>Serie</th>
              <th>RFID</th>
              <th>Ubicación</th>
              <th>Responsable</th>
              
              <th class="actions-column">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="asset in paginatedAssets" :key="asset.id">
              <td>{{ asset.name }}</td>
              <td>{{ asset.serial }}</td>
              <td class="rfid-code">{{ asset.rfid }}</td>
              <td>{{ asset.location }}</td>
              <td>
                <span :class="['asset-badge', asset.responsible === 'Sin asignar' ? 'warning' : 'success']">
                  {{ asset.responsible }}
                </span>
              </td>
              
              <td class="actions-column">
                <div class="asset-actions">
                  <button class="asset-icon-action view" type="button" title="Ver detalle" @click="openDetail(asset)">
                    <Eye size="20" />
                  </button>

                  <button class="asset-icon-action assign" type="button" title="Asignar" @click="openAssignModal(asset)">
                    <UserCheck size="20" />
                  </button>

                  <button class="asset-icon-action rfid" type="button" title="Cambiar etiqueta RFID" @click="openRfidModal(asset)">
                    <Tags size="20" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer class="table-footer">
        <p>Mostrando {{ filteredAssets.length }} de {{ assets.length }} activos</p>

        <div class="pagination">
          <button :disabled="currentPage === 1" @click="currentPage--">
            Anterior
          </button>

          <button class="active-page">{{ currentPage }}</button>

          <button :disabled="currentPage === totalPages" @click="currentPage++">
            Siguiente
          </button>
        </div>
      </footer>
    </section>

    <!-- MODAL PARA ESCANEAR Y LOCALIZAR UN ACTIVO -->
    <div v-if="showScanModal" class="modal-backdrop rfid-scan-backdrop" @click="closeScanModal">
      <div class="rfid-scan-modal" @click.stop>
        <button type="button" class="rfid-scan-close" aria-label="Cerrar" @click="closeScanModal">
          <X size="24" />
        </button>

        <template v-if="scanStatus === 'scanning'">
          <div class="rfid-scan-visual scanning"><Radio size="52" /></div>
          <span class="rfid-scan-kicker">LECTOR RFID ACTIVO</span>
          <h2>Escaneando etiqueta RFID</h2>
          <p>Acerca una tarjeta o un llavero al lector RC522. El sistema buscará automáticamente el activo asociado.</p>
          <div class="rfid-countdown"><span>Tiempo restante</span><strong>{{ scanSeconds }} s</strong></div>
          <button type="button" class="secondary-action rfid-modal-action" @click="closeScanModal">Cancelar lectura</button>
        </template>

        <template v-else-if="scanStatus === 'found'">
          <div class="rfid-scan-visual success"><CheckCircle2 size="52" /></div>
          <span class="rfid-scan-kicker success-text">ACTIVO IDENTIFICADO</span>
          <h2>{{ scannedAsset?.nombre_activo }}</h2>
          <p>La etiqueta <strong>{{ scannedCode }}</strong> está asociada a un activo registrado.</p>
          <div class="scanned-asset-summary">
            <div><span>Etiqueta RFID</span><strong>{{ scannedCode }}</strong></div>
            <div><span>Serie</span><strong>{{ scannedAsset?.serie || "Sin serie" }}</strong></div>
            <div><span>Ubicación actual</span><strong>{{ scannedAsset?.ubicacion_actual || "Sin ubicación" }}</strong></div>
          </div>
          <div class="rfid-modal-actions">
            <button type="button" class="secondary-action" @click="restartRfidScan"><RefreshCw size="20" /> Escanear otra</button>
            <button type="button" class="primary-action" @click="showScannedAsset"><Eye size="20" /> Ver activo en la tabla</button>
          </div>
        </template>

        <template v-else-if="scanStatus === 'not-found'">
          <div class="rfid-scan-visual warning"><AlertTriangle size="52" /></div>
          <span class="rfid-scan-kicker warning-text">ETIQUETA SIN ASOCIACIÓN</span>
          <h2>No hay un activo asociado</h2>
          <p>La etiqueta <strong>{{ scannedCode }}</strong> fue detectada, pero no pertenece a ningún activo registrado.</p>
          <div class="rfid-modal-actions">
            <button type="button" class="secondary-action" @click="closeScanModal">Cerrar</button>
            <button type="button" class="primary-action" @click="restartRfidScan"><RefreshCw size="20" /> Escanear otra</button>
          </div>
        </template>

        <template v-else-if="scanStatus === 'timeout'">
          <div class="rfid-scan-visual timeout"><Radio size="52" /></div>
          <span class="rfid-scan-kicker timeout-text">TIEMPO DE ESPERA AGOTADO</span>
          <h2>No se recibió ninguna lectura</h2>
          <p>No se detectó una tarjeta o llavero durante los 25 segundos de espera.</p>
          <div class="rfid-modal-actions">
            <button type="button" class="secondary-action" @click="closeScanModal">Cerrar</button>
            <button type="button" class="primary-action" @click="restartRfidScan"><RefreshCw size="20" /> Reintentar lectura</button>
          </div>
        </template>

        <template v-else-if="scanStatus === 'error'">
          <div class="rfid-scan-visual warning"><AlertTriangle size="52" /></div>
          <span class="rfid-scan-kicker warning-text">ERROR DE CONEXIÓN</span>
          <h2>No fue posible iniciar la lectura</h2>
          <p>{{ scanError }}</p>
          <div class="rfid-modal-actions">
            <button type="button" class="secondary-action" @click="closeScanModal">Cerrar</button>
            <button type="button" class="primary-action" @click="restartRfidScan"><RefreshCw size="20" /> Reintentar</button>
          </div>
        </template>
      </div>
    </div>

    <div v-if="selectedAsset" class="modal-backdrop" @click="selectedAsset = null">
      <div class="asset-modal asset-detail-modal" @click.stop>
        <h2>Detalle del Activo</h2>

        <div class="asset-detail-grid">
          <p><strong>Nombre:</strong> {{ selectedAsset.name }}</p>
          <p><strong>Serie:</strong> {{ selectedAsset.serial }}</p>
          <p><strong>RFID:</strong> {{ selectedAsset.rfid }}</p>
          <p><strong>Ubicación:</strong> {{ selectedAsset.location }}</p>
          <p><strong>Responsable:</strong> {{ selectedAsset.responsible }}</p>
          <p><strong>Estado:</strong> {{ selectedAsset.status }}</p>
          <p><strong>Valor de compra:</strong> ${{ selectedAsset.purchaseValue }}</p>
          <p><strong>Valor actual:</strong> ${{ selectedAsset.currentValue }}</p>
          <p><strong>Fecha de compra:</strong> {{ selectedAsset.purchaseDate }}</p>
        </div>

        <button type="button" class="primary-action modal-main-button" @click="selectedAsset = null">
          Cerrar
        </button>
      </div>
    </div>

    <div v-if="showAssignModal" class="modal-backdrop" @click="closeAssignModal">
      <div class="asset-modal assign-modal" @click.stop>
        <div class="assign-modal-header">
          <div class="assign-modal-icon">
            <UserCheck size="30" />
          </div>
          <div>
            <h2>Asignar Responsable</h2>
            <p>Selecciona el responsable que tendrá asignado este activo.</p>
          </div>
        </div>

        <div class="assign-asset-summary">
          <div>
            <span>Activo</span>
            <strong>{{ assetToAssign?.name ?? "Sin activo seleccionado" }}</strong>
          </div>

          <div>
            <span>Serie</span>
            <strong>{{ assetToAssign?.serial ?? "Sin serie" }}</strong>
          </div>

          <div>
            <span>RFID</span>
            <strong>{{ assetToAssign?.rfid ?? "Sin RFID" }}</strong>
          </div>
        </div>

        <div class="assign-field">
          <label>Responsable <span>*</span></label>
          <select v-model="assignForm.id_responsable">
            <option value="">Seleccionar responsable...</option>
            <option
              v-for="responsible in responsables"
              :key="responsible.id"
              :value="responsible.id"
            >
              {{ responsible.nombre_responsable }} - {{ responsible.codigo_empleado }}
            </option>
          </select>
        </div>

        <div class="assign-modal-actions">
          <button type="button" class="primary-action" :disabled="isAssigning" @click="assignResponsible">
            <UserCheck size="20" />
            {{ isAssigning ? "Asignando..." : "Guardar Asignación" }}
          </button>

          <button type="button" class="secondary-action" @click="closeAssignModal">
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <div v-if="showRfidModal" class="modal-backdrop" @click="closeRfidModal">
      <div class="asset-modal assign-modal" @click.stop>
        <div class="assign-modal-header">
          <div class="assign-modal-icon rfid-modal-icon">
            <Tags size="30" />
          </div>
          <div>
            <h2>Cambiar Etiqueta RFID</h2>
            <p>Actualiza la etiqueta RFID asignada a este activo.</p>
          </div>
        </div>

        <div class="assign-asset-summary">
          <div>
            <span>Activo</span>
            <strong>{{ assetToChangeRfid?.name ?? "Sin activo seleccionado" }}</strong>
          </div>

          <div>
            <span>Serie</span>
            <strong>{{ assetToChangeRfid?.serial ?? "Sin serie" }}</strong>
          </div>

          <div>
            <span>RFID actual</span>
            <strong>{{ assetToChangeRfid?.rfid ?? "Sin RFID" }}</strong>
          </div>
          
        </div>

        <div class="assign-field">
          <label>Nueva etiqueta RFID <span>*</span></label>

          <div class="rfid-replacement-field">
            <input
              v-model="rfidForm.codigo_rfid"
              type="text"
              maxlength="50"
              placeholder="Presiona Escanear nueva etiqueta"
              readonly
            />

            <button
              type="button"
              class="scan-new-rfid-button"
              :disabled="isPreparingRfidReplacement"
              @click="startRfidReplacementScan"
            >
              <LoaderCircle v-if="isPreparingRfidReplacement" size="20" class="rotating" />
              <ScanLine v-else size="20" />
              {{ isPreparingRfidReplacement ? "Preparando..." : isRfidReplacementScanning ? "Reiniciar lectura" : "Escanear nueva etiqueta" }}
            </button>
          </div>

          <small>La nueva etiqueta se capturará directamente desde el lector RFID físico.</small>

          <div v-if="rfidReplacementStatus === 'scanning'" class="rfid-replacement-feedback scanning">
            <Radio size="19" class="pulse" />
            <div><strong>Esperando una nueva lectura RFID</strong><span>Acerca una tarjeta o llavero. Tiempo restante: {{ rfidReplacementSeconds }} s</span></div>
          </div>

          <div v-else-if="rfidReplacementStatus === 'detected'" class="rfid-replacement-feedback detected">
            <CheckCircle2 size="19" />
            <div><strong>Etiqueta detectada: {{ rfidForm.codigo_rfid }}</strong><span>Esta lectura será utilizada como la nueva etiqueta del activo.</span></div>
          </div>

          <div v-else-if="rfidReplacementStatus === 'same'" class="rfid-replacement-feedback warning">
            <AlertTriangle size="19" />
            <div><strong>La etiqueta detectada es la misma que ya tiene el activo</strong><span>Escanea una etiqueta diferente para realizar el cambio.</span></div>
          </div>

          <div v-else-if="rfidReplacementStatus === 'timeout'" class="rfid-replacement-feedback warning">
            <AlertTriangle size="19" />
            <div><strong>No se recibió ninguna lectura</strong><span>Vuelve a presionar “Escanear nueva etiqueta” para intentarlo otra vez.</span></div>
          </div>

          <div v-else-if="rfidReplacementStatus === 'error'" class="rfid-replacement-feedback warning">
            <AlertTriangle size="19" />
            <div><strong>No fue posible completar la lectura</strong><span>{{ rfidReplacementError }}</span></div>
          </div>

          <label>Motivo de Cambio <span>*</span></label>
          <input v-model="rfidForm.motivo" type="text" maxlength="50" placeholder="Ej: Etiqueta dañada" />
        </div>

        <div class="assign-modal-actions">
          <button type="button" class="primary-action rfid-save-action" :disabled="isChangingRfid" @click="changeRfidTag">
            <Tags size="20" />
            {{ isChangingRfid ? "Guardando..." : "Guardar Etiqueta" }}
          </button>

          <button type="button" class="secondary-action" @click="closeRfidModal">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import {
  Search,
  Eye,
  UserCheck,
  Tags,
  ScanLine,
  Radio,
  X,
  CheckCircle2,
  AlertTriangle,
  RefreshCw,
  LoaderCircle
} from "lucide-vue-next"

import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "AssetsList",

  components: {
    Search,
    Eye,
    UserCheck,
    Tags,
    ScanLine,
    Radio,
    X,
    CheckCircle2,
    AlertTriangle,
    RefreshCw,
    LoaderCircle
  },

  data() {
    return {
      search: "",
      currentPage: 1,
      perPage: 10,
      isLoading: false,
      isAssigning: false,
      isChangingRfid: false,
      selectedAsset: null,
      showAssignModal: false,
      showRfidModal: false,
      assetToAssign: null,
      assetToChangeRfid: null,
      responsables: [],
      assignForm: {
        id_responsable: ""
      },
      rfidForm: {
        codigo_rfid: "",
        motivo:""
      },
      assets: [],

      // Lectura RFID para localizar activos
      showScanModal: false,
      isPreparingScan: false,
      scanStatus: "idle",
      scanSeconds: 25,
      scanBaselineId: 0,
      scanPollTimer: null,
      scanCountdownTimer: null,
      scannedCode: "",
      scannedAsset: null,
      scanError: "",
      isPreparingRfidReplacement: false,
      isRfidReplacementScanning: false,
      rfidReplacementStatus: "idle",
      rfidReplacementSeconds: 25,
      rfidReplacementBaselineId: 0,
      rfidReplacementPollTimer: null,
      rfidReplacementCountdownTimer: null,
      rfidReplacementError: ""
    }
  },

  computed: {
    filteredAssets() {
      const text = this.search.toLowerCase().trim()

      return this.assets.filter(asset => {
        const matchesSearch =
          asset.id.toString().toLowerCase().includes(text) ||
          asset.name.toLowerCase().includes(text) ||
          asset.serial.toLowerCase().includes(text) ||
          asset.rfid.toLowerCase().includes(text) ||
          asset.location.toLowerCase().includes(text) ||
          asset.responsible.toLowerCase().includes(text)

return matchesSearch
      })
    },

    totalPages() {
      return Math.ceil(this.filteredAssets.length / this.perPage) || 1
    },

    paginatedAssets() {
      const start = (this.currentPage - 1) * this.perPage
      return this.filteredAssets.slice(start, start + this.perPage)
    }
  },

  mounted() {
    this.loadAssets()
    this.loadResponsables()
  },

  beforeUnmount() {
    this.stopRfidScan()
    this.stopRfidReplacementScan()
  },

  methods: {
    async loadAssets() {
      try {
        this.isLoading = true

        const response = await api.get("/activo")

        this.assets = response.data.map(item => {
          const responsableNombre = item.responsable
            ? `${item.responsable.nombre ?? ""} ${item.responsable.apellido ?? ""}`.trim()
            : "Sin asignar"

          return {
            id: item.id_activo ?? item.id ?? "N/A",
            name: item.nombre_activo ?? "Sin nombre",
            serial: item.serie ?? "Sin serie",
            rfid: item.etiqueta?.codigo ?? item.etiqueta?.codigo_rfid ?? "Sin RFID",
            location: item.ubicacion?.laboratorio?.nombre_laboratorio ??
              item.ubicacion?.nombre ??
              item.ubicacion?.id_laboratorio ??
              "Sin ubicación",
            responsible: responsableNombre || "Sin asignar",
            status: responsableNombre ? "Asignado" : "Sin asignar",
            purchaseValue: item.valor_compra ?? "0.00",
            currentValue: item.valor_actual ?? "0.00",
            purchaseDate: item.fecha_compra ?? "Sin fecha",
            raw: item
          }
        })
      } catch (error) {
        console.error(error)

        Swal.fire({
          icon: "error",
          title: "Error al cargar activos",
          text: error.response?.data?.message || "No fue posible obtener los activos registrados."
        })
      } finally {
        this.isLoading = false
      }
    },

    async loadResponsables() {
      try {
        const response = await api.get("/activo-catalogos")

        this.responsables = response.data.responsables.map(item => ({
          id: item.id,
          nombre_responsable: `${item.nombre ?? ""} ${item.apellido ?? ""}`.trim(),
          codigo_empleado: item.codigo_empleado ?? "Sin código"
        }))

      } catch (error) {
        console.error(error)

        Swal.fire({
          icon: "error",
          title: "Error al cargar responsables",
          text: "No fue posible obtener el listado de responsables."
        })
      }
    },

    normalizeAlerts(payload) {
      if (Array.isArray(payload)) return payload
      if (Array.isArray(payload?.data)) return payload.data
      if (Array.isArray(payload?.alertas)) return payload.alertas
      return []
    },

    getAlertId(alert) {
      return Number(alert?.id_alerta ?? alert?.id ?? 0)
    },

    async getLatestRfidAlert() {
      const response = await api.get("/alertas")
      const alerts = this.normalizeAlerts(response.data)
        .filter(alert => Boolean(alert?.codigo_rfid))
        .sort((a, b) => this.getAlertId(b) - this.getAlertId(a))
      return alerts[0] ?? null
    },

    async startRfidScan() {
      try {
        this.stopRfidScan()
        this.isPreparingScan = true
        this.showScanModal = true
        this.scanStatus = "scanning"
        this.scanSeconds = 25
        this.scannedCode = ""
        this.scannedAsset = null
        this.scanError = ""

        const latestAlert = await this.getLatestRfidAlert()
        this.scanBaselineId = latestAlert ? this.getAlertId(latestAlert) : 0

        this.startScanCountdown()
        this.scanPollTimer = window.setInterval(this.checkNewRfidReading, 1500)
      } catch (error) {
        console.error(error)
        this.scanStatus = "error"
        this.scanError = error.response?.data?.message || "No fue posible preparar el lector RFID. Verifica la conexión con el servidor."
      } finally {
        this.isPreparingScan = false
      }
    },

    startScanCountdown() {
      if (this.scanCountdownTimer) window.clearInterval(this.scanCountdownTimer)
      this.scanCountdownTimer = window.setInterval(() => {
        if (this.scanStatus !== "scanning") {
          window.clearInterval(this.scanCountdownTimer)
          this.scanCountdownTimer = null
          return
        }
        this.scanSeconds -= 1
        if (this.scanSeconds <= 0) {
          this.stopRfidScan()
          this.scanStatus = "timeout"
        }
      }, 1000)
    },

    async checkNewRfidReading() {
      if (this.scanStatus !== "scanning") return
      try {
        const latestAlert = await this.getLatestRfidAlert()
        if (!latestAlert) return
        const latestId = this.getAlertId(latestAlert)
        if (latestId <= this.scanBaselineId) return
        this.scanBaselineId = latestId
        this.scannedCode = String(latestAlert.codigo_rfid ?? "").trim()
        this.stopRfidScan()
        await this.findAssetByRfid(this.scannedCode)
      } catch (error) {
        console.error("Error consultando lectura RFID:", error)
      }
    },

    async findAssetByRfid(codigoRfid) {
      try {
        const response = await api.get(`/movimientos/activo-rfid/${encodeURIComponent(codigoRfid)}`)
        this.scannedAsset = response.data.activo
        this.scanStatus = "found"
      } catch (error) {
        if (error.response?.status === 404) {
          this.scannedAsset = null
          this.scanStatus = "not-found"
          return
        }
        console.error(error)
        this.scanStatus = "error"
        this.scanError = error.response?.data?.message || "No fue posible comprobar la etiqueta RFID detectada."
      }
    },

    restartRfidScan() {
      this.startRfidScan()
    },

    stopRfidScan() {
      if (this.scanPollTimer) {
        window.clearInterval(this.scanPollTimer)
        this.scanPollTimer = null
      }
      if (this.scanCountdownTimer) {
        window.clearInterval(this.scanCountdownTimer)
        this.scanCountdownTimer = null
      }
    },

    closeScanModal() {
      this.stopRfidScan()
      this.showScanModal = false
      this.scanStatus = "idle"
      this.scanSeconds = 25
      this.scannedCode = ""
      this.scannedAsset = null
      this.scanError = ""
    },

    showScannedAsset() {
      if (!this.scannedCode) return
      this.search = this.scannedCode
      this.currentPage = 1
      this.closeScanModal()
      Swal.fire({
        icon: "success",
        title: "Activo localizado",
        text: "La tabla fue filtrada con la etiqueta RFID detectada.",
        timer: 1800,
        showConfirmButton: false
      })
    },

    openDetail(asset) {
      this.selectedAsset = asset
    },

    openAssignModal(asset) {
      this.assetToAssign = asset
      this.assignForm.id_responsable = asset.raw?.id_responsable ?? ""
      this.showAssignModal = true
    },

    closeAssignModal() {
      this.showAssignModal = false
      this.assetToAssign = null
      this.assignForm.id_responsable = ""
    },

    openRfidModal(asset) {
      this.stopRfidReplacementScan()
      this.assetToChangeRfid = asset
      this.rfidForm.codigo_rfid = ""
      this.rfidForm.motivo = ""
      this.rfidReplacementStatus = "idle"
      this.rfidReplacementSeconds = 25
      this.rfidReplacementError = ""
      this.showRfidModal = true
    },

    closeRfidModal() {
      this.stopRfidReplacementScan()
      this.showRfidModal = false
      this.assetToChangeRfid = null
      this.rfidForm.codigo_rfid = ""
      this.rfidForm.motivo = ""
      this.rfidReplacementStatus = "idle"
      this.rfidReplacementSeconds = 25
      this.rfidReplacementError = ""
    },

    async startRfidReplacementScan() {
      try {
        this.stopRfidReplacementScan()
        this.isPreparingRfidReplacement = true
        this.rfidReplacementStatus = "scanning"
        this.rfidReplacementSeconds = 25
        this.rfidReplacementError = ""
        this.rfidForm.codigo_rfid = ""
        const latestAlert = await this.getLatestRfidAlert()
        this.rfidReplacementBaselineId = latestAlert ? this.getAlertId(latestAlert) : 0
        this.isRfidReplacementScanning = true
        this.startRfidReplacementCountdown()
        this.rfidReplacementPollTimer = window.setInterval(this.checkNewRfidReplacementReading, 1500)
      } catch (error) {
        console.error(error)
        this.rfidReplacementStatus = "error"
        this.rfidReplacementError = error.response?.data?.message || "No fue posible preparar el lector RFID."
      } finally {
        this.isPreparingRfidReplacement = false
      }
    },

    startRfidReplacementCountdown() {
      if (this.rfidReplacementCountdownTimer) window.clearInterval(this.rfidReplacementCountdownTimer)
      this.rfidReplacementCountdownTimer = window.setInterval(() => {
        if (!this.isRfidReplacementScanning) {
          window.clearInterval(this.rfidReplacementCountdownTimer)
          this.rfidReplacementCountdownTimer = null
          return
        }
        this.rfidReplacementSeconds -= 1
        if (this.rfidReplacementSeconds <= 0) {
          this.stopRfidReplacementScan()
          this.rfidReplacementStatus = "timeout"
        }
      }, 1000)
    },

    async checkNewRfidReplacementReading() {
      if (!this.isRfidReplacementScanning) return
      try {
        const latestAlert = await this.getLatestRfidAlert()
        if (!latestAlert) return
        const latestId = this.getAlertId(latestAlert)
        if (latestId <= this.rfidReplacementBaselineId) return
        this.rfidReplacementBaselineId = latestId
        const detectedCode = String(latestAlert.codigo_rfid ?? "").trim().toUpperCase()
        if (!detectedCode) return
        this.stopRfidReplacementScan()
        const currentCode = String(this.assetToChangeRfid?.rfid ?? "").trim().toUpperCase()
        if (detectedCode === currentCode) {
          this.rfidReplacementStatus = "same"
          return
        }
        this.rfidForm.codigo_rfid = detectedCode
        this.rfidReplacementStatus = "detected"
      } catch (error) {
        console.error(error)
        this.stopRfidReplacementScan()
        this.rfidReplacementStatus = "error"
        this.rfidReplacementError = error.response?.data?.message || "No fue posible obtener la nueva lectura RFID."
      }
    },

    stopRfidReplacementScan() {
      if (this.rfidReplacementPollTimer) window.clearInterval(this.rfidReplacementPollTimer)
      if (this.rfidReplacementCountdownTimer) window.clearInterval(this.rfidReplacementCountdownTimer)
      this.rfidReplacementPollTimer = null
      this.rfidReplacementCountdownTimer = null
      this.isRfidReplacementScanning = false
      this.isPreparingRfidReplacement = false
    },

    async assignResponsible() {
      if (!this.assignForm.id_responsable) {
        Swal.fire({
          icon: "warning",
          title: "Responsable requerido",
          text: "Seleccioná un responsable para asignar el activo."
        })
        return
      }

      if (!this.assetToAssign?.raw) {
        Swal.fire({
          icon: "error",
          title: "Activo no válido",
          text: "No se encontró la información del activo seleccionado."
        })
        return
      }

      const asset = this.assetToAssign.raw

      if (!asset.id_ubicacion || !asset.id_etiqueta) {
        Swal.fire({
          icon: "error",
          title: "Faltan datos del activo",
          text: "El activo necesita ubicación y etiqueta RFID para poder actualizar la asignación."
        })
        return
      }

      try {
        this.isAssigning = true

        await api.put(`/activo/${asset.id_activo}/asignaciones`, {
          id_responsable: this.assignForm.id_responsable,
          id_ubicacion: asset.id_ubicacion,
          id_etiqueta: asset.id_etiqueta
        })

        Swal.fire({
          icon: "success",
          title: "Responsable asignado",
          text: "El responsable fue asignado correctamente al activo."
        })

        this.closeAssignModal()
        await this.loadAssets()
      } catch (error) {
        console.error(error)

        Swal.fire({
          icon: "error",
          title: "Error al asignar",
          text: error.response?.data?.message || "No fue posible asignar el responsable."
        })
      } finally {
        this.isAssigning = false
      }
    },

    async changeRfidTag() {
      this.stopRfidReplacementScan()
      const codigo = this.rfidForm.codigo_rfid.trim()
      const motivo = this.rfidForm.motivo.trim()
      if (!codigo) {
        Swal.fire({
          icon: "warning",
          title: "Etiqueta requerida",
          text: "Ingresá el código de la nueva etiqueta RFID."
        })
        return
      }
      if (!motivo) {
        Swal.fire({
          icon: "warning",
          title: "Motivo de cambio es requerido",
          text: "Ingresá el motivo del cambio."
        })
        return
      }

      if (!this.assetToChangeRfid?.raw?.id_activo) {
        Swal.fire({
          icon: "error",
          title: "Activo no válido",
          text: "No se encontró la información del activo seleccionado."
        })
        return
      }

      try {
        this.isChangingRfid = true

        await api.put(`/activo/${this.assetToChangeRfid.raw.id_activo}/etiqueta-rfid`, {
          codigo_rfid: codigo,
          motivo : motivo
        })

        Swal.fire({
          icon: "success",
          title: "Etiqueta actualizada",
          text: "La etiqueta RFID fue actualizada correctamente."
        })

        this.closeRfidModal()
        await this.loadAssets()
      } catch (error) {
        console.error(error)

        Swal.fire({
          icon: "error",
          title: "Error al actualizar etiqueta",
          text: error.response?.data?.message || "No fue posible actualizar la etiqueta RFID."
        })
      } finally {
        this.isChangingRfid = false
      }
    },

  },

  watch: {
    search() {
      this.currentPage = 1
    },
  }
}
</script>

<style scoped>
.assets-scan-toolbar {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 20px;
  align-items: center;
}

.scan-rfid-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-width: 245px;
  min-height: 58px;
  padding: 0 24px;
  border: 1px solid #cfd5df;
  border-radius: 15px;
  background: #ffffff;
  color: #10192e;
  font: inherit;
  font-weight: 800;
  cursor: pointer;
  transition: 0.2s ease;
}

.scan-rfid-action:hover:not(:disabled) {
  transform: translateY(-2px);
  border-color: #e5252a;
  color: #d71920;
  box-shadow: 0 12px 25px rgba(229, 37, 42, 0.14);
}

.scan-rfid-action:disabled { cursor: not-allowed; opacity: 0.6; }
.rfid-scan-backdrop { z-index: 1200; }

.rfid-scan-modal {
  position: relative;
  width: min(680px, calc(100vw - 32px));
  padding: 48px 52px 42px;
  border-radius: 30px;
  background: #ffffff;
  text-align: center;
  box-shadow: 0 30px 90px rgba(14, 28, 52, 0.28);
}

.rfid-scan-close {
  position: absolute;
  top: 20px;
  right: 20px;
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  border: 0;
  border-radius: 14px;
  background: #f3f5f8;
  color: #516078;
  cursor: pointer;
}

.rfid-scan-visual {
  position: relative;
  display: grid;
  place-items: center;
  width: 112px;
  height: 112px;
  margin: 0 auto 28px;
  border-radius: 30px;
  color: #ffffff;
}

.rfid-scan-visual::before,
.rfid-scan-visual::after {
  content: "";
  position: absolute;
  border: 1px solid currentColor;
  border-radius: 50%;
  opacity: 0.16;
}

.rfid-scan-visual::before { width: 145px; height: 145px; }
.rfid-scan-visual::after { width: 190px; height: 190px; }
.rfid-scan-visual.scanning { background: #e8202a; box-shadow: 0 16px 35px rgba(232, 32, 42, 0.28); animation: rfidPulse 1.4s ease-in-out infinite; }
.rfid-scan-visual.success { background: #198754; box-shadow: 0 16px 35px rgba(25, 135, 84, 0.22); }
.rfid-scan-visual.warning { background: #be4c19; box-shadow: 0 16px 35px rgba(190, 76, 25, 0.22); }
.rfid-scan-visual.timeout { background: #69758a; box-shadow: 0 16px 35px rgba(105, 117, 138, 0.22); }

.rfid-scan-kicker { display: block; margin-bottom: 10px; color: #ba1f27; font-size: 0.82rem; font-weight: 900; letter-spacing: 0.14em; }
.success-text { color: #198754; }
.warning-text { color: #b54a1c; }
.timeout-text { color: #647086; }
.rfid-scan-modal h2 { margin: 0 0 12px; color: #12203b; font-size: clamp(1.8rem, 4vw, 2.4rem); line-height: 1.15; }
.rfid-scan-modal > p { max-width: 540px; margin: 0 auto; color: #69778d; font-size: 1rem; line-height: 1.6; }

.rfid-countdown { display: flex; align-items: center; justify-content: space-between; width: min(340px, 100%); margin: 32px auto 28px; padding: 16px 20px; border: 1px solid #f0d0c8; border-radius: 15px; background: #fff8f5; color: #765f59; }
.rfid-countdown strong { color: #c01f27; font-size: 1.35rem; }

.scanned-asset-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 28px 0; }
.scanned-asset-summary div { display: flex; flex-direction: column; gap: 7px; padding: 15px; border: 1px solid #e1e6ed; border-radius: 14px; background: #f8fafc; text-align: left; }
.scanned-asset-summary span { color: #748197; font-size: 0.76rem; font-weight: 700; }
.scanned-asset-summary strong { overflow-wrap: anywhere; color: #17233d; font-size: 0.9rem; }

.rfid-modal-action { margin-top: 30px; }
.rfid-modal-actions { display: flex; justify-content: center; gap: 14px; margin-top: 30px; }
.rfid-modal-actions button,
.rfid-modal-action { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 50px; padding: 0 22px; border-radius: 13px; }

.rfid-replacement-field { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; }
.rfid-replacement-field input[readonly] { cursor: default; background: #f8fafc; }
.scan-new-rfid-button { display: inline-flex; min-height: 50px; align-items: center; justify-content: center; gap: 9px; padding: 0 18px; border: 1px solid #cfd5df; border-radius: 13px; background: #fff; color: #17233d; font: inherit; font-weight: 850; white-space: nowrap; cursor: pointer; transition: .2s ease; }
.scan-new-rfid-button:hover:not(:disabled) { transform: translateY(-2px); border-color: #e5252a; color: #d71920; box-shadow: 0 10px 22px rgba(229,37,42,.12); }
.scan-new-rfid-button:disabled { cursor: not-allowed; opacity: .65; }
.rfid-replacement-feedback { display: flex; align-items: flex-start; gap: 10px; margin: 12px 0 18px; padding: 13px 14px; border-radius: 13px; }
.rfid-replacement-feedback strong, .rfid-replacement-feedback span { display: block; }
.rfid-replacement-feedback span { margin-top: 4px; font-size: .82rem; line-height: 1.45; }
.rfid-replacement-feedback.scanning { border: 1px solid #f1c9ca; background: #fff6f6; color: #b91c25; }
.rfid-replacement-feedback.detected { border: 1px solid #b8e7ce; background: #effbf4; color: #168259; }
.rfid-replacement-feedback.warning { border: 1px solid #f1d0b9; background: #fff8f1; color: #a44718; }

@keyframes rfidPulse {
  0%, 100% { transform: scale(0.96); }
  50% { transform: scale(1.04); }
}

@media (max-width: 780px) {
  .assets-scan-toolbar { grid-template-columns: 1fr; }
  .scan-rfid-action { width: 100%; }
  .rfid-scan-modal { padding: 42px 22px 28px; }
  .scanned-asset-summary { grid-template-columns: 1fr; }
  .rfid-modal-actions { flex-direction: column; }
  .rfid-modal-actions button,
  .rfid-modal-action { width: 100%; }
  .rfid-replacement-field { grid-template-columns: 1fr; }
  .scan-new-rfid-button { width: 100%; }
}
</style>