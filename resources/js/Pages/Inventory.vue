<template>
  <section class="inventory-page">
    <header class="inventory-header">
      <div>
        <h1>Gestión de Inventario</h1>
        <p>Administración de activos tecnológicos del laboratorio</p>
      </div>

      <div class="notifications">
        <button class="notification-btn" @click="showNotifications = !showNotifications">
          <Bell size="26" />
          <span>{{ unreadNotifications }}</span>
        </button>

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
            <th>ID</th>
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
      assets: [
        { id: "ACT-001", name: "Computadora Dell OptiPlex 7090", type: "Computadora", location: "Lab A-102", rfid: "RFID-2847" },
        { id: "ACT-002", name: "Monitor Samsung 24\" LED", type: "Monitor", location: "Lab B-205", rfid: "RFID-1293" },
        { id: "ACT-003", name: "Router Cisco 2901", type: "Networking", location: "Lab A-102", rfid: "RFID-5621" },
        { id: "ACT-004", name: "Arduino Mega 2560", type: "Microcontrolador", location: "Lab C-301", rfid: "RFID-8834" },
        { id: "ACT-005", name: "Laptop HP EliteBook 840", type: "Computadora", location: "Lab A-102", rfid: "RFID-4521" },
        { id: "ACT-006", name: "Switch D-Link 24 puertos", type: "Networking", location: "Lab B-205", rfid: "RFID-7893" },
        { id: "ACT-007", name: "Raspberry Pi 4 Model B", type: "Microcontrolador", location: "Lab C-301", rfid: "RFID-3347" },
        { id: "ACT-008", name: "Teclado Logitech K380", type: "Periférico", location: "Lab A-102", rfid: "RFID-9012" },
        { id: "ACT-009", name: "Mouse Logitech MX Master 3", type: "Periférico", location: "Lab B-205", rfid: "RFID-6754" },
        { id: "ACT-010", name: "Impresora HP LaserJet Pro", type: "Impresora", location: "Lab A-102", rfid: "RFID-2198" }
      ]
    }
  },
  computed: {
    unreadNotifications() {
      return this.notifications.filter(notification => notification.unread).length
    },
    filteredAssets() {
      return this.assets.filter(asset => {
        const searchText = this.search.toLowerCase()

        const matchesSearch =
          asset.id.toLowerCase().includes(searchText) ||
          asset.name.toLowerCase().includes(searchText) ||
          asset.rfid.toLowerCase().includes(searchText)

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
  methods: {
    startRfidInventory() {
      this.isScanning = true

      setTimeout(() => {
        const newAssets = [
          { id: "ACT-011", name: "Tablet Samsung Galaxy Tab S8", type: "Tablet", location: "Lab D-104", rfid: "RFID-3421" },
          { id: "ACT-012", name: "Proyector Epson PowerLite", type: "Proyector", location: "Lab B-205", rfid: "RFID-8765" },
          { id: "ACT-013", name: "Scanner HP ScanJet Pro", type: "Scanner", location: "Lab A-102", rfid: "RFID-5544" }
        ]

        const alreadyAdded = this.assets.some(asset => asset.id === "ACT-011")

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