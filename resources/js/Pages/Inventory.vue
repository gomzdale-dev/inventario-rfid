<template>
  <section class="inventory-page">
    <header class="inventory-header">
      <div>
        <h1>Gestión de Inventario</h1>
        <p>Administración de activos tecnológicos del laboratorio</p>
      </div>

      <div class="notifications">

        <div v-if="showNotifications" class="notifications-panel">
          <h2>Notificaciones</h2>

          <div
            v-for="notification in notifications"
            :key="notification.message"
            :class="['notification-item', { unread: notification.unread }]"
          >
            <p>{{ notification.message }}</p>
            <small>{{ notification.time }}</small>
          </div>
        </div>
      </div>
    </header>

    <button
      class="rfid-button"
      :disabled="isScanning"
      @click="startRfidInventory"
    >
      <RadioTower :class="{ pulse: isScanning }" size="24" />
      {{ isScanning ? "Realizando Inventario..." : "Realizar Inventario con RFID" }}
    </button>

    <section class="inventory-toolbar">
      <div class="search-box">
        <Search size="24" />
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por ID, nombre o código RFID..."
        />
      </div>

      <select v-model="selectedType">
        <option>Todos</option>
        <option v-for="type in assetTypes" :key="type">{{ type }}</option>
      </select>

      <button class="outline-action">
        <Filter size="21" />
        Filtros
      </button>

      <button class="outline-action">
        <Download size="21" />
        Exportar
      </button>
    </section>

    <section class="inventory-table-card">
      <div v-if="isLoading" class="assets-state-box">
        Cargando activos...
      </div>

      <table v-else>
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre/Descripción</th>
            <th>Tipo</th>
            <th>Ubicación</th>
            <th>RFID</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="asset in paginatedAssets" :key="asset.id">
            <td>{{ asset.id }}</td>
            <td>{{ asset.name }}</td>
            <td>{{ asset.type }}</td>
            <td>{{ asset.location }}</td>
            <td class="rfid-code">{{ asset.rfid }}</td>
            <td>
              <button class="eye-btn" @click="selectedAsset = asset">
                <Eye size="20" />
              </button>
            </td>
          </tr>

          <tr v-if="filteredAssets.length === 0">
            <td colspan="6">No se encontraron activos registrados.</td>
          </tr>
        </tbody>
      </table>

      <footer class="table-footer">
        <p>Mostrando {{ filteredAssets.length }} de {{ assets.length }} activos</p>

        <div class="pagination">
          <button :disabled="currentPage === 1" @click="currentPage--">
            Anterior
          </button>

          <button class="active-page">{{ currentPage }}</button>

          <button v-if="totalPages > 1 && currentPage !== 2" @click="currentPage = 2">2</button>

          <button :disabled="currentPage === totalPages" @click="currentPage++">
            Siguiente
          </button>
        </div>
      </footer>
    </section>

    <div v-if="selectedAsset" class="modal-backdrop" @click="selectedAsset = null">
      <div class="asset-modal" @click.stop>
        <h2>Detalle del Activo</h2>
        <p><strong>ID:</strong> {{ selectedAsset.id }}</p>
        <p><strong>Nombre:</strong> {{ selectedAsset.name }}</p>
        <p><strong>Tipo:</strong> {{ selectedAsset.type }}</p>
        <p><strong>Ubicación:</strong> {{ selectedAsset.location }}</p>
        <p><strong>RFID:</strong> {{ selectedAsset.rfid }}</p>

        <button @click="selectedAsset = null">Cerrar</button>
      </div>
    </div>
  </section>
</template>

<script>
import {
  Bell,
  Search,
  Filter,
  Download,
  Eye,
  RadioTower
} from "lucide-vue-next"

import api from "../services/api"

export default {
  name: "Inventory",
  components: {
    Bell,
    Search,
    Filter,
    Download,
    Eye,
    RadioTower
  },

  data() {
    return {
      search: "",
      selectedType: "Todos",
      currentPage: 1,
      perPage: 10,
      isScanning: false,
      isLoading: false,
      showNotifications: false,
      selectedAsset: null,

      notifications: [],

      assets: []
    }
  },

  computed: {
    unreadNotifications() {
      return this.notifications.filter(n => n.unread).length
    },

    assetTypes() {
      return [...new Set(this.assets.map(asset => asset.type).filter(Boolean))]
    },

    filteredAssets() {
      return this.assets.filter(asset => {
        const searchText = this.search.toLowerCase()

        const id = String(asset.id ?? "").toLowerCase()
        const name = String(asset.name ?? "").toLowerCase()
        const rfid = String(asset.rfid ?? "").toLowerCase()

        const matchesSearch =
          id.includes(searchText) ||
          name.includes(searchText) ||
          rfid.includes(searchText)

        const matchesType =
          this.selectedType === "Todos" || asset.type === this.selectedType

        return matchesSearch && matchesType
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
    this.getAssets()
    this.getNotifications()
  },

  methods: {
    async startRfidInventory() {
      this.isScanning = true

      try {
        await this.getAssets()

        this.notifications.unshift({
          message: "Inventario RFID actualizado con los activos registrados en la base de datos",
          time: "Ahora",
          unread: true
        })
      } catch (error) {
        console.error(error)
      } finally {
        this.isScanning = false
      }
    },

    async getNotifications() {
      try {
        const response = await api.get("/alertas")
        this.notifications = (response.data ?? []).map(alert => ({
          message: alert.mensaje ?? alert.titulo ?? "Notificación del sistema",
          time: alert.fecha_alerta ? new Date(alert.fecha_alerta).toLocaleString("es-SV") : "Sin fecha",
          unread: !alert.leida
        }))
      } catch (error) {
        console.error(error)
      }
    },

    async getAssets() {
      try {
        this.isLoading = true
        const response = await api.get("/activo")

        this.assets = (response.data ?? []).map(item => {
          const labName =
            item.ubicacion?.laboratorio?.nombre_laboratorio ??
            item.ubicacion?.nombre_laboratorio ??
            item.nombre_laboratorio ??
            (item.ubicacion?.id_laboratorio ? `Laboratorio #${item.ubicacion.id_laboratorio}` : "SIN UBICACIÓN")

          const buildingName =
            item.ubicacion?.laboratorio?.edificio?.nombre_edificio ??
            item.ubicacion?.nombre_edificio ??
            item.nombre_edificio ??
            ""

          const location = buildingName ? `${buildingName} - ${labName}` : labName

          return {
            id: item.id_activo,
            name: item.nombre_activo,
            type: item.categoria?.nombre_categoria ?? item.nombre_categoria ?? item.modelo?.nombre_modelo ?? "SIN TIPO",
            location,
            rfid: item.etiqueta?.codigo ?? item.codigo_rfid ?? "SIN RFID",
            original: item
          }
        })

      } catch (error) {
        console.error(error)
      } finally {
        this.isLoading = false
      }
    }
  },

  watch: {
    search() {
      this.currentPage = 1
    },
    selectedType() {
      this.currentPage = 1
    }
  }
}
</script>
