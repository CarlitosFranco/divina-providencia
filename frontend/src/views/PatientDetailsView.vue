<template>
  <div class="container">
    <div class="header">
      <h1>Detalles del Paciente</h1>
      <div class="header-buttons">
        <button @click="exportarPDF" class="btn-pdf">📄 Exportar PDF</button>
        <button @click="$router.push('/')" class="btn-back">Volver</button>
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando datos del paciente...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarDatos" class="btn-retry">Reintentar</button>
    </div>

    <!-- Datos del paciente -->
    <div v-else-if="paciente" class="tab-content">
      <div class="tabs">
        <button :class="{ active: activeTab === 'info' }" @click="activeTab = 'info'">Datos Personales</button>
        <button :class="{ active: activeTab === 'medical' }" @click="activeTab = 'medical'">Historial Médico</button>
      </div>

      <!-- Pestaña: Datos Personales -->
      <div v-if="activeTab === 'info'" class="info-grid">
        <div><strong>Documento:</strong> {{ paciente.documento_identidad || 'No registrado' }}</div>
        <div><strong>Fecha nacimiento:</strong> {{ formatFecha(paciente.fecha_nacimiento) }}</div>
        <div><strong>Género:</strong> {{ paciente.genero }}</div>
        <div><strong>Teléfono:</strong> {{ paciente.telefono || 'No registrado' }}</div>
        <div><strong>Celular:</strong> {{ paciente.celular || 'No registrado' }}</div>
        <div><strong>Email:</strong> {{ paciente.email || 'No registrado' }}</div>
        <div><strong>Dirección:</strong> {{ paciente.direccion || 'No registrada' }}</div>
        <div><strong>Contacto emergencia:</strong> {{ paciente.contacto_emergencia_nombre || 'No registrado' }} ({{ paciente.contacto_emergencia_telefono || 'N/A' }})</div>
        <div><strong>Fecha ingreso:</strong> {{ formatFecha(paciente.fecha_ingreso) }}</div>
        <div><strong>Estado:</strong> {{ paciente.estado }}</div>
        <div><strong>Observaciones:</strong> {{ paciente.observaciones || 'Ninguna' }}</div>
      </div>

      <!-- Pestaña: Historial Médico -->
      <div v-if="activeTab === 'medical'">
        <div class="medical-header">
          <button @click="abrirFormularioHistorial()" class="btn-add-medical">+ Agregar Historial</button>
        </div>

        <div v-if="historial.length === 0" class="empty-state">
          <p>📋 No hay historial médico registrado para este paciente.</p>
        </div>
        <div v-else>
          <div v-for="item in historial" :key="item.id" class="card medical-card">
            <div class="card-actions-medical">
              <button @click="editarHistorial(item)" class="btn-edit-small">Editar</button>
              <button @click="eliminarHistorial(item.id)" class="btn-delete-small">Eliminar</button>
            </div>
            <p><strong>Alergias:</strong> {{ item.alergias || 'Ninguna' }}</p>
            <p><strong>Enfermedades crónicas:</strong> {{ item.enfermedades_cronicas || 'Ninguna' }}</p>
            <p><strong>Antecedentes familiares:</strong> {{ item.antecedentes_familiares || 'Ninguno' }}</p>
            <p><strong>Cirugías previas:</strong> {{ item.cirugias_previas || 'Ninguna' }}</p>
            <p><strong>Grupo sanguíneo:</strong> {{ item.grupo_sanguineo || 'No registrado' }}</p>
          </div>
        </div>

        <!-- Modal para crear/editar historial -->
        <HistorialForm
          :show="mostrarFormHistorial"
          :historial="historialSeleccionado"
          :pacienteId="paciente.id"
          @close="cerrarFormularioHistorial"
          @saved="recargarHistorial"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import HistorialForm from '@/components/HistorialForm.vue'

const route = useRoute()
const paciente = ref(null)
const historial = ref([])
const activeTab = ref('info')
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarFormHistorial = ref(false)
const historialSeleccionado = ref(null)

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

const cargarDatos = async () => {
  cargando.value = true
  error.value = false
  const id = route.params.id

  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/pacientes/${id}/completo`, {
      headers: { Authorization: `Bearer ${token}` }
    })

    paciente.value = response.data.paciente
    historial.value = response.data.historial || []
  } catch (err) {
    console.error(err)
    error.value = true
    errorMessage.value = err.response?.data?.error || err.message
  } finally {
    cargando.value = false
  }
}

const recargarHistorial = async () => {
  const id = route.params.id
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get(`/api/pacientes/${id}/completo`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    historial.value = response.data.historial || []
  } catch (err) {
    console.error(err)
  }
}

const abrirFormularioHistorial = () => {
  historialSeleccionado.value = null
  mostrarFormHistorial.value = true
}

const editarHistorial = (item) => {
  historialSeleccionado.value = item
  mostrarFormHistorial.value = true
}

const cerrarFormularioHistorial = () => {
  mostrarFormHistorial.value = false
  historialSeleccionado.value = null
}

const eliminarHistorial = async (idHistorial) => {
  if (!confirm('¿Eliminar este registro de historial médico?')) return
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`/api/historial/${idHistorial}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    recargarHistorial()
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.error || 'Error al eliminar el historial')
  }
}

// ========== EXPORTAR A PDF (con fetch y token en header) ==========
const exportarPDF = async () => {
  const id = route.params.id
  const token = localStorage.getItem('token')
  if (!token) {
    alert('No hay sesión activa. Inicia sesión nuevamente.')
    return
  }
  try {
    const response = await fetch(`/api/exportar/paciente/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.error || 'Error al generar PDF')
    }
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `paciente_${id}.pdf`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error(err)
    alert('Error al generar el PDF: ' + err.message)
  }
}

onMounted(() => {
  cargarDatos()
})
</script>

<style scoped>
/* Estilos base (mantenemos los existentes) */
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.header-buttons { display: flex; gap: 12px; }
.btn-back { background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
.btn-pdf { background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; transition: 0.2s; }
.btn-pdf:hover { background: #c82333; transform: scale(1.02); }
.tabs { display: flex; gap: 5px; margin-bottom: 20px; border-bottom: 1px solid #ddd; flex-wrap: wrap; }
.tabs button { background: none; border: none; padding: 10px 15px; cursor: pointer; font-size: 1rem; }
.tabs button.active { border-bottom: 2px solid #42b983; color: #42b983; font-weight: bold; }
.tab-content { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap: 10px; }
.card { background: #f8f9fa; border-left: 4px solid #42b983; padding: 12px; margin-bottom: 12px; border-radius: 4px; position: relative; }
.loading-state, .error-state, .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 24px; }
.spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
.btn-retry { margin-top: 16px; background: #667eea; color: white; border: none; padding: 8px 20px; border-radius: 40px; cursor: pointer; }

/* Estilos para el CRUD de historial */
.medical-header { display: flex; justify-content: flex-end; margin-bottom: 20px; }
.btn-add-medical { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 8px 20px; border-radius: 40px; cursor: pointer; font-weight: 500; transition: all 0.2s; }
.btn-add-medical:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
.medical-card { position: relative; padding-right: 100px; }
.card-actions-medical { position: absolute; top: 12px; right: 12px; display: flex; gap: 8px; }
.btn-edit-small, .btn-delete-small { padding: 4px 10px; border: none; border-radius: 20px; font-size: 0.7rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn-edit-small { background: #e0e7ff; color: #4338ca; }
.btn-edit-small:hover { background: #c7d2fe; }
.btn-delete-small { background: #fee2e2; color: #b91c1c; }
.btn-delete-small:hover { background: #fecaca; }

/* Responsive */
@media (max-width: 640px) {
  .medical-card { padding-right: 12px; }
  .card-actions-medical { position: static; justify-content: flex-end; margin-bottom: 10px; }
}
</style>
