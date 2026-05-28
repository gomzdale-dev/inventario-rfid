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
    </section>

    <section class="maintenance-layout">
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
                <th v-for="field in tableFields" :key="field.key">
                  {{ field.label }}
                </th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="record in filteredRecords"
                :key="record.id || record.id_categoria || record.id_marca || record.id_modelo || record.id_laboratorio"
              >
                <td v-for="field in tableFields" :key="field.key">
                  <span>{{ record[field.key] }}</span>
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

    <div v-if="showModal" class="modal-backdrop" @click="closeModal">
      <form class="maintenance-modal" @submit.prevent="saveRecord" @click.stop>
        <header>
          <h2>{{ editingRecord ? "Editar Registro" : "Nuevo Registro" }}</h2>
          <button type="button" @click="closeModal">
            <X size="22" />
          </button>
        </header>

        <template v-if="currentCatalog !== 'responsables'">
          <div
            v-for="field in formFields"
            :key="field.key"
            class="maintenance-field"
          >
            <label>{{ field.label }} *</label>

            <select v-if="field.type === 'select'" v-model="form[field.key]" required>
              <option value="" disabled>Seleccione una opción</option>
              <option
                v-for="option in field.options"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>

            <input
              v-else
              v-model="form[field.key]"
              :placeholder="field.placeholder || ''"
              required
            />
          </div>
        </template>

        <template v-else>
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
import Swal from "sweetalert2"

import {
  Database,
  TableProperties,
  Plus,
  Search,
  Pencil,
  Trash2,
  X,
  Tags,
  Building2,
  UserCog,
  Cpu,
  Landmark,
  Box,
  BadgeCheck
} from "lucide-vue-next"
import api from "../services/api"

export default {
  name: "Maintenance",

  components: {
    Database,
    TableProperties,
    Plus,
    Search,
    Pencil,
    Trash2,
    X,
    Tags,
    Building2,
    UserCog,
    Cpu,
    Landmark,
    Box,
    BadgeCheck
  },

  // ✅ FIX: prop declarada para recibir el catálogo desde App.vue
  props: {
    activeCatalog: {
      type: String,
      default: "categorias"
    }
  },

  mounted() {
    this.fetchCategorias()
    this.fetchEdificios()
    this.fetchTipoUsuario()
    this.fetchMarcas()
    this.fetchModelos()
    this.fetchLaboratorios()
    this.fetchResponsables()
  },

  data() {
    return {
      currentCatalog: this.activeCatalog,
      search: "",
      showModal: false,
      editingRecord: null,
      form: {},
      timeline: [],
      catalogs: [
        {
          key: "categorias",
          name: "Categorías",
          description: "Clasificación general de activos tecnológicos.",
          icon: Tags,
          fields: [
            { key: "id_categoria", label: "ID Categoría", hidden: true },
            { key: "nombre_categoria", label: "Nombre Categoría" },
            { key: "estado", label: "Estado", hidden: true }
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
            { key: "id_marca", label: "ID Marca", hidden: true },
            { key: "nombre_marca", label: "Nombre Marca" },
            { key: "estado", label: "Estado", hidden: true }
          ],
          records: []
        },
        {
          key: "modelos",
          name: "Modelos",
          description: "Modelos asociados a marcas y activos.",
          icon: Cpu,
          fields: [
            { key: "id_modelo", label: "ID Modelo", hidden: true },
            { key: "nombre_modelo", label: "Nombre Modelo" },
            { key: "id_marca", label: "Marca", type: "select", options: [] },
            { key: "estado", label: "Estado", hidden: true }
          ],
          records: []
        },
        {
          key: "laboratorios",
          name: "Laboratorios",
          description: "Laboratorios registrados dentro del sistema.",
          icon: Building2,
          fields: [
            { key: "id_laboratorio", label: "ID Laboratorio", hidden: true },
            { key: "nombre_laboratorio", label: "Nombre Laboratorio" },
            { key: "id_edificio", label: "Edificio", type: "select", options: [] }
          ],
          records: []
        },
        {
          key: "edificios",
          name: "Edificios",
          description: "Edificios institucionales donde se ubican laboratorios.",
          icon: Building2,
          fields: [
            { key: "id", label: "ID Edificio", hidden: true },
            { key: "name", label: "Nombre Edificio" }
          ],
          records: []
        },
        {
          key: "tipoUsuario",
          name: "Tipo de Usuario",
          description: "Roles o tipos de usuarios permitidos en el sistema.",
          icon: UserCog,
          fields: [
            { key: "id", label: "Id tipo", hidden: true },
            { key: "nombre_tipo", label: "Rol" },
            { key: "estado", label: "Estado", hidden: true }
          ],
          records: []
        },
        {
          key: "responsables",
          name: "Responsables",
          description: "Personas responsables de equipos o áreas.",
          icon: UserCog,
          fields: [
            { key: "id", label: "ID Responsable", hidden: true },
            { key: "nombre_responsable", label: "Nombre Responsable" },
            { key: "codigo_empleado", label: "Código Empleado" }
          ],
          records: []
        },
        {
          key: "etiquetas",
          name: "Etiquetas RFID",
          description: "Etiquetas RFID disponibles o asignadas.",
          icon: Box,
          fields: [
            { key: "id", label: "ID Etiqueta", hidden: true },
            { key: "name", label: "Código RFID" }
          ],
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
      return this.catalogs.find(catalog => catalog.key === this.currentCatalog) || this.catalogs[0]
    },

    tableFields() {
      return this.selectedCatalog.fields.filter(field => !field.hidden && field.type !== "select")
    },

    formFields() {
      return this.selectedCatalog.fields.filter(field => !field.hidden)
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

  watch: {
    activeCatalog(newCatalog) {
      this.currentCatalog = newCatalog
      this.search = ""
    }
  },

  methods: {
    openCreateModal() {
      this.editingRecord = null
      if (this.currentCatalog === "responsables") {
        this.form = { nombre: "", apellido: "", codigo_empleado_form: "" }
      } else {
        this.form = {}
        this.formFields.forEach(field => {
          this.form[field.key] = ""
        })
      }
      this.showModal = true
    },

    openEditModal(record) {
      this.editingRecord = record
      if (this.currentCatalog === "responsables") {
        const partes = (record.nombre_responsable || "").split(" ")
        this.form = {
          nombre: partes[0] || "",
          apellido: partes.slice(1).join(" ") || "",
          codigo_empleado_form: record.codigo_empleado || ""
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
      console.log('currentCatalog:', this.currentCatalog)
      console.log('activeCatalog:', this.activeCatalog)

      if (this.currentCatalog === 'categorias') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/categoria/${this.editingRecord.id_categoria}`, {
              nombre_categoria: this.form.nombre_categoria
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/categoria', {
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

      } else if (this.currentCatalog === 'marcas') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/marca/${this.editingRecord.id_marca}`, {
              nombre_marca: this.form.nombre_marca
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/marca', {
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

      } else if (this.currentCatalog === 'modelos') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/modelo/${this.editingRecord.id_modelo}`, {
              nombre_modelo: this.form.nombre_modelo,
              id_marca:      this.form.id_marca
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/modelo', {
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

      } else if (this.currentCatalog === 'laboratorios') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/laboratorio/${this.editingRecord.id_laboratorio}`, {
              nombre_laboratorio: this.form.nombre_laboratorio,
              id_edificio:        this.form.id_edificio
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/laboratorio', {
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

      } else if (this.currentCatalog === 'responsables') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/responsable/${this.editingRecord.id}`, {
              nombre:          this.form.nombre,
              apellido:        this.form.apellido,
              codigo_empleado: this.form.codigo_empleado_form
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/responsable', {
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

      } else if (this.currentCatalog === 'edificios') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/edificio/${this.editingRecord.id}`, {
              nombre_edificio: this.form.name
            })
            Object.assign(this.editingRecord, {
              id: response.data.data.id_edificio,
              name: response.data.data.nombre_edificio
            })
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/edificio', {
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

      } else if (this.currentCatalog === 'tipoUsuario') {
        try {
          if (this.editingRecord) {
            const response = await api.put(`/tipo-usuarios/${this.editingRecord.id}`, {
              nombre_tipo: this.form.nombre_tipo
            })
            Object.assign(this.editingRecord, response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.data.message, confirmButtonColor: '#3085d6' })
          } else {
            const response = await api.post('/tipo-usuarios', {
              nombre_tipo: this.form.nombre_tipo
            })
            this.selectedCatalog.records.push(response.data.data)
            await Swal.fire({ icon: 'success', title: '¡Rol agregado!', text: response.data.message, confirmButtonColor: '#3085d6' })
          }
        } catch (error) {
          console.error('Error al guardar rol:', error)
          await Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar rol', confirmButtonColor: '#d33' })
          return
        }

      } else {
        if (this.editingRecord) {
          Object.assign(this.editingRecord, this.form)
        } else {
          this.selectedCatalog.records.unshift({ ...this.form })
        }
      }
      this.closeModal()
    },

    async deleteRecord(record) {
      const nombre = record.nombre_categoria || record.nombre_marca || record.nombre_modelo || record.nombre_laboratorio || record.nombre_responsable || record.name || record.nombre
      const result = await Swal.fire({
        icon: "warning",
        title: "¿Eliminar registro?",
        text: `¿Eliminar "${nombre}"?`,
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
      })

      if (!result.isConfirmed) return

      try {
        let response

        if (this.currentCatalog === 'categorias') {
          response = await api.delete(`/categoria/${record.id_categoria}`)
          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id_categoria !== record.id_categoria
          )
        } else if (this.currentCatalog === 'marcas') {
          response = await api.delete(`/marca/${record.id_marca}`)
          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id_marca !== record.id_marca
          )
        } else if (this.currentCatalog === 'modelos') {
          response = await api.delete(`/modelo/${record.id_modelo}`)
          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id_modelo !== record.id_modelo
          )
        } else if (this.currentCatalog === 'laboratorios') {
          response = await api.delete(`/laboratorio/${record.id_laboratorio}`)
          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id_laboratorio !== record.id_laboratorio
          )
        } else if (this.currentCatalog === 'responsables') {
          response = await api.delete(`/responsable/${record.id}`)
          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id !== record.id
          )
        } else if (this.currentCatalog === 'edificios') {
          response = await api.delete(`/edificio/${record.id}`)
          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id !== record.id
          )
        } else if (this.currentCatalog === 'tipoUsuario') {
          response = await api.delete(`/tipo-usuarios/${record.id}`)
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
    },

    async fetchCategorias() {
      try {
        const response = await api.get("/categoria")
        console.log(response.data)
        const catalogo = this.catalogs.find(c => c.key === 'categorias')
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error("Error al cargar categorías:", error)
      }
    },

    async fetchMarcas() {
      try {
        const response = await api.get('/marca')
        const catalogo = this.catalogs.find(c => c.key === 'marcas')
        if (catalogo) catalogo.records = response.data

        const catalogoModelos = this.catalogs.find(c => c.key === "modelos")
        if (catalogoModelos) {
          const fieldMarca = catalogoModelos.fields.find(f => f.key === "id_marca")
          if (fieldMarca) {
            fieldMarca.options = response.data.map(m => ({ value: m.id_marca, label: m.nombre_marca }))
          }
        }
      } catch (error) {
        console.error("Error al cargar marcas:", error)
      }
    },

    async fetchModelos() {
      try {
        const response = await api.get('/modelo')
        const catalogo = this.catalogs.find(c => c.key === 'modelos')
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error("Error al cargar modelos:", error)
      }
    },

    async fetchLaboratorios() {
      try {
        const response = await api.get('/laboratorio')
        const catalogo = this.catalogs.find(c => c.key === 'laboratorios')
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error("Error al cargar laboratorios:", error)
      }
    },

    async fetchResponsables() {
      try {
        const response = await api.get('/responsable')
        const catalogo = this.catalogs.find(c => c.key === 'responsables')
        console.log('Responsables recibidos:', response.data)
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error("Error al cargar responsables:", error)
      }
    },

    async fetchEdificios() {
      try {
        const response = await api.get('/edificio')
        const catalogo = this.catalogs.find(c => c.key === 'edificios')
        if (catalogo) {
          catalogo.records = response.data.map(e => ({ id: e.id_edificio, name: e.nombre_edificio }))
        }

        const catalogoLabs = this.catalogs.find(c => c.key === "laboratorios")
        if (catalogoLabs) {
          const fieldEdificio = catalogoLabs.fields.find(f => f.key === "id_edificio")
          if (fieldEdificio) {
            fieldEdificio.options = response.data.map(e => ({ value: e.id_edificio, label: e.nombre_edificio }))
          }
        }
      } catch (error) {
        console.error("Error al cargar edificios:", error)
      }
    },

    async fetchTipoUsuario() {
      try {
        const response = await api.get('/tipo-usuarios')
        const catalogo = this.catalogs.find(c => c.key === 'tipoUsuario')
        console.log('Roles recibidos:', response.data)
        if (catalogo) catalogo.records = response.data
      } catch (error) {
        console.error('Error al cargar roles:', error)
      }
    }
  }
}
</script>