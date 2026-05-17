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
                <th v-for="field in selectedCatalog.fields.filter(f => !f.hidden && !f.formVisible)" :key="field.key">
                  {{ field.label }}
                </th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="record in filteredRecords" :key="record.id">
                <td v-for="field in selectedCatalog.fields.filter(f => !f.hidden && !f.formVisible)" :key="field.key">
                  <span
                    v-if="field.key === 'estado'"
                    :class="record[field.key] === 'A' ? 'badge-activo' : 'badge-inactivo'"
                  >
                    {{ record[field.key] === 'A' ? 'Activo' : 'Inactivo' }}
                  </span>
                  <span v-else>{{ record[field.key] }}</span>
                </td>
                <td>
                  <div class="catalog-actions">
                    <button @click="openEditModal(record)">
                      <Pencil size="19" />
                    </button>
                    <button
                      v-if="activeCatalog !== 'edificios'"
                      @click="deleteRecord(record)">
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

    <!-- MODAL -->
    <div v-if="showModal" class="modal-backdrop" @click="closeModal">
      <form class="maintenance-modal" @submit.prevent="saveRecord" @click.stop>
        <header>
          <h2>{{ editingRecord ? "Editar Registro" : "Nuevo Registro" }}</h2>
          <button type="button" @click="closeModal">
            <X size="22" />
          </button>
        </header>

        <!-- Campos normales para catálogos regulares -->
        <template v-if="activeCatalog !== 'responsables'">
          <div
            v-for="field in selectedCatalog.fields.filter(f => !f.hidden && f.type !== 'select')"
            :key="field.key"
            class="maintenance-field"
          >
            <label>{{ field.label }} *</label>
            <input v-model="form[field.key]" :placeholder="field.placeholder || ''" required />
          </div>

          <div
            v-for="field in selectedCatalog.fields.filter(f => !f.hidden && f.type === 'select')"
            :key="'select-' + field.key"
            class="maintenance-field"
          >
            <label>{{ field.label }} *</label>
            <select v-model="form[field.key]" required>
              <option value="" disabled>Seleccione una opción</option>
              <option
                v-for="option in field.options"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>
        </template>

        <!-- Campos especiales para responsables -->
        <template v-if="activeCatalog === 'responsables'">
          <div class="maintenance-field">
            <label>Ingrese nombres *</label>
            <input v-model="form.nombre" placeholder="Ej: Juan Carlos" required />
          </div>
          <div class="maintenance-field">
            <label>Ingrese apellidos *</label>
            <input v-model="form.apellido" placeholder="Ej: García López" required />
          </div>
          <div class="maintenance-field">
            <label>Código Empleado *</label>
            <input v-model="form.codigo_empleado_form" placeholder="Ej: 3006080" required />
          </div>
        </template>

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
import axios from 'axios'
import Swal from 'sweetalert2'
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
  mounted() {
    this.fetchCategorias()
    this.fetchEdificios()
    this.fetchMarcas()
    this.fetchModelos()
    this.fetchLaboratorios()
    this.fetchResponsables()
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
          fields: [
            { key: "id_categoria",     label: "ID Categoría",    hidden: true },
            { key: "nombre_categoria", label: "Nombre Categoría" },
            { key: "estado",           label: "Estado",          hidden: true }
          ],
          records: []
        },
        {
          key: "estados",
          name: "Estados de Activo",
          description: "Estados operativos asignados a los activos.",
          icon: BadgeCheck,
          fields: [{ key: "id", label: "ID Estado" }, { key: "name", label: "Nombre Estado" }],
          records: [
            { id: "EST-001", name: "Activo" },
            { id: "EST-002", name: "En Mantenimiento" },
            { id: "EST-003", name: "Fuera de Servicio" }
          ]
        },
        {
          key: "marcas",
          name: "Marcas",
          description: "Marcas comerciales de los equipos registrados.",
          icon: Landmark,
          fields: [
            { key: "id_marca",     label: "ID Marca",    hidden: true },
            { key: "nombre_marca", label: "Nombre Marca" },
            { key: "estado",       label: "Estado",      hidden: true }
          ],
          records: []
        },
        {
          key: "modelos",
          name: "Modelos",
          description: "Modelos asociados a marcas y activos.",
          icon: Cpu,
          fields: [
            { key: "id_modelo",     label: "ID Modelo", hidden: true },
            { key: "nombre_modelo", label: "Nombre Modelo" },
            { key: "id_marca",      label: "Marca",     type: "select", options: [] },
            { key: "estado",        label: "Estado",    hidden: true }
          ],
          records: []
        },
        {
          key: "ubicaciones",
          name: "Ubicaciones",
          description: "Ubicaciones físicas asociadas a laboratorios.",
          icon: MapPin,
          fields: [{ key: "id", label: "ID Ubicación" }, { key: "name", label: "Nombre Ubicación" }],
          records: [
            { id: "UBI-001", name: "Lab A-102" },
            { id: "UBI-002", name: "Lab B-205" },
            { id: "UBI-003", name: "Lab C-301" }
          ]
        },
        {
          key: "laboratorios",
          name: "Laboratorios",
          description: "Laboratorios registrados dentro del sistema.",
          icon: Building2,
          fields: [
            { key: "id_laboratorio",     label: "ID Laboratorio", hidden: true },
            { key: "nombre_laboratorio", label: "Nombre Laboratorio" },
            { key: "id_edificio",        label: "Edificio",       type: "select", options: [] }
          ],
          records: []
        },
        {
          key: "edificios",
          name: "Edificios",
          description: "Edificios institucionales donde se ubican laboratorios.",
          icon: Building2,
          fields: [
            { key: "id",   label: "ID Edificio",    hidden: true },
            { key: "name", label: "Nombre Edificio" }
          ],
          records: []
        },
        {
          key: "tipoUsuario",
          name: "Tipo de Usuario",
          description: "Roles o tipos de usuarios permitidos en el sistema.",
          icon: UserCog,
          fields: [{ key: "id", label: "ID Tipo" }, { key: "name", label: "Nombre Tipo" }],
          records: [
            { id: "TIP-001", name: "Administrador" },
            { id: "TIP-002", name: "Técnico" },
            { id: "TIP-003", name: "Consulta" }
          ]
        },
        {
          key: "responsables",
          name: "Responsables",
          description: "Personas responsables de equipos o áreas.",
          icon: UserCog,
          fields: [
            { key: "id",                 label: "ID Responsable",   hidden: true },
            { key: "nombre_responsable", label: "Nombre Responsable" },
            { key: "codigo_empleado",    label: "Código Empleado" }
          ],
          records: []
        },
        {
          key: "lectores",
          name: "Lectores RFID",
          description: "Lectores utilizados para registrar movimientos RFID.",
          icon: RadioTower,
          fields: [{ key: "id", label: "ID Lector" }, { key: "name", label: "Ubicación Lector" }],
          records: [
            { id: "LEC-001", name: "Entrada Lab A-102" },
            { id: "LEC-002", name: "Entrada Lab B-205" }
          ]
        },
        {
          key: "tipoMovimiento",
          name: "Tipo de Movimiento",
          description: "Clasificación de movimientos de activos.",
          icon: MoveRight,
          fields: [{ key: "id", label: "ID Movimiento" }, { key: "name", label: "Descripción" }],
          records: [
            { id: "MOV-001", name: "Entrada" },
            { id: "MOV-002", name: "Salida" },
            { id: "MOV-003", name: "Traslado" }
          ]
        },
        {
          key: "etiquetas",
          name: "Etiquetas RFID",
          description: "Etiquetas RFID disponibles o asignadas.",
          icon: Box,
          fields: [{ key: "id", label: "ID Etiqueta" }, { key: "name", label: "Código RFID" }],
          records: [
            { id: "TAG-001", name: "RFID-2847" },
            { id: "TAG-002", name: "RFID-1293" }
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
      this.form = {}
      if (this.activeCatalog === 'responsables') {
        this.form = { nombre: '', apellido: '', codigo_empleado_form: '' }
      } else {
        this.selectedCatalog.fields
          .filter(f => !f.hidden)
          .forEach(field => {
            this.form[field.key] = ""
          })
      }
      this.showModal = true
    },
    openEditModal(record) {
      this.editingRecord = record
      if (this.activeCatalog === 'responsables') {
        const partes = (record.nombre_responsable || '').split(' ')
        this.form = {
          nombre:               partes[0] || '',
          apellido:             partes.slice(1).join(' ') || '',
          codigo_empleado_form: record.codigo_empleado || ''
        }
      } else {
        this.form = { ...record }
      }
      this.showModal = true
    },
    closeModal() {
      this.showModal = false
      this.editingRecord = null
      this.form = {}
    },
    async saveRecord() {
      if (this.activeCatalog === 'categorias') {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`http://localhost:8000/api/categoria/${this.editingRecord.id_categoria}`, {
              nombre_categoria: this.form.nombre_categoria
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await axios.post('http://localhost:8000/api/categoria', {
              nombre_categoria: this.form.nombre_categoria
            })
            this.selectedCatalog.records.push(response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Categoría creada!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar categoría:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar la categoría', confirmButtonColor: '#d33' })
          return
        }

      } else if (this.activeCatalog === 'marcas') {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`http://localhost:8000/api/marca/${this.editingRecord.id_marca}`, {
              nombre_marca: this.form.nombre_marca
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await axios.post('http://localhost:8000/api/marca', {
              nombre_marca: this.form.nombre_marca
            })
            this.selectedCatalog.records.push(response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Marca creada!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar marca:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar la marca', confirmButtonColor: '#d33' })
          return
        }

      } else if (this.activeCatalog === 'modelos') {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`http://localhost:8000/api/modelo/${this.editingRecord.id_modelo}`, {
              nombre_modelo: this.form.nombre_modelo,
              id_marca:      this.form.id_marca
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await axios.post('http://localhost:8000/api/modelo', {
              nombre_modelo: this.form.nombre_modelo,
              id_marca:      this.form.id_marca
            })
            this.selectedCatalog.records.push(response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Modelo creado!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar modelo:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el modelo', confirmButtonColor: '#d33' })
          return
        }

      } else if (this.activeCatalog === 'laboratorios') {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`http://localhost:8000/api/laboratorio/${this.editingRecord.id_laboratorio}`, {
              nombre_laboratorio: this.form.nombre_laboratorio,
              id_edificio:        this.form.id_edificio
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await axios.post('http://localhost:8000/api/laboratorio', {
              nombre_laboratorio: this.form.nombre_laboratorio,
              id_edificio:        this.form.id_edificio
            })
            this.selectedCatalog.records.push(response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Laboratorio creado!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar laboratorio:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el laboratorio', confirmButtonColor: '#d33' })
          return
        }

      } else if (this.activeCatalog === 'responsables') {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`http://localhost:8000/api/responsable/${this.editingRecord.id}`, {
              nombre:          this.form.nombre,
              apellido:        this.form.apellido,
              codigo_empleado: this.form.codigo_empleado_form
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await axios.post('http://localhost:8000/api/responsable', {
              nombre:          this.form.nombre,
              apellido:        this.form.apellido,
              codigo_empleado: this.form.codigo_empleado_form
            })
            this.selectedCatalog.records.push(response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Responsable creado!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar responsable:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el responsable', confirmButtonColor: '#d33' })
          return
        }

      } else if (this.activeCatalog === 'edificios') {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`http://localhost:8000/api/edificio/${this.editingRecord.id}`, {
              nombre_edificio: this.form.name
            })
            Object.assign(this.editingRecord, {
              id: response.data.data.id_edificio,
              name: response.data.data.nombre_edificio
            })
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await axios.post('http://localhost:8000/api/edificio', {
              nombre_edificio: this.form.name
            })
            this.selectedCatalog.records.push({
              id: response.data.data.id_edificio,
              name: response.data.data.nombre_edificio
            })
            await Swal.fire({ icon: 'success', title: '¡Edificio creado!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar edificio:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el edificio', confirmButtonColor: '#d33' })
          return
        }

      } else {
        if (this.editingRecord) {
          Object.assign(this.editingRecord, this.form)
        } else {
          this.selectedCatalog.records.unshift({ ...this.form })
        }
      }

      this.timeline.unshift({
        text: `Se actualizó el catálogo de ${this.selectedCatalog.name}`,
        time: "Ahora"
      })

      this.closeModal()
    },
    async deleteRecord(record) {
      const nombre = record.nombre_categoria || record.nombre_marca || record.nombre_modelo || record.nombre_laboratorio || record.nombre_responsable || record.name || record.nombre
      const result = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar registro?',
        text: `¿Eliminar "${nombre}"?`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
      })

      if (result.isConfirmed) {
        try {
          let response

          if (this.activeCatalog === 'categorias') {
            response = await axios.delete(`http://localhost:8000/api/categoria/${record.id_categoria}`)
            this.selectedCatalog.records = this.selectedCatalog.records.filter(
              item => item.id_categoria !== record.id_categoria
            )
          } else if (this.activeCatalog === 'marcas') {
            response = await axios.delete(`http://localhost:8000/api/marca/${record.id_marca}`)
            this.selectedCatalog.records = this.selectedCatalog.records.filter(
              item => item.id_marca !== record.id_marca
            )
          } else if (this.activeCatalog === 'modelos') {
            response = await axios.delete(`http://localhost:8000/api/modelo/${record.id_modelo}`)
            this.selectedCatalog.records = this.selectedCatalog.records.filter(
              item => item.id_modelo !== record.id_modelo
            )
          } else if (this.activeCatalog === 'laboratorios') {
            response = await axios.delete(`http://localhost:8000/api/laboratorio/${record.id_laboratorio}`)
            this.selectedCatalog.records = this.selectedCatalog.records.filter(
              item => item.id_laboratorio !== record.id_laboratorio
            )
          } else if (this.activeCatalog === 'responsables') {
            response = await axios.delete(`http://localhost:8000/api/responsable/${record.id}`)
            this.selectedCatalog.records = this.selectedCatalog.records.filter(
              item => item.id !== record.id
            )
          }

          await Swal.fire({ icon: 'success', title: '¡Eliminado!', text: response.data.message, confirmButtonColor: '#3085d6' })

          this.timeline.unshift({
            text: `Se eliminó un registro de ${this.selectedCatalog.name}`,
            time: "Ahora"
          })

        } catch (error) {
          console.error('Error al eliminar:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al eliminar el registro', confirmButtonColor: '#d33' })
        }
      }
    },
    async fetchCategorias() {
      try {
        const response = await axios.get('http://localhost:8000/api/categoria')
        const catalogo = this.catalogs.find(c => c.key === 'categorias')
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error('Error:', error)
      }
    },
    async fetchMarcas() {
      try {
        const response = await axios.get('http://localhost:8000/api/marca')
        const catalogo = this.catalogs.find(c => c.key === 'marcas')
        if (catalogo) catalogo.records = response.data

        const catalogoModelos = this.catalogs.find(c => c.key === 'modelos')
        if (catalogoModelos) {
          const fieldMarca = catalogoModelos.fields.find(f => f.key === 'id_marca')
          if (fieldMarca) {
            fieldMarca.options = response.data.map(m => ({
              value: m.id_marca,
              label: m.nombre_marca
            }))
          }
        }
      } catch (error) {
        console.error('Error al cargar marcas:', error)
      }
    },
    async fetchModelos() {
      try {
        const response = await axios.get('http://localhost:8000/api/modelo')
        const catalogo = this.catalogs.find(c => c.key === 'modelos')
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error('Error al cargar modelos:', error)
      }
    },
    async fetchLaboratorios() {
      try {
        const response = await axios.get('http://localhost:8000/api/laboratorio')
        const catalogo = this.catalogs.find(c => c.key === 'laboratorios')
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error('Error al cargar laboratorios:', error)
      }
    },
    async fetchResponsables() {
      try {
        const response = await axios.get('http://localhost:8000/api/responsable')
        const catalogo = this.catalogs.find(c => c.key === 'responsables')
        console.log('Responsables recibidos:', response.data)
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error('Error al cargar responsables:', error)
      }
    },
    async fetchEdificios() {
      try {
        const response = await axios.get('http://localhost:8000/api/edificio')
        const catalogo = this.catalogs.find(c => c.key === 'edificios')
        if (catalogo) {
          catalogo.records = response.data.map(e => ({
            id: e.id_edificio,
            name: e.nombre_edificio
          }))
        }

        const catalogoLabs = this.catalogs.find(c => c.key === 'laboratorios')
        if (catalogoLabs) {
          const fieldEdificio = catalogoLabs.fields.find(f => f.key === 'id_edificio')
          if (fieldEdificio) {
            fieldEdificio.options = response.data.map(e => ({
              value: e.id_edificio,
              label: e.nombre_edificio
            }))
          }
        }
      } catch (error) {
        console.error('Error al cargar edificios:', error)
      }
    }
  }
}
</script>