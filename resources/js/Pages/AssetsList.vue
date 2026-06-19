<template>
  <section class="inventory-page">
    <header class="inventory-header">
      <div>
        <h1>Activos</h1>
        <p>Consulta general de activos registrados en el sistema RFID</p>
      </div>
    </header>

    <section class="inventory-toolbar assets-toolbar-fix">
      <div class="search-box">
        <Search size="24" />
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre, serie, RFID, responsable o ubicación..."
        />
      </div>

      <select v-model="selectedStatus">
        <option value="Todos">Todos</option>
        <option value="Asignados">Asignados</option>
        <option value="Sin asignar">Sin asignar</option>
      </select>

      <button class="outline-action" type="button" @click="loadAssets">
        <RefreshCcw size="21" />
        Actualizar
      </button>

      <button class="outline-action" type="button" @click="exportCsv">
        <Download size="21" />
        Exportar
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
              <th>ID</th>
              <th>Activo</th>
              <th>Serie</th>
              <th>RFID</th>
              <th>Ubicación</th>
              <th>Responsable</th>
              <th>Estado</th>
              <th class="actions-column">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="asset in paginatedAssets" :key="asset.id">
              <td>{{ asset.id }}</td>
              <td>{{ asset.name }}</td>
              <td>{{ asset.serial }}</td>
              <td class="rfid-code">{{ asset.rfid }}</td>
              <td>{{ asset.location }}</td>
              <td>
                <span :class="['asset-badge', asset.responsible === 'Sin asignar' ? 'warning' : 'success']">
                  {{ asset.responsible }}
                </span>
              </td>
              <td>
                <span :class="['asset-badge', asset.status === 'Asignado' ? 'success' : 'warning']">
                  {{ asset.status }}
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

    <div v-if="selectedAsset" class="modal-backdrop" @click="selectedAsset = null">
      <div class="asset-modal asset-detail-modal" @click.stop>
        <h2>Detalle del Activo</h2>

        <div class="asset-detail-grid">
          <p><strong>ID:</strong> {{ selectedAsset.id }}</p>
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
  </section>
</template>

<script>
import {
  Search,
  Download,
  Eye,
  RefreshCcw,
  UserCheck
} from "lucide-vue-next"

import api from "../services/api"
import Swal from "sweetalert2"

export default {
  name: "AssetsList",

  components: {
    Search,
    Download,
    Eye,
    RefreshCcw,
    UserCheck
  },

  data() {
    return {
      search: "",
      selectedStatus: "Todos",
      currentPage: 1,
      perPage: 10,
      isLoading: false,
      isAssigning: false,
      selectedAsset: null,
      showAssignModal: false,
      assetToAssign: null,
      responsables: [],
      assignForm: {
        id_responsable: ""
      },
      assets: []
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

        const matchesStatus =
          this.selectedStatus === "Todos" ||
          asset.status === this.selectedStatus ||
          (this.selectedStatus === "Asignados" && asset.status === "Asignado")

        return matchesSearch && matchesStatus
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

    exportCsv() {
      if (this.filteredAssets.length === 0) {
        Swal.fire({
          icon: "warning",
          title: "Sin datos",
          text: "No hay activos para exportar."
        })
        return
      }

      const headers = [
        "ID",
        "Activo",
        "Serie",
        "RFID",
        "Ubicación",
        "Responsable",
        "Estado",
        "Valor Compra",
        "Valor Actual",
        "Fecha Compra"
      ]

      const rows = this.filteredAssets.map(asset => [
        asset.id,
        asset.name,
        asset.serial,
        asset.rfid,
        asset.location,
        asset.responsible,
        asset.status,
        asset.purchaseValue,
        asset.currentValue,
        asset.purchaseDate
      ])

      const csvContent = [headers, ...rows]
        .map(row => row.map(value => `"${String(value).replaceAll('"', '""')}"`).join(","))
        .join("\n")

      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" })
      const url = URL.createObjectURL(blob)
      const link = document.createElement("a")

      link.href = url
      link.setAttribute("download", "activos_registrados.csv")
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
    }
  },

  watch: {
    search() {
      this.currentPage = 1
    },

    selectedStatus() {
      this.currentPage = 1
    }
  }
}
</script>
