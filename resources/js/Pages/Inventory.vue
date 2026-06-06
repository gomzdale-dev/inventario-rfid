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
        <option>Computadora</option>
        <option>Monitor</option>
        <option>Networking</option>
        <option>Microcontrolador</option>
        <option>Periférico</option>
        <option>Impresora</option>
        <option>Tablet</option>
        <option>Proyector</option>
        <option>Scanner</option>
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
      <table>
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
        </tbody>
      </table>

      <footer class="table-footer">
        <p>Mostrando {{ filteredAssets.length }} de {{ assets.length }} activos</p>

        <div class="pagination">
          <button :disabled="currentPage === 1" @click="currentPage--">
            Anterior
          </button>

          <button class="active-page">{{ currentPage }}</button>

          <button v-if="totalPages > 1" @click="currentPage = 2">2</button>

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
      showNotifications: false,
      selectedAsset: null,

      notifications: [
        { message: "Nuevo inventario detectado en Lab A-102", time: "Hace 5 min", unread: true },
        { message: "3 activos actualizados exitosamente", time: "Hace 12 min", unread: true },
        { message: "Inventario completado en Lab B-205", time: "Hace 1 hora", unread: false }
      ],

      assets: []
    }
  },

  computed: {
    unreadNotifications() {
      return this.notifications.filter(n => n.unread).length
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
  },

  methods: {
    startRfidInventory() {
      this.isScanning = true

      setTimeout(() => {
        const newAssets = [
          { id: "ACT-011", name: "Tablet Samsung Galaxy Tab S8", type: "Tablet", location: "Lab D-104", rfid: "RFID-3421" },
          { id: "ACT-012", name: "Proyector Epson PowerLite", type: "Proyector", location: "Lab B-205", rfid: "RFID-8765" },
          { id: "ACT-013", name: "Scanner HP ScanJet Pro", type: "Scanner", location: "Lab A-102", rfid: "RFID-5544" }
        ]

        const alreadyAdded = this.assets.some(a => a.id === "ACT-011")

        if (!alreadyAdded) {
          this.assets.push(...newAssets)

          this.notifications.unshift({
            message: "Inventario RFID completado: 3 nuevos activos detectados",
            time: "Ahora",
            unread: true
          })
        }

        this.isScanning = false
      }, 2000)
    },

    async getAssets() {
      try {
        const response = await api.get('/detalle')

        this.assets = response.data.map(item => {
          return {
            id: item.activo?.id_activo,
            name: item.activo?.nombre_activo,
            type: item.activo?.serie ?? 'SIN TIPO',
            location: item.activo?.ubicacion?.nombre ?? 'SIN UBICACIÓN',
            rfid: item.activo?.etiqueta?.codigo_rfid ?? 'SIN RFID'
          }
        })

      } catch (error) {
        console.error(error)
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