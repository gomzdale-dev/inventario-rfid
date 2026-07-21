<template>
  <section class="inventory-page">
    <header class="inventory-header">
      <div>
        <h1>Gestión de Inventario</h1>
        <p>
          Verificación física de activos por edificio y salón mediante lecturas RFID
          en tiempo real.
        </p>
      </div>

      <div v-if="session.active" class="session-chip">
        <span class="live-dot"></span>
        <div>
          <strong>Inventario en curso</strong>
          <small>{{ selectedLocationLabel }}</small>
        </div>
      </div>
    </header>

    <section class="hero-actions">
      <button
        type="button"
        class="rfid-button"
        :disabled="loading.catalogs || loading.assets"
        @click="openLocationModal"
      >
        <RadioTower size="25" />
        {{ session.active ? "Cambiar ubicación" : "Realizar Inventario con RFID" }}
      </button>

      <div v-if="session.active" class="metrics">
        <article>
          <span>Total esperado</span>
          <strong>{{ assets.length }}</strong>
        </article>
        <article>
          <span>Encontrados</span>
          <strong>{{ foundCount }}</strong>
        </article>
        <article>
          <span>Pendientes</span>
          <strong>{{ pendingCount }}</strong>
        </article>
        <article>
          <span>Avance</span>
          <strong>{{ progress }}%</strong>
        </article>
      </div>
    </section>

    <section class="toolbar">
      <div class="search-box">
        <Search size="22" />
        <input
          v-model="search"
          type="text"
          :disabled="!session.active"
          placeholder="Buscar por RFID, descripción, ubicación u observación..."
        />
      </div>

      <button
        type="button"
        class="save-button"
        :disabled="!session.active || loading.save || assets.length === 0"
        @click="saveInventory"
      >
        <LoaderCircle v-if="loading.save" class="spin" size="21" />
        <Save v-else size="21" />
        {{ loading.save ? "Guardando..." : "Guardar inventario" }}
      </button>
    </section>

    <section class="inventory-card">
      <div v-if="!session.active && !loading.assets" class="empty-state">
        <div class="empty-icon">
          <ClipboardCheck size="46" />
        </div>
        <h2>Inicia una jornada de inventario</h2>
        <p>
          Selecciona un edificio y un salón. El sistema cargará únicamente los activos
          asignados a esa ubicación.
        </p>
      </div>

      <div v-else-if="loading.assets" class="loading-state">
        <LoaderCircle class="spin" size="30" />
        Cargando activos...
      </div>

      <template v-else>
        <div class="progress-area">
          <div>
            <strong>Progreso del inventario</strong>
            <span>{{ foundCount }} de {{ assets.length }} activos detectados</span>
          </div>
          <div class="progress-track">
            <div class="progress-bar" :style="{ width: `${progress}%` }"></div>
          </div>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>RFID</th>
                <th>Descripción</th>
                <th>Ubicación</th>
                <th>Observaciones</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="asset in paginatedAssets"
                :key="asset.id"
                :class="{ found: asset.found }"
              >
                <td>
                  <div class="rfid-cell">
                    <span :class="['rfid-status', asset.found ? 'ok' : 'pending']">
                      <CheckCircle2 v-if="asset.found" size="17" />
                      <RadioTower v-else size="17" />
                    </span>
                    <div>
                      <strong>{{ asset.rfid }}</strong>
                      <small>{{ asset.found ? "Detectado" : "Pendiente" }}</small>
                    </div>
                  </div>
                </td>

                <td>
                  <strong class="asset-name">{{ asset.name }}</strong>
                </td>

                <td>
                  <div class="location-cell">
                    <MapPin size="18" />
                    <span>{{ asset.location }}</span>
                  </div>
                </td>

                <td>
                  <span v-if="asset.observation" class="observation">
                    {{ asset.observation }}
                  </span>
                  <span v-else class="muted">Sin observaciones</span>
                </td>

                <td>
                  <button
                    type="button"
                    class="icon-action"
                    :title="asset.observation ? 'Editar observación' : 'Agregar observación'"
                    @click="openObservationModal(asset)"
                  >
                    <SquarePen v-if="asset.observation" size="20" />
                    <Plus v-else size="21" />
                  </button>
                </td>
              </tr>

              <tr v-if="filteredAssets.length === 0">
                <td colspan="5" class="no-results">
                  No se encontraron activos con los criterios indicados.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <footer class="table-footer">
          <p>Mostrando {{ paginatedAssets.length }} de {{ filteredAssets.length }} activos</p>

          <div class="pagination">
            <button
              type="button"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              Anterior
            </button>

            <button type="button" class="active-page">
              {{ currentPage }}
            </button>

            <button
              type="button"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Siguiente
            </button>
          </div>
        </footer>
      </template>
    </section>

    <div v-if="modals.location" class="modal-backdrop" @click="closeLocationModal">
      <section class="modal" @click.stop>
        <header class="modal-header">
          <div class="modal-icon">
            <Building2 size="25" />
          </div>
          <div>
            <span>Nueva jornada RFID</span>
            <h2>Seleccionar ubicación</h2>
          </div>
          <button type="button" class="modal-close" @click="closeLocationModal">
            <X size="22" />
          </button>
        </header>

        <p class="modal-description">
          Selecciona el edificio y el salón cuyos activos serán verificados.
        </p>

        <div class="form-grid">
          <label>
            <span>Edificio *</span>
            <div class="select-box">
              <Building2 size="19" />
              <select v-model="form.id_edificio" @change="handleBuildingChange">
                <option value="">Seleccione un edificio</option>
                <option
                  v-for="building in buildings"
                  :key="building.id_edificio"
                  :value="building.id_edificio"
                >
                  {{ building.nombre_edificio }}
                </option>
              </select>
            </div>
          </label>

          <label>
            <span>Salón o laboratorio *</span>
            <div class="select-box">
              <DoorOpen size="19" />
              <select
                v-model="form.id_laboratorio"
                :disabled="!form.id_edificio"
              >
                <option value="">Seleccione un salón</option>
                <option
                  v-for="room in filteredRooms"
                  :key="room.id_laboratorio"
                  :value="room.id_laboratorio"
                >
                  {{ room.nombre_laboratorio }}
                </option>
              </select>
            </div>
          </label>
        </div>

        <p v-if="modalError" class="modal-error">{{ modalError }}</p>

        <footer class="modal-actions">
          <button type="button" class="secondary" @click="closeLocationModal">
            Cancelar
          </button>
          <button type="button" class="primary" @click="confirmInventoryStart">
            <RadioTower size="20" />
            Iniciar inventario
          </button>
        </footer>
      </section>
    </div>

    <div v-if="modals.observation" class="modal-backdrop" @click="closeObservationModal">
      <section class="modal" @click.stop>
        <header class="modal-header">
          <div class="modal-icon">
            <SquarePen size="24" />
          </div>
          <div>
            <span>Registro de novedad</span>
            <h2>Observación del activo</h2>
          </div>
          <button type="button" class="modal-close" @click="closeObservationModal">
            <X size="22" />
          </button>
        </header>

        <div v-if="selectedAsset" class="asset-summary">
          <strong>{{ selectedAsset.name }}</strong>
          <span>RFID: {{ selectedAsset.rfid }}</span>
          <small>{{ selectedAsset.location }}</small>
        </div>

        <label class="observation-field">
          <span>Observación</span>
          <textarea
            v-model.trim="observationText"
            rows="5"
            maxlength="500"
            placeholder="Ejemplo: equipo operativo, pantalla rayada, cable faltante..."
          ></textarea>
          <small>{{ observationText.length }}/500 caracteres</small>
        </label>

        <footer class="modal-actions">
          <button type="button" class="secondary" @click="closeObservationModal">
            Cancelar
          </button>
          <button type="button" class="primary" @click="saveObservation">
            <Save size="20" />
            Guardar observación
          </button>
        </footer>
      </section>
    </div>

    <div v-if="modals.scanner" class="scanner-overlay">
      <div class="scanner-box">
        <div class="scanner-rings">
          <span></span>
          <span></span>
          <span></span>
          <div class="scanner-center">
            <RadioTower size="42" />
          </div>
        </div>
        <h2>Preparando inventario RFID</h2>
        <p>{{ scannerMessage }}</p>
      </div>
    </div>

    <div v-if="modals.success" class="modal-backdrop">
      <section class="modal success-modal">
        <div class="success-icon">
          <CheckCircle2 size="48" />
        </div>
        <h2>Inventario guardado correctamente</h2>
        <p>
          Se registraron {{ assets.length }} activos: {{ foundCount }} encontrados
          y {{ pendingCount }} pendientes.
        </p>
        <button type="button" class="primary" @click="modals.success = false">
          Entendido
        </button>
      </section>
    </div>

    <div v-if="toast.visible" :class="['toast', toast.type]">
      <CheckCircle2 v-if="toast.type === 'success'" size="22" />
      <TriangleAlert v-else-if="toast.type === 'warning'" size="22" />
      <Info v-else size="22" />
      <span>{{ toast.message }}</span>
    </div>
  </section>
</template>

<script>
import {
  Search,
  RadioTower,
  Save,
  Plus,
  X,
  Building2,
  DoorOpen,
  CheckCircle2,
  LoaderCircle,
  ClipboardCheck,
  MapPin,
  SquarePen,
  TriangleAlert,
  Info
} from "lucide-vue-next"

import api from "../services/api"

export default {
  name: "Inventory",

  components: {
    Search,
    RadioTower,
    Save,
    Plus,
    X,
    Building2,
    DoorOpen,
    CheckCircle2,
    LoaderCircle,
    ClipboardCheck,
    MapPin,
    SquarePen,
    TriangleAlert,
    Info
  },

  data() {
    return {
      search: "",
      currentPage: 1,
      perPage: 10,

      buildings: [],
      rooms: [],
      assets: [],

      form: {
        id_edificio: "",
        id_laboratorio: ""
      },

      session: {
        active: false,
        buildingName: "",
        roomName: ""
      },

      loading: {
        catalogs: false,
        assets: false,
        save: false
      },

      modals: {
        location: false,
        observation: false,
        scanner: false,
        success: false
      },

      modalError: "",
      selectedAsset: null,
      observationText: "",
      scannerMessage: "Cargando los activos del salón seleccionado...",

      pollingTimer: null,
      lastAlertId: 0,

      toast: {
        visible: false,
        message: "",
        type: "info",
        timer: null
      }
    }
  },

  computed: {
    filteredRooms() {
      if (!this.form.id_edificio) return []

      return this.rooms.filter(
        room => String(room.id_edificio) === String(this.form.id_edificio)
      )
    },

    selectedLocationLabel() {
      return `${this.session.buildingName} - ${this.session.roomName}`
    },

    filteredAssets() {
      const query = this.normalize(this.search)

      if (!query) return this.assets

      return this.assets.filter(asset => {
        return this.normalize(
          `${asset.rfid} ${asset.name} ${asset.location} ${asset.observation}`
        ).includes(query)
      })
    },

    totalPages() {
      return Math.max(1, Math.ceil(this.filteredAssets.length / this.perPage))
    },

    paginatedAssets() {
      const start = (this.currentPage - 1) * this.perPage
      return this.filteredAssets.slice(start, start + this.perPage)
    },

    foundCount() {
      return this.assets.filter(asset => asset.found).length
    },

    pendingCount() {
      return Math.max(0, this.assets.length - this.foundCount)
    },

    progress() {
      if (this.assets.length === 0) return 0
      return Math.round((this.foundCount / this.assets.length) * 100)
    }
  },

  watch: {
    search() {
      this.currentPage = 1
    }
  },

  async mounted() {
    await this.loadCatalogs()
  },

  beforeUnmount() {
    this.stopPolling()

    if (this.toast.timer) {
      clearTimeout(this.toast.timer)
    }
  },

  methods: {
    normalize(value) {
      return String(value ?? "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
    },

    async loadCatalogs() {
      try {
        this.loading.catalogs = true
        const response = await api.get("/inventario/catalogos")

        this.buildings = response.data?.edificios ?? []
        this.rooms = response.data?.laboratorios ?? []
      } catch (error) {
        console.error(error)
        this.showToast("No fue posible cargar edificios y salones.", "warning")
      } finally {
        this.loading.catalogs = false
      }
    },

    openLocationModal() {
      this.modalError = ""
      this.modals.location = true
      this.playTone([520])
    },

    closeLocationModal() {
      this.modals.location = false
      this.modalError = ""
    },

    handleBuildingChange() {
      this.form.id_laboratorio = ""
      this.modalError = ""
    },

    async confirmInventoryStart() {
      if (!this.form.id_edificio || !this.form.id_laboratorio) {
        this.modalError = "Debes seleccionar un edificio y un salón."
        return
      }

      const building = this.buildings.find(
        item => String(item.id_edificio) === String(this.form.id_edificio)
      )

      const room = this.rooms.find(
        item => String(item.id_laboratorio) === String(this.form.id_laboratorio)
      )

      this.session.buildingName = building?.nombre_edificio ?? "Edificio"
      this.session.roomName = room?.nombre_laboratorio ?? "Salón"

      this.closeLocationModal()
      this.modals.scanner = true
      this.scannerMessage = `Cargando activos de ${this.selectedLocationLabel}...`
      this.playTone([420, 560, 720])

      try {
        await this.captureAlertBaseline()
        await this.loadAssetsByLocation()
        this.session.active = true
        this.startPolling()
        await this.wait(900)

        this.showToast(
          `Inventario iniciado en ${this.selectedLocationLabel}.`,
          "success"
        )
      } catch (error) {
        console.error(error)
        this.session.active = false
        this.showToast(
          error.response?.data?.message || "No fue posible iniciar el inventario.",
          "warning"
        )
      } finally {
        this.modals.scanner = false
      }
    },

    async loadAssetsByLocation() {
      try {
        this.loading.assets = true

        const response = await api.get("/inventario/activos-ubicacion", {
          params: {
            id_edificio: this.form.id_edificio,
            id_laboratorio: this.form.id_laboratorio
          }
        })

        const data = response.data?.activos ?? response.data ?? []

        this.assets = data.map(item => ({
          id: item.id_activo,
          name: item.nombre_activo ?? "Activo sin descripción",
          location:
            item.ubicacion_formateada ??
            `${this.session.buildingName} - ${this.session.roomName}`,
          rfid:
            item.etiqueta?.codigo ??
            item.etiqueta?.codigo_rfid ??
            item.codigo_rfid ??
            "SIN RFID",
          observation: "",
          found: false,
          scannedAt: null,
          original: item
        }))

        this.currentPage = 1
        this.search = ""
      } finally {
        this.loading.assets = false
      }
    },

    async captureAlertBaseline() {
      try {
        const response = await api.get("/alertas")
        const alerts = response.data ?? []

        this.lastAlertId = alerts.reduce(
          (max, alert) => Math.max(max, Number(alert.id ?? 0)),
          0
        )
      } catch (error) {
        console.error(error)
        this.lastAlertId = 0
      }
    },

    startPolling() {
      this.stopPolling()

      this.pollingTimer = setInterval(() => {
        this.checkNewRfidAlerts()
      }, 2000)
    },

    stopPolling() {
      if (this.pollingTimer) {
        clearInterval(this.pollingTimer)
        this.pollingTimer = null
      }
    },

    async checkNewRfidAlerts() {
      if (!this.session.active) return

      try {
        const response = await api.get("/alertas")
        const alerts = response.data ?? []

        const newAlerts = alerts
          .filter(alert => Number(alert.id ?? 0) > this.lastAlertId)
          .sort((a, b) => Number(a.id ?? 0) - Number(b.id ?? 0))

        for (const alert of newAlerts) {
          const code = String(alert.codigo_rfid ?? "").trim().toUpperCase()

          if (code) {
            this.processRfid(code, alert.fecha_alerta || alert.created_at)
          }

          this.lastAlertId = Math.max(this.lastAlertId, Number(alert.id ?? 0))
        }
      } catch (error) {
        console.error(error)
      }
    },

    processRfid(code, detectedAt) {
      const asset = this.assets.find(
        item => String(item.rfid ?? "").trim().toUpperCase() === code
      )

      if (!asset) {
        this.playTone([320, 240])
        this.showToast(
          `La etiqueta ${code} no pertenece al salón seleccionado.`,
          "warning"
        )
        return
      }

      if (asset.found) {
        this.showToast(`La etiqueta ${code} ya había sido detectada.`, "info")
        return
      }

      asset.found = true
      asset.scannedAt = detectedAt || new Date().toISOString()

      this.playTone([660, 880])
      this.showToast(`${asset.name} detectado correctamente.`, "success")
    },

    openObservationModal(asset) {
      this.selectedAsset = asset
      this.observationText = asset.observation ?? ""
      this.modals.observation = true
      this.playTone([520])
    },

    closeObservationModal() {
      this.modals.observation = false
      this.selectedAsset = null
      this.observationText = ""
    },

    saveObservation() {
      if (!this.selectedAsset) return

      this.selectedAsset.observation = this.observationText
      this.closeObservationModal()
      this.playTone([660, 880])
      this.showToast("Observación guardada temporalmente.", "success")
    },

    async saveInventory() {
      if (!this.session.active || this.assets.length === 0) return

      try {
        this.loading.save = true

        const user = JSON.parse(localStorage.getItem("usuario") || "null")

        await api.post("/inventario", {
          fecha_inventario: new Date().toISOString(),
          id_usuario: user?.id_usuario ?? user?.id ?? null,
          id_laboratorio: this.form.id_laboratorio,
          detalles: this.assets.map(asset => ({
            id_activo: asset.id,
            cantidad: 1,
            observaciones: asset.observation || null,
            encontrado: asset.found,
            fecha_lectura: asset.scannedAt
          }))
        })

        this.stopPolling()
        this.session.active = false
        this.modals.success = true
        this.playTone([520, 680, 840, 1040])
      } catch (error) {
        console.error(error)

        const validationErrors = error.response?.data?.errors
        const firstError = validationErrors
          ? Object.values(validationErrors).flat()[0]
          : null

        this.showToast(
          firstError ||
            error.response?.data?.message ||
            "No fue posible guardar el inventario.",
          "warning"
        )
      } finally {
        this.loading.save = false
      }
    },

    showToast(message, type = "info") {
      if (this.toast.timer) {
        clearTimeout(this.toast.timer)
      }

      this.toast.visible = true
      this.toast.message = message
      this.toast.type = type

      this.toast.timer = setTimeout(() => {
        this.toast.visible = false
      }, 4200)
    },

    wait(ms) {
      return new Promise(resolve => setTimeout(resolve, ms))
    },

    playTone(frequencies) {
      try {
        const AudioContext = window.AudioContext || window.webkitAudioContext
        const context = new AudioContext()

        frequencies.forEach((frequency, index) => {
          const oscillator = context.createOscillator()
          const gain = context.createGain()
          const start = context.currentTime + index * 0.14

          oscillator.connect(gain)
          gain.connect(context.destination)

          oscillator.frequency.setValueAtTime(frequency, start)
          oscillator.type = "sine"

          gain.gain.setValueAtTime(0.08, start)
          gain.gain.exponentialRampToValueAtTime(0.001, start + 0.13)

          oscillator.start(start)
          oscillator.stop(start + 0.13)
        })
      } catch (error) {
        console.warn("El navegador bloqueó el sonido:", error)
      }
    }
  }
}
</script>

<style scoped>
.inventory-page {
  min-height: 100%;
  color: #10213e;
}

.inventory-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 30px;
}

.eyebrow {
  display: inline-block;
  margin-bottom: 8px;
  color: #b42318;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.inventory-header h1 {
  margin: 0;
  font-size: clamp(38px, 4vw, 56px);
  line-height: 1;
  letter-spacing: -0.04em;
}

.inventory-header p {
  max-width: 760px;
  margin: 14px 0 0;
  color: #50617a;
  font-size: 18px;
  line-height: 1.6;
}

.session-chip {
  min-width: 250px;
  padding: 15px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border: 1px solid #e7ded5;
  border-radius: 18px;
  box-shadow: 0 14px 30px rgba(78, 45, 22, 0.08);
}

.session-chip strong,
.session-chip small {
  display: block;
}

.session-chip small {
  margin-top: 4px;
  color: #667085;
}

.live-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #16a34a;
  box-shadow: 0 0 0 6px rgba(22, 163, 74, 0.14);
  animation: pulse 1.5s infinite;
}

.hero-actions {
  display: flex;
  gap: 18px;
  margin-bottom: 26px;
}

.rfid-button {
  min-height: 86px;
  padding: 0 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  border: none;
  border-radius: 18px;
  background: linear-gradient(135deg, #ef2929, #bd111c);
  color: white;
  font-size: 20px;
  font-weight: 900;
  cursor: pointer;
  box-shadow: 0 18px 34px rgba(207, 22, 32, 0.25);
}

.metrics {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(4, minmax(120px, 1fr));
  gap: 12px;
}

.metrics article {
  padding: 15px 18px;
  background: white;
  border: 1px solid #ece3da;
  border-radius: 18px;
}

.metrics span,
.metrics strong {
  display: block;
}

.metrics span {
  color: #68758a;
  font-size: 13px;
  font-weight: 700;
}

.metrics strong {
  margin-top: 7px;
  font-size: 27px;
}

.toolbar {
  padding: 24px;
  display: flex;
  gap: 18px;
  background: white;
  border-radius: 22px;
  box-shadow: 0 16px 34px rgba(68, 49, 34, 0.08);
  margin-bottom: 26px;
}

.search-box {
  flex: 1;
  height: 64px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 13px;
  border: 2px solid #d6dce5;
  border-radius: 16px;
  color: #7a8ba3;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 17px;
}

.save-button {
  height: 64px;
  padding: 0 26px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border: 2px solid #b51722;
  border-radius: 16px;
  color: #b51722;
  background: white;
  font-weight: 900;
  cursor: pointer;
}

.save-button:disabled,
.rfid-button:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.inventory-card {
  overflow: hidden;
  background: white;
  border-radius: 24px;
  box-shadow: 0 18px 42px rgba(68, 49, 34, 0.09);
}

.empty-state,
.loading-state {
  min-height: 330px;
  padding: 50px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #627087;
}

.empty-state h2 {
  margin: 17px 0 8px;
}

.empty-state p {
  max-width: 620px;
  margin: 0;
  line-height: 1.6;
}

.empty-icon {
  width: 86px;
  height: 86px;
  display: grid;
  place-items: center;
  border-radius: 25px;
  color: #c61c27;
  background: #fee9e9;
}

.loading-state {
  flex-direction: row;
  gap: 12px;
  font-weight: 800;
}

.progress-area {
  padding: 22px 28px;
  display: grid;
  grid-template-columns: auto minmax(220px, 1fr);
  align-items: center;
  gap: 26px;
  border-bottom: 1px solid #eceff3;
}

.progress-area strong,
.progress-area span {
  display: block;
}

.progress-area span {
  margin-top: 4px;
  color: #6b778c;
  font-size: 13px;
}

.progress-track {
  height: 12px;
  overflow: hidden;
  border-radius: 999px;
  background: #eef1f5;
}

.progress-bar {
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #db1f2a, #f05d43);
  transition: width 0.45s ease;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  min-width: 1000px;
  border-collapse: collapse;
}

th,
td {
  padding: 20px 24px;
  text-align: left;
  border-bottom: 1px solid #e8ecf1;
}

th {
  background: #f8fafc;
  font-size: 14px;
  font-weight: 900;
}

tbody tr {
  transition: background 0.2s ease;
}

tbody tr:hover {
  background: #fffafa;
}

tbody tr.found {
  background: #f2fcf5;
  box-shadow: inset 4px 0 #18a957;
}

.rfid-cell,
.location-cell {
  display: flex;
  align-items: center;
  gap: 11px;
}

.rfid-status {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: 12px;
}

.rfid-status.pending {
  color: #b67500;
  background: #fff4d6;
}

.rfid-status.ok {
  color: #138a47;
  background: #dff8e8;
}

.rfid-cell strong,
.rfid-cell small {
  display: block;
}

.rfid-cell strong {
  color: #173eae;
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
}

.rfid-cell small {
  margin-top: 4px;
  color: #77849a;
}

.asset-name {
  color: #17233c;
}

.muted {
  color: #98a2b3;
  font-style: italic;
}

.icon-action {
  width: 43px;
  height: 43px;
  display: grid;
  place-items: center;
  border: 1px solid #efb3b7;
  border-radius: 13px;
  color: #bc1722;
  background: #fff6f6;
  cursor: pointer;
}

.icon-action:hover {
  color: white;
  background: #bc1722;
}

.no-results {
  height: 130px;
  text-align: center;
  color: #7b8799;
}

.table-footer {
  padding: 20px 26px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.pagination {
  display: flex;
  gap: 8px;
}

.pagination button {
  min-height: 40px;
  padding: 0 15px;
  border: 1px solid #d7dce4;
  border-radius: 11px;
  background: white;
  font-weight: 800;
  cursor: pointer;
}

.pagination .active-page {
  color: white;
  background: #c51924;
}

.pagination button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.modal-backdrop,
.scanner-overlay {
  position: fixed;
  inset: 0;
  z-index: 120;
  padding: 22px;
  display: grid;
  place-items: center;
  background: rgba(8, 18, 36, 0.62);
  backdrop-filter: blur(7px);
}

.modal {
  width: min(620px, 100%);
  padding: 28px;
  border-radius: 24px;
  background: white;
  box-shadow: 0 28px 80px rgba(0, 0, 0, 0.25);
  animation: modalIn 0.22s ease;
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 14px;
}

.modal-icon {
  width: 50px;
  height: 50px;
  display: grid;
  place-items: center;
  border-radius: 16px;
  color: #bf1621;
  background: #fee8e8;
}

.modal-header span,
.modal-header h2 {
  display: block;
}

.modal-header span {
  color: #a31d24;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
}

.modal-header h2 {
  margin: 3px 0 0;
}

.modal-close {
  width: 42px;
  height: 42px;
  margin-left: auto;
  display: grid;
  place-items: center;
  border: none;
  border-radius: 12px;
  background: #f2f4f7;
  cursor: pointer;
}

.modal-description {
  margin: 22px 0;
  color: #5e6c81;
  line-height: 1.6;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 17px;
}

.form-grid label > span,
.observation-field > span {
  display: block;
  margin-bottom: 8px;
  font-weight: 800;
}

.select-box {
  height: 54px;
  padding: 0 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #d5dae2;
  border-radius: 14px;
}

.select-box select {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
}

.modal-error {
  margin-top: 16px;
  padding: 12px 14px;
  border-radius: 12px;
  color: #a3131c;
  background: #fff0f0;
}

.modal-actions {
  margin-top: 26px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.primary,
.secondary {
  min-height: 48px;
  padding: 0 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border-radius: 13px;
  font-weight: 900;
  cursor: pointer;
}

.primary {
  border: 1px solid #be1722;
  color: white;
  background: #be1722;
}

.secondary {
  border: 1px solid #d4dae3;
  background: white;
}

.asset-summary {
  margin: 22px 0;
  padding: 16px 18px;
  border-radius: 16px;
  background: #f8fafc;
}

.asset-summary strong,
.asset-summary span,
.asset-summary small {
  display: block;
}

.asset-summary span {
  margin-top: 6px;
  color: #2448b5;
}

.asset-summary small {
  margin-top: 5px;
  color: #6c788c;
}

.observation-field textarea {
  width: 100%;
  padding: 14px;
  border: 1px solid #d3d9e2;
  border-radius: 14px;
  box-sizing: border-box;
  resize: vertical;
}

.observation-field small {
  display: block;
  margin-top: 7px;
  text-align: right;
  color: #7b8799;
}

.scanner-overlay {
  z-index: 140;
  background: rgba(8, 18, 36, 0.82);
}

.scanner-box {
  text-align: center;
  color: white;
}

.scanner-rings {
  position: relative;
  width: 190px;
  height: 190px;
  margin: 0 auto 28px;
  display: grid;
  place-items: center;
}

.scanner-rings > span {
  position: absolute;
  inset: 0;
  border: 2px solid rgba(255, 255, 255, 0.55);
  border-radius: 50%;
  animation: scannerWave 2.1s infinite;
}

.scanner-rings > span:nth-child(2) {
  animation-delay: 0.55s;
}

.scanner-rings > span:nth-child(3) {
  animation-delay: 1.1s;
}

.scanner-center {
  width: 92px;
  height: 92px;
  display: grid;
  place-items: center;
  border-radius: 28px;
  background: linear-gradient(145deg, #f32b35, #b9101b);
  box-shadow: 0 0 45px rgba(244, 47, 59, 0.55);
}

.success-modal {
  text-align: center;
}

.success-icon {
  width: 88px;
  height: 88px;
  margin: 0 auto 18px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  color: #159653;
  background: #e5f8ec;
}

.success-modal .primary {
  margin: 0 auto;
}

.toast {
  position: fixed;
  right: 28px;
  bottom: 28px;
  z-index: 160;
  max-width: 430px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 11px;
  border-radius: 16px;
  color: white;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2);
}

.toast.success {
  background: #14884a;
}

.toast.warning {
  background: #ad3e15;
}

.toast.info {
  background: #294d8f;
}

.spin {
  animation: spin 0.85s linear infinite;
}

@keyframes pulse {
  50% {
    box-shadow: 0 0 0 10px rgba(22, 163, 74, 0.05);
  }
}

@keyframes scannerWave {
  0% {
    transform: scale(0.35);
    opacity: 0.9;
  }

  100% {
    transform: scale(1.1);
    opacity: 0;
  }
}

@keyframes modalIn {
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

@media (max-width: 1080px) {
  .hero-actions {
    flex-direction: column;
  }

  .metrics {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 760px) {
  .inventory-header,
  .toolbar,
  .table-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .metrics,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .progress-area {
    grid-template-columns: 1fr;
  }

  .rfid-button,
  .save-button,
  .primary,
  .secondary {
    width: 100%;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }

  .toast {
    right: 16px;
    left: 16px;
    bottom: 16px;
  }
}
</style>
