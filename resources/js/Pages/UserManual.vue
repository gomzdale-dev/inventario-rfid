<template>
  <section class="help-page">
    <header class="help-page-header">
      <h1>Manual de usuario</h1>
      <p>Guía rápida y videos interactivos para utilizar el Sistema de Inventario RFID de ITCA-FEPADE.</p>
      
      <!-- Buscador interactivo -->
      <div class="search-wrapper">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="¿Qué módulo deseas consultar? (Ej: activos, reportes...)"
          class="manual-search-bar"
        />
      </div>
    </header>

    <div class="manual-layout">
      <!-- Menú lateral / Índice interactivo -->
      <aside class="manual-sidebar">
        <h3>Módulos del Sistema</h3>
        <ul>
          <li 
            v-for="item in filteredModules" 
            :key="item.id"
            :class="{ active: selectedModule && selectedModule.id === item.id }"
            @click="selectedModule = item"
          >
            <component :is="item.icon" size="18" />
            <span>{{ item.title }}</span>
          </li>
          <li v-if="filteredModules.length === 0" class="no-results">
            No se encontraron resultados
          </li>
        </ul>
      </aside>

      <!-- Blindaje con v-if para evitar errores de lectura de propiedades null -->
      <main class="manual-content" v-if="selectedModule">
        <article class="help-card interactive-card">
          <div class="card-header-inline">
            <component :is="selectedModule.icon" size="28" class="module-icon" />
            <h2>{{ selectedModule.title }}</h2>
          </div>
          
          <!-- Descripción principal -->
          <p v-if="selectedModule.description">{{ selectedModule.description }}</p>

          <!-- Renderizado si es tipo lista ordenada (como Inicio de Sesión) -->
          <ol v-if="selectedModule.type === 'ordered'">
            <li v-for="(step, index) in selectedModule.content" :key="index">
              {{ step }}
            </li>
          </ol>

          <!-- Renderizado si es tipo lista con subtítulos (como Módulo de activos) -->
          <ul v-if="selectedModule.type === 'bullet'">
            <li v-for="(item, index) in selectedModule.content" :key="index">
              <strong>{{ item.bold }}:</strong> {{ item.text }}
            </li>
          </ul>

          <!-- Zona del Video Tutorial Nativo de Windows (.mp4) -->
          <div class="video-section" v-if="selectedModule.videoUrl">
            <div class="video-title">
              <PlayCircle size="20" />
              <h3>Video tutorial de apoyo</h3>
            </div>
            
            <!-- Reemplazo de iframe por etiqueta HTML5 nativa <video> -->
            <div class="video-container">
              <video 
                :src="selectedModule.videoUrl"
                autoplay 
                loop 
                muted 
                playsinline
                controls
                class="manual-video-player"
              >
                Tu navegador no soporta la reproducción de videos en formato MP4.
              </video>
            </div>
          </div>
        </article>
      </main>

      <!-- Estado alternativo amigable si la búsqueda vacía el índice -->
      <main class="manual-content" v-else>
        <article class="help-card interactive-card">
          <h2>No hay selección</h2>
          <p>Por favor, borra los términos de búsqueda o selecciona un módulo válido del menú lateral.</p>
        </article>
      </main>
    </div>
  </section>
</template>

<script>
import { 
  LogIn, 
  LayoutDashboard, 
  Package, 
  Boxes, 
  Users, 
  Wrench, 
  BarChart3, 
  Bell,
  PlayCircle
} from "lucide-vue-next";

export default {
  name: "UserManual",
  components: { PlayCircle },
  data() {
    return {
      searchQuery: "",
      selectedModule: null,
      modules: [
        {
          id: "login",
          title: "Inicio de sesión",
          icon: LogIn,
          type: "ordered",
          content: [
            "Ingresar el correo institucional autorizado.",
            "Escribir la contraseña del usuario.",
            "Presionar el botón Acceder al Sistema."
          ],
          videoUrl: "/videos/login.mp4"
        },
        {
          id: "dashboard",
          title: "Panel principal",
          icon: LayoutDashboard,
          type: "text",
          description: "Permite visualizar indicadores generales del sistema, como activos registrados, equipos asignados, equipos en mantenimiento y actividad reciente.",
          videoUrl: "/videos/dashboard.mp4"
        },
        {
          id: "inventario",
          title: "Inventario",
          icon: Package,
          type: "text",
          description: "Permite consultar activos tecnológicos, realizar búsquedas, revisar códigos asociados y apoyar el proceso de inventario físico.",
          videoUrl: "/videos/inventario.mp4"
        },
        {
          id: "activos",
          title: "Módulo de activos",
          icon: Boxes,
          type: "bullet",
          content: [
            { bold: "Registrar Activos", text: "crea un activo nuevo en la base de datos." },
            { bold: "Activos", text: "muestra activos registrados y permite ver detalle, asignar responsable o cambiar etiqueta." },
            { bold: "Registrar Movimientos", text: "registra traslados, entradas, salidas o cambios de ubicación." }
          ],
          videoUrl: "/videos/activos.mp4"
        },
        {
          id: "usuarios",
          title: "Usuarios",
          icon: Users,
          type: "text",
          description: "Permite administrar usuarios del sistema, roles y accesos permitidos según el tipo de usuario.",
          videoUrl: "/videos/usuarios.mp4"
        },
        {
          id: "mantenimiento",
          title: "Mantenimiento",
          icon: Wrench,
          type: "text",
          description: "Permite administrar catálogos base como categorías, marcas, modelos, laboratorios, edificios y responsables.",
          videoUrl: "/videos/mantenimiento.mp4"
        },
        {
          id: "reportes",
          title: "Reportes",
          icon: BarChart3,
          type: "text",
          description: "Permite consultar reportes de inventario general, activos por categoría, ubicación, estado, lecturas RFID, activos no encontrados y diferencias entre inventarios.",
          videoUrl: "/videos/reportes.mp4"
        },
        {
          id: "notificaciones",
          title: "Notificaciones",
          icon: Bell,
          type: "text",
          description: "La campana muestra alertas RFID o IA. Desde ahí se pueden filtrar, marcar como leídas, ver detalle o eliminar notificaciones.",
          videoUrl: "/videos/notificaciones.mp4"
        }
      ]
    };
  },
  computed: {
    filteredModules() {
      if (!this.searchQuery) return this.modules;
      const query = this.searchQuery.toLowerCase();
      return this.modules.filter(m => m.title.toLowerCase().includes(query));
    }
  },
  watch: {
    filteredModules(newVal) {
      if (newVal.length) {
        if (!newVal.includes(this.selectedModule)) {
          this.selectedModule = newVal[0];
        }
      } else {
        this.selectedModule = null; // Evita conservar un módulo que no coincide
      }
    }
  },
  created() {
    this.selectedModule = this.modules[0];
  }
};
</script>

<style scoped>
.help-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.help-page-header {
  margin-bottom: 25px;
}

.search-wrapper {
  margin-top: 15px;
}

.manual-search-bar {
  width: 100%;
  max-width: 450px;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.95rem;
  outline: none;
}

.manual-search-bar:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.manual-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 25px;
  margin-top: 20px;
}

@media (max-width: 768px) {
  .manual-layout {
    grid-template-columns: 1fr;
  }
}

.manual-sidebar {
  background: #f8fafc;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  height: fit-content;
}

.manual-sidebar h3 {
  font-size: 0.8rem;
  text-transform: uppercase;
  color: #64748b;
  margin-bottom: 12px;
  padding-left: 5px;
}

.manual-sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.manual-sidebar li {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  margin-bottom: 4px;
  border-radius: 6px;
  cursor: pointer;
  color: #475569;
  font-weight: 500;
  transition: all 0.15s;
}

.manual-sidebar li:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.manual-sidebar li.active {
  background: #e0f2fe;
  color: #0369a1;
}

.no-results {
  font-size: 0.85rem;
  color: #94a3b8;
  padding: 10px;
}

.interactive-card {
  width: 100%;
  box-sizing: border-box;
}

.card-header-inline {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.card-header-inline h2 {
  margin: 0;
}

.module-icon {
  color: #0284c7;
}

.video-section {
  margin-top: 30px;
  border-top: 1px solid #e2e8f0;
  padding-top: 20px;
}

.video-title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  color: #1e293b;
}

.video-title h3 {
  margin: 0;
  font-size: 1.1rem;
}

/* Contenedor optimizado para el reproductor nativo */
.video-container {
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  background: #1e293b;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.manual-video-player {
  display: block;
  width: 100%;
  height: auto;
  max-height: 550px;
  object-fit: contain;
}
</style>