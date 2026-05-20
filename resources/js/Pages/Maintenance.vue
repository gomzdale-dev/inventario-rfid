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

      <!-- 
      <div class="maintenance-card">
        <Activity size="30" />
        <div>
          <h2>CRUD</h2>
          <p>Crear, editar y eliminar</p>
        </div>
      </div>
      -->

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
                <th v-for="field in visibleFields" :key="field.key">
                  {{ field.label }}
                </th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="record in filteredRecords" :key="record.id || record.id_categoria">
                <td v-for="field in visibleFields" :key="field.key">
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

        <div
          v-for="field in visibleFields"
          :key="field.key"
          class="maintenance-field"
        >
          <label>{{ field.label }} *</label>
          <input v-model="form[field.key]" required />
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
import axios from "axios"
import {
  Database,
  TableProperties,
  Activity,
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
  Box
} from "lucide-vue-next"

export default {
  name: "Maintenance",

  components: {
    Database,
    TableProperties,
    Activity,
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
    Box
  },

  props: {
    activeCatalog: {
      type: String,
      default: "categorias"
    }
  },

  data() {
    return {
      currentCatalog: this.activeCatalog,
      search: "",
      showModal: false,
      editingRecord: null,
      form: {},

      catalogs: [
        {
          key: "categorias",
          name: "Categorías",
          description: "Clasificación general de activos tecnológicos.",
          icon: Tags,
          fields: [
            { key: "id_categoria", label: "ID Categoría", hidden: true },
            { key: "nombre_categoria", label: "Nombre Categoría" }
          ],
          records: []
        },
        {
          key: "marcas",
          name: "Marcas",
          description: "Marcas comerciales de los equipos registrados.",
          icon: Landmark,
          fields: [
            { key: "id", label: "ID Marca", hidden: true },
            { key: "name", label: "Nombre Marca" }
          ],
          records: [
            { id: "MAR-001", name: "Dell" },
            { id: "MAR-002", name: "HP" },
            { id: "MAR-003", name: "Cisco" }
          ]
        },
        {
          key: "modelos",
          name: "Modelos",
          description: "Modelos asociados a marcas y activos.",
          icon: Cpu,
          fields: [
            { key: "id", label: "ID Modelo", hidden: true },
            { key: "name", label: "Nombre Modelo" }
          ],
          records: [
            { id: "MOD-001", name: "OptiPlex 7090" },
            { id: "MOD-002", name: "EliteBook 840" },
            { id: "MOD-003", name: "PowerLite" }
          ]
        },
        {
          key: "laboratorios",
          name: "Laboratorios",
          description: "Laboratorios registrados dentro del sistema.",
          icon: Building2,
          fields: [
            { key: "id", label: "ID Laboratorio", hidden: true },
            { key: "name", label: "Nombre Laboratorio" }
          ],
          records: [
            { id: "LAB-001", name: "Laboratorio A-102" },
            { id: "LAB-002", name: "Laboratorio B-205" }
          ]
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
            { key: "id", label: "ID Tipo", hidden: true },
            { key: "name", label: "Nombre Tipo" }
          ],
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
            { key: "id", label: "ID Responsable", hidden: true },
            { key: "name", label: "Nombre Responsable" }
          ],
          records: [
            { id: "RESP-001", name: "Ing. Carlos Méndez" },
            { id: "RESP-002", name: "Lic. María Rodríguez" }
          ]
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

    visibleFields() {
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

  mounted() {
    this.fetchCategorias()
    this.fetchEdificios()
  },

  methods: {
    openCreateModal() {
      this.editingRecord = null
      this.form = {}

      this.visibleFields.forEach(field => {
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

    async saveRecord() {
      if (this.currentCatalog === "categorias") {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`/api/categoria/${this.editingRecord.id_categoria}`, {
              nombre_categoria: this.form.nombre_categoria
            })

            Object.assign(this.editingRecord, response.data.data)
            alert(response.data.message)
          } else {
            const response = await axios.post("/api/categoria", {
              nombre_categoria: this.form.nombre_categoria
            })

            this.selectedCatalog.records.push(response.data.data)
            alert(response.data.message)
          }
        } catch (error) {
          console.error("Error al guardar categoría:", error)
          alert("Error al guardar la categoría")
          return
        }
      } else if (this.currentCatalog === "edificios") {
        try {
          if (this.editingRecord) {
            const response = await axios.put(`/api/edificio/${this.editingRecord.id}`, {
              nombre_edificio: this.form.name
            })

            Object.assign(this.editingRecord, {
              id: response.data.data.id_edificio,
              name: response.data.data.nombre_edificio
            })

            alert(response.data.message)
          } else {
            const response = await axios.post("/api/iedificio", {
              nombre_edificio: this.form.name
            })

            this.selectedCatalog.records.push({
              id: response.data.data.id_edificio,
              name: response.data.data.nombre_edificio
            })

            alert(response.data.message)
          }
        } catch (error) {
          console.error("Error al guardar edificio:", error)
          alert("Error al guardar el edificio")
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
      if (this.currentCatalog !== "categorias") {
        this.selectedCatalog.records = this.selectedCatalog.records.filter(item => item !== record)
        return
      }

      const confirmed = confirm(`¿Desactivar la categoría "${record.nombre_categoria}"?`)

      if (confirmed) {
        try {
          const response = await axios.delete(`/api/categoria/${record.id_categoria}`)

          this.selectedCatalog.records = this.selectedCatalog.records.filter(
            item => item.id_categoria !== record.id_categoria
          )

          alert(response.data.message)
        } catch (error) {
          console.error("Error al desactivar categoría:", error)
          alert("Error al desactivar la categoría")
        }
      }
    },

    async fetchCategorias() {
      try {
        const response = await axios.get("/api/categoria/categorias")
        const catalogo = this.catalogs.find(c => c.key === "categorias")

        if (catalogo) {
          catalogo.records = response.data
        }
      } catch (error) {
        console.error("Error al cargar categorías:", error)
      }
    },

    async fetchEdificios() {
      try {
        const response = await axios.get("/api/edificio/edificios")
        const catalogo = this.catalogs.find(c => c.key === "edificios")

        if (catalogo) {
          catalogo.records = response.data.map(e => ({
            id: e.id_edificio,
            name: e.nombre_edificio
          }))
        }
      } catch (error) {
        console.error("Error al cargar edificios:", error)
      }
    }
  }
}
</script>
