<template>
  <section class="users-page">
    <header class="users-header">
      <h1>Gestión de Usuarios y Roles</h1>
      <p>Administración de permisos de acceso al sistema</p>
    </header>

    <section class="users-stats">
      <div class="user-stat-card">
        <div>
          <p>Total Usuarios</p>
          <h2>{{ users.length }}</h2>
        </div>
        <div class="stat-icon blue"><Users size="30" /></div>
      </div>

      <div class="user-stat-card">
        <div>
          <p>Usuarios Activos</p>
          <h2 class="green-text">{{ activeUsers }}</h2>
        </div>
        <div class="stat-icon green"><CheckCircle size="30" /></div>
      </div>

      <div class="user-stat-card">
        <div>
          <p>Administradores</p>
          <h2 class="purple-text">{{ adminUsers }}</h2>
        </div>
        <div class="stat-icon purple"><Shield size="30" /></div>
      </div>

      <div class="user-stat-card">
        <div>
          <p>Inactivos</p>
          <h2 class="gray-text">{{ inactiveUsers }}</h2>
        </div>
        <div class="stat-icon gray"><XCircle size="30" /></div>
      </div>
    </section>

    <section class="users-toolbar">
      <div class="tabs">
        <button :class="{ active: filterStatus === 'Todos' }" @click="filterStatus = 'Todos'">
          Todos
        </button>
        <button :class="{ active: filterStatus === 'Activo' }" @click="filterStatus = 'Activo'">
          Activos
        </button>
        <button :class="{ active: filterStatus === 'Inactivo' }" @click="filterStatus = 'Inactivo'">
          Inactivos
        </button>
      </div>

      <button class="create-user-btn" @click="openCreateModal">
        <UserPlus size="22" />
        Crear Nuevo Usuario
      </button>
    </section>

    <section class="users-table-card">
      <table>
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Último Acceso</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="user in filteredUsers" :key="user.email">
            <td>
              <div class="user-info">
                <div class="avatar">{{ user.initials }}</div>
                <div>
                  <h3>{{ user.name }}</h3>
                  <p>{{ user.email }}</p>
                </div>
              </div>
            </td>

            <td>
              <span :class="['role-badge', roleClass(user.role)]">
                {{ user.role }}
              </span>
            </td>

            <td>
              <span :class="['status-badge', user.status === 'Activo' ? 'active' : 'inactive']">
                 {{ user.status  }}
              </span>
            </td>

            <td>{{ user.lastAccess }}</td>

            <td>
              <div class="actions">
                <button title="Editar" @click="openEditModal(user)">
                  <Pencil size="21" />
                </button>
                <button title="Bloquear usuario" @click="toggleUserStatus(user)">
                  <Lock size="21" />
                </button>
                <button title="Eliminar" @click="deleteUser(user)">
                  <Trash2 size="21" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <section class="security-box">
      <Shield size="30" />
      <div>
        <h2>Control de Acceso y Seguridad</h2>
        <p>
          El sistema registra todas las acciones realizadas por los usuarios.
          Los permisos se asignan según el rol para garantizar que cada usuario
          solo pueda acceder a las funciones necesarias para sus responsabilidades.
        </p>
      </div>
    </section>

    <div v-if="showModal" class="modal-backdrop" @click="closeModal">
      <form class="user-modal" @submit.prevent="saveUser" @click.stop>
        <header>
          <h2>{{ editingUser ? "Editar Usuario" : "Crear Nuevo Usuario" }}</h2>
          <button type="button" @click="closeModal">
            <X size="22" />
          </button>
        </header>

        <div class="modal-grid">
          <div class="modal-field">
            <label>Nombre completo *</label>
            <input v-model="form.name" required placeholder="Ej: Carlos Martínez" />
          </div>

          <div class="modal-field">
            <label>Correo institucional *</label>
            <input
              v-model="form.email"
              required
              type="email"
              placeholder="usuario@itca.edu.sv"
              :disabled="editingUser"
            />
          </div>

          <div class="modal-field">
            <label>Rol *</label>
            <select v-model="form.id_tipo"  required>
               <option 
                    v-for="role in roles"
                    :key="role.id"
                    :value="role.id"
                  >
                  {{ role.nombre_tipo }}
                  </option>
                </select>
          </div>

          <div class="modal-field">
            <label>Estado *</label>
            <select v-model="form.status" required>
              <option value = "A">Activo</option>
              <option value = "I">Inactivo</option>
            </select>
          </div>

          <div v-if="!editingUser" class="modal-field full">
            <label>Contraseña temporal *</label>
            <input v-model="form.password" required type="password" placeholder="Mínimo 8 caracteres" />
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="cancel-btn" @click="closeModal">Cancelar</button>
          <button type="submit" class="save-btn">
            {{ editingUser ? "Actualizar Usuario" : "Guardar Usuario" }}
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import {
  Users,
  CheckCircle,
  Shield,
  XCircle,
  UserPlus,
  Pencil,
  Lock,
  Trash2,
  X
} from "lucide-vue-next"
import axios from "axios"
export default {
  name: "UsersPage",
  components: {
    Users,
    CheckCircle,
    Shield,
    XCircle,
    UserPlus,
    Pencil,
    Lock,
    Trash2,
    X
  },
  data() {
    return {
      filterStatus: "Todos",
      showModal: false,
      editingUser: null,
      users:[],
      roles:[],

      form: this.emptyForm()
    }
  },
  computed: {
    filteredUsers() {
      if (this.filterStatus === "Todos") return this.users
      return this.users.filter(user => user.status === this.filterStatus)
    },
    activeUsers() {
      return this.users.filter(user => user.status === "Activo").length
    },
    inactiveUsers() {
      return this.users.filter(user => user.status === "Inactivo").length
    },
    adminUsers() {
      return this.users.filter(user => user.role === "Administrador del sistema").length
    }
  },
  mounted() {
    this.getRoles()
    this.getUsers()},
  methods: {
    emptyForm() {
      return {
        id: null,
        name: "",
        email: "",
        id_tipo: null,
        status: "A",
        password: ""
      }
    },
    getInitials(name) {
      return name
        .split(" ")
        .slice(0, 2)
        .map(word => word[0])
        .join("")
        .toUpperCase()
    },
    roleClass(role) {
      if (role.includes("Administrador")) return "admin"
      if (role.includes("Técnico")) return "tech"
      if (role.includes("administrativo")) return "staff"
      return "viewer"
    },
    async getRoles() {
    const res = await axios.get("http://127.0.0.1:8000/api/roles")
         this.roles = res.data
    },
    async getUsers() {
       try {
       const response = await axios.get("http://127.0.0.1:8000/api/usuarios")

        this.users = response.data.map(user => ({
        id: user.id_usuario,
        initials: this.getInitials(user.nombre_usuario),
        name: user.nombre_usuario,
        email: user.correo,
        role: user.tipo_usuario?.nombre_tipo ?? '',
        id_tipo: user.id_tipo, 
        status: user.estado === 'A' ? "Activo" : "Inactivo",
        lastAccess: user.ultimo_acceso || "Sin acceso"
       }))
      } catch (error) {
         
         console.error(error)
         console.log("users:", this.users)
         console.log("response:", error?.response?.data)

      }
    },
    openCreateModal(user) {
      this.editingUser = null
      this.form = this.emptyForm()
      this.showModal = true
    },
    openEditModal(user) {
      this.editingUser = user
      this.form = {
        id: user.id,
        name: user.name,
        email: user.email,
        status: user.status === "Activo" ? "A" : "I",
        id_tipo: user.id_tipo
      }
      this.showModal = true
    },
    closeModal() {
      this.showModal = false
      this.editingUser = null
      this.form = this.emptyForm()
    },
    async saveUser() {
      try {
        if (!this.editingUser){
           console.log(this.form)
           await axios.post("http://127.0.0.1:8000/api/usuarios", {
            nombre_usuario: this.form.name,
            correo: this.form.email,
            estado: this.form.status,
            id_tipo: this.form.id_tipo,
            password: this.form.password })

        }else{ // actualizar 
           await axios.put(`http://127.0.0.1:8000/api/usuarios/${this.form.id}`, {
           nombre_usuario: this.form.name,
           correo: this.form.email,
           estado: this.form.status,
           id_tipo: this.form.id_tipo})
        }
        this.closeModal()
        this.getUsers()
      } catch (error) {
          console.error(error)
          console.log("users:", this.users)
          console.log("response:", error?.response?.data)
      }
    },
    toggleUserStatus(user) {
      user.status = user.status === "Activo" ? "Inactivo" : "Activo"
    },
    deleteUser(user) {
      const confirmed = confirm(`¿Eliminar al usuario ${user.name}?`)

      if (confirmed) {
        this.users = this.users.filter(item => item.email !== user.email)
      }
    }
  }
}
</script>