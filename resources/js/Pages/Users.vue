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
          <p>Usuarios Inactivos</p>
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
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
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
                {{ user.role || "Sin rol" }}
              </span>
            </td>

            <td>
              <div class="actions">
                <button title="Editar usuario" @click="openEditModal(user)">
                  <Pencil size="21" />
                </button>
                <button title="Cambiar contraseña" @click="openPasswordModal(user)">
                  <KeyRound size="21" />
                </button>
                <button title="Eliminar usuario" @click="deleteUser(user)">
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

          <div class="modal-field full">
            <label>Rol *</label>
            <select v-model="form.id_tipo" required>
              <option value="" disabled>Seleccionar rol</option>
              <option
                v-for="role in roles"
                :key="role.id_tipo || role.id"
                :value="role.id_tipo || role.id"
              >
                {{ role.nombre_tipo }}
              </option>
            </select>
          </div>

          <div v-if="!editingUser" class="modal-field full">
            <label>Contraseña *</label>
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

    <div v-if="showPasswordModal" class="modal-backdrop" @click="closePasswordModal">
      <form class="user-modal" @submit.prevent="savePassword" @click.stop>
        <header>
          <h2>Cambiar Contraseña</h2>
          <button type="button" @click="closePasswordModal">
            <X size="22" />
          </button>
        </header>

        <div class="modal-grid">
          <div class="modal-field full">
            <label>Usuario</label>
            <input :value="passwordUser?.name" disabled />
          </div>

          <div class="modal-field full">
            <label>Nueva contraseña *</label>
            <input
              v-model="passwordForm.password"
              required
              type="password"
              placeholder="Nueva contraseña"
            />
          </div>

          <div class="modal-field full">
            <label>Confirmar contraseña *</label>
            <input
              v-model="passwordForm.confirmPassword"
              required
              type="password"
              placeholder="Confirmar contraseña"
            />
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="cancel-btn" @click="closePasswordModal">Cancelar</button>
          <button type="submit" class="save-btn">Guardar Contraseña</button>
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
  KeyRound,
  Trash2,
  X
} from "lucide-vue-next"

import api from "../services/api"

export default {
  name: "UsersPage",

  components: {
    Users,
    CheckCircle,
    Shield,
    XCircle,
    UserPlus,
    Pencil,
    KeyRound,
    Trash2,
    X
  },

  data() {
    return {
      filterStatus: "Todos",
      showModal: false,
      showPasswordModal: false,
      editingUser: null,
      passwordUser: null,
      users: [],
      roles: [],
      form: this.emptyForm(),
      passwordForm: {
        password: "",
        confirmPassword: ""
      }
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
      return this.users.filter(user => user.role?.includes("Administrador")).length
    }
  },

  mounted() {
    this.getRoles()
    this.getUsers()
  },

  methods: {
    emptyForm() {
      return {
        id: null,
        name: "",
        email: "",
        id_tipo: "",
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
      if (role?.includes("Administrador")) return "admin"
      if (role?.includes("Técnico")) return "tech"
      if (role?.includes("administrativo")) return "staff"
      return "viewer"
    },

    async getRoles() {
      try {
        const res = await api.get("/roles")
        this.roles = res.data
      } catch (error) {
        console.error("Error al cargar roles:", error)
      }
    },

    async getUsers() {
      try {
        const response = await api.get("/usuarios")

        this.users = response.data.map(user => ({
          id: user.id_usuario,
          initials: this.getInitials(user.nombre_usuario),
          name: user.nombre_usuario,
          email: user.correo,
          role: user.tipo_usuario?.nombre_tipo ?? "Sin rol",
          id_tipo: user.id_tipo,
          status: user.estado === "A" ? "Activo" : "Inactivo"
        }))
      } catch (error) {
        console.error("Error al cargar usuarios:", error)
      }
    },

    openCreateModal() {
      window.scrollTo({ top: 0, behavior: "smooth" })
      this.editingUser = null
      this.form = this.emptyForm()
      this.showModal = true
    },

    openEditModal(user) {
      window.scrollTo({ top: 0, behavior: "smooth" })
      this.editingUser = user
      this.form = {
        id: user.id,
        name: user.name,
        email: user.email,
        status: user.status === "Activo" ? "A" : "I",
        id_tipo: user.id_tipo,
        password: ""
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
        if (!this.editingUser) {
          await api.post("/usuarios", {
            nombre_usuario: this.form.name,
            correo: this.form.email,
            estado: this.form.status,
            id_tipo: this.form.id_tipo,
            password: this.form.password
          })
        } else {
          await api.put(`/usuarios/${this.form.id}`, {
            nombre_usuario: this.form.name,
            correo: this.form.email,
            estado: this.form.status,
            id_tipo: this.form.id_tipo
          })
        }

        this.closeModal()
        this.getUsers()
      } catch (error) {
        console.error("Error al guardar usuario:", error)
        alert("No se pudo guardar el usuario")
      }
    },

    openPasswordModal(user) {
      window.scrollTo({ top: 0, behavior: "smooth" })
      this.passwordUser = user
      this.passwordForm = {
        password: "",
        confirmPassword: ""
      }
      this.showPasswordModal = true
    },

    closePasswordModal() {
      this.showPasswordModal = false
      this.passwordUser = null
      this.passwordForm = {
        password: "",
        confirmPassword: ""
      }
    },

    async savePassword() {
      if (this.passwordForm.password !== this.passwordForm.confirmPassword) {
        alert("Las contraseñas no coinciden")
        return
      }

      if (this.passwordForm.password.length < 8) {
        alert("La contraseña debe tener mínimo 8 caracteres")
        return
      }

      try {
        await api.put(`/usuarios/${this.passwordUser.id}`, {
          password: this.passwordForm.password
        })

        alert(`Contraseña actualizada para ${this.passwordUser.name}`)
        this.closePasswordModal()
      } catch (error) {
        console.error("Error al cambiar contraseña:", error)
        alert("No se pudo actualizar la contraseña")
      }
    },

    async deleteUser(user) {
      try {
        await api.put(`/usuarios/${user.id}`, {
          nombre_usuario: user.name,
          correo: user.email,
          estado: "I",
          id_tipo: user.id_tipo
        })

        this.getUsers()
      } catch (error) {
        console.error("Error al eliminar usuario:", error)
      }
    }
  }
}
</script>