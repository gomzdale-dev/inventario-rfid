<template>
  <section class="maintenance-page">
    <header class="maintenance-header">
      <h1>Mantenimiento de Catálogos</h1>
      <p>Administración de tablas base del sistema RFID</p>
    </header>

    <section class="maintenance-summary">
      <div class="maintenance-card">
        <Database size="30" />
        <div>
          <h2>{{ catalogs.length }}</h2>
          <p>Catálogos disponibles</p>
        </div>
      </div>

      <div class="maintenance-card">
        <TableProperties size="30" />
        <div>
          <h2>{{ selectedCatalog.records.length }}</h2>
          <p>Registros actuales</p>
        </div>
      </div>

      <div class="maintenance-card">
        <Activity size="30" />
        <div>
          <h2>CRUD</h2>
          <p>Crear, editar y eliminar</p>
        </div>
      </div>
    </section>

    <section class="maintenance-layout">
      <aside class="catalog-panel">
        <button class="catalog-dropdown" @click="showCatalogs = !showCatalogs">
          <span>
            <ChevronDown :class="{ rotate: showCatalogs }" size="22" />
            Catálogos del DER
          </span>
        </button>

        <div v-if="showCatalogs" class="catalog-list">
          <button
            v-for="catalog in catalogs"
            :key="catalog.key"
            :class="{ active: activeCatalog === catalog.key }"
            @click="activeCatalog = catalog.key"
          >
            <component :is="catalog.icon" size="20" />
            {{ catalog.name }}
          </button>
        </div>
      </aside>

      <main class="catalog-content">
        <div class="catalog-top">
          <div>
            <h2>{{ selectedCatalog.name }}</h2>
            <p>{{ selectedCatalog.description }}</p>
          </div>

          <button class="new-record-btn" @click="openCreateModal">
            <Plus size="21" />
            Nuevo Registro
          </button>
        </div>

        <div class="catalog-toolbar">
          <div class="catalog-search">
            <Search size="22" />
            <input v-model="search" placeholder="Buscar registro..." />
          </div>
        </div>

        <section class="catalog-table-card">
          <table>
            <thead>
              <tr>
                <th v-for="field in selectedCatalog.fields" :key="field.key">
                  {{ field.label }}
                </th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="record in filteredRecords" :key="record.id">
                <td v-for="field in selectedCatalog.fields" :key="field.key">
                  {{ record[field.key] }}
                </td>

                <td>
                  <span :class="['catalog-status', record.status === 'Activo' ? 'active' : 'inactive']">
                    {{ record.status }}
                  </span>
                </td>

                <td>
                  <div class="catalog-actions">
                    <button @click="openEditModal(record)">
                      <Pencil size="19" />
                    </button>

                    <button @click="deleteRecord(record)">
                      <Trash2 size="19" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </section>
      </main>
    </section>


<section class="maintenance-analytics">
  <div class="analytics-card">
    <div class="analytics-header">
      <div>
        <h2>Distribución de Registros por Catálogo</h2>
        <p>Resumen dinámico de tablas maestras configuradas en el sistema</p>
      </div>

      <div class="analytics-badge">
        {{ totalCatalogRecords }} registros
      </div>
    </div>

    <div class="catalog-bars">
      <div
        v-for="catalog in catalogChartData"
        :key="catalog.name"
        class="catalog-bar-row"
      >
        <div class="bar-info">
          <span>{{ catalog.name }}</span>
          <strong>{{ catalog.total }}</strong>
        </div>

        <div class="bar-track">
          <div
            class="bar-fill"
            :style="{ width: catalog.percentage + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </div>

  <div class="analytics-side-card">
    <h2>Actividad del Módulo</h2>

    <div class="activity-metric">
      <span>Catálogo activo</span>
      <strong>{{ selectedCatalog.name }}</strong>
    </div>

    <div class="activity-metric">
      <span>Registros visibles</span>
      <strong>{{ filteredRecords.length }}</strong>
    </div>

    <div class="activity-metric">
      <span>Estado del módulo</span>
      <strong class="online">Operativo</strong>
    </div>
  </div>
</section>





    <section class="maintenance-timeline">
      <h2>Historial Administrativo</h2>

      <div class="timeline-item" v-for="item in timeline" :key="item.text">
        <span></span>
        <div>
          <h3>{{ item.text }}</h3>
          <p>{{ item.time }}</p>
        </div>
      </div>
    </section>

    <div v-if="showModal" class="modal-backdrop" @click="closeModal">
      <form class="maintenance-modal" @submit.prevent="saveRecord" @click.stop>
        <header>
          <h2>{{ editingRecord ? "Editar Registro" : "Nuevo Registro" }}</h2>
          <button type="button" @click="closeModal">
            <X size="22" />
          </button>
        </header>

        <div class="maintenance-modal-grid">
          <div
            v-for="field in selectedCatalog.fields"
            :key="field.key"
            class="maintenance-field"
          >
            <label>{{ field.label }} *</label>
            <input v-model="form[field.key]" required />
          </div>

          <div class="maintenance-field">
            <label>Estado *</label>
            <select v-model="form.status" required>
              <option>Activo</option>
              <option>Inactivo</option>
            </select>
          </div>
        </div>

        <div class="maintenance-modal-actions">
          <button type="button" class="cancel-btn" @click="closeModal">
            Cancelar
          </button>

          <button type="submit" class="save-btn">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import {
  Database,
  TableProperties,
  Activity,
  ChevronDown,
  Plus,
  Search,
  Pencil,
  Trash2,
  X,
  Tags,
  MapPin,
  Building2,
  UserCog,
  RadioTower,
  MoveRight,
  BadgeCheck,
  Cpu,
  Landmark,
  Box
} from "lucide-vue-next"

export default {
  name: "Maintenance",
  components: {
    Database,
    TableProperties,
    Activity,
    ChevronDown,
    Plus,
    Search,
    Pencil,
    Trash2,
    X,
    Tags,
    MapPin,
    Building2,
    UserCog,
    RadioTower,
    MoveRight,
    BadgeCheck,
    Cpu,
    Landmark,
    Box
  },
  data() {
    return {
      showCatalogs: true,
      activeCatalog: "categorias",
      search: "",
      showModal: false,
      editingRecord: null,
      form: {},
      timeline: [
        { text: "Se actualizó el catálogo de categorías", time: "Hoy, 09:35" },
        { text: "Se agregó nueva ubicación Lab D-104", time: "Ayer, 15:20" },
        { text: "Se modificó el estado En Mantenimiento", time: "Hace 2 días" }
      ],
      catalogs: [
        {
          key: "categorias",
          name: "Categorías",
          description: "Clasificación general de activos tecnológicos.",
          icon: Tags,
          fields: [{ key: "id", label: "ID Categoría" }, { key: "name", label: "Nombre Categoría" }],
          records: [
            { id: "CAT-001", name: "Computadora", status: "Activo" },
            { id: "CAT-002", name: "Monitor", status: "Activo" },
            { id: "CAT-003", name: "Networking", status: "Activo" }
          ]
        },
        {
          key: "estados",
          name: "Estados de Activo",
          description: "Estados operativos asignados a los activos.",
          icon: BadgeCheck,
          fields: [{ key: "id", label: "ID Estado" }, { key: "name", label: "Nombre Estado" }],
          records: [
            { id: "EST-001", name: "Activo", status: "Activo" },
            { id: "EST-002", name: "En Mantenimiento", status: "Activo" },
            { id: "EST-003", name: "Fuera de Servicio", status: "Activo" }
          ]
        },
        {
          key: "marcas",
          name: "Marcas",
          description: "Marcas comerciales de los equipos registrados.",
          icon: Landmark,
          fields: [{ key: "id", label: "ID Marca" }, { key: "name", label: "Nombre Marca" }],
          records: [
            { id: "MAR-001", name: "Dell", status: "Activo" },
            { id: "MAR-002", name: "HP", status: "Activo" },
            { id: "MAR-003", name: "Cisco", status: "Activo" }
          ]
        },
        {
          key: "modelos",
          name: "Modelos",
          description: "Modelos asociados a marcas y activos.",
          icon: Cpu,
          fields: [{ key: "id", label: "ID Modelo" }, { key: "name", label: "Nombre Modelo" }],
          records: [
            { id: "MOD-001", name: "OptiPlex 7090", status: "Activo" },
            { id: "MOD-002", name: "EliteBook 840", status: "Activo" },
            { id: "MOD-003", name: "PowerLite", status: "Activo" }
          ]
        },
        {
          key: "ubicaciones",
          name: "Ubicaciones",
          description: "Ubicaciones físicas asociadas a laboratorios.",
          icon: MapPin,
          fields: [{ key: "id", label: "ID Ubicación" }, { key: "name", label: "Nombre Ubicación" }],
          records: [
            { id: "UBI-001", name: "Lab A-102", status: "Activo" },
            { id: "UBI-002", name: "Lab B-205", status: "Activo" },
            { id: "UBI-003", name: "Lab C-301", status: "Activo" }
          ]
        },
        {
          key: "laboratorios",
          name: "Laboratorios",
          description: "Laboratorios registrados dentro del sistema.",
          icon: Building2,
          fields: [{ key: "id", label: "ID Laboratorio" }, { key: "name", label: "Nombre Laboratorio" }],
          records: [
            { id: "LAB-001", name: "Laboratorio A-102", status: "Activo" },
            { id: "LAB-002", name: "Laboratorio B-205", status: "Activo" }
          ]
        },
        {
          key: "edificios",
          name: "Edificios",
          description: "Edificios institucionales donde se ubican laboratorios.",
          icon: Building2,
          fields: [{ key: "id", label: "ID Edificio" }, { key: "name", label: "Nombre Edificio" }],
          records: [
            { id: "EDI-001", name: "Edificio de Computación", status: "Activo" },
            { id: "EDI-002", name: "Edificio Administrativo", status: "Activo" }
          ]
        },
        {
          key: "tipoUsuario",
          name: "Tipo de Usuario",
          description: "Roles o tipos de usuarios permitidos en el sistema.",
          icon: UserCog,
          fields: [{ key: "id", label: "ID Tipo" }, { key: "name", label: "Nombre Tipo" }],
          records: [
            { id: "TIP-001", name: "Administrador", status: "Activo" },
            { id: "TIP-002", name: "Técnico", status: "Activo" },
            { id: "TIP-003", name: "Consulta", status: "Activo" }
          ]
        },
        {
          key: "responsables",
          name: "Responsables",
          description: "Personas responsables de equipos o áreas.",
          icon: UserCog,
          fields: [{ key: "id", label: "ID Responsable" }, { key: "name", label: "Nombre Responsable" }],
          records: [
            { id: "RESP-001", name: "Ing. Carlos Méndez", status: "Activo" },
            { id: "RESP-002", name: "Lic. María Rodríguez", status: "Activo" }
          ]
        },
        {
          key: "lectores",
          name: "Lectores RFID",
          description: "Lectores utilizados para registrar movimientos RFID.",
          icon: RadioTower,
          fields: [{ key: "id", label: "ID Lector" }, { key: "name", label: "Ubicación Lector" }],
          records: [
            { id: "LEC-001", name: "Entrada Lab A-102", status: "Activo" },
            { id: "LEC-002", name: "Entrada Lab B-205", status: "Activo" }
          ]
        },
        {
          key: "tipoMovimiento",
          name: "Tipo de Movimiento",
          description: "Clasificación de movimientos de activos.",
          icon: MoveRight,
          fields: [{ key: "id", label: "ID Movimiento" }, { key: "name", label: "Descripción" }],
          records: [
            { id: "MOV-001", name: "Entrada", status: "Activo" },
            { id: "MOV-002", name: "Salida", status: "Activo" },
            { id: "MOV-003", name: "Traslado", status: "Activo" }
          ]
        },
        {
          key: "etiquetas",
          name: "Etiquetas RFID",
          description: "Etiquetas RFID disponibles o asignadas.",
          icon: Box,
          fields: [{ key: "id", label: "ID Etiqueta" }, { key: "name", label: "Código RFID" }],
          records: [
            { id: "TAG-001", name: "RFID-2847", status: "Activo" },
            { id: "TAG-002", name: "RFID-1293", status: "Activo" }
          ]
        }
      ]
    }
  },
  computed: {
    
    selectedCatalog() {
      return this.catalogs.find(catalog => catalog.key === this.activeCatalog)
    },
    filteredRecords() {
      const term = this.search.toLowerCase()

      return this.selectedCatalog.records.filter(record =>
        Object.values(record).some(value =>
          String(value).toLowerCase().includes(term)
        )
      )
    }
  },

computed: {
  selectedCatalog() {
    return this.catalogs.find(catalog => catalog.key === this.activeCatalog)
  },
  filteredRecords() {
    const term = this.search.toLowerCase()

    return this.selectedCatalog.records.filter(record =>
      Object.values(record).some(value =>
        String(value).toLowerCase().includes(term)
      )
    )
  },
  totalCatalogRecords() {
    return this.catalogs.reduce((total, catalog) => {
      return total + catalog.records.length
    }, 0)
  },
  catalogChartData() {
    const maxRecords = Math.max(...this.catalogs.map(catalog => catalog.records.length))

    return this.catalogs.map(catalog => {
      return {
        name: catalog.name,
        total: catalog.records.length,
        percentage: maxRecords === 0 ? 0 : (catalog.records.length / maxRecords) * 100
      }
    })
  }
},





  methods: {
    openCreateModal() {
      this.editingRecord = null
      this.form = { status: "Activo" }

      this.selectedCatalog.fields.forEach(field => {
        this.form[field.key] = ""
      })

      this.showModal = true
    },
    openEditModal(record) {
      this.editingRecord = record
      this.form = { ...record }
      this.showModal = true
    },
    closeModal() {
      this.showModal = false
      this.editingRecord = null
      this.form = {}
    },
    saveRecord() {
      if (this.editingRecord) {
        Object.assign(this.editingRecord, this.form)
      } else {
        this.selectedCatalog.records.unshift({ ...this.form })
      }

      this.timeline.unshift({
        text: `Se actualizó el catálogo de ${this.selectedCatalog.name}`,
        time: "Ahora"
      })

      this.closeModal()
    },
    deleteRecord(record) {
      const confirmed = confirm(`¿Eliminar el registro ${record.id}?`)

      if (confirmed) {
        this.selectedCatalog.records = this.selectedCatalog.records.filter(
          item => item !== record
        )

        this.timeline.unshift({
          text: `Se eliminó un registro de ${this.selectedCatalog.name}`,
          time: "Ahora"
        })
      }
    }
  }
}
</script>